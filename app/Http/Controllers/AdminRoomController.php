<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class AdminRoomController extends Controller
{
    /**
     * Toggle status maintenance sebuah ruangan.
     * PATCH /admin/rooms/{room}/maintenance
     */
    public function toggleMaintenance(Request $request, Room $room)
    {
        $request->validate([
            'maintenance'      => ['required', 'boolean'],
            'maintenance_note' => ['nullable', 'string', 'max:255'],
        ]);

        $room->update([
            'maintenance'      => $request->boolean('maintenance'),
            'maintenance_note' => $request->boolean('maintenance')
                ? ($request->maintenance_note ?: 'Sedang dalam perbaikan')
                : null,
        ]);

        $status = $room->maintenance ? 'dinonaktifkan (perbaikan)' : 'diaktifkan kembali';

        return back()->with('success', "Ruangan {$room->name} berhasil {$status}.");
    }
}
