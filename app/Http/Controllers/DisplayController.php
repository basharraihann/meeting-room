<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DisplayController extends Controller
{
    public function show(Request $request, ?int $roomId = null)
    {
        $room = $roomId ? Room::find($roomId) : null;

        $today = Carbon::today();

        $query = Booking::with('room')
            ->whereDate('start_at', $today)
            ->where('status', 'APPROVED')
            ->orderBy('start_at');

        if ($room) {
            $query->where('room_id', $room->id);
        }

        $todayBookings = $query->get();

        return view('display', compact('room', 'todayBookings'));
    }
}
