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

    $profileActive = request()->routeIs('profile.*');

    // Label menyempit + memudar saat sidebar dalam mode ikon (diatur <style> di bawah)
    $labelBase = 'sb-label min-w-0 overflow-hidden whitespace-nowrap';
@endphp

{{-- ===== Gaya mandiri sidebar (tidak perlu CSS tambahan di layout) ===== --}}
<style>
    .sb-label {
        transition: max-width .2s ease-out, opacity .2s ease-out;
    }

    .sb-chevron {
        transition: transform .3s ease-out;
    }

    /* Mode ikon hanya berlaku di desktop; di mobile (drawer) selalu tampil lengkap */
    @media (min-width: 1024px) {
        html.sb-collapsed .sb-label {
            max-width: 0 !important;
            opacity: 0;
        }

        html.sb-collapsed .sb-item {
            justify-content: center;
            gap: 0;
            padding-left: 0;
            padding-right: 0;
        }

        html.sb-collapsed .sb-user {
            justify-content: center;
            gap: 0;
            padding-left: 0;
            padding-right: 0;
        }

        html.sb-collapsed .sb-role {
            padding-left: 0;
            padding-right: 0;
        }

        html.sb-collapsed .sb-chevron {
            transform: rotate(180deg);
        }

        html.sb-collapsed .sb-badge {
            position: absolute;
            top: 2px;
            right: 2px;
            margin: 0;
            height: 16px;
            min-width: 16px;
            padding: 0 4px;
            font-size: 10px;
        }
    }

    @media (prefers-reduced-motion: reduce) {

        .sb-label,
        .sb-chevron {
            transition: none;
        }
    }
</style>

{{-- Pasang status collapse sebelum sidebar digambar agar tidak berkedip saat pindah halaman --}}
<script>
    try { if (localStorage.getItem('sidebarCollapsed') === 'true') document.documentElement.classList.add('sb-collapsed'); } catch (e) { }
</script>

{{--
Memakai state dari layout: sidebarCollapsed, sidebarMobile, toggleSidebar().
Di dalam method x-data, state layout diakses lewat `this.`
--}}
<div x-data="{
        isDesktop: window.matchMedia('(min-width: 1024px)').matches,
        get compact() { return !!this.sidebarCollapsed && this.isDesktop },
        init() {
            const root = document.documentElement;
            const apply = () => root.classList.toggle('sb-collapsed', !!this.sidebarCollapsed);
            apply();
            this.$watch('sidebarCollapsed', apply);
            const mq = window.matchMedia('(min-width: 1024px)');
            mq.addEventListener('change', e => this.isDesktop = e.matches);
        },
        toggle() {
            if (typeof this.toggleSidebar === 'function') this.toggleSidebar();
            else this.sidebarCollapsed = !this.sidebarCollapsed;
            try { localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed) } catch (e) { }
        },
        closeOnMobile() { if (!this.isDesktop) this.sidebarMobile = false },
    }" @keydown.escape.window="closeOnMobile()"
    class="relative flex h-full flex-col justify-between overflow-visible select-none bg-white">

    {{-- Toggle Collapse/Expand (kapsul di tepi kanan, desktop saja) --}}
    <button type="button" @click="toggle()"
        class="absolute -right-3 top-1/2 z-50 hidden h-12 w-6 -translate-y-1/2 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-400 shadow-sm transition duration-200 hover:bg-slate-50 hover:text-slate-600 hover:shadow focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 lg:flex"
        :title="compact ? 'Perlebar sidebar' : 'Ciutkan sidebar'"
        :aria-label="compact ? 'Perlebar sidebar' : 'Ciutkan sidebar'" :aria-expanded="(!compact).toString()">
        <svg class="sb-chevron h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
            aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
    </button>

    {{-- Top Section: hanya tombol close di mobile --}}
    <div class="flex h-12 shrink-0 items-center justify-end border-b border-slate-100 px-3 lg:hidden">
        <button type="button" @click="sidebarMobile = false" aria-label="Tutup menu"
            class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
            <svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    {{-- Middle Section: Navigation Links --}}
    <nav class="sidebar-nav flex-1 space-y-1 overflow-y-auto overflow-x-hidden px-2.5 py-3" aria-label="Menu utama">
        @foreach($links as $l)
            <a href="{{ $l['url'] }}" @if($l['active']) aria-current="page" @endif @click="closeOnMobile()"
                :title="compact ? @js($l['name']) : null"
                class="nav-item-link sb-item group relative flex h-10 w-full items-center gap-3 rounded-lg px-3 text-[13px] transition-colors duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 {{ $l['active'] ? 'bg-indigo-50 font-semibold text-indigo-600' : 'font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">

                {{-- Indikator aktif --}}
                @if($l['active'])
                    <span class="absolute -left-2.5 bottom-2.5 top-2.5 w-[3px] rounded-r-full bg-indigo-600"
                        aria-hidden="true"></span>
                @endif

                <svg class="h-[18px] w-[18px] shrink-0 transition-colors {{ $l['active'] ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-700' }}"
                    fill="none" stroke="currentColor" stroke-width="{{ $l['active'] ? '2' : '1.75' }}"
                    stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                    {!! $l['icon'] !!}
                </svg>

                <span class="{{ $labelBase }} truncate" style="max-width:170px">{{ $l['name'] }}</span>

                @if(($l['badge'] ?? 0) > 0)
                    <span
                        class="sb-badge ml-auto inline-flex h-[18px] min-w-[18px] items-center justify-center rounded-full bg-red-500 px-1.5 text-[11px] font-bold text-white">
                        {{ $l['badge'] }}
                    </span>
                @endif
            </a>
        @endforeach
    </nav>

    {{-- Bottom Section: User Info & Actions --}}
    <div class="shrink-0 border-t border-slate-100 p-2.5">

        {{-- User --}}
        <div class="sb-user flex items-center gap-2.5 rounded-lg px-2 py-1.5"
            :title="compact ? @js($user->name . ' (' . $primaryRole . ')') : null">
            <div
                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-[11px] font-bold text-indigo-600">
                {{ $initials }}
            </div>
            <div class="{{ $labelBase }} flex-1" style="max-width:150px">
                <div class="truncate text-xs font-bold leading-tight text-slate-900">{{ $user->name }}</div>
                <div class="mt-0.5 truncate text-[10.5px] leading-tight text-slate-400">{{ $user->email }}</div>
            </div>
            <span
                class="{{ $labelBase }} sb-role shrink-0 rounded px-1.5 py-0.5 text-[10px] font-semibold {{ $roleBadgeClass }}"
                style="max-width:60px">{{ $primaryRole }}</span>
        </div>

        {{-- Actions --}}
        <div class="mt-1 space-y-0.5">
            <a href="{{ route('profile.edit') }}" @click="closeOnMobile()" :title="compact ? 'Profil' : null"
                class="sb-item group flex h-9 items-center gap-3 rounded-lg px-3 text-[13px] font-medium transition-colors hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 {{ $profileActive ? 'bg-indigo-50 font-semibold text-indigo-600' : 'text-slate-600' }}">
                <svg class="h-[18px] w-[18px] shrink-0 {{ $profileActive ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-700' }}"
                    fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                    viewBox="0 0 24 24" aria-hidden="true">
                    {!! $ic['profile'] !!}
                </svg>
                <span class="{{ $labelBase }}" style="max-width:150px">Profil</span>
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" :title="compact ? 'Keluar' : null"
                    class="sb-item group flex h-9 w-full items-center gap-3 rounded-lg px-3 text-[13px] font-medium text-slate-600 transition-colors hover:bg-red-50 hover:text-red-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500">
                    <svg class="h-[18px] w-[18px] shrink-0 text-slate-400 transition-colors group-hover:text-red-500"
                        fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"
                        stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                        {!! $ic['logout'] !!}
                    </svg>
                    <span class="{{ $labelBase }}" style="max-width:150px">Keluar</span>
                </button>
            </form>
        </div>
    </div>
</div>