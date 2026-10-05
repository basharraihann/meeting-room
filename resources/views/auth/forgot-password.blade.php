<x-guest-layout>
    <x-slot name="title">Lupa Password</x-slot>

    <div class="relative flex min-h-screen flex-col overflow-hidden bg-gradient-to-br from-slate-50 via-indigo-50/60 to-white">

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

        <div class="relative mx-auto grid w-full max-w-6xl flex-1 items-center gap-10 px-6 pb-10 pt-4 sm:px-12 lg:grid-cols-2">

            {{-- Kiri: penjelasan --}}
            <div class="hidden lg:block">
                <span class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3.5 py-1.5 text-xs font-semibold text-indigo-600">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                    Lupa Password
                </span>
                <h1 class="mt-5 text-4xl font-extrabold leading-tight text-slate-900">
                    Tenang, Kami<br><span class="text-indigo-600">Bisa Membantu</span>
                </h1>
                <p class="mt-4 max-w-md text-sm leading-relaxed text-slate-500">
                    Masukkan alamat email yang terdaftar pada akun Anda. Kami akan mengirimkan tautan untuk mereset
                    password.
                </p>

                <div class="mt-8 grid max-w-xl grid-cols-3 gap-6">
                    @foreach([
                        ['Aman', 'Proses cepat dan terpercaya', 'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75'],
                        ['Mudah', 'Hanya dengan email terdaftar', 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z'],
                        ['Efisien', 'Kembali akses dalam hitungan menit', 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z'],
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

            {{-- Kanan: kartu lupa password --}}
            <div class="w-full max-w-md justify-self-center lg:justify-self-end">
                <div class="rounded-3xl bg-white p-8 shadow-2xl shadow-indigo-900/10 sm:p-10">
                    <img src="{{ asset('images/logoheader.png') }}" alt="Logo" class="mb-6 h-12 w-auto">

                    <h2 class="text-3xl font-extrabold text-slate-900">Lupa Password</h2>
                    <p class="mt-2 text-sm text-slate-500">Masukkan email akun Anda, dan kami akan mengirimkan tautan untuk mereset password.</p>

                    <x-auth-session-status class="mt-4" :status="session('status')" />

                    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-5">
                        @csrf

                        <div>
                            <label for="email" class="mb-1.5 block text-sm font-bold text-slate-800">Email</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                                </span>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                                    autocomplete="email" placeholder="nama@gmail.com"
                                    class="block w-full rounded-2xl border-slate-200 bg-white py-3 pl-12 pr-4 text-sm placeholder-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10">
                            </div>
                            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
                        </div>

                        <button type="submit"
                            class="group flex w-full items-center justify-center gap-2 rounded-full bg-indigo-600 px-4 py-3.5 text-sm font-bold text-white shadow-lg shadow-indigo-600/30 transition hover:-translate-y-0.5 hover:bg-indigo-700 active:translate-y-0">
                            Kirim Link Reset
                            <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        </button>

                        <div class="text-center">
                            <a href="{{ route('login') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">Kembali ke login</a>
                        </div>
                    </form>

                    <p class="mt-6 text-center text-xs text-slate-400">Butuh bantuan akses? Hubungi Biro MKDI.</p>
                </div>
            </div>
        </div>

        <p class="relative shrink-0 px-6 pb-6 text-center text-xs text-slate-400">
            Kementerian Koordinator Bidang Pangan · Republik Indonesia · &copy; {{ date('Y') }}
        </p>
    </div>
</x-guest-layout>