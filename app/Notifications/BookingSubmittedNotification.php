<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Booking;

class BookingSubmittedNotification extends Notification
{
    use Queueable;

    public $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Pengajuan Booking Baru')
            ->greeting('Halo TU,')
            ->line('Ada pengajuan booking baru.')
            ->line('Judul: ' . $this->booking->title)
            ->line('Ruang: ' . $this->booking->room->name)
            ->line('Mulai: ' . $this->booking->start_at)
            ->line('Selesai: ' . $this->booking->end_at)
            ->action('Lihat Approval', url('/approvals'))
            ->line('Terima kasih.');
    }
}
