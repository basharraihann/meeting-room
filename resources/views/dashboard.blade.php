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

    $todayBookings = $rangeBookings->filter(fn ($b) => Carbon::parse($b->start_at)->isSameDay($now))->values();
    $upcomingByDay = $rangeBookings
        ->reject(fn ($b) => Carbon::parse($b->start_at)->isSameDay($now))
        ->groupBy(fn ($b) => Carbon::parse($b->start_at)->toDateString());

    $rooms = \App\Models\Room::where('active', true)->orderBy('id')->get();
    $pendingTotal = \App\Models\Booking::where('status', 'PENDING')->where('start_at', '>=', $rangeStart)->count();

    // Status tiap kegiatan
    $statusOf = function ($b) use ($now) {
        $s = Carbon::parse($b->start_at);
        $e = Carbon::parse($b->end_at);
        if ($now->between($s, $e)) return ['live', 'Sedang Berlangsung', 'bg-emerald-50 text-emerald-700', 'bg-emerald-500'];
        if ($now->greaterThan($e)) return ['done', 'Selesai', 'bg-slate-100 text-slate-500', 'bg-slate-400'];
        if ($now->diffInMinutes($s) <= 60) return ['upcoming', 'Segera', 'bg-amber-50 text-amber-600', 'bg-amber-500'];
        return ['upcoming', 'Terjadwal', 'bg-sky-50 text-sky-700', 'bg-sky-500'];
    };

    $liveBookings = $todayBookings->filter(fn ($b) => $statusOf($b)[0] === 'live')->values();
    $liveBooking = $liveBookings->first();
    $nextBooking = \App\Models\Booking::with('room')->where('status', 'APPROVED')
        ->where('start_at', '>', $now)->orderBy('start_at')->first();

    // Status ruangan saat ini
    $roomStates = $rooms->map(function ($r) use ($todayBookings, $now) {
        if ($r->maintenance) return [$r, 'maint', 'Kegiatan BPK', null];
        $mine = $todayBookings->where('room_id', $r->id);
        $live = $mine->first(fn ($b) => $now->between(Carbon::parse($b->start_at), Carbon::parse($b->end_at)));
        if ($live) return [$r, 'busy', 'Dipakai sampai ' . Carbon::parse($live->end_at)->format('H.i'), $live->title];
        $next = $mine->first(fn ($b) => Carbon::parse($b->start_at)->greaterThan($now));
        return [$r, 'free', $next ? 'Berikutnya ' . Carbon::parse($next->start_at)->format('H.i') : 'Kosong hari ini', null];
    });
    $availableRooms = $roomStates->filter(fn ($x) => $x[1] === 'free')->count();

    $roomColors = [1 => '#1a1a1a', 2 => '#a855f7', 3 => '#92400e', 4 => '#facc15', 5 => '#22d3ee', 6 => '#ef4444', 7 => '#ec4899', 8 => '#468432'];

    $dateLabel = $rangeStart->isoFormat('DD MMM') . ' – ' . $rangeEnd->isoFormat('DD MMM Y');
    $isPic = auth()->user()->hasRole('PIC');

    $stats = [
        ['Total Kegiatan', $rangeBookings->count(), 'dalam ' . ($range === 14 ? '2 minggu' : $range . ' hari') . ' ke depan', 'bg-indigo-100 text-indigo-600', 'M8 7V3m8 4V3M5 11h14M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z', route('calendar')],
        ['Ruang Tersedia', $availableRooms, 'dari ' . $rooms->count() . ' ruangan', 'bg-emerald-100 text-emerald-600', 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1', '#status-ruangan'],
        ['Kegiatan Hari Ini', $todayBookings->count(), $liveBookings->count() . ' sedang berlangsung', 'bg-amber-100 text-amber-500', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', '#jadwal-hari-ini'],
        ['Menunggu Persetujuan', $pendingTotal, 'pengajuan belum diproses', 'bg-sky-100 text-sky-500', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', auth()->user()->hasRole('TU') ? route('approvals.index') : route('calendar')],
    ];

    $tabs = ['all' => 'Semua', 'live' => 'Berlangsung', 'upcoming' => 'Akan Datang', 'done' => 'Selesai'];
    $tabCounts = ['all' => $todayBookings->count()];
    foreach (['live', 'upcoming', 'done'] as $k) {
        $tabCounts[$k] = $todayBookings->filter(fn ($b) => $statusOf($b)[0] === $k)->count();
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
@endphp

<x-app-layout>
    <div class="space-y-6 px-4 py-8 sm:px-8" x-data="{ d: null }" @keydown.escape.window="d = null">

        {{-- ===== Banner judul ===== --}}
        <div class="relative flex items-center gap-4 overflow-hidden rounded-2xl border border-white/70 bg-white/80 p-4 shadow-sm backdrop-blur sm:gap-5 sm:p-6">
            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-500 sm:h-16 sm:w-16">
                <svg class="h-7 w-7 sm:h-8 sm:w-8" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h4v-6h4v6h4a1 1 0 001-1V10"/></svg>
            </span>
            <div class="min-w-0 flex-1">
                <h1 class="text-xl font-extrabold text-[#0f1e5a] sm:text-2xl">Jadwal Ruang Rapat</h1>
                <p class="mt-1 text-sm text-slate-500">Kelola pemesanan ruang rapat di lingkungan Kementerian Koordinator Bidang Pangan.</p>
            </div>

            {{-- Daun dekoratif --}}
            <svg class="pointer-events-none absolute -bottom-2 right-2 hidden h-28 w-28 text-indigo-200/70 sm:block" viewBox="0 0 120 120" fill="currentColor" aria-hidden="true">
                <path d="M60 120C50 80 55 45 80 15c12 35 5 75-20 105z"/>
                <path d="M58 120C35 100 25 70 35 40c25 15 33 50 23 80z" opacity=".7"/>
                <path d="M62 120c20-15 38-20 55-12-12 18-35 24-55 12z" opacity=".6"/>
            </svg>
        </div>

        {{-- ===== Filter periode + aksi ===== --}}
        <div class="flex flex-wrap items-center justify-end gap-3">
            @if($isPic)
                <a href="{{ route('calendar') }}" class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-600/25 transition hover:bg-indigo-700">+ Ajukan Rapat</a>
            @endif
            <div class="flex items-center gap-2 rounded-2xl border border-slate-100 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm">
                <svg class="h-5 w-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M5 11h14M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z"/></svg>
                {{ $dateLabel }}
            </div>
            <div class="flex rounded-full bg-white p-1 shadow-sm">
                @foreach([3 => '3 Hari', 7 => '7 Hari', 14 => '2 Minggu'] as $val => $lbl)
                    <a href="{{ route('dashboard', ['range' => $val]) }}"
                        class="rounded-full px-4 py-2 text-sm font-semibold transition sm:px-5 {{ $range === $val ? 'bg-indigo-500 text-white shadow' : 'text-slate-600 hover:text-slate-900' }}">{{ $lbl }}</a>
                @endforeach
            </div>
        </div>

        {{-- ===== Hero: sedang berlangsung / berikutnya ===== --}}
        @if($liveBooking)
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-600 to-indigo-500 p-6 text-white shadow-xl shadow-indigo-600/20 sm:p-8">
                <div class="pointer-events-none absolute -right-10 -top-16 h-56 w-56 rounded-full bg-white/10"></div>
                <div class="pointer-events-none absolute -bottom-20 right-24 h-48 w-48 rounded-full bg-white/5"></div>
                <div class="relative flex flex-wrap items-center justify-between gap-6">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-white/80">
                            <span class="h-2.5 w-2.5 animate-pulse rounded-full bg-white"></span> Sedang Berlangsung
                            @if($liveBookings->count() > 1)
                                <span class="rounded-full bg-white/20 px-2 py-0.5 text-[10px] normal-case tracking-normal">+{{ $liveBookings->count() - 1 }} lainnya</span>
                            @endif
                        </div>
                        <div class="mt-2 text-2xl font-extrabold leading-snug sm:text-3xl">{{ $liveBooking->title }}</div>
                        <div class="mt-1.5 text-sm text-white/80">
                            {{ $liveBooking->room?->name }} · {{ Carbon::parse($liveBooking->start_at)->format('H.i') }} – {{ Carbon::parse($liveBooking->end_at)->format('H.i') }}
                            @if($liveBooking->unit_kerja) · {{ $liveBooking->unit_kerja }} @endif
                        </div>
                        <div class="mt-4 h-1.5 max-w-md overflow-hidden rounded-full bg-white/20">
                            <div id="hero-progress" class="h-full rounded-full bg-white transition-all" style="width:0%"></div>
                        </div>
                    </div>
                    <div class="min-w-[150px] rounded-2xl bg-white/15 px-6 py-4 text-center backdrop-blur">
                        <div class="text-xs font-semibold text-white/80">Selesai dalam</div>
                        <div id="hero-countdown" class="mt-1 text-2xl font-extrabold tabular-nums">--</div>
                    </div>
                </div>
            </div>
            <script>
                (function () {
                    const start = new Date("{{ Carbon::parse($liveBooking->start_at)->toIso8601String() }}");
                    const end = new Date("{{ Carbon::parse($liveBooking->end_at)->toIso8601String() }}");
                    const cd = document.getElementById('hero-countdown'), bar = document.getElementById('hero-progress');
                    function tick() {
                        const now = new Date(), diff = Math.max(0, end - now);
                        const h = Math.floor(diff / 3600000), m = Math.floor((diff % 3600000) / 60000), s = Math.floor((diff % 60000) / 1000);
                        cd.textContent = (h ? h + 'j ' : '') + m + 'm ' + s + 'd';
                        bar.style.width = Math.min(100, Math.max(0, (now - start) / (end - start) * 100)) + '%';
                        if (diff > 0) setTimeout(tick, 1000); else cd.textContent = 'Selesai';
                    }
                    tick();
                })();
            </script>
        @elseif($nextBooking)
            <div class="flex flex-wrap items-center justify-between gap-6 rounded-3xl border border-indigo-100 bg-white p-6 shadow-sm sm:p-8">
                <div class="min-w-0 flex-1">
                    <div class="text-xs font-bold uppercase tracking-widest text-indigo-400">Rapat Berikutnya</div>
                    <div class="mt-2 text-2xl font-extrabold leading-snug text-[#0f1e5a]">{{ $nextBooking->title }}</div>
                    <div class="mt-1.5 text-sm text-slate-500">
                        {{ $nextBooking->room?->name }} · {{ Carbon::parse($nextBooking->start_at)->isoFormat('dddd, D MMM') }} ·
                        {{ Carbon::parse($nextBooking->start_at)->format('H.i') }} – {{ Carbon::parse($nextBooking->end_at)->format('H.i') }}
                    </div>
                </div>
                <div class="min-w-[150px] rounded-2xl border border-indigo-100 bg-indigo-50 px-6 py-4 text-center">
                    <div class="text-xs font-semibold text-indigo-400">Mulai dalam</div>
                    <div id="hero-countdown" class="mt-1 text-2xl font-extrabold tabular-nums text-indigo-600">--</div>
                </div>
            </div>
            <script>
                (function () {
                    const start = new Date("{{ Carbon::parse($nextBooking->start_at)->toIso8601String() }}");
                    const cd = document.getElementById('hero-countdown');
                    function tick() {
                        const diff = Math.max(0, start - new Date());
                        const dd = Math.floor(diff / 86400000), h = Math.floor((diff % 86400000) / 3600000), m = Math.floor((diff % 3600000) / 60000), s = Math.floor((diff % 60000) / 1000);
                        cd.textContent = (dd ? dd + 'h ' : '') + (h ? h + 'j ' : '') + m + 'm' + (dd ? '' : ' ' + s + 'd');
                        if (diff > 0) setTimeout(tick, 1000);
                    }
                    tick();
                })();
            </script>
        @endif

        {{-- ===== Kartu statistik ===== --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($stats as [$label, $value, $sub, $cls, $icon, $href])
                <a href="{{ $href }}" class="group flex items-center gap-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full {{ $cls }}">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                    </span>
                    <div class="min-w-0">
                        <div class="text-xs font-medium text-slate-500">{{ $label }}</div>
                        <div class="text-3xl font-extrabold leading-tight text-slate-900">{{ $value }}</div>
                        <div class="truncate text-xs text-slate-400">{{ $sub }}</div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="grid gap-6 xl:grid-cols-3">

            {{-- ===== Kolom kiri: jadwal ===== --}}
            <div class="space-y-6 xl:col-span-2">

                {{-- Jadwal hari ini --}}
                <div id="jadwal-hari-ini" class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm sm:p-6">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-500">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M5 11h14M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z"/></svg>
                            </span>
                            <div>
                                <h2 class="text-lg font-extrabold text-slate-900">Jadwal Hari Ini</h2>
                                <p class="text-xs text-slate-500">{{ $now->isoFormat('dddd, DD MMMM Y') }}</p>
                            </div>
                        </div>
                        <a href="{{ route('calendar') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">
                            Lihat Kalender
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>

                    @if($todayBookings->isNotEmpty())
                        {{-- Cari (muncul di HP, karena search topbar disembunyikan) + tab status --}}
                        <div class="mb-4 space-y-3">
                            <input id="local-search" type="search" placeholder="Cari kegiatan atau ruangan..."
                                class="w-full rounded-xl border-slate-200 bg-slate-50 py-2.5 text-sm placeholder-slate-400 focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100 sm:hidden">
                            <div class="flex flex-wrap gap-2" id="status-tabs">
                                @foreach($tabs as $key => $lbl)
                                    <button type="button" data-filter="{{ $key }}"
                                        class="status-tab inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-xs font-semibold transition {{ $key === 'all' ? 'bg-indigo-500 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                                        {{ $lbl }}
                                        <span class="rounded-full px-1.5 text-[10px] {{ $key === 'all' ? 'bg-white/25' : 'bg-white' }}">{{ $tabCounts[$key] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Tabel (desktop) --}}
                        <div class="hidden overflow-x-auto md:block">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="bg-slate-50 text-xs font-semibold text-slate-500">
                                        <th class="rounded-l-xl px-4 py-3">No.</th>
                                        <th class="px-4 py-3">Nama Kegiatan</th>
                                        <th class="px-4 py-3">Ruangan</th>
                                        <th class="px-4 py-3">Waktu</th>
                                        <th class="px-4 py-3">Pengusul</th>
                                        <th class="rounded-r-xl px-4 py-3">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    @foreach($todayBookings as $i => $b)
                                        @php
                                            [$key, $label, $cls, $dot] = $statusOf($b);
                                            $c = $roomColors[$b->room_id] ?? '#6366f1';
                                        @endphp
                                        <tr class="schedule-row cursor-pointer transition hover:bg-indigo-50/40 {{ $key === 'live' ? 'bg-indigo-50/60' : '' }} {{ $key === 'done' ? 'opacity-60' : '' }}"
                                            data-status="{{ $key }}"
                                            data-search="{{ strtolower($b->title . ' ' . ($b->room?->name ?? '') . ' ' . ($b->unit_kerja ?? '')) }}"
                                            x-on:click="d = @js($payload($b))">
                                            <td class="px-4 py-4 text-slate-400">{{ $i + 1 }}</td>
                                            <td class="max-w-xs px-4 py-4 font-semibold text-slate-900">{{ $b->title }}</td>
                                            <td class="px-4 py-4">
                                                <span class="inline-flex items-center gap-2 whitespace-nowrap rounded-full px-3 py-1.5 text-xs font-semibold text-slate-700" style="background: {{ $c }}1f">
                                                    <span class="h-2 w-2 rounded-full" style="background: {{ $c }}"></span>{{ $b->room?->name ?? '-' }}
                                                </span>
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-4 font-semibold text-slate-800">{{ Carbon::parse($b->start_at)->format('H.i') }} – {{ Carbon::parse($b->end_at)->format('H.i') }}</td>
                                            <td class="px-4 py-4 text-slate-500">{{ $b->unit_kerja ?: '-' }}</td>
                                            <td class="px-4 py-4">
                                                <span class="inline-flex items-center gap-2 whitespace-nowrap rounded-full px-3 py-1.5 text-xs font-semibold {{ $cls }}">
                                                    <span class="h-1.5 w-1.5 rounded-full {{ $dot }} {{ $key === 'live' ? 'animate-pulse' : '' }}"></span>{{ $label }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Kartu (HP) --}}
                        <div class="space-y-3 md:hidden">
                            @foreach($todayBookings as $b)
                                @php
                                    [$key, $label, $cls, $dot] = $statusOf($b);
                                    $c = $roomColors[$b->room_id] ?? '#6366f1';
                                @endphp
                                <div class="schedule-row flex cursor-pointer gap-3 rounded-xl border border-slate-100 p-4 {{ $key === 'live' ? 'bg-indigo-50/60' : '' }} {{ $key === 'done' ? 'opacity-60' : '' }}"
                                    data-status="{{ $key }}"
                                    data-search="{{ strtolower($b->title . ' ' . ($b->room?->name ?? '') . ' ' . ($b->unit_kerja ?? '')) }}"
                                    x-on:click="d = @js($payload($b))">
                                    <div class="w-1 shrink-0 self-stretch rounded" style="background: {{ $c }}"></div>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-sm font-bold text-slate-900">{{ $b->title }}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ $b->room?->name }} · {{ $b->unit_kerja ?: '-' }}</div>
                                        <div class="mt-2 flex flex-wrap items-center gap-2">
                                            <span class="rounded-md bg-indigo-50 px-2 py-1 text-xs font-bold text-indigo-600">{{ Carbon::parse($b->start_at)->format('H.i') }} – {{ Carbon::parse($b->end_at)->format('H.i') }}</span>
                                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $cls }}">
                                                <span class="h-1.5 w-1.5 rounded-full {{ $dot }}"></span>{{ $label }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div id="no-result" class="hidden py-12 text-center text-sm text-slate-400">Tidak ada kegiatan yang cocok.</div>
                    @else
                        <div class="flex flex-col items-center py-14 text-center">
                            <span class="flex h-16 w-16 items-center justify-center rounded-full bg-indigo-50 text-indigo-400">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M5 11h14M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z"/></svg>
                            </span>
                            <div class="mt-4 text-base font-bold text-slate-800">Tidak ada kegiatan hari ini</div>
                            <p class="mt-1 text-sm text-slate-500">Semua ruangan kosong. Ajukan rapat atau cek jadwal hari lain.</p>
                            @if($isPic)
                                <a href="{{ route('calendar') }}" class="mt-5 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">+ Ajukan Rapat</a>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Jadwal mendatang (mengikuti filter 3 hari / 7 hari / 2 minggu) --}}
                @if($range > 1)
                    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm sm:p-6">
                        <div class="mb-4 flex items-center gap-3">
                            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-sky-50 text-sky-500">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            </span>
                            <div>
                                <h2 class="text-lg font-extrabold text-slate-900">Jadwal Mendatang</h2>
                                <p class="text-xs text-slate-500">Sampai {{ $rangeEnd->isoFormat('dddd, D MMMM') }}</p>
                            </div>
                        </div>

                        @forelse($upcomingByDay as $day => $items)
                            <div class="{{ $loop->first ? '' : 'mt-5' }}">
                                <div class="mb-2 flex items-center gap-3">
                                    <span class="text-xs font-extrabold uppercase tracking-wider text-slate-600">{{ Carbon::parse($day)->isoFormat('dddd, D MMM') }}</span>
                                    <span class="h-px flex-1 bg-slate-100"></span>
                                    <span class="text-xs text-slate-400">{{ $items->count() }} kegiatan</span>
                                </div>
                                <div class="space-y-2">
                                    @foreach($items as $b)
                                        @php $c = $roomColors[$b->room_id] ?? '#6366f1'; @endphp
                                        <div class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-100 px-4 py-3 transition hover:border-indigo-200 hover:bg-indigo-50/30"
                                            x-on:click="d = @js($payload($b))">
                                            <span class="w-24 shrink-0 text-sm font-bold text-indigo-600">{{ Carbon::parse($b->start_at)->format('H.i') }} – {{ Carbon::parse($b->end_at)->format('H.i') }}</span>
                                            <span class="h-8 w-1 shrink-0 rounded" style="background: {{ $c }}"></span>
                                            <div class="min-w-0 flex-1">
                                                <div class="truncate text-sm font-semibold text-slate-900">{{ $b->title }}</div>
                                                <div class="truncate text-xs text-slate-500">{{ $b->room?->name }} · {{ $b->unit_kerja ?: '-' }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <p class="py-8 text-center text-sm text-slate-400">Belum ada jadwal pada hari-hari berikutnya.</p>
                        @endforelse
                    </div>
                @endif
            </div>

            {{-- ===== Kolom kanan: status ruangan ===== --}}
            <div id="status-ruangan" class="h-fit rounded-2xl border border-slate-100 bg-white p-5 shadow-sm sm:p-6 xl:sticky xl:top-28">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-extrabold text-slate-900">Status Ruangan</h2>
                        <p class="text-xs text-slate-500">Kondisi saat ini · {{ $now->format('H.i') }}</p>
                    </div>
                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">{{ $availableRooms }} tersedia</span>
                </div>

                <div class="space-y-2.5">
                    @foreach($roomStates as [$room, $state, $info, $title])
                        @php $c = $roomColors[$room->id] ?? '#6366f1'; @endphp
                        <div class="flex items-center gap-3 rounded-xl border border-slate-100 px-4 py-3">
                            <span class="h-3 w-3 shrink-0 rounded-full" style="background: {{ $c }}"></span>
                            <div class="min-w-0 flex-1">
                                <div class="truncate text-sm font-semibold text-slate-900">{{ $room->name }}</div>
                                <div class="truncate text-xs text-slate-500">{{ $title ?: $info }}@if($title) · {{ $info }}@endif</div>
                            </div>
                            @if($state === 'busy')
                                <span class="shrink-0 rounded-full bg-red-50 px-2.5 py-1 text-[11px] font-bold text-red-600">Dipakai</span>
                            @elseif($state === 'maint')
                                <span class="shrink-0 rounded-full bg-orange-50 px-2.5 py-1 text-[11px] font-bold text-orange-600">BPK</span>
                            @else
                                <span class="shrink-0 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700">Tersedia</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ===== Modal detail kegiatan ===== --}}
        <div x-show="d" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/50" @click="d = null"></div>
            <div class="relative w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl" x-show="d" x-transition>
                <template x-if="d">
                    <div>
                        <div class="flex items-start justify-between gap-4 border-b border-slate-100 p-5">
                            <div class="min-w-0">
                                <h3 class="text-lg font-bold leading-snug text-slate-900" x-text="d.title"></h3>
                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                        <span class="h-2 w-2 rounded-full" :style="'background:' + d.color"></span><span x-text="d.room"></span>
                                    </span>
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="d.statusCls" x-text="d.status"></span>
                                </div>
                            </div>
                            <button @click="d = null" class="text-slate-400 hover:text-slate-600">✕</button>
                        </div>
                        <div class="space-y-3 p-5">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-xl bg-slate-50 p-3"><div class="text-xs text-slate-500">Tanggal</div><div class="text-sm font-semibold text-slate-900" x-text="d.date"></div></div>
                                <div class="rounded-xl bg-slate-50 p-3"><div class="text-xs text-slate-500">Waktu</div><div class="text-sm font-semibold text-slate-900" x-text="d.time"></div></div>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3"><div class="text-xs text-slate-500">Pengusul</div><div class="text-sm font-semibold text-slate-900" x-text="d.unit"></div></div>
                            <div class="rounded-xl bg-slate-50 p-3" x-show="d.desc"><div class="text-xs text-slate-500">Deskripsi</div><div class="mt-1 whitespace-pre-wrap text-sm text-slate-800" x-text="d.desc"></div></div>
                        </div>
                        <div class="flex justify-end border-t border-slate-100 p-4">
                            <button @click="d = null" class="rounded-xl bg-slate-100 px-5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200">Tutup</button>
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
                const shown = [...rows].filter(r => !r.classList.contains('hidden') && r.offsetParent !== null).length;
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
                    o.classList.toggle('bg-indigo-500', on); o.classList.toggle('text-white', on);
                    o.classList.toggle('bg-slate-100', !on); o.classList.toggle('text-slate-600', !on);
                });
                apply();
            }));
        })();
    </script>
</x-app-layout>