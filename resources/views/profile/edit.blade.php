@php
    $user = auth()->user();
    $initials = collect(preg_split('/\s+/', trim($user->name)))->take(2)->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
    $roles = method_exists($user, 'getRoleNames') ? $user->getRoleNames()->implode(', ') : '';
@endphp

<x-app-layout>
    @if(session('status') === 'profile-updated')
        <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 4000)" class="fixed right-4 top-24 z-50">
            <div class="flex items-center gap-3 rounded-2xl border border-green-200 bg-green-50 p-4 shadow-lg">
                <svg class="h-5 w-5 shrink-0 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <p class="text-sm font-semibold text-green-800">Profil berhasil diperbarui!</p>
                <button @click="show = false" class="ml-2 text-green-400 hover:text-green-600">✕</button>
            </div>
        </div>
    @endif

    <div class="space-y-6 px-4 py-6 sm:px-8">

        {{-- Banner judul --}}
        <div class="relative flex items-center gap-4 overflow-hidden rounded-2xl border border-white/70 bg-white/80 p-4 shadow-sm backdrop-blur sm:gap-5 sm:p-6">
            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-500 sm:h-16 sm:w-16">
                <svg class="h-7 w-7 sm:h-8 sm:w-8" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.118a7.5 7.5 0 0115 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.5-1.632z"/></svg>
            </span>
            <div class="min-w-0 flex-1">
                <h1 class="text-xl font-extrabold text-[#0f1e5a] sm:text-2xl">Pengaturan Akun</h1>
                <p class="mt-1 text-sm text-slate-500">Kelola informasi profil akun Anda.</p>
            </div>
            <svg class="pointer-events-none absolute -bottom-2 right-2 hidden h-28 w-28 text-indigo-200/70 sm:block" viewBox="0 0 120 120" fill="currentColor" aria-hidden="true">
                <path d="M60 120C50 80 55 45 80 15c12 35 5 75-20 105z"/>
                <path d="M58 120C35 100 25 70 35 40c25 15 33 50 23 80z" opacity=".7"/>
                <path d="M62 120c20-15 38-20 55-12-12 18-35 24-55 12z" opacity=".6"/>
            </svg>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">

            {{-- Kartu ringkasan akun --}}
            <aside class="h-fit rounded-2xl border border-slate-100 bg-white p-6 text-center shadow-sm lg:sticky lg:top-28">
                <span class="mx-auto flex h-24 w-24 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-indigo-400 text-3xl font-extrabold text-white shadow-lg shadow-indigo-500/30">{{ $initials }}</span>
                <h2 class="mt-4 text-lg font-extrabold text-slate-900">{{ $user->name }}</h2>
                @if($roles)
                    <span class="mt-2 inline-block rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-600">{{ $roles }}</span>
                @endif

                <dl class="mt-6 space-y-3 border-t border-slate-100 pt-5 text-left text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-slate-500">Username</dt>
                        <dd class="truncate font-semibold text-slate-800">{{ $user->username ?? '-' }}</dd>
                    </div>
                    @if($user->created_at)
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Bergabung</dt>
                            <dd class="font-semibold text-slate-800">{{ $user->created_at->isoFormat('D MMM Y') }}</dd>
                        </div>
                    @endif
                </dl>
            </aside>

            {{-- Form --}}
            <div class="space-y-6 lg:col-span-2">
                <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm sm:p-8">
                    @include('profile.partials.update-profile-information-form')
                </div>

            </div>
        </div>
    </div>
</x-app-layout>