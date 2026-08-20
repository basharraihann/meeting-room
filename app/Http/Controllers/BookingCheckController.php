<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class BookingCheckController extends Controller
{
    /**
     * Cek apakah ada PENDING di ruangan + waktu yang sama.
     * Dipanggil via AJAX saat PIC klik tombol Kirim.
     * GET /api/bookings/check-conflict
     */
    public function checkConflict(Request $request)
    {
        $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'date' => ['required', 'date'],
            'start_time' => ['required'],
            'end_time' => ['required'],
        ]);

        $startAt = $request->date . ' ' . $request->start_time . ':00';
        $endAt = $request->date . ' ' . $request->end_time . ':00';

        $pending = Booking::where('room_id', $request->room_id)
            ->where('status', 'PENDING')
            ->where('start_at', '<', $endAt)
            ->where('end_at', '>', $startAt)
            ->first();

        if ($pending) {
            return response()->json([
                'conflict' => true,
                'message' => "Di waktu ini sudah ada pengajuan dari unit {$pending->unit_kerja} yang masih menunggu persetujuan. Tetap mau lanjut ngajuin?",
            ]);
        }

        return response()->json(['conflict' => false]);
    }

    public function availableSlots(Request $request)
    {
        // placeholder — bisa diisi nanti
    }
}