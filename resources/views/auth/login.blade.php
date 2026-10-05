<x-guest-layout>
    <x-slot name="title">Login</x-slot>

    <div class="relative min-h-screen overflow-hidden bg-gradient-to-br from-slate-50 via-indigo-50/60 to-white">

        {{-- Dekorasi latar --}}
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -top-40 -right-40 h-[520px] w-[520px] rounded-full bg-indigo-200/40 blur-3xl"></div>
            <div class="absolute -bottom-40 -left-32 h-[480px] w-[480px] rounded-full bg-sky-200/40 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 h-40 w-full bg-gradient-to-t from-emerald-100/40 to-transparent"></div>
        </div>

        {{-- Header --}}
        <header class="relative flex items-center gap-4 px-6 py-6 sm:px-12">
            <img src="{{ asset('images/logoheader.png') }}" alt="Logo Kemenko Pangan" class="h-12 w-auto">
            <div class="hidden sm:block border-l border-slate-200 pl-4">
                <div class="text-sm font-bold text-slate-900">Rupat Kemenko Pangan</div>
                <div class="text-xs text-slate-500">Kementerian Koordinator Bidang Pangan</div>
            </div>
        </header>

        <div class="relative mx-auto grid max-w-6xl items-center gap-10 px-6 pb-16 pt-4 sm:px-12 lg:min-h-[calc(100vh-180px)] lg:grid-cols-2">

            {{-- Kiri: sambutan --}}
            <div class="hidden lg:block">
                <span class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3.5 py-1.5 text-xs font-semibold text-indigo-600">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l8 3v6c0 5-3.4 9.4-8 11-4.6-1.6-8-6-8-11V5l8-3z"/></svg>
                    Sistem Informasi
                </span>
                <h1 class="mt-5 text-4xl font-extrabold leading-tight text-slate-900">
                    Selamat Datang di<br><span class="text-indigo-600">Rupat Kemenko Pangan</span>
                </h1>
                <p class="mt-4 max-w-md text-sm leading-relaxed text-slate-500">
                    Platform digital untuk mempermudah koordinasi, monitoring, dan pengelolaan program kerja
                    Kementerian Koordinator Bidang Pangan.
                </p>

                <div class="mt-8 flex gap-6">
                    @foreach([
                        ['Mudah', 'Akses cepat dan terintegrasi', 'M8 7V3m8 4V3M5 11h14M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z'],
                        ['Aman', 'Data terlindungi dengan baik', 'M12 3l8 3v6c0 5-3.4 8.4-8 10-4.6-1.6-8-5-8-10V6l8-3z'],
                        ['Efisien', 'Mendukung kinerja terbaik', 'M13 2L4 14h6l-1 8 9-12h-6l1-8z'],
                    ] as [$t, $d, $icon])
                        <div class="flex items-start gap-2.5">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                            </span>
                            <div>
                                <div class="text-sm font-bold text-slate-900">{{ $t }}</div>
                                <div class="text-[11px] leading-snug text-slate-500">{{ $d }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Kanan: kartu login --}}
            <div class="w-full max-w-md justify-self-center lg:justify-self-end">
                <div class="rounded-3xl bg-white p-8 shadow-2xl shadow-indigo-900/10 sm:p-10">
                    <img src="{{ asset('images/logoheader.png') }}" alt="Logo" class="mb-6 h-12 w-auto">

                    <h2 class="text-3xl font-extrabold text-slate-900">Masuk</h2>
                    <p class="mt-2 text-sm text-slate-500">Silakan login untuk mengakses platform booking ruang rapat.</p>

                    <x-auth-session-status class="mt-4" :status="session('status')" />

                    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
                        @csrf

                        <div>
                            <label for="username" class="mb-1.5 block text-sm font-bold text-slate-800">Username</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.118a7.5 7.5 0 0115 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.5-1.632z"/></svg>
                                </span>
                                <input id="username" name="username" type="text" value="{{ old('username') }}" required autofocus
                                    autocomplete="username" placeholder="Masukkan username"
                                    class="block w-full rounded-2xl border-slate-200 bg-white py-3 pl-12 pr-4 text-sm placeholder-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">
                            </div>
                            <x-input-error :messages="$errors->get('username')" class="mt-1.5" />
                        </div>

                        <div x-data="{ show: false }">
                            <label for="password" class="mb-1.5 block text-sm font-bold text-slate-800">Kata Sandi</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                                </span>
                                <input id="password" name="password" :type="show ? 'text' : 'password'" required
                                    autocomplete="current-password" placeholder="Masukkan kata sandi"
                                    class="block w-full rounded-2xl border-slate-200 bg-white py-3 pl-12 pr-12 text-sm placeholder-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">
                                <button type="button" @click="show = !show"
                                    class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 hover:text-slate-600">
                                    <svg x-show="!show" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <svg x-show="show" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.52 10.52 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                                </button>
                            </div>
                            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
                        </div>

                        <div class="flex items-center justify-between">
                            <label for="remember_me" class="inline-flex cursor-pointer items-center gap-2">
                                <input id="remember_me" type="checkbox" name="remember"
                                    class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500/20">
                                <span class="text-sm text-slate-600">Ingat saya</span>
                            </label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">Lupa kata sandi?</a>
                            @endif
                        </div>

                        <button type="submit"
                            class="group flex w-full items-center justify-center gap-2 rounded-full bg-indigo-600 px-4 py-3.5 text-sm font-bold text-white shadow-lg shadow-indigo-600/30 transition hover:-translate-y-0.5 hover:bg-indigo-700 active:translate-y-0">
                            Masuk ke Akun
                            <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        </button>
                    </form>

                    <p class="mt-6 text-center text-xs text-slate-400">Butuh bantuan akses? Hubungi Biro MKDI.</p>
                </div>
            </div>
        </div>

        <p class="relative pb-6 text-center text-xs text-slate-400">
            Kementerian Koordinator Bidang Pangan · Republik Indonesia · &copy; {{ date('Y') }}
        </p>
    </div>
</x-guest-layout>