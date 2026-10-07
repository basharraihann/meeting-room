@use('Carbon\Carbon')
@use('Illuminate\Support\Str')

<x-app-layout>
    @php
        // Ikon (gaya Lucide, sama dengan sidebar)
        $ic = [
            'approval' => '<path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"/><path d="m9 12 2 2 4-4"/>',
            'building' => '<path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4M10 10h4M10 14h4M10 18h4"/>',
            'check' => '<path d="M20 6 9 17l-5-5"/>',
            'x' => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
            'alert' => '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/>',
            'circle-check' => '<circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>',
            'circle-alert' => '<circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/>',
            'undo' => '<path d="M9 14 4 9l5-5"/><path d="M4 9h10.5a5.5 5.5 0 0 1 5.5 5.5a5.5 5.5 0 0 1-5.5 5.5H11"/>',
            'history' => '<path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/>',
            'inbox' => '<polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>',
            'clipboard' => '<rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/>',
        ];
        $svg = fn(string $name, string $cls = 'h-4 w-4', string $sw = '1.75') =>
            '<svg class="' . $cls . '" fill="none" stroke="currentColor" stroke-width="' . $sw . '" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">' . $ic[$name] . '</svg>';

        // Warna ruangan dari kolom rooms.color (konsisten dengan halaman lain)
        $roomColors = \App\Models\Room::pluck('color', 'id')->filter()->all();

        $statusMap = [
            'APPROVED' => ['Disetujui', 'bg-emerald-50 text-emerald-700 ring-emerald-600/20', 'bg-emerald-500'],
            'REJECTED' => ['Ditolak', 'bg-rose-50 text-rose-700 ring-rose-600/20', 'bg-rose-500'],
            'CANCELLED' => ['Dibatalkan', 'bg-slate-100 text-slate-500 ring-slate-400/20', 'bg-slate-400'],
            'CANCELED' => ['Dibatalkan', 'bg-slate-100 text-slate-500 ring-slate-400/20', 'bg-slate-400'],
        ];

        // Kolom tetap agar rata di semua baris (tabel mulai breakpoint xl)
        $pCols = 'grid-cols-1 xl:grid-cols-[112px_minmax(0,1fr)_190px_132px_212px]';
        $rCols = 'grid-cols-1 xl:grid-cols-[112px_minmax(0,1fr)_180px_132px_124px_112px]';
    @endphp

    <div class="space-y-4 px-4 pb-8 pt-4 sm:px-8">

        {{-- ===== BANNER JUDUL ===== --}}
        <div
            class="relative flex items-center gap-3 overflow-hidden rounded-xl border border-white/70 bg-white/80 px-4 py-3 shadow-sm backdrop-blur sm:gap-4">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-500">
                {!! $svg('approval', 'h-5 w-5') !!}
            </span>
            <div class="min-w-0 flex-1">
                <h1 class="text-base font-extrabold leading-tight text-[#0f1e5a] sm:text-lg">Approval Inbox</h1>
                <p class="mt-0.5 text-xs text-slate-500">Tinjau dan putuskan pengajuan booking untuk ruangan yang Anda
                    kelola.</p>
            </div>
        </div>

        {{-- ===== ALERT ===== --}}
        @if(session('status'))
            <div x-data="{ show: true }" x-show="show" x-transition
                class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-[13px] font-semibold text-emerald-800">
                <span class="shrink-0 text-emerald-600">{!! $svg('circle-check', 'h-5 w-5') !!}</span>
                <span class="flex-1">{{ session('status') }}</span>
                <button type="button" @click="show = false" aria-label="Tutup"
                    class="rounded-md p-1 text-emerald-500 transition hover:bg-emerald-100 hover:text-emerald-700">
                    {!! $svg('x', 'h-4 w-4', '2') !!}
                </button>
            </div>
        @endif
        @if($errors->any())
            <div
                class="flex items-center gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-[13px] font-semibold text-rose-800">
                <span class="shrink-0 text-rose-600">{!! $svg('circle-alert', 'h-5 w-5') !!}</span>
                <span class="flex-1">{{ $errors->first() }}</span>
            </div>
        @endif

        @if($noRoom)
            {{-- ===== BELUM ADA PENUGASAN ===== --}}
            <div class="rounded-2xl border border-slate-200/80 bg-white px-6 py-16 text-center shadow-sm">
                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    {!! $svg('building', 'h-6 w-6', '1.6') !!}
                </span>
                <h3 class="mt-3 text-sm font-bold text-slate-800">Belum ada penugasan ruangan</h3>
                <p class="mx-auto mt-1 max-w-sm text-xs text-slate-500">Anda belum ditugaskan untuk mengelola ruangan
                    manapun. Silakan hubungi admin untuk mendapatkan penugasan.</p>
            </div>
        @else
            @php
                $totalPending = $clusters->sum(fn($c) => $c['items']->count());
                $conflictCount = $clusters->filter(fn($c) => $c['items']->count() > 1)->count();
                $conflictReq = $clusters->filter(fn($c) => $c['items']->count() > 1)->sum(fn($c) => $c['items']->count());
                $singleReq = $totalPending - $conflictReq;
                $multiRoom = $clusters->pluck('room_name')->unique()->count() > 1;
                $fmtDur = fn($m) => trim(($m >= 60 ? intdiv($m, 60) . ' j ' : '') . ($m % 60 ? ($m % 60) . ' m' : ''));

                // Kelompokkan per hari (terdekat dulu), lalu per slot waktu
                $days = $clusters
                    ->sortBy(fn($c) => $c['start'])
                    ->groupBy(fn($c) => Carbon::parse($c['start'])->toDateString())
                    ->sortKeys();

                // Teks pencarian per slot + indeks untuk filter (Alpine)
                $searchOf = fn($c) => Str::lower(implode(' ', array_filter(array_merge(
                    [Carbon::parse($c['start'])->translatedFormat('l d F Y'), $c['room_name'] ?? ''],
                    $c['items']->flatMap(fn($b) => [$b->title, $b->pic?->name, $b->unit_kerja, $b->description])->all()
                ))));
                $allIdx = $clusters->map(fn($c) => [$c['items']->count() > 1 ? 1 : 0, $searchOf($c)])->values()->all();
            @endphp

            <div x-data="{
                            tab: @js(request()->has('page') || request('tab') === 'riwayat' ? 'riwayat' : 'pending'),
                            filter: 'all',
                            q: '',
                            idx: @js($allIdx),
                            show(c, s) {
                                return (this.filter === 'all' || (this.filter === 'conflict') === !!c)
                                    && (!this.q || s.includes(this.q.toLowerCase().trim()));
                            },
                            dayShow(list) { return list.some(r => this.show(r[0], r[1])); },
                            get visible() { return this.idx.filter(r => this.show(r[0], r[1])).length; }
                        }" class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">

                {{-- ===== RINGKASAN ===== --}}
                <div class="flex flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-5">
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Ruangan yang Anda
                            kelola</p>
                        <div class="mt-1 flex flex-wrap items-center gap-2">
                            <span class="text-indigo-500">{!! $svg('building', 'h-5 w-5', '2') !!}</span>
                            <h2 class="text-lg font-extrabold leading-tight text-slate-900">
                                {{ $assignedRoom?->name ?? 'Semua Ruangan' }}
                            </h2>
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $totalPending > 0 ? 'bg-amber-50 text-amber-700 ring-amber-600/20' : 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' }}">
                                <span
                                    class="h-1.5 w-1.5 rounded-full {{ $totalPending > 0 ? 'animate-pulse bg-amber-500' : 'bg-emerald-500' }}"></span>
                                {{ $totalPending > 0 ? $totalPending . ' menunggu' : 'Tidak ada pending' }}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-stretch gap-2">
                        <div class="min-w-[84px] rounded-xl bg-amber-50/70 px-3.5 py-2 ring-1 ring-inset ring-amber-200/70">
                            <div class="text-lg font-extrabold leading-none tabular-nums text-amber-700">
                                {{ $totalPending }}
                            </div>
                            <div class="mt-1 text-[11px] font-medium text-amber-700/80">Menunggu</div>
                        </div>
                        <div
                            class="min-w-[84px] rounded-xl px-3.5 py-2 ring-1 ring-inset {{ $conflictCount > 0 ? 'bg-rose-50/70 ring-rose-200/70' : 'bg-slate-50 ring-slate-200/70' }}">
                            <div
                                class="text-lg font-extrabold leading-none tabular-nums {{ $conflictCount > 0 ? 'text-rose-700' : 'text-slate-900' }}">
                                {{ $conflictCount }}
                            </div>
                            <div
                                class="mt-1 text-[11px] font-medium {{ $conflictCount > 0 ? 'text-rose-700/80' : 'text-slate-500' }}">
                                Bentrok</div>
                        </div>
                        <div class="min-w-[84px] rounded-xl bg-slate-50 px-3.5 py-2 ring-1 ring-inset ring-slate-200/70">
                            <div class="text-lg font-extrabold leading-none tabular-nums text-slate-900">
                                {{ $riwayatTotal }}
                            </div>
                            <div class="mt-1 text-[11px] font-medium text-slate-500">Riwayat</div>
                        </div>
                    </div>
                </div>

                {{-- ===== TAB ===== --}}
                <div class="flex flex-wrap items-center gap-3 border-y border-slate-100 bg-slate-50/60 px-4 py-3 sm:px-5">
                    <div class="flex rounded-lg bg-slate-200/60 p-0.5" role="tablist" aria-label="Tampilan">
                        <button type="button" role="tab" @click="tab = 'pending'" :aria-selected="tab === 'pending'"
                            :class="tab === 'pending' ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                            class="flex items-center gap-2 rounded-md px-4 py-1.5 text-[13px] font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                            Pending
                            @if($totalPending > 0)
                                <span
                                    :class="tab === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-slate-300/70 text-slate-600'"
                                    class="rounded-full px-1.5 py-0.5 text-[10px] font-extrabold leading-none">{{ $totalPending }}</span>
                            @endif
                        </button>
                        <button type="button" role="tab" @click="tab = 'riwayat'" :aria-selected="tab === 'riwayat'"
                            :class="tab === 'riwayat' ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                            class="rounded-md px-4 py-1.5 text-[13px] font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                            Riwayat
                        </button>
                    </div>
                </div>

                {{-- ================= TAB: PENDING ================= --}}
                <div x-show="tab === 'pending'">
                    @if($clusters->isEmpty())
                        <div class="px-4 py-14 text-center">
                            <span
                                class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-emerald-500">
                                {!! $svg('circle-check', 'h-6 w-6', '1.6') !!}
                            </span>
                            <div class="mt-3 text-sm font-bold text-slate-800">Semua beres</div>
                            <p class="mx-auto mt-1 max-w-sm text-xs text-slate-500">Tidak ada request pending untuk
                                ruangan ini saat ini.</p>
                        </div>
                    @else
                        {{-- Filter + pencarian --}}
                        <div class="flex flex-wrap items-center gap-3 border-b border-slate-100 px-4 py-3 sm:px-5">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <button type="button" @click="filter = 'all'"
                                    :class="filter === 'all' ? 'border-indigo-500 bg-indigo-500 text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-indigo-300 hover:text-indigo-600'"
                                    class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold transition">
                                    Semua <span class="tabular-nums opacity-80">{{ $totalPending }}</span>
                                </button>
                                <button type="button" @click="filter = 'conflict'"
                                    :class="filter === 'conflict' ? 'border-rose-500 bg-rose-500 text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-rose-300 hover:text-rose-600'"
                                    class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold transition">
                                    @if($conflictReq > 0)<span
                                    class="h-1.5 w-1.5 animate-pulse rounded-full bg-rose-400"></span>@endif
                                    Bentrok <span class="tabular-nums opacity-80">{{ $conflictReq }}</span>
                                </button>
                                <button type="button" @click="filter = 'single'"
                                    :class="filter === 'single' ? 'border-indigo-500 bg-indigo-500 text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-indigo-300 hover:text-indigo-600'"
                                    class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold transition">
                                    Tanpa bentrok <span class="tabular-nums opacity-80">{{ $singleReq }}</span>
                                </button>
                            </div>

                            <div class="relative w-full sm:ml-auto sm:w-72">
                                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z" />
                                </svg>
                                <input type="text" x-model="q" autocomplete="off" aria-label="Cari pengajuan"
                                    placeholder="Cari judul, pemohon, unit kerja…"
                                    class="h-9 w-full rounded-lg border border-slate-200 bg-white py-0 pl-9 pr-3 text-[13px] font-medium text-slate-700 placeholder-slate-400 focus:border-indigo-400 focus:ring-4 focus:ring-indigo-500/10">
                            </div>
                        </div>

                        {{-- Header kolom --}}
                        <div
                            class="hidden gap-4 border-b border-slate-100 bg-slate-50/70 px-5 py-2 text-[11px] font-semibold uppercase tracking-wide text-slate-400 xl:grid {{ $pCols }}">
                            <span>Waktu</span>
                            <span>Rapat</span>
                            <span>Pemohon</span>
                            <span>Diajukan</span>
                            <span class="text-right">Aksi</span>
                        </div>

                        {{-- Hasil kosong (filter/pencarian) --}}
                        <div x-show="visible === 0" x-cloak class="px-4 py-12 text-center">
                            <div class="text-sm font-bold text-slate-800">Tidak ada pengajuan yang cocok</div>
                            <p class="mt-1 text-xs text-slate-500">Ubah filter atau kata kunci pencarian.</p>
                            <button type="button" @click="filter = 'all'; q = ''"
                                class="mt-3 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-[13px] font-semibold text-slate-700 transition hover:bg-slate-50">Reset</button>
                        </div>

                        {{-- Per hari --}}
                        @foreach($days as $dateKey => $dayClusters)
                            @php
                                $dDate = Carbon::parse($dateKey);
                                $dayReq = $dayClusters->sum(fn($c) => $c['items']->count());
                                $dayConflict = $dayClusters->filter(fn($c) => $c['items']->count() > 1)->count();
                                $dayIdx = $dayClusters->map(fn($c) => [$c['items']->count() > 1 ? 1 : 0, $searchOf($c)])->values()->all();
                            @endphp

                            <section
                                x-data="{ open: false, get expanded() { return this.open || this.q.trim() !== '' || this.filter !== 'all'; } }"
                                x-show="dayShow(@js($dayIdx))" class="{{ !$loop->first ? 'border-t border-slate-100' : '' }}">

                                {{-- Header hari --}}
                                <button type="button" @click="open = !expanded" :aria-expanded="expanded"
                                    class="flex w-full items-center gap-2.5 border-b border-slate-100 bg-slate-50/80 px-4 py-2.5 text-left transition hover:bg-slate-100/70 sm:px-5">
                                    <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform"
                                        :class="expanded ? 'rotate-90' : ''" fill="none" stroke="currentColor" stroke-width="2.2"
                                        stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="m9 18 6-6-6-6" />
                                    </svg>
                                    <span class="text-sm font-bold text-slate-800">{{ $dDate->translatedFormat('l, d F Y') }}</span>
                                    @if($dDate->isToday())
                                        <span class="rounded bg-indigo-100 px-1.5 py-0.5 text-[11px] font-semibold text-indigo-700">Hari
                                            ini</span>
                                    @elseif($dDate->isTomorrow())
                                        <span
                                            class="rounded bg-sky-100 px-1.5 py-0.5 text-[11px] font-semibold text-sky-700">Besok</span>
                                    @elseif($dDate->lt(today()))
                                        <span
                                            class="rounded bg-amber-100 px-1.5 py-0.5 text-[11px] font-semibold text-amber-700">Terlewat</span>
                                    @endif

                                    <span class="ml-auto flex items-center gap-2">
                                        @if($dayConflict > 0)
                                            <span
                                                class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2 py-0.5 text-[11px] font-bold text-rose-700">
                                                {!! $svg('alert', 'h-3 w-3', '2.2') !!}
                                                {{ $dayConflict }} bentrok
                                            </span>
                                        @endif
                                        <span class="text-xs text-slate-400">{{ $dayReq }} pengajuan</span>
                                    </span>
                                </button>

                                <div x-show="expanded" x-cloak>
                                    @foreach($dayClusters as $c)
                                        @php
                                            $items = $c['items']->sortBy('created_at')->values();
                                            $count = $items->count();
                                            $conflict = $count > 1;
                                            $rc = $roomColors[$items->first()->room_id ?? 0] ?? '#6366f1';
                                            $cStart = Carbon::parse($c['start']);
                                            $cEnd = Carbon::parse($c['end']);
                                        @endphp

                                        <div x-show="show({{ $conflict ? 1 : 0 }}, @js($searchOf($c)))"
                                            class="{{ $conflict ? 'mx-3 my-3 overflow-hidden rounded-xl border border-rose-200 bg-white shadow-sm sm:mx-5' : 'border-b border-slate-100 last:border-b-0' }}">

                                            @if($conflict)
                                                {{-- Header kelompok bentrok --}}
                                                <div
                                                    class="flex flex-wrap items-center gap-x-3 gap-y-1 border-b border-rose-100 bg-rose-50 px-4 py-2.5">
                                                    <span class="text-rose-600">{!! $svg('alert', 'h-4 w-4', '2') !!}</span>
                                                    <span class="text-[13px] font-bold text-rose-800">Bentrok · {{ $count }}
                                                        pengajuan berebut slot ini</span>
                                                    <span
                                                        class="text-xs font-semibold tabular-nums text-rose-700">{{ $cStart->format('H.i') }}
                                                        – {{ $cEnd->format('H.i') }}</span>
                                                    @if($multiRoom)
                                                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-rose-700/80">
                                                            <span class="h-2 w-2 rounded-full"
                                                                style="background: {{ $rc }}"></span>{{ $c['room_name'] ?? '-' }}
                                                        </span>
                                                    @endif
                                                    <span class="ml-auto text-xs font-medium text-rose-700/80">Pilih salah satu untuk
                                                        disetujui</span>
                                                </div>
                                            @endif

                                            <div class="{{ $conflict ? 'divide-y divide-slate-100' : '' }}">
                                                @foreach($items as $i => $b)
                                                    @php
                                                        $picName = $b->pic?->name ?? '-';
                                                        $picInit = mb_strtoupper(mb_substr($picName, 0, 1));
                                                        $bStart = Carbon::parse($b->start_at);
                                                        $bEnd = Carbon::parse($b->end_at);
                                                        $durMin = (int) $bStart->diffInMinutes($bEnd);
                                                        $submitted = Carbon::parse($b->created_at);
                                                    @endphp

                                                    <div
                                                        class="group relative grid gap-x-4 gap-y-2 px-4 py-3.5 transition hover:bg-slate-50/70 sm:px-5 xl:items-center {{ $pCols }}">
                                                        <span class="absolute inset-y-0 left-0 w-[3px]"
                                                            style="background: {{ $conflict ? '#f43f5e' : $rc }}" aria-hidden="true"></span>

                                                        {{-- Waktu --}}
                                                        <div>
                                                            <div class="text-sm font-extrabold tabular-nums leading-tight text-slate-900">
                                                                {{ $bStart->format('H.i') }}
                                                                <span class="font-semibold text-slate-400">–</span>
                                                                {{ $bEnd->format('H.i') }}
                                                            </div>
                                                            <div class="mt-0.5 text-xs text-slate-400">{{ $fmtDur($durMin) }}</div>
                                                        </div>

                                                        {{-- Rapat --}}
                                                        <div class="min-w-0">
                                                            <div class="truncate text-sm font-semibold text-slate-900"
                                                                title="{{ $b->title }}">{{ $b->title }}</div>
                                                            @if($b->unit_kerja || $b->description || $multiRoom)
                                                                <div class="mt-1 flex min-w-0 items-center gap-2 text-xs text-slate-500">
                                                                    @if($multiRoom && !$conflict)
                                                                        <span
                                                                            class="inline-flex shrink-0 items-center gap-1.5 font-medium text-slate-600">
                                                                            <span class="h-2 w-2 rounded-full"
                                                                                style="background: {{ $rc }}"></span>{{ $c['room_name'] ?? '-' }}
                                                                        </span>
                                                                    @endif
                                                                    @if($b->unit_kerja)
                                                                        <span
                                                                            class="inline-flex min-w-0 max-w-[65%] shrink-0 items-center gap-1.5 rounded-md bg-indigo-50 px-2 py-0.5 text-[11px] font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-100"
                                                                            title="{{ $b->unit_kerja }}">
                                                                            {!! $svg('building', 'h-3 w-3 shrink-0', '2') !!}
                                                                            <span class="truncate">{{ $b->unit_kerja }}</span>
                                                                        </span>
                                                                    @endif
                                                                    @if($b->description)
                                                                        <span class="truncate"
                                                                            title="{{ $b->description }}">{{ Str::limit($b->description, 80) }}</span>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        </div>

                                                        {{-- Pemohon --}}
                                                        <div class="flex min-w-0 items-center gap-2.5">
                                                            <span
                                                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-600">{{ $picInit }}</span>
                                                            <span class="truncate text-[13px] font-semibold text-slate-800"
                                                                title="{{ $picName }}">{{ $picName }}</span>
                                                        </div>

                                                        {{-- Diajukan --}}
                                                        <div class="text-xs" title="{{ $submitted->translatedFormat('d M Y, H.i') }}">
                                                            <div class="font-medium text-slate-600">{{ $submitted->diffForHumans() }}</div>
                                                            @if($conflict && $i === 0)
                                                                <span
                                                                    class="mt-1 inline-block rounded bg-emerald-50 px-1.5 py-0.5 text-[10px] font-bold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">Masuk
                                                                    pertama</span>
                                                            @elseif($conflict)
                                                                <span class="mt-1 inline-block text-[11px] text-slate-400">Pengajuan
                                                                    ke-{{ $i + 1 }}</span>
                                                            @endif
                                                        </div>

                                                        {{-- Aksi --}}
                                                        <div class="flex items-center gap-2 pt-1 xl:justify-end xl:pt-0">
                                                            {{-- Setujui --}}
                                                            <form method="POST" action="{{ route('approvals.approve', $b) }}"
                                                                class="flex-1 xl:flex-none" @if($conflict)
                                                                    onsubmit="return confirm('Ada pengajuan lain pada slot waktu yang sama. Setujui pengajuan ini?')"
                                                                @endif>
                                                                @csrf
                                                                <button type="submit"
                                                                    class="inline-flex w-full items-center justify-center gap-1.5 whitespace-nowrap rounded-lg bg-emerald-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm shadow-emerald-600/25 transition hover:bg-emerald-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2">
                                                                    {!! $svg('check', 'h-4 w-4', '2.2') !!}
                                                                    Setujui
                                                                </button>
                                                            </form>

                                                            {{-- Tolak + modal --}}
                                                            <div x-data="{ open: false }" class="flex-1 xl:flex-none">
                                                                <button type="button" @click="open = true"
                                                                    class="inline-flex w-full items-center justify-center gap-1.5 whitespace-nowrap rounded-lg border border-rose-200 bg-rose-50 px-3.5 py-2 text-xs font-semibold text-rose-700 transition hover:border-rose-600 hover:bg-rose-600 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500/40">
                                                                    {!! $svg('x', 'h-4 w-4', '2.2') !!}
                                                                    Tolak
                                                                </button>

                                                                <div x-show="open" x-cloak @keydown.escape.window="open = false"
                                                                    class="fixed inset-0 z-50 flex items-center justify-center p-4">
                                                                    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"
                                                                        @click="open = false"></div>

                                                                    <div x-show="open" x-transition
                                                                        class="relative flex w-full max-w-md flex-col overflow-hidden rounded-2xl bg-white text-left shadow-2xl"
                                                                        role="dialog" aria-modal="true">
                                                                        <div class="flex items-start justify-between gap-3 px-5 pb-2 pt-5">
                                                                            <div class="flex min-w-0 items-center gap-3">
                                                                                <span
                                                                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-500">
                                                                                    {!! $svg('alert', 'h-5 w-5') !!}
                                                                                </span>
                                                                                <div class="min-w-0">
                                                                                    <h3 class="text-base font-extrabold text-slate-900">
                                                                                        Tolak permohonan</h3>
                                                                                    <p class="mt-0.5 truncate text-xs text-slate-500">
                                                                                        {{ $b->title }} · {{ $picName }}
                                                                                    </p>
                                                                                </div>
                                                                            </div>
                                                                            <button type="button" @click="open = false" aria-label="Tutup"
                                                                                class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                                                                                {!! $svg('x', 'h-5 w-5', '2') !!}
                                                                            </button>
                                                                        </div>

                                                                        <form method="POST" action="{{ route('approvals.reject', $b) }}">
                                                                            @csrf
                                                                            <div class="px-5 py-4">
                                                                                <label
                                                                                    class="mb-1.5 block text-xs font-bold text-slate-600">Alasan
                                                                                    penolakan</label>
                                                                                <textarea name="tu_note" required rows="3"
                                                                                    placeholder="Tuliskan alasan spesifik menolak permohonan ini..."
                                                                                    class="w-full resize-none rounded-lg border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-rose-400 focus:bg-white focus:ring-4 focus:ring-rose-500/10"></textarea>
                                                                                <p class="mt-1.5 text-xs text-slate-400">Alasan ini
                                                                                    akan terlihat oleh pengaju.</p>
                                                                            </div>
                                                                            <div
                                                                                class="flex justify-end gap-2 border-t border-slate-100 bg-slate-50/60 px-5 py-3.5">
                                                                                <button type="button" @click="open = false"
                                                                                    class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-[13px] font-semibold text-slate-700 transition hover:bg-slate-50">Kembali</button>
                                                                                <button type="submit"
                                                                                    class="rounded-lg bg-rose-600 px-4 py-2 text-[13px] font-semibold text-white shadow-sm shadow-rose-600/25 transition hover:bg-rose-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500 focus-visible:ring-offset-2">Kirim
                                                                                    penolakan</button>
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        @endforeach

                        <div class="border-t border-slate-100 bg-slate-50/40 px-4 py-3 sm:px-5">
                            <span class="text-xs text-slate-400">
                                <span x-text="visible"></span> dari {{ $clusters->count() }} slot waktu ·
                                {{ $totalPending }} pengajuan menunggu keputusan
                            </span>
                        </div>
                    @endif
                </div>

                {{-- ================= TAB: RIWAYAT ================= --}}
                <div x-show="tab === 'riwayat'" x-cloak>
                    @php
                        $hasRFilter = request()->hasAny(['q', 'from', 'to', 'room_id']) || $statusFilter;
                        $rBase = ['tab' => 'riwayat', 'page' => null];

                        // Class ditulis lengkap agar terdeteksi Tailwind
                        $chipOn = [
                            'indigo' => 'border-indigo-500 bg-indigo-500 text-white',
                            'emerald' => 'border-emerald-500 bg-emerald-500 text-white',
                            'rose' => 'border-rose-500 bg-rose-500 text-white',
                            'slate' => 'border-slate-500 bg-slate-500 text-white',
                        ];
                        $chipOff = [
                            'indigo' => 'border-slate-200 bg-white text-slate-600 hover:border-indigo-300 hover:text-indigo-600',
                            'emerald' => 'border-slate-200 bg-white text-slate-600 hover:border-emerald-300 hover:text-emerald-600',
                            'rose' => 'border-slate-200 bg-white text-slate-600 hover:border-rose-300 hover:text-rose-600',
                            'slate' => 'border-slate-200 bg-white text-slate-600 hover:border-slate-400 hover:text-slate-800',
                        ];
                        $chip = fn($active, $tone = 'indigo') => $active ? $chipOn[$tone] : $chipOff[$tone];
                    @endphp

                    {{-- Filter + pencarian --}}
                    <form method="GET"
                        class="flex flex-wrap items-center gap-3 border-b border-slate-100 px-4 py-3 sm:px-5">
                        <input type="hidden" name="tab" value="riwayat">
                        @if($statusFilter)<input type="hidden" name="status" value="{{ $statusFilter }}">@endif

                        <div class="flex flex-wrap items-center gap-1.5">
                            <a href="{{ request()->fullUrlWithQuery($rBase + ['status' => null]) }}"
                                class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold transition {{ $chip(!$statusFilter, 'indigo') }}">
                                Semua <span class="tabular-nums opacity-80">{{ $riwayatCounts->sum() }}</span>
                            </a>
                            <a href="{{ request()->fullUrlWithQuery($rBase + ['status' => 'APPROVED']) }}"
                                class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold transition {{ $chip($statusFilter === 'APPROVED', 'emerald') }}">
                                Disetujui <span class="tabular-nums opacity-80">{{ $riwayatCounts['APPROVED'] ?? 0 }}</span>
                            </a>
                            <a href="{{ request()->fullUrlWithQuery($rBase + ['status' => 'REJECTED']) }}"
                                class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold transition {{ $chip($statusFilter === 'REJECTED', 'rose') }}">
                                Ditolak <span class="tabular-nums opacity-80">{{ $riwayatCounts['REJECTED'] ?? 0 }}</span>
                            </a>
                            <a href="{{ request()->fullUrlWithQuery($rBase + ['status' => 'CANCELLED']) }}"
                                class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold transition {{ $chip($statusFilter === 'CANCELLED', 'slate') }}">
                                Dibatalkan <span
                                    class="tabular-nums opacity-80">{{ $riwayatCounts['CANCELLED'] ?? 0 }}</span>
                            </a>
                        </div>

                        <div class="flex w-full flex-wrap items-center gap-2 sm:ml-auto sm:w-auto">
                            @if(!$roomId)
                                <select name="room_id" onchange="this.form.submit()" aria-label="Ruangan"
                                    class="h-9 rounded-lg border border-slate-200 bg-white py-0 pl-3 pr-8 text-[13px] font-medium text-slate-700 focus:border-indigo-400 focus:ring-4 focus:ring-indigo-500/10">
                                    <option value="">Semua ruangan</option>
                                    @foreach($rooms as $r)
                                        <option value="{{ $r->id }}" @selected(request('room_id') == $r->id)>{{ $r->name }}</option>
                                    @endforeach
                                </select>
                            @endif
                            <input type="date" name="from" value="{{ request('from') }}" onchange="this.form.submit()"
                                aria-label="Dari tanggal"
                                class="h-9 rounded-lg border border-slate-200 bg-white py-0 text-[13px] font-medium text-slate-700 focus:border-indigo-400 focus:ring-4 focus:ring-indigo-500/10">
                            <span class="text-xs text-slate-400">–</span>
                            <input type="date" name="to" value="{{ request('to') }}" onchange="this.form.submit()"
                                aria-label="Sampai tanggal"
                                class="h-9 rounded-lg border border-slate-200 bg-white py-0 text-[13px] font-medium text-slate-700 focus:border-indigo-400 focus:ring-4 focus:ring-indigo-500/10">

                            <div class="relative w-full sm:w-64">
                                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                                    aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z" />
                                </svg>
                                <input type="text" name="q" value="{{ request('q') }}" autocomplete="off"
                                    aria-label="Cari riwayat" placeholder="Cari judul, pemohon, unit kerja…"
                                    class="h-9 w-full rounded-lg border border-slate-200 bg-white py-0 pl-9 pr-3 text-[13px] font-medium text-slate-700 placeholder-slate-400 focus:border-indigo-400 focus:ring-4 focus:ring-indigo-500/10">
                            </div>
                            @if($hasRFilter)
                                <a href="{{ route('approvals.index', ['tab' => 'riwayat']) }}"
                                    class="text-xs font-semibold text-slate-500 underline-offset-2 hover:text-indigo-600 hover:underline">Reset</a>
                            @endif
                        </div>
                    </form>

                    @if($riwayat->isEmpty())
                        <div class="px-4 py-14 text-center">
                            <span
                                class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                {!! $svg('clipboard', 'h-6 w-6', '1.6') !!}
                            </span>
                            <div class="mt-3 text-sm font-bold text-slate-800">
                                {{ $hasRFilter ? 'Tidak ada riwayat yang cocok' : 'Belum ada riwayat' }}
                            </div>
                            <p class="mx-auto mt-1 max-w-sm text-xs text-slate-500">
                                {{ $hasRFilter ? 'Ubah filter atau kata kunci pencarian.' : 'Belum ada tindakan approval atau penolakan yang dilakukan.' }}
                            </p>
                        </div>
                    @else
                        {{-- Header kolom --}}
                        <div
                            class="hidden gap-4 border-b border-slate-100 bg-slate-50/70 px-5 py-2 text-[11px] font-semibold uppercase tracking-wide text-slate-400 xl:grid {{ $rCols }}">
                            <span>Waktu</span>
                            <span>Rapat</span>
                            <span>Pemohon</span>
                            <span>Diputuskan</span>
                            <span>Status</span>
                            <span class="text-right">Aksi</span>
                        </div>

                        {{-- Per hari --}}
                        @foreach($riwayatDays as $dateKey => $dayItems)
                            @php
                                $dDate = Carbon::parse($dateKey);
                                $dayStat = $dayItems
                                    ->groupBy(fn($x) => strtoupper($x->status) === 'CANCELED' ? 'CANCELLED' : strtoupper($x->status))
                                    ->map->count();
                            @endphp

                            <section
                                x-data="{ open: false, get expanded() { return this.open || {{ $hasRFilter ? 'true' : 'false' }}; } }"
                                class="{{ !$loop->first ? 'border-t border-slate-100' : '' }}">
                                {{-- Header hari --}}
                                <button type="button" @click="open = !expanded" :aria-expanded="expanded"
                                    class="flex w-full items-center gap-2.5 border-b border-slate-100 bg-slate-50/80 px-4 py-2.5 text-left transition hover:bg-slate-100/70 sm:px-5">
                                    <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform"
                                        :class="expanded ? 'rotate-90' : ''" fill="none" stroke="currentColor" stroke-width="2.2"
                                        stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="m9 18 6-6-6-6" />
                                    </svg>
                                    <span class="text-sm font-bold text-slate-800">{{ $dDate->translatedFormat('l, d F Y') }}</span>
                                    @if($dDate->isToday())
                                        <span class="rounded bg-indigo-100 px-1.5 py-0.5 text-[11px] font-semibold text-indigo-700">Hari
                                            ini</span>
                                    @elseif($dDate->isYesterday())
                                        <span
                                            class="rounded bg-sky-100 px-1.5 py-0.5 text-[11px] font-semibold text-sky-700">Kemarin</span>
                                    @endif

                                    <span class="ml-auto flex items-center gap-2">
                                        @if($dayStat->get('APPROVED'))
                                            <span
                                                class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-bold text-emerald-700">{{ $dayStat['APPROVED'] }}
                                                disetujui</span>
                                        @endif
                                        @if($dayStat->get('REJECTED'))
                                            <span
                                                class="inline-flex items-center rounded-full bg-rose-100 px-2 py-0.5 text-[11px] font-bold text-rose-700">{{ $dayStat['REJECTED'] }}
                                                ditolak</span>
                                        @endif
                                        @if($dayStat->get('CANCELLED'))
                                            <span
                                                class="inline-flex items-center rounded-full bg-slate-200 px-2 py-0.5 text-[11px] font-bold text-slate-600">{{ $dayStat['CANCELLED'] }}
                                                batal</span>
                                        @endif
                                        <span class="hidden text-xs text-slate-400 sm:inline">{{ $dayItems->count() }}
                                            booking</span>
                                    </span>
                                </button>

                                <div x-show="expanded" x-cloak>
                                    @foreach($dayItems as $b)
                                        @php
                                            $bStart = Carbon::parse($b->start_at);
                                            $bEnd = Carbon::parse($b->end_at);
                                            $rc = $roomColors[$b->room_id ?? 0] ?? '#6366f1';
                                            $picName = $b->pic?->name ?? '-';
                                            $picInit = mb_strtoupper(mb_substr($picName, 0, 1));
                                            $durMin = (int) $bStart->diffInMinutes($bEnd);
                                            $decided = Carbon::parse($b->updated_at);
                                            [$label, $badge, $dot] = $statusMap[strtoupper($b->status)] ?? [Str::title(strtolower($b->status)), 'bg-slate-100 text-slate-500 ring-slate-400/20', 'bg-slate-400'];
                                            $dim = in_array(strtoupper($b->status), ['REJECTED', 'CANCELLED', 'CANCELED'], true);
                                        @endphp

                                        <div
                                            class="group relative grid gap-x-4 gap-y-2 border-b border-slate-100 px-4 py-3.5 transition last:border-b-0 hover:bg-slate-50/70 sm:px-5 xl:items-center {{ $rCols }}">
                                            <span class="absolute inset-y-0 left-0 w-[3px]" style="background: {{ $rc }}"
                                                aria-hidden="true"></span>

                                            {{-- Waktu --}}
                                            <div class="{{ $dim ? 'opacity-70' : '' }}">
                                                <div class="text-sm font-extrabold tabular-nums leading-tight text-slate-900">
                                                    {{ $bStart->format('H.i') }}
                                                    <span class="font-semibold text-slate-400">–</span>
                                                    {{ $bEnd->format('H.i') }}
                                                </div>
                                                <div class="mt-0.5 text-xs text-slate-400">{{ $fmtDur($durMin) }}</div>
                                            </div>

                                            {{-- Rapat --}}
                                            <div class="min-w-0 {{ $dim ? 'opacity-70' : '' }}">
                                                <div class="truncate text-sm font-semibold text-slate-900" title="{{ $b->title }}">
                                                    {{ $b->title }}</div>
                                                <div class="mt-1 flex min-w-0 flex-wrap items-center gap-2 text-xs text-slate-500">
                                                    @if(!$roomId)
                                                        <span class="inline-flex shrink-0 items-center gap-1.5 font-medium text-slate-600">
                                                            <span class="h-2 w-2 rounded-full"
                                                                style="background: {{ $rc }}"></span>{{ $b->room?->name ?? 'Ruang Tidak Diketahui' }}
                                                        </span>
                                                    @endif
                                                    @if($b->unit_kerja)
                                                        <span
                                                            class="inline-flex min-w-0 max-w-[65%] shrink-0 items-center gap-1.5 rounded-md bg-indigo-50 px-2 py-0.5 text-[11px] font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-100"
                                                            title="{{ $b->unit_kerja }}">
                                                            {!! $svg('building', 'h-3 w-3 shrink-0', '2') !!}
                                                            <span class="truncate">{{ $b->unit_kerja }}</span>
                                                        </span>
                                                    @endif
                                                </div>
                                                @if($b->tu_note)
                                                    <div
                                                        class="mt-1.5 inline-flex max-w-full items-start gap-1.5 rounded-md bg-slate-50 px-2 py-1 text-[11px] text-slate-500 ring-1 ring-inset ring-slate-200">
                                                        <span class="shrink-0 font-semibold">Catatan:</span>
                                                        <span class="min-w-0">{{ $b->tu_note }}</span>
                                                    </div>
                                                @endif
                                            </div>

                                            {{-- Pemohon --}}
                                            <div class="flex min-w-0 items-center gap-2.5 {{ $dim ? 'opacity-70' : '' }}">
                                                <span
                                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-600">{{ $picInit }}</span>
                                                <span class="truncate text-[13px] font-semibold text-slate-800"
                                                    title="{{ $picName }}">{{ $picName }}</span>
                                            </div>

                                            {{-- Diputuskan --}}
                                            <div class="text-xs" title="{{ $decided->translatedFormat('d M Y, H.i') }}">
                                                <div class="font-medium text-slate-600">{{ $decided->diffForHumans() }}</div>
                                            </div>

                                            {{-- Status --}}
                                            <div>
                                                <span
                                                    class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $badge }}">
                                                    <span class="h-1.5 w-1.5 rounded-full {{ $dot }}"></span>{{ $label }}
                                                </span>
                                            </div>

                                            {{-- Aksi --}}
                                            @if($b->status === 'APPROVED')
                                                <div x-data="{ openCancel: false }" class="flex xl:justify-end">
                                                    <button type="button" @click="openCancel = true" title="Batalkan approval"
                                                        class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-lg border border-orange-200 bg-orange-50 px-3 py-1.5 text-xs font-semibold text-orange-700 transition hover:border-orange-600 hover:bg-orange-600 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-500/40">
                                                        {!! $svg('undo', 'h-3.5 w-3.5', '2.2') !!}
                                                        Batalkan
                                                    </button>

                                                    <div x-show="openCancel" x-cloak @keydown.escape.window="openCancel = false"
                                                        class="fixed inset-0 z-50 flex items-center justify-center p-4">
                                                        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"
                                                            @click="openCancel = false"></div>

                                                        <div x-show="openCancel" x-transition
                                                            class="relative flex w-full max-w-md flex-col overflow-hidden rounded-2xl bg-white text-left shadow-2xl"
                                                            role="dialog" aria-modal="true">
                                                            <div class="flex items-start justify-between gap-3 px-5 pb-2 pt-5">
                                                                <div class="flex min-w-0 items-center gap-3">
                                                                    <span
                                                                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange-50 text-orange-500">
                                                                        {!! $svg('undo', 'h-5 w-5') !!}
                                                                    </span>
                                                                    <div class="min-w-0">
                                                                        <h3 class="text-base font-extrabold text-slate-900">Batalkan
                                                                            approval</h3>
                                                                        <p class="mt-0.5 truncate text-xs text-slate-500">{{ $b->title }}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                                <button type="button" @click="openCancel = false" aria-label="Tutup"
                                                                    class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                                                                    {!! $svg('x', 'h-5 w-5', '2') !!}
                                                                </button>
                                                            </div>

                                                            <form method="POST" action="{{ route('approvals.cancelApprove', $b) }}">
                                                                @csrf
                                                                <div class="px-5 py-4">
                                                                    <div
                                                                        class="mb-4 flex gap-2.5 rounded-lg border border-orange-200 bg-orange-50 p-3 text-xs text-orange-800">
                                                                        {!! $svg('alert', 'h-4 w-4 shrink-0 mt-0.5', '2') !!}
                                                                        <p>Status booking ini akan diubah menjadi
                                                                            <strong class="font-extrabold">CANCELLED</strong>. Pengaju
                                                                            perlu membuat ulang jika masih diperlukan.
                                                                        </p>
                                                                    </div>
                                                                    <label class="mb-1.5 block text-xs font-bold text-slate-600">Alasan
                                                                        (opsional)</label>
                                                                    <textarea name="tu_note" rows="3"
                                                                        placeholder="Tulis alasan pembatalan approval..."
                                                                        class="w-full resize-none rounded-lg border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-orange-400 focus:bg-white focus:ring-4 focus:ring-orange-500/10"></textarea>
                                                                </div>
                                                                <div
                                                                    class="flex justify-end gap-2 border-t border-slate-100 bg-slate-50/60 px-5 py-3.5">
                                                                    <button type="button" @click="openCancel = false"
                                                                        class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-[13px] font-semibold text-slate-700 transition hover:bg-slate-50">Tutup</button>
                                                                    <button type="submit"
                                                                        class="rounded-lg bg-orange-600 px-4 py-2 text-[13px] font-semibold text-white shadow-sm shadow-orange-600/25 transition hover:bg-orange-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-500 focus-visible:ring-offset-2">Ya,
                                                                        batalkan approval</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="hidden xl:block"></div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        @endforeach

                        <div
                            class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 bg-slate-50/40 px-4 py-3 sm:px-5">
                            <span class="text-xs text-slate-400">
                                Menampilkan {{ $riwayat->firstItem() }}–{{ $riwayat->lastItem() }} dari
                                {{ $riwayat->total() }} keputusan
                            </span>
                            @if($riwayat->hasPages())
                                <div class="w-full sm:w-auto">{{ $riwayat->links() }}</div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-app-layout>