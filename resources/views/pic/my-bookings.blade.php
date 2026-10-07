@use('Carbon\Carbon')
@use('Illuminate\Support\Str')

<x-app-layout>
    @php
        $tabs = [
            '' => 'Semua',
            'PENDING' => 'Menunggu',
            'APPROVED' => 'Disetujui',
            'REJECTED' => 'Ditolak',
            'CANCELED' => 'Dibatalkan',
        ];

        // Warna ruangan dari kolom rooms.color (sama seperti agenda, dashboard & kalender)
        $roomColors = \App\Models\Room::pluck('color', 'id')->filter()->all();

        $todayStr = now()->toDateString();
        $now = now();

        // Kelompokkan per tanggal (urutan dari controller tetap dipertahankan)
        $grouped = $bookings->getCollection()->groupBy(fn($b) => Carbon::parse($b->start_at)->toDateString());

        // [label, kelas badge, kelas titik]
        $statusMap = [
            'APPROVED' => ['Disetujui', 'bg-emerald-50 text-emerald-700 ring-emerald-600/20', 'bg-emerald-500'],
            'PENDING' => ['Menunggu', 'bg-amber-50 text-amber-700 ring-amber-600/20', 'bg-amber-500'],
            'REJECTED' => ['Ditolak', 'bg-rose-50 text-rose-700 ring-rose-600/20', 'bg-rose-500'],
            'CANCELED' => ['Dibatalkan', 'bg-slate-100 text-slate-500 ring-slate-400/20', 'bg-slate-400'],
            'CANCELLED' => ['Dibatalkan', 'bg-slate-100 text-slate-500 ring-slate-400/20', 'bg-slate-400'],
        ];

        // Ikon gedung untuk unit kerja (SVG inline, warna mengikuti teks)
        $iconBuilding = '<svg class="%s" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4M10 10h4M10 14h4M10 18h4"/></svg>';
        $building = fn(string $cls) => sprintf($iconBuilding, $cls);

        $fmtDur = fn($m) => trim(($m >= 60 ? intdiv($m, 60) . ' j ' : '') . ($m % 60 ? ($m % 60) . ' m' : ''));

        $hasFilter = filled($q) || filled($status) || filled($unitKerja);

        // Mobile: waktu + status di baris atas. Desktop: 4 kolom.
        $cols = 'grid-cols-[1fr_auto] xl:grid-cols-[104px_minmax(0,1fr)_160px_112px_300px]';

        // Logo WhatsApp (SVG inline, warna mengikuti teks)
        $iconWa = '<svg class="%s" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>';
        $wa = fn(string $cls) => sprintf($iconWa, $cls);
    @endphp

    <div class="space-y-4 px-4 pb-8 pt-4 sm:px-8">

        {{-- ===== BANNER JUDUL ===== --}}
        <div
            class="relative flex items-center gap-3 overflow-hidden rounded-xl border border-white/70 bg-white/80 px-4 py-3 shadow-sm backdrop-blur sm:gap-4">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-500">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </span>
            <div class="min-w-0 flex-1">
                <h1 class="text-base font-extrabold leading-tight text-[#0f1e5a] sm:text-lg">Riwayat Pengajuan</h1>
                <p class="mt-0.5 text-xs text-slate-500">Pantau status pengajuan rapat Anda, hubungi TU, atau batalkan
                    booking.</p>
            </div>


        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">

            {{-- ===== TOOLBAR ===== --}}
            <div class="flex flex-wrap items-center gap-3 border-b border-slate-100 bg-slate-50/60 px-4 py-3.5 sm:px-5">

                {{-- Tab status --}}
                <div class="-mx-1 max-w-full overflow-x-auto px-1">
                    <div class="inline-flex gap-0.5 rounded-lg bg-slate-200/60 p-0.5" role="tablist"
                        aria-label="Status">
                        @foreach($tabs as $key => $label)
                            @php $active = ($status === $key || (empty($status) && $key === '')); @endphp
                            <a href="{{ route('my_bookings.index', array_filter(['status' => $key, 'q' => $q, 'unit_kerja' => $unitKerja])) }}"
                                role="tab" aria-selected="{{ $active ? 'true' : 'false' }}"
                                class="whitespace-nowrap rounded-md px-3.5 py-1.5 text-[13px] font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 {{ $active ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Cari + unit kerja (satu form) --}}
                <form method="GET" id="searchForm"
                    class="flex w-full flex-wrap items-center gap-2 sm:ml-auto sm:w-auto sm:flex-nowrap">
                    @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif

                    <div class="relative w-full sm:w-72">
                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z" />
                        </svg>
                        <input type="text" name="q" id="searchInput" value="{{ $q }}" autocomplete="off"
                            data-server-q="{{ $q ? '1' : '' }}" aria-label="Cari rapat"
                            placeholder="Cari judul, ruangan, unit, status…"
                            class="h-9 w-full rounded-lg border border-slate-200 bg-white py-0 pl-9 pr-16 text-[13px] font-medium text-slate-700 placeholder-slate-400 focus:border-indigo-400 focus:ring-4 focus:ring-indigo-500/10">
                        <div class="absolute right-2 top-1/2 flex -translate-y-1/2 items-center gap-1">
                            <button type="button" id="clearBtn"
                                class="hidden rounded-md p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                                aria-label="Hapus pencarian">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18" />
                                </svg>
                            </button>
                            <kbd id="slashHint"
                                class="hidden rounded border border-slate-200 bg-slate-50 px-1.5 py-0.5 text-[11px] font-semibold text-slate-400 sm:block">/</kbd>
                        </div>
                    </div>

                    @if($unitKerjaOptions->isNotEmpty())
                        <div class="relative w-full sm:w-56">
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

                    <button type="submit"
                        class="h-9 rounded-lg bg-indigo-600 px-4 text-[13px] font-semibold text-white shadow-sm shadow-indigo-600/25 transition hover:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                        Cari
                    </button>
                </form>
            </div>

            {{-- ===== RINGKASAN ===== --}}
            <div class="flex flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-5">
                <div class="min-w-0">
                    <h2 class="text-lg font-extrabold leading-tight text-slate-900">Daftar rapat</h2>
                    <div class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
                        @if($status && isset($tabs[$status]))
                            <span class="font-semibold text-slate-700">Status: {{ $tabs[$status] }}</span>
                        @endif
                        @if($unitKerja)
                            <span class="inline-flex max-w-full items-center gap-1.5">
                                <span class="text-indigo-500">{!! $building('h-3.5 w-3.5 shrink-0') !!}</span>
                                <span class="truncate font-semibold text-slate-700">{{ $unitKerja }}</span>
                            </span>
                        @endif
                        <span id="resultInfo" class="hidden font-semibold text-indigo-600"></span>
                    </div>
                </div>

                <div class="min-w-[76px] rounded-xl bg-slate-50 px-3.5 py-2 ring-1 ring-inset ring-slate-200/70">
                    <div class="text-lg font-extrabold leading-none tabular-nums text-slate-900">
                        {{ $bookings->total() }}
                    </div>
                    <div class="mt-1 text-[11px] font-medium text-slate-500">Total rapat</div>
                </div>
            </div>

            @if($grouped->isEmpty())
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
                        {{ $hasFilter ? 'Tidak ada rapat yang cocok' : 'Belum ada riwayat rapat' }}
                    </div>
                    <p class="mx-auto mt-1 max-w-sm text-xs text-slate-500">
                        {{ $hasFilter ? 'Ubah kata kunci atau filter yang dipakai.' : 'Pengajuan yang Anda buat akan muncul di sini.' }}
                    </p>
                    <div class="mt-4 flex items-center justify-center gap-2">
                        @if($hasFilter)
                            <a href="{{ route('my_bookings.index') }}"
                                class="rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-[13px] font-semibold text-slate-700 transition hover:bg-slate-50">Reset
                                filter</a>
                        @else
                            <a href="{{ route('calendar', ['ajukan' => 1]) }}"
                                class="rounded-lg bg-indigo-600 px-3.5 py-2 text-[13px] font-semibold text-white shadow-sm shadow-indigo-600/25 transition hover:bg-indigo-700">Ajukan
                                rapat</a>
                        @endif
                    </div>
                </div>
            @else
                {{-- Header kolom --}}
                <div
                    class="hidden gap-4 border-y border-slate-100 bg-slate-50/70 px-5 py-2 text-[11px] font-semibold uppercase tracking-wide text-slate-400 xl:grid {{ $cols }}">
                    <span>Waktu</span>
                    <span>Rapat</span>
                    <span>Ruangan</span>
                    <span>Status</span>
                    <span class="text-right">Aksi</span>
                </div>

                @foreach($grouped as $dateStr => $items)
                    @php
                        $isToday = $dateStr === $todayStr;
                        $dateLabel = Carbon::parse($dateStr)->translatedFormat('l, d F Y');
                    @endphp

                    <section data-group class="{{ !$loop->first ? 'border-t border-slate-100' : '' }}">
                        <div class="flex items-center gap-2 border-b border-slate-100 bg-slate-50/50 px-4 py-2 sm:px-5">
                            <span class="text-xs font-bold {{ $isToday ? 'text-indigo-700' : 'text-slate-700' }}">
                                {{ $dateLabel }}
                            </span>
                            @if($isToday)
                                <span class="rounded bg-indigo-100 px-1.5 py-0.5 text-[11px] font-semibold text-indigo-700">Hari
                                    ini</span>
                            @endif
                            <span class="ml-auto text-xs text-slate-400">{{ $items->count() }} rapat</span>
                        </div>

                        <div class="divide-y divide-slate-100">
                            @foreach($items as $b)
                                @php
                                    $c = $roomColors[$b->room_id ?? 0] ?? '#6366f1';
                                    $start = Carbon::parse($b->start_at);
                                    $end = Carbon::parse($b->end_at);
                                    $startTime = $start->format('H.i');
                                    $endTime = $end->format('H.i');
                                    $durMin = (int) $start->diffInMinutes($end);

                                    $isActive = in_array($b->status, ['APPROVED', 'PENDING'], true);
                                    $live = $isActive && $now->between($start, $end);
                                    $isDone = $b->status === 'APPROVED' && $end->lessThan($now);

                                    [$sLabel, $sCls, $sDot] = $statusMap[strtoupper($b->status)] ?? [Str::title(strtolower($b->status)), 'bg-slate-100 text-slate-500 ring-slate-400/20', 'bg-slate-400'];
                                    if ($isDone) {
                                        [$sLabel, $sCls, $sDot] = ['Selesai', 'bg-slate-100 text-slate-500 ring-slate-400/20', 'bg-slate-400'];
                                    }
                                    if ($live) {
                                        [$sLabel, $sCls, $sDot] = ['Berlangsung', 'bg-emerald-50 text-emerald-700 ring-emerald-600/20', 'bg-emerald-500 animate-pulse'];
                                    }
                                    $dim = $isDone || !$isActive;
                                    $canCancel = in_array($b->status, ['PENDING', 'APPROVED'], true) && !$isDone;

                                    // Teks yang bisa dicari
                                    $searchText = implode(' ', array_filter([
                                        $b->title,
                                        $b->room?->name,
                                        $b->unit_kerja,
                                        $b->description,
                                        $sLabel,
                                        $dateLabel,
                                        "$startTime $endTime",
                                    ]));
                                @endphp

                                <article data-row data-search="{{ $searchText }}"
                                    class="group relative grid gap-x-4 gap-y-2 px-4 py-3.5 transition hover:bg-slate-50/70 xl:items-center sm:px-5 {{ $cols }} {{ $live ? 'bg-emerald-50/40' : '' }}">
                                    <span class="absolute inset-y-0 left-0 w-[3px]" style="background: {{ $c }}"
                                        aria-hidden="true"></span>

                                    {{-- Waktu --}}
                                    <div class="order-1 xl:order-none {{ $dim ? 'opacity-70' : '' }}">
                                        <div class="text-sm font-extrabold tabular-nums leading-tight text-slate-900">
                                            {{ $startTime }}
                                            <span class="font-semibold text-slate-400">–</span>
                                            {{ $endTime }}
                                        </div>
                                        <div class="mt-0.5 text-xs text-slate-400">{{ $fmtDur($durMin) }}</div>
                                    </div>

                                    {{-- Rapat --}}
                                    <div
                                        class="order-3 col-span-2 min-w-0 xl:order-none xl:col-span-1 {{ $dim ? 'opacity-70' : '' }}">
                                        <div class="js-title truncate text-sm font-semibold text-slate-900"
                                            data-title="{{ $b->title }}" title="{{ $b->title }}">{{ $b->title }}</div>

                                        @php
                                            $waUrl = null;
                                            if ($b->room?->tuUser?->phone && $b->status === 'PENDING') {
                                                $tuPhone = ltrim(preg_replace('/^0/', '62', $b->room->tuUser->phone), '+');
                                                $startDate = $start->translatedFormat('d F Y');
                                                $jam = $start->format('H:i') . ' - ' . $end->format('H:i');
                                                $waMsg = "Halo Bapak/Ibu {$b->room->tuUser->name},\n\n"
                                                    . "Saya PIC {$b->unit_kerja} ingin mengkonfirmasi pengajuan peminjaman ruang rapat:\n\n"
                                                    . "*{$b->title}*\n"
                                                    . "Ruangan: {$b->room->name}\n"
                                                    . "Tanggal: {$startDate}\n"
                                                    . "Waktu: {$jam}\n\n"
                                                    . "Mohon konfirmasinya apakah jadwal tersebut tersedia. Jika tersedia, mohon untuk melakukan approval di sistem.\n\nTerima kasih.";
                                                $waUrl = 'https://wa.me/' . $tuPhone . '?text=' . rawurlencode($waMsg);
                                            }
                                        @endphp

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
                                                    <span class="truncate"
                                                        title="{{ $b->description }}">{{ Str::limit($b->description, 70) }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Ruangan --}}
                                    <div
                                        class="order-4 col-span-2 flex items-center gap-2 text-xs font-medium text-slate-600 xl:order-none xl:col-span-1 {{ $dim ? 'opacity-70' : '' }}">
                                        <span class="h-2.5 w-2.5 shrink-0 rounded-full ring-2 ring-white"
                                            style="background: {{ $c }}; box-shadow: 0 0 0 1px {{ $c }}55;"></span>
                                        <span class="truncate">{{ $b->room?->name ?? '-' }}</span>
                                    </div>

                                    {{-- Status --}}
                                    <div class="order-2 justify-self-end xl:order-none xl:justify-self-start">
                                        <span
                                            class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $sCls }}">
                                            <span class="h-1.5 w-1.5 rounded-full {{ $sDot }}"></span>{{ $sLabel }}
                                        </span>
                                    </div>

                                    {{-- Aksi --}}
                                    @if($waUrl || $canCancel)
                                        <div
                                            class="order-5 col-span-2 flex flex-wrap items-center justify-end gap-2 xl:order-none xl:col-span-1 xl:flex-nowrap">
                                            @if($waUrl)
                                                <a href="{{ $waUrl }}" target="_blank" rel="noopener"
                                                    title="Chat TU {{ $b->room->tuUser->name }} via WhatsApp"
                                                    class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:border-emerald-300 hover:bg-emerald-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40">
                                                    {!! $wa('h-4 w-4 shrink-0 text-[#25D366]') !!}
                                                    Chat TU
                                                </a>
                                            @endif

                                            @if($canCancel)
                                                <button type="button" data-id="{{ $b->id }}" data-title="{{ $b->title }}"
                                                    onclick="openCancelModal(this)"
                                                    class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:border-rose-600 hover:bg-rose-600 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500/40">
                                                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2"
                                                        stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"
                                                        aria-hidden="true">
                                                        <circle cx="12" cy="12" r="9" />
                                                        <path d="M15 9l-6 6M9 9l6 6" />
                                                    </svg>
                                                    Batalkan
                                                </button>
                                            @endif
                                        </div>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endforeach

                {{-- Kosong hasil live search (di halaman ini) --}}
                <div id="noMatch" class="hidden flex-col items-center border-t border-slate-100 px-4 py-12 text-center">
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z" />
                        </svg>
                    </span>
                    <div class="mt-3 text-sm font-bold text-slate-800">Tidak ditemukan di halaman ini</div>
                    <p class="mt-1 max-w-xs text-xs text-slate-500">Tekan Enter untuk mencari di seluruh riwayat.</p>
                </div>

                {{-- Footer --}}
                <div
                    class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 bg-slate-50/40 px-4 py-3 sm:px-5">
                    <span class="text-xs text-slate-400">
                        Menampilkan {{ $bookings->firstItem() }}–{{ $bookings->lastItem() }} dari {{ $bookings->total() }}
                        rapat
                    </span>
                    <a href="{{ route('agenda') }}"
                        class="text-[13px] font-semibold text-indigo-600 hover:text-indigo-800">Buka agenda →</a>
                </div>
            @endif
        </div>

        @if($bookings->hasPages())
            <div>
                {{ $bookings->links() }}
            </div>
        @endif
    </div>

    {{-- ===== Modal batalkan booking ===== --}}
    <div id="cancelModal" class="fixed inset-0 z-50 items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm"
        style="display:none;">
        <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl" role="dialog" aria-modal="true"
            aria-labelledby="cancelTitle">
            <div class="flex items-start justify-between gap-3 px-5 pb-2 pt-5">
                <div class="flex items-center gap-3">
                    <span
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-500">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v4m0 4h.01M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z" />
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <h3 id="cancelTitle" class="text-base font-extrabold text-slate-900">Batalkan booking</h3>
                        <p id="cancelSubtitle" class="mt-0.5 line-clamp-2 text-xs text-slate-500"></p>
                    </div>
                </div>
                <button type="button" onclick="closeCancelModal()" aria-label="Tutup"
                    class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" d="M6 6l12 12M18 6L6 18" />
                    </svg>
                </button>
            </div>
            <form id="cancelForm" method="POST">
                @csrf
                <div class="px-5 py-4">
                    <label for="cancelReason" class="mb-1.5 block text-xs font-bold text-slate-600">Alasan
                        pembatalan</label>
                    <textarea id="cancelReason" name="cancel_reason" rows="4" required
                        placeholder="Contoh: jadwal rapat dipindah ke minggu depan"
                        class="w-full resize-none rounded-lg border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-500/10"></textarea>
                </div>
                <div class="flex justify-end gap-2 border-t border-slate-100 bg-slate-50/60 px-5 py-3.5">
                    <button type="button" onclick="closeCancelModal()"
                        class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-[13px] font-semibold text-slate-700 transition hover:bg-slate-50">Kembali</button>
                    <button type="submit"
                        class="rounded-lg bg-rose-600 px-4 py-2 text-[13px] font-semibold text-white shadow-sm shadow-rose-600/25 transition hover:bg-rose-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500 focus-visible:ring-offset-2">Batalkan
                        booking</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        /* ===== Modal batal ===== */
        const cancelUrlTemplate = @json(route('bookings.cancel', ['booking' => '__ID__']));
        const cancelModal = document.getElementById('cancelModal');

        function openCancelModal(btn) {
            document.getElementById('cancelForm').action = cancelUrlTemplate.replace('__ID__', btn.dataset.id);
            document.getElementById('cancelSubtitle').textContent = btn.dataset.title || '';
            cancelModal.style.display = 'flex';
            setTimeout(() => document.getElementById('cancelReason').focus(), 50);
        }
        function closeCancelModal() { cancelModal.style.display = 'none'; }
        cancelModal.addEventListener('click', e => { if (e.target === cancelModal) closeCancelModal(); });

        /* ===== Live search (pada halaman yang sedang tampil) ===== */
        (function () {
            const input = document.getElementById('searchInput');
            const clearBtn = document.getElementById('clearBtn');
            const hint = document.getElementById('slashHint');
            const info = document.getElementById('resultInfo');
            const noMatch = document.getElementById('noMatch');
            const rows = [...document.querySelectorAll('[data-row]')];
            const groups = [...document.querySelectorAll('[data-group]')];

            const norm = s => (s || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            const esc = s => s.replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

            const index = rows.map(r => ({
                el: r,
                hay: norm(r.dataset.search),
                titleEl: r.querySelector('.js-title'),
            }));

            function highlight(el, terms) {
                const text = el.dataset.title;
                if (!terms.length) { el.textContent = text; return; }
                // cocokkan tanpa memedulikan aksen/huruf besar-kecil
                const n = norm(text);
                const ranges = [];
                terms.forEach(t => {
                    let i = n.indexOf(t);
                    while (i !== -1) { ranges.push([i, i + t.length]); i = n.indexOf(t, i + t.length); }
                });
                if (!ranges.length || n.length !== text.length) { el.textContent = text; return; }
                ranges.sort((a, b) => a[0] - b[0]);
                const merged = [ranges[0].slice()];
                for (const r of ranges.slice(1)) {
                    const last = merged[merged.length - 1];
                    if (r[0] <= last[1]) last[1] = Math.max(last[1], r[1]); else merged.push(r.slice());
                }
                let out = '', pos = 0;
                merged.forEach(([s, e]) => {
                    out += esc(text.slice(pos, s)) + '<mark class="rounded bg-yellow-200 px-0.5 text-slate-900">' + esc(text.slice(s, e)) + '</mark>';
                    pos = e;
                });
                el.innerHTML = out + esc(text.slice(pos));
            }

            function apply() {
                const raw = input.value.trim();
                const terms = norm(raw).split(/\s+/).filter(Boolean);
                let shown = 0;

                index.forEach(item => {
                    const ok = terms.every(t => item.hay.includes(t));
                    item.el.style.display = ok ? '' : 'none';
                    if (ok) shown++;
                    if (item.titleEl) highlight(item.titleEl, ok ? terms : []);
                });

                groups.forEach(g => {
                    const any = [...g.querySelectorAll('[data-row]')].some(r => r.style.display !== 'none');
                    g.style.display = any ? '' : 'none';
                });

                const searching = terms.length > 0;
                clearBtn.classList.toggle('hidden', !raw);
                hint.classList.toggle('sm:block', !raw);
                info.classList.toggle('hidden', !searching);
                info.textContent = searching ? shown + ' dari ' + rows.length + ' cocok' : '';
                if (noMatch) {
                    const empty = searching && shown === 0 && rows.length > 0;
                    noMatch.classList.toggle('hidden', !empty);
                    noMatch.classList.toggle('flex', empty);
                }
            }

            let t;
            input.addEventListener('input', () => { clearTimeout(t); t = setTimeout(apply, 80); });

            clearBtn.addEventListener('click', () => {
                // Jika halaman ini hasil pencarian server, kembali ke daftar penuh
                if (input.dataset.serverQ) {
                    input.value = '';
                    document.getElementById('searchForm').submit();
                    return;
                }
                input.value = '';
                apply();
                input.focus();
            });

            // Tekan "/" untuk fokus ke kolom cari, Esc untuk keluar
            document.addEventListener('keydown', e => {
                const tag = (document.activeElement?.tagName || '').toLowerCase();
                if (e.key === '/' && !['input', 'textarea', 'select'].includes(tag)) {
                    e.preventDefault();
                    input.focus();
                    input.select();
                }
                if (e.key === 'Escape') {
                    if (cancelModal.style.display === 'flex') closeCancelModal();
                    else if (document.activeElement === input) input.blur();
                }
            });

            apply();
        })();
    </script>
</x-app-layout>