@php
    $user = auth()->user();
    $links = [];

    if ($user->hasRole('PIC')) {
        $links[] = ['Beranda', route('dashboard'), request()->routeIs('dashboard'), 'M3 12l9-9 9 9M5 10v10a1 1 0 001 1h4v-6h4v6h4a1 1 0 001-1V10'];
        $links[] = ['Jadwal Ruang Rapat', route('calendar'), request()->routeIs('calendar'), 'M8 7V3m8 4V3M5 11h14M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z'];
        $links[] = ['Agenda Saya', route('agenda'), request()->routeIs('agenda'), 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'];
        $links[] = ['Riwayat Booking', route('my_bookings.index'), request()->routeIs('my_bookings.*'), 'M12 8v4l3 3M3 12a9 9 0 109-9 9.75 9.75 0 00-6.74 2.74L3 8m0-5v5h5'];
    }

    $pendingCount = 0;
    if ($user->hasRole('TU')) {
        $pendingCount = $user->room_id
            ? \App\Models\Booking::where('status', 'PENDING')->where('room_id', $user->room_id)->count()
            : 0;
        $links[] = ['Jadwal Ruang Rapat', route('calendar'), request()->routeIs('calendar'), 'M8 7V3m8 4V3M5 11h14M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z'];
        $links[] = ['Approvals', route('approvals.index'), request()->routeIs('approvals.*'), 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', $pendingCount];
    }

    if ($user->hasRole('Admin')) {
        $links[] = ['Manajemen User', route('admin.users.index'), request()->routeIs('admin.users.*'), 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'];
    }

    $links[] = ['Pengaturan', route('profile.edit'), request()->routeIs('profile.*'), 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z'];
    $links = collect($links)->unique(0)->values()->all();
@endphp

<div class="flex h-full flex-col">
    {{-- Logo --}}
    <a href="{{ $user->hasRole('PIC') ? route('dashboard') : route('calendar') }}" class="flex items-center gap-3 px-6 pb-6 pt-6">
        <img src="{{ asset('images/logoheader.png') }}" alt="Logo Kemenko Pangan" class="h-12 w-auto">
    </a>

    {{-- Menu --}}
    <nav class="flex-1 space-y-1.5 px-4">
        @foreach($links as $l)
            <a href="{{ $l[1] }}"
                class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition
                       {{ $l[2] ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $l[3] }}" />
                </svg>
                <span class="flex-1">{{ $l[0] }}</span>
                @if(($l[4] ?? 0) > 0)
                    <span class="inline-flex h-5 min-w-[20px] items-center justify-center rounded-full bg-red-500 px-1.5 text-xs font-bold text-white">{{ $l[4] }}</span>
                @endif
            </a>
        @endforeach
    </nav>

    {{-- Footer sidebar --}}
    <div class="px-6 pb-6 pt-8 text-xs leading-relaxed text-slate-500">
        Kementerian Koordinator<br>Bidang Pangan - Republik Indonesia<br>&copy; {{ date('Y') }}
    </div>
</div>