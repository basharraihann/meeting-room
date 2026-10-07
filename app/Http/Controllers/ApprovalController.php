<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use App\Notifications\BookingStatusNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ApprovalController extends Controller
{
    private const RIWAYAT_STATUSES = ['APPROVED', 'REJECTED', 'CANCELLED'];

    public function index(Request $request)
    {
        $tu = $request->user();
        $assignedRoomId = $tu?->room_id;
        $isAdmin = $this->isAdmin($request);

        // Bukan Admin DAN tidak punya room_id -> tampilkan view noRoom
        if (!$assignedRoomId && !$isAdmin) {
            return view('approvals.index', [
                'clusters' => collect(),
                'rooms' => collect(),
                'roomId' => null,
                'pendingCounts' => collect(),
                'assignedRoom' => null,
                'noRoom' => true,
                'riwayat' => collect(),
                'riwayatDays' => collect(),
                'riwayatCounts' => collect(),
                'riwayatTotal' => 0,
                'statusFilter' => null,
            ]);
        }

        // Ruangan yang dikelola (admin tanpa room_id -> semua ruangan)
        $rooms = $assignedRoomId ? Room::where('id', $assignedRoomId)->get() : Room::all();
        $assignedRoom = $assignedRoomId ? Room::find($assignedRoomId) : null;

        // ===== PENDING =====
        $pendingQuery = Booking::with(['room', 'pic'])->where('status', 'PENDING');
        if ($assignedRoomId) {
            $pendingQuery->where('room_id', $assignedRoomId);
        }
        $pending = $pendingQuery->orderBy('start_at')->get();

        $pendingCountsQuery = Booking::where('status', 'PENDING')->selectRaw('room_id, COUNT(*) as total');
        if ($assignedRoomId) {
            $pendingCountsQuery->where('room_id', $assignedRoomId);
        }
        $pendingCounts = $pendingCountsQuery->groupBy('room_id')->pluck('total', 'room_id');

        // Clustering overlap
        $clusters = collect();
        $current = [];
        $currentEnd = null;

        foreach ($pending->values() as $b) {
            if (empty($current)) {
                $current = [$b];
                $currentEnd = $b->end_at;
                continue;
            }

            if ($b->start_at < $currentEnd) {
                $current[] = $b;
                if ($b->end_at > $currentEnd) {
                    $currentEnd = $b->end_at;
                }
            } else {
                $clusters->push($this->makeCluster($current, $currentEnd));
                $current = [$b];
                $currentEnd = $b->end_at;
            }
        }

        if (!empty($current)) {
            $clusters->push($this->makeCluster($current, $currentEnd));
        }

        $clusters = $clusters->sortBy('start')->values();

        // ===== RIWAYAT =====
        $riwayatBase = Booking::with(['pic', 'room'])->whereIn('status', self::RIWAYAT_STATUSES);

        if ($assignedRoomId) {
            $riwayatBase->where('room_id', $assignedRoomId);
        } elseif ($request->filled('room_id')) {
            $riwayatBase->where('room_id', $request->room_id);
        }

        // Total tanpa filter pencarian/tanggal/status (untuk tile ringkasan)
        $riwayatTotal = (clone $riwayatBase)->count();

        if ($request->filled('q')) {
            $q = trim($request->q);
            $riwayatBase->where(function ($w) use ($q) {
                $w->where('title', 'like', "%{$q}%")
                    ->orWhere('unit_kerja', 'like', "%{$q}%")
                    ->orWhere('applicant_email', 'like', "%{$q}%")
                    ->orWhere('tu_note', 'like', "%{$q}%")
                    ->orWhereHas('pic', fn($p) => $p->where('name', 'like', "%{$q}%"));
            });
        }
        if ($request->filled('from')) {
            $riwayatBase->whereDate('start_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $riwayatBase->whereDate('start_at', '<=', $request->to);
        }

        // Jumlah per status (sebelum filter status, supaya angka chip tetap utuh)
        $riwayatCounts = (clone $riwayatBase)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusFilter = in_array($request->status, self::RIWAYAT_STATUSES, true) ? $request->status : null;
        if ($statusFilter) {
            $riwayatBase->where('status', $statusFilter);
        }

        $riwayat = $riwayatBase->orderBy('start_at', 'desc')->paginate(15)->withQueryString();

        // Kelompok per hari (urutan sudah start_at desc)
        $riwayatDays = $riwayat->getCollection()
            ->groupBy(fn($b) => Carbon::parse($b->start_at)->toDateString());

        return view('approvals.index', [
            'clusters' => $clusters,
            'rooms' => $rooms,
            'roomId' => $assignedRoomId,
            'pendingCounts' => $pendingCounts,
            'assignedRoom' => $assignedRoom,
            'noRoom' => false,
            'riwayat' => $riwayat,
            'riwayatDays' => $riwayatDays,
            'riwayatCounts' => $riwayatCounts,
            'riwayatTotal' => $riwayatTotal,
            'statusFilter' => $statusFilter,
        ]);
    }

    public function approve(Request $request, Booking $booking)
    {
        $this->authorizeRoom($request, $booking, 'Anda tidak berwenang approve booking ruangan ini.');

        if ($booking->status !== 'PENDING') {
            return back()->withErrors(['msg' => 'Booking bukan status PENDING.']);
        }

        $conflicts = Booking::with('pic')
            ->where('room_id', $booking->room_id)
            ->where('status', 'PENDING')
            ->where('id', '!=', $booking->id)
            ->where('start_at', '<', $booking->end_at)
            ->where('end_at', '>', $booking->start_at)
            ->get();

        $booking->update(['status' => 'APPROVED', 'tu_note' => null]);
        $this->notifyApplicant($booking);

        foreach ($conflicts as $c) {
            $c->update([
                'status' => 'REJECTED',
                'tu_note' => 'Ditolak otomatis karena bentrok dengan booking APPROVED: "' . $booking->title . '".',
            ]);
            $this->notifyApplicant($c);
        }

        return back()->with('status', 'Booking approved. Booking lain yang bentrok otomatis ditolak.');
    }

    public function reject(Request $request, Booking $booking)
    {
        $this->authorizeRoom($request, $booking, 'Anda tidak berwenang reject booking ruangan ini.');

        $request->validate([
            'tu_note' => ['required', 'string', 'max:500'],
        ]);

        if ($booking->status !== 'PENDING') {
            return back()->withErrors(['msg' => 'Booking bukan status PENDING.']);
        }

        $booking->update(['status' => 'REJECTED', 'tu_note' => $request->tu_note]);
        $this->notifyApplicant($booking);

        return back()->with('status', 'Booking rejected.');
    }

    public function cancelApprove(Request $request, Booking $booking)
    {
        $this->authorizeRoom($request, $booking, 'Anda tidak berwenang membatalkan approval ruangan ini.');

        if ($booking->status !== 'APPROVED') {
            return back()->withErrors(['msg' => 'Hanya booking berstatus APPROVED yang bisa dibatalkan approvalnya.']);
        }

        $request->validate([
            'tu_note' => ['nullable', 'string', 'max:500'],
        ]);

        $booking->update([
            'status' => 'CANCELLED',
            'tu_note' => $request->tu_note ?: 'Approval dibatalkan oleh TU.',
        ]);
        $this->notifyApplicant($booking);

        return back()->with('status', 'Approval untuk "' . $booking->title . '" berhasil dibatalkan.');
    }

    // ================= HELPERS =================

    private function isAdmin(Request $request): bool
    {
        $u = $request->user();

        return $u && ($u->hasRole('Admin') || $u->hasRole('Super Admin') || $u->hasRole('Uji Semua Role'));
    }

    private function authorizeRoom(Request $request, Booking $booking, string $message): void
    {
        $tu = $request->user();

        if ($tu?->room_id && $booking->room_id !== $tu->room_id && !$this->isAdmin($request)) {
            abort(403, $message);
        }
    }

    private function makeCluster(array $items, $end): array
    {
        return [
            'room_id' => $items[0]->room_id,
            'room_name' => optional($items[0]->room)->name,
            'start' => $items[0]->start_at,
            'end' => $end,
            'items' => collect($items),
        ];
    }

    private function notifyApplicant(Booking $booking): void
    {
        if (empty($booking->applicant_email)) {
            return;
        }

        if (!$booking->relationLoaded('pic')) {
            $booking->load('pic');
        }

        try {
            Notification::route('mail', $booking->applicant_email)
                ->notify(new BookingStatusNotification($booking));
        } catch (\Exception $e) {
            Log::error('Email gagal: ' . $e->getMessage());
        }
    }
}