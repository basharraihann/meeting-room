<x-app-layout>
    @php
        $mode = $mode ?? 'day';
        $isRange = in_array($mode, ['week', 'month']);
        $grouped = $isRange
            ? $bookings->groupBy(fn($b) => \Carbon\Carbon::parse($b->start_at)->toDateString())
            : collect([\Carbon\Carbon::parse($date)->toDateString() => $bookings]);

        $roomColors = [1 => '#1a1a1a', 2 => '#a855f7', 3 => '#92400e', 4 => '#facc15', 5 => '#22d3ee', 6 => '#ef4444', 7 => '#ec4899', 8 => '#468432'];

        $periods = ['day' => 'Hari ini', 'week' => 'Minggu ini', 'month' => 'Bulan ini'];

        $statusMap = [
            'APPROVED' => ['Disetujui', 'bg-emerald-50 text-emerald-700'],
            'PENDING' => ['Menunggu', 'bg-amber-50 text-amber-700'],
            'REJECTED' => ['Ditolak', 'bg-red-50 text-red-600'],
        ];
    @endphp

    <div class="space-y-6 px-4 py-8 sm:px-8">

        {{-- ===== Banner judul ===== --}}
        <div
            class="relative flex items-center gap-4 overflow-hidden rounded-2xl border border-white/70 bg-white/80 p-4 shadow-sm backdrop-blur sm:gap-5 sm:p-6">
            <span
                class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-500 sm:h-16 sm:w-16">
                <svg class="h-7 w-7 sm:h-8 sm:w-8" fill="none" stroke="currentColor" stroke-width="1.7"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </span>
            <div class="min-w-0 flex-1">
                <h1 class="text-xl font-extrabold text-[#0f1e5a] sm:text-2xl">Agenda Saya</h1>
                <p class="mt-1 text-sm text-slate-500">Pantau jadwal rapat unit kerja Anda dan salin ringkasannya untuk
                    dibagikan.</p>
            </div>

            <svg class="pointer-events-none absolute -bottom-2 right-2 hidden h-28 w-28 text-indigo-200/70 sm:block"
                viewBox="0 0 120 120" fill="currentColor" aria-hidden="true">
                <path d="M60 120C50 80 55 45 80 15c12 35 5 75-20 105z" />
                <path d="M58 120C35 100 25 70 35 40c25 15 33 50 23 80z" opacity=".7" />
                <path d="M62 120c20-15 38-20 55-12-12 18-35 24-55 12z" opacity=".6" />
            </svg>

            <a href="{{ route('calendar') }}"
                class="relative z-10 hidden shrink-0 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50 sm:inline-flex">
                ← Kalender
            </a>
        </div>

        {{-- ===== Filter tanggal + periode ===== --}}
        <div
            class="flex flex-wrap items-end justify-between gap-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('agenda') }}" class="flex flex-wrap items-end gap-3">
                <input type="hidden" name="mode" value="{{ $mode }}">
                @if($unitKerja)
                    <input type="hidden" name="unit_kerja" value="{{ $unitKerja }}">
                @endif

                <div>
                    <label
                        class="mb-1.5 block text-[11px] font-bold uppercase tracking-wider text-slate-400">Tanggal</label>
                    <input type="date" name="date" value="{{ $date }}"
                        class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
                </div>

                <button type="submit"
                    class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-600/25 transition hover:bg-indigo-700">
                    Lihat
                </button>

                <div class="flex rounded-full bg-slate-100 p-1">
                    @foreach($periods as $key => $lbl)
                        <a href="{{ route('agenda', ['mode' => $key, 'date' => now()->toDateString(), 'unit_kerja' => $unitKerja]) }}"
                            class="rounded-full px-4 py-1.5 text-sm font-semibold transition {{ $mode === $key ? 'bg-indigo-500 text-white shadow' : 'text-slate-600 hover:text-slate-900' }}">
                            {{ $lbl }}
                        </a>
                    @endforeach
                </div>
            </form>

            <button type="button" id="copy-btn" onclick="copyAgenda()"
                class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-500/25 transition hover:bg-emerald-600">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                <span id="copy-label">Copy agenda</span>
            </button>
        </div>

        {{-- ===== Filter unit kerja ===== --}}
        @if($unitKerjaOptions->isNotEmpty())
            <div
                class="flex flex-wrap items-center gap-2 rounded-2xl border border-slate-100 bg-white px-5 py-3.5 shadow-sm">
                <span class="mr-1 text-[11px] font-bold uppercase tracking-wider text-slate-400">Unit Kerja</span>
                <a href="{{ route('agenda', ['mode' => $mode, 'date' => $date]) }}"
                    class="rounded-full px-3.5 py-1.5 text-xs font-bold transition {{ !$unitKerja ? 'bg-indigo-500 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Semua
                </a>
                @foreach($unitKerjaOptions as $uk)
                    <a href="{{ route('agenda', ['mode' => $mode, 'date' => $date, 'unit_kerja' => $uk]) }}"
                        class="rounded-full px-3.5 py-1.5 text-xs font-bold transition {{ $unitKerja === $uk ? 'bg-indigo-500 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        {{ $uk }}
                    </a>
                @endforeach
            </div>
        @endif

        {{-- ===== Daftar agenda ===== --}}
        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 7V3m8 4V3M5 11h14M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-lg font-extrabold text-slate-900">Jadwal</h2>
                        <p class="text-xs text-slate-500">
                            {{ $title ?? \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</p>
                    </div>
                </div>
                @if($bookings->isNotEmpty())
                    <span
                        class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-600">{{ $bookings->count() }}
                        rapat</span>
                @endif
            </div>

            @if($bookings->isEmpty())
                <div class="flex flex-col items-center py-14 text-center">
                    <span class="flex h-16 w-16 items-center justify-center rounded-full bg-indigo-50 text-indigo-400">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 7V3m8 4V3M5 11h14M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z" />
                        </svg>
                    </span>
                    <div class="mt-4 text-base font-bold text-slate-800">Tidak ada rapat pada periode ini</div>
                    <p class="mt-1 text-sm text-slate-500">Coba pilih tanggal lain atau ubah periode.</p>
                </div>
            @else
                @foreach($grouped as $day => $items)
                    @if($isRange)
                        <div class="{{ $loop->first ? '' : 'mt-6' }} mb-2 flex items-center gap-3">
                            <span class="text-xs font-extrabold uppercase tracking-wider text-slate-600">
                                {{ \Carbon\Carbon::parse($day)->translatedFormat('l, d M Y') }}
                            </span>
                            <span class="h-px flex-1 bg-slate-100"></span>
                            <span class="text-xs text-slate-400">{{ $items->count() }} rapat</span>
                        </div>
                    @endif

                    <div class="space-y-2">
                        @foreach($items as $b)
                            @php
                                $c = $roomColors[$b->room_id ?? 0] ?? '#cbd5e1';
                                [$sLabel, $sCls] = $statusMap[$b->status] ?? ['Dibatalkan', 'bg-slate-100 text-slate-500'];
                            @endphp
                            <div
                                class="flex flex-wrap items-center gap-3 rounded-xl border border-slate-100 px-4 py-3.5 transition hover:border-indigo-200 hover:bg-indigo-50/30 sm:flex-nowrap sm:gap-4">
                                <span class="h-11 w-1 shrink-0 rounded" style="background: {{ $c }}"></span>

                                <span class="w-28 shrink-0 text-sm font-bold text-indigo-600">
                                    {{ \Carbon\Carbon::parse($b->start_at)->format('H.i') }} –
                                    {{ \Carbon\Carbon::parse($b->end_at)->format('H.i') }}
                                </span>

                                <div class="min-w-0 flex-1">
                                    <div class="text-sm font-bold leading-snug text-slate-900">{{ $b->title }}</div>
                                    <div class="mt-0.5 text-xs text-slate-500">
                                        {{ $b->room?->name ?? '-' }}
                                        @if($b->unit_kerja) · {{ $b->unit_kerja }} @endif
                                        @if($b->description) · {{ \Illuminate\Support\Str::limit($b->description, 60) }} @endif
                                    </div>
                                </div>

                                <span class="shrink-0 rounded-full px-3 py-1 text-xs font-bold {{ $sCls }}">{{ $sLabel }}</span>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <textarea id="agendaText" class="hidden">{{ $summaryText }}</textarea>

    <script>
        window.roomColors = @json($roomDotColors);
        function copyAgenda() {
            const text = document.getElementById('agendaText').value
            const label = document.getElementById('copy-label')

            const done = () => {
                label.textContent = 'Tersalin ✓'
                setTimeout(() => label.textContent = 'Copy agenda', 2000)
            }

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(done)
            } else {
                // Fallback untuk koneksi non-HTTPS
                const ta = document.getElementById('agendaText')
                ta.classList.remove('hidden'); ta.select(); document.execCommand('copy'); ta.classList.add('hidden')
                done()
            }
        }
    </script>
</x-app-layout>