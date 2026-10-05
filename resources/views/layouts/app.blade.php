<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Rupat Kemenko Pangan') }}</title>

    <link rel="icon" type="image/png" href="/favicon.png">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        // Synchronous — sets sidebar width BEFORE any paint, zero flash
        (function() {
            var c = localStorage.getItem('sidebar_collapsed') === 'true';
            var w = (c && window.innerWidth >= 1024) ? '5rem' : '18rem';
            document.documentElement.style.setProperty('--sw', w);
            if (c) document.documentElement.classList.add('sidebar-collapsed');
        })();
    </script>
    <style>
        [x-cloak] { display: none !important; }

        /* Sidebar width driven by --sw custom property (set inline, instant) */
        @media (min-width: 1024px) {
            aside.sidebar-container {
                width: var(--sw, 18rem) !important;
                transition: none !important;
            }
            .main-content-wrapper {
                padding-left: var(--sw, 18rem) !important;
                transition: none !important;
            }

            /* --- Visibility toggles --- */
            html.sidebar-collapsed .sidebar-expanded-only {
                display: none !important;
            }
            html:not(.sidebar-collapsed) .sidebar-collapsed-only {
                display: none !important;
            }

            /* --- Collapsed: logo section --- */
            html.sidebar-collapsed .sidebar-logo-section {
                justify-content: center !important;
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }

            /* --- Collapsed: nav section --- */
            html.sidebar-collapsed .sidebar-nav {
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
            }

            /* --- Collapsed: nav item links --- */
            html.sidebar-collapsed .nav-item-link {
                width: 3rem !important;
                height: 3rem !important;
                padding: 0 !important;
                justify-content: center !important;
            }
        }
    </style>
    @stack('styles')
</head>

@php
    $u = auth()->user();
    $initials = collect(preg_split('/\s+/', trim($u->name)))->take(2)->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
    $hasPending = $u->hasRole('TU') && $u->room_id
        && \App\Models\Booking::where('status', 'PENDING')->where('room_id', $u->room_id)->exists();
@endphp

<body class="font-sans antialiased text-slate-900 bg-gradient-to-br from-slate-50 via-indigo-50/40 to-slate-50 min-h-screen"
    x-data="{
        sidebarMobile: false,
        sidebarCollapsed: localStorage.getItem('sidebar_collapsed') === 'true',
        toggleSidebar() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
            localStorage.setItem('sidebar_collapsed', this.sidebarCollapsed);
            var w = this.sidebarCollapsed ? '5rem' : '18rem';
            document.documentElement.style.setProperty('--sw', w);
            if (this.sidebarCollapsed) {
                document.documentElement.classList.add('sidebar-collapsed');
            } else {
                document.documentElement.classList.remove('sidebar-collapsed');
            }
        }
    }">

    {{-- Overlay mobile --}}
    <div x-show="sidebarMobile" x-cloak @click="sidebarMobile = false" class="fixed inset-0 z-40 bg-slate-900/40 lg:hidden transition-opacity"></div>

    {{-- Sidebar --}}
    <aside
        class="sidebar-container fixed inset-y-0 left-0 z-50 border-r border-slate-200/80 bg-white -translate-x-full lg:translate-x-0"
        :class="sidebarMobile ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
        @include('layouts.navigation')
    </aside>

    <div class="main-content-wrapper min-h-screen">

        {{-- Topbar --}}
        <header class="sticky top-0 z-30 flex h-20 items-center gap-4 border-b border-slate-100 bg-white/80 px-4 backdrop-blur sm:px-8">
            <button @click="sidebarMobile = true" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

            @if(request()->routeIs('dashboard'))
                <div class="relative hidden max-w-xl flex-1 sm:block">
                    <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
                    <input id="global-search" type="search" placeholder="Cari jadwal rapat, ruangan, atau kegiatan..."
                        class="w-full rounded-2xl border-0 bg-slate-100/70 py-3 pl-12 pr-4 text-sm placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-indigo-200">
                </div>
            @endif

            <div class="ml-auto flex items-center gap-5">
                {{-- Notifikasi --}}
                <a href="{{ $u->hasRole('TU') ? route('approvals.index') : '#' }}" class="relative text-slate-500 hover:text-slate-700">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9a6 6 0 10-12 0v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                    @if($hasPending)
                        <span class="absolute -right-0.5 -top-0.5 h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-white"></span>
                    @endif
                </a>

                {{-- Profil --}}
                <div class="relative border-l border-slate-200 pl-5" x-data="{ menu: false }" @click.outside="menu = false">
                    <button @click="menu = !menu" class="flex items-center gap-3">
                        <span class="flex h-11 w-11 items-center justify-center rounded-full bg-indigo-500 text-sm font-bold text-white">{{ $initials }}</span>
                        <span class="hidden text-left sm:block">
                            <span class="block text-[11px] text-slate-500">Selamat datang,</span>
                            <span class="block text-sm font-bold text-slate-900">{{ $u->name }}</span>
                        </span>
                        <svg class="hidden h-4 w-4 text-slate-400 sm:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div x-show="menu" x-cloak x-transition
                        class="absolute right-0 mt-3 w-48 overflow-hidden rounded-xl border border-slate-100 bg-white py-1 shadow-xl">
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">Profil</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full px-4 py-2.5 text-left text-sm text-red-600 hover:bg-red-50">Keluar</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{-- Heading halaman lain (calendar, approvals, dll.) --}}
        @isset($header)
            <div class="px-4 pt-6 sm:px-8">
                <div class="rounded-2xl border border-slate-100 bg-white px-6 py-4 shadow-sm">
                    {{ $header }}
                </div>
            </div>
        @endisset

        <main>
            {{ $slot }}
        </main>
    </div>

    @stack('scripts')
</body>

</html>