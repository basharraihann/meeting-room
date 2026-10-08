@php
    $user = auth()->user();
    $links = [];

    if ($user->hasRole('PIC')) {
        $links[] = [
            'name' => 'Beranda',
            'url' => route('dashboard'),
            'active' => request()->routeIs('dashboard'),
            'icon' => 'M3 12l9-9 9 9M5 10v10a1 1 0 001 1h4v-6h4v6h4a1 1 0 001-1V10'
        ];
        $links[] = [
            'name' => 'Jadwal Ruang Rapat',
            'url' => route('calendar'),
            'active' => request()->routeIs('calendar'),
            'icon' => 'M8 7V3m8 4V3M5 11h14M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z'
        ];
        $links[] = [
            'name' => 'Agenda Saya',
            'url' => route('agenda'),
            'active' => request()->routeIs('agenda'),
            'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'
        ];
        $links[] = [
            'name' => 'Riwayat Booking',
            'url' => route('my_bookings.index'),
            'active' => request()->routeIs('my_bookings.*'),
            'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'
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
            'icon' => 'M8 7V3m8 4V3M5 11h14M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z'
        ];
        $links[] = [
            'name' => 'Approvals',
            'url' => route('approvals.index'),
            'active' => request()->routeIs('approvals.*'),
            'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            'badge' => $pendingCount
        ];
    }

    if ($user->hasRole('Admin')) {
        $links[] = [
            'name' => 'Manajemen User',
            'url' => route('admin.users.index'),
            'active' => request()->routeIs('admin.users.*'),
            'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'
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
        :class="isDesktopCollapsed() ? 'px-2 flex flex-col items-center' : 'px-2.5'">
        @foreach($links as $l)
            <a href="{{ $l['url'] }}"
                :class="isDesktopCollapsed()
                    ? 'h-11 w-11 justify-center {{ $l['active'] ? 'bg-indigo-50 text-indigo-600 shadow-sm' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}'
                    : 'w-full px-3 py-2.5 {{ $l['active'] ? 'bg-indigo-50/80 text-indigo-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}'"
                class="nav-item-link group relative flex items-center rounded-xl text-sm transition-colors w-full px-3 py-2.5 {{ $l['active'] ? 'bg-indigo-50/80 text-indigo-600 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 font-medium' }}"
                :title="isDesktopCollapsed() ? '{{ $l['name'] }}' : ''">

                <svg class="h-5 w-5 shrink-0 {{ $l['active'] ? 'text-indigo-600' : 'text-slate-500 group-hover:text-slate-700' }}"
                    fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $l['icon'] }}" />
                </svg>

                <span x-show="!isDesktopCollapsed()" class="sidebar-expanded-only ml-3 truncate whitespace-nowrap">
                    {{ $l['name'] }}
                </span>

                @if(($l['badge'] ?? 0) > 0)
                    <span x-show="!isDesktopCollapsed()"
                        class="sidebar-expanded-only ml-auto inline-flex h-5 min-w-[20px] items-center justify-center rounded-full bg-red-500 px-1.5 text-xs font-bold text-white">
                        {{ $l['badge'] }}
                    </span>
                    <span x-show="isDesktopCollapsed()" x-cloak
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
        <div x-show="!isDesktopCollapsed()" class="sidebar-expanded-only p-2.5 pt-2.5">
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
                    class="flex items-center gap-2.5 rounded-xl px-2.5 py-2 text-sm font-medium transition-colors hover:bg-slate-50 hover:text-slate-900 {{ request()->routeIs('profile.*') ? 'bg-indigo-50 text-indigo-600 font-semibold' : 'text-slate-600' }}">
                    <svg class="h-4 w-4 shrink-0 text-slate-500" fill="none" stroke="currentColor" stroke-width="1.8"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Profil</span>
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="flex w-full items-center gap-2.5 rounded-xl px-2.5 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-red-50 hover:text-red-600">
                        <svg class="h-4 w-4 shrink-0 text-slate-500 hover:text-red-500" fill="none"
                            stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </div>

        {{-- Collapsed Bottom View --}}
        <div x-show="isDesktopCollapsed()" x-cloak class="sidebar-collapsed-only flex flex-col items-center gap-2.5 py-3">
            {{-- User Initial Avatar --}}
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-600 cursor-default"
                title="{{ $user->name }} ({{ $primaryRole }})">
                {{ $initials }}
            </div>

            {{-- Profil Icon --}}
            <a href="{{ route('profile.edit') }}"
                class="flex h-9 w-9 items-center justify-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 {{ request()->routeIs('profile.*') ? 'bg-indigo-50 text-indigo-600' : '' }}"
                title="Profil">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </a>

            {{-- Keluar Icon --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="flex h-9 w-9 items-center justify-center rounded-xl text-slate-500 transition hover:bg-red-50 hover:text-red-600"
                    title="Keluar">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>