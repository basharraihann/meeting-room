<section>
    <header class="flex items-start gap-4">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-500">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
        </span>
        <div>
            <h2 class="text-lg font-extrabold text-slate-900">Ubah Kata Sandi</h2>
            <p class="mt-0.5 text-sm text-slate-500">Gunakan kata sandi yang panjang dan acak agar akun tetap aman.</p>
        </div>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('put')

        @foreach([
            ['update_password_current_password', 'current_password', 'Kata Sandi Saat Ini', 'current-password'],
            ['update_password_password', 'password', 'Kata Sandi Baru', 'new-password'],
            ['update_password_password_confirmation', 'password_confirmation', 'Konfirmasi Kata Sandi Baru', 'new-password'],
        ] as [$id, $name, $label, $auto])
            <div x-data="{ show: false }">
                <label for="{{ $id }}" class="mb-2 block text-sm font-semibold text-slate-800">{{ $label }}</label>
                <div class="relative">
                    <input id="{{ $id }}" name="{{ $name }}" :type="show ? 'text' : 'password'" autocomplete="{{ $auto }}"
                        class="block w-full rounded-xl border-slate-200 bg-white py-3 pl-4 pr-12 text-sm focus:border-indigo-400 focus:ring-4 focus:ring-indigo-500/10">
                    <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 hover:text-indigo-500" tabindex="-1">
                        <svg x-show="!show" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <svg x-show="show" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.52 10.52 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->updatePassword->get($name)" class="mt-2" />
            </div>
        @endforeach

        <div class="flex items-center gap-4 pt-1">
            <button type="submit" class="rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-600/25 transition hover:bg-indigo-700">Perbarui Kata Sandi</button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)"
                    class="inline-flex items-center gap-1.5 text-sm font-semibold text-emerald-600">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Tersimpan
                </p>
            @endif
        </div>
    </form>
</section>