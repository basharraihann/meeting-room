{{-- resources/views/layouts/bottom-nav.blade.php — menu bawah untuk HP (< 1024px) --}}
@php
    $u = auth()->user();

    $ic = [
        'home' => '<rect width="7" height="9" x="3" y="3" rx="1.5"/><rect width="7" height="5" x="14" y="3" rx="1.5"/><rect width="7" height="9" x="14" y="12" rx="1.5"/><rect width="7" height="5" x="3" y="16" rx="1.5"/>',
        'schedule' => '<path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2.5"/><path d="M3 10h18"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/><path d="M16 18h.01"/>',
        'agenda' => '<rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/>',
        'history' => '<path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/>',
        'approval' => '<path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"/><path d="m9 12 2 2 4-4"/>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'profile' => '<circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/>',
        'more' => '<circle cx="5" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="19" cy="12" r="1.5"/>',
        'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>',
    ];

    $profileActive = request()->routeIs('profile.*');
    $items = [];
    if ($u->hasRole('PIC')) {
        $items[] = ['Beranda', route('dashboard'), request()->routeIs('dashboard'), 'home'];
        $items[] = ['Jadwal', route('calendar'), request()->routeIs('calendar'), 'schedule'];
        $items[] = ['Agenda', route('agenda'), request()->routeIs('agenda'), 'agenda'];
        $items[] = ['Riwayat', route('my_bookings.index'), request()->routeIs('my_bookings.*'), 'history'];
    }

    $pending = 0;
    if ($u->hasRole('TU')) {
        $pending = $u->room_id
            ? \App\Models\Booking::where('status', 'PENDING')->where('room_id', $u->room_id)->count()
            : 0;
        $items[] = ['Jadwal', route('calendar'), request()->routeIs('calendar'), 'schedule'];
        $items[] = ['Approvals', route('approvals.index'), request()->routeIs('approvals.*'), 'approval', $pending];
    }

    if ($u->hasRole('Admin')) {
        $items[] = ['Users', route('admin.users.index'), request()->routeIs('admin.users.*'), 'users'];
    }

    $items = collect($items)->unique(0)->values()->all();
    $main = array_slice($items, 0, 4);   // tampil di bar
    $more = array_slice($items, 4);      // masuk tombol "Lainnya"
    $moreBadge = collect($more)->sum(fn($i) => $i[4] ?? 0);
    $moreActive = collect($more)->contains(fn($i) => $i[2]);

    $bnInitials = collect(preg_split('/\s+/', trim($u->name)))->take(2)->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
    $bnRole = $u->roles->pluck('name')->first() ?? 'PIC';

    $icon = fn(string $k, int $px = 22, string $sw = '1.75', string $cls = '') =>
        '<svg width="' . $px . '" height="' . $px . '" class="shrink-0 ' . $cls . '" fill="none" stroke="currentColor" stroke-width="' . $sw . '" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">' . $ic[$k] . '</svg>';
@endphp

<div x-data="{ sheet: false }" x-on:keydown.escape.window="sheet = false" class="lg:hidden">

    {{-- Bar bawah --}}
    <nav class="border-t border-slate-200 bg-white"
        style="position:fixed;left:0;right:0;bottom:0;z-index:40;box-sizing:border-box;width:100%;padding-bottom:env(safe-area-inset-bottom,0px);box-shadow:0 -4px 16px -6px rgba(15,23,42,.12);" aria-label="Menu bawah">
        <ul style="display:flex;align-items:stretch;margin:0;padding:0 4px;list-style:none;">
            @foreach($main as $it)
                @php [$label, $url, $active, $key] = $it; $badge = $it[4] ?? 0; @endphp
                <li style="flex:1 1 0;min-width:0;">
                    <a href="{{ $url }}" @if($active) aria-current="page" @endif
                        class="transition-colors {{ $active ? 'text-indigo-600' : 'text-slate-400 active:text-slate-700' }}" style="position:relative;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;width:100%;height:64px;font-size:10.5px;font-weight:600;line-height:1;background:none;border:0;padding:0;">
                        @if($active)
                            <span style="position:absolute;top:0;left:50%;transform:translateX(-50%);width:32px;height:2px;border-radius:0 0 4px 4px;background:#4f46e5;" aria-hidden="true"></span>
                        @endif
                        <span style="position:relative;display:inline-flex;">
                            {!! $icon($key, 22, $active ? '2' : '1.75') !!}
                            @if($badge > 0)
                                <span style="position:absolute;top:-6px;right:-10px;display:flex;align-items:center;justify-content:center;min-width:16px;height:16px;padding:0 4px;border-radius:999px;background:#ef4444;color:#fff;font-size:10px;font-weight:700;line-height:1;">{{ $badge }}</span>
                            @endif
                        </span>
                        <span style="white-space:nowrap;">{{ $label }}</span>
                    </a>
                </li>
            @endforeach

            {{-- Lainnya / Akun --}}
            @php $tabActive = $profileActive || $moreActive; $hasMore = count($more) > 0; @endphp
            <li style="flex:1 1 0;min-width:0;">
                <button type="button" @click="sheet = true" :aria-expanded="sheet.toString()"
                    class="transition-colors {{ $tabActive ? 'text-indigo-600' : 'text-slate-400 active:text-slate-700' }}" style="position:relative;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;width:100%;height:64px;font-size:10.5px;font-weight:600;line-height:1;background:none;border:0;padding:0;">
                    @if($tabActive)
                        <span style="position:absolute;top:0;left:50%;transform:translateX(-50%);width:32px;height:2px;border-radius:0 0 4px 4px;background:#4f46e5;" aria-hidden="true"></span>
                    @endif
                    <span style="position:relative;display:inline-flex;">
                        {!! $icon($hasMore ? 'more' : 'profile', 22, $tabActive ? '2' : '1.75') !!}
                        @if($moreBadge > 0)
                            <span style="position:absolute;top:-3px;right:-4px;width:10px;height:10px;border-radius:999px;background:#ef4444;box-shadow:0 0 0 2px #fff;"></span>
                        @endif
                    </span>
                    <span style="white-space:nowrap;">{{ $hasMore ? 'Lainnya' : 'Akun' }}</span>
                </button>
            </li>
        </ul>
    </nav>

    {{-- Sheet akun --}}
    <div x-show="sheet" x-cloak class="fixed inset-0 z-50">
        <div class="absolute inset-0 bg-slate-900/40" @click="sheet = false"></div>

        <div x-show="sheet" x-transition
            class="rounded-t-3xl bg-white px-4 pt-3 shadow-2xl"
            style="position:absolute;left:0;right:0;bottom:0;padding-bottom:calc(1rem + env(safe-area-inset-bottom,0px));" role="dialog" aria-modal="true"
            aria-label="Menu akun">
            <div class="mx-auto mb-3 h-1 w-10 rounded-full bg-slate-200"></div>

            <div class="flex items-center gap-3 rounded-2xl bg-slate-50 p-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-indigo-500 text-sm font-bold text-white">{{ $bnInitials }}</span>
                <div class="min-w-0 flex-1">
                    <div class="truncate text-sm font-bold text-slate-900">{{ $u->name }}</div>
                    <div class="truncate text-xs text-slate-500">{{ $u->email }}</div>
                </div>
                <span class="shrink-0 rounded-md bg-indigo-100 px-2 py-0.5 text-[11px] font-semibold text-indigo-700">{{ $bnRole }}</span>
            </div>

            <div class="mt-2 space-y-1">
                @foreach($more as $it)
                    @php $mBadge = $it[4] ?? 0; @endphp
                    <a href="{{ $it[1] }}"
                        class="flex h-12 items-center gap-3 rounded-xl px-3 text-sm font-medium transition hover:bg-slate-50 {{ $it[2] ? 'bg-indigo-50 text-indigo-600' : 'text-slate-700' }}">
                        {!! $icon($it[3], 20, '1.75', $it[2] ? 'text-indigo-600' : 'text-slate-400') !!}
                        <span class="flex-1">{{ $it[0] }}</span>
                        @if($mBadge > 0)
                            <span class="flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1.5 text-[11px] font-bold text-white">{{ $mBadge }}</span>
                        @endif
                    </a>
                @endforeach

                <a href="{{ route('profile.edit') }}"
                    class="flex h-12 items-center gap-3 rounded-xl px-3 text-sm font-medium transition hover:bg-slate-50 {{ $profileActive ? 'bg-indigo-50 text-indigo-600' : 'text-slate-700' }}">
                    {!! $icon('profile', 20, '1.75', 'text-slate-400') !!} Profil
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="flex h-12 w-full items-center gap-3 rounded-xl px-3 text-sm font-medium text-red-600 transition hover:bg-red-50">
                        {!! $icon('logout', 20) !!} Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>