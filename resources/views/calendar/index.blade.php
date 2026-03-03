<x-app-layout>
    @if($errors->any())
        <pre>ERRORS: {{ json_encode($errors->all()) }}</pre>
    @endif
    @if(session()->has('_old_input'))
        <pre>OLD INPUT ADA</pre>
    @endif
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Kalender Booking Ruang Rapat
            </h2>

            @if(auth()->user()?->hasRole('PIC'))
                <div class="flex gap-2">
                    <button type="button"
                        class="px-3 py-1.5 text-sm sm:px-4 sm:py-2 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700"
                        onclick="window.dispatchEvent(new CustomEvent('open-booking-modal'))">
                        + Ajukan Rapat
                    </button>
                </div>
            @endif
        </div>
    </x-slot>

    {{-- NOTIFIKASI SUCCESS --}}
    @if(session('status'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
            class="fixed top-4 right-4 z-40">
            <div class="bg-green-50 border border-green-200 rounded-2xl p-4 shadow-lg flex items-start justify-between">
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
                <button @click="show = false" class="text-green-400 hover:text-green-600">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
    @endif

    @php
        $activeRoomId = request('room_id');
    @endphp

    <div class="py-6">
        <div class="mx-auto sm:px-6 lg:px-8" style="max-width:90%">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

                {{-- SIDEBAR FILTER (PIC) --}}
                @if(auth()->user()?->hasRole('PIC'))
                    <aside class="lg:col-span-3">
                        <div class="bg-white shadow-sm rounded-2xl p-4" x-data="{ openFilter: false }">
                            <button type="button" class="w-full flex items-center justify-between"
                                @click="openFilter = !openFilter">
                                <div>
                                    <div class="font-semibold text-gray-900">Filter Ruang</div>
                                    <div class="text-sm text-gray-500 mt-0.5 hidden sm:block">Klik untuk melihat jadwal per
                                        ruang.</div>
                                </div>
                                <span class="lg:hidden text-gray-400 text-lg" x-text="openFilter ? '▲' : '▼'"></span>
                            </button>

                            <div class="mt-4 space-y-1 lg:block" :class="openFilter ? 'block' : 'hidden lg:block'"
                                id="room-sidebar" data-active-room="{{ $activeRoomId }}">

                                <button type="button"
                                    class="room-filter w-full text-left px-3 py-2 rounded-xl hover:bg-gray-50 flex items-center gap-2"
                                    data-room-id="" data-room-name="Semua Ruang">
                                    Semua Ruang
                                </button>

                                @php
                                    $roomDotColors = [1 => 'bg-slate-500', 2 => 'bg-teal-500', 3 => 'bg-violet-500', 4 => 'bg-amber-500', 5 => 'bg-fuchsia-500', 6 => 'bg-rose-500'];
                                @endphp

                                @foreach(\App\Models\Room::orderBy('id')->get() as $room)
                                    <button type="button"
                                        class="room-filter w-full text-left px-3 py-2 rounded-xl hover:bg-gray-50 flex items-center gap-2"
                                        data-room-id="{{ $room->id }}" data-room-name="{{ $room->name }}">
                                        <span
                                            class="h-2 w-2 rounded-full flex-shrink-0 {{ $roomDotColors[$room->id] ?? 'bg-gray-400' }}"></span>
                                        {{ $room->name }}
                                    </button>
                                @endforeach

                            </div>
                        </div>
                    </aside>
                @endif

                {{-- MAIN --}}
                <main class="@if(auth()->user()?->hasRole('PIC')) lg:col-span-9 @else lg:col-span-12 @endif">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl p-4 sm:p-6">
                        <div class="mb-3 text-sm text-gray-600">
                            Tampilan:
                            <span id="active-room-label" class="font-semibold text-gray-900">Semua Ruang</span>
                        </div>
                        <div id="calendar"></div>
                    </div>
                </main>

            </div>
        </div>
    </div>

    {{-- Modal Ajukan Rapat --}}
    @if(auth()->user()?->hasRole('PIC'))

        <script>
            window.activeRoomId = document.getElementById('room-sidebar')?.dataset.activeRoom || '';
            window.activeRoomName = (() => {
                const btn = document.querySelector(`.room-filter[data-room-id="${window.activeRoomId}"]`);
                return btn?.dataset.roomName || 'Semua Ruang';
            })();

            document.addEventListener('click', (e) => {
                const btn = e.target.closest('.room-filter');
                if (!btn) return;
                window.activeRoomId = btn.dataset.roomId || '';
                window.activeRoomName = btn.dataset.roomName || 'Semua Ruang';
            });
        </script>

        <div x-data="bookingModal()" x-init="init()" x-show="open" x-cloak
            x-on:open-booking-modal.window="openModal($event.detail || {})" x-on:keydown.escape.window="close()"
            class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/50" x-on:click="close()"></div>

            <div class="relative bg-white w-full max-w-lg mx-4 rounded-2xl shadow-xl overflow-hidden">
                {{-- Modal Header --}}
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

                <form method="POST" action="{{ route('bookings.store') }}" class="px-6 py-5 space-y-5">
                    @csrf

                    @if ($errors->any())
                        <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                            <div class="flex gap-3">
                                <svg class="h-5 w-5 text-red-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd" />
                                </svg>
                                <div>
                                    <p class="text-sm font-semibold text-red-800">Terdapat kesalahan:</p>
                                    <ul class="mt-1.5 text-sm text-red-700 list-disc list-inside space-y-0.5">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- RUANGAN --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Ruangan <span class="text-red-500">*</span>
                        </label>

                        <template x-if="lockRoom">
                            <div>
                                <input type="hidden" name="room_id" :value="roomId">
                                <div
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 bg-gray-50 text-gray-800 text-sm flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <span x-text="roomName"></span>
                                </div>
                            </div>
                        </template>

                        <template x-if="!lockRoom">
                            <div class="relative">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <select name="room_id"
                                    class="w-full border border-gray-200 rounded-xl pl-9 pr-4 py-2.5 text-sm text-gray-800 bg-gray-50 focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 outline-none transition appearance-none"
                                    required>
                                    <option value="" disabled selected>— Pilih ruangan rapat —</option>
                                    @foreach(\App\Models\Room::where('active', true)->orderBy('name')->get() as $room)
                                        <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}>
                                            {{ $room->name }}
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
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Judul Kegiatan <span class="text-red-500">*</span>
                        </label>
                        <input name="title" value="{{ old('title') }}" placeholder="Contoh: Rapat Koordinasi Tim..."
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 bg-gray-50 focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 outline-none transition"
                            required />
                    </div>

                    {{-- WAKTU --}}
                    <div class="grid grid-cols-3 gap-3">
                        {{-- DATE --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="booking_date" x-model="bookingDate"
                                class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-800 bg-gray-50 focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 outline-none transition"
                                required />
                        </div>

                        {{-- START TIME --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                                </svg>
                                Start Time <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="start_time" x-model="startTime" @change="autoSetEndTime()"
                                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-800 bg-gray-50 focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 outline-none transition appearance-none"
                                    required>
                                    <option value="" disabled>Pilih</option>
                                    @for($h = 7; $h <= 21; $h++)
                                        <option value="{{ sprintf('%02d', $h) }}:00">{{ sprintf('%02d', $h) }}:00</option>
                                        @if($h < 21)
                                            <option value="{{ sprintf('%02d', $h) }}:30">{{ sprintf('%02d', $h) }}:30</option>
                                        @endif
                                    @endfor
                                </select>
                                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>

                        {{-- END TIME --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                                </svg>
                                End Time <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="end_time" x-model="endTime"
                                    class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-800 bg-gray-50 focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 outline-none transition appearance-none"
                                    required>
                                    <option value="" disabled>Pilih</option>
                                    @for($h = 7; $h <= 21; $h++)
                                        <option value="{{ sprintf('%02d', $h) }}:00">{{ sprintf('%02d', $h) }}:00</option>
                                        @if($h < 21)
                                            <option value="{{ sprintf('%02d', $h) }}:30">{{ sprintf('%02d', $h) }}:30</option>
                                        @endif
                                    @endfor
                                </select>
                                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Deskripsi</label>
                        <textarea name="description" rows="3" placeholder="Tambahkan keterangan rapat (opsional)..."
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 bg-gray-50 focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 outline-none transition resize-none">{{ old('description') }}</textarea>
                    </div>

                    {{-- ACTIONS --}}
                    <div class="flex justify-end gap-2 pt-1">
                        <button type="button"
                            class="px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-sm font-semibold text-gray-700 transition"
                            x-on:click="close()">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-sm font-semibold text-white transition">
                            Kirim
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function bookingModal() {
                return {
                    open: {{ $errors->any() ? 'true' : 'false' }},
                    start: @json(old('start_at', '')),
                    end: @json(old('end_at', '')),
                    roomId: @json(old('room_id', '')),
                    roomName: '',
                    lockRoom: false,

                    init() {
                        this.syncWithSidebar()
                    },

                    syncWithSidebar() {
                        const activeId = window.activeRoomId || ''
                        const activeName = window.activeRoomName || 'Semua Ruang'
                        this.lockRoom = !!activeId
                        if (this.lockRoom) {
                            this.roomId = activeId
                            this.roomName = activeName
                        } else {
                            this.roomName = ''
                        }
                    },

                    openModal(payload = {}) {
                        this.open = true
                        this.start = payload.start ?? this.start ?? ''
                        this.end = payload.end ?? this.end ?? ''
                        this.syncWithSidebar()
                    },

                    close() {
                        this.open = false
                    }
                }
            }
        </script>
    @endif

    {{-- EXPOSE USER ROLE --}}
    <script>
        window.userRole = @json(auth()->check()
            ? (auth()->user()->hasRole('PIC') ? 'PIC' : (auth()->user()->hasRole('TU') ? 'TU' : 'USER'))
        : 'GUEST');
        console.log('User Role:', window.userRole)
        document.documentElement.setAttribute('data-user-role', window.userRole)
    </script>

    @vite(['resources/js/calendar.js'])

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');

        #calendar {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* datetime-local styling */
        input[type="datetime-local"]::-webkit-calendar-picker-indicator {
            opacity: 0.5;
            cursor: pointer;
            padding: 2px;
        }

        input[type="datetime-local"]::-webkit-datetime-edit {
            padding: 0;
            color: #1e293b;
        }

        input[type="datetime-local"]::-webkit-datetime-edit-fields-wrapper {
            padding: 0;
        }

        /* ============================================
           TOOLBAR / HEADER
        ============================================ */
        .fc .fc-toolbar {
            padding: 4px 0 16px;
            flex-wrap: wrap;
            gap: 8px;
        }

        .fc .fc-toolbar-title {
            font-size: 1.25rem !important;
            font-weight: 700 !important;
            color: #1e293b !important;
            letter-spacing: -0.02em;
        }

        .fc .fc-button {
            background: #f1f5f9 !important;
            border: none !important;
            color: #475569 !important;
            font-weight: 600 !important;
            font-size: 0.8rem !important;
            border-radius: 10px !important;
            padding: 6px 14px !important;
            box-shadow: none !important;
            transition: background 0.15s, color 0.15s !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }

        .fc .fc-button:hover {
            background: #e2e8f0 !important;
            color: #1e293b !important;
        }

        .fc .fc-button-primary:not(:disabled).fc-button-active,
        .fc .fc-button-primary:not(:disabled):active {
            background: #6366f1 !important;
            color: #fff !important;
        }

        .fc .fc-today-button {
            background: #6366f1 !important;
            color: #fff !important;
        }

        .fc .fc-today-button:hover {
            background: #4f46e5 !important;
        }

        .fc .fc-col-header-cell {
            background: #f8fafc !important;
            border-bottom: 2px solid #e2e8f0 !important;
            padding: 10px 0 !important;
        }

        .fc .fc-col-header-cell-cushion {
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.06em !important;
            color: #64748b !important;
            text-decoration: none !important;
        }

        .fc .fc-day-today .fc-col-header-cell-cushion {
            color: #6366f1 !important;
        }

        .fc .fc-timegrid-slot {
            height: 48px !important;
            border-color: #cbd5e1 !important;
        }

        .fc .fc-timegrid-slot-label {
            font-size: 0.7rem !important;
            font-weight: 600 !important;
            color: #94a3b8 !important;
            letter-spacing: 0.02em;
            vertical-align: top;
            padding-top: 4px;
        }

        .fc .fc-day-today {
            background: #f5f3ff !important;
        }

        .fc .fc-timegrid-col.fc-day-today {
            background: #f5f3ff !important;
        }

        .fc-timegrid-event-harness .fc-event {
            border-radius: 10px !important;
            border: none !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08) !important;
            overflow: hidden !important;
        }

        .fc-timegrid-event .fc-event-main {
            padding: 6px 8px !important;
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
        }

        .fc-event-main {
            overflow: visible !important;
        }

        .fc .fc-scrollgrid {
            border-radius: 16px !important;
            overflow: hidden !important;
            border-color: #94a3b8 !important;
        }

        .fc td,
        .fc th {
            border-color: #cbd5e1 !important;
        }

        .fc-scroller::-webkit-scrollbar {
            width: 4px;
        }

        .fc-scroller::-webkit-scrollbar-track {
            background: transparent;
        }

        .fc-scroller::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .fc .fc-timegrid-now-indicator-line {
            border-color: #6366f1 !important;
            border-width: 2px !important;
        }

        .fc .fc-timegrid-now-indicator-arrow {
            border-top-color: #6366f1 !important;
            border-bottom-color: #6366f1 !important;
        }
    </style>

    {{-- Modal Detail Meeting --}}
    <div x-data="meetingDetailModal()" x-show="open" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/50" @click="close()"></div>

        <div class="relative w-full max-w-lg mx-4 bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="p-5 border-b flex items-start justify-between">
                <div class="min-w-0">
                    <h3 class="text-lg font-semibold text-gray-900 truncate" x-text="data.title"></h3>
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