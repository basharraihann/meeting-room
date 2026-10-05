<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserSeeder extends Seeder
{
    /**
     * Password awal semua akun di bawah. WAJIB diganti setelah login pertama
     * (menu Manajemen User > Password), terutama di server produksi.
     */
    private const DEFAULT_PASSWORD = 'password123';

    public function run(): void
    {
        // Ruangan untuk akun TU (dibutuhkan fitur Approvals dan titik merah notifikasi)
        $roomId = Room::where('maintenance', false)->orderBy('id')->value('id');

        $accounts = [
            [
                'username' => 'admin',
                'name'     => 'Administrator',
                'email'    => 'admin@rupat.test',
                'roles'    => ['Admin'],
            ],
            [
                // Username memengaruhi pilihan Unit Kerja di form Ajukan Rapat
                'username' => 'biro-mkdi',
                'name'     => 'Biro MKDI',
                'email'    => 'pic@rupat.test',
                'roles'    => ['PIC'],
            ],
            [
                'username' => 'tu-uji',
                'name'     => 'TU Uji Coba',
                'email'    => 'tu@rupat.test',
                'phone'    => '081234567890',
                'room_id'  => $roomId,
                'roles'    => ['TU'],
            ],
            [
                // Akun uji: bisa melihat semua menu (hanya untuk pengujian)
                'username' => 'superuser',
                'name'     => 'Akun Uji Semua Role',
                'email'    => 'superuser@rupat.test',
                'phone'    => '081234567891',
                'room_id'  => $roomId,
                'roles'    => ['Admin', 'PIC', 'TU'],
            ],
        ];

        foreach ($accounts as $a) {
            $roles = $a['roles'];
            unset($a['roles']);

            // Migrasi lama "add_role_to_users" mungkin menambah kolom `role` biasa di tabel users.
            // Kalau kolomnya ada (dan tidak boleh kosong), isi dengan role pertama supaya insert tidak gagal.
            if (Schema::hasColumn('users', 'role')) {
                $a['role'] = $roles[0];
            }

            // firstOrNew + forceFill: aman dijalankan berulang dan tidak
            // bergantung pada isi $fillable di model User.
            $user = User::firstOrNew(['username' => $a['username']]);
            $user->forceFill($a + [
                'password'          => Hash::make(self::DEFAULT_PASSWORD),
                'email_verified_at' => now(),
            ])->save();

            $user->syncRoles($roles);
        }
    }
}