<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'name',
        'active',
        'maintenance',
        'maintenance_note',
    ];

    protected $casts = [
        'active' => 'boolean',
        'maintenance' => 'boolean',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // TU yang bertanggung jawab atas ruangan ini
    public function tuUser()
    {
        return $this->hasOne(\App\Models\User::class, 'room_id')
            ->whereHas('roles', fn($q) => $q->where('name', 'TU'));
    }
}