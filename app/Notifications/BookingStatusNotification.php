<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Booking;

class BookingStatusNotification extends Notification
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
            ->subject('Status Booking Anda')
            ->greeting('Halo PIC,')
            ->line('Pengajuan booking Anda telah diperbarui.')
            ->line('Judul: ' . $this->booking->title)
            ->line('Status: ' . $this->booking->status)
            ->action('Lihat Booking Saya', url('/my-bookings'))
            ->line('Terima kasih.');
    }
}
