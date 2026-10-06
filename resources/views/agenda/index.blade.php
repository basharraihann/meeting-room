@use('Carbon\Carbon')
@use('Illuminate\Support\Str')

<x-app-layout>
    @php
        $mode = $mode ?? 'day';
        $isRange = in_array($mode, ['week', 'month']);
        $grouped = $isRange
            ? $bookings->groupBy(fn($b) => Carbon::parse($b->start_at)->toDateString())
            : collect([Carbon::parse($date)->toDateString() => $bookings]);

        $roomColors = [1 => '#1a1a1a', 2 => '#a855f7', 3 => '#92400e', 4 => '#facc15', 5 => '#22d3ee', 6 => '#ef4444', 7 => '#ec4899', 8 => '#468432'];

        $periods = ['day' => 'Hari ini', 'week' => 'Minggu ini', 'month' => 'Bulan ini'];

        $statusMap = [
            'APPROVED' => ['Disetujui', 'bg-emerald-50 text-emerald-700 ring-emerald-600/15', 'bg-emerald-500'],
            'PENDING' => ['Menunggu', 'bg-amber-50 text-amber-700 ring-amber-600/15', 'bg-amber-500'],
            'REJECTED' => ['Ditolak', 'bg-red-50 text-red-700 ring-red-600/15', 'bg-red-500'],
        ];

        $today = now()->toDateString();
        $nowTs = now();
        $total = $bookings->count();
        $approved = $bookings->where('status', 'APPROVED')->count();
        $pending = $bookings->where('status', 'PENDING')->count();
        $rejected = $bookings->where('status', 'REJECTED')->count();

        $cols = 'sm:grid-cols-[104px_minmax(0,1fr)_200px_112px]';
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
                <p class="mt-0.5 text-xs text-slate-500">Pantau jadwal rapat unit kerja Anda dan salin ringkasannya untuk dibagikan.</p>
            </div>

            <div class="relative z-10 flex shrink-0 items-center gap-2">
                <a href="{{ route('calendar') }}"
                    class="hidden rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-[13px] font-semibold text-slate-700 transition hover:bg-slate-50 sm:inline-flex">
                    Kalender
                </a>
                <button type="button" id="copy-btn" onclick="copyAgenda()"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3.5 py-2 text-[13px] font-semibold text-white shadow-sm shadow-indigo-600/20 transition hover:bg-indigo-700">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    <span id="copy-label">Salin agenda</span>
                </button>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">

            {{-- Toolbar --}}
            <div class="space-y-3 border-b border-slate-100 bg-slate-50/50 px-4 py-3.5 sm:px-5">
                <form method="GET" action="{{ route('agenda') }}" class="flex flex-wrap items-center gap-2.5">
                    <input type="hidden" name="mode" value="{{ $mode }}">
                    @if($unitKerja)
                        <input type="hidden" name="unit_kerja" value="{{ $unitKerja }}">
                    @endif

                    <div class="flex rounded-lg bg-slate-200/60 p-0.5">
                        @foreach($periods as $key => $lbl)
                            <a href="{{ route('agenda', ['mode' => $key, 'date' => $today, 'unit_kerja' => $unitKerja]) }}"
                                class="rounded-md px-3.5 py-1.5 text-[13px] font-semibold transition {{ $mode === $key ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                                {{ $lbl }}
                            </a>
                        @endforeach
                    </div>

                    <div class="flex items-center gap-2 sm:ml-auto">
                        <input type="date" name="date" value="{{ $date }}" aria-label="Pilih tanggal"
                            class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[13px] text-slate-700 focus:border-indigo-400 focus:ring-4 focus:ring-indigo-500/10">
                        <button type="submit"
                            class="rounded-lg border border-slate-200 bg-white px-3.5 py-1.5 text-[13px] font-semibold text-slate-700 transition hover:bg-slate-50">
                            Lihat
                        </button>
                    </div>
                </form>

                @if($unitKerjaOptions->isNotEmpty())
                    <div class="flex items-center gap-1.5 overflow-x-auto">
                        <span class="mr-1 shrink-0 text-xs text-slate-400">Unit kerja:</span>
                        <a href="{{ route('agenda', ['mode' => $mode, 'date' => $date]) }}"
                            class="shrink-0 rounded-full px-3 py-1 text-xs font-semibold transition {{ !$unitKerja ? 'bg-slate-800 text-white' : 'bg-white text-slate-600 ring-1 ring-inset ring-slate-200 hover:bg-slate-50' }}">
                            Semua
                        </a>
                        @foreach($unitKerjaOptions as $uk)
                            <a href="{{ route('agenda', ['mode' => $mode, 'date' => $date, 'unit_kerja' => $uk]) }}"
                                class="shrink-0 rounded-full px-3 py-1 text-xs font-semibold transition {{ $unitKerja === $uk ? 'bg-slate-800 text-white' : 'bg-white text-slate-600 ring-1 ring-inset ring-slate-200 hover:bg-slate-50' }}">
                                {{ $uk }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Ringkasan periode --}}
            <div class="flex flex-wrap items-center justify-between gap-x-6 gap-y-1 px-4 py-3.5 sm:px-5">
                <div>
                    <h2 class="text-[15px] font-bold text-slate-900">
                        {{ $title ?? Carbon::parse($date)->translatedFormat('l, d F Y') }}
                    </h2>
                </div>
                @if($total)
                    <div class="flex items-center gap-4 text-xs text-slate-500">
                        <span class="font-semibold text-slate-700">{{ $total }} rapat</span>
                        <span class="inline-flex items-center gap-1.5"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>{{ $approved }} disetujui</span>
                        <span class="inline-flex items-center gap-1.5"><span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>{{ $pending }} menunggu</span>
                        @if($rejected)
                            <span class="inline-flex items-center gap-1.5"><span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>{{ $rejected }} ditolak</span>
                        @endif
                    </div>
                @endif
            </div>

            @if($bookings->isEmpty())
                <div class="border-t border-slate-100 py-14 text-center">
                    <div class="text-sm font-bold text-slate-800">Tidak ada rapat pada periode ini</div>
                    <p class="mt-1 text-xs text-slate-500">Coba pilih tanggal lain atau ubah periode.</p>
                </div>
            @else
                {{-- Header kolom --}}
                <div class="hidden grid-cols-1 gap-4 border-y border-slate-100 bg-slate-50/70 px-5 py-2 text-xs font-semibold text-slate-400 sm:grid {{ $cols }}">
                    <span>Waktu</span>
                    <span>Rapat</span>
                    <span>Ruangan</span>
                    <span class="text-right">Status</span>
                </div>

                @foreach($grouped as $day => $items)
                    @php $isToday = $day === $today; @endphp

                    @if($isRange)
                        <div class="flex items-center gap-2 border-b border-slate-100 bg-slate-50/40 px-4 py-2 sm:px-5">
                            <span class="text-xs font-bold {{ $isToday ? 'text-indigo-700' : 'text-slate-600' }}">
                                {{ Carbon::parse($day)->translatedFormat('l, d F Y') }}
                            </span>
                            @if($isToday)
                                <span class="rounded bg-indigo-100 px-1.5 py-0.5 text-[11px] font-semibold text-indigo-700">Hari ini</span>
                            @endif
                            <span class="ml-auto text-xs text-slate-400">{{ $items->count() }} rapat</span>
                        </div>
                    @endif

                    <div class="divide-y divide-slate-100">
                        @foreach($items as $b)
                            @php
                                $c = $roomColors[$b->room_id ?? 0] ?? '#cbd5e1';
                                [$sLabel, $sCls, $sDot] = $statusMap[$b->status] ?? ['Dibatalkan', 'bg-slate-100 text-slate-500 ring-slate-400/20', 'bg-slate-400'];
                                $inactive = !isset($statusMap[$b->status]) || $b->status === 'REJECTED';
                                $start = Carbon::parse($b->start_at);
                                $end = Carbon::parse($b->end_at);
                                $live = !$inactive && $nowTs->between($start, $end);
                            @endphp
                            <div class="relative grid grid-cols-1 gap-x-4 gap-y-1.5 px-4 py-3 transition hover:bg-slate-50/70 sm:items-center sm:px-5 {{ $cols }} {{ $live ? 'bg-indigo-50/40' : '' }}">
                                <span class="absolute inset-y-0 left-0 w-[3px]" style="background: {{ $c }}" aria-hidden="true"></span>

                                <div class="{{ $inactive ? 'opacity-50' : '' }}">
                                    <div class="text-[13px] font-bold tabular-nums text-slate-900">{{ $start->format('H.i') }} – {{ $end->format('H.i') }}</div>
                                    @if($isRange)
                                        <div class="text-xs text-slate-400">{{ $start->translatedFormat('D, j M') }}</div>
                                    @endif
                                </div>

                                <div class="min-w-0 {{ $inactive ? 'opacity-50' : '' }}">
                                    <div class="flex items-center gap-2">
                                        <span class="truncate text-[13px] font-semibold text-slate-900 {{ $b->status === 'REJECTED' ? 'line-through decoration-slate-300' : '' }}">{{ $b->title }}</span>
                                        @if($live)
                                            <span class="shrink-0 rounded bg-indigo-600 px-1.5 py-0.5 text-[10px] font-semibold text-white">Berlangsung</span>
                                        @endif
                                    </div>
                                    @if($b->unit_kerja || $b->description)
                                        <div class="mt-0.5 truncate text-xs text-slate-500">
                                            {{ $b->unit_kerja }}@if($b->unit_kerja && $b->description) · @endif{{ Str::limit($b->description, 70) }}
                                        </div>
                                    @endif
                                </div>

                                <div class="flex items-center gap-2 text-xs text-slate-600 {{ $inactive ? 'opacity-50' : '' }}">
                                    <span class="h-2.5 w-2.5 shrink-0 rounded-full ring-1 ring-slate-300/70" style="background: {{ $c }}"></span>
                                    <span class="truncate">{{ $b->room?->name ?? '-' }}</span>
                                </div>

                                <div class="sm:text-right">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $sCls }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $sDot }}"></span>{{ $sLabel }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
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