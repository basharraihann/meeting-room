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
        // Gabungkan date + time jika dikirim terpisah
        if ($request->filled('booking_date') && $request->filled('start_time')) {
            $request->merge([
                'start_at' => $request->booking_date . ' ' . $request->start_time . ':00',
            ]);
        }
        if ($request->filled('booking_date') && $request->filled('end_time')) {
            $request->merge([
                'end_at' => $request->booking_date . ' ' . $request->end_time . ':00',
            ]);
        }

        // Normalize format dari hidden input (T → spasi)
        if ($request->filled('start_at')) {
            $request->merge(['start_at' => str_replace('T', ' ', $request->start_at)]);
        }
        if ($request->filled('end_at')) {
            $request->merge(['end_at' => str_replace('T', ' ', $request->end_at)]);
        }

        $data = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_at' => ['required', 'date_format:Y-m-d H:i:s'],
            'end_at' => ['required', 'date_format:Y-m-d H:i:s', 'after:start_at'],
        ]);

        // Cek bentrok dengan booking APPROVED
        $conflict = Booking::where('room_id', $data['room_id'])
            ->where('status', 'APPROVED')
            ->where('start_at', '<', $data['end_at'])
            ->where('end_at', '>', $data['start_at'])
            ->exists();

        if ($conflict) {
            return back()
                ->withErrors(['start_at' => 'Jadwal bentrok dengan booking yang sudah disetujui. Pilih jam atau ruang lain.'])
                ->withInput();
        }

        $booking = Booking::create([
            'room_id' => $data['room_id'],
            'pic_user_id' => $request->user()->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'],
            'status' => 'PENDING',
        ]);

        // Notif email ke semua TU
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

        return redirect()->route('calendar')
            ->with('status', 'Pengajuan ruang rapat berhasil terkirim. Silakan cek kembali di riwayat pengajuan.');
    }

    public function cancel(Request $request, Booking $booking)
    {
        if ($booking->pic_user_id !== auth()->id()) {
            abort(403, 'Tidak boleh cancel booking orang lain.');
        }

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