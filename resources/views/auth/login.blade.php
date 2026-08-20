<x-guest-layout>
    <x-slot name="title">Login - Sistem Manajemen Internal</x-slot>

    <div
        class="relative min-h-screen bg-gradient-to-br from-slate-50 via-indigo-50/50 to-slate-100 flex items-center justify-center p-4 sm:p-6 overflow-hidden">

        {{-- Ambient Glow Elements --}}
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div
                class="absolute -top-32 left-1/2 -translate-x-1/2 h-[550px] w-[550px] rounded-full bg-indigo-300/30 blur-3xl">
            </div>
            <div
                class="absolute -bottom-32 left-1/2 -translate-x-1/2 h-[400px] w-[400px] rounded-full bg-slate-200/40 blur-3xl">
            </div>
        </div>

        {{-- Container Main --}}
        <div class="relative w-full max-w-md my-auto">

            {{-- Main Card --}}
            <div
                class="rounded-3xl border border-white/60 bg-white/80 p-6 sm:p-10 shadow-2xl shadow-indigo-900/5 backdrop-blur-xl">

                {{-- Header Inside Card --}}
                <div class="text-center mb-8">
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-indigo-200/80 bg-indigo-50/50 px-3.5 py-1.5 text-xs font-semibold tracking-wide text-indigo-700 backdrop-blur-sm">
                        <span class="relative flex h-2 w-2">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-600"></span>
                        </span>
                        SISTEM MANAJEMEN INTERNAL
                    </div>

                    <h1 class="mt-4 text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                        Masuk ke Sistem
                    </h1>

                    <p class="mt-2 text-xs sm:text-sm text-slate-500 leading-relaxed">
                        Silakan login untuk mengakses platform booking ruang rapat.
                    </p>
                </div>

                <x-auth-session-status class="mb-6" :status="session('status')" />

                {{-- Form Otentikasi --}}
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    {{-- Field Username --}}
                    <div>
                        <x-input-label for="username" value="Username"
                            class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5" />
                        <div class="relative">
                            <div
                                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                            </div>
                            <x-text-input id="username" name="username" type="text" :value="old('username')" required
                                autofocus autocomplete="username" placeholder="Masukkan username"
                                class="block w-full rounded-2xl border-slate-200 bg-white/70 pl-11 pr-4 py-3 text-sm text-slate-800 placeholder-slate-400 transition-all duration-200 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10" />
                        </div>
                        <x-input-error :messages="$errors->get('username')" class="mt-1.5" />
                    </div>

                    {{-- Field Password (With Show/Hide Alpine.js) --}}
                    <div x-data="{ showPassword: false }">
                        <div class="flex items-center justify-between mb-1.5">
                            <x-input-label for="password" :value="__('Password')"
                                class="text-xs font-bold text-slate-700 uppercase tracking-wider" />
                        </div>
                        <div class="relative">
                            <div
                                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                </svg>
                            </div>

                            <x-text-input id="password" name="password" ::type="showPassword ? 'text' : 'password'"
                                required autocomplete="current-password" placeholder="••••••••"
                                class="block w-full rounded-2xl border-slate-200 bg-white/70 pl-11 pr-11 py-3 text-sm text-slate-800 placeholder-slate-400 transition-all duration-200 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10" />

                            {{-- Toggle Password Button --}}
                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600 focus:outline-none">
                                <svg x-show="!showPassword" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <svg x-show="showPassword" x-cloak class="h-5 w-5" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.52 10.52 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
                    </div>

                    {{-- Checkbox Remember Me --}}
                    <div class="flex items-center justify-between pt-1">
                        <label for="remember_me" class="inline-flex items-center gap-2.5 cursor-pointer group">
                            <input id="remember_me" type="checkbox" name="remember"
                                class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500/20 focus:ring-offset-0 transition cursor-pointer">
                            <span
                                class="text-xs sm:text-sm text-slate-600 group-hover:text-slate-900 transition-colors">Ingat
                                saya di perangkat ini</span>
                        </label>
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit"
                        class="group relative flex w-full justify-center items-center gap-2 rounded-2xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-600/25 transition-all duration-200 hover:bg-indigo-700 hover:shadow-indigo-600/35 hover:-translate-y-0.5 active:translate-y-0 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <span>Masuk ke Akun</span>
                        <svg class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </button>
                </form>
            </div>

            {{-- Footer Copyright --}}
            <p class="mt-8 text-center text-xs text-slate-500">
                &copy; {{ date('Y') }} Kementerian Koordinator Bidang Pangan RI. All rights reserved.
            </p>
        </div>

    </div>
</x-guest-layout>