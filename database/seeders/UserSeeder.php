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
     * Password awal akun BARU. Bisa diganti lewat .env (SEED_DEFAULT_PASSWORD).
     * WAJIB diganti setelah login pertama (Manajemen User > Password).
     */
    private const FALLBACK_PASSWORD = 'password123';

    public function run(): void
    {
        $isProduction = app()->environment('production');
        $password = env('SEED_DEFAULT_PASSWORD', self::FALLBACK_PASSWORD);

        // Di produksi, jangan pakai password bawaan yang mudah ditebak
        if ($isProduction && $password === self::FALLBACK_PASSWORD) {
            $this->command?->error('SEED_DEFAULT_PASSWORD belum diset di .env. Seeder dibatalkan di production.');

            return;
        }

        // Ruangan untuk akun TU (dibutuhkan fitur Approvals dan titik merah notifikasi)
        $roomId = Room::where('maintenance', false)->orderBy('id')->value('id');

        if (!$roomId) {
            $this->command?->warn('Belum ada ruangan. Jalankan RoomSeeder dulu agar akun TU punya ruangan.');
        }

        $accounts = [
            [
                'username' => 'admin',
                'name' => 'Administrator',
                'email' => 'admin@rupat.test',
                'roles' => ['Admin'],
            ],
            [
                // Username memengaruhi pilihan Unit Kerja di form Ajukan Rapat
                'username' => 'biro-mkdi',
                'name' => 'Biro MKDI',
                'email' => 'pic@rupat.test',
                'roles' => ['PIC'],
            ],
            [
                'username' => 'tu-uji',
                'name' => 'TU Uji Coba',
                'email' => 'tu@rupat.test',
                'phone' => '081234567890',
                'room_id' => $roomId,
                'roles' => ['TU'],
                'test' => true,
            ],
            [
                // Akun uji: bisa melihat semua menu (hanya untuk pengujian)
                'username' => 'superuser',
                'name' => 'Akun Uji Semua Role',
                'email' => 'superuser@rupat.test',
                'phone' => '081234567891',
                'room_id' => $roomId,
                'roles' => ['Admin', 'PIC', 'TU'],
                'test' => true,
            ],
        ];

        foreach ($accounts as $a) {
            // Akun uji tidak dibuat di production
            if (!empty($a['test']) && $isProduction) {
                continue;
            }

            $roles = $a['roles'];
            unset($a['roles'], $a['test']);

            // Migrasi lama "add_role_to_users" mungkin menambah kolom `role` biasa di tabel users.
            if (Schema::hasColumn('users', 'role')) {
                $a['role'] = $roles[0];
            }

            $user = User::firstOrNew(['username' => $a['username']]);

            if (!$user->exists) {
                // Akun baru: isi semua data + password awal
                $user->forceFill($a + [
                    'password' => Hash::make($password),
                    'email_verified_at' => now(),
                ])->save();
            }
            // Akun yang sudah ada TIDAK diubah (password, nama, email, penugasan ruangan tetap),
            // jadi aman dijalankan ulang tanpa menimpa perubahan dari Manajemen User.

            $user->syncRoles($roles);
        }
    }
}