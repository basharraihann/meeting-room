<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use App\Notifications\BookingSubmittedNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function store(Request $request)
    {
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

        if ($request->filled('start_at')) {
            $request->merge(['start_at' => str_replace('T', ' ', $request->start_at)]);
        }
        if ($request->filled('end_at')) {
            $request->merge(['end_at' => str_replace('T', ' ', $request->end_at)]);
        }

        $data = $request->validate([
            'room_id' => [
                'required',
                Rule::exists('rooms', 'id')->where(function ($query) {
                    $query->where('active', true)
                        ->where('maintenance', false);
                }),
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'applicant_email' => ['required', 'email'],
            'unit_kerja' => ['required', 'string', 'max:100'],
            'start_at' => ['required', 'date_format:Y-m-d H:i:s'],
            'end_at' => ['required', 'date_format:Y-m-d H:i:s', 'after:start_at'],
            'confirm_pending' => ['nullable', 'boolean'],
        ], [
            'room_id.exists' => 'Ruangan yang dipilih tidak tersedia untuk booking (sedang maintenance atau tidak aktif). Silakan pilih ruangan lain.',
        ]);

        // Cek tebar jala - unit kerja sudah punya PENDING di waktu yang sama
        $tebarJala = Booking::where('unit_kerja', $data['unit_kerja'])
            ->where('status', 'PENDING')
            ->where('start_at', '<', $data['end_at'])
            ->where('end_at', '>', $data['start_at'])
            ->exists();

        if ($tebarJala) {
            return back()
                ->withErrors(['start_at' => 'Unit kerja Anda sudah memiliki pengajuan yang sedang menunggu persetujuan di waktu yang sama. Harap tunggu hingga pengajuan sebelumnya diproses.'])
                ->withInput();
        }

        // Cek bentrok langsung dengan booking APPROVED
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

        // Cek jeda 15 menit pembersihan ruangan
        $startWithBuffer = Carbon::parse($data['start_at'])->subMinutes(15)->format('Y-m-d H:i:s');

        $tooClose = Booking::where('room_id', $data['room_id'])
            ->where('status', 'APPROVED')
            ->where('end_at', '>', $startWithBuffer)
            ->where('end_at', '<=', $data['start_at'])
            ->exists();

        if ($tooClose) {
            return back()
                ->withErrors(['start_at' => 'Waktu mulai terlalu dekat dengan booking sebelumnya. Diperlukan jeda minimal 15 menit untuk pembersihan ruangan.'])
                ->withInput();
        }

        // Cek pengajuan PENDING dari unit lain di ruangan & waktu yang sama
        $pendingLain = Booking::where('room_id', $data['room_id'])
            ->where('status', 'PENDING')
            ->where('unit_kerja', '!=', $data['unit_kerja'])
            ->where('start_at', '<', $data['end_at'])
            ->where('end_at', '>', $data['start_at'])
            ->orderBy('created_at')
            ->get(['unit_kerja', 'title', 'start_at', 'end_at']);

        // Beri tahu dulu; simpan hanya setelah user konfirmasi
        if ($pendingLain->isNotEmpty() && !$request->boolean('confirm_pending')) {
            return back()
                ->withInput($request->except('confirm_pending'))
                ->with('pending_warning', $pendingLain->map(fn($b) => [
                    'unit_kerja' => $b->unit_kerja,
                    'title' => $b->title,
                    'start_at' => Carbon::parse($b->start_at)->format('d M Y H:i'),
                    'end_at' => Carbon::parse($b->end_at)->format('H:i'),
                ])->all());
        }

        $room = Room::findOrFail($data['room_id']);

        $booking = Booking::create([
            'room_id' => $room->id,
            'room_name' => $room->name,
            'pic_user_id' => $request->user()->id,
            'applicant_email' => $data['applicant_email'],
            'unit_kerja' => $data['unit_kerja'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'],
            'status' => 'PENDING',
        ]);

        $tuUsers = User::role('TU')
            ->where('room_id', $booking->room_id)
            ->whereNotNull('email')
            ->get();

        if ($tuUsers->isNotEmpty()) {
            try {
                Notification::send($tuUsers, new BookingSubmittedNotification($booking));
            } catch (\Exception $e) {
                Log::error('Gagal mengirim notifikasi email booking: ' . $e->getMessage());
            }
        }

        $message = 'Pengajuan ruang rapat berhasil terkirim. Silakan cek kembali di riwayat pengajuan.';

        if ($pendingLain->isNotEmpty()) {
            $message .= ' Perhatian: pengajuan Anda bersaing dengan pengajuan lain di waktu yang sama dan bisa ditolak jika pengajuan tersebut disetujui lebih dulu.';
        }

        return redirect()->route('calendar')->with('status', $message);
    }
    public function cancel(Request $request, Booking $booking)
    {
        // Pengecekan autorisasi pengaju
        if ($booking->pic_user_id !== $request->user()->id) {
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
            'canceled_by' => $request->user()->id,
        ]);

        return back()->with('status', 'Booking berhasil dibatalkan.');
    }
}