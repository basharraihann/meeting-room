<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(public Booking $booking) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $booking = $this->booking;
        $room    = $booking->room?->name ?? '-';
        $pic     = $booking->pic?->name ?? '-';
        $start   = \Carbon\Carbon::parse($booking->start_at)->format('d M Y, H:i');
        $end     = \Carbon\Carbon::parse($booking->end_at)->format('H:i');

        return (new MailMessage)
            ->subject('📋 Pengajuan Booking Baru — ' . $booking->title)
            ->greeting('Halo!')
            ->line('Ada pengajuan booking ruang rapat baru yang perlu ditinjau.')
            ->line('**Judul:** ' . $booking->title)
            ->line('**Ruangan:** ' . $room)
            ->line('**Pengaju:** ' . $pic)
            ->line('**Waktu:** ' . $start . ' – ' . $end)
            ->line($booking->description ? '**Keterangan:** ' . $booking->description : '')
            ->action('Lihat & Proses Booking', url('/approvals'))
            ->line('Silakan approve atau reject pengajuan ini melalui sistem.');
    }
}
