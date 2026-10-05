<section>
    <header class="flex items-start gap-4">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-500">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.118a7.5 7.5 0 0115 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.5-1.632z"/></svg>
        </span>
        <div>
            <h2 class="text-lg font-extrabold text-slate-900">Informasi Profil</h2>
            <p class="mt-0.5 text-sm text-slate-500">Perbarui nama yang tampil di akun Anda.</p>
        </div>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="mb-2 block text-sm font-semibold text-slate-800">Nama</label>
            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name"
                class="block w-full rounded-xl border-slate-200 bg-white px-4 py-3 text-sm placeholder-slate-400 focus:border-indigo-400 focus:ring-4 focus:ring-indigo-500/10">
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        @if(!empty($user->username))
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-800">Username</label>
                <input type="text" value="{{ $user->username }}" disabled
                    class="block w-full cursor-not-allowed rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500">
                <p class="mt-1.5 text-xs text-slate-400">Username hanya dapat diubah oleh Administrator.</p>
            </div>
        @endif

        <div class="flex items-center gap-4 pt-1">
            <button type="submit" class="rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-600/25 transition hover:bg-indigo-700">Simpan Perubahan</button>

        </div>
    </form>
</section>