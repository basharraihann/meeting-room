<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\BookingSubmittedNotification;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
        ]);

        // bentrok dengan APPROVED
        $conflict = Booking::where('room_id', $data['room_id'])
            ->where('status', 'APPROVED')
            ->where('start_at', '<', $data['end_at'])
            ->where('end_at', '>', $data['start_at'])
            ->exists();

        if ($conflict) {
            return back()
                ->withErrors(['start_at' => 'Jadwal bentrok dengan booking APPROVED. Pilih jam/ruang lain.'])
                ->withInput();
        }

        // create booking
        $booking = Booking::create([
            'room_id' => $data['room_id'],
            'pic_user_id' => $request->user()->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'],
            'status' => 'PENDING',
        ]);

        // ====== NOTIF EMAIL ke semua TU ======
        $tuUsers = User::role('TU')->get();

        foreach ($tuUsers as $tu) {
            if (!empty($tu->email)) {
                try {
                    $tu->notify(new BookingSubmittedNotification($booking));
                } catch (\Exception $e) {
                    \Log::error('Email gagal ke ' . $tu->email . ': ' . $e->getMessage());
                }
            }
        }
        // ====================================

        return redirect()->route('calendar')
            ->with('status', 'Pengajuan ruang rapat berhasil terkirim. Silakan cek kembali di riwayat pengajuan.');
    }

    public function cancel(Request $request, Booking $booking)
    {
        // pastiin PIC cuma cancel booking milik dia
        if ($booking->pic_user_id !== auth()->id()) {
            abort(403, 'Tidak boleh cancel booking orang lain.');
        }

        // cuma boleh cancel kalau masih pending/approved
        if (!in_array($booking->status, ['PENDING', 'APPROVED'])) {
            return back()->withErrors(['cancel_reason' => 'Booking ini tidak bisa dibatalkan.']);
        }

        $data = $request->validate([
            'cancel_reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $booking->update([
            'status' => 'CANCELLED',
            'cancel_reason' => $data['cancel_reason'],
            'canceled_at' => now(),
            'canceled_by' => auth()->id(),
        ]);

        return back()->with('status', 'Booking berhasil dibatalkan.');
    }
}