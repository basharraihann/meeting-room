<x-app-layout>

    {{-- NOTIFIKASI SUCCESS --}}
    @if(session('status'))
        <div x-data="{ show: true }" x-show="show" x-transition
            x-init="setTimeout(() => show = false, {{ session('status_warning') ? 9000 : 5000 }})"
            class="fixed right-4 top-24 z-40 w-[calc(100vw-2rem)] max-w-sm">
            <div class="overflow-hidden rounded-2xl border border-green-200 bg-white shadow-lg">

                <div class="flex items-start justify-between gap-3 bg-green-50 p-4">
                    <div class="min-w-0">
                        <p class="font-semibold text-green-800">Berhasil!</p>
                        <p class="mt-1 text-sm text-green-700">{{ session('status') }}</p>
                    </div>
                    <button @click="show = false" class="text-green-400 hover:text-green-600">✕</button>
                </div>

                @if(session('status_warning'))
                    <div class="border-t border-amber-200 bg-amber-50 px-4 py-3">
                        <p class="text-xs font-bold text-amber-800">Perhatian</p>
                        <p class="mt-0.5 text-xs leading-relaxed text-amber-700">{{ session('status_warning') }}</p>
                    </div>
                @endif

            </div>
        </div>
    @endif

    @php
        $activeRooms = \App\Models\Room::where('active', true)->orderBy('sort_order')->orderBy('id')->get();
        $roomDotColors = $activeRooms->pluck('color', 'id')->all();

        // ===== Prefill dari link "Ajukan" di dashboard (?room=&date=&start=&until=) =====
        $prefill = null;
        if (auth()->user()?->hasRole('PIC') && request()->filled('room') && !$errors->any() && !session('pending_warning')) {
            $pfRoom = $activeRooms->firstWhere('id', (int) request('room'));
            if ($pfRoom && !$pfRoom->maintenance) {
                $pfDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) request('date')) ? request('date') : now()->toDateString();
                if ($pfDate < now()->toDateString()) {
                    $pfDate = now()->toDateString();
                }

                $pfStart = $pfEnd = null;
                if (preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', (string) request('start'), $mm)) {
                    $mins = (int) (ceil(((int) $mm[1] * 60 + (int) $mm[2]) / 15) * 15);   // 11.06 -> 11.15
                    $mins = max(7 * 60, min($mins, 20 * 60 + 45));                         // 07.00-21.00
                    $endMins = $mins + 60;                                                 // default 1 jam
                    if (preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', (string) request('until'), $um)) {
                        $u = (int) $um[1] * 60 + (int) $um[2];
                        if ($u > $mins) {
                            $endMins = min($endMins, (int) (floor($u / 15) * 15));
                        }
                    }
                    $endMins = min($endMins, 21 * 60);
                    if ($endMins <= $mins) {
                        $endMins = min($mins + 15, 21 * 60);
                    }
                    $fmtM = fn($m) => sprintf('%02d:%02d', intdiv($m, 60), $m % 60);
                    $pfStart = $fmtM($mins);
                    $pfEnd = $fmtM($endMins);
                }

                $prefill = [
                    'room_id' => $pfRoom->id,
                    'room_name' => $pfRoom->name,
                    'is_abt' => trim($pfRoom->name) === 'Ruang Rapat ABT',
                    'date' => $pfDate,
                    'start' => $pfStart,
                    'end' => $pfEnd,
                ];
            }
        }

        // Sorot ruangan di sidebar (kecuali ABT, karena ABT harus lewat konfirmasi dulu)
        $activeRoomId = request('room_id') ?: (($prefill && !$prefill['is_abt']) ? $prefill['room_id'] : null);

        $pill = 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 ring-inset transition';
        $navBtn = 'flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-600 transition hover:bg-slate-200';
    @endphp

    {{-- ===== BANNER JUDUL ===== --}}
    <div class="px-4 pt-4 sm:px-8">
        <div
            class="relative flex items-center gap-3 overflow-hidden rounded-xl border border-white/70 bg-white/80 px-4 py-3 shadow-sm backdrop-blur sm:gap-4">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-500">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"
                    stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M8 2v4" />
                    <path d="M16 2v4" />
                    <rect width="18" height="18" x="3" y="4" rx="2.5" />
                    <path d="M3 10h18" />
                </svg>
            </span>
            <div class="min-w-0 flex-1">
                <h1 class="text-base font-extrabold leading-tight text-[#0f1e5a] sm:text-lg">Kalender Booking Ruang
                    Rapat</h1>
                <p class="mt-0.5 text-xs text-slate-500">Lihat dan kelola jadwal pemesanan ruang rapat dengan mudah.</p>
            </div>

            @if(auth()->user()?->hasRole('PIC'))
                <button type="button" onclick="bukaModalAjukan()"
                    class="relative z-10 shrink-0 rounded-lg bg-indigo-600 px-3.5 py-2 text-xs font-bold text-white shadow-md shadow-indigo-600/25 transition hover:bg-indigo-700">
                    + Ajukan Rapat
                </button>
            @endif
        </div>
    </div>

    {{-- ===== MOBILE / TABLET (logic ada di script bawah) ===== --}}
    <div id="mobile-calendar-app" class="mt-3 min-h-screen bg-[#f4f6fb]" style="display:none;">
        <div class="bg-white px-4 pb-3 pt-4">
            <div class="mb-3 flex items-center justify-between gap-2">
                <button type="button" data-act="prev" aria-label="Bulan sebelumnya" class="{{ $navBtn }}">‹</button>
                <div class="text-center">
                    <div id="mc-month-label" class="text-base font-bold text-slate-900"></div>
                    <button type="button" id="mc-today-btn" data-act="today"
                        class="hidden text-[11px] font-semibold text-indigo-600 hover:text-indigo-800">Kembali ke hari
                        ini</button>
                </div>
                <button type="button" data-act="next" aria-label="Bulan berikutnya" class="{{ $navBtn }}">›</button>
            </div>

            {{-- Filter ruangan --}}
            <div class="mb-3 flex flex-wrap gap-1.5" id="mc-room-filters">
                <button type="button" id="mc-pill-" data-room-id=""
                    class="{{ $pill }} bg-indigo-600 text-white ring-indigo-600">Semua</button>
                @foreach($activeRooms as $room)
                    <button type="button" id="mc-pill-{{ $room->id }}" data-room-id="{{ $room->id }}"
                        data-color="{{ $roomDotColors[$room->id] ?? '#9ca3af' }}" @if($room->maintenance) disabled
                        title="Ruangan sedang dalam perbaikan" @endif
                        class="{{ $pill }} {{ $room->maintenance ? 'cursor-not-allowed bg-orange-50 text-orange-700 ring-orange-200' : 'bg-white text-slate-700 ring-slate-300' }}">
                        <span class="h-2 w-2 shrink-0 rounded-full"
                            style="background: {{ $roomDotColors[$room->id] ?? '#9ca3af' }}"></span>
                        {{ $room->name }}
                        @if($room->maintenance)
                            <span class="text-[10px] font-medium opacity-70">Kegiatan BPK</span>
                        @endif
                    </button>
                @endforeach
            </div>

            {{-- Grid kalender --}}
            <div class="grid grid-cols-7 text-center text-[11px] font-semibold text-slate-400">
                @foreach(['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $i => $d)
                    <div class="py-1 {{ $i > 4 ? 'text-rose-400' : '' }}">{{ $d }}</div>
                @endforeach
            </div>
            <div id="mc-grid" class="grid grid-cols-7 gap-y-0.5 sm:gap-1"></div>
        </div>

        <div class="h-px bg-slate-200"></div>

        <div class="p-4">
            <div id="mc-date-label" class="mb-3 text-[11px] font-bold uppercase tracking-wider text-slate-500"></div>
            <div id="mc-agenda" class="flex flex-col gap-2.5"></div>
        </div>
    </div>

    {{-- ===== DESKTOP ===== --}}
    <div id="desktop-calendar" class="px-4 pb-6 pt-3.5 sm:px-8" style="display:none;">
        <div class="flex items-start gap-4">

            {{-- Sidebar ruangan (#room-sidebar & .room-filter dipakai calendar.js) --}}
            <aside class="sticky top-20 w-60 shrink-0 rounded-2xl border border-slate-300 bg-white p-3 shadow-sm">
                <div class="mb-2.5 px-1.5">
                    <h2 class="text-[13px] font-extrabold text-[#0f1e5a]">Ruang Rapat</h2>
                    <p class="text-[11px] text-slate-400">{{ $activeRooms->count() }} ruangan tersedia</p>
                </div>

                @php $check = '<svg class="room-check h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>'; @endphp

                <div id="room-sidebar" data-active-room="{{ $activeRoomId }}" class="space-y-1">
                    <button type="button" data-room-id="" data-room-name="Semua Ruang"
                        class="room-filter {{ $activeRoomId ? '' : 'is-active' }}">
                        <span class="flex h-3 w-3 shrink-0 items-center justify-center opacity-70">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <rect x="3" y="3" width="7" height="7" rx="1.5" />
                                <rect x="14" y="3" width="7" height="7" rx="1.5" />
                                <rect x="3" y="14" width="7" height="7" rx="1.5" />
                                <rect x="14" y="14" width="7" height="7" rx="1.5" />
                            </svg>
                        </span>
                        <span class="flex-1 truncate">Semua Ruang</span>
                        {!! $check !!}
                    </button>

                    <div class="my-1.5 border-t border-slate-100"></div>

                    @foreach($activeRooms as $room)
                        @php $dotColor = $roomDotColors[$room->id] ?? '#9ca3af'; @endphp
                        <button type="button" data-room-id="{{ $room->id }}" data-room-name="{{ $room->name }}"
                            data-maintenance="{{ $room->maintenance ? '1' : '0' }}" {!! $room->maintenance ? 'disabled title="Ruangan sedang dalam perbaikan"' : '' !!}
                            class="room-filter {{ (string) $activeRoomId === (string) $room->id ? 'is-active' : '' }}">
                            <span class="h-3 w-3 shrink-0 rounded-full"
                                style="background: {{ $dotColor }}; box-shadow: 0 0 0 2px #fff, 0 0 0 3.5px {{ $dotColor }};"></span>
                            <span class="flex-1 truncate">{{ $room->name }}</span>
                            @if($room->maintenance)
                                <span
                                    class="whitespace-nowrap rounded-md border border-orange-200 bg-orange-50 px-1.5 py-0.5 text-[10px] font-bold text-orange-700">Kegiatan
                                    BPK</span>
                            @else
                                {!! $check !!}
                            @endif
                        </button>
                    @endforeach
                </div>
            </aside>

            {{-- Kalender --}}
            <div class="min-w-0 flex-1">
                <div class="rounded-2xl border border-white/70 bg-white/90 p-4 shadow-sm backdrop-blur">
                    <div class="mb-2.5 flex items-center gap-2 text-xs text-slate-500">
                        Menampilkan jadwal:
                        <span id="active-room-label"
                            class="rounded-lg bg-indigo-50 px-2 py-0.5 text-xs font-bold text-indigo-700">{{ $activeRooms->firstWhere('id', $activeRoomId)?->name ?? 'Semua Ruang' }}</span>
                    </div>
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Layout switch + penanda sidebar + gate ABT --}}
    <script>
        function checkLayout() {
            const mobile = window.innerWidth < 1024
            document.getElementById('mobile-calendar-app').style.display = mobile ? 'block' : 'none'
            document.getElementById('desktop-calendar').style.display = mobile ? 'none' : 'block'
        }
        checkLayout()
        window.addEventListener('resize', checkLayout)

        // Penanda ruangan aktif di sidebar (calendar.js menangani filter datanya)
        document.addEventListener('click', (e) => {
            const sidebar = document.getElementById('room-sidebar')
            const btn = e.target.closest('.room-filter')
            if (!sidebar || !btn || !sidebar.contains(btn) || btn.disabled || btn.dataset.maintenance === '1') return
            sidebar.querySelectorAll('.room-filter').forEach(b => b.classList.toggle('is-active', b === btn))
            sidebar.dataset.activeRoom = btn.dataset.roomId || ''
            const label = document.getElementById('active-room-label')
            if (label) label.textContent = btn.dataset.roomName || 'Semua Ruang'
        })

            // GATE: konfirmasi sebelum membuka kalender Ruang Rapat ABT (fase capture)
            ; (function () {
                const ABT = 'Ruang Rapat ABT'
                window.__abtGateBypass = false
                const isAbt = (btn) => btn.classList.contains('room-filter')
                    ? (btn.dataset.roomName || '').trim() === ABT
                    : /^mc-pill-.+/.test(btn.id || '') && (btn.textContent || '').trim().startsWith(ABT)

                document.addEventListener('click', (e) => {
                    const btn = e.target.closest('.room-filter, [id^="mc-pill-"]')
                    if (!btn || !isAbt(btn)) return
                    if (window.__abtGateBypass) { window.__abtGateBypass = false; return }
                    e.preventDefault(); e.stopPropagation(); e.stopImmediatePropagation()
                    const modalEl = document.querySelector('[x-data="abtGateModal()"]')
                    if (modalEl && window.Alpine) window.Alpine.$data(modalEl).show(btn)
                }, true)
            })()

        window.userRole = @json(auth()->check() ? (auth()->user()->hasRole('PIC') ? 'PIC' : (auth()->user()->hasRole('TU') ? 'TU' : 'USER')) : 'GUEST');
        document.documentElement.setAttribute('data-user-role', window.userRole)
    </script>

    {{-- Modal Ajukan Rapat (PIC only) — pindahkan blok lamamu ke file partial ini --}}
    @if(auth()->user()?->hasRole('PIC'))

        @php
            // Daftar jam 07.00 - 21.00, tiap 15 menit
            $times = [];
            for ($h = 7; $h <= 21; $h++) {
                foreach ([0, 15, 30, 45] as $m) {
                    if ($h === 21 && $m > 0)
                        continue;
                    $times[] = sprintf('%02d:%02d', $h, $m);
                }
            }

            // Unit kerja yang tampil menyesuaikan username PIC
            $userUsername = auth()->user()->username ?? '';
            $showD = [];
            foreach ([1, 2, 3, 4] as $n) {
                $showD[$n] = str_contains($userUsername, "deputi-$n");
            }
            $showBiro = in_array($userUsername, ['biro-mkdi', 'biro-hks', 'biro-sdmo', 'biro-kbmn', 'biro-uhm', 'inspektorat', 'Sahli']);
            $showAll = !in_array(true, $showD, true) && !$showBiro;

            $unitGroups = [];
            foreach ($showD as $n => $show) {
                if ($show) {
                    $unitGroups["Deputi $n"] = array_merge(["Deputi $n", "Sesdep D$n"], array_map(fn($i) => "Asdep $i D$n", range(1, 5)));
                }
            }
            if ($showBiro || $showAll) {
                $unitGroups['Sekretariat & Lainnya'] = ['Biro MKDI', 'Biro UHM', 'Biro HKS', 'Biro SDMO', 'Biro KBMN', 'Inspektorat', 'Staff Ahli', 'Sesmenko', 'Wamenko'];
            }

            // Ikon (gaya Lucide)
            $ic = [
                'calendar' => '<path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="M12 14v4"/><path d="M10 16h4"/>',
                'x' => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
                'building' => '<path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4M10 10h4M10 14h4M10 18h4"/>',
                'clock' => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
                'circle-alert' => '<circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/>',
                'chevron' => '<path d="m6 9 6 6 6-6"/>',
                'lock' => '<rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
            ];
            $svg = fn(string $name, string $cls = 'h-4 w-4', string $sw = '1.75') =>
                '<svg class="' . $cls . '" fill="none" stroke="currentColor" stroke-width="' . $sw . '" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">' . $ic[$name] . '</svg>';

            $fieldCls = 'w-full rounded-lg border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 outline-none transition focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-500/10';
            $labelCls = 'mb-1.5 block text-xs font-bold text-slate-600';
            $noArrow = '-webkit-appearance:none;-moz-appearance:none;appearance:none;';
            $selectArrow = 'pointer-events-none absolute top-1/2 -translate-y-1/2 text-slate-400';
        @endphp

        <script>
            // Ruangan yang sedang dipilih di sidebar (desktop) -> dipakai untuk preselect di form
            window.activeRoomId = document.getElementById('room-sidebar')?.dataset.activeRoom || '';
            window.activeRoomName = (() => {
                const btn = document.querySelector(`.room-filter[data-room-id="${window.activeRoomId}"]`);
                return btn?.dataset.roomName || 'Semua Ruang';
            })();
            window.activeRoomMaintenance = (() => {
                const btn = document.querySelector(`.room-filter[data-room-id="${window.activeRoomId}"]`);
                return btn?.dataset.maintenance === '1';
            })();
            document.addEventListener('click', (e) => {
                const btn = e.target.closest('.room-filter');
                if (!btn || btn.dataset.maintenance === '1') return;
                window.activeRoomId = btn.dataset.roomId || '';
                window.activeRoomName = btn.dataset.roomName || 'Semua Ruang';
                window.activeRoomMaintenance = false;
            });

            // Tombol "+ Ajukan Rapat" di banner
            function bukaModalAjukan() {
                const isMobile = window.innerWidth < 1024
                const d = (isMobile && window.mobileCal) ? window.mobileCal.getSelectedDate() : ''
                window.dispatchEvent(new CustomEvent('open-booking-modal', {
                    detail: { start: d ? d + 'T00:00' : '' }
                }))
            }
        </script>

        <div x-data="bookingModal()" x-init="init()" x-show="open" x-cloak
            x-on:open-booking-modal.window="openModal($event.detail || {})" x-on:keydown.escape.window="close()"
            class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" x-on:click="close()"></div>

            <div x-show="open" x-transition
                class="relative flex w-full max-w-lg flex-col overflow-hidden rounded-2xl bg-white text-left shadow-2xl"
                style="max-height:90vh;" role="dialog" aria-modal="true" aria-labelledby="bookingModalTitle">

                {{-- Header --}}
                <div class="flex items-start justify-between gap-3 border-b border-slate-100 px-5 pb-4 pt-5">
                    <div class="flex min-w-0 items-center gap-3">
                        <span
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-500">
                            {!! $svg('calendar', 'h-5 w-5') !!}
                        </span>
                        <div class="min-w-0">
                            <h3 id="bookingModalTitle" class="text-base font-extrabold text-slate-900">Ajukan rapat</h3>
                            <p class="mt-0.5 text-xs text-slate-500">Pengajuan akan ditinjau oleh TU ruangan terkait.</p>
                        </div>
                    </div>
                    <button type="button" x-on:click="close()" aria-label="Tutup"
                        class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                        {!! $svg('x', 'h-5 w-5', '2') !!}
                    </button>
                </div>

                <form method="POST" action="{{ route('bookings.store') }}" class="flex min-h-0 flex-1 flex-col">
                    @csrf
                    <input type="hidden" name="confirm_pending" value="1" :disabled="!hasWarning">

                    <div class="flex-1 space-y-5 overflow-y-auto px-5 py-5">

                        @if ($errors->any())
                            <div
                                class="flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-[13px] text-rose-800">
                                <span class="mt-0.5 shrink-0 text-rose-600">{!! $svg('circle-alert', 'h-4 w-4', '2') !!}</span>
                                <div>
                                    <p class="font-bold">Periksa kembali isian Anda</p>
                                    <ul class="mt-1 list-inside list-disc space-y-0.5 font-medium text-rose-700">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif

                        @if (session('pending_warning'))
                            <div x-show="hasWarning" x-cloak
                                class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-[13px] text-amber-900">
                                <span class="mt-0.5 shrink-0 text-amber-600">{!! $svg('circle-alert', 'h-4 w-4', '2') !!}</span>
                                <div>
                                    <p class="font-bold">Sudah ada pengajuan lain di ruangan & waktu yang sama</p>
                                    <ul class="mt-1 list-inside list-disc space-y-0.5 font-medium text-amber-800">
                                        @foreach (session('pending_warning') as $p)
                                            <li>
                                                <strong>{{ $p['unit_kerja'] }}</strong> — {{ $p['title'] }}
                                                ({{ $p['start_at'] }} – {{ $p['end_at'] }})
                                            </li>
                                        @endforeach
                                    </ul>
                                    <p class="mt-2">
                                        Anda tetap bisa mengajukan, tetapi pengajuan Anda bisa ditolak jika
                                        pengajuan tersebut disetujui lebih dulu. Klik <strong>Tetap kirim</strong> untuk
                                        melanjutkan.
                                    </p>
                                </div>
                            </div>
                        @endif

                        {{-- RUANGAN --}}
                        <div>
                            <label class="{{ $labelCls }}">Ruangan <span class="text-rose-500">*</span></label>

                            {{-- Terkunci kalau datang dari rekomendasi dashboard --}}
                            <template x-if="lockRoom">
                                <div>
                                    <input type="hidden" name="room_id" :value="roomId">
                                    <div
                                        class="flex w-full items-center gap-2.5 rounded-lg border border-slate-200 bg-slate-100/70 px-3.5 py-2.5 text-sm font-semibold text-slate-800">
                                        <span class="text-indigo-500">{!! $svg('building', 'h-4 w-4', '2') !!}</span>
                                        <span class="min-w-0 flex-1 truncate" x-text="roomName"></span>
                                        <span class="text-slate-400"
                                            title="Ruangan dipilih dari rekomendasi">{!! $svg('lock', 'h-3.5 w-3.5', '2') !!}</span>
                                    </div>
                                </div>
                            </template>

                            <template x-if="!lockRoom">
                                <div class="relative">
                                    <select name="room_id" x-model="roomId" class="{{ $fieldCls }} pr-9"
                                        style="{{ $noArrow }}" required>
                                        <option value="" disabled>Pilih ruangan rapat</option>
                                        @foreach($activeRooms as $room)
                                            <option value="{{ $room->id }}" {{ $room->maintenance ? 'disabled' : '' }}>
                                                {{ $room->name }}{{ $room->maintenance ? ' — Sedang perbaikan' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="{{ $selectArrow }} right-3">{!! $svg('chevron', 'h-4 w-4', '2') !!}</span>
                                </div>
                            </template>
                        </div>

                        {{-- JUDUL --}}
                        <div>
                            <label class="{{ $labelCls }}">Judul kegiatan <span class="text-rose-500">*</span></label>
                            <input name="title" value="{{ old('title') }}" placeholder="Contoh: Rapat Koordinasi Tim"
                                class="{{ $fieldCls }}" required />
                        </div>

                        {{-- JADWAL --}}
                        <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4">
                            <div class="mb-3 flex items-center justify-between gap-2">
                                <span class="flex items-center gap-1.5 text-xs font-bold text-slate-600">
                                    <span class="text-indigo-500">{!! $svg('clock', 'h-4 w-4', '2') !!}</span>
                                    Jadwal
                                </span>
                                <span x-show="duration" x-cloak
                                    class="rounded-full bg-indigo-50 px-2.5 py-0.5 text-[11px] font-bold text-indigo-700 ring-1 ring-inset ring-indigo-600/20"
                                    x-text="'Durasi ' + duration"></span>
                            </div>

                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                <div>
                                    <label class="{{ $labelCls }}">Tanggal <span class="text-rose-500">*</span></label>
                                    <input type="date" name="booking_date" x-model="bookingDate"
                                        class="{{ $fieldCls }} bg-white" required />
                                </div>

                                <div>
                                    <label class="{{ $labelCls }}">Jam mulai <span class="text-rose-500">*</span></label>
                                    <div class="relative">
                                        <select name="start_time" x-model="startTime" @change="autoSetEndTime()"
                                            class="{{ $fieldCls }} bg-white pr-8 tabular-nums" style="{{ $noArrow }}"
                                            required>
                                            <option value="" disabled>Pilih</option>
                                            @foreach($times as $t)
                                                <option value="{{ $t }}">{{ $t }}</option>
                                            @endforeach
                                        </select>
                                        <span
                                            class="{{ $selectArrow }} right-2.5">{!! $svg('chevron', 'h-4 w-4', '2') !!}</span>
                                    </div>
                                </div>

                                <div>
                                    <label class="{{ $labelCls }}">Jam selesai <span class="text-rose-500">*</span></label>
                                    <div class="relative">
                                        <select name="end_time" x-model="endTime"
                                            :class="timeInvalid ? 'border-rose-300 focus:border-rose-400 focus:ring-rose-500/10' : ''"
                                            class="{{ $fieldCls }} bg-white pr-8 tabular-nums" style="{{ $noArrow }}"
                                            required>
                                            <option value="" disabled>Pilih</option>
                                            @foreach($times as $t)
                                                <option value="{{ $t }}">{{ $t }}</option>
                                            @endforeach
                                        </select>
                                        <span
                                            class="{{ $selectArrow }} right-2.5">{!! $svg('chevron', 'h-4 w-4', '2') !!}</span>
                                    </div>
                                </div>
                            </div>

                            <p x-show="timeInvalid" x-cloak class="mt-2.5 text-xs font-semibold text-rose-600">Jam selesai
                                harus setelah jam mulai.</p>
                        </div>

                        {{-- UNIT KERJA --}}
                        <div>
                            <label class="{{ $labelCls }}">Unit kerja <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <select name="unit_kerja" class="{{ $fieldCls }} pr-9" style="{{ $noArrow }}" required>
                                    <option value="" disabled {{ old('unit_kerja') ? '' : 'selected' }}>Pilih unit kerja
                                    </option>
                                    @foreach($unitGroups as $groupLabel => $units)
                                        <optgroup label="{{ $groupLabel }}">
                                            @foreach($units as $u)
                                                <option value="{{ $u }}" {{ old('unit_kerja') == $u ? 'selected' : '' }}>{{ $u }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                <span class="{{ $selectArrow }} right-3">{!! $svg('chevron', 'h-4 w-4', '2') !!}</span>
                            </div>
                        </div>

                        {{-- EMAIL --}}
                        <div>
                            <label class="{{ $labelCls }}">Email penerima notifikasi <span
                                    class="text-rose-500">*</span></label>
                            <input type="email" name="applicant_email" value="{{ old('applicant_email') }}"
                                placeholder="email@domain.com" class="{{ $fieldCls }}" required />
                            <p class="mt-1.5 text-xs text-slate-400">Status persetujuan akan dikirim ke email ini.</p>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="flex justify-end gap-2 border-t border-slate-100 bg-slate-50/60 px-5 py-3.5">
                        <button type="button" x-on:click="close()"
                            class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-[13px] font-semibold text-slate-700 transition hover:bg-slate-50">Batal</button>
                        <button type="submit" :disabled="timeInvalid"
                            :class="hasWarning ? 'bg-amber-500 hover:bg-amber-600 shadow-amber-500/25' : 'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-600/25'"
                            class="rounded-lg px-5 py-2 text-[13px] font-semibold text-white shadow-sm transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            x-text="hasWarning ? 'Tetap kirim' : 'Kirim pengajuan'"></button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function bookingModal() {
                const pad = (n) => String(n).padStart(2, '0')
                const toMin = (t) => { const [h, m] = t.split(':').map(Number); return h * 60 + m }
                const toTime = (m) => `${pad(Math.floor(m / 60))}:${pad(m % 60)}`

                return {
                    open: {{ ($errors->any() || session('pending_warning')) ? 'true' : 'false' }},
                    hasWarning: @json((bool) session('pending_warning')),
                    prefill: @json($prefill),
                    bookingDate: @json(old('booking_date', '')),
                    startTime: @json(old('start_time', '')),
                    endTime: @json(old('end_time', '')),
                    roomId: @json((string) old('room_id', '')),
                    roomName: '',
                    lockRoom: false,

                    // Durasi rapat (badge di bagian Jadwal)
                    get minutes() {
                        if (!this.startTime || !this.endTime) return null
                        return toMin(this.endTime) - toMin(this.startTime)
                    },
                    get timeInvalid() { return this.minutes !== null && this.minutes <= 0 },
                    get duration() {
                        const m = this.minutes
                        if (m === null || m <= 0) return ''
                        return (m >= 60 ? Math.floor(m / 60) + ' jam ' : '') + (m % 60 ? (m % 60) + ' menit' : '')
                    },

                    init() {
                        this.syncWithSidebar()

                            // Ubah ruangan/jadwal -> peringatan & konfirmasi tidak berlaku lagi
                            ;['roomId', 'bookingDate', 'startTime', 'endTime'].forEach((k) =>
                                this.$watch(k, () => { this.hasWarning = false })
                            )

                        if (!this.prefill) return

                        const p = this.prefill
                        const go = () => window.dispatchEvent(new CustomEvent('open-booking-modal', { detail: { prefill: p } }))

                        setTimeout(() => {
                            if (p.is_abt) {
                                // Ruang ABT: tetap lewat modal konfirmasi, form baru terbuka setelah "Ya, Lanjutkan"
                                const btn = document.querySelector('.room-filter[data-room-name="Ruang Rapat ABT"]')
                                const gate = document.querySelector('[x-data="abtGateModal()"]')
                                if (btn && gate && window.Alpine) window.Alpine.$data(gate).show(btn, go)
                                return
                            }
                            go()
                        }, 0)

                        // Bersihkan parameter supaya refresh tidak membuka form lagi
                        history.replaceState(null, '', window.location.pathname)
                    },

                    // Preselect ruangan sesuai pilihan di sidebar (kalau bukan "Semua" & tidak maintenance)
                    syncWithSidebar() {
                        if (this.lockRoom) return
                        if (window.activeRoomId && !window.activeRoomMaintenance) {
                            this.roomId = String(window.activeRoomId)
                        }
                    },

                    openModal(payload = {}) {
                        this.lockRoom = false
                        this.open = true
                        this.syncWithSidebar()

                        if (payload.start) {
                            const [date, time] = payload.start.split('T')
                            this.bookingDate = date
                            // jam dari klik kalender (abaikan 00:00 dari tombol "Ajukan Rapat")
                            if (time && time.slice(0, 5) !== '00:00') {
                                this.startTime = time.slice(0, 5)
                                this.endTime = ''
                                this.autoSetEndTime()
                            }
                        } else if (!this.bookingDate) {
                            const t = new Date()
                            this.bookingDate = `${t.getFullYear()}-${pad(t.getMonth() + 1)}-${pad(t.getDate())}`
                        }

                        // Isi dari rekomendasi dashboard
                        const p = payload.prefill
                        if (p) {
                            this.lockRoom = true
                            this.roomId = String(p.room_id)
                            this.roomName = p.room_name
                            this.bookingDate = p.date
                            if (p.start) { this.startTime = p.start; this.endTime = p.end }
                        }
                    },

                    // Jam selesai otomatis = jam mulai + 1 jam (maks 21:00), kalau kosong / tidak valid
                    autoSetEndTime() {
                        if (!this.startTime) return
                        const s = toMin(this.startTime)
                        if (!this.endTime || toMin(this.endTime) <= s) {
                            this.endTime = toTime(Math.min(s + 60, 21 * 60))
                        }
                    },

                    close() { this.open = false }
                }
            }
        </script>
    @endif

    {{-- Modal Konfirmasi Ruang Rapat ABT --}}
    <div x-data="abtGateModal()" x-show="open" x-cloak @keydown.escape.window="cancel()"
        class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/55 backdrop-blur-[1.5px]" @click="cancel()"></div>
        <div x-show="open" x-transition role="alertdialog" aria-modal="true" aria-labelledby="abt-gate-title"
            class="relative max-h-[calc(100vh-32px)] w-full max-w-[440px] overflow-y-auto rounded-[20px] bg-white shadow-2xl ring-1 ring-slate-900/5">
            <div class="flex items-start gap-3 px-5 pb-4 pt-5">
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-indigo-500">Konfirmasi Agenda</p>
                    <h3 id="abt-gate-title" class="text-[17px] font-extrabold leading-snug text-slate-900">Ruang Rapat
                        ABT</h3>
                </div>
                <button type="button" @click="cancel()" aria-label="Tutup"
                    class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">✕</button>
            </div>
            <p class="px-5 pb-5 text-sm leading-relaxed text-slate-600">
                Ruang ini khusus digunakan untuk agenda <strong class="font-bold text-slate-900">ABT</strong>.
                Apakah pengajuan rapat Anda terkait dengan agenda ABT?
            </p>
            <div
                class="flex flex-col-reverse gap-2 border-t border-slate-200 bg-slate-50 px-5 py-4 sm:flex-row sm:justify-end">
                <button type="button" @click="cancel()"
                    class="rounded-xl bg-slate-200 px-[18px] py-2.5 text-[13.5px] font-bold text-slate-700 transition hover:bg-slate-300 active:scale-95">Tidak</button>
                <button type="button" @click="confirm()"
                    class="rounded-xl bg-indigo-600 px-[18px] py-2.5 text-[13.5px] font-bold text-white shadow-md shadow-indigo-600/40 transition hover:bg-indigo-700 active:scale-95">Ya,
                    Lanjutkan</button>
            </div>
        </div>
    </div>

    {{-- Modal Detail Meeting --}}
    <div x-data="meetingDetailModal()" x-show="open" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/50" @click="close()"></div>
        <div class="relative mx-4 w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-xl">
            <div class="flex items-start justify-between border-b p-5">
                <div class="min-w-0">
                    <h3 class="text-lg font-semibold text-gray-900" x-text="data.title"></h3>
                    <p class="mt-1 text-sm text-gray-500" x-text="data.room || ''"></p>
                </div>
                <button class="text-gray-400 hover:text-gray-600" @click="close()">✕</button>
            </div>
            <div class="space-y-4 p-5">
                <div class="flex flex-wrap gap-2">
                    <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="badgeClass(data.status)"
                        x-text="data.status || '-'"></span>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700"
                        x-text="'PIC: ' + (data.pic || '-')"></span>
                </div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="rounded-xl bg-gray-50 p-3">
                        <div class="text-xs text-gray-500">Mulai</div>
                        <div class="font-semibold text-gray-900" x-text="data.start || '-'"></div>
                    </div>
                    <div class="rounded-xl bg-gray-50 p-3">
                        <div class="text-xs text-gray-500">Selesai</div>
                        <div class="font-semibold text-gray-900" x-text="data.end || '-'"></div>
                    </div>
                </div>
                <div class="rounded-xl bg-gray-50 p-3">
                    <div class="text-xs text-gray-500">Deskripsi</div>
                    <div class="mt-1 whitespace-pre-wrap text-sm text-gray-800" x-text="data.description || '-'"></div>
                </div>
            </div>
            <div class="flex justify-end gap-2 border-t p-5">
                <button class="rounded-xl bg-gray-100 px-4 py-2 hover:bg-gray-200" @click="close()">Tutup</button>
            </div>
        </div>
    </div>

    <script>
        function abtGateModal() {
            return {
                open: false, _btn: null, _cb: null,
                show(btn, cb) { this._btn = btn; this._cb = cb || null; this.open = true },
                cancel() { this.open = false; this._btn = this._cb = null },
                confirm() {
                    const { _btn: btn, _cb: cb } = this
                    this.cancel()
                    if (btn) { window.__abtGateBypass = true; btn.click() }
                    if (cb) cb()
                }
            }
        }

        function meetingDetailModal() {
            const colors = { APPROVED: 'bg-green-100 text-green-700', PENDING: 'bg-yellow-100 text-yellow-700', REJECTED: 'bg-red-100 text-red-700' }
            return {
                open: false,
                data: { title: '', room: '', status: '', pic: '', start: '', end: '', description: '' },
                show(payload) { this.data = payload; this.open = true },
                close() { this.open = false },
                badgeClass(status) { return colors[(status || '').toUpperCase()] || 'bg-gray-100 text-gray-700' }
            }
        }
    </script>

    {{-- JS kalender mobile --}}
    <script>
        (function () {

            const MONTHS = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
            const DAYS = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']

            const STATUS = {
                APPROVED: { label: 'Disetujui', cls: 'bg-emerald-50 text-emerald-800 ring-emerald-200', dot: 'bg-emerald-500' },
                PENDING: { label: 'Menunggu', cls: 'bg-amber-50 text-amber-800 ring-amber-200', dot: 'bg-amber-500' },
            }
            const CHIP_ALL = 'bg-indigo-600 text-white ring-indigo-600'
            const CHIP_ROOM = 'bg-slate-900 text-white ring-slate-900'
            const CHIP_OFF = 'bg-white text-slate-700 ring-slate-300'
            const split = (s) => s.split(' ')

            const svg = (p) =>
                `<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">${p}</svg>`
            const ICON = {
                clock: svg('<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>'),
                pin: svg('<path d="M12 21s-7-6.2-7-11a7 7 0 1 1 14 0c0 4.8-7 11-7 11Z"/><circle cx="12" cy="10" r="2.5"/>'),
                building: svg('<path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M10 6h4M10 10h4M10 14h4M10 18h4"/>'),
            }

            const pad = (n) => String(n).padStart(2, '0')
            const toDateStr = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`
            const fmtTime = (s) => (s ? s.slice(11, 16).replace(':', '.') : '')
            const fmtHuman = (s) => {
                const d = new Date(s)
                return isNaN(d)
                    ? '-'
                    : d.toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: false })
            }
            const esc = (s) =>
                String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]))

            function initMobileCalendar() {
                const root = document.getElementById('mobile-calendar-app')
                if (!root) return

                const $ = (id) => document.getElementById(id)
                const pills = [...root.querySelectorAll('#mc-room-filters [data-room-id]')]
                const colors = Object.fromEntries(pills.map((p) => [p.dataset.roomId, p.dataset.color]))
                const colorOf = (id) => colors[id] || '#9ca3af'

                const now = new Date()
                let year = now.getFullYear()
                let month = now.getMonth()
                let selected = toDateStr(now)
                let room = ''
                let bookings = []
                let items = []
                let loadedYear = null

                async function load() {
                    if (loadedYear === year) return
                    try {
                        const res = await fetch(`/api/bookings?start=${year}-01-01&end=${year}-12-31`)
                        const json = await res.json()
                        const list = Array.isArray(json) ? json : json.data || []
                        bookings = list.map((e) => {
                            const x = e.extendedProps || {}
                            return {
                                title: e.title,
                                startRaw: e.start,
                                endRaw: e.end,
                                start: (e.start || '').replace('T', ' '),
                                end: (e.end || '').replace('T', ' '),
                                room_id: String(x.room_id ?? e.room_id ?? ''),
                                room_name: x.room_name ?? e.room_name ?? '',
                                unit_kerja: x.unit_kerja ?? x.pic ?? e.unit_kerja ?? '-',
                                status: String(x.status ?? e.status ?? 'APPROVED').toUpperCase(),
                                description: x.description ?? e.description ?? '',
                            }
                        })
                        loadedYear = year
                    } catch (err) {
                        console.error('Gagal load booking:', err)
                        bookings = []
                    }
                }

                // booking yang jatuh di tanggal tertentu (mendukung booking lintas hari)
                const forDay = (dateStr) =>
                    bookings
                        .filter((b) => {
                            const s = b.start.slice(0, 10)
                            const e = (b.end || b.start).slice(0, 10)
                            return (
                                s <= dateStr && dateStr <= e &&
                                ['APPROVED', 'PENDING'].includes(b.status) &&
                                (!room || b.room_id === room)
                            )
                        })
                        .sort((a, b) => a.start.localeCompare(b.start))

                function renderGrid() {
                    $('mc-month-label').textContent = `${MONTHS[month]} ${year}`
                    const today = toDateStr(new Date())
                    const offset = (new Date(year, month, 1).getDay() + 6) % 7
                    const total = new Date(year, month + 1, 0).getDate()
                    const t = new Date()
                    $('mc-today-btn')?.classList.toggle('hidden', selected === today && year === t.getFullYear() && month === t.getMonth())

                    let html = '<div></div>'.repeat(offset)
                    for (let d = 1; d <= total; d++) {
                        const dateStr = `${year}-${pad(month + 1)}-${pad(d)}`
                        const col = (offset + d - 1) % 7
                        const isSel = dateStr === selected
                        const state = isSel
                            ? 'bg-indigo-600 text-white'
                            : dateStr === today
                                ? 'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200'
                                : (col > 4 ? 'text-rose-500' : 'text-slate-700') + ' hover:bg-slate-50'

                        const evs = forDay(dateStr)
                        const dots = [...new Set(evs.map((b) => colorOf(b.room_id)))]
                            .slice(0, 4)
                            .map((c) => `<span class="h-1.5 w-1.5 rounded-full ring-1 ${isSel ? 'ring-white/70' : 'ring-white'}" style="background:${c}"></span>`)
                            .join('')
                        const chips = evs
                            .slice(0, 2)
                            .map(
                                (b) => `<span class="flex items-center gap-1 truncate rounded px-1 py-px text-left text-[10px] font-semibold ${isSel ? 'bg-white/20 text-white' : 'bg-slate-50 text-slate-700'}">
            <i class="h-1.5 w-1.5 shrink-0 rounded-full" style="background:${colorOf(b.room_id)}"></i><span class="truncate">${esc(b.title)}</span></span>`
                            )
                            .join('')
                        const more = evs.length > 2 ? `<span class="px-1 text-left text-[10px] font-bold ${isSel ? 'text-white/80' : 'text-slate-400'}">+${evs.length - 2} lagi</span>` : ''

                        html += `<button type="button" data-date="${dateStr}"
        class="mx-auto flex h-11 w-11 flex-col items-center justify-center rounded-full text-xs font-semibold transition sm:mx-0 sm:h-auto sm:min-h-[84px] sm:w-full sm:items-stretch sm:justify-start sm:rounded-xl sm:p-1.5 ${state}">
        <span class="sm:text-sm sm:px-0.5">${d}</span>
        <span class="mt-0.5 flex h-1.5 items-center justify-center gap-0.5 sm:hidden">${dots}</span>
        <span class="mt-1 hidden w-full flex-col gap-0.5 overflow-hidden sm:flex">${chips}${more}</span>
      </button>`
                    }
                    $('mc-grid').innerHTML = html
                }

                function renderAgenda() {
                    const d = new Date(selected + 'T00:00:00')
                    $('mc-date-label').textContent = `${DAYS[d.getDay()]}, ${d.getDate()} ${MONTHS[d.getMonth()]} ${d.getFullYear()}`
                    items = forDay(selected)
                    const el = $('mc-agenda')

                    if (!items.length) {
                        el.innerHTML = `<div class="py-10 text-center text-slate-400">
        <div class="text-sm font-semibold">Tidak ada jadwal</div>
        <div class="mt-1 text-xs">Belum ada kegiatan pada tanggal ini.</div></div>`
                        return
                    }

                    el.innerHTML = items
                        .map((b, i) => {
                            const st = STATUS[b.status] || { label: b.status, cls: 'bg-slate-100 text-slate-700 ring-slate-200', dot: 'bg-slate-400' }
                            const unit = b.unit_kerja && b.unit_kerja !== '-'
                                ? `<span class="mt-1.5 inline-flex max-w-full items-center gap-1.5 rounded-md bg-indigo-50 px-2 py-0.5 text-[11px] font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-100">${ICON.building}<span class="truncate">${esc(b.unit_kerja)}</span></span>`
                                : ''
                            return `<button type="button" data-i="${i}" class="flex w-full items-start gap-3 rounded-2xl bg-white p-3.5 text-left shadow-sm transition active:scale-[.99]">
          <span class="mt-0.5 h-12 w-1 shrink-0 rounded-full" style="background:${colorOf(b.room_id)}"></span>
          <span class="min-w-0 flex-1">
            <span class="flex items-start justify-between gap-2">
              <span class="text-sm font-semibold leading-snug text-slate-900">${esc(b.title)}</span>
              <span class="inline-flex shrink-0 items-center gap-1.5 rounded-md px-2 py-0.5 text-[11px] font-semibold ring-1 ring-inset ${st.cls}">
                <span class="h-1.5 w-1.5 rounded-full ${st.dot}"></span>${st.label}</span>
            </span>
            <span class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-0.5 text-xs text-slate-500">
              <span class="inline-flex items-center gap-1"><span class="text-slate-400">${ICON.clock}</span><span class="tabular-nums">${fmtTime(b.start)} – ${fmtTime(b.end)}</span></span>
              <span class="inline-flex min-w-0 items-center gap-1"><span class="text-slate-400">${ICON.pin}</span><span class="truncate">${esc(b.room_name)}</span></span>
            </span>
            ${unit}
          </span></button>`
                        })
                        .join('')
                }

                const render = () => { renderGrid(); renderAgenda() }

                async function move(delta) {
                    month += delta
                    if (month < 0) { month = 11; year-- }
                    if (month > 11) { month = 0; year++ }
                    await load()
                    render()
                }

                function filterRoom(id) {
                    room = String(id)
                    pills.forEach((btn) => {
                        if (btn.disabled) return
                        const on = btn.dataset.roomId === room
                        btn.classList.remove(...split(CHIP_ALL), ...split(CHIP_ROOM), ...split(CHIP_OFF))
                        btn.classList.add(...split(on ? (room ? CHIP_ROOM : CHIP_ALL) : CHIP_OFF))
                    })
                    render()
                }

                function openDetail(b) {
                    const modalEl = document.querySelector('[x-data="meetingDetailModal()"]')
                    if (!modalEl || !window.Alpine) return
                    window.Alpine.$data(modalEl).show({
                        title: b.title,
                        room: b.room_name ? `Ruang: ${b.room_name}` : '',
                        status: b.status,
                        pic: b.unit_kerja,
                        start: fmtHuman(b.startRaw),
                        end: fmtHuman(b.endRaw),
                        description: b.description,
                    })
                }

                // satu listener untuk semua tombol di kalender mobile
                root.addEventListener('click', async (e) => {
                    const el = e.target.closest('[data-act],[data-date],[data-i],[data-room-id]')
                    if (!el || el.disabled) return
                    if (el.dataset.act === 'prev') return move(-1)
                    if (el.dataset.act === 'next') return move(1)
                    if (el.dataset.act === 'today') {
                        const n = new Date()
                        year = n.getFullYear(); month = n.getMonth(); selected = toDateStr(n)
                        await load()
                        return render()
                    }
                    if (el.dataset.date) { selected = el.dataset.date; return render() }
                    if (el.dataset.i !== undefined) return items[el.dataset.i] && openDetail(items[el.dataset.i])
                    if (el.dataset.roomId !== undefined) return filterRoom(el.dataset.roomId)
                })

                window.mobileCal = { getSelectedDate: () => selected }

                load().then(render)
            }

            if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initMobileCalendar)
            else initMobileCalendar()

        })()
    </script>

    <style>
        /* resources/css/calendar-page.css — CSS halaman kalender (dipindah dari index blade) */
        #calendar {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        select {
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            appearance: none !important;
        }

        /* ---- Sidebar ruangan ---- */
        .room-filter {
            display: flex;
            width: 100%;
            align-items: center;
            gap: 10px;
            padding: 7px 10px;
            border-radius: 12px;
            border: 1px solid transparent;
            font-size: 12.5px;
            font-weight: 600;
            color: #475569;
            text-align: left;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: transparent;
            cursor: pointer;
            transition: background .15s, color .15s, border-color .15s;
        }

        .room-filter:not(:disabled):hover {
            background: #f5f7ff;
            color: #1e293b;
        }

        .room-filter.is-active {
            background: #eef2ff;
            color: #3730a3;
            border-color: #c7d2fe;
        }

        .room-filter .room-check {
            display: none;
            color: #4f46e5;
            flex-shrink: 0;
        }

        .room-filter.is-active .room-check {
            display: block;
        }

        .room-filter:disabled {
            cursor: not-allowed;
            opacity: .65;
        }

        .room-filter:focus-visible {
            outline: 2px solid #6366f1;
            outline-offset: 2px;
        }

        /* ---- FullCalendar ---- */
        .fc {
            --fc-border-color: #e3e9fb;
            --fc-today-bg-color: #e8edff;
            --fc-page-bg-color: transparent;
        }

        .fc .fc-toolbar {
            margin-bottom: .75rem !important;
            gap: 8px;
            flex-wrap: wrap;
        }

        .fc .fc-toolbar-title {
            font-size: 1.1rem !important;
            font-weight: 800 !important;
            color: #0f1e5a !important;
            letter-spacing: -0.02em;
        }

        .fc .fc-button {
            background: #eef2ff !important;
            border: 0 !important;
            color: #334155 !important;
            border-radius: 10px !important;
            font-weight: 600 !important;
            font-size: .75rem !important;
            padding: .45rem .8rem !important;
            box-shadow: none !important;
            text-transform: none !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            transition: background .15s, color .15s;
        }

        .fc .fc-button:hover {
            background: #e0e7ff !important;
            color: #1e293b !important;
        }

        .fc .fc-button:disabled {
            opacity: 1 !important;
        }

        .fc .fc-toolbar-chunk:first-child {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .fc .fc-toolbar-chunk:first-child .fc-button-group {
            display: flex;
            gap: 6px;
        }

        .fc .fc-toolbar-chunk:first-child .fc-button {
            margin: 0 !important;
        }

        .fc .fc-prev-button,
        .fc .fc-next-button {
            width: 34px;
            height: 34px;
            padding: 0 !important;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            background: #f1f5ff !important;
        }

        .fc .fc-today-button {
            color: #4f46e5 !important;
            background: #eef2ff !important;
        }

        .fc .fc-today-button::before {
            content: "";
            display: inline-block;
            width: 14px;
            height: 14px;
            margin-right: 6px;
            vertical-align: -3px;
            background: currentColor;
            -webkit-mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' stroke='black' stroke-width='1.8' viewBox='0 0 24 24'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M8 7V3m8 4V3M5 11h14M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z'/%3E%3C/svg%3E") center / contain no-repeat;
            mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' stroke='black' stroke-width='1.8' viewBox='0 0 24 24'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M8 7V3m8 4V3M5 11h14M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z'/%3E%3C/svg%3E") center / contain no-repeat;
        }

        /* Segmented Bulan / Minggu / Hari */
        .fc .fc-toolbar-chunk:last-child .fc-button-group {
            background: #eef2ff;
            border-radius: 12px;
            padding: 3px;
            gap: 2px;
            display: flex;
        }

        .fc .fc-toolbar-chunk:last-child .fc-button {
            background: transparent !important;
            color: #64748b !important;
            border-radius: 9px !important;
            margin: 0 !important;
            padding: .4rem .9rem !important;
        }

        .fc .fc-toolbar-chunk:last-child .fc-button:hover {
            color: #1e293b !important;
        }

        .fc .fc-toolbar-chunk:last-child .fc-button.fc-button-active,
        .fc .fc-toolbar-chunk:last-child .fc-button:active {
            background: #4f6af5 !important;
            color: #fff !important;
            box-shadow: 0 6px 14px -4px rgba(79, 106, 245, .5) !important;
        }

        /* ---- Grid ---- */
        .fc .fc-scrollgrid {
            border-radius: 14px !important;
            overflow: hidden !important;
            border: 1px solid #e3e9fb !important;
        }

        .fc td,
        .fc th {
            border-color: #e3e9fb !important;
        }

        .fc .fc-col-header-cell {
            background: #f1f5ff !important;
            padding: 8px 0 !important;
            border-bottom: 1px solid #e3e9fb !important;
        }

        .fc .fc-col-header-cell-cushion {
            font-size: .66rem !important;
            font-weight: 600 !important;
            text-transform: uppercase !important;
            letter-spacing: .06em !important;
            color: #64748b !important;
            text-decoration: none !important;
        }

        .fc .fc-day-today .fc-col-header-cell-cushion {
            color: #4f46e5 !important;
        }

        .fc .fc-day-today {
            background: #f3f5ff !important;
        }

        .fc .fc-daygrid-day-frame {
            min-height: 88px;
        }

        .fc .fc-daygrid-day-number {
            padding: 5px 9px !important;
            font-size: .75rem;
            font-weight: 600;
            color: #1e2a5a;
            text-decoration: none !important;
        }

        .fc .fc-day-other .fc-daygrid-day-number {
            color: #cbd5e1;
        }

        /* Sabtu & Minggu merah, hari ini berupa lingkaran */
        .fc .fc-col-header-cell.fc-day-sat .fc-col-header-cell-cushion,
        .fc .fc-col-header-cell.fc-day-sun .fc-col-header-cell-cushion {
            color: #f43f5e !important;
        }

        .fc .fc-daygrid-day.fc-day-sat .fc-daygrid-day-number,
        .fc .fc-daygrid-day.fc-day-sun .fc-daygrid-day-number {
            color: #e11d48;
        }

        .fc .fc-daygrid-day:not(.fc-day-today):hover {
            background: #f8faff;
        }

        .fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
            background: #4f46e5;
            color: #fff !important;
            border-radius: 999px;
            min-width: 22px;
            height: 22px;
            margin: 5px 7px;
            padding: 0 6px !important;
            font-size: .72rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        /* ---- Timegrid ---- */
        .fc .fc-timegrid-slot {
            height: 38px !important;
            border-color: #e3e9fb !important;
        }

        .fc .fc-timegrid-slot-label {
            font-size: .65rem !important;
            font-weight: 600 !important;
            color: #94a3b8 !important;
        }

        .fc .fc-timegrid-now-indicator-line {
            border-color: #6366f1 !important;
            border-width: 2px !important;
        }

        .fc .fc-timegrid-now-indicator-arrow {
            border-top-color: #6366f1 !important;
            border-bottom-color: #6366f1 !important;
        }

        /* ---- Event ---- */
        .fc .fc-event {
            font-size: .7rem;
        }

        .fc-timegrid-event-harness .fc-event {
            border-radius: 10px !important;
            border: none !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .08) !important;
            overflow: hidden !important;
        }

        .fc-timegrid-event .fc-event-main {
            padding: 4px 6px !important;
        }

        .fc-daygrid-event-harness {
            overflow: visible !important;
            position: relative !important;
            z-index: 1;
        }

        .fc-daygrid-event-harness:hover {
            z-index: 10;
        }

        .fc-daygrid-day-events {
            overflow: visible !important;
        }

        .fc-daygrid-event {
            white-space: normal !important;
            overflow: visible !important;
            border-radius: 8px !important;
        }

        .fc-event-main {
            overflow: visible !important;
        }
    </style>

    @push('scripts')
        @vite(['resources/js/calendar.js'])
    @endpush

</x-app-layout>