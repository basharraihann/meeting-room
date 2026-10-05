<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        // nama ruangan => warna (hex). Warna dicocokkan lewat NAMA, bukan id.
        $rooms = [
            'Ruang Rapat Utama' => '#1a1a1a',
            'Ruang Rapat Setmenko' => '#a855f7',
            'Ruang Rapat D1' => '#92400e',
            'Ruang Rapat D2' => '#facc15',
            'Ruang Rapat D3' => '#22d3ee',
            'Ruang Rapat D4' => '#ef4444',
            'Ruang Dharma Wanita' => '#ec4899',
            'Ruang Rapat ABT' => '#468432',
        ];

        $order = 0;

        foreach ($rooms as $name => $color) {
            Room::updateOrCreate(
                ['name' => $name],
                ['color' => $color, 'sort_order' => ++$order]
            );
        }
    }
}