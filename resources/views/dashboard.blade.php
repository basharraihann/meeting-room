@php
    use Carbon\Carbon;

    $now = now();
    $range = in_array((int) request('range'), [3, 7, 14]) ? (int) request('range') : 3;
    $rangeStart = $now->copy()->startOfDay();
    $rangeEnd = $now->copy()->addDays($range - 1)->endOfDay();

    $rangeBookings = \App\Models\Booking::with('room')
        ->where('status', 'APPROVED')
        ->whereBetween('start_at', [$rangeStart, $rangeEnd])
        ->orderBy('start_at')
        ->get();

    $todayBookings = $rangeBookings->filter(fn($b) => Carbon::parse($b->start_at)->isSameDay($now))->values();
    $upcomingByDay = $rangeBookings
        ->reject(fn($b) => Carbon::parse($b->start_at)->isSameDay($now))
        ->groupBy(fn($b) => Carbon::parse($b->start_at)->toDateString());

    $rooms = \App\Models\Room::where('active', true)->ordered()->get();
    // Status tiap kegiatan
    $statusOf = function ($b) use ($now) {
        $s = Carbon::parse($b->start_at);
        $e = Carbon::parse($b->end_at);
        if ($now->between($s, $e))
            return ['live', 'Sedang Berlangsung', 'bg-emerald-50 text-emerald-700', 'bg-emerald-500'];
        if ($now->greaterThan($e))
            return ['done', 'Selesai', 'bg-slate-100 text-slate-500', 'bg-slate-400'];
        if ($now->diffInMinutes($s) <= 60)
            return ['upcoming', 'Segera', 'bg-amber-50 text-amber-600', 'bg-amber-500'];
        return ['upcoming', 'Terjadwal', 'bg-sky-50 text-sky-700', 'bg-sky-500'];
    };

    $liveBookings = $todayBookings->filter(fn($b) => $statusOf($b)[0] === 'live')->values();
    $liveBooking = $liveBookings->first();
    $nextBooking = \App\Models\Booking::with('room')->where('status', 'APPROVED')
        ->where('start_at', '>', $now)->orderBy('start_at')->first();

    $roomColors = \App\Models\Room::pluck('color', 'id')
        ->filter()   // buang null/kosong supaya fallback '#6366f1' dipakai
        ->all();
    $isPic = auth()->user()->hasRole('PIC');

    // ===== WhatsApp TU (nomor diambil dari Room::tuUser -> users.phone) =====
    $normalizePhone = function (?string $p) {
        $p = preg_replace('/\D+/', '', (string) $p);
        if ($p === '')
            return null;
        if (str_starts_with($p, '0'))
            return '62' . substr($p, 1);
        if (str_starts_with($p, '8'))
            return '62' . $p;
        return $p;
    };

    // Link WA ke TU yang bertanggung jawab atas ruangan booking tsb (null jika tidak ada nomor)
    $waLink = function ($booking, string $text) use ($normalizePhone) {
        $wa = $normalizePhone($booking->room?->tuUser?->phone);
        return $wa ? 'https://wa.me/' . $wa . '?text=' . rawurlencode($text) : null;
    };

    // ===== Rekomendasi ruangan kosong (untuk booking mendadak) =====
    $jamTutup = 17; // jam kerja berakhir pukul 17.00
    $closeTime = $now->copy()->setTime($jamTutup, 0);
    $afterHours = $now->greaterThanOrEqualTo($closeTime);
    $minDurasi = 30; // ruangan baru direkomendasikan jika kosong minimal 30 menit

    // Kegiatan hari ini yang belum selesai (APPROVED dan PENDING dianggap memakai ruangan)
    $dayBlocks = \App\Models\Booking::whereIn('status', ['APPROVED', 'PENDING'])
        ->whereBetween('start_at', [$now->copy()->startOfDay(), $now->copy()->endOfDay()])
        ->where('end_at', '>', $now)
        ->orderBy('start_at')
        ->get();

    $recommendations = $afterHours ? collect() : $rooms
        ->filter(fn($r) => !$r->maintenance)
        ->map(function ($r) use ($dayBlocks, $now, $closeTime) {
            $mine = $dayBlocks->where('room_id', $r->id);
            $busy = $mine->first(fn($b) => Carbon::parse($b->start_at)->lessThanOrEqualTo($now));
            if ($busy)
                return null;
            $next = $mine->first(fn($b) => Carbon::parse($b->start_at)->greaterThan($now));
            $until = $next ? Carbon::parse($next->start_at) : $closeTime;
            return ['room' => $r, 'until' => $until, 'minutes' => (int) $now->diffInMinutes($until), 'whole' => !$next];
        })
        ->filter(fn($x) => $x && $x['minutes'] >= $minDurasi)
        ->sortByDesc('minutes')
        ->values()
        ->take(3);

    $fmtDur = fn($m) => trim(($m >= 60 ? intdiv($m, 60) . ' j ' : '') . ($m % 60 ? ($m % 60) . ' m' : ''));

    // ===== Pengajuan saya (khusus PIC) =====
    // [label, kelas badge, kelas titik]
    $myStyles = [
        'PENDING' => ['Menunggu', 'bg-amber-50 text-amber-800 ring-amber-200', 'bg-amber-500'],
        'APPROVED' => ['Disetujui', 'bg-emerald-50 text-emerald-800 ring-emerald-200', 'bg-emerald-500'],
        'REJECTED' => ['Ditolak', 'bg-rose-50 text-rose-800 ring-rose-200', 'bg-rose-500'],
        'CANCELED' => ['Dibatalkan', 'bg-slate-100 text-slate-700 ring-slate-200', 'bg-slate-400'],
    ];
    $myBookings = $isPic
        ? \App\Models\Booking::with('room.tuUser')
            ->where('pic_user_id', auth()->id())
            ->where('end_at', '>=', $rangeStart)
            ->latest()                // pengajuan terbaru dulu
            ->orderByDesc('id')
            ->get()
        : collect();

    $myItems = $myBookings->map(function ($b) use ($myStyles, $roomColors, $waLink) {
        $key = strtoupper($b->status);
        if ($key === 'CANCELLED')
            $key = 'CANCELED';
        [$label, $badge, $dot] = $myStyles[$key] ?? [$b->status, 'bg-slate-100 text-slate-700 ring-slate-200', 'bg-slate-400'];
        $roomLabel = $b->room?->name ?? $b->room_name ?? '-';
        $start = Carbon::parse($b->start_at);
        $end = Carbon::parse($b->end_at);

        $waText = "Halo Admin TU, saya ingin menanyakan pengajuan rapat berikut:\n"
            . "Kegiatan: {$b->title}\n"
            . "Unit Kerja: " . ($b->unit_kerja ?: '-') . "\n"
            . "Ruangan: {$roomLabel}\n"
            . "Waktu: " . $start->isoFormat('dddd, D MMMM Y') . ', ' . $start->format('H.i') . ' – ' . $end->format('H.i') . "\n"
            . "Status: {$label}";

        return [
            'id' => $b->id,
            'status' => $key,
            'label' => $label,
            'badge' => $badge,
            'dot' => $dot,
            'title' => $b->title,
            'room' => $roomLabel,
            'unit' => $b->unit_kerja ?: '-',
            'color' => $roomColors[$b->room_id] ?? '#6366f1',
            'time' => $start->format('H.i') . ' – ' . $end->format('H.i'),
            'date' => $start->isoFormat('ddd, D MMM'),
            'note' => $key === 'REJECTED' ? ($b->tu_note ?? null) : null,
            // Chat TU hanya untuk pengajuan yang masih menunggu
            'wa' => $key === 'PENDING' ? $waLink($b, $waText) : null,
            'tu' => $b->room?->tuUser?->name,
        ];
    })->values();

    $myCounts = [
        'all' => $myItems->count(),
        'PENDING' => $myItems->where('status', 'PENDING')->count(),
        'APPROVED' => $myItems->where('status', 'APPROVED')->count(),
        'REJECTED' => $myItems->where('status', 'REJECTED')->count(),
    ];

    // [key, label, kelas aktif]
    $myFilters = [
        ['all', 'Semua', 'bg-slate-900 text-white ring-slate-900'],
        ['PENDING', 'Menunggu', 'bg-amber-50 text-amber-800 ring-amber-300'],
        ['APPROVED', 'Disetujui', 'bg-emerald-50 text-emerald-800 ring-emerald-300'],
        ['REJECTED', 'Ditolak', 'bg-rose-50 text-rose-800 ring-rose-300'],
    ];

    $tabs = ['all' => 'Semua', 'live' => 'Berlangsung', 'upcoming' => 'Akan Datang'];
    $tabCounts = ['all' => $todayBookings->count()];
    foreach (['live', 'upcoming'] as $k) {
        $tabCounts[$k] = $todayBookings->filter(fn($b) => $statusOf($b)[0] === $k)->count();
    }

    // Payload detail untuk modal
    $payload = function ($b) use ($statusOf, $roomColors) {
        $st = $statusOf($b);
        return [
            'title' => $b->title,
            'room' => $b->room?->name ?? '-',
            'color' => $roomColors[$b->room_id] ?? '#6366f1',
            'date' => Carbon::parse($b->start_at)->isoFormat('dddd, D MMMM Y'),
            'time' => Carbon::parse($b->start_at)->format('H.i') . ' – ' . Carbon::parse($b->end_at)->format('H.i'),
            'unit' => $b->unit_kerja ?: '-',
            'desc' => $b->description ?? '',
            'status' => $st[1],
            'statusCls' => $st[2],
        ];
    };

    // ===== Kalender mini (navigasi bulan di sisi browser: bulan lalu s/d +3 bulan) =====
    $calMin = $now->copy()->startOfMonth()->subMonthNoOverflow();
    $calMax = $now->copy()->startOfMonth()->addMonthsNoOverflow(3)->endOfMonth();

    $calBookings = \App\Models\Booking::with('room')
        ->where('status', 'APPROVED')
        ->whereBetween('start_at', [$calMin, $calMax])
        ->orderBy('start_at')
        ->get();

    $calItems = $calBookings->map(function ($b) use ($roomColors, $payload) {
        $st = Carbon::parse($b->start_at);
        $en = Carbon::parse($b->end_at);
        return [
            'id' => $b->id,
            'date' => $st->toDateString(),
            'time' => $st->format('H.i') . ' – ' . $en->format('H.i'),
            'title' => $b->title,
            'room_id' => $b->room_id,
            'room' => $b->room?->name ?? '-',
            'color' => $roomColors[$b->room_id] ?? '#6366f1',
            'unit' => $b->unit_kerja ?: '-',
            'detail' => $payload($b),
        ];
    })->values();

    $calCfg = [
        'items' => $calItems,
        'rooms' => $rooms->map(fn($r) => [
            'id' => $r->id,
            'name' => $r->name,
            'color' => $r->color ?: '#6366f1',
            'maint' => (bool) $r->maintenance,
        ])->values(),
        'today' => $now->toDateString(),
        'min' => [$calMin->year, $calMin->month - 1],
        'max' => [$calMax->year, $calMax->month - 1],
    ];
@endphp

<x-app-layout>
    <div class="space-y-4 px-4 pb-8 pt-4 sm:px-8" x-data="{ d: null }" @keydown.escape.window="d = null">
        {{-- ===== BANNER JUDUL ===== --}}
        <div class="space-y-4">
            <div
                class="relative flex items-center gap-3 overflow-hidden rounded-xl border border-white/70 bg-white/80 px-4 py-3 shadow-sm backdrop-blur sm:gap-4">
                <span
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-500">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                </span>
                <div class="min-w-0 flex-1">
                    <h1 class="text-base font-extrabold leading-tight text-[#0f1e5a] sm:text-lg">Dashboard Ruang Rapat
                    </h1>
                    <p class="mt-0.5 text-xs text-slate-500">Pantau jadwal hari ini, status pengajuan, dan ketersediaan
                        ruangan.</p>
                </div>

                @if($isPic)
                    <a href="{{ route('calendar') }}"
                        class="relative z-10 shrink-0 rounded-lg bg-indigo-600 px-3.5 py-2 text-xs font-bold text-white shadow-md shadow-indigo-600/25 transition hover:bg-indigo-700">
                        + Ajukan Rapat
                    </a>
                @endif
            </div>

            {{-- ===== Hero: sedang berlangsung / berikutnya ===== --}}
            @php
                $hero = $liveBooking ?? $nextBooking;
                $heroLive = (bool) $liveBooking;
            @endphp

            @if($hero)
                <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-5">
                        <div class="min-w-0 flex-1">
                            <div
                                class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-widest {{ $heroLive ? 'text-emerald-600' : 'text-indigo-500' }}">
                                @if($heroLive)
                                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span> Sedang Berlangsung
                                    @if($liveBookings->count() > 1)
                                        <span
                                            class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] normal-case tracking-normal text-slate-600">+{{ $liveBookings->count() - 1 }}
                                            lainnya</span>
                                    @endif
                                @else
                                    Rapat Berikutnya
                                @endif
                            </div>

                            <div class="mt-2 text-lg font-bold leading-snug text-slate-900 sm:text-xl">{{ $hero->title }}
                            </div>
                            <div class="mt-2 text-sm text-slate-700">
                                <span class="text-slate-400">Pengusul</span>
                                <span class="font-semibold">{{ $hero->unit_kerja ?: '-' }}</span>
                            </div>
                            <div class="mt-0.5 text-sm text-slate-500">
                                {{ $hero->room?->name }} ·
                                @unless($heroLive)
                                    {{ Carbon::parse($hero->start_at)->isoFormat('dddd, D MMM') }} ·
                                @endunless
                                {{ Carbon::parse($hero->start_at)->format('H.i') }} –
                                {{ Carbon::parse($hero->end_at)->format('H.i') }}
                            </div>

                            @if($heroLive)
                                <div class="mt-4 h-1.5 max-w-md overflow-hidden rounded-full bg-slate-100">
                                    <div id="hero-progress" class="h-full rounded-full bg-emerald-500 transition-all"
                                        style="width:0%"></div>
                                </div>
                            @endif
                        </div>

                        <div class="text-right">
                            <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                                {{ $heroLive ? 'Selesai dalam' : 'Mulai dalam' }}
                            </div>
                            <div id="hero-countdown"
                                class="mt-1 text-2xl font-bold tabular-nums text-slate-900 sm:text-3xl">--
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    (function () {
                        const live = @json($heroLive);
                        const start = new Date("{{ Carbon::parse($hero->start_at)->toIso8601String() }}");
                        const end = new Date("{{ Carbon::parse($hero->end_at)->toIso8601String() }}");
                        const cd = document.getElementById('hero-countdown');
                        const bar = document.getElementById('hero-progress');

                        function tick() {
                            const now = new Date();
                            const diff = Math.max(0, (live ? end : start) - now);
                            const dd = Math.floor(diff / 86400000);
                            const h = Math.floor((diff % 86400000) / 3600000);
                            const m = Math.floor((diff % 3600000) / 60000);
                            const s = Math.floor((diff % 60000) / 1000);

                            cd.textContent = (dd ? dd + 'h ' : '') + (h ? h + 'j ' : '') + m + 'm' + (dd ? '' : ' ' + s + 'd');
                            if (bar) bar.style.width = Math.min(100, Math.max(0, (now - start) / (end - start) * 100)) + '%';

                            if (diff > 0) setTimeout(tick, 1000);
                            else cd.textContent = live ? 'Selesai' : 'Dimulai';
                        }
                        tick();
                    })();
                </script>
            @endif

            <div class="grid gap-5 xl:grid-cols-3">

                {{-- ===== Kolom kiri ===== --}}
                <div class="space-y-5 xl:col-span-2">

                    {{-- Pengajuan saya (khusus PIC) --}}
                    @if($isPic)
                        <div id="pengajuan-saya" class="rounded-2xl border border-slate-200 bg-white p-5" x-data="{
                                                                                                                items: @js($myItems),
                                                                                                                f: 'all',
                                                                                                                get filtered() { return this.items.filter(i => this.f === 'all' || i.status === this.f) },
                                                                                                                get shown() { return this.filtered.slice(0, 5) },
                                                                                                                toggle(k) { this.f = (this.f === k ? 'all' : k) },
                                                                                                                get moreUrl() { return '{{ route('my_bookings.index') }}' + (this.f === 'all' ? '' : '?status=' + this.f) }
                                                                                                            }">
                            <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <h2 class="text-base font-bold text-slate-900">Pengajuan Saya</h2>
                                    <p class="text-xs text-slate-500">5 pengajuan terbaru Anda yang belum lewat</p>
                                </div>
                                <div class="flex flex-wrap items-center gap-1.5">
                                    @foreach($myFilters as [$key, $lbl, $on])
                                        <button type="button" @click="toggle('{{ $key }}')"
                                            :class="f === '{{ $key }}' ? '{{ $on }}' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'"
                                            class="inline-flex items-center gap-2 rounded-lg px-3 py-1.5 text-xs font-semibold ring-1 ring-inset transition">
                                            {{ $lbl }}
                                            <span
                                                class="rounded-md bg-black/5 px-1.5 text-[11px] font-bold tabular-nums">{{ $myCounts[$key] }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <template x-for="i in shown" :key="i.id">
                                <div
                                    class="flex flex-col gap-2 border-t border-slate-100 py-3 sm:flex-row sm:items-center sm:gap-5">
                                    <div class="w-32 shrink-0">
                                        <div class="text-sm font-bold tabular-nums text-slate-900" x-text="i.time"></div>
                                        <div class="text-xs text-slate-500" x-text="i.date"></div>
                                    </div>
                                    <div class="flex min-w-0 flex-1 items-center gap-3">
                                        <span class="h-9 w-1 shrink-0 rounded-full" :style="'background:' + i.color"></span>
                                        <div class="min-w-0">
                                            <div class="truncate text-sm font-semibold text-slate-900" x-text="i.title">
                                            </div>
                                            <div class="truncate text-xs text-slate-500" x-text="i.room + ' · ' + i.unit">
                                            </div>
                                            <template x-if="i.note">
                                                <div class="truncate text-xs text-rose-600"
                                                    x-text="'Catatan TU: ' + i.note">
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    <div class="flex shrink-0 items-center gap-2">
                                        <span
                                            class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-semibold ring-1 ring-inset"
                                            :class="i.badge">
                                            <span class="h-1.5 w-1.5 rounded-full" :class="i.dot"></span>
                                            <span x-text="i.label"></span>
                                        </span>
                                        <template x-if="i.wa">
                                            <a :href="i.wa" target="_blank" rel="noopener"
                                                :title="i.tu ? 'Hubungi ' + i.tu + ' via WhatsApp' : 'Hubungi TU via WhatsApp'"
                                                class="inline-flex items-center gap-1.5 rounded-md bg-emerald-600 px-3 py-1 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"
                                                    aria-hidden="true">
                                                    <path
                                                        d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
                                                </svg>
                                                Chat TU
                                            </a>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <div x-show="filtered.length === 0" x-cloak class="border-t border-slate-100 py-8 text-center">
                                <div class="text-sm font-semibold text-slate-700"
                                    x-text="f === 'all' ? 'Belum ada pengajuan' : 'Tidak ada pengajuan dengan status ini'">
                                </div>
                                <p class="mt-1 text-xs text-slate-500">Pengajuan rapat yang Anda buat akan muncul di sini.
                                </p>
                            </div>

                            <div class="flex items-center justify-between border-t border-slate-100 pt-3">
                                <span class="text-xs text-slate-400"
                                    x-text="'Menampilkan ' + shown.length + ' dari ' + filtered.length + ' pengajuan'"></span>
                                <a :href="moreUrl" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Lihat
                                    selengkapnya
                                    →</a>
                            </div>
                        </div>
                    @endif

                    {{-- Jadwal hari ini --}}
                    <div id="jadwal-hari-ini" class="rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="mb-4">
                            <h2 class="text-base font-bold text-slate-900">Jadwal Hari Ini</h2>
                            <p class="text-xs text-slate-500">{{ $now->isoFormat('dddd, D MMMM Y') }}</p>
                        </div>

                        @if($todayBookings->isNotEmpty())
                            <div class="mb-3 space-y-3">
                                {{-- Pencarian (hanya di HP, karena search topbar disembunyikan) --}}
                                <input id="local-search" type="search" placeholder="Cari kegiatan atau ruangan..."
                                    class="w-full rounded-xl border-slate-200 bg-slate-50 py-2 text-sm placeholder-slate-400 focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100 sm:hidden">
                                @php
                                    $tabOn = [
                                        'all' => 'bg-slate-900 text-white ring-slate-900',
                                        'live' => 'bg-emerald-50 text-emerald-800 ring-emerald-300',
                                        'upcoming' => 'bg-sky-50 text-sky-800 ring-sky-300',
                                    ];
                                    $tabOff = 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50';
                                @endphp
                                <div class="flex flex-wrap gap-1.5" id="status-tabs">
                                    @foreach($tabs as $key => $lbl)
                                        <button type="button" data-filter="{{ $key }}" data-on="{{ $tabOn[$key] }}"
                                            data-off="{{ $tabOff }}"
                                            class="status-tab inline-flex items-center gap-2 rounded-lg px-3 py-1.5 text-xs font-semibold ring-1 ring-inset transition {{ $key === 'all' ? $tabOn[$key] : $tabOff }}">
                                            {{ $lbl }}
                                            <span
                                                class="rounded-md bg-black/5 px-1.5 text-[11px] font-bold tabular-nums">{{ $tabCounts[$key] }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <div class="divide-y divide-slate-100">
                                @foreach($todayBookings as $b)
                                    @php
                                        [$key, $label, $cls, $dot] = $statusOf($b);
                                        $c = $roomColors[$b->room_id] ?? '#6366f1';
                                    @endphp
                                    <div class="schedule-row flex cursor-pointer flex-col gap-2 py-3 transition hover:bg-slate-50 sm:flex-row sm:items-center sm:gap-5 {{ $key === 'done' ? 'opacity-50' : '' }}"
                                        data-status="{{ $key }}"
                                        data-search="{{ strtolower($b->title . ' ' . ($b->room?->name ?? '') . ' ' . ($b->unit_kerja ?? '')) }}"
                                        x-on:click="d = @js($payload($b))">

                                        <div
                                            class="w-28 shrink-0 text-sm font-bold tabular-nums {{ $key === 'live' ? 'text-emerald-600' : 'text-slate-900' }}">
                                            {{ Carbon::parse($b->start_at)->format('H.i') }} –
                                            {{ Carbon::parse($b->end_at)->format('H.i') }}
                                        </div>

                                        <div class="flex min-w-0 flex-1 items-center gap-3">
                                            <span class="h-9 w-1 shrink-0 rounded-full" style="background: {{ $c }}"></span>
                                            <div class="min-w-0">
                                                <div class="truncate text-sm font-semibold text-slate-900">{{ $b->title }}</div>
                                                <div class="truncate text-xs text-slate-500">{{ $b->room?->name ?? '-' }} ·
                                                    {{ $b->unit_kerja ?: '-' }}
                                                </div>
                                            </div>
                                        </div>

                                        <span
                                            class="inline-flex w-fit shrink-0 items-center gap-2 whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold {{ $cls }}">
                                            <span
                                                class="h-1.5 w-1.5 rounded-full {{ $dot }} {{ $key === 'live' ? 'animate-pulse' : '' }}"></span>{{ $label }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>

                            <div id="no-result" class="hidden py-10 text-center text-sm text-slate-400">Tidak ada kegiatan
                                yang
                                cocok.</div>
                        @else
                            <div class="py-12 text-center">
                                <div class="text-base font-semibold text-slate-800">Tidak ada kegiatan hari ini</div>
                                <p class="mt-1 text-sm text-slate-500">Semua ruangan kosong. Ajukan rapat atau cek jadwal
                                    hari
                                    lain.</p>
                                @if($isPic)
                                    <a href="{{ route('calendar') }}"
                                        class="mt-4 inline-block rounded-full bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">+
                                        Ajukan Rapat</a>
                                @endif
                            </div>
                        @endif

                        <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3">
                            <span class="text-xs text-slate-400">{{ $todayBookings->count() }} kegiatan hari ini</span>
                            <a href="{{ route('calendar') }}"
                                class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Lihat Kalender →</a>
                        </div>
                    </div>
                </div>

                {{-- ===== Kolom kanan ===== --}}
                <div class="h-fit space-y-5">

                    {{-- Rekomendasi ruangan --}}
                    <div id="rekomendasi-ruangan" class="rounded-2xl border border-indigo-200 bg-indigo-50/50 p-5">
                        <div class="mb-3">
                            <h2 class="text-base font-bold text-slate-900">Rekomendasi Ruangan</h2>
                            <p class="text-xs text-slate-500">Kosong sekarang, untuk rapat mendadak ·
                                {{ $now->format('H.i') }}
                            </p>
                        </div>

                        @if($afterHours)
                            <p class="rounded-xl bg-white p-4 text-center text-sm text-slate-500">Sudah di luar jam kerja.
                                Silakan ajukan untuk hari berikutnya.</p>
                        @elseif($recommendations->isEmpty())
                            <p class="rounded-xl bg-white p-4 text-center text-sm text-slate-500">Tidak ada ruangan yang
                                kosong
                                minimal {{ $minDurasi }} menit saat ini.</p>
                        @else
                            <div class="space-y-2">
                                @foreach($recommendations as $rec)
                                    @php $c = $roomColors[$rec['room']->id] ?? '#6366f1'; @endphp
                                    <div
                                        class="rounded-xl bg-white p-3.5 {{ $loop->first ? 'ring-2 ring-indigo-300' : 'border border-slate-100' }}">
                                        <div class="flex items-center gap-3">
                                            <span class="h-3 w-3 shrink-0 rounded-full" style="background: {{ $c }}"></span>
                                            <div class="min-w-0 flex-1">
                                                <div class="truncate text-sm font-semibold text-slate-900">
                                                    {{ $rec['room']->name }}
                                                </div>
                                                <div class="text-xs text-slate-500">
                                                    @if($rec['whole'])
                                                        Kosong sampai jam kerja berakhir ({{ $rec['until']->format('H.i') }})
                                                    @else
                                                        Kosong sampai {{ $rec['until']->format('H.i') }}
                                                    @endif
                                                    · {{ $fmtDur($rec['minutes']) }}
                                                </div>
                                            </div>
                                            @if($loop->first)
                                                <span
                                                    class="shrink-0 rounded-full bg-indigo-600 px-2.5 py-1 text-[11px] font-semibold text-white">Terlama</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if($isPic)
                                <a href="{{ route('calendar') }}"
                                    class="mt-3 block rounded-xl bg-indigo-600 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-indigo-700">Ajukan
                                    Sekarang</a>
                            @endif
                        @endif
                    </div>

                    {{-- Kalender mini --}}
                    <div id="kalender-ruangan" class="rounded-2xl border border-slate-200 bg-white p-5">
                        <script>
                            window.miniCal = function (cfg) {
                                const pad = n => String(n).padStart(2, '0');
                                const [ty, tm] = cfg.today.split('-').map(Number);
                                const idx = (y, m) => y * 12 + m;
                                return {
                                    items: cfg.items, rooms: cfg.rooms, today: cfg.today,
                                    y: ty, m: tm - 1, selected: cfg.today, room: null,
                                    get shownItems() { return this.room === null ? this.items : this.items.filter(i => i.room_id === this.room); },
                                    get byDate() {
                                        const map = {};
                                        this.shownItems.forEach(i => { (map[i.date] = map[i.date] || []).push(i); });
                                        return map;
                                    },
                                    get label() { return new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric' }).format(new Date(this.y, this.m, 1)); },
                                    get cells() {
                                        const first = (new Date(this.y, this.m, 1).getDay() + 6) % 7; // Senin = 0
                                        const days = new Date(this.y, this.m + 1, 0).getDate();
                                        const out = [];
                                        for (let i = 0; i < first; i++) out.push({ d: null, key: 'e' + i, colors: [] });
                                        for (let d = 1; d <= days; d++) {
                                            const key = this.y + '-' + pad(this.m + 1) + '-' + pad(d);
                                            const list = this.byDate[key] || [];
                                            out.push({ d, key, colors: [...new Set(list.map(i => i.color))].slice(0, 4) });
                                        }
                                        return out;
                                    },
                                    get dayItems() { return this.byDate[this.selected] || []; },
                                    get dayLabel() {
                                        const [a, b, c] = this.selected.split('-').map(Number);
                                        return new Intl.DateTimeFormat('id-ID', { weekday: 'long', day: 'numeric', month: 'long' }).format(new Date(a, b - 1, c));
                                    },
                                    get canPrev() { return idx(this.y, this.m) > idx(cfg.min[0], cfg.min[1]); },
                                    get canNext() { return idx(this.y, this.m) < idx(cfg.max[0], cfg.max[1]); },
                                    get offToday() { return this.selected !== this.today || idx(this.y, this.m) !== idx(ty, tm - 1); },
                                    prev() { if (!this.canPrev) return; if (--this.m < 0) { this.m = 11; this.y--; } },
                                    next() { if (!this.canNext) return; if (++this.m > 11) { this.m = 0; this.y++; } },
                                    goToday() { this.y = ty; this.m = tm - 1; this.selected = this.today; },
                                    toggleRoom(id) { this.room = (this.room === id ? null : id); },
                                };
                            };
                        </script>

                        <div x-data="miniCal(@js($calCfg))">
                            {{-- Header bulan --}}
                            <div class="mb-3 flex items-center justify-between gap-2">
                                <button type="button" @click="prev()" :disabled="!canPrev"
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-600 transition hover:bg-slate-200 disabled:cursor-not-allowed disabled:opacity-40"
                                    aria-label="Bulan sebelumnya">‹</button>
                                <div class="text-center">
                                    <h2 class="text-base font-bold capitalize text-slate-900" x-text="label"></h2>
                                    <button type="button" x-show="offToday" x-cloak @click="goToday()"
                                        class="text-[11px] font-semibold text-indigo-600 hover:text-indigo-800">Kembali
                                        ke
                                        hari ini</button>
                                </div>
                                <button type="button" @click="next()" :disabled="!canNext"
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-600 transition hover:bg-slate-200 disabled:cursor-not-allowed disabled:opacity-40"
                                    aria-label="Bulan berikutnya">›</button>
                            </div>

                            {{-- Filter ruangan --}}
                            <div class="mb-3 flex flex-wrap gap-1.5">
                                <button type="button" @click="room = null"
                                    :class="room === null ? 'bg-indigo-600 text-white ring-indigo-600' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'"
                                    class="rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 ring-inset transition">Semua</button>
                                <template x-for="r in rooms" :key="r.id">
                                    <button type="button" @click="toggleRoom(r.id)"
                                        :class="room === r.id ? 'bg-slate-900 text-white ring-slate-900' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'"
                                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 ring-inset transition">
                                        <span class="h-2 w-2 rounded-full" :style="'background:' + r.color"></span>
                                        <span x-text="r.name.replace('Ruang Rapat ', '').replace('Ruang ', '')"></span>
                                        <span x-show="r.maint" class="text-[10px] font-medium opacity-60">BPK</span>
                                    </button>
                                </template>
                            </div>

                            {{-- Grid kalender --}}
                            <div class="grid grid-cols-7 text-center text-[11px] font-semibold text-slate-400">
                                <div class="py-1">Sen</div>
                                <div class="py-1">Sel</div>
                                <div class="py-1">Rab</div>
                                <div class="py-1">Kam</div>
                                <div class="py-1">Jum</div>
                                <div class="py-1">Sab</div>
                                <div class="py-1">Min</div>
                            </div>
                            <div class="grid grid-cols-7 gap-y-0.5">
                                <template x-for="(c, i) in cells" :key="c.key">
                                    <button type="button" :disabled="!c.d" @click="c.d && (selected = c.key)" :class="[
                                        c.d ? '' : 'invisible',
                                        selected === c.key ? 'bg-indigo-600 text-white' :
                                            (c.key === today ? 'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200' :
                                                (i % 7 > 4 ? 'text-slate-400 hover:bg-slate-50' : 'text-slate-700 hover:bg-slate-50'))
                                    ]"
                                        class="mx-auto flex h-10 w-10 flex-col items-center justify-center rounded-full text-xs font-semibold transition">
                                        <span x-text="c.d"></span>
                                        <span class="mt-0.5 flex h-1 items-center gap-0.5">
                                            <template x-for="col in c.colors" :key="col">
                                                <span class="h-1 w-1 rounded-full" :style="'background:' + col"></span>
                                            </template>
                                        </span>
                                    </button>
                                </template>
                            </div>

                            {{-- Kegiatan pada tanggal terpilih --}}
                            <div class="mt-3 border-t border-slate-200 pt-3">
                                <div class="mb-2.5 flex items-center justify-between gap-2">
                                    <span
                                        class="inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-slate-600">
                                        <svg class="h-3.5 w-3.5 text-indigo-500" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" aria-hidden="true">
                                            <path
                                                d="M8 7V3m8 4V3M5 11h14M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z" />
                                        </svg>
                                        <span x-text="dayLabel"></span>
                                    </span>
                                    <span
                                        class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600"
                                        x-text="dayItems.length + ' kegiatan'"></span>
                                </div>

                                <div class="max-h-72 space-y-2 overflow-y-auto pr-0.5">
                                    <template x-for="i in dayItems" :key="i.id">
                                        <button type="button" @click="d = i.detail"
                                            class="group flex w-full items-stretch gap-3 rounded-xl border border-slate-200 bg-white p-2.5 text-left shadow-sm transition hover:border-indigo-300 hover:bg-indigo-50/40 hover:shadow">
                                            <span class="w-1 shrink-0 rounded-full"
                                                :style="'background:' + i.color"></span>

                                            <span class="min-w-0 flex-1">
                                                <span class="block truncate text-sm font-bold text-slate-900"
                                                    x-text="i.title"></span>

                                                <span class="mt-1 flex items-center gap-1.5 text-xs text-slate-600">
                                                    <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" viewBox="0 0 24 24"
                                                        fill="none" stroke="currentColor" stroke-width="2"
                                                        stroke-linecap="round" stroke-linejoin="round"
                                                        aria-hidden="true">
                                                        <circle cx="12" cy="12" r="9" />
                                                        <path d="M12 7v5l3 2" />
                                                    </svg>
                                                    <span class="font-semibold tabular-nums" x-text="i.time"></span>
                                                </span>

                                                <span class="mt-0.5 flex items-center gap-1.5 text-xs text-slate-500">
                                                    <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" viewBox="0 0 24 24"
                                                        fill="none" stroke="currentColor" stroke-width="2"
                                                        stroke-linecap="round" stroke-linejoin="round"
                                                        aria-hidden="true">
                                                        <path d="M12 21s-7-6.2-7-11a7 7 0 1 1 14 0c0 4.8-7 11-7 11Z" />
                                                        <circle cx="12" cy="10" r="2.5" />
                                                    </svg>
                                                    <span class="truncate" x-text="i.room"></span>
                                                </span>

                                                <span x-show="i.unit && i.unit !== '-'"
                                                    class="mt-1.5 inline-flex max-w-full items-center gap-1.5 rounded-md bg-indigo-50 px-2 py-0.5 text-[11px] font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-100">
                                                    <svg class="h-3 w-3 shrink-0" viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round" aria-hidden="true">
                                                        <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z" />
                                                        <path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2" />
                                                        <path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2" />
                                                        <path d="M10 6h4M10 10h4M10 14h4M10 18h4" />
                                                    </svg>
                                                    <span class="truncate" x-text="i.unit"></span>
                                                </span>
                                            </span>

                                            <svg class="h-4 w-4 shrink-0 self-center text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-indigo-500"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M9 6l6 6-6 6" />
                                            </svg>
                                        </button>
                                    </template>

                                    {{-- Kosong --}}
                                    <div x-show="dayItems.length === 0" x-cloak
                                        class="flex flex-col items-center py-6 text-center">
                                        <span
                                            class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                                                aria-hidden="true">
                                                <path
                                                    d="M8 7V3m8 4V3M5 11h14M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z" />
                                                <path d="M9 16l2 2 4-4" />
                                            </svg>
                                        </span>
                                        <p class="mt-2 text-xs font-semibold text-slate-600">Tidak ada kegiatan</p>
                                        <p class="text-[11px] text-slate-400">Belum ada rapat disetujui pada tanggal
                                            ini.
                                        </p>
                                    </div>
                                </div>

                                <a href="{{ route('calendar') }}"
                                    class="mt-3 flex items-center justify-center gap-1.5 rounded-xl bg-slate-50 py-2 text-xs font-semibold text-indigo-600 transition hover:bg-indigo-50 hover:text-indigo-800">
                                    Buka kalender lengkap
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        aria-hidden="true">
                                        <path d="M5 12h14M13 6l6 6-6 6" />
                                    </svg>
                                </a>
                            </div>

                            {{-- ===== Modal detail kegiatan ===== --}}
                            <div x-show="d" x-cloak x-transition.opacity
                                class="fixed inset-0 z-50 flex items-center justify-center p-4">
                                <div class="absolute inset-0 bg-slate-900/50" @click="d = null"></div>
                                <div class="relative w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl"
                                    x-show="d" x-transition>
                                    <template x-if="d">
                                        <div>
                                            <div
                                                class="flex items-start justify-between gap-4 border-b border-slate-100 p-5">
                                                <div class="min-w-0">
                                                    <h3 class="text-base font-bold leading-snug text-slate-900"
                                                        x-text="d.title"></h3>
                                                    <div class="mt-2 flex flex-wrap items-center gap-2">
                                                        <span
                                                            class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                                            <span class="h-2 w-2 rounded-full"
                                                                :style="'background:' + d.color"></span><span
                                                                x-text="d.room"></span>
                                                        </span>
                                                        <span class="rounded-full px-3 py-1 text-xs font-semibold"
                                                            :class="d.statusCls" x-text="d.status"></span>
                                                    </div>
                                                </div>
                                                <button @click="d = null"
                                                    class="text-slate-400 hover:text-slate-600">✕</button>
                                            </div>
                                            <div class="space-y-3 p-5">
                                                <div class="grid grid-cols-2 gap-3">
                                                    <div class="rounded-xl bg-slate-50 p-3">
                                                        <div class="text-xs text-slate-500">Tanggal</div>
                                                        <div class="text-sm font-semibold text-slate-900"
                                                            x-text="d.date">
                                                        </div>
                                                    </div>
                                                    <div class="rounded-xl bg-slate-50 p-3">
                                                        <div class="text-xs text-slate-500">Waktu</div>
                                                        <div class="text-sm font-semibold text-slate-900"
                                                            x-text="d.time">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="rounded-xl bg-slate-50 p-3">
                                                    <div class="text-xs text-slate-500">Pengusul</div>
                                                    <div class="text-sm font-semibold text-slate-900" x-text="d.unit">
                                                    </div>
                                                </div>
                                                <div class="rounded-xl bg-slate-50 p-3" x-show="d.desc">
                                                    <div class="text-xs text-slate-500">Deskripsi</div>
                                                    <div class="mt-1 whitespace-pre-wrap text-sm text-slate-800"
                                                        x-text="d.desc"></div>
                                                </div>
                                            </div>
                                            <div class="flex justify-end border-t border-slate-100 p-4">
                                                <button @click="d = null"
                                                    class="rounded-xl bg-slate-100 px-5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">Tutup</button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <script>
                            (function () {
                                const rows = document.querySelectorAll('.schedule-row');
                                if (!rows.length) return;
                                const inputs = [document.getElementById('global-search'), document.getElementById('local-search')].filter(Boolean);
                                const empty = document.getElementById('no-result');
                                const tabs = document.querySelectorAll('.status-tab');
                                let status = 'all', q = '';

                                function apply() {
                                    rows.forEach(r => {
                                        const ok = (status === 'all' || r.dataset.status === status) && (!q || r.dataset.search.includes(q));
                                        r.classList.toggle('hidden', !ok);
                                    });
                                    const shown = [...rows].filter(r => !r.classList.contains('hidden')).length;
                                    empty.classList.toggle('hidden', shown > 0);
                                }

                                inputs.forEach(inp => inp.addEventListener('input', () => {
                                    q = inp.value.trim().toLowerCase();
                                    inputs.forEach(o => { if (o !== inp) o.value = inp.value; });
                                    apply();
                                }));

                                tabs.forEach(t => t.addEventListener('click', () => {
                                    status = t.dataset.filter;
                                    tabs.forEach(o => {
                                        const on = o === t;
                                        o.classList.remove(...o.dataset.on.split(' '), ...o.dataset.off.split(' '));
                                        o.classList.add(...(on ? o.dataset.on : o.dataset.off).split(' '));
                                    });
                                    apply();
                                }));
                            })();
                        </script>
</x-app-layout>