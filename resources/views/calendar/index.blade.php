<x-app-layout>

    {{-- NOTIFIKASI SUCCESS --}}
    @if(session('status'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
            class="fixed top-24 right-4 z-40">
            <div
                class="bg-green-50 border border-green-200 rounded-2xl p-4 shadow-lg flex items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-green-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    <div>
                        <p class="font-semibold text-green-800">Berhasil!</p>
                        <p class="text-sm text-green-700 mt-1">{{ session('status') }}</p>
                    </div>
                </div>
                <button @click="show = false" class="text-green-400 hover:text-green-600">✕</button>
            </div>
        </div>
    @endif

    @php
        $activeRoomId = request('room_id');
        $activeRooms = \App\Models\Room::where('active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();        // Warna ruangan diambil dari kolom rooms.color (diatur lewat RoomSeeder)
        $roomDotColors = $activeRooms->pluck('color', 'id')->all();
    @endphp

    {{-- ===== BANNER JUDUL ===== --}}
    <div class="px-4 pt-4 sm:px-8">
        <div
            class="relative flex items-center gap-3 overflow-hidden rounded-xl border border-white/70 bg-white/80 px-4 py-3 shadow-sm backdrop-blur sm:gap-4">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-500">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M8 7V3m8 4V3M5 11h14M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z" />
                </svg>
            </span>
            <div class="min-w-0 flex-1">
                <h1 class="text-base font-extrabold leading-tight text-[#0f1e5a] sm:text-lg">Kalender Booking Ruang
                    Rapat</h1>
                <p class="mt-0.5 text-xs text-slate-500">Lihat dan kelola jadwal pemesanan ruang rapat dengan mudah.</p>
            </div>

            {{-- Daun dekoratif --}}
            <svg class="pointer-events-none absolute -bottom-2 right-2 hidden h-16 w-16 text-indigo-200/70 sm:block"
                viewBox="0 0 120 120" fill="currentColor" aria-hidden="true">
                <path d="M60 120C50 80 55 45 80 15c12 35 5 75-20 105z" />
                <path d="M58 120C35 100 25 70 35 40c25 15 33 50 23 80z" opacity=".7" />
                <path d="M62 120c20-15 38-20 55-12-12 18-35 24-55 12z" opacity=".6" />
            </svg>

            @if(auth()->user()?->hasRole('PIC'))
                <button type="button" onclick="bukaModalAjukan()"
                    class="relative z-10 shrink-0 rounded-lg bg-indigo-600 px-3.5 py-2 text-xs font-bold text-white shadow-md shadow-indigo-600/25 transition hover:bg-indigo-700">
                    + Ajukan Rapat
                </button>
            @endif
        </div>
    </div>

    {{-- ===== MOBILE ===== --}}
    <div id="mobile-calendar-app" style="background:#f4f6fb;min-height:100vh;display:none;margin-top:12px;">
        <div class="bg-white px-4 pb-3 pt-4">
            {{-- Header bulan --}}
            <div class="mb-3 flex items-center justify-between gap-2">
                <button type="button" onclick="mobileCal.prevMonth()" aria-label="Bulan sebelumnya"
                    class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-600 transition hover:bg-slate-200">‹</button>
                <div class="text-center">
                    <div id="mc-month-label" class="text-base font-bold text-slate-900"></div>
                    <button type="button" id="mc-today-btn" onclick="mobileCal.goToday()"
                        class="hidden text-[11px] font-semibold text-indigo-600 hover:text-indigo-800">Kembali ke hari
                        ini</button>
                </div>
                <button type="button" onclick="mobileCal.nextMonth()" aria-label="Bulan berikutnya"
                    class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-600 transition hover:bg-slate-200">›</button>
            </div>

            {{-- Filter ruangan --}}
            <div class="mb-3 flex flex-wrap gap-1.5" id="mc-room-filters">
                <button type="button" id="mc-pill-" onclick="mobileCal.filterRoom('')"
                    class="rounded-full bg-indigo-600 px-2.5 py-1 text-[11px] font-semibold text-white ring-1 ring-inset ring-indigo-600 transition">Semua</button>
                @foreach($activeRooms as $room)
                    <button type="button" id="mc-pill-{{ $room->id }}" data-room-name="{{ $room->name }}"
                        data-maintenance="{{ $room->maintenance ? '1' : '0' }}" @if($room->maintenance) disabled
                        title="Ruangan sedang dalam perbaikan" @else onclick="mobileCal.filterRoom('{{ $room->id }}')"
                        @endif
                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 ring-inset transition {{ $room->maintenance ? 'cursor-not-allowed bg-orange-50 text-orange-700 ring-orange-200' : 'bg-white text-slate-600 ring-slate-200' }}">
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
                <div class="py-1">Sen</div>
                <div class="py-1">Sel</div>
                <div class="py-1">Rab</div>
                <div class="py-1">Kam</div>
                <div class="py-1">Jum</div>
                <div class="py-1 text-rose-400">Sab</div>
                <div class="py-1 text-rose-400">Min</div>
            </div>
            <div id="mc-grid" class="grid grid-cols-7 gap-y-0.5"></div>
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

            {{-- ---------- Sidebar ruangan (semua ruangan langsung terlihat) ---------- --}}
            <aside class="sticky top-20 w-60 shrink-0 rounded-2xl border border-slate-300 bg-white p-3 shadow-sm">
                <div class="mb-2.5 flex items-center gap-2.5 px-1.5">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-500">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6M9 10h.01M15 10h.01" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-[13px] font-extrabold text-[#0f1e5a]">Ruang Rapat</h2>
                        <p class="text-[11px] text-slate-400">{{ $activeRooms->count() }} ruangan tersedia</p>
                    </div>
                </div>

                {{-- #room-sidebar & .room-filter dipertahankan agar calendar.js tetap bekerja --}}
                <div id="room-sidebar" data-active-room="{{ $activeRoomId }}" class="space-y-1">
                    <button type="button" data-room-id="" data-room-name="Semua Ruang"
                        class="room-filter {{ $activeRoomId ? '' : 'is-active' }}">
                        <span class="flex h-3 w-3 shrink-0 items-center justify-center opacity-70"> <svg class="h-3 w-3"
                                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <rect x="3" y="3" width="7" height="7" rx="1.5" />
                                <rect x="14" y="3" width="7" height="7" rx="1.5" />
                                <rect x="3" y="14" width="7" height="7" rx="1.5" />
                                <rect x="14" y="14" width="7" height="7" rx="1.5" />
                            </svg>
                        </span>
                        <span class="flex-1 truncate">Semua Ruang</span>
                        <svg class="room-check h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5"
                            viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </button>

                    <div class="my-1.5 border-t border-slate-100"></div>

                    @foreach($activeRooms as $room)
                        @php $dotColor = $roomDotColors[$room->id] ?? '#9ca3af'; @endphp
                        <button type="button" data-room-id="{{ $room->id }}" data-room-name="{{ $room->name }}"
                            data-maintenance="{{ $room->maintenance ? '1' : '0' }}" {{ $room->maintenance ? 'disabled title="Ruangan sedang dalam perbaikan"' : '' }}
                            class="room-filter {{ (string) $activeRoomId === (string) $room->id ? 'is-active' : '' }}">
                            <span class="h-3 w-3 shrink-0 rounded-full"
                                style="background: {{ $dotColor }}; box-shadow: 0 0 0 2px #fff, 0 0 0 3.5px {{ $dotColor }};"></span>
                            <span class="flex-1 truncate">{{ $room->name }}</span>
                            @if($room->maintenance)
                                <span
                                    class="whitespace-nowrap rounded-md border border-orange-200 bg-orange-50 px-1.5 py-0.5 text-[10px] font-bold text-orange-700">Kegiatan
                                    BPK</span>
                            @else
                                <svg class="room-check h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5"
                                    viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            @endif
                        </button>
                    @endforeach
                </div>

            </aside>

            {{-- ---------- Kalender ---------- --}}
            <div class="min-w-0 flex-1">
                <div class="rounded-2xl border border-white/70 bg-white/90 p-4 shadow-sm backdrop-blur">
                    <div class="mb-2.5 flex flex-wrap items-center justify-between gap-3 text-xs">
                        <div class="flex items-center gap-2 text-slate-500">
                            <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18l-7 8v6l-4 2v-8L3 4z" />
                            </svg>
                            Menampilkan jadwal:
                            <span id="active-room-label"
                                class="rounded-lg bg-indigo-50 px-2 py-0.5 text-xs font-bold text-indigo-700">{{ $activeRooms->firstWhere('id', $activeRoomId)?->name ?? 'Semua Ruang' }}</span>
                        </div>
                    </div>

                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Penanda ruangan aktif di sidebar (calendar.js tetap menangani filter datanya) --}}
    <script>
        (function () {
            const sidebar = document.getElementById('room-sidebar')
            if (!sidebar) return
            document.addEventListener('click', function (e) {
                const btn = e.target.closest('.room-filter')
                if (!btn || !sidebar.contains(btn)) return
                if (btn.disabled || btn.dataset.maintenance === '1') return
                sidebar.querySelectorAll('.room-filter').forEach(b => b.classList.toggle('is-active', b === btn))
                sidebar.dataset.activeRoom = btn.dataset.roomId || ''
                const label = document.getElementById('active-room-label')
                if (label) label.textContent = btn.dataset.roomName || 'Semua Ruang'
            })
        })()
    </script>

    {{-- checkLayout --}}
    <script>
        function checkLayout() {
            const isMobile = window.innerWidth < 1024
            document.getElementById('mobile-calendar-app').style.display = isMobile ? 'block' : 'none'
            document.getElementById('desktop-calendar').style.display = isMobile ? 'none' : 'block'
        }
        checkLayout()
        window.addEventListener('resize', checkLayout)
    </script>

    {{-- GATE: konfirmasi sebelum membuka kalender Ruang Rapat ABT (listener fase capture) --}}
    <script>
            (function () {
                const ABT_ROOM_NAME = 'Ruang Rapat ABT'
                window.__abtGateBypass = false

                function isAbtButton(btn) {
                    if (!btn) return false
                    if (btn.classList.contains('room-filter')) {
                        return (btn.dataset.roomName || '').trim() === ABT_ROOM_NAME
                    }
                    if (/^mc-pill-.+/.test(btn.id || '')) {
                        return (btn.textContent || '').trim().startsWith(ABT_ROOM_NAME)
                    }
                    return false
                }

                document.addEventListener('click', function (e) {
                    const btn = e.target.closest('.room-filter, [id^="mc-pill-"]')
                    if (!btn || !isAbtButton(btn)) return

                    if (window.__abtGateBypass) {
                        window.__abtGateBypass = false
                        return
                    }

                    e.preventDefault()
                    e.stopPropagation()
                    e.stopImmediatePropagation()

                    const modalEl = document.querySelector('[x-data="abtGateModal()"]')
                    if (modalEl && window.Alpine) {
                        window.Alpine.$data(modalEl).show(btn)
                    }
                }, true)
            })()
    </script>

    {{-- Modal Ajukan Rapat (PIC only) --}}
    @if(auth()->user()?->hasRole('PIC'))
        @php
            $times = [];
            for ($h = 7; $h <= 21; $h++) {
                foreach ([0, 15, 30, 45] as $m) {
                    if ($h === 21 && $m > 0)
                        continue;
                    $times[] = sprintf('%02d:%02d', $h, $m);
                }
            }
            $userUsername = auth()->user()->username ?? '';
            $showD = [
                1 => str_contains($userUsername, 'deputi-1'),
                2 => str_contains($userUsername, 'deputi-2'),
                3 => str_contains($userUsername, 'deputi-3'),
                4 => str_contains($userUsername, 'deputi-4'),
            ];
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
            $fieldCls = 'w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 bg-gray-50 focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 outline-none transition';
        @endphp

        <script>
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
                if (!btn) return;
                if (btn.dataset.maintenance === '1') return;
                window.activeRoomId = btn.dataset.roomId || '';
                window.activeRoomName = btn.dataset.roomName || 'Semua Ruang';
                window.activeRoomMaintenance = false;
            });

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
            class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/50" x-on:click="close()"></div>

            <div class="relative bg-white w-full max-w-lg mx-4 rounded-2xl shadow-xl overflow-hidden flex flex-col"
                style="max-height:90vh;">
                <div class="px-6 py-5 border-b flex items-start justify-between bg-gray-50">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Ajukan Rapat</h3>
                        <p class="text-sm text-gray-500 mt-0.5">Isi data rapat yang akan diajukan.</p>
                    </div>
                    <button class="text-gray-400 hover:text-gray-600 transition mt-0.5" type="button" x-on:click="close()">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('bookings.store') }}"
                    class="px-6 py-4 space-y-4 overflow-y-auto flex-1">
                    @csrf

                    @if ($errors->any())
                        <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                            <p class="text-sm font-semibold text-red-800">Terdapat kesalahan:</p>
                            <ul class="mt-1.5 text-sm text-red-700 list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- RUANGAN --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Ruangan <span
                                class="text-red-500">*</span></label>
                        <template x-if="lockRoom">
                            <div>
                                <input type="hidden" name="room_id" :value="roomId">
                                <div
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 bg-gray-50 text-gray-800 text-sm flex items-center gap-2">
                                    <span x-text="roomName"></span>
                                </div>
                            </div>
                        </template>
                        <template x-if="!lockRoom">
                            <div class="relative">
                                <select name="room_id" class="{{ $fieldCls }}"
                                    style="-webkit-appearance:none;-moz-appearance:none;appearance:none;" required>
                                    <option value="" disabled selected>— Pilih ruangan rapat —</option>
                                    @foreach(\App\Models\Room::where('active', true)->orderBy('sort_order')->orderBy('id')->get() as $room)
                                        <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }} {{ $room->maintenance ? 'disabled' : '' }}
                                            style="{{ $room->maintenance ? 'color:#94a3b8;' : '' }}">
                                            {{ $room->name }}{{ $room->maintenance ? ' — 🔧 ' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </template>
                    </div>

                    {{-- JUDUL --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Judul Kegiatan <span
                                class="text-red-500">*</span></label>
                        <input name="title" value="{{ old('title') }}" placeholder="Contoh: Rapat Koordinasi Tim..."
                            class="{{ $fieldCls }}" required />
                    </div>

                    {{-- WAKTU --}}
                    <div class="grid grid-cols-3 gap-3 items-start">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Date <span
                                    class="text-red-500">*</span></label>
                            <input type="date" name="booking_date" x-model="bookingDate" class="{{ $fieldCls }}" required />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Start Time <span
                                    class="text-red-500">*</span></label>
                            <select name="start_time" x-model="startTime" @change="autoSetEndTime()" class="{{ $fieldCls }}"
                                style="-webkit-appearance:none;-moz-appearance:none;appearance:none;" required>
                                <option value="" disabled>Pilih</option>
                                @foreach($times as $t)
                                <option value="{{ $t }}">{{ $t }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">End Time <span
                                    class="text-red-500">*</span></label>
                            <select name="end_time" x-model="endTime" class="{{ $fieldCls }}"
                                style="-webkit-appearance:none;-moz-appearance:none;appearance:none;" required>
                                <option value="" disabled>Pilih</option>
                                @foreach($times as $t)
                                <option value="{{ $t }}">{{ $t }}</option>@endforeach
                            </select>
                        </div>
                    </div>

                    {{-- EMAIL --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email Penerima Notifikasi <span
                                class="text-red-500">*</span></label>
                        <input type="email" name="applicant_email" value="{{ old('applicant_email') }}"
                            placeholder="email@domain.com" class="{{ $fieldCls }}" required />
                        <p class="text-xs text-gray-400 mt-1">Email ini akan menerima notifikasi status booking.</p>
                    </div>

                    {{-- UNIT KERJA --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Unit Kerja <span
                                class="text-red-500">*</span></label>
                        <select name="unit_kerja" class="{{ $fieldCls }}"
                            style="-webkit-appearance:none;-moz-appearance:none;appearance:none;" required>
                            <option value="" disabled selected>— Pilih unit kerja —</option>
                            @foreach($unitGroups as $groupLabel => $units)
                                <optgroup label="{{ $groupLabel }}">
                                    @foreach($units as $u)
                                        <option value="{{ $u }}" {{ old('unit_kerja') == $u ? 'selected' : '' }}>{{ $u }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex justify-end gap-2 pt-1">
                        <button type="button"
                            class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-sm font-semibold text-gray-700 transition"
                            x-on:click="close()">Batal</button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-sm font-semibold text-white transition">Kirim</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function bookingModal() {
                return {
                    open: {{ $errors->any() ? 'true' : 'false' }},
                    bookingDate: @json(old('booking_date', '')),
                    startTime: @json(old('start_time', '')),
                    endTime: @json(old('end_time', '')),
                    roomId: @json(old('room_id', '')),
                    roomName: '',
                    lockRoom: false,
                    init() { this.syncWithSidebar() },
                    syncWithSidebar() {
                        const isMobile = window.innerWidth < 1024
                        let activeId = ''
                        let activeName = 'Semua Ruang'
                        let activeMaintenance = false
                        if (isMobile) {
                            activeId = window.mobileActiveRoomId ? String(window.mobileActiveRoomId) : ''
                            activeName = window.mobileActiveRoomName || 'Semua Ruang'
                            activeMaintenance = !!window.mobileActiveRoomMaintenance
                        } else {
                            activeId = window.activeRoomId || ''
                            activeName = window.activeRoomName || 'Semua Ruang'
                            activeMaintenance = !!window.activeRoomMaintenance
                        }

                        if (activeMaintenance) {
                            this.lockRoom = false
                            this.roomId = ''
                            this.roomName = ''
                            return
                        }

                        this.lockRoom = !!activeId
                        if (this.lockRoom) { this.roomId = activeId; this.roomName = activeName }
                        else { this.roomId = ''; this.roomName = '' }
                    },
                    openModal(payload = {}) {
                        this.open = true
                        this.syncWithSidebar()
                        if (payload.start) {
                            this.bookingDate = payload.start.split('T')[0]
                        } else if (!this.bookingDate) {
                            const today = new Date()
                            this.bookingDate = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`
                        }
                    },
                    autoSetEndTime() {
                        if (!this.startTime) return
                        const [h, m] = this.startTime.split(':').map(Number)
                        const totalMins = h * 60 + m + 60
                        const nh = Math.floor(totalMins / 60)
                        const nm = totalMins % 60
                        if (nh <= 21) { this.endTime = String(nh).padStart(2, '0') + ':' + String(nm).padStart(2, '0') }
                    },
                    close() { this.open = false }
                }
            }
        </script>
    @endif

    {{-- MOBILE CALENDAR JS --}}
    <script>
        const roomNames = {
            @foreach($activeRooms as $room)
                {{ $room->id }}: @json($room->name),
            @endforeach
        }
        const roomMaintenance = {
            @foreach($activeRooms as $room)
                {{ $room->id }}: {{ $room->maintenance ? 'true' : 'false' }},
            @endforeach
        }

        const mobileCal = (() => {
            const roomColors = @json($roomDotColors)
            const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
            const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']

            const STATUS = {
                APPROVED: { label: 'Disetujui', cls: 'bg-emerald-50 text-emerald-800 ring-emerald-200', dot: 'bg-emerald-500' },
                PENDING: { label: 'Menunggu', cls: 'bg-amber-50 text-amber-800 ring-amber-200', dot: 'bg-amber-500' },
            }
            const CHIP_ALL = 'bg-indigo-600 text-white ring-indigo-600'
            const CHIP_ROOM = 'bg-slate-900 text-white ring-slate-900'
            'bg-white text-slate-700 ring-slate-300'            const SVG = 'class="h-3 w-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"'
            const ICON_CLOCK = `<svg ${SVG}><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>`
            const ICON_PIN = `<svg ${SVG}><path d="M12 21s-7-6.2-7-11a7 7 0 1 1 14 0c0 4.8-7 11-7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>`
            const ICON_BUILDING = `<svg ${SVG}><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4M10 10h4M10 14h4M10 18h4"/></svg>`

            let allBookings = []
            let visibleItems = []
            let currentYear = new Date().getFullYear()
            let currentMonth = new Date().getMonth()
            let selectedDate = toDateStr(new Date())
            let selectedRoomId = ''

            const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]))

            function toDateStr(d) {
                return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
            }

            function fmtTime(str) {
                return str ? str.slice(11, 16).replace(':', '.') : ''
            }

            async function fetchBookings() {
                try {
                    const res = await fetch(`/api/bookings?start=${currentYear}-01-01&end=${currentYear}-12-31`)
                    const data = await res.json()
                    allBookings = data.map(e => ({
                        title: e.title,
                        start: (e.start || '').replace('T', ' '),
                        end: (e.end || '').replace('T', ' '),
                        room_id: e.extendedProps?.room_id ?? e.room_id,
                        room_name: e.extendedProps?.room_name ?? e.room_name ?? '',
                        unit_kerja: e.extendedProps?.unit_kerja ?? e.unit_kerja ?? '-',
                        status: e.extendedProps?.status ?? e.status ?? 'APPROVED',
                        description: e.extendedProps?.description ?? e.description ?? '',
                    }))
                } catch (e) {
                    console.error('Fetch error:', e)
                    allBookings = []
                }
            }

            function matches(b, dateStr) {
                return b.start.startsWith(dateStr) &&
                    ['APPROVED', 'PENDING'].includes(b.status) &&
                    (!selectedRoomId || String(b.room_id) === selectedRoomId)
            }

            function filterRoom(roomId) {
                if (roomId && roomMaintenance[roomId]) return

                selectedRoomId = String(roomId)
                window.mobileActiveRoomId = roomId
                window.mobileActiveRoomName = roomId ? (roomNames[roomId] || '') : 'Semua Ruang'
                window.mobileActiveRoomMaintenance = roomId ? !!roomMaintenance[roomId] : false

                document.querySelectorAll('[id^="mc-pill-"]').forEach(btn => {
                    if (btn.disabled) return
                    const on = btn.id === `mc-pill-${roomId}`
                    const onCls = btn.id === 'mc-pill-' ? CHIP_ALL : CHIP_ROOM
                    btn.classList.remove(...CHIP_ALL.split(' '), ...CHIP_ROOM.split(' '), ...CHIP_OFF.split(' '))
                    btn.classList.add(...(on ? onCls : CHIP_OFF).split(' '))
                })

                renderGrid()
                renderAgenda()
            }

            function getDotsForDate(dateStr) {
                const colors = allBookings.filter(b => matches(b, dateStr)).map(b => roomColors[b.room_id] || '#9ca3af')
                return [...new Set(colors)].slice(0, 4)
            }

            function renderGrid() {
                const grid = document.getElementById('mc-grid')
                const label = document.getElementById('mc-month-label')
                if (!grid || !label) return

                label.textContent = `${monthNames[currentMonth]} ${currentYear}`

                const now = new Date()
                const todayStr = toDateStr(now)
                const offset = (new Date(currentYear, currentMonth, 1).getDay() + 6) % 7
                const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate()

                const todayBtn = document.getElementById('mc-today-btn')
                if (todayBtn) {
                    const atToday = selectedDate === todayStr && currentYear === now.getFullYear() && currentMonth === now.getMonth()
                    todayBtn.classList.toggle('hidden', atToday)
                }

                let html = ''
                for (let i = 0; i < offset; i++) html += '<div></div>'

                for (let d = 1; d <= daysInMonth; d++) {
                    const dateStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`
                    const col = (offset + d - 1) % 7
                    let state
                    if (dateStr === selectedDate) state = 'bg-indigo-600 text-white'
                    else if (dateStr === todayStr) state = 'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200'
                    else state = (col > 4 ? 'text-rose-500' : 'text-slate-700') + ' hover:bg-slate-50'

                    const dots = getDotsForDate(dateStr)
                        .map(c => `<span class="h-1 w-1 rounded-full" style="background:${c}"></span>`).join('')

                    html += `<button type="button" onclick="mobileCal.selectDate('${dateStr}')"
                        class="mx-auto flex h-10 w-10 flex-col items-center justify-center rounded-full text-xs font-semibold transition ${state}">
                        <span>${d}</span>
                        <span class="mt-0.5 flex h-1 items-center gap-0.5">${dots}</span>
                    </button>`
                }
                grid.innerHTML = html
            }

            function renderAgenda() {
                const agendaEl = document.getElementById('mc-agenda')
                const labelEl = document.getElementById('mc-date-label')
                if (!agendaEl || !labelEl) return

                const d = new Date(selectedDate + 'T00:00:00')
                labelEl.textContent = `${dayNames[d.getDay()]}, ${d.getDate()} ${monthNames[d.getMonth()]} ${d.getFullYear()}`

                visibleItems = allBookings.filter(b => matches(b, selectedDate)).sort((a, b) => a.start.localeCompare(b.start))

                if (visibleItems.length === 0) {
                    agendaEl.innerHTML = `<div class="py-10 text-center text-slate-400">
                        <div class="text-sm font-semibold">Tidak ada jadwal</div>
                        <div class="mt-1 text-xs">Belum ada kegiatan pada tanggal ini.</div>
                    </div>`
                    return
                }

                agendaEl.innerHTML = visibleItems.map((b, i) => {
                    const color = roomColors[b.room_id] || '#9ca3af'
                    const st = STATUS[b.status] || { label: b.status, cls: 'bg-slate-100 text-slate-700 ring-slate-200', dot: 'bg-slate-400' }
                    const unit = (b.unit_kerja && b.unit_kerja !== '-')
                        ? `<span class="mt-1.5 inline-flex max-w-full items-center gap-1.5 rounded-md bg-indigo-50 px-2 py-0.5 text-[11px] font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-100">${ICON_BUILDING}<span class="truncate">${esc(b.unit_kerja)}</span></span>`
                        : ''

                    return `<button type="button" onclick="mobileCal.openAt(${i})"
                        class="flex w-full items-start gap-3 rounded-2xl bg-white p-3.5 text-left shadow-sm transition active:scale-[.99]">
                        <span class="mt-0.5 h-12 w-1 shrink-0 rounded-full" style="background:${color}"></span>
                        <span class="min-w-0 flex-1">
                            <span class="flex items-start justify-between gap-2">
                                <span class="text-sm font-semibold leading-snug text-slate-900">${esc(b.title)}</span>
                                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-md px-2 py-0.5 text-[11px] font-semibold ring-1 ring-inset ${st.cls}">
                                    <span class="h-1.5 w-1.5 rounded-full ${st.dot}"></span>${st.label}
                                </span>
                            </span>
                            <span class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-0.5 text-xs text-slate-500">
                                <span class="inline-flex items-center gap-1"><span class="text-slate-400">${ICON_CLOCK}</span><span class="tabular-nums">${fmtTime(b.start)} – ${fmtTime(b.end)}</span></span>
                                <span class="inline-flex min-w-0 items-center gap-1"><span class="text-slate-400">${ICON_PIN}</span><span class="truncate">${esc(b.room_name)}</span></span>
                            </span>
                            ${unit}
                        </span>
                    </button>`
                }).join('')
            }

            function openDetail(b) {
                if (typeof b === 'string') b = JSON.parse(b)
                const modalEl = document.querySelector('[x-data="meetingDetailModal()"]')
                if (!modalEl || !window.Alpine) return
                window.Alpine.$data(modalEl).show({
                    title: b.title,
                    room: b.room_name ? `Ruang: ${b.room_name}` : '',
                    status: b.status,
                    pic: b.unit_kerja,
                    start: b.start ? b.start.slice(0, 16) : '-',
                    end: b.end ? b.end.slice(0, 16) : '-',
                    description: b.description || ''
                })
            }

            function openAt(i) { if (visibleItems[i]) openDetail(visibleItems[i]) }

            function selectDate(dateStr) {
                selectedDate = dateStr
                renderGrid()
                renderAgenda()
            }

            function goToday() {
                const now = new Date()
                currentYear = now.getFullYear()
                currentMonth = now.getMonth()
                selectedDate = toDateStr(now)
                renderGrid()
                renderAgenda()
            }

            function getSelectedDate() { return selectedDate }

            async function prevMonth() {
                if (currentMonth === 0) { currentMonth = 11; currentYear--; await fetchBookings() }
                else { currentMonth-- }
                renderGrid()
                renderAgenda()
            }

            async function nextMonth() {
                if (currentMonth === 11) { currentMonth = 0; currentYear++; await fetchBookings() }
                else { currentMonth++ }
                renderGrid()
                renderAgenda()
            }

            async function init() {
                if (!document.getElementById('mobile-calendar-app')) return
                await fetchBookings()
                renderGrid()
                renderAgenda()
            }

            document.addEventListener('DOMContentLoaded', init)

            window.mobileCal = { prevMonth, nextMonth, selectDate, goToday, openDetail, openAt, filterRoom, getSelectedDate }
            return window.mobileCal
        })()
    </script>

    {{-- EXPOSE USER ROLE --}}
    <script>
        window.userRole = @json(auth()->check()
            ? (auth()->user()->hasRole('PIC') ? 'PIC' : (auth()->user()->hasRole('TU') ? 'TU' : 'USER'))
        : 'GUEST');
        document.documentElement.setAttribute('data-user-role', window.userRole)
    </script>

    @push('scripts')
        @vite(['resources/js/calendar.js'])
    @endpush

    <style>
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
            transition: background .15s, color .15s, border-color .15s;
            cursor: pointer;
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

        .fc {
            --fc-border-color: #e3e9fb;
            --fc-today-bg-color: #e8edff;
            --fc-page-bg-color: transparent;
        }

        /* ---- Toolbar ---- */
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

    {{-- Modal Konfirmasi Ruang Rapat ABT --}}
    <div x-data="abtGateModal()" x-show="open" x-cloak x-on:keydown.escape.window="cancel()" class="abt-gate-overlay">
        <div class="abt-gate-backdrop" @click="cancel()"></div>

        <div class="abt-gate-card" x-show="open" x-transition:enter="abt-gate-enter"
            x-transition:enter-start="abt-gate-enter-start" x-transition:enter-end="abt-gate-enter-end"
            x-transition:leave="abt-gate-leave" x-transition:leave-start="abt-gate-leave-start"
            x-transition:leave-end="abt-gate-leave-end" role="alertdialog" aria-modal="true"
            aria-labelledby="abt-gate-title" aria-describedby="abt-gate-desc">

            <div class="abt-gate-header">
                <div class="abt-gate-header-text">
                    <h3 class="abt-gate-title" id="abt-gate-title">Ruang Rapat ABT</h3>
                    <p class="abt-gate-eyebrow">Konfirmasi Agenda</p>
                </div>
                <button type="button" class="abt-gate-close" @click="cancel()" aria-label="Tutup">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </button>
            </div>

            <div class="abt-gate-body" id="abt-gate-desc">
                Ruang ini khusus digunakan untuk agenda <strong>ABT</strong>. Apakah pengajuan rapat Anda
                terkait dengan agenda ABT?
            </div>

            <div class="abt-gate-footer">
                <button type="button" class="abt-gate-btn abt-gate-btn-secondary" @click="cancel()">Tidak</button>
                <button type="button" class="abt-gate-btn abt-gate-btn-primary" @click="confirm()">Ya,
                    Lanjutkan</button>
            </div>
        </div>
    </div>

    <style>
        .abt-gate-overlay {
            position: fixed;
            inset: 0;
            z-index: 60;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            box-sizing: border-box;
        }

        .abt-gate-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, .55);
            backdrop-filter: blur(1.5px);
        }

        .abt-gate-card {
            position: relative;
            width: 100%;
            max-width: 440px;
            max-height: calc(100vh - 32px);
            overflow-y: auto;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 45px -12px rgba(15, 23, 42, .35), 0 0 0 1px rgba(15, 23, 42, .04);
            font-family: 'Plus Jakarta Sans', sans-serif;
            box-sizing: border-box;
        }

        .abt-gate-header {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 20px 20px 16px;
        }

        .abt-gate-header-text {
            flex: 1;
            min-width: 0;
            padding-top: 1px;
        }

        .abt-gate-eyebrow {
            margin: 0 0 2px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #6366f1;
        }

        .abt-gate-title {
            margin: 0;
            font-size: 17px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.3;
        }

        .abt-gate-close {
            flex-shrink: 0;
            width: 30px;
            height: 30px;
            border-radius: 10px;
            border: none;
            background: transparent;
            color: #94a3b8;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background .15s, color .15s;
        }

        .abt-gate-close:hover {
            background: #f1f5f9;
            color: #475569;
        }

        .abt-gate-close:focus-visible,
        .abt-gate-btn:focus-visible {
            outline: 2px solid #6366f1;
            outline-offset: 2px;
        }

        .abt-gate-body {
            padding: 0 20px 20px;
            font-size: 14px;
            line-height: 1.6;
            color: #475569;
        }

        .abt-gate-body strong {
            color: #0f172a;
            font-weight: 700;
        }

        .abt-gate-footer {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            padding: 16px 20px;
            border-top: 1px solid #e2e8f0;
            background: #f8fafc;
            border-radius: 0 0 20px 20px;
        }

        .abt-gate-btn {
            appearance: none;
            border: none;
            cursor: pointer;
            font-family: inherit;
            font-size: 13.5px;
            font-weight: 700;
            padding: 10px 18px;
            border-radius: 12px;
            transition: background .15s, transform .05s;
            white-space: nowrap;
        }

        .abt-gate-btn:active {
            transform: scale(.97);
        }

        .abt-gate-btn-secondary {
            background: #e2e8f0;
            color: #334155;
        }

        .abt-gate-btn-secondary:hover {
            background: #cbd5e1;
        }

        .abt-gate-btn-primary {
            background: #4f46e5;
            color: #fff;
            box-shadow: 0 4px 12px -2px rgba(79, 70, 229, .4);
        }

        .abt-gate-btn-primary:hover {
            background: #4338ca;
        }

        .abt-gate-enter {
            transition: opacity .18s ease-out, transform .18s ease-out;
        }

        .abt-gate-enter-start {
            opacity: 0;
            transform: translateY(8px) scale(.97);
        }

        .abt-gate-enter-end {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        .abt-gate-leave {
            transition: opacity .12s ease-in, transform .12s ease-in;
        }

        .abt-gate-leave-start {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        .abt-gate-leave-end {
            opacity: 0;
            transform: translateY(8px) scale(.97);
        }

        @media (max-width: 420px) {
            .abt-gate-card {
                max-width: 100%;
                border-radius: 18px;
            }

            .abt-gate-header {
                padding: 18px 16px 14px;
            }

            .abt-gate-body {
                padding: 0 16px 18px;
                font-size: 13.5px;
            }

            .abt-gate-footer {
                flex-direction: column-reverse;
                padding: 14px 16px;
                border-radius: 0 0 18px 18px;
            }

            .abt-gate-btn {
                width: 100%;
                text-align: center;
                padding: 12px 18px;
            }
        }

        @media (prefers-reduced-motion: reduce) {

            .abt-gate-enter,
            .abt-gate-leave {
                transition: opacity .01s linear !important;
            }

            .abt-gate-enter-start,
            .abt-gate-enter-end,
            .abt-gate-leave-start,
            .abt-gate-leave-end {
                transform: none !important;
            }
        }
    </style>

    <script>
        function abtGateModal() {
            return {
                open: false,
                _pendingBtn: null,
                show(btn) { this._pendingBtn = btn; this.open = true },
                cancel() { this.open = false; this._pendingBtn = null },
                confirm() {
                    this.open = false
                    const btn = this._pendingBtn
                    this._pendingBtn = null
                    if (btn) {
                        window.__abtGateBypass = true
                        btn.click()
                    }
                }
            }
        }
    </script>

    {{-- Modal Detail Meeting --}}
    <div x-data="meetingDetailModal()" x-show="open" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/50" @click="close()"></div>
        <div class="relative w-full max-w-lg mx-4 bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="p-5 border-b flex items-start justify-between">
                <div class="min-w-0">
                    <h3 class="text-lg font-semibold text-gray-900" x-text="data.title"></h3>
                    <p class="text-sm text-gray-500 mt-1" x-text="data.room || ''"></p>
                </div>
                <button class="text-gray-400 hover:text-gray-600" @click="close()">✕</button>
            </div>
            <div class="p-5 space-y-4">
                <div class="flex flex-wrap gap-2">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold" :class="badgeClass(data.status)"
                        x-text="data.status || '-'"></span>
                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700"
                        x-text="data.pic ? ('PIC: ' + data.pic) : 'PIC: -'"></span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="p-3 rounded-xl bg-gray-50">
                        <div class="text-xs text-gray-500">Mulai</div>
                        <div class="font-semibold text-gray-900" x-text="data.start || '-'"></div>
                    </div>
                    <div class="p-3 rounded-xl bg-gray-50">
                        <div class="text-xs text-gray-500">Selesai</div>
                        <div class="font-semibold text-gray-900" x-text="data.end || '-'"></div>
                    </div>
                </div>
                <div class="p-3 rounded-xl bg-gray-50">
                    <div class="text-xs text-gray-500">Deskripsi</div>
                    <div class="mt-1 text-sm text-gray-800 whitespace-pre-wrap" x-text="data.description || '-'"></div>
                </div>
            </div>
            <div class="p-5 border-t flex justify-end gap-2">
                <button class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200" @click="close()">Tutup</button>
            </div>
        </div>
    </div>

    <script>
        function meetingDetailModal() {
            return {
                open: false,
                data: { title: '', room: '', status: '', pic: '', start: '', end: '', description: '' },
                show(payload) { this.data = payload; this.open = true },
                close() { this.open = false },
                badgeClass(status) {
                    const s = (status || '').toUpperCase()
                    if (s === 'APPROVED') return 'bg-green-100 text-green-700'
                    if (s === 'PENDING') return 'bg-yellow-100 text-yellow-700'
                    if (s === 'REJECTED') return 'bg-red-100 text-red-700'
                    return 'bg-gray-100 text-gray-700'
                }
            }
        }
    </script>

</x-app-layout>