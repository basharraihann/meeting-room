<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function pic()
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }

    protected $fillable = [
        'room_id',
        'room_name',  // ← tambah ini
        'pic_user_id',
        'applicant_email',
        'unit_kerja',
        'title',
        'description',
        'start_at',
        'end_at',
        'status',
        'tu_note',
        'cancel_reason',
        'canceled_at',
        'canceled_by',
    ];
}