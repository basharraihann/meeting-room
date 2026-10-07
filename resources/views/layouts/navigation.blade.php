@php
    $user = auth()->user();
    $links = [];

    /*
     * Set ikon (gaya Lucide, ISC license). Berisi isi <svg> agar bisa multi-elemen.
     * Dirender lewat <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">.
     */
    $ic = [
        // layout-dashboard
        'home' => '<rect width="7" height="9" x="3" y="3" rx="1.5"/><rect width="7" height="5" x="14" y="3" rx="1.5"/><rect width="7" height="9" x="14" y="12" rx="1.5"/><rect width="7" height="5" x="3" y="16" rx="1.5"/>',
        // calendar-days
        'schedule' => '<path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2.5"/><path d="M3 10h18"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/><path d="M16 18h.01"/>',
        // clipboard-list
        'agenda' => '<rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/>',
        // history
        'history' => '<path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/>',
        // badge-check
        'approval' => '<path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"/><path d="m9 12 2 2 4-4"/>',
        // users
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        // user-round
        'profile' => '<circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/>',
        // log-out
        'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>',
    ];

    if ($user->hasRole('PIC')) {
        $links[] = [
            'name' => 'Beranda',
            'url' => route('dashboard'),
            'active' => request()->routeIs('dashboard'),
            'icon' => $ic['home'],
        ];
        $links[] = [
            'name' => 'Jadwal Ruang Rapat',
            'url' => route('calendar'),
            'active' => request()->routeIs('calendar'),
            'icon' => $ic['schedule'],
        ];
        $links[] = [
            'name' => 'Agenda Saya',
            'url' => route('agenda'),
            'active' => request()->routeIs('agenda'),
            'icon' => $ic['agenda'],
        ];
        $links[] = [
            'name' => 'Riwayat Booking',
            'url' => route('my_bookings.index'),
            'active' => request()->routeIs('my_bookings.*'),
            'icon' => $ic['history'],
        ];
    }

    $pendingCount = 0;
    if ($user->hasRole('TU')) {
        $pendingCount = $user->room_id
            ? \App\Models\Booking::where('status', 'PENDING')->where('room_id', $user->room_id)->count()
            : 0;
        $links[] = [
            'name' => 'Jadwal Ruang Rapat',
            'url' => route('calendar'),
            'active' => request()->routeIs('calendar'),
            'icon' => $ic['schedule'],
        ];
        $links[] = [
            'name' => 'Approvals',
            'url' => route('approvals.index'),
            'active' => request()->routeIs('approvals.*'),
            'icon' => $ic['approval'],
            'badge' => $pendingCount
        ];
    }

    if ($user->hasRole('Admin')) {
        $links[] = [
            'name' => 'Manajemen User',
            'url' => route('admin.users.index'),
            'active' => request()->routeIs('admin.users.*'),
            'icon' => $ic['users'],
        ];
    }

    $links = collect($links)->unique('name')->values()->all();

    $initials = collect(preg_split('/\s+/', trim($user->name)))->take(2)->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
    $primaryRole = $user->roles->pluck('name')->first() ?? ($user->hasRole('Admin') ? 'Admin' : ($user->hasRole('TU') ? 'TU' : 'PIC'));
    $roleBadgeClass = match ($primaryRole) {
        'Admin' => 'bg-purple-100 text-purple-700',
        'PIC' => 'bg-indigo-100 text-indigo-700',
        'TU' => 'bg-emerald-100 text-emerald-700',
        default => 'bg-slate-100 text-slate-700',
    };
    $homeUrl = $user->hasRole('PIC') ? route('dashboard') : ($user->hasRole('Admin') ? route('admin.users.index') : route('calendar'));
@endphp

<div class="relative flex h-full flex-col justify-between overflow-visible select-none bg-white">

    {{-- Toggle Collapse/Expand Button (Floating capsule on right border) --}}
    <button type="button" @click="toggleSidebar()"
        class="hidden lg:flex absolute -right-3 top-1/2 -translate-y-1/2 z-50 h-14 w-6 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-400 shadow-sm transition-all duration-200 hover:bg-slate-50 hover:text-slate-600 hover:shadow focus:outline-none"
        :title="sidebarCollapsed ? 'Perlebar Sidebar' : 'Tutup Sidebar'">
        <svg x-show="!sidebarCollapsed" class="sidebar-expanded-only h-3.5 w-3.5" fill="none" stroke="currentColor"
            stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
        <svg x-show="sidebarCollapsed" x-cloak class="sidebar-collapsed-only h-3.5 w-3.5" fill="none"
            stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
        </svg>
    </button>

    {{-- Top Section: hanya untuk tombol close di mobile --}}
    <div class="flex h-14 shrink-0 items-center justify-end border-b border-slate-100 px-4 lg:hidden">
        <button @click="sidebarMobile = false"
            class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    {{-- Middle Section: Navigation Links --}}
    <nav class="sidebar-nav flex-1 overflow-y-auto overflow-x-hidden py-4 space-y-1 px-2.5"
        :class="sidebarCollapsed ? 'px-2 flex flex-col items-center' : 'px-2.5'">
        @foreach($links as $l)
            <a href="{{ $l['url'] }}" @if($l['active']) aria-current="page" @endif
                :class="sidebarCollapsed
                                                ? 'h-11 w-11 justify-center {{ $l['active'] ? 'bg-indigo-50 text-indigo-600 shadow-sm' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}'
                                                : 'w-full px-3 py-2.5 {{ $l['active'] ? 'bg-indigo-50/80 text-indigo-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}'"
                class="nav-item-link group relative flex items-center rounded-xl text-sm transition-colors w-full px-3 py-2.5 {{ $l['active'] ? 'bg-indigo-50/80 text-indigo-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}"
                :title="sidebarCollapsed ? '{{ $l['name'] }}' : ''">

                {{-- Indikator aktif --}}
                @if($l['active'])
                    <span class="absolute -left-2.5 top-2.5 bottom-2.5 w-[3px] rounded-r-full bg-indigo-600"
                        aria-hidden="true"></span>
                @endif

                <svg class="h-[22px] w-[22px] shrink-0 transition-colors {{ $l['active'] ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-700' }}"
                    fill="none" stroke="currentColor" stroke-width="{{ $l['active'] ? '2' : '1.75' }}"
                    stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                    {!! $l['icon'] !!}
                </svg>

                <span x-show="!sidebarCollapsed" class="sidebar-expanded-only ml-3 truncate whitespace-nowrap">
                    {{ $l['name'] }}
                </span>

                @if(($l['badge'] ?? 0) > 0)
                    <span x-show="!sidebarCollapsed"
                        class="sidebar-expanded-only ml-auto inline-flex h-5 min-w-[20px] items-center justify-center rounded-full bg-red-500 px-1.5 text-xs font-bold text-white">
                        {{ $l['badge'] }}
                    </span>
                    <span x-show="sidebarCollapsed" x-cloak
                        class="sidebar-collapsed-only absolute -top-1 -right-1 inline-flex h-4 min-w-[16px] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white">
                        {{ $l['badge'] }}
                    </span>
                @endif
            </a>
        @endforeach
    </nav>

    {{-- Bottom Section: User Info Card & Actions --}}
    <div class="shrink-0 border-t border-slate-100">
        {{-- Expanded Bottom View --}}
        <div x-show="!sidebarCollapsed" class="sidebar-expanded-only p-2.5 pt-2.5">
            {{-- User Info Box --}}
            <div class="flex items-center gap-2.5 px-1.5 py-1.5">
                <div
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-600">
                    {{ $initials }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="truncate text-xs font-bold text-slate-900 leading-tight">{{ $user->name }}</div>
                    <div class="truncate text-[11px] text-slate-400 leading-tight mt-0.5">{{ $user->email }}</div>
                </div>
                <span class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-semibold {{ $roleBadgeClass }}">
                    {{ $primaryRole }}
                </span>
            </div>

            {{-- Actions --}}
            <div class="mt-1 space-y-0.5">
                <a href="{{ route('profile.edit') }}"
                    class="group flex items-center gap-2.5 rounded-xl px-2.5 py-2 text-sm font-medium transition-colors hover:bg-slate-50 hover:text-slate-900 {{ request()->routeIs('profile.*') ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-slate-600' }}">
                    <svg class="h-5 w-5 shrink-0 {{ request()->routeIs('profile.*') ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-700' }}"
                        fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"
                        stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                        {!! $ic['profile'] !!}
                    </svg>
                    <span>Profil</span>
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="group flex w-full items-center gap-2.5 rounded-xl px-2.5 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-red-50 hover:text-red-600">
                        <svg class="h-5 w-5 shrink-0 text-slate-400 transition-colors group-hover:text-red-500"
                            fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"
                            stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                            {!! $ic['logout'] !!}
                        </svg>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </div>

        {{-- Collapsed Bottom View --}}
        <div x-show="sidebarCollapsed" x-cloak class="sidebar-collapsed-only flex flex-col items-center gap-2.5 py-3">
            {{-- User Initial Avatar --}}
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-600 cursor-default"
                title="{{ $user->name }} ({{ $primaryRole }})">
                {{ $initials }}
            </div>

            {{-- Profil Icon --}}
            <a href="{{ route('profile.edit') }}"
                class="flex h-9 w-9 items-center justify-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 {{ request()->routeIs('profile.*') ? 'bg-indigo-50 text-indigo-600' : '' }}"
                title="Profil">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75"
                    stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                    {!! $ic['profile'] !!}
                </svg>
            </a>

            {{-- Keluar Icon --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="flex h-9 w-9 items-center justify-center rounded-xl text-slate-500 transition hover:bg-red-50 hover:text-red-600"
                    title="Keluar">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75"
                        stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                        {!! $ic['logout'] !!}
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>