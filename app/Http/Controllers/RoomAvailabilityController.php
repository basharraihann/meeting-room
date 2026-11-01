<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RoomAvailabilityController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'date'  => ['required', 'date_format:Y-m-d'],
            'start' => ['required', 'date_format:H:i'],
            'end'   => ['required', 'date_format:H:i', 'after:start'],
        ], [
            'end.after' => 'Jam selesai harus setelah jam mulai.',
        ]);

        $start = Carbon::createFromFormat('Y-m-d H:i', $data['date'] . ' ' . $data['start']);
        $end   = Carbon::createFromFormat('Y-m-d H:i', $data['date'] . ' ' . $data['end']);

        // Tidak boleh mencari waktu yang sudah lewat
        if ($end->lte(now())) {
            return response()->json([
                'message' => 'Waktu yang dipilih sudah lewat.',
            ], 422);
        }

        // Booking yang beririsan dengan rentang waktu yang dicari
        $conflicts = Booking::whereIn('status', ['APPROVED', 'PENDING'])
            ->where('start_at', '<', $end)
            ->where('end_at', '>', $start)
            ->orderBy('start_at')
            ->get()
            ->groupBy('room_id');

        $rooms = Room::where('active', true)->ordered()->get();

        $free = [];
        $busy = [];
        $maintenance = [];

        foreach ($rooms as $room) {
            $item = [
                'id'    => $room->id,
                'name'  => $room->name,
                'color' => $room->color ?: '#6366f1',
            ];

            if ($room->maintenance) {
                $maintenance[] = $item;
                continue;
            }

            $list = $conflicts->get($room->id);

            if (!$list || $list->isEmpty()) {
                $free[] = $item;
                continue;
            }

            $item['conflicts'] = $list->map(fn ($b) => [
                'title'   => $b->title,
                'unit'    => $b->unit_kerja ?: '-',
                'time'    => Carbon::parse($b->start_at)->format('H.i') . ' – ' . Carbon::parse($b->end_at)->format('H.i'),
                'pending' => strtoupper($b->status) === 'PENDING',
            ])->values();

            $busy[] = $item;
        }

        return response()->json([
            'free'        => $free,
            'busy'        => $busy,
            'maintenance' => $maintenance,
        ]);
    }
}
