<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        Room::insert([
            [
                'name' => 'Ruang Rapat Utama',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ruang Rapat KDKMP',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ruang Rapat Setmenko',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ruang Rapat D2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ruang Rapat D3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ruang Rapat D4',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
