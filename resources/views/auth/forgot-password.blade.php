<x-guest-layout>
    <x-slot name="title">Lupa Password</x-slot>
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
                        Lupa Password
                    </h1>

                    <p class="mt-3 text-sm leading-relaxed text-slate-600">
                        Masukkan email akun kamu. Kami akan kirim tautan untuk reset password.
                    </p>
                </div>

                {{-- Card --}}
                <div
                    class="mt-8 rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-xl shadow-slate-900/5 backdrop-blur sm:p-8">
                    <x-auth-session-status class="mb-4" :status="session('status')" />

                    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                        @csrf

                        {{-- Email --}}
                        <div>
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" name="email" type="email" :value="old('email')" required autofocus
                                placeholder="nama@kemenkopangan.go.id" class="mt-2 block w-full rounded-2xl border-slate-200 bg-white px-4 py-3
                                       focus:border-indigo-400 focus:ring-indigo-200" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        {{-- Button --}}
                        <button type="submit" class="group w-full rounded-2xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white
                                   shadow-lg shadow-indigo-600/20 transition-all duration-200
                                   hover:bg-indigo-700 hover:-translate-y-0.5 hover:shadow-xl">
                            Kirim Link Reset →
                        </button>

                        {{-- Back to login --}}
                        <div class="text-center">
                            <a href="{{ route('login') }}"
                                class="text-sm font-medium text-slate-600 hover:text-slate-900 underline underline-offset-4">
                                Kembali ke login
                            </a>
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