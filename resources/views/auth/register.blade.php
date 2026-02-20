<x-guest-layout>
    <x-slot name="title">Register</x-slot>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-indigo-50 to-slate-100">

        {{-- soft background glow --}}
        <div class="pointer-events-none fixed inset-0">
            <div
                class="absolute -top-24 left-1/2 -translate-x-1/2 h-[500px] w-[500px] rounded-full bg-indigo-300/25 blur-3xl">
            </div>
        </div>

        <div class="relative mx-auto flex min-h-screen items-center justify-center px-4 py-10">
            <div class="w-full max-w-md">

                {{-- Badge + Title --}}
                <div class="text-center">
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-white/70 px-4 py-2 text-xs font-semibold tracking-wide text-indigo-700 shadow-sm backdrop-blur">
                        <span class="h-1.5 w-1.5 rounded-full bg-indigo-600"></span>
                        SISTEM MANAJEMEN INTERNAL
                    </div>

                    <h1 class="mt-6 text-4xl font-extrabold tracking-tight text-slate-900">
                        Buat Akun
                    </h1>

                    <p class="mt-3 text-sm text-slate-600">
                        Daftarkan akun untuk mengakses sistem booking ruang rapat.
                    </p>
                </div>

                {{-- Card --}}
                <div
                    class="mt-8 rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-xl shadow-slate-900/5 backdrop-blur sm:p-8">
                    <form method="POST" action="{{ route('register') }}" class="space-y-5">
                        @csrf

                        {{-- Name --}}
                        <div>
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" name="name" type="text" :value="old('name')" required autofocus
                                autocomplete="name" placeholder="Nama lengkap" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white px-4 py-3
                                       focus:border-indigo-400 focus:ring-indigo-200" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        {{-- Email --}}
                        <div>
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" name="email" type="email" :value="old('email')" required
                                autocomplete="username" placeholder="nama@gmail.com" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white px-4 py-3
                                       focus:border-indigo-400 focus:ring-indigo-200" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        {{-- Role / Posisi --}}
                        <div>
                            <x-input-label for="role" value="Role / Posisi" />
                            <select id="role" name="role" required class="mt-2 block w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-slate-900
                                       shadow-sm focus:border-indigo-400 focus:ring-indigo-200">
                                <option value="" disabled {{ old('role') ? '' : 'selected' }}>-- Pilih Role --</option>
                                <option value="PIC" {{ old('role') === 'PIC' ? 'selected' : '' }}>PIC</option>
                                <option value="TU" {{ old('role') === 'TU' ? 'selected' : '' }}>TU</option>
                            </select>

                            <p class="mt-2 text-xs text-slate-500 leading-relaxed">
                                <span class="font-semibold text-slate-700">PIC</span>: Bisa mengajukan booking ruang
                                rapat<br>
                                <span class="font-semibold text-slate-700">TU</span>: Bisa menyetujui/menolak pengajuan
                                booking
                            </p>

                            <x-input-error :messages="$errors->get('role')" class="mt-2" />
                        </div>

                        {{-- Password --}}
                        <div>
                            <x-input-label for="password" :value="__('Password')" />
                            <x-text-input id="password" name="password" type="password" required
                                autocomplete="new-password" placeholder="Buat password" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white px-4 py-3
                                       focus:border-indigo-400 focus:ring-indigo-200" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        {{-- Confirm Password --}}
                        <div>
                            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                            <x-text-input id="password_confirmation" name="password_confirmation" type="password"
                                required autocomplete="new-password" placeholder="Ulangi password" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white px-4 py-3
                                       focus:border-indigo-400 focus:ring-indigo-200" />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center justify-between gap-3">
                            <a href="{{ route('login') }}"
                                class="text-sm font-medium text-slate-600 hover:text-slate-900 underline underline-offset-4">
                                Sudah punya akun?
                            </a>

                            <button type="submit" class="group inline-flex items-center justify-center rounded-2xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white
                                       shadow-lg shadow-indigo-600/20 transition-all duration-200
                                       hover:bg-indigo-700 hover:-translate-y-0.5 hover:shadow-xl">
                                Daftar →
                            </button>
                        </div>
                    </form>
                </div>

                <p class="mt-6 text-center text-xs text-slate-500">
                    © {{ date('Y') }} Kementerian Koordinator Bidang Pangan RI
                </p>
            </div>
        </div>
    </div>
</x-guest-layout>