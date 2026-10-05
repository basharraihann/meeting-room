<section x-data="{ confirming: {{ $errors->userDeletion->isNotEmpty() ? 'true' : 'false' }} }" @keydown.escape.window="confirming = false">
    <header class="flex items-start gap-4">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-500">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
        </span>
        <div>
            <h2 class="text-lg font-extrabold text-slate-900">Hapus Akun</h2>
            <p class="mt-0.5 text-sm text-slate-500">Setelah akun dihapus, seluruh data dan sumber daya terkait akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.</p>
        </div>
    </header>

    <button type="button" @click="confirming = true"
        class="mt-6 rounded-xl border border-red-200 bg-red-50 px-6 py-3 text-sm font-bold text-red-600 transition hover:bg-red-100">Hapus Akun</button>

    {{-- Modal konfirmasi --}}
    <div x-show="confirming" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/50" @click="confirming = false"></div>

        <form method="post" action="{{ route('profile.destroy') }}" x-transition
            class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
            @csrf
            @method('delete')

            <h3 class="text-lg font-extrabold text-slate-900">Yakin ingin menghapus akun?</h3>
            <p class="mt-2 text-sm text-slate-500">Masukkan kata sandi Anda untuk mengonfirmasi penghapusan akun secara permanen.</p>

            <div class="mt-5">
                <label for="delete_password" class="sr-only">Kata Sandi</label>
                <input id="delete_password" name="password" type="password" placeholder="Kata sandi"
                    class="block w-full rounded-xl border-slate-200 px-4 py-3 text-sm focus:border-red-300 focus:ring-4 focus:ring-red-500/10">
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="confirming = false" class="rounded-xl bg-slate-100 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-200">Batal</button>
                <button type="submit" class="rounded-xl bg-red-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-red-700">Ya, Hapus Akun</button>
            </div>
        </form>
    </div>
</section>