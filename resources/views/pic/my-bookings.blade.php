<x-app-layout>
    @php
        $tabs = [
            '' => 'Semua',
            'PENDING' => 'Menunggu',
            'APPROVED' => 'Disetujui',
            'REJECTED' => 'Ditolak',
            'CANCELED' => 'Dibatalkan',
        ];

        $roomColors = [1 => '#1a1a1a', 2 => '#a855f7', 3 => '#92400e', 4 => '#facc15', 5 => '#22d3ee', 6 => '#ef4444', 7 => '#ec4899', 8 => '#468432'];

        $todayStr = now()->format('Y-m-d');
        $now = now();

        // Kelompokkan per tanggal (urutan dari controller tetap dipertahankan)
        $grouped = $bookings->getCollection()->groupBy(fn($b) => \Carbon\Carbon::parse($b->start_at)->format('Y-m-d'));
    @endphp

    <div class="mx-auto max-w-5xl space-y-6 px-4 py-8 sm:px-8">

        {{-- ===== Hero ===== --}}
        <div class="relative overflow-hidden rounded-3xl bg-[#0f1e5a] p-6 text-white shadow-xl shadow-indigo-900/20 sm:p-8">
            <div class="pointer-events-none absolute -right-16 -top-20 h-64 w-64 rounded-full border-[28px] border-indigo-400/20"></div>
            <div class="pointer-events-none absolute -bottom-24 right-24 hidden h-56 w-56 rounded-full bg-indigo-500/20 sm:block"></div>

            <div class="relative flex flex-wrap items-center justify-between gap-5">
                <div class="min-w-0">
                    <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl">Riwayat Pengajuan</h1>
                    <p class="mt-2 max-w-md text-sm leading-relaxed text-indigo-100/80">
                        Pantau status pengajuan rapat Anda, hubungi TU, atau batalkan booking.
                    </p>
                </div>
                <a href="{{ route('calendar', ['ajukan' => 1]) }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-bold text-indigo-700 shadow-lg transition hover:bg-indigo-50 focus:outline-none focus-visible:ring-4 focus-visible:ring-white/40">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                    Ajukan rapat
                </a>
            </div>
        </div>

        {{-- ===== Pencarian & filter ===== --}}
        <div class="space-y-4 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm sm:p-5">

            {{-- Search --}}
            <form method="GET" id="searchForm" class="flex items-center gap-2">
                <div class="relative flex-1">
                    <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
                    <input type="text" name="q" id="searchInput" value="{{ $q }}" autocomplete="off"
                        data-server-q="{{ $q ? '1' : '' }}"
                        placeholder="Cari judul, ruangan, unit kerja, atau status…"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3.5 pl-12 pr-24 text-sm text-slate-800 placeholder-slate-400 transition focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
                    <div class="absolute right-3 top-1/2 flex -translate-y-1/2 items-center gap-1.5">
                        <button type="button" id="clearBtn" class="hidden rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-200 hover:text-slate-600" aria-label="Hapus pencarian">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                        </button>
                        <kbd id="slashHint" class="hidden rounded-md border border-slate-200 bg-white px-2 py-0.5 text-[11px] font-semibold text-slate-400 sm:block">/</kbd>
                    </div>
                </div>
                @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
                @if($unitKerja)<input type="hidden" name="unit_kerja" value="{{ $unitKerja }}">@endif
                <button type="submit" class="rounded-2xl bg-indigo-600 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-700 focus:outline-none focus-visible:ring-4 focus-visible:ring-indigo-500/30">
                    Cari
                </button>
            </form>

            {{-- Tab status --}}
            <div class="-mx-1 overflow-x-auto px-1 pb-1">
                <div class="inline-flex min-w-full gap-1 rounded-2xl bg-slate-100 p-1 sm:min-w-0">
                    @foreach($tabs as $key => $label)
                        @php $active = ($status === $key || (empty($status) && $key === '')); @endphp
                        <a href="{{ route('my_bookings.index', array_filter(['status' => $key, 'q' => $q, 'unit_kerja' => $unitKerja])) }}"
                            class="whitespace-nowrap rounded-xl px-4 py-2 text-sm font-semibold transition {{ $active ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Unit kerja --}}
            @if($unitKerjaOptions->isNotEmpty())
                <div class="flex flex-wrap items-center gap-2 border-t border-slate-100 pt-4">
                    <span class="mr-1 text-xs font-semibold text-slate-400">Unit kerja</span>
                    <a href="{{ route('my_bookings.index', array_filter(['status' => $status, 'q' => $q])) }}"
                        class="rounded-full border px-3.5 py-1.5 text-xs font-bold transition {{ !$unitKerja ? 'border-indigo-500 bg-indigo-500 text-white' : 'border-slate-200 text-slate-600 hover:border-indigo-300 hover:text-indigo-600' }}">
                        Semua
                    </a>
                    @foreach($unitKerjaOptions as $uk)
                        <a href="{{ route('my_bookings.index', array_filter(['status' => $status, 'q' => $q, 'unit_kerja' => $uk])) }}"
                            class="rounded-full border px-3.5 py-1.5 text-xs font-bold transition {{ $unitKerja === $uk ? 'border-indigo-500 bg-indigo-500 text-white' : 'border-slate-200 text-slate-600 hover:border-indigo-300 hover:text-indigo-600' }}">
                            {{ $uk }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ===== Daftar rapat ===== --}}
        <div>
            <div class="mb-3 flex items-center justify-between gap-3 px-1">
                <h2 class="text-lg font-extrabold text-slate-900">Daftar rapat</h2>
                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <span id="resultInfo" class="hidden font-semibold text-indigo-600"></span>
                    @if($bookings->total() > 0)
                        <span class="rounded-full bg-indigo-50 px-3 py-1 font-bold text-indigo-600">{{ $bookings->total() }} total</span>
                    @endif
                </div>
            </div>

            @forelse($grouped as $dateStr => $items)
                @php
                    $isToday = $dateStr === $todayStr;
                    $dateLabel = \Carbon\Carbon::parse($dateStr)->translatedFormat('l, d F Y');
                @endphp

                <section class="date-group mb-6" data-group>
                    <div class="mb-2.5 flex items-center gap-3 px-1">
                        <span class="text-sm font-extrabold {{ $isToday ? 'text-indigo-600' : 'text-slate-700' }}">{{ $dateLabel }}</span>
                        @if($isToday)
                            <span class="rounded-full bg-indigo-600 px-2.5 py-0.5 text-[11px] font-bold text-white">Hari ini</span>
                        @endif
                        <span class="h-px flex-1 bg-slate-200"></span>
                    </div>

                    <div class="space-y-2.5">
                        @foreach($items as $b)
                            @php
                                $displayStatus = strtoupper($b->status);
                                $isDone = ($b->status === 'APPROVED' && \Carbon\Carbon::parse($b->end_at)->lt($now));
                                if ($isDone)
                                    $displayStatus = 'DONE';

                                [$sLabel, $sCls, $sDot] = match ($displayStatus) {
                                    'APPROVED' => ['Disetujui', 'bg-emerald-50 text-emerald-700', 'bg-emerald-500'],
                                    'PENDING' => ['Menunggu', 'bg-amber-50 text-amber-700', 'bg-amber-500'],
                                    'REJECTED' => ['Ditolak', 'bg-red-50 text-red-600', 'bg-red-500'],
                                    'CANCELED', 'CANCELLED' => ['Dibatalkan', 'bg-slate-100 text-slate-500', 'bg-slate-400'],
                                    'DONE' => ['Selesai', 'bg-sky-50 text-sky-700', 'bg-sky-500'],
                                    default => [ucfirst(strtolower($displayStatus)), 'bg-slate-100 text-slate-600', 'bg-slate-400'],
                                };

                                $c = $roomColors[$b->room_id ?? 0] ?? '#cbd5e1';
                                $startTime = \Carbon\Carbon::parse($b->start_at)->format('H.i');
                                $endTime = \Carbon\Carbon::parse($b->end_at)->format('H.i');
                                $dim = in_array($displayStatus, ['CANCELED', 'CANCELLED', 'REJECTED']);

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
                                class="group relative flex flex-wrap items-center gap-x-4 gap-y-3 overflow-hidden rounded-2xl border border-slate-100 bg-white py-4 pl-6 pr-4 shadow-sm transition hover:border-indigo-200 hover:shadow-md sm:flex-nowrap {{ $dim ? 'opacity-70' : '' }}">

                                {{-- Garis warna ruangan --}}
                                <span class="absolute inset-y-0 left-0 w-1.5" style="background: {{ $c }}"></span>

                                {{-- Jam --}}
                                <div class="w-16 shrink-0 text-center">
                                    <div class="text-lg font-extrabold leading-none tabular-nums text-slate-900">{{ $startTime }}</div>
                                    <div class="mt-1 text-xs tabular-nums text-slate-400">s/d {{ $endTime }}</div>
                                </div>

                                <span class="hidden h-10 w-px bg-slate-100 sm:block"></span>

                                {{-- Detail --}}
                                <div class="min-w-0 flex-1">
                                    <h3 class="js-title text-sm font-bold leading-snug text-slate-900 sm:text-[15px]" data-title="{{ $b->title }}">{{ $b->title }}</h3>

                                    <div class="mt-1.5 flex flex-wrap items-center gap-2 text-xs">
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-50 px-2.5 py-1 font-semibold text-slate-600 ring-1 ring-slate-200">
                                            <span class="h-2 w-2 rounded-full" style="background: {{ $c }}"></span>
                                            {{ $b->room?->name ?? '-' }}
                                        </span>

                                        @if($b->unit_kerja)
                                            <span class="inline-flex items-center gap-1 text-slate-500">
                                                <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m14 0h2M5 21H3M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5"/></svg>
                                                {{ $b->unit_kerja }}
                                            </span>
                                        @endif

                                        @if($b->room?->tuUser?->phone && $b->status === 'PENDING')
                                            @php
                                                $tuPhone = preg_replace('/^0/', '62', $b->room->tuUser->phone);
                                                $tuPhone = ltrim($tuPhone, '+');
                                                $startDate = \Carbon\Carbon::parse($b->start_at)->translatedFormat('d F Y');
                                                $jam = \Carbon\Carbon::parse($b->start_at)->format('H:i') . ' - ' . \Carbon\Carbon::parse($b->end_at)->format('H:i');
                                                $waMsg = "Halo Bapak/Ibu {$b->room->tuUser->name},\n\n"
                                                    . "Saya PIC {$b->unit_kerja} ingin mengkonfirmasi pengajuan peminjaman ruang rapat:\n\n"
                                                    . "*{$b->title}*\n"
                                                    . "Ruangan: {$b->room->name}\n"
                                                    . "Tanggal: {$startDate}\n"
                                                    . "Waktu: {$jam}\n\n"
                                                    . "Mohon konfirmasinya apakah jadwal tersebut tersedia. Jika tersedia, mohon untuk melakukan approval di sistem.\n\nTerima kasih.";
                                                $waUrl = 'https://wa.me/' . $tuPhone . '?text=' . rawurlencode($waMsg);
                                            @endphp
                                            <a href="{{ $waUrl }}" target="_blank" rel="noopener"
                                                class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 font-bold text-emerald-600 transition hover:bg-emerald-100"
                                                title="Chat TU {{ $b->room->tuUser->name }}">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4-.8L3 20l1.3-3.9A7.6 7.6 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                                Chat TU
                                            </a>
                                        @endif
                                    </div>

                                    @if($b->description)
                                        <p class="mt-2 line-clamp-1 text-xs text-slate-400">{{ $b->description }}</p>
                                    @endif
                                </div>

                                {{-- Status & aksi --}}
                                <div class="flex shrink-0 items-center gap-2">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold {{ $sCls }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $sDot }}"></span>
                                        {{ $sLabel }}
                                    </span>

                                    @if(in_array($b->status, ['PENDING', 'APPROVED']) && !$isDone)
                                        <button type="button" data-id="{{ $b->id }}" data-title="{{ $b->title }}" onclick="openCancelModal(this)"
                                            class="rounded-full border border-slate-200 px-3.5 py-1.5 text-xs font-bold text-slate-600 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600 focus:outline-none focus-visible:ring-4 focus-visible:ring-red-500/20">
                                            Batalkan
                                        </button>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @empty
                <div class="flex flex-col items-center rounded-2xl border border-dashed border-slate-200 bg-white py-16 text-center">
                    <span class="flex h-16 w-16 items-center justify-center rounded-full bg-indigo-50 text-indigo-400">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </span>
                    <div class="mt-4 text-base font-bold text-slate-800">
                        {{ ($q || $status || $unitKerja) ? 'Tidak ada rapat yang cocok' : 'Belum ada riwayat rapat' }}
                    </div>
                    <p class="mt-1 max-w-xs text-sm text-slate-500">
                        {{ ($q || $status || $unitKerja) ? 'Ubah kata kunci atau filter yang dipakai.' : 'Pengajuan yang Anda buat akan muncul di sini.' }}
                    </p>
                    @if($q || $status || $unitKerja)
                        <a href="{{ route('my_bookings.index') }}" class="mt-5 rounded-xl bg-slate-100 px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-200">Reset filter</a>
                    @else
                        <a href="{{ route('calendar', ['ajukan' => 1]) }}" class="mt-5 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-indigo-700">Ajukan rapat</a>
                    @endif
                </div>
            @endforelse

            {{-- Kosong hasil live search (di halaman ini) --}}
            <div id="noMatch" class="hidden flex-col items-center rounded-2xl border border-dashed border-slate-200 bg-white py-14 text-center">
                <span class="flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
                </span>
                <div class="mt-4 text-base font-bold text-slate-800">Tidak ditemukan di halaman ini</div>
                <p class="mt-1 max-w-xs text-sm text-slate-500">Tekan Enter untuk mencari di seluruh riwayat.</p>
            </div>

            @if($bookings->hasPages())
                <div class="mt-6">
                    {{ $bookings->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- ===== Modal batalkan booking ===== --}}
    <div id="cancelModal" class="fixed inset-0 z-50 items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm" style="display:none;">
        <div class="w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="cancelTitle">
            <div class="flex items-start justify-between gap-3 px-6 pb-2 pt-5">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-500">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/></svg>
                    </span>
                    <div>
                        <h3 id="cancelTitle" class="text-base font-extrabold text-slate-900">Batalkan booking</h3>
                        <p id="cancelSubtitle" class="mt-0.5 line-clamp-2 text-xs text-slate-500"></p>
                    </div>
                </div>
                <button type="button" onclick="closeCancelModal()" aria-label="Tutup"
                    class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                </button>
            </div>
            <form id="cancelForm" method="POST">
                @csrf
                <div class="px-6 py-4">
                    <label for="cancelReason" class="mb-1.5 block text-xs font-bold text-slate-600">Alasan pembatalan</label>
                    <textarea id="cancelReason" name="cancel_reason" rows="4" required placeholder="Contoh: jadwal rapat dipindah ke minggu depan"
                        class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-500/10"></textarea>
                </div>
                <div class="flex justify-end gap-2 border-t border-slate-100 bg-slate-50 px-6 py-4">
                    <button type="button" onclick="closeCancelModal()"
                        class="rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-100">Kembali</button>
                    <button type="submit"
                        class="rounded-xl bg-red-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-red-600/25 transition hover:bg-red-700">Batalkan booking</button>
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
            const input   = document.getElementById('searchInput');
            const clearBtn = document.getElementById('clearBtn');
            const hint    = document.getElementById('slashHint');
            const info    = document.getElementById('resultInfo');
            const noMatch = document.getElementById('noMatch');
            const rows    = [...document.querySelectorAll('[data-row]')];
            const groups  = [...document.querySelectorAll('[data-group]')];

            const norm = s => (s || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            const esc  = s => s.replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
            const escRe = s => s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

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
                noMatch.classList.toggle('hidden', !(searching && shown === 0 && rows.length > 0));
                noMatch.classList.toggle('flex', searching && shown === 0 && rows.length > 0);
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