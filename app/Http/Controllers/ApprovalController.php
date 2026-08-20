<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Notifications\BookingStatusNotification;

class ApprovalController extends Controller
{
    public function index(Request $request)
    {
        $tu = auth()->user();
        $assignedRoomId = $tu->room_id;

        if (!$assignedRoomId) {
            return view('approvals.index', [
                'clusters' => collect(),
                'rooms' => collect(),
                'roomId' => null,
                'pendingCounts' => collect(),
                'assignedRoom' => null,
                'noRoom' => true,
                'riwayat' => collect(),
            ]);
        }

        $assignedRoom = Room::find($assignedRoomId);

        $pendingCounts = Booking::where('status', 'PENDING')
            ->where('room_id', $assignedRoomId)
            ->selectRaw('room_id, COUNT(*) as total')
            ->groupBy('room_id')
            ->pluck('total', 'room_id');

        $pending = Booking::with(['room', 'pic'])
            ->where('status', 'PENDING')
            ->where('room_id', $assignedRoomId)
            ->orderBy('start_at')
            ->get();

        // CLUSTERING OVERLAP
        $clusters = collect();
        $items = $pending->values();
        $current = [];
        $currentEnd = null;

        foreach ($items as $b) {
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
                $clusters->push([
                    'room_id' => $assignedRoomId,
                    'room_name' => optional($current[0]->room)->name,
                    'start' => $current[0]->start_at,
                    'end' => $currentEnd,
                    'items' => collect($current),
                ]);
                $current = [$b];
                $currentEnd = $b->end_at;
            }
        }

        if (!empty($current)) {
            $clusters->push([
                'room_id' => $assignedRoomId,
                'room_name' => optional($current[0]->room)->name,
                'start' => $current[0]->start_at,
                'end' => $currentEnd,
                'items' => collect($current),
            ]);
        }

        $clusters = $clusters->sortBy('start')->values();

        $riwayat = Booking::where('room_id', $assignedRoomId)
            ->whereIn('status', ['APPROVED', 'REJECTED', 'CANCELLED'])
            ->with(['pic', 'room'])
            ->orderBy('updated_at', 'desc')
            ->paginate(15);

        return view('approvals.index', [
            'clusters' => $clusters,
            'rooms' => collect([$assignedRoom]),
            'roomId' => $assignedRoomId,
            'pendingCounts' => $pendingCounts,
            'assignedRoom' => $assignedRoom,
            'noRoom' => false,
            'riwayat' => $riwayat,
        ]);
    }

    public function approve(Booking $booking)
    {
        $tu = auth()->user();

        if ($tu->room_id && $booking->room_id !== $tu->room_id) {
            abort(403, 'Anda tidak berwenang approve booking ruangan ini.');
        }

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

        if (!$booking->relationLoaded('pic'))
            $booking->load('pic');
        if (!empty($booking->applicant_email)) {
            try {
                Notification::route('mail', $booking->applicant_email)
                    ->notify(new BookingStatusNotification($booking));
            } catch (\Exception $e) {
                \Log::error('Email gagal: ' . $e->getMessage());
            }
        }

        foreach ($conflicts as $c) {
            $c->update([
                'status' => 'REJECTED',
                'tu_note' => 'Ditolak otomatis karena bentrok dengan booking APPROVED: "' . $booking->title . '".',
            ]);
            if (!empty($c->applicant_email)) {
                try {
                    Notification::route('mail', $c->applicant_email)
                        ->notify(new BookingStatusNotification($c));
                } catch (\Exception $e) {
                    \Log::error('Email gagal: ' . $e->getMessage());
                }
            }
        }

        return back()->with('status', 'Booking approved. Booking lain yang bentrok otomatis ditolak.');
    }

    public function reject(Request $request, Booking $booking)
    {
        $tu = auth()->user();

        if ($tu->room_id && $booking->room_id !== $tu->room_id) {
            abort(403, 'Anda tidak berwenang reject booking ruangan ini.');
        }

        $request->validate([
            'tu_note' => ['required', 'string', 'max:500'],
        ]);

        if ($booking->status !== 'PENDING') {
            return back()->withErrors(['msg' => 'Booking bukan status PENDING.']);
        }

        $booking->update(['status' => 'REJECTED', 'tu_note' => $request->tu_note]);

        if (!$booking->relationLoaded('pic'))
            $booking->load('pic');
        if (!empty($booking->applicant_email)) {
            try {
                Notification::route('mail', $booking->applicant_email)
                    ->notify(new BookingStatusNotification($booking));
            } catch (\Exception $e) {
                \Log::error('Email gagal: ' . $e->getMessage());
            }
        }

        return back()->with('status', 'Booking rejected.');
    }

    /**
     * Cancel approve — kembalikan status APPROVED → PENDING
     * POST /approvals/{booking}/cancel-approve
     */
    public function cancelApprove(Request $request, Booking $booking)
    {
        $tu = auth()->user();

        if ($tu->room_id && $booking->room_id !== $tu->room_id) {
            abort(403, 'Anda tidak berwenang membatalkan approval ruangan ini.');
        }

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

        // Notif ke pengaju bahwa booking dibatalkan
        if (!$booking->relationLoaded('pic'))
            $booking->load('pic');
        if (!empty($booking->applicant_email)) {
            try {
                Notification::route('mail', $booking->applicant_email)
                    ->notify(new BookingStatusNotification($booking));
            } catch (\Exception $e) {
                \Log::error('Email gagal: ' . $e->getMessage());
            }
        }

        return back()->with('status', 'Approval untuk "' . $booking->title . '" berhasil dibatalkan.');
    }
}