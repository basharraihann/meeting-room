@use('Carbon\Carbon')
@use('Illuminate\Support\Str')

<x-app-layout>
    @php
        $mode = in_array($mode ?? 'day', ['day', 'week', 'month']) ? $mode : 'day';
        $isRange = $mode !== 'day';
        $d = Carbon::parse($date);
        $today = now()->toDateString();
        $nowTs = now();

        $grouped = $isRange
            ? $bookings->groupBy(fn($b) => Carbon::parse($b->start_at)->toDateString())
            : collect([$d->toDateString() => $bookings]);

        // Warna ruangan dari kolom rooms.color (sama seperti dashboard & kalender)
        $roomColors = \App\Models\Room::pluck('color', 'id')->filter()->all();

        $periods = ['day' => 'Hari', 'week' => 'Minggu', 'month' => 'Bulan'];

        // Judul periode (bahasa Indonesia)
        $heading = match ($mode) {
            'week' => $d->copy()->startOfWeek()->translatedFormat('d M') . ' – ' . $d->copy()->endOfWeek()->translatedFormat('d M Y'),
            'month' => $d->translatedFormat('F Y'),
            default => $d->translatedFormat('l, d F Y'),
        };

        // Navigasi sebelumnya / berikutnya mengikuti mode
        [$prevDate, $nextDate] = match ($mode) {
            'week' => [$d->copy()->subWeek(), $d->copy()->addWeek()],
            'month' => [$d->copy()->subMonthNoOverflow(), $d->copy()->addMonthNoOverflow()],
            default => [$d->copy()->subDay(), $d->copy()->addDay()],
        };

        // Helper URL: pertahankan mode, tanggal, dan unit kerja
        $link = fn(array $over = []) => route('agenda', array_filter(
            array_merge(['mode' => $mode, 'date' => $date, 'unit_kerja' => $unitKerja], $over),
            fn($v) => filled($v)
        ));

        // [label, kelas badge, kelas titik]
        $statusMap = [
            'APPROVED' => ['Disetujui', 'bg-emerald-50 text-emerald-700 ring-emerald-600/20', 'bg-emerald-500'],
            'PENDING' => ['Menunggu', 'bg-amber-50 text-amber-700 ring-amber-600/20', 'bg-amber-500'],
            'REJECTED' => ['Ditolak', 'bg-rose-50 text-rose-700 ring-rose-600/20', 'bg-rose-500'],
            'CANCELLED' => ['Dibatalkan', 'bg-slate-100 text-slate-500 ring-slate-400/20', 'bg-slate-400'],
        ];

        $total = $bookings->count();
        $approved = $bookings->where('status', 'APPROVED')->count();
        $pending = $bookings->where('status', 'PENDING')->count();

        // Ikon gedung untuk unit kerja (SVG inline, warna mengikuti teks)
        $iconBuilding = '<svg class="%s" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4M10 10h4M10 14h4M10 18h4"/></svg>';
        $building = fn(string $cls) => sprintf($iconBuilding, $cls);

        $fmtDur = fn($m) => trim(($m >= 60 ? intdiv($m, 60) . ' j ' : '') . ($m % 60 ? ($m % 60) . ' m' : ''));

        // Mobile: waktu + status di baris atas. Desktop: 4 kolom.
        $cols = 'grid-cols-[1fr_auto] sm:grid-cols-[112px_minmax(0,1fr)_200px_124px]';
    @endphp

    <div class="space-y-4 px-4 pb-8 pt-4 sm:px-8">

        {{-- ===== BANNER JUDUL ===== --}}
        <div
            class="relative flex items-center gap-3 overflow-hidden rounded-xl border border-white/70 bg-white/80 px-4 py-3 shadow-sm backdrop-blur sm:gap-4">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-500">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M8 7V3m8 4V3M5 11h14M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z" />
                </svg>
            </span>
            <div class="min-w-0 flex-1">
                <h1 class="text-base font-extrabold leading-tight text-[#0f1e5a] sm:text-lg">Agenda Saya</h1>
                <p class="mt-0.5 text-xs text-slate-500">Jadwal rapat yang Anda ajukan. Salin ringkasannya untuk
                    dibagikan.</p>
            </div>

            <div class="relative z-10 flex shrink-0 items-center gap-2">
                <a href="{{ route('calendar') }}"
                    class="hidden rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-[13px] font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 sm:inline-flex">
                    Kalender
                </a>
                <button type="button" id="copy-btn" onclick="copyAgenda()"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3.5 py-2 text-[13px] font-semibold text-white shadow-sm shadow-indigo-600/25 transition hover:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    <span id="copy-label">Salin agenda</span>
                </button>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">

            {{-- ===== TOOLBAR ===== --}}
            <div class="flex flex-wrap items-center gap-3 border-b border-slate-100 bg-slate-50/60 px-4 py-3.5 sm:px-5">

                {{-- Periode --}}
                <div class="flex rounded-lg bg-slate-200/60 p-0.5" role="tablist" aria-label="Periode">
                    @foreach($periods as $key => $lbl)
                        <a href="{{ $link(['mode' => $key]) }}" role="tab"
                            aria-selected="{{ $mode === $key ? 'true' : 'false' }}"
                            class="rounded-md px-4 py-1.5 text-[13px] font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 {{ $mode === $key ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                            {{ $lbl }}
                        </a>
                    @endforeach
                </div>

                {{-- Tanggal + unit kerja (satu form, otomatis terkirim saat berubah) --}}
                <form method="GET" action="{{ route('agenda') }}"
                    class="flex w-full flex-wrap items-center gap-2 sm:ml-auto sm:w-auto">
                    <input type="hidden" name="mode" value="{{ $mode }}">

                    <div class="flex items-center gap-1">
                        <a href="{{ $link(['date' => $prevDate->toDateString()]) }}" aria-label="Sebelumnya"
                            class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-slate-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>
                        <input type="date" name="date" value="{{ $date }}" aria-label="Pilih tanggal"
                            onchange="this.form.submit()"
                            class="h-9 rounded-lg border border-slate-200 bg-white px-3 py-0 text-[13px] font-medium text-slate-700 focus:border-indigo-400 focus:ring-4 focus:ring-indigo-500/10">
                        <a href="{{ $link(['date' => $nextDate->toDateString()]) }}" aria-label="Berikutnya"
                            class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-slate-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>

                    @if($date !== $today)
                        <a href="{{ $link(['date' => $today]) }}"
                            class="h-9 rounded-lg px-3 text-[13px] font-semibold leading-9 text-indigo-600 transition hover:bg-indigo-50 hover:text-indigo-800">
                            Hari ini
                        </a>
                    @endif

                    @if($unitKerjaOptions->isNotEmpty())
                        <div class="relative w-full sm:w-64">
                            <span
                                class="pointer-events-none absolute inset-y-0 left-3 flex items-center {{ $unitKerja ? 'text-indigo-600' : 'text-slate-400' }}">
                                {!! $building('h-4 w-4') !!}
                            </span>
                            <select name="unit_kerja" onchange="this.form.submit()" aria-label="Filter unit kerja"
                                class="h-9 w-full rounded-lg border bg-white py-0 pl-9 pr-9 text-[13px] font-medium focus:border-indigo-400 focus:ring-4 focus:ring-indigo-500/10 {{ $unitKerja ? 'border-indigo-300 text-indigo-700' : 'border-slate-200 text-slate-700' }}">
                                <option value="">Semua unit kerja</option>
                                @foreach($unitKerjaOptions as $uk)
                                    <option value="{{ $uk }}" @selected($unitKerja === $uk)>{{ $uk }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </form>
            </div>

            {{-- ===== RINGKASAN PERIODE ===== --}}
            <div class="flex flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-5">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-lg font-extrabold leading-tight text-slate-900">{{ $heading }}</h2>
                        @if(!$isRange && $date === $today)
                            <span class="rounded-md bg-indigo-100 px-2 py-0.5 text-[11px] font-bold text-indigo-700">Hari
                                ini</span>
                        @endif
                    </div>
                    @if($unitKerja)
                        <p class="mt-1.5 inline-flex max-w-full items-center gap-1.5 text-xs text-slate-500">
                            <span class="text-indigo-500">{!! $building('h-3.5 w-3.5 shrink-0') !!}</span>
                            <span class="truncate font-semibold text-slate-700">{{ $unitKerja }}</span>
                        </p>
                    @endif
                </div>

                <div class="flex items-stretch gap-2">
                    <div class="min-w-[76px] rounded-xl bg-slate-50 px-3.5 py-2 ring-1 ring-inset ring-slate-200/70">
                        <div class="text-lg font-extrabold leading-none tabular-nums text-slate-900">{{ $total }}</div>
                        <div class="mt-1 text-[11px] font-medium text-slate-500">Total rapat</div>
                    </div>
                    <div
                        class="min-w-[76px] rounded-xl bg-emerald-50/70 px-3.5 py-2 ring-1 ring-inset ring-emerald-200/70">
                        <div class="text-lg font-extrabold leading-none tabular-nums text-emerald-700">{{ $approved }}
                        </div>
                        <div class="mt-1 text-[11px] font-medium text-emerald-700/80">Disetujui</div>
                    </div>
                    <div class="min-w-[76px] rounded-xl bg-amber-50/70 px-3.5 py-2 ring-1 ring-inset ring-amber-200/70">
                        <div class="text-lg font-extrabold leading-none tabular-nums text-amber-700">{{ $pending }}
                        </div>
                        <div class="mt-1 text-[11px] font-medium text-amber-700/80">Menunggu</div>
                    </div>
                </div>
            </div>

            @if($bookings->isEmpty())
                {{-- ===== KOSONG ===== --}}
                <div class="border-t border-slate-100 px-4 py-14 text-center">
                    <span
                        class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path
                                d="M8 7V3m8 4V3M5 11h14M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z" />
                            <path d="M9 16l2 2 4-4" />
                        </svg>
                    </span>
                    <div class="mt-3 text-sm font-bold text-slate-800">
                        {{ $unitKerja ? 'Tidak ada rapat untuk unit kerja ini' : 'Belum ada rapat pada periode ini' }}
                    </div>
                    <p class="mx-auto mt-1 max-w-sm text-xs text-slate-500">Pilih tanggal atau periode lain, atau ajukan
                        rapat baru lewat kalender.</p>
                    <div class="mt-4 flex items-center justify-center gap-2">
                        @if($unitKerja)
                            <a href="{{ $link(['unit_kerja' => null]) }}"
                                class="rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-[13px] font-semibold text-slate-700 transition hover:bg-slate-50">Semua
                                unit</a>
                        @endif
                        <a href="{{ route('calendar') }}"
                            class="rounded-lg bg-indigo-600 px-3.5 py-2 text-[13px] font-semibold text-white shadow-sm shadow-indigo-600/25 transition hover:bg-indigo-700">Buka
                            Kalender</a>
                    </div>
                </div>
            @else
                {{-- Header kolom --}}
                <div
                    class="hidden gap-4 border-y border-slate-100 bg-slate-50/70 px-5 py-2 text-[11px] font-semibold uppercase tracking-wide text-slate-400 sm:grid {{ $cols }}">
                    <span>Waktu</span>
                    <span>Rapat</span>
                    <span>Ruangan</span>
                    <span class="text-right">Status</span>
                </div>

                @foreach($grouped as $day => $items)
                    @php $isToday = $day === $today; @endphp

                    @if($isRange)
                        <div
                            class="flex items-center gap-2 border-b border-slate-100 bg-slate-50/50 px-4 py-2 sm:px-5 {{ !$loop->first ? 'border-t' : '' }}">
                            <span class="text-xs font-bold {{ $isToday ? 'text-indigo-700' : 'text-slate-700' }}">
                                {{ Carbon::parse($day)->translatedFormat('l, d F Y') }}
                            </span>
                            @if($isToday)
                                <span class="rounded bg-indigo-100 px-1.5 py-0.5 text-[11px] font-semibold text-indigo-700">Hari
                                    ini</span>
                            @endif
                            <span class="ml-auto text-xs text-slate-400">{{ $items->count() }} rapat</span>
                        </div>
                    @endif

                    <div class="divide-y divide-slate-100">
                        @foreach($items as $b)
                            @php
                                $c = $roomColors[$b->room_id ?? 0] ?? '#6366f1';
                                $start = Carbon::parse($b->start_at);
                                $end = Carbon::parse($b->end_at);
                                $isActive = in_array($b->status, ['APPROVED', 'PENDING'], true);
                                $live = $isActive && $nowTs->between($start, $end);
                                $done = $b->status === 'APPROVED' && $end->lessThan($nowTs);

                                [$sLabel, $sCls, $sDot] = $statusMap[$b->status] ?? [Str::title(strtolower($b->status)), 'bg-slate-100 text-slate-500 ring-slate-400/20', 'bg-slate-400'];
                                if ($done) {
                                    [$sLabel, $sCls, $sDot] = ['Selesai', 'bg-slate-100 text-slate-500 ring-slate-400/20', 'bg-slate-400'];
                                }
                                if ($live) {
                                    [$sLabel, $sCls, $sDot] = ['Berlangsung', 'bg-emerald-50 text-emerald-700 ring-emerald-600/20', 'bg-emerald-500 animate-pulse'];
                                }
                                $dim = $done || !$isActive;
                                $durMin = (int) $start->diffInMinutes($end);
                            @endphp

                            <div
                                class="group relative grid gap-x-4 gap-y-2 px-4 py-3.5 transition hover:bg-slate-50/70 sm:items-center sm:px-5 {{ $cols }} {{ $live ? 'bg-emerald-50/40' : '' }}">
                                <span class="absolute inset-y-0 left-0 w-[3px]" style="background: {{ $c }}"
                                    aria-hidden="true"></span>

                                {{-- Waktu --}}
                                <div class="order-1 sm:order-none {{ $dim ? 'opacity-60' : '' }}">
                                    <div class="text-sm font-extrabold tabular-nums leading-tight text-slate-900">
                                        {{ $start->format('H.i') }}
                                        <span class="font-semibold text-slate-400">–</span>
                                        {{ $end->format('H.i') }}
                                    </div>
                                    <div class="mt-0.5 text-xs text-slate-400">
                                        @if($isRange){{ $start->translatedFormat('D, j M') }} · @endif{{ $fmtDur($durMin) }}
                                    </div>
                                </div>

                                {{-- Rapat --}}
                                <div class="order-3 col-span-2 min-w-0 sm:order-none sm:col-span-1 {{ $dim ? 'opacity-60' : '' }}">
                                    <div class="truncate text-sm font-semibold text-slate-900">{{ $b->title }}</div>
                                    @if($b->unit_kerja || $b->description)
                                        <div class="mt-1 flex min-w-0 items-center gap-2 text-xs text-slate-500">
                                            @if($b->unit_kerja)
                                                <span
                                                    class="inline-flex min-w-0 max-w-[65%] shrink-0 items-center gap-1.5 rounded-md bg-indigo-50 px-2 py-0.5 text-[11px] font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-100">
                                                    {!! $building('h-3 w-3 shrink-0') !!}
                                                    <span class="truncate">{{ $b->unit_kerja }}</span>
                                                </span>
                                            @endif
                                            @if($b->description)
                                                <span class="truncate">{{ Str::limit($b->description, 70) }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                {{-- Ruangan --}}
                                <div
                                    class="order-4 col-span-2 flex items-center gap-2 text-xs font-medium text-slate-600 sm:order-none sm:col-span-1 {{ $dim ? 'opacity-60' : '' }}">
                                    <span class="h-2.5 w-2.5 shrink-0 rounded-full ring-2 ring-white"
                                        style="background: {{ $c }}; box-shadow: 0 0 0 1px {{ $c }}55;"></span>
                                    <span class="truncate">{{ $b->room?->name ?? '-' }}</span>
                                </div>

                                {{-- Status --}}
                                <div class="order-2 justify-self-end sm:order-none">
                                    <span
                                        class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $sCls }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $sDot }}"></span>{{ $sLabel }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach

                <div class="flex items-center justify-between border-t border-slate-100 bg-slate-50/40 px-4 py-3 sm:px-5">
                    <span class="text-xs text-slate-400">{{ $total }} rapat ditampilkan</span>
                    <a href="{{ route('my_bookings.index') }}"
                        class="text-[13px] font-semibold text-indigo-600 hover:text-indigo-800">Semua pengajuan →</a>
                </div>
            @endif
        </div>
    </div>

    <textarea id="agendaText" class="hidden" aria-hidden="true">{{ $summaryText }}</textarea>

    <script>
        function copyAgenda() {
            const text = document.getElementById('agendaText').value
            const label = document.getElementById('copy-label')

            const done = () => {
                label.textContent = 'Tersalin ✓'
                setTimeout(() => label.textContent = 'Salin agenda', 2000)
            }

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(done)
            } else {
                const ta = document.getElementById('agendaText')
                ta.classList.remove('hidden'); ta.select(); document.execCommand('copy'); ta.classList.add('hidden')
                done()
            }
        }
    </script>
</x-app-layout>