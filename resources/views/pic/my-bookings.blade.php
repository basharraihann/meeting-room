<x-app-layout>
    @php
        $tabs = [
            ''         => 'Semua',
            'PENDING'  => 'Menunggu',
            'APPROVED' => 'Disetujui',
            'REJECTED' => 'Ditolak',
            'CANCELED' => 'Dibatalkan',
        ];

        $roomColors = [1 => '#1a1a1a', 2 => '#a855f7', 3 => '#92400e', 4 => '#facc15', 5 => '#22d3ee', 6 => '#ef4444', 7 => '#ec4899', 8 => '#468432'];

        $lastDate = null;
        $todayStr = now()->format('Y-m-d');
    @endphp

    <div class="space-y-6 px-4 py-8 sm:px-8">

        {{-- ===== Banner judul ===== --}}
        <div class="relative flex items-center gap-4 overflow-hidden rounded-2xl border border-white/70 bg-white/80 p-4 shadow-sm backdrop-blur sm:gap-5 sm:p-6">
            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-500 sm:h-16 sm:w-16">
                <svg class="h-7 w-7 sm:h-8 sm:w-8" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3M3 12a9 9 0 109-9 9.75 9.75 0 00-6.74 2.74L3 8m0-5v5h5"/></svg>
            </span>
            <div class="min-w-0 flex-1">
                <h1 class="text-xl font-extrabold text-[#0f1e5a] sm:text-2xl">Riwayat Pengajuan</h1>
                <p class="mt-1 text-sm text-slate-500">Pantau status pengajuan rapat Anda, hubungi TU, atau batalkan booking.</p>
            </div>

            <svg class="pointer-events-none absolute -bottom-2 right-2 hidden h-28 w-28 text-indigo-200/70 sm:block" viewBox="0 0 120 120" fill="currentColor" aria-hidden="true">
                <path d="M60 120C50 80 55 45 80 15c12 35 5 75-20 105z"/>
                <path d="M58 120C35 100 25 70 35 40c25 15 33 50 23 80z" opacity=".7"/>
                <path d="M62 120c20-15 38-20 55-12-12 18-35 24-55 12z" opacity=".6"/>
            </svg>

            <a href="{{ route('calendar', ['ajukan' => 1]) }}"
                class="relative z-10 shrink-0 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-600/25 transition hover:bg-indigo-700 sm:px-5">
                + Ajukan Rapat
            </a>
        </div>

        {{-- ===== Filter ===== --}}
        <div class="space-y-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap gap-2">
                    @foreach($tabs as $key => $label)
                        @php $active = ($status === $key || (empty($status) && $key === '')); @endphp
                        <a href="{{ route('my_bookings.index', array_filter(['status' => $key, 'q' => $q, 'unit_kerja' => $unitKerja])) }}"
                            class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $active ? 'bg-indigo-500 text-white shadow' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                <form method="GET" class="flex items-center gap-2">
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
                        <input type="text" name="q" value="{{ $q }}" placeholder="Cari judul rapat..."
                            class="w-60 rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm placeholder-slate-400 focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 sm:w-64">
                    </div>
                    @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
                    @if($unitKerja)<input type="hidden" name="unit_kerja" value="{{ $unitKerja }}">@endif
                    <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-indigo-700">Cari</button>
                </form>
            </div>

            @if($unitKerjaOptions->isNotEmpty())
                <div class="flex flex-wrap items-center gap-2 border-t border-slate-100 pt-4">
                    <span class="mr-1 text-[11px] font-bold uppercase tracking-wider text-slate-400">Unit Kerja</span>
                    <a href="{{ route('my_bookings.index', array_filter(['status' => $status, 'q' => $q])) }}"
                        class="rounded-full px-3.5 py-1.5 text-xs font-bold transition {{ !$unitKerja ? 'bg-indigo-500 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        Semua
                    </a>
                    @foreach($unitKerjaOptions as $uk)
                        <a href="{{ route('my_bookings.index', array_filter(['status' => $status, 'q' => $q, 'unit_kerja' => $uk])) }}"
                            class="rounded-full px-3.5 py-1.5 text-xs font-bold transition {{ $unitKerja === $uk ? 'bg-indigo-500 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            {{ $uk }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ===== Daftar rapat ===== --}}
        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-2 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M5 11h14M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z"/></svg>
                    </span>
                    <h2 class="text-lg font-extrabold text-slate-900">Daftar Rapat</h2>
                </div>
                @if($bookings->total() > 0)
                    <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-600">{{ $bookings->total() }} total</span>
                @endif
            </div>

            @forelse($bookings as $b)
                @php
                    $now = now();
                    $bDateStr = \Carbon\Carbon::parse($b->start_at)->format('Y-m-d');
                    $bDateLabel = \Carbon\Carbon::parse($b->start_at)->translatedFormat('l, d F Y');
                    $isToday = $bDateStr === $todayStr;

                    $displayStatus = strtoupper($b->status);
                    $isDone = ($b->status === 'APPROVED' && \Carbon\Carbon::parse($b->end_at)->lt($now));
                    if ($isDone) $displayStatus = 'DONE';

                    [$sLabel, $sCls] = match ($displayStatus) {
                        'APPROVED'            => ['Disetujui', 'bg-emerald-50 text-emerald-700'],
                        'PENDING'             => ['Menunggu', 'bg-amber-50 text-amber-700'],
                        'REJECTED'            => ['Ditolak', 'bg-red-50 text-red-600'],
                        'CANCELED', 'CANCELLED' => ['Dibatalkan', 'bg-slate-100 text-slate-500'],
                        'DONE'                => ['Selesai', 'bg-sky-50 text-sky-700'],
                        default               => [ucfirst(strtolower($displayStatus)), 'bg-slate-100 text-slate-600'],
                    };

                    $c = $roomColors[$b->room_id ?? 0] ?? '#cbd5e1';
                @endphp

                {{-- Pemisah tanggal --}}
                @if($bDateStr !== $lastDate)
                    @php $lastDate = $bDateStr; @endphp
                    <div class="mb-2 mt-5 flex items-center gap-3">
                        <span class="text-xs font-extrabold uppercase tracking-wider {{ $isToday ? 'text-indigo-600' : 'text-slate-500' }}">
                            {{ $bDateLabel }}@if($isToday) · Hari ini @endif
                        </span>
                        <span class="h-px flex-1 bg-slate-100"></span>
                    </div>
                @endif

                <div class="mb-2 flex flex-wrap items-center gap-3 rounded-xl border border-slate-100 px-4 py-3.5 transition hover:border-indigo-200 hover:bg-indigo-50/30 sm:flex-nowrap sm:gap-4 {{ in_array($displayStatus, ['CANCELED', 'CANCELLED', 'REJECTED']) ? 'opacity-70' : '' }}">
                    <span class="h-11 w-1 shrink-0 rounded" style="background: {{ $c }}"></span>

                    <div class="min-w-0 flex-1">
                        <div class="text-sm font-bold leading-snug text-slate-900">{{ $b->title }}</div>
                        <div class="mt-0.5 flex flex-wrap items-center gap-x-2 text-xs text-slate-500">
                            <span>{{ $b->room?->name ?? '-' }}</span>

                            @if($b->room?->tuUser?->phone && $b->status === 'PENDING')
                                @php
                                    $tuPhone = preg_replace('/^0/', '62', $b->room->tuUser->phone);
                                    $tuPhone = ltrim($tuPhone, '+');
                                    $start = \Carbon\Carbon::parse($b->start_at)->translatedFormat('d F Y');
                                    $jam = \Carbon\Carbon::parse($b->start_at)->format('H:i') . ' - ' . \Carbon\Carbon::parse($b->end_at)->format('H:i');
                                    $waMsg = "Halo Bapak/Ibu {$b->room->tuUser->name},\n\n"
                                        . "Saya PIC {$b->unit_kerja} ingin mengkonfirmasi pengajuan peminjaman ruang rapat:\n\n"
                                        . "*{$b->title}*\n"
                                        . "Ruangan: {$b->room->name}\n"
                                        . "Tanggal: {$start}\n"
                                        . "Waktu: {$jam}\n\n"
                                        . "Mohon konfirmasinya apakah jadwal tersebut tersedia. Jika tersedia, mohon untuk melakukan approval di sistem.\n\nTerima kasih.";
                                    $waUrl = 'https://wa.me/' . $tuPhone . '?text=' . rawurlencode($waMsg);
                                @endphp
                                <span class="text-slate-300">·</span>
                                <a href="{{ $waUrl }}" target="_blank"
                                    class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-bold text-emerald-600 hover:bg-emerald-100"
                                    title="Chat TU {{ $b->room->tuUser->name }}">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4-.8L3 20l1.3-3.9A7.6 7.6 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    Chat TU
                                </a>
                            @endif

                            <span class="text-slate-300">·</span>
                            <span class="font-semibold text-slate-600">{{ \Carbon\Carbon::parse($b->start_at)->format('H.i') }} – {{ \Carbon\Carbon::parse($b->end_at)->format('H.i') }}</span>

                            @if($b->unit_kerja)
                                <span class="text-slate-300">·</span>
                                <span>{{ $b->unit_kerja }}</span>
                            @endif
                        </div>
                        @if($b->description)
                            <div class="mt-1 line-clamp-1 text-xs text-slate-400">{{ $b->description }}</div>
                        @endif
                    </div>

                    <div class="flex shrink-0 items-center gap-2">
                        <span class="rounded-full px-3 py-1 text-xs font-bold {{ $sCls }}">{{ $sLabel }}</span>
                        @if(in_array($b->status, ['PENDING', 'APPROVED']) && !$isDone)
                            <button type="button" onclick="openCancelModal({{ $b->id }})"
                                class="rounded-full bg-slate-800 px-3.5 py-1.5 text-[11px] font-bold text-white transition hover:bg-red-600">
                                Cancel
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center py-14 text-center">
                    <span class="flex h-16 w-16 items-center justify-center rounded-full bg-indigo-50 text-indigo-400">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </span>
                    <div class="mt-4 text-base font-bold text-slate-800">Belum ada riwayat rapat</div>
                    <p class="mt-1 text-sm text-slate-500">Pengajuan yang Anda buat akan muncul di sini.</p>
                </div>
            @endforelse

            @if($bookings->hasPages())
                <div class="mt-5 border-t border-slate-100 pt-4">
                    {{ $bookings->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- ===== Modal batalkan booking ===== --}}
    <div id="cancelModal" class="fixed inset-0 z-50 items-center justify-center bg-slate-900/50 p-4" style="display:none;">
        <div class="w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <h3 class="text-base font-extrabold text-slate-900">Batalkan Booking</h3>
                <button type="button" onclick="closeCancelModal()"
                    class="rounded-lg bg-slate-100 px-2.5 py-1.5 text-sm text-slate-500 hover:bg-slate-200">✕</button>
            </div>
            <form id="cancelForm" method="POST">
                @csrf
                <div class="px-6 py-5">
                    <label class="mb-1.5 block text-xs font-bold text-slate-600">Alasan Pembatalan</label>
                    <textarea name="cancel_reason" rows="4" required placeholder="Masukkan alasan pembatalan..."
                        class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-500/10"></textarea>
                </div>
                <div class="flex justify-end gap-2 border-t border-slate-100 bg-slate-50 px-6 py-4">
                    <button type="button" onclick="closeCancelModal()"
                        class="rounded-xl bg-slate-200 px-5 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-300">Tutup</button>
                    <button type="submit"
                        class="rounded-xl bg-red-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-red-600/25 transition hover:bg-red-700">Konfirmasi Batal</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const cancelUrlTemplate = @json(route('bookings.cancel', ['booking' => '__ID__']));

        function openCancelModal(bookingId) {
            document.getElementById('cancelForm').action = cancelUrlTemplate.replace('__ID__', bookingId);
            document.getElementById('cancelModal').style.display = 'flex';
        }
        function closeCancelModal() {
            document.getElementById('cancelModal').style.display = 'none';
        }
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeCancelModal(); });
    </script>
</x-app-layout>