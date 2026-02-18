<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\Request;
use App\Notifications\BookingStatusNotification;

class ApprovalController extends Controller
{
    /**
     * Tampilkan inbox TU
     * Booking akan dikelompokkan per cluster bentrok (overlap)
     * Bisa difilter per ruangan: ?room_id=
     */
    public function index(Request $request)
    {
        $roomId = $request->query('room_id'); // nullable

        // daftar rooms buat sidebar
        $rooms = Room::orderBy('name')->get();

        // count pending per room (buat badge sidebar)
        $pendingCounts = Booking::where('status', 'PENDING')
            ->selectRaw('room_id, COUNT(*) as total')
            ->groupBy('room_id')
            ->pluck('total', 'room_id'); // [room_id => total]

        // ambil pending bookings (dengan filter room kalau dipilih)
        $pendingQuery = Booking::with(['room', 'pic'])
            ->where('status', 'PENDING')
            ->orderBy('room_id')
            ->orderBy('start_at');

        if ($roomId) {
            $pendingQuery->where('room_id', $roomId);
        }

        $pending = $pendingQuery->get();

        // ===============================
        // CLUSTERING OVERLAP PER ROOM
        // ===============================
        $groupedByRoom = $pending->groupBy('room_id');
        $clusters = collect();

        foreach ($groupedByRoom as $rid => $items) {
            $items = $items->values();
            $current = [];
            $currentEnd = null;

            foreach ($items as $b) {
                if (empty($current)) {
                    $current = [$b];
                    $currentEnd = $b->end_at;
                    continue;
                }

                // overlap jika start < currentEnd
                if ($b->start_at < $currentEnd) {
                    $current[] = $b;

                    if ($b->end_at > $currentEnd) {
                        $currentEnd = $b->end_at;
                    }
                } else {
                    $clusters->push([
                        'room_id' => $rid,
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
                    'room_id' => $rid,
                    'room_name' => optional($current[0]->room)->name,
                    'start' => $current[0]->start_at,
                    'end' => $currentEnd,
                    'items' => collect($current),
                ]);
            }
        }

        // Sort cluster berdasarkan waktu mulai
        $clusters = $clusters->sortBy('start')->values();

        return view('approvals.index', [
            'clusters' => $clusters,
            'rooms' => $rooms,
            'roomId' => $roomId,
            'pendingCounts' => $pendingCounts,
        ]);
    }

    /**
     * Approve booking
     * Otomatis reject booking lain yang bentrok
     */
    public function approve(Booking $booking)
    {
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

        // approve booking utama
        $booking->update([
            'status' => 'APPROVED',
            'tu_note' => null,
        ]);

        // notif ke PIC booking utama
        if ($booking->relationLoaded('pic') === false) {
            $booking->load('pic');
        }
        if ($booking->pic && !empty($booking->pic->email)) {
            $booking->pic->notify(new BookingStatusNotification($booking));
        }

        // auto reject yang bentrok + notif ke PIC masing-masing
        foreach ($conflicts as $c) {
            $c->update([
                'status' => 'REJECTED',
                'tu_note' => 'Ditolak otomatis karena bentrok dengan booking APPROVED: "' . $booking->title . '".'
            ]);

            if ($c->pic && !empty($c->pic->email)) {
                $c->pic->notify(new BookingStatusNotification($c));
            }
        }

        return back()->with('status', 'Booking approved. Booking lain yang bentrok otomatis ditolak.');
    }

    /**
     * Reject manual oleh TU
     */
    public function reject(Request $request, Booking $booking)
    {
        $request->validate([
            'tu_note' => ['required', 'string', 'max:500'],
        ]);

        if ($booking->status !== 'PENDING') {
            return back()->withErrors(['msg' => 'Booking bukan status PENDING.']);
        }

        $booking->update([
            'status' => 'REJECTED',
            'tu_note' => $request->tu_note,
        ]);

        // notif ke PIC booking yang direject
        if ($booking->relationLoaded('pic') === false) {
            $booking->load('pic');
        }
        if ($booking->pic && !empty($booking->pic->email)) {
            $booking->pic->notify(new BookingStatusNotification($booking));
        }

        return back()->with('status', 'Booking rejected.');
    }
}
