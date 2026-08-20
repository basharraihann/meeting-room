<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingStatusNotification extends Notification
{
    use Queueable;

    public function __construct(public Booking $booking) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $booking  = $this->booking;
        $room     = $booking->room?->name ?? '-';
        $start    = \Carbon\Carbon::parse($booking->start_at)->format('d M Y, H:i');
        $end      = \Carbon\Carbon::parse($booking->end_at)->format('H:i');
        $approved = $booking->status === 'APPROVED';

        $mail = (new MailMessage)
            ->subject($approved
                ? '✅ Booking Disetujui — ' . $booking->title
                : '❌ Booking Ditolak — ' . $booking->title)
            ->greeting('Halo!')
            ->line($approved
                ? 'Kabar baik! Pengajuan booking ruang rapat kamu telah **disetujui**.'
                : 'Mohon maaf, pengajuan booking ruang rapat kamu **ditolak**.')
            ->line('**Judul:** ' . $booking->title)
            ->line('**Ruangan:** ' . $room)
            ->line('**Waktu:** ' . $start . ' – ' . $end);

        if (!$approved && $booking->tu_note) {
            $mail->line('**Alasan Penolakan:** ' . $booking->tu_note);
        }

        return $mail
            ->action('Lihat Riwayat Pengajuan', url('/my-bookings'))
            ->line('Terima kasih telah menggunakan Sistem Manajemen Rapat.');
    }
}
