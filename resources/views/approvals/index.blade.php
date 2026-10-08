<x-app-layout>
    <div class="space-y-6 px-4 py-6 sm:px-8">

        {{-- ===== Banner judul ===== --}}
        <div class="relative flex items-center gap-4 overflow-hidden rounded-2xl border border-white/70 bg-white/80 p-4 shadow-sm backdrop-blur sm:gap-5 sm:p-6">
            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-500 sm:h-16 sm:w-16">
                <svg class="h-7 w-7 sm:h-8 sm:w-8" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </span>
            <div class="min-w-0 flex-1">
                <h1 class="text-xl font-extrabold text-[#0f1e5a] sm:text-2xl">Approval Inbox</h1>
                <p class="mt-1 text-sm text-slate-500">Tinjau dan putuskan pengajuan booking untuk ruangan yang Anda kelola.</p>
            </div>
            <svg class="pointer-events-none absolute -bottom-2 right-2 hidden h-28 w-28 text-indigo-200/70 sm:block" viewBox="0 0 120 120" fill="currentColor" aria-hidden="true">
                <path d="M60 120C50 80 55 45 80 15c12 35 5 75-20 105z"/>
                <path d="M58 120C35 100 25 70 35 40c25 15 33 50 23 80z" opacity=".7"/>
                <path d="M62 120c20-15 38-20 55-12-12 18-35 24-55 12z" opacity=".6"/>
            </svg>
        </div>

        {{-- ===== Alert ===== --}}
        @if(session('status'))
            <div x-data="{ show: true }" x-show="show" x-transition class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800 shadow-sm">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span class="flex-1">{{ session('status') }}</span>
                <button @click="show = false" class="text-emerald-400 hover:text-emerald-600">✕</button>
            </div>
        @endif
        @if($errors->any())
            <div class="flex items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm font-semibold text-rose-800 shadow-sm">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ $errors->first() }}
            </div>
        @endif

        @if($noRoom)
            {{-- ===== Belum ada penugasan ruangan ===== --}}
            <div class="flex flex-col items-center rounded-3xl border border-slate-100 bg-white px-6 py-16 text-center shadow-sm">
                <span class="flex h-20 w-20 items-center justify-center rounded-full bg-indigo-50 text-indigo-400">
                    <svg class="h-10 w-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1"/></svg>
                </span>
                <h3 class="mt-5 text-lg font-bold text-slate-800">Belum Ada Penugasan Ruangan</h3>
                <p class="mt-2 max-w-sm text-sm text-slate-500">Anda belum ditugaskan untuk mengelola ruangan manapun. Silakan hubungi admin untuk mendapatkan penugasan.</p>
            </div>
        @else
            @php
                $totalPending = $clusters->sum(fn($c) => $c['items']->count());
                $conflictCount = $clusters->filter(fn($c) => $c['items']->count() > 1)->count();
            @endphp

            {{-- ===== Kartu ruangan ===== --}}
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-600 to-indigo-500 p-6 text-white shadow-xl shadow-indigo-600/20 sm:p-8">
                <div class="pointer-events-none absolute -right-10 -top-16 h-56 w-56 rounded-full bg-white/10"></div>
                <div class="pointer-events-none absolute -bottom-20 right-32 h-48 w-48 rounded-full bg-white/5"></div>
                <div class="relative flex flex-wrap items-center justify-between gap-5">
                    <div class="flex items-center gap-4 sm:gap-5">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/20 backdrop-blur">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1"/></svg>
                        </span>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-widest text-white/70">Ruangan yang Anda kelola</p>
                            <h2 class="mt-1 text-2xl font-extrabold sm:text-3xl">{{ $assignedRoom?->name ?? 'Semua Ruangan' }}</h2>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 rounded-full border border-white/20 bg-white/15 px-5 py-2.5 backdrop-blur">
                        <span class="h-2 w-2 rounded-full {{ $totalPending > 0 ? 'animate-pulse bg-amber-300' : 'bg-emerald-300' }}"></span>
                        <span class="text-sm font-bold">{{ $totalPending > 0 ? $totalPending . ' request menunggu' : 'Tidak ada request pending' }}</span>
                    </div>
                </div>
            </div>

            {{-- ===== Ringkasan ===== --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                @foreach([
                    ['Menunggu Persetujuan', $totalPending, 'request', 'bg-amber-100 text-amber-500', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['Jadwal Bentrok', $conflictCount, 'slot waktu', 'bg-rose-100 text-rose-500', 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z'],
                    ['Total Riwayat', $riwayat->total(), 'keputusan', 'bg-indigo-100 text-indigo-600', 'M12 8v4l3 3M3 12a9 9 0 109-9 9.75 9.75 0 00-6.74 2.74L3 8m0-5v5h5'],
                ] as [$lbl, $val, $unit, $cls, $icon])
                    <div class="flex items-center gap-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full {{ $cls }}">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                        </span>
                        <div>
                            <div class="text-xs font-medium text-slate-500">{{ $lbl }}</div>
                            <div class="text-3xl font-extrabold leading-tight text-slate-900">{{ $val }}</div>
                            <div class="text-xs text-slate-400">{{ $unit }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- ===== Tab ===== --}}
            <div x-data="{ tab: '{{ request()->has('page') ? 'riwayat' : 'pending' }}' }" class="space-y-5">

                <div class="flex max-w-full overflow-x-auto rounded-full bg-white p-1 shadow-sm sm:w-fit">
                    <button type="button" @click="tab = 'pending'"
                        :class="tab === 'pending' ? 'bg-indigo-500 text-white shadow' : 'text-slate-600 hover:text-slate-900'"
                        class="flex items-center gap-2 whitespace-nowrap rounded-full px-4 py-2 text-xs font-semibold transition sm:px-5 sm:py-2.5 sm:text-sm">
                        Pending Requests
                        @if($totalPending > 0)
                            <span :class="tab === 'pending' ? 'bg-white/25 text-white' : 'bg-amber-100 text-amber-700'" class="rounded-full px-2 py-0.5 text-[10px] font-extrabold">{{ $totalPending }}</span>
                        @endif
                    </button>
                    <button type="button" @click="tab = 'riwayat'"
                        :class="tab === 'riwayat' ? 'bg-indigo-500 text-white shadow' : 'text-slate-600 hover:text-slate-900'"
                        class="whitespace-nowrap rounded-full px-4 py-2 text-xs font-semibold transition sm:px-5 sm:py-2.5 sm:text-sm">
                        Riwayat Approval
                    </button>
                </div>

                {{-- ===== TAB: Pending ===== --}}
                <div x-show="tab === 'pending'" x-transition.opacity class="space-y-5">
                    @forelse($clusters as $c)
                        @php $count = $c['items']->count(); @endphp
                        <div class="overflow-hidden rounded-2xl border {{ $count > 1 ? 'border-rose-200' : 'border-slate-100' }} bg-white shadow-sm">

                            {{-- Header slot waktu --}}
                            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 {{ $count > 1 ? 'bg-rose-50/60' : 'bg-slate-50' }}">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-11 w-11 items-center justify-center rounded-xl {{ $count > 1 ? 'bg-rose-100 text-rose-500' : 'bg-indigo-50 text-indigo-500' }}">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M5 11h14M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z"/></svg>
                                    </span>
                                    <div>
                                        <div class="text-sm font-extrabold text-slate-900">{{ \Carbon\Carbon::parse($c['start'])->translatedFormat('l, d F Y') }}</div>
                                        <div class="mt-0.5 text-xs font-medium text-slate-500">
                                            {{ \Carbon\Carbon::parse($c['start'])->format('H.i') }} – {{ \Carbon\Carbon::parse($c['end'])->format('H.i') }}
                                            · {{ $c['room_name'] ?? 'Ruangan Tidak Diketahui' }}
                                        </div>
                                    </div>
                                </div>

                                @if($count > 1)
                                    <div class="flex items-center gap-2 rounded-full border border-rose-200 bg-white px-4 py-1.5">
                                        <span class="h-2 w-2 animate-pulse rounded-full bg-rose-500"></span>
                                        <span class="text-xs font-bold text-rose-700">BENTROK · {{ $count }} pengajuan</span>
                                    </div>
                                @else
                                    <span class="rounded-full bg-slate-100 px-4 py-1.5 text-xs font-bold text-slate-600">Tunggal</span>
                                @endif
                            </div>

                            @if($count > 1)
                                <div class="border-y border-rose-100 bg-rose-50 px-5 py-2.5 text-xs font-medium text-rose-700">
                                    Beberapa pengajuan meminta slot waktu yang sama. Pilih salah satu untuk disetujui.
                                </div>
                            @endif

                            {{-- Kartu pengajuan --}}
                            <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2 xl:grid-cols-3">
                                @foreach($c['items'] as $b)
                                    @php
                                        $picName = $b->pic?->name ?? '-';
                                        $picInit = mb_strtoupper(mb_substr($picName, 0, 1));
                                    @endphp
                                    <div class="group relative flex flex-col rounded-2xl border border-slate-200 bg-white p-5 transition hover:border-indigo-200 hover:shadow-md">

                                        <div class="mb-4 flex items-start justify-between gap-3">
                                            <h4 class="text-sm font-bold leading-snug text-slate-900 transition-colors group-hover:text-indigo-700">{{ $b->title }}</h4>
                                            <span class="shrink-0 rounded-lg bg-amber-100 px-2 py-1 text-[10px] font-extrabold uppercase tracking-wider text-amber-700">Pending</span>
                                        </div>

                                        <div class="mb-4 flex flex-1 flex-col gap-3 text-xs text-slate-600">
                                            <div class="flex items-center gap-3">
                                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-extrabold text-indigo-600">{{ $picInit }}</span>
                                                <div class="min-w-0">
                                                    <div class="truncate text-sm font-semibold text-slate-800">{{ $picName }}</div>
                                                    @if($b->unit_kerja)
                                                        <div class="truncate text-slate-500">{{ $b->unit_kerja }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2">
                                                <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span class="font-semibold text-slate-700">{{ \Carbon\Carbon::parse($b->start_at)->format('d M Y, H.i') }} – {{ \Carbon\Carbon::parse($b->end_at)->format('H.i') }}</span>
                                            </div>
                                            @if($b->description)
                                                <p class="line-clamp-2 rounded-xl border border-slate-100 bg-slate-50 p-3 text-slate-600">{{ $b->description }}</p>
                                            @endif
                                            <div class="text-[11px] text-slate-400">Diajukan {{ \Carbon\Carbon::parse($b->created_at)->translatedFormat('d M Y, H:i') }}</div>
                                        </div>

                                        <div class="mt-auto flex gap-2 border-t border-slate-100 pt-4">
                                            {{-- Approve --}}
                                            <form method="POST" action="{{ route('approvals.approve', $b) }}" class="flex-1"
                                                @if($count > 1) onsubmit="return confirm('Ada pengajuan lain pada slot waktu yang sama. Setujui pengajuan ini?')" @endif>
                                                @csrf
                                                <button type="submit" class="flex w-full items-center justify-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2.5 text-xs font-bold text-emerald-700 transition hover:border-emerald-500 hover:bg-emerald-500 hover:text-white">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                    Approve
                                                </button>
                                            </form>

                                            {{-- Reject + modal --}}
                                            <div x-data="{ open: false }" class="flex-1">
                                                <button type="button" @click="open = true" class="flex w-full items-center justify-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2.5 text-xs font-bold text-rose-700 transition hover:border-rose-500 hover:bg-rose-500 hover:text-white">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    Reject
                                                </button>

                                                <div x-show="open" x-cloak @keydown.escape.window="open = false" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                                                    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>

                                                    <div x-show="open" x-transition class="relative flex w-full max-w-md max-h-[90vh] overflow-y-auto flex-col rounded-3xl bg-white shadow-2xl">
                                                        <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-6 py-5">
                                                            <div class="min-w-0">
                                                                <h3 class="text-base font-bold text-slate-800">Tolak Permohonan</h3>
                                                                <p class="mt-1 truncate text-xs text-slate-500">{{ $b->title }}</p>
                                                            </div>
                                                            <button type="button" @click="open = false" class="rounded-full p-2 text-slate-400 transition hover:bg-slate-200 hover:text-slate-600">
                                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                            </button>
                                                        </div>

                                                        <form method="POST" action="{{ route('approvals.reject', $b) }}">
                                                            @csrf
                                                            <div class="p-6">
                                                                <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-700">Alasan Penolakan</label>
                                                                <textarea name="tu_note" required rows="3" placeholder="Tuliskan alasan spesifik menolak permohonan ini..."
                                                                    class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 transition focus:border-rose-400 focus:ring-2 focus:ring-rose-500/20"></textarea>
                                                                <p class="mt-2 text-xs text-slate-400">Alasan ini akan terlihat oleh pengaju.</p>
                                                            </div>
                                                            <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">
                                                                <button type="button" @click="open = false" class="w-full sm:w-auto rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-600 transition hover:bg-slate-50 text-center">Batal</button>
                                                                <button type="submit" class="w-full sm:w-auto rounded-xl bg-rose-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm shadow-rose-600/30 transition hover:bg-rose-700 text-center">Kirim Penolakan</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center rounded-2xl border border-slate-100 bg-white px-6 py-20 text-center shadow-sm">
                            <span class="flex h-20 w-20 items-center justify-center rounded-full bg-emerald-50 text-emerald-500">
                                <svg class="h-10 w-10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <h4 class="mt-5 text-lg font-bold text-slate-800">Semua Beres!</h4>
                            <p class="mt-1 text-sm text-slate-500">Tidak ada request pending untuk ruangan ini saat ini.</p>
                        </div>
                    @endforelse
                </div>

                {{-- ===== TAB: Riwayat ===== --}}
                <div x-show="tab === 'riwayat'" x-cloak x-transition.opacity class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">

                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                        <div class="flex items-center gap-3">
                            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-500">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3M3 12a9 9 0 109-9 9.75 9.75 0 00-6.74 2.74L3 8m0-5v5h5"/></svg>
                            </span>
                            <h3 class="text-lg font-extrabold text-slate-900">Riwayat Keputusan</h3>
                        </div>
                        <span class="rounded-full border border-indigo-100 bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-700">{{ $riwayat->total() }} Total</span>
                    </div>

                    <div class="flex flex-col">
                        @php $lastRiwayatDate = null; @endphp
                        @forelse($riwayat as $b)
                            @php
                                $bDate = \Carbon\Carbon::parse($b->start_at)->format('Y-m-d');
                                $bDateLabel = \Carbon\Carbon::parse($b->start_at)->translatedFormat('l, d F Y');
                                [$badge, $dot, $label] = match ($b->status) {
                                    'APPROVED' => ['bg-emerald-50 text-emerald-700', 'bg-emerald-500', 'Disetujui'],
                                    'REJECTED' => ['bg-rose-50 text-rose-700', 'bg-rose-500', 'Ditolak'],
                                    'CANCELLED' => ['bg-slate-100 text-slate-600', 'bg-slate-400', 'Dibatalkan'],
                                    default => ['bg-slate-100 text-slate-600', 'bg-slate-400', $b->status],
                                };
                            @endphp

                            @if($bDate !== $lastRiwayatDate)
                                @php $lastRiwayatDate = $bDate; @endphp
                                <div class="border-y border-slate-100 bg-slate-50 px-6 py-2.5">
                                    <span class="text-[11px] font-extrabold uppercase tracking-widest text-slate-500">{{ $bDateLabel }}</span>
                                </div>
                            @endif

                            <div class="flex flex-col justify-between gap-4 border-b border-slate-50 px-4 py-4 transition hover:bg-slate-50/60 sm:flex-row sm:items-center sm:px-6">
                                <div class="min-w-0 flex-1">
                                    <h4 class="truncate text-sm font-bold text-slate-900">{{ $b->title }}</h4>
                                    <div class="mt-1.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-500">
                                        <span class="font-semibold text-slate-600">{{ $b->room?->name ?? 'Ruang Tidak Diketahui' }}</span>
                                        <span>•</span>
                                        <span>{{ \Carbon\Carbon::parse($b->start_at)->format('H.i') }} – {{ \Carbon\Carbon::parse($b->end_at)->format('H.i') }}</span>
                                        @if($b->unit_kerja)
                                             <span>•</span>
                                             <span>{{ $b->unit_kerja }}</span>
                                        @endif
                                    </div>
                                    @if($b->tu_note)
                                        <div class="mt-2 inline-block rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-[11px] text-slate-500">
                                            <span class="font-semibold">Catatan:</span> {{ $b->tu_note }}
                                        </div>
                                    @endif
                                </div>

                                <div class="flex shrink-0 items-center gap-3">
                                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold {{ $badge }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $dot }}"></span>{{ $label }}
                                    </span>

                                    @if($b->status === 'APPROVED')
                                        <div x-data="{ openCancel: false }">
                                            <button type="button" @click="openCancel = true" title="Batalkan Approval"
                                                class="rounded-xl border border-orange-200 bg-white p-2 text-orange-600 transition hover:border-orange-300 hover:bg-orange-50 hover:text-orange-700">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                            </button>

                                            <div x-show="openCancel" x-cloak @keydown.escape.window="openCancel = false" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                                                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="openCancel = false"></div>

                                                <div x-show="openCancel" x-transition class="relative flex w-full max-w-md max-h-[90vh] overflow-y-auto flex-col rounded-3xl bg-white shadow-2xl">
                                                    <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-6 py-5">
                                                        <div class="min-w-0">
                                                            <h3 class="text-base font-bold text-slate-800">Batalkan Approval</h3>
                                                            <p class="mt-1 truncate text-xs text-slate-500">{{ $b->title }}</p>
                                                        </div>
                                                        <button type="button" @click="openCancel = false" class="rounded-full p-2 text-slate-400 transition hover:bg-slate-200 hover:text-slate-600">
                                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                        </button>
                                                    </div>

                                                    <form method="POST" action="{{ route('approvals.cancelApprove', $b) }}">
                                                        @csrf
                                                        <div class="p-6">
                                                            <div class="mb-5 flex gap-3 rounded-2xl border border-orange-200 bg-orange-50 p-4 text-sm text-orange-800">
                                                                <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                                <p>Status booking ini akan diubah menjadi <strong class="font-extrabold">CANCELLED</strong>. Pengaju perlu membuat ulang jika masih diperlukan.</p>
                                                            </div>
                                                            <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-700">Alasan (Opsional)</label>
                                                            <textarea name="tu_note" rows="3" placeholder="Tulis alasan pembatalan approval..."
                                                                class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 transition focus:border-orange-400 focus:ring-2 focus:ring-orange-500/20"></textarea>
                                                        </div>
                                                        <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">
                                                            <button type="button" @click="openCancel = false" class="w-full sm:w-auto rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-600 transition hover:bg-slate-50 text-center">Tutup</button>
                                                            <button type="submit" class="w-full sm:w-auto rounded-xl bg-orange-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm shadow-orange-600/30 transition hover:bg-orange-700 text-center">Ya, Batalkan Approval</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="flex flex-col items-center px-6 py-20 text-center">
                                <span class="flex h-20 w-20 items-center justify-center rounded-full bg-slate-50 text-slate-400">
                                    <svg class="h-10 w-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                </span>
                                <h4 class="mt-5 text-lg font-bold text-slate-800">Belum Ada Riwayat</h4>
                                <p class="mt-1 text-sm text-slate-500">Belum ada tindakan approval atau reject yang dilakukan.</p>
                            </div>
                        @endforelse
                    </div>

                    @if($riwayat->hasPages())
                        <div class="border-t border-slate-100 bg-slate-50 px-6 py-4">
                            {{ $riwayat->links() }}
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-app-layout>