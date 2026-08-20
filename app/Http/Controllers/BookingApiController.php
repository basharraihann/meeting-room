<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class BookingApiController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date'],
            'room_id' => ['nullable', 'integer'],
        ]);

        $start = $request->query('start');
        $end = $request->query('end');
        $roomId = $request->query('room_id');

        $bookings = Booking::with(['room', 'pic'])
            ->where('status', 'APPROVED')
            ->where('start_at', '<', $end)
            ->where('end_at', '>', $start)
            ->when($roomId, fn($q) => $q->where('room_id', $roomId))
            ->get();

        return $bookings->map(function ($b) {
            return [
                'id' => $b->id,
                'title' => $b->title, // ✅ hanya judul rapat
                'start' => $b->start_at,
                'end' => $b->end_at,
                'extendedProps' => [
                    'status' => $b->status,
                    'pic' => $b->unit_kerja ?? $b->pic?->name,
                    'unit_kerja' => $b->unit_kerja,
                    'description' => $b->description,
                    'room_name' => $b->room?->name,
                    'room_id' => $b->room_id,
                ],
            ];
        });
    }
}