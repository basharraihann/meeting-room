<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Rupat Kemenko Pangan') }}</title>

    <link rel="icon" type="image/png" href="/favicon.png">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap"
        rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        // Synchronous — sets sidebar width BEFORE any paint, zero flash
        (function () {
            var c = localStorage.getItem('sidebar_collapsed') === 'true';
            var w = (c && window.innerWidth >= 1024) ? '4.5rem' : '15rem';
            document.documentElement.style.setProperty('--sw', w);
            if (c) document.documentElement.classList.add('sidebar-collapsed');
        })();
    </script>
    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Mobile sidebar width: constrained and narrowed */
        aside.sidebar-container {
            width: 15rem !important;
            max-width: 80vw !important;
        }

        /* Sidebar width driven by --sw custom property (set inline, instant) */
        @media (min-width: 1024px) {
            aside.sidebar-container {
                width: var(--sw, 15rem) !important;
                max-width: none !important;
                transition: none !important;
            }

            .main-content-wrapper {
                padding-left: var(--sw, 15rem) !important;
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
                padding-left: 0.25rem !important;
                padding-right: 0.25rem !important;
            }

            /* --- Collapsed: nav section --- */
            html.sidebar-collapsed .sidebar-nav {
                padding-left: 0.25rem !important;
                padding-right: 0.25rem !important;
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
            }

            /* --- Collapsed: nav item links --- */
            html.sidebar-collapsed .nav-item-link {
                width: 2.75rem !important;
                height: 2.75rem !important;
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
    $homeUrl = $u->hasRole('PIC')
        ? route('dashboard')
        : ($u->hasRole('Admin') ? route('admin.users.index') : route('calendar'));
@endphp

<body
    class="font-sans antialiased text-slate-900 bg-gradient-to-br from-slate-50 via-indigo-50/40 to-slate-50 min-h-screen"
    x-data="{
        sidebarMobile: false,
        sidebarCollapsed: localStorage.getItem('sidebar_collapsed') === 'true',
        toggleSidebar() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
            localStorage.setItem('sidebar_collapsed', this.sidebarCollapsed);
            var w = this.sidebarCollapsed ? '4.5rem' : '15rem';
            document.documentElement.style.setProperty('--sw', w);
            if (this.sidebarCollapsed) {
                document.documentElement.classList.add('sidebar-collapsed');
            } else {
                document.documentElement.classList.remove('sidebar-collapsed');
            }
        }
    }">

    {{-- Overlay mobile --}}
    <div x-show="sidebarMobile" x-cloak @click="sidebarMobile = false"
        class="fixed inset-0 z-40 bg-slate-900/40 lg:hidden transition-opacity"></div>

    {{-- Sidebar --}}
    <aside
        class="sidebar-container fixed inset-y-0 left-0 z-50 border-r border-slate-200/80 bg-white -translate-x-full lg:translate-x-0"
        :class="sidebarMobile ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
        @include('layouts.navigation')
    </aside>

    {{-- pb-24: beri ruang untuk menu bawah di HP --}}
    <div class="main-content-wrapper min-h-screen pb-24 lg:pb-0">

        {{-- Topbar --}}
        <header
            class="sticky top-0 z-30 flex h-20 items-center gap-4 border-b border-slate-100 bg-white/80 px-4 backdrop-blur sm:px-8">
            {{-- Hamburger disembunyikan (diganti menu bawah). Ganti "hidden" jadi "lg:hidden" untuk memunculkan lagi
            --}}
            <button @click="sidebarMobile = true" class="hidden rounded-lg p-2 text-slate-500 hover:bg-slate-100">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            {{-- Logo instansi --}}
            <a href="{{ $homeUrl }}" class="flex min-w-0 shrink items-center">
                <img src="{{ asset('images/logoheader.png') }}"
                    alt="Kementerian Koordinator Bidang Pangan Republik Indonesia"
                    class="h-11 w-auto max-w-full object-contain sm:h-9">
            </a>

            <div class="ml-auto flex items-center gap-3">
                {{-- Profil --}}
                <div class="relative" x-data="{ menu: false }" @click.outside="menu = false">
                    <button @click="menu = !menu" class="flex items-center gap-2">
                        <span
                            class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-500 text-xs font-bold text-white">{{ $initials }}</span>
                        <span class="hidden text-left sm:block">
                            <span class="block text-[10px] leading-tight text-slate-500">Selamat datang,</span>
                            <span class="block text-xs font-bold leading-tight text-slate-900">{{ $u->name }}</span>
                        </span>
                        <svg class="hidden h-3.5 w-3.5 text-slate-400 sm:block" fill="none" stroke="currentColor"
                            stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    {{-- Dropdown profil --}}
                    <div x-show="menu" x-cloak x-transition
                        class="absolute right-0 top-full mt-2 w-48 rounded-xl border border-slate-100 bg-white py-1 shadow-lg">
                        <div class="border-b border-slate-100 px-4 py-2 sm:hidden">
                            <p class="text-xs font-bold text-slate-900">{{ $u->name }}</p>
                        </div>

                        <a href="{{ route('profile.edit') }}"
                            class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">
                            Profil
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="block w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50">
                                Keluar
                            </button>
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

    {{-- Menu bawah (HP) --}}
    @include('layouts.bottom-nav')

    @stack('scripts')
</body>

</html>