<x-app-layout>
    <x-slot name="header">
        @php
            $hour = $now->hour;
            if ($hour >= 5 && $hour < 12)
                $greeting = 'Selamat pagi';
            elseif ($hour >= 12 && $hour < 15)
                $greeting = 'Selamat siang';
            elseif ($hour >= 15 && $hour < 18)
                $greeting = 'Selamat sore';
            else
                $greeting = 'Selamat malam';
        @endphp
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-gray-800 leading-tight">Dashboard</h2>
                <p class="text-sm text-gray-400 mt-0.5">{{ $greeting }}, {{ Auth::user()->name }} 👋</p>
            </div>
            <a href="{{ route('calendar') }}"
                class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition">
                + Ajukan Rapat
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-5">

            {{-- SECTION 1: HERO --}}
            <div
                class="rounded-2xl overflow-hidden shadow-sm {{ $activeBooking ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-100' }}">
                <div class="p-6">

                    @if($activeBooking)
                        <div class="flex items-start justify-between gap-4 flex-wrap">
                            <div>
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-white animate-pulse inline-block"></span>
                                    <span class="text-xs font-bold uppercase tracking-widest opacity-75">Sedang
                                        Berlangsung</span>
                                </div>
                                <div class="text-2xl font-bold">{{ $activeBooking->title }}</div>
                                <div class="text-sm opacity-80 mt-1">
                                    {{ $activeBooking->room?->name }} &middot;
                                    {{ \Carbon\Carbon::parse($activeBooking->start_at)->format('H:i') }} –
                                    {{ \Carbon\Carbon::parse($activeBooking->end_at)->format('H:i') }}
                                </div>
                            </div>
                            <div class="bg-white/20 rounded-xl px-5 py-3 text-center min-w-[110px]">
                                <div class="text-xs opacity-75 font-semibold">Selesai dalam</div>
                                <div class="text-2xl font-bold mt-1" id="countdown-active">--:--</div>
                            </div>
                        </div>
                        <script>
                            (function () {
                                const end = new Date("{{ \Carbon\Carbon::parse($activeBooking->end_at)->toIso8601String() }}");
                                function tick() {
                                    const diff = Math.max(0, end - new Date());
                                    const h = Math.floor(diff / 3600000);
                                    const m = Math.floor((diff % 3600000) / 60000);
                                    const s = Math.floor((diff % 60000) / 1000);
                                    const el = document.getElementById('countdown-active');
                                    if (el) el.textContent = (h ? h + 'j ' : '') + m + 'm ' + s + 'd';
                                    if (diff > 0) setTimeout(tick, 1000);
                                }
                                tick();
                            })();
                        </script>

                    @elseif($nextBooking)
                        <div class="flex items-start justify-between gap-4 flex-wrap">
                            <div>
                                <div class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-2">Rapat Berikutnya
                                </div>
                                <div class="text-xl font-bold text-gray-900">{{ $nextBooking->title }}</div>
                                <div class="text-sm text-gray-500 mt-1">
                                    {{ $nextBooking->room?->name }} &middot;
                                    {{ \Carbon\Carbon::parse($nextBooking->start_at)->isoFormat('dddd, D MMM') }} &middot;
                                    {{ \Carbon\Carbon::parse($nextBooking->start_at)->format('H:i') }} –
                                    {{ \Carbon\Carbon::parse($nextBooking->end_at)->format('H:i') }}
                                </div>
                            </div>
                            <div
                                class="bg-indigo-50 border border-indigo-100 rounded-xl px-5 py-3 text-center min-w-[110px]">
                                <div class="text-xs text-indigo-400 font-semibold">Mulai dalam</div>
                                <div class="text-2xl font-bold text-indigo-600 mt-1" id="countdown-next">--:--</div>
                            </div>
                        </div>
                        @if($pendingCount > 0)
                            <div
                                class="mt-4 flex items-center gap-2 text-sm text-yellow-700 bg-yellow-50 border border-yellow-200 rounded-xl px-4 py-2.5">
                                ⏳ <span>Kamu punya <strong>{{ $pendingCount }} booking</strong> yang masih menunggu approval
                                    TU.</span>
                            </div>
                        @endif
                        <script>
                            (function () {
                                const start = new Date("{{ \Carbon\Carbon::parse($nextBooking->start_at)->toIso8601String() }}");
                                function tick() {
                                    const diff = Math.max(0, start - new Date());
                                    const h = Math.floor(diff / 3600000);
                                    const m = Math.floor((diff % 3600000) / 60000);
                                    const s = Math.floor((diff % 60000) / 1000);
                                    const el = document.getElementById('countdown-next');
                                    if (el) el.textContent = (h ? h + 'j ' : '') + m + 'm ' + s + 'd';
                                    if (diff > 0) setTimeout(tick, 1000);
                                }
                                tick();
                            })();
                        </script>

                    @else
                        <div class="flex items-center justify-between flex-wrap gap-4">
                            <div>
                                <div class="text-xl font-bold text-gray-900">Tidak ada rapat mendatang 🎉</div>
                                <div class="text-sm text-gray-400 mt-1">Jadwal kamu kosong untuk saat ini.</div>
                            </div>
                            @if($pendingCount > 0)
                                <div
                                    class="flex items-center gap-2 text-sm text-yellow-700 bg-yellow-50 border border-yellow-200 rounded-xl px-4 py-2.5">
                                    ⏳ <span><strong>{{ $pendingCount }} booking</strong> menunggu approval</span>
                                </div>
                            @endif
                        </div>
                    @endif

                </div>
            </div>

            {{-- SECTION 2: AGENDA HARI INI --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <div class="font-bold text-gray-900">Agenda Saya Hari Ini</div>
                        <div class="text-xs text-gray-400 mt-0.5">{{ $now->isoFormat('dddd, D MMMM Y') }}</div>
                    </div>
                    <a href="{{ route('agenda') }}" class="text-xs text-indigo-600 font-semibold hover:underline">Lihat
                        Semua →</a>
                </div>

                @if($todayBookings->isEmpty())
                    <div class="px-5 py-10 text-center text-gray-400 text-sm">
                        <div class="text-3xl mb-2">📭</div>
                        Tidak ada agenda hari ini
                    </div>
                @else
                    <div class="divide-y divide-gray-50">
                        @foreach($todayBookings as $booking)
                            @php
                                $start = \Carbon\Carbon::parse($booking->start_at);
                                $end = \Carbon\Carbon::parse($booking->end_at);
                                $isActive = $now->between($start, $end);
                                $isDone = $now->greaterThan($end);
                                $isSoon = !$isActive && !$isDone && $start->diffInMinutes($now) <= 30 && $start->greaterThan($now);
                                if ($isActive) {
                                    $statusLabel = 'Berlangsung';
                                    $statusClass = 'bg-indigo-100 text-indigo-700';
                                } elseif ($isDone) {
                                    $statusLabel = 'Selesai';
                                    $statusClass = 'bg-gray-100 text-gray-400';
                                } elseif ($isSoon) {
                                    $statusLabel = '⚡ Segera';
                                    $statusClass = 'bg-yellow-100 text-yellow-700';
                                } else {
                                    $statusLabel = 'Terjadwal';
                                    $statusClass = 'bg-green-100 text-green-700';
                                }
                            @endphp
                            <div class="px-5 py-4 flex items-center gap-4 {{ $isActive ? 'bg-indigo-50' : '' }}">
                                <div class="text-center min-w-[48px]">
                                    <div class="text-sm font-bold {{ $isActive ? 'text-indigo-600' : 'text-gray-700' }}">
                                        {{ \Carbon\Carbon::parse($booking->start_at)->format('H:i') }}
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        {{ \Carbon\Carbon::parse($booking->end_at)->format('H:i') }}</div>
                                </div>
                                <div class="w-px h-10 {{ $isActive ? 'bg-indigo-300' : 'bg-gray-200' }}"></div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-semibold text-sm text-gray-900 truncate flex items-center gap-2">
                                        @if($isActive)
                                            <span
                                                class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse inline-block flex-shrink-0"></span>
                                        @endif
                                        {{ $booking->title }}
                                    </div>
                                    <div class="text-xs text-gray-400 mt-0.5">
                                        {{ $booking->room?->name }}
                                        @if($booking->unit_kerja)
                                            &middot; <span class="text-gray-500 font-medium">{{ $booking->unit_kerja }}</span>
                                        @endif
                                    </div>
                                </div>
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-full flex-shrink-0 {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- SECTION 3: PENGAJUAN TERBARU --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <div class="font-bold text-gray-900">Pengajuan Terbaru</div>
                        <div class="text-xs text-gray-400 mt-0.5">5 booking terakhir yang diajukan</div>
                    </div>
                    <a href="{{ route('my_bookings.index') }}"
                        class="text-xs text-indigo-600 font-semibold hover:underline">Semua →</a>
                </div>

                @if($recentBookings->isEmpty())
                    <div class="px-5 py-10 text-center text-gray-400 text-sm">
                        <div class="text-3xl mb-2">📋</div>
                        Belum ada pengajuan
                    </div>
                @else
                    <div class="divide-y divide-gray-50">
                        @foreach($recentBookings as $booking)
                            @php
                                $statusMap = [
                                    'APPROVED' => ['label' => 'Disetujui', 'class' => 'bg-green-100 text-green-700'],
                                    'PENDING' => ['label' => 'Menunggu', 'class' => 'bg-yellow-100 text-yellow-700'],
                                    'REJECTED' => ['label' => 'Ditolak', 'class' => 'bg-red-100 text-red-600'],
                                    'CANCELLED' => ['label' => 'Dibatalkan', 'class' => 'bg-gray-100 text-gray-500'],
                                ];
                                $s = $statusMap[$booking->status] ?? ['label' => $booking->status, 'class' => 'bg-gray-100 text-gray-500'];
                            @endphp
                            <div class="px-5 py-4 flex items-center gap-3">
                                <div class="flex-shrink-0 bg-gray-50 rounded-xl px-2.5 py-2 text-center min-w-[44px]">
                                    <div class="text-base font-bold text-gray-700 leading-none">
                                        {{ \Carbon\Carbon::parse($booking->start_at)->format('d') }}
                                    </div>
                                    <div class="text-[10px] font-semibold text-gray-400 uppercase mt-0.5">
                                        {{ \Carbon\Carbon::parse($booking->start_at)->format('M') }}
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-semibold text-gray-900 truncate">{{ $booking->title }}</div>
                                    <div class="text-xs text-gray-400 mt-0.5">
                                        {{ $booking->room?->name }} &middot;
                                        {{ \Carbon\Carbon::parse($booking->start_at)->format('H:i') }} –
                                        {{ \Carbon\Carbon::parse($booking->end_at)->format('H:i') }}
                                        @if($booking->unit_kerja)
                                            &middot; <span class="text-gray-500 font-medium">{{ $booking->unit_kerja }}</span>
                                        @endif
                                    </div>
                                </div>
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-full flex-shrink-0 {{ $s['class'] }}">
                                    {{ $s['label'] }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>