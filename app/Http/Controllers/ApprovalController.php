<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\Request;
use App\Notifications\BookingStatusNotification;

class ApprovalController extends Controller
{
    /**
     * Tampilkan inbox TU — hanya room yang ditugaskan ke TU ini
     */
    public function index(Request $request)
    {
        $tu = auth()->user();

        // TU hanya bisa handle room yang ditugaskan
        // Kalau room_id null = belum ditugaskan, tampilkan kosong
        $assignedRoomId = $tu->room_id;

        if (!$assignedRoomId) {
            return view('approvals.index', [
                'clusters' => collect(),
                'rooms' => collect(),
                'roomId' => null,
                'pendingCounts' => collect(),
                'assignedRoom' => null,
                'noRoom' => true,
            ]);
        }

        $assignedRoom = Room::find($assignedRoomId);

        // count pending hanya untuk room ini
        $pendingCounts = Booking::where('status', 'PENDING')
            ->where('room_id', $assignedRoomId)
            ->selectRaw('room_id, COUNT(*) as total')
            ->groupBy('room_id')
            ->pluck('total', 'room_id');

        // ambil pending bookings untuk room ini saja
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

        return view('approvals.index', [
            'clusters' => $clusters,
            'rooms' => collect([$assignedRoom]),
            'roomId' => $assignedRoomId,
            'pendingCounts' => $pendingCounts,
            'assignedRoom' => $assignedRoom,
            'noRoom' => false,
        ]);
    }

    /**
     * Approve booking — pastikan TU hanya bisa approve room miliknya
     */
    public function approve(Booking $booking)
    {
        $tu = auth()->user();

        if ($tu->room_id && $booking->room_id !== $tu->room_id) {
            abort(403, 'Anda tidak berwenang approve booking ruangan ini.');
        }

        if ($booking->status !== 'PENDING') {
            return back()->withErrors(['msg' => 'Booking bukan status PENDING.']);
        }

        // cari booking lain yang bentrok
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
        if ($booking->pic && !empty($booking->pic->email)) {
            $booking->pic->notify(new BookingStatusNotification($booking));
        }

        foreach ($conflicts as $c) {
            $c->update([
                'status' => 'REJECTED',
                'tu_note' => 'Ditolak otomatis karena bentrok dengan booking APPROVED: "' . $booking->title . '".',
            ]);
            if ($c->pic && !empty($c->pic->email)) {
                $c->pic->notify(new BookingStatusNotification($c));
            }
        }

        return back()->with('status', 'Booking approved. Booking lain yang bentrok otomatis ditolak.');
    }

    /**
     * Reject manual — pastikan TU hanya bisa reject room miliknya
     */
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
        if ($booking->pic && !empty($booking->pic->email)) {
            $booking->pic->notify(new BookingStatusNotification($booking));
        }

        return back()->with('status', 'Booking rejected.');
    }
}