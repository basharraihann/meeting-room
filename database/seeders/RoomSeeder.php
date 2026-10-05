<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Ruang Rapat Utama',
            'Ruang Rapat D1',
            'Ruang Rapat Setmenko',
            'Ruang Rapat D2',
            'Ruang Rapat D3',
            'Ruang Rapat D4',
            'Ruang Dharma Wanita',
            'Ruang Rapat ABT'
        ];

        foreach ($names as $name) {
            Room::updateOrCreate(
                ['name' => $name],
                ['name' => $name]
            );
        }
    }
}
