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
                    <button type="button" class="px-4 py-2 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700"
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
        $activeRoomId = request('room_id'); // optional kalau kamu mau share link
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

                {{-- SIDEBAR FILTER (PIC) --}}
                @if(auth()->user()?->hasRole('PIC'))
                    <aside class="lg:col-span-3">
                        <div class="bg-white shadow-sm rounded-2xl p-4">
                            <div class="font-semibold text-gray-900">Filter Ruang</div>
                            <div class="text-sm text-gray-500 mt-1">Klik untuk melihat jadwal per ruang.</div>

                            <div class="mt-4 space-y-1" id="room-sidebar" data-active-room="{{ $activeRoomId }}">

                                {{-- Semua Ruang --}}
                                <button type="button"
                                    class="room-filter w-full text-left px-3 py-2 rounded-xl hover:bg-gray-50"
                                    data-room-id="" data-room-name="Semua Ruang">
                                    Semua Ruang
                                </button>

                                {{-- Rooms dari DB (pasti benar id-nya, gak ketuker D2/D3 lagi) --}}
                                @foreach(\App\Models\Room::orderBy('id')->get() as $room)
                                    <button type="button"
                                        class="room-filter w-full text-left px-3 py-2 rounded-xl hover:bg-gray-50"
                                        data-room-id="{{ $room->id }}" data-room-name="{{ $room->name }}">
                                        {{ $room->name }}
                                    </button>
                                @endforeach

                            </div>

                            {{-- LEGEND WARNA --}}
                            <div class="mt-4 pt-4 border-t">
                                <div class="text-xs font-semibold text-gray-700 mb-2">Legenda Warna</div>

                                <div class="space-y-1 text-sm text-gray-700">

                                    <div class="flex items-center gap-2">
                                        <span class="h-2 w-2 rounded-full bg-slate-500"></span>
                                        Ruang Rapat Utama
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span class="h-2 w-2 rounded-full bg-teal-500"></span>
                                        Ruang Rapat KDKMP
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span class="h-2 w-2 rounded-full bg-violet-500"></span>
                                        Ruang Rapat Setmenko
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                        Ruang Rapat D2
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span class="h-2 w-2 rounded-full bg-fuchsia-500"></span>
                                        Ruang Rapat D3
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                                        Ruang Rapat D4
                                    </div>

                                </div>
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
            // ===============================
            // GLOBAL ROOM FILTER STATE (MUST BE BEFORE bookingModal())
            // ===============================
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
                <div class="p-5 border-b flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Ajukan Rapat</h3>
                        <p class="text-sm text-gray-500 mt-1">Isi data rapat yang akan diajukan.</p>
                    </div>
                    <button class="text-gray-400 hover:text-gray-600" type="button" x-on:click="close()">✕</button>
                </div>

                <form method="POST" action="{{ route('bookings.store') }}" class="p-5 space-y-4">
                    @csrf

                    @if ($errors->any())
                        <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">Terdapat kesalahan:</h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <ul class="list-disc list-inside space-y-1">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- RUANG (LOCK kalau filter ruang tertentu, dropdown kalau Semua Ruang) --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Ruang</label>

                        <template x-if="lockRoom">
                            <div class="mt-1">
                                <input type="hidden" name="room_id" :value="roomId">
                                <div class="w-full border rounded-xl px-3 py-2 bg-gray-50 text-gray-800" x-text="roomName">
                                </div>
                            </div>
                        </template>

                        <template x-if="!lockRoom">
                            <select name="room_id" class="mt-1 w-full border rounded-xl px-3 py-2" required>
                                @foreach(\App\Models\Room::where('active', true)->orderBy('name')->get() as $room)
                                    <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}>
                                        {{ $room->name }}
                                    </option>
                                @endforeach
                            </select>
                        </template>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Judul</label>
                        <input name="title" value="{{ old('title') }}" class="mt-1 w-full border rounded-xl px-3 py-2"
                            required />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Mulai</label>
                            <input type="datetime-local" name="start_at" x-model="start"
                                class="mt-1 w-full border rounded-xl px-3 py-2" required />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Selesai</label>
                            <input type="datetime-local" name="end_at" x-model="end"
                                class="mt-1 w-full border rounded-xl px-3 py-2" required />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                        <textarea name="description" class="mt-1 w-full border rounded-xl px-3 py-2"
                            rows="3">{{ old('description') }}</textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200"
                            x-on:click="close()">
                            Batal
                        </button>
                        <button class="px-4 py-2 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700">
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

    {{-- EXPOSE USER ROLE FOR JAVASCRIPT --}}
    <script>
        // Ambil role dari Spatie (satu sumber kebenaran)
        window.userRole = @json(auth()->check()
            ? (auth()->user()->hasRole('PIC') ? 'PIC' : (auth()->user()->hasRole('TU') ? 'TU' : 'USER'))
        : 'GUEST');

        console.log('User Role:', window.userRole)
        document.documentElement.setAttribute('data-user-role', window.userRole)
    </script>

    @vite(['resources/js/calendar.js'])

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
                <button class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200" @click="close()">
                    Tutup
                </button>
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