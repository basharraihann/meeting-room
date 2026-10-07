<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Seeder agenda (booking) untuk dashboard ruang rapat.
 *
 * Jalankan (sebaiknya pada jam kerja 08.00 - 16.00):
 *   php artisan db:seed --class=BookingSeeder
 *
 * Isi data:
 *  - HARI INI: semua ruangan aktif (non-maintenance) punya agenda, tapi dengan
 *    pola berbeda supaya "Rekomendasi Ruangan" bervariasi (lihat $patterns).
 *  - 7 hari lalu s/d 14 hari ke depan (kecuali hari ini, dan weekend):
 *    agenda acak dengan status campuran APPROVED / PENDING / REJECTED / CANCELED.
 *  - PENDING BENTROK: beberapa slot waktu (hari kerja ke depan) diisi 2-3 pengajuan
 *    PENDING pada ruangan & jam yang sama, untuk menguji halaman Approval Inbox.
 *
 * Agenda APPROVED tidak bentrok dalam satu ruangan. Satu-satunya bentrok yang
 * disengaja adalah antar-PENDING pada slot yang sama (lihat seedPendingConflicts).
 */
class BookingSeeder extends Seeder
{
    /** Set true untuk menghapus SEMUA booking sebelum seeding. */
    private const RESET = false;

    /** Hapus booking yang mulai hari ini sebelum mengisi pola hari ini. */
    private const CLEAR_TODAY = true;

    /** Jumlah slot waktu yang diisi pengajuan PENDING bentrok. */
    private const CONFLICT_SLOTS = 4;

    private const JAM_BUKA = 8;     // 08.00
    private const JAM_TUTUP = 17;   // 17.00

    private array $busy = [];       // [room_id][Y-m-d] => [[start, end], ...]

    private array $units = [
        'Biro MKDI',
        'Biro Umum',
        'Biro Hukum',
        'Biro Perencanaan',
        'Deputi Bidang Koordinasi Ketersediaan Pangan',
        'Deputi Bidang Koordinasi Keterjangkauan Pangan',
        'Deputi Bidang Koordinasi Keamanan Pangan',
        'Inspektorat',
        'Sekretariat Deputi',
        'Bagian Keuangan',
    ];

    private array $titles = [
        'Rapat Koordinasi Ketahanan Pangan',
        'Evaluasi Capaian Triwulan',
        'Sinkronisasi Program Kerja',
        'Rapat Persiapan Rakor Nasional',
        'Pembahasan Anggaran',
        'Monitoring Stok Pangan Daerah',
        'Sosialisasi Peraturan Baru',
        'Rapat Tim Penyusun Laporan',
        'Diskusi Kebijakan Harga Pangan',
        'Briefing Mingguan',
        'Workshop Digitalisasi Layanan',
        'Rapat Pimpinan',
        'Koordinasi Lintas Kementerian',
        'Review Dokumen Perencanaan',
    ];

    private array $descriptions = [
        'Membahas progres kegiatan dan kendala di lapangan.',
        'Peserta diharapkan membawa bahan paparan masing-masing.',
        'Rapat dilaksanakan secara tatap muka, notulen akan dibagikan.',
        'Agenda: pembukaan, paparan, diskusi, kesimpulan.',
        null,
    ];

    public function run(): void
    {
        if (self::RESET) {
            Booking::query()->delete();
        }

        $rooms = Room::where('active', true)->where('maintenance', false)->ordered()->get();
        if ($rooms->isEmpty()) {
            $this->command?->warn('Tidak ada ruangan aktif. Jalankan RoomSeeder dulu.');
            return;
        }

        $pics = $this->picUsers();
        if ($pics->isEmpty()) {
            $this->command?->warn('Tidak ada user. Buat user (role PIC) dulu.');
            return;
        }

        $this->seedToday($rooms, $pics);
        $this->seedOtherDays($rooms, $pics);
        $this->seedPendingConflicts($rooms, $pics);

        $this->command?->info('BookingSeeder selesai: ' . Booking::count() . ' agenda di database.');
    }

    /**
     * Agenda hari ini di SEMUA ruangan, dengan jam kosong yang bervariasi.
     *
     *   A, F : sedang dipakai sekarang                -> tidak direkomendasikan
     *   B    : kosong, rapat berikutnya 45 menit lagi -> kosong 45 m
     *   C    : kosong, rapat berikutnya 2 jam lagi    -> kosong 2 j
     *   D    : kosong, rapat berikutnya 20 menit lagi -> tidak (kurang dari 30 m)
     *   E    : hanya rapat pagi yang sudah selesai    -> kosong sampai jam kerja berakhir
     *   G    : kosong, rapat berikutnya 3 jam lagi    -> kosong 3 j
     *
     * Pola diulang berurutan jika ruangan lebih dari 7.
     */
    private function seedToday($rooms, $pics): void
    {
        $now = now();
        $today = $now->copy()->startOfDay();
        $close = $now->copy()->setTime(self::JAM_TUTUP, 0);

        if ($now->greaterThanOrEqualTo($close->copy()->subMinutes(30))) {
            $this->command?->warn('Sudah mendekati/lewat jam tutup (17.00). Rekomendasi tidak akan tampil. Jalankan saat jam kerja.');
        }

        if (self::CLEAR_TODAY) {
            Booking::whereBetween('start_at', [$today, $today->copy()->endOfDay()])->delete();
            $this->busy = [];
        }

        // Dibulatkan ke bawah 15 menit agar jam terlihat rapi
        $n = $now->copy()->second(0)->minute(intdiv($now->minute, 15) * 15);

        // Format item: ['rel', menit dari $n, durasi, status]
        //              ['abs', [jam, menit], durasi, status]  (hanya dipakai jika sudah selesai)
        //              ['now', menit dari sekarang, durasi, status]
        $patterns = [
            'A' => [['rel', -45, 90, 'APPROVED'], ['rel', 150, 60, 'APPROVED'], ['abs', [8, 0], 60, 'APPROVED']],
            'B' => [['rel', 45, 90, 'APPROVED'], ['rel', 240, 60, 'PENDING'], ['abs', [8, 30], 60, 'APPROVED']],
            'C' => [['rel', 120, 60, 'APPROVED'], ['abs', [8, 0], 90, 'APPROVED']],
            'D' => [['now', 20, 60, 'APPROVED'], ['rel', 150, 60, 'APPROVED']],
            'E' => [['abs', [8, 0], 60, 'APPROVED'], ['abs', [9, 30], 60, 'APPROVED']],
            'F' => [['rel', -15, 60, 'APPROVED'], ['rel', 90, 60, 'APPROVED']],
            'G' => [['rel', 180, 60, 'APPROVED'], ['abs', [8, 30], 60, 'APPROVED']],
        ];
        $keys = array_keys($patterns);

        foreach ($rooms->values() as $i => $room) {
            $key = $keys[$i % count($keys)];

            foreach ($patterns[$key] as [$type, $arg, $dur, $status]) {
                if ($type === 'abs') {
                    $start = $today->copy()->setTime($arg[0], $arg[1]);
                    // Agenda pagi hanya dipakai jika sudah selesai sebelum sekarang,
                    // supaya tidak menabrak pola ruangan.
                    if ($start->copy()->addMinutes($dur)->greaterThan($now)) {
                        continue;
                    }
                } elseif ($type === 'now') {
                    $start = $now->copy()->second(0)->addMinutes($arg);
                } else {
                    $start = $n->copy()->addMinutes($arg);
                }

                $end = $start->copy()->addMinutes($dur);
                if ($end->greaterThan($close)) {
                    $end = $close->copy();
                }
                if ($end->lessThanOrEqualTo($start) || $start->greaterThanOrEqualTo($close)) {
                    continue;
                }
                if ($this->overlaps($room->id, $start, $end)) {
                    continue;
                }

                $this->make($room, $pics, $status, $start, $end);
            }

            $this->command?->line(sprintf('  %-28s pola %s', $room->name, $key));
        }
    }

    /** Agenda acak 7 hari lalu s/d 14 hari ke depan, kecuali hari ini & weekend. */
    private function seedOtherDays($rooms, $pics): void
    {
        $today = now()->startOfDay();

        for ($offset = -7; $offset <= 14; $offset++) {
            if ($offset === 0) {
                continue;
            }

            $day = $today->copy()->addDays($offset);
            if ($day->isWeekend()) {
                continue;
            }

            $jumlah = random_int(1, 4);

            for ($i = 0; $i < $jumlah; $i++) {
                $room = $rooms->random();
                $durs = [30, 60, 60, 90, 120];
                $dur = $durs[array_rand($durs)];

                $slot = $this->findSlot($room->id, $day, $dur);
                if (!$slot) {
                    continue;
                }

                $status = $offset < 0
                    ? 'APPROVED'
                    : $this->weighted(['APPROVED' => 60, 'PENDING' => 25, 'REJECTED' => 10, 'CANCELLED' => 5]);

                $this->make($room, $pics, $status, $slot[0], $slot[1]);
            }
        }
    }

    /**
     * Pengajuan PENDING yang BENTROK: beberapa PIC berbeda meminta ruangan dan
     * jam yang persis sama. Slot dipilih di hari kerja ke depan pada waktu yang
     * masih kosong (tidak menabrak APPROVED), lalu diisi 2-3 pengajuan sekaligus.
     *
     * Hasilnya muncul sebagai satu kelompok "Bentrok" di Approval Inbox.
     */
    private function seedPendingConflicts($rooms, $pics): void
    {
        $today = now()->startOfDay();

        // Hari kerja 1-14 hari ke depan, diacak
        $days = collect(range(1, 14))
            ->map(fn($o) => $today->copy()->addDays($o))
            ->reject(fn(Carbon $d) => $d->isWeekend())
            ->shuffle()
            ->values();

        if ($days->isEmpty()) {
            return;
        }

        $roomPool = $rooms->shuffle()->values();
        $sizes = [2, 3, 2, 2];          // jumlah pengajuan per slot bentrok
        $durs = [60, 90, 120];
        $made = 0;

        for ($i = 0; $i < self::CONFLICT_SLOTS; $i++) {
            $room = $roomPool[$i % $roomPool->count()];
            $day = $days[$i % $days->count()];
            $dur = $durs[array_rand($durs)];

            $slot = $this->findSlot($room->id, $day, $dur);
            if (!$slot) {
                continue;
            }

            [$start, $end] = $slot;
            $size = $sizes[$i % count($sizes)];

            // Pemohon, judul, dan unit kerja dibuat berbeda-beda per pengajuan
            $applicants = $pics->shuffle()->values();
            $titles = collect($this->titles)->shuffle()->values();
            $units = collect($this->units)->shuffle()->values();

            for ($k = 0; $k < $size; $k++) {
                $this->make(
                    $room,
                    $pics,
                    'PENDING',
                    $start,
                    $end,
                    pic: $applicants[$k % $applicants->count()],
                    register: $k === 0, // slot cukup dicatat sekali
                    meta: [
                        'title' => $titles[$k % $titles->count()],
                        'unit_kerja' => $units[$k % $units->count()],
                        // Waktu diajukan dibuat berbeda agar urutan masuk terlihat
                        'created_at' => now()->subMinutes(random_int(10, 60 * 36)),
                    ],
                );
            }

            $made++;
            $this->command?->line(sprintf(
                '  Bentrok: %-26s %s %s-%s (%d pengajuan)',
                $room->name,
                $start->format('d M'),
                $start->format('H.i'),
                $end->format('H.i'),
                $size
            ));
        }

        $this->command?->info("  {$made} slot PENDING bentrok dibuat.");
    }

    /**
     * Buat satu booking dan catat jamnya supaya tidak bentrok.
     *
     * @param  User|null  $pic       Pemohon tertentu (default: acak dari $pics)
     * @param  bool       $register  Catat jam sebagai terpakai (false untuk bentrok yang disengaja)
     * @param  array      $meta      Override kolom (title, unit_kerja, created_at, ...)
     */
    private function make(
        $room,
        $pics,
        string $status,
        Carbon $start,
        Carbon $end,
        ?User $pic = null,
        bool $register = true,
        array $meta = [],
    ): void {
        $pic ??= $pics->random();

        // APPROVED/PENDING memakai ruangan; REJECTED/CANCELED tidak
        if ($register && in_array($status, ['APPROVED', 'PENDING'], true)) {
            $this->busy[$room->id][$start->toDateString()][] = [$start->copy(), $end->copy()];
        }

        Booking::unguarded(function () use ($room, $pic, $status, $start, $end, $meta) {
            Booking::create(array_merge([
                'room_id' => $room->id,
                'pic_user_id' => $pic->id,
                'title' => $this->titles[array_rand($this->titles)],
                'unit_kerja' => $this->units[array_rand($this->units)],
                'description' => $this->descriptions[array_rand($this->descriptions)],
                'start_at' => $start,
                'end_at' => $end,
                'status' => $status,
                'tu_note' => $status === 'REJECTED'
                    ? 'Ruangan dipakai untuk kegiatan pimpinan, mohon pilih jadwal lain.'
                    : null,
            ], $meta));
        });
    }

    /** Cari slot kosong (kelipatan 30 menit) di ruangan & hari tertentu. */
    private function findSlot(int|string $roomId, Carbon $day, int $minutes): ?array
    {
        for ($try = 0; $try < 12; $try++) {
            $startMin = random_int(self::JAM_BUKA * 2, self::JAM_TUTUP * 2 - intdiv($minutes, 30)) * 30;
            $start = $day->copy()->startOfDay()->addMinutes($startMin);
            $end = $start->copy()->addMinutes($minutes);

            if ($end->hour > self::JAM_TUTUP || ($end->hour === self::JAM_TUTUP && $end->minute > 0)) {
                continue;
            }
            if (!$this->overlaps($roomId, $start, $end)) {
                return [$start, $end];
            }
        }

        return null;
    }

    private function overlaps(int|string $roomId, Carbon $start, Carbon $end): bool
    {
        foreach ($this->busy[$roomId][$start->toDateString()] ?? [] as [$s, $e]) {
            if ($start < $e && $end > $s) {
                return true;
            }
        }

        // Cek juga booking yang sudah ada di database
        return Booking::where('room_id', $roomId)
            ->whereIn('status', ['APPROVED', 'PENDING'])
            ->where('start_at', '<', $end)
            ->where('end_at', '>', $start)
            ->exists();
    }

    /** User dengan role PIC; kalau tidak ada, pakai semua user. */
    private function picUsers()
    {
        try {
            if (method_exists(User::class, 'role')) {
                $pics = User::role('PIC')->get();      // spatie/laravel-permission
                if ($pics->isNotEmpty()) {
                    return $pics;
                }
            }
        } catch (\Throwable $e) {
            // fallback di bawah
        }

        return User::all();
    }

    private function weighted(array $weights): string
    {
        $r = random_int(1, array_sum($weights));
        foreach ($weights as $key => $w) {
            if (($r -= $w) <= 0) {
                return $key;
            }
        }
        return array_key_first($weights);
    }
}