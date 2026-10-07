<x-app-layout>
    @php
        // Ikon (gaya Lucide, sama dengan halaman Approval)
        $ic = [
            'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
            'user' => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
            'shield' => '<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/>',
            'circle-check' => '<circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>',
            'circle-alert' => '<circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/>',
            'plus' => '<path d="M5 12h14"/><path d="M12 5v14"/>',
            'x' => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
            'search' => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
            'pencil' => '<path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/><path d="m15 5 4 4"/>',
            'key' => '<path d="m15.5 7.5 2.3 2.3a1 1 0 0 0 1.4 0l2.1-2.1a1 1 0 0 0 0-1.4L19 4"/><path d="m21 2-9.6 9.6"/><circle cx="7.5" cy="15.5" r="5.5"/>',
            'trash' => '<path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>',
            'wrench' => '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94z"/>',
            'building' => '<path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4M10 10h4M10 14h4M10 18h4"/>',
            'phone' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>',
        ];
        $svg = fn(string $name, string $cls = 'h-4 w-4', string $sw = '1.75') =>
            '<svg class="' . $cls . '" fill="none" stroke="currentColor" stroke-width="' . $sw . '" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">' . $ic[$name] . '</svg>';

        // Warna ruangan: pakai kolom rooms.color, fallback ke palet lama
        $roomDotColors = [
            1 => '#1a1a1a',
            2 => '#a855f7',
            3 => '#92400e',
            4 => '#facc15',
            5 => '#22d3ee',
            6 => '#ef4444',
            7 => '#ec4899',
            8 => '#468432',
        ];

        $inputCls = 'w-full rounded-lg border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-500/10';
        $labelCls = 'mb-1.5 block text-xs font-bold text-slate-600';

        $roleCls = [
            'Admin' => 'bg-violet-50 text-violet-700 ring-violet-600/20',
            'PIC' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
            'TU' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        ];

        $countRole = fn($r) => \App\Models\User::whereHas('roles', fn($q) => $q->where('name', $r))->count();
        $totalUsers = \App\Models\User::count();
        $roleCounts = $roles->mapWithKeys(fn($r) => [$r->name => $countRole($r->name)]);

        $allRooms = \App\Models\Room::orderBy('id')->get();
        $maintCount = $allRooms->where('maintenance', true)->count();

        // Kolom tetap agar rata di semua baris (grid mulai breakpoint xl)
        $uCols = 'grid-cols-1 xl:grid-cols-[minmax(0,1.5fr)_150px_110px_minmax(0,1.3fr)_230px]';
        $mCols = 'grid-cols-1 xl:grid-cols-[minmax(0,1fr)_130px_190px_minmax(0,1.3fr)_96px]';

        $hasUFilter = request('search') || request('role');
    @endphp

    <div class="space-y-4 px-4 pb-8 pt-4 sm:px-8">

        {{-- ===== BANNER JUDUL ===== --}}
        <div
            class="relative flex flex-wrap items-center gap-3 overflow-hidden rounded-xl border border-white/70 bg-white/80 px-4 py-3 shadow-sm backdrop-blur sm:gap-4">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-500">
                {!! $svg('users', 'h-5 w-5') !!}
            </span>
            <div class="min-w-0 flex-1">
                <h1 class="text-base font-extrabold leading-tight text-[#0f1e5a] sm:text-lg">Manajemen User</h1>
                <p class="mt-0.5 text-xs text-slate-500">Kelola akun pengguna dan status ruang rapat di lingkungan
                    Kementerian Koordinator Bidang Pangan.</p>
            </div>
            <button type="button" onclick="openModal('modalTambah')"
                class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-[13px] font-semibold text-white shadow-sm shadow-indigo-600/25 transition hover:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                {!! $svg('plus', 'h-4 w-4', '2.2') !!}
                Tambah User
            </button>
        </div>

        {{-- ===== ALERT ===== --}}
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-transition
                class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-[13px] font-semibold text-emerald-800">
                <span class="shrink-0 text-emerald-600">{!! $svg('circle-check', 'h-5 w-5') !!}</span>
                <span class="flex-1">{{ session('success') }}</span>
                <button type="button" @click="show = false" aria-label="Tutup"
                    class="rounded-md p-1 text-emerald-500 transition hover:bg-emerald-100 hover:text-emerald-700">
                    {!! $svg('x', 'h-4 w-4', '2') !!}
                </button>
            </div>
        @endif
        @if($errors->any())
            <div
                class="flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-[13px] font-semibold text-rose-800">
                <span class="mt-0.5 shrink-0 text-rose-600">{!! $svg('circle-alert', 'h-5 w-5') !!}</span>
                <div class="flex-1 space-y-0.5">
                    @foreach($errors->all() as $e)
                        <div>{{ $e }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">

            {{-- ===== RINGKASAN ===== --}}
            <div class="flex flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-5">
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Akun terdaftar</p>
                    <div class="mt-1 flex flex-wrap items-center gap-2">
                        <span class="text-indigo-500">{!! $svg('users', 'h-5 w-5', '2') !!}</span>
                        <h2 class="text-lg font-extrabold leading-tight text-slate-900">{{ $totalUsers }} user</h2>
                        @if($maintCount > 0)
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-orange-50 px-2.5 py-1 text-xs font-semibold text-orange-700 ring-1 ring-inset ring-orange-600/20">
                                <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-orange-500"></span>
                                {{ $maintCount }} ruangan perbaikan
                            </span>
                        @endif
                    </div>
                </div>

                <div class="flex flex-wrap items-stretch gap-2">
                    <div class="min-w-[84px] rounded-xl bg-violet-50/70 px-3.5 py-2 ring-1 ring-inset ring-violet-200/70">
                        <div class="text-lg font-extrabold leading-none tabular-nums text-violet-700">
                            {{ $roleCounts['Admin'] ?? $countRole('Admin') }}</div>
                        <div class="mt-1 text-[11px] font-medium text-violet-700/80">Admin</div>
                    </div>
                    <div class="min-w-[84px] rounded-xl bg-indigo-50/70 px-3.5 py-2 ring-1 ring-inset ring-indigo-200/70">
                        <div class="text-lg font-extrabold leading-none tabular-nums text-indigo-700">
                            {{ $roleCounts['PIC'] ?? $countRole('PIC') }}</div>
                        <div class="mt-1 text-[11px] font-medium text-indigo-700/80">PIC · pengaju</div>
                    </div>
                    <div class="min-w-[84px] rounded-xl bg-emerald-50/70 px-3.5 py-2 ring-1 ring-inset ring-emerald-200/70">
                        <div class="text-lg font-extrabold leading-none tabular-nums text-emerald-700">
                            {{ $roleCounts['TU'] ?? $countRole('TU') }}</div>
                        <div class="mt-1 text-[11px] font-medium text-emerald-700/80">TU · approval</div>
                    </div>
                </div>
            </div>

            {{-- ===== TAB ===== --}}
            <div class="flex flex-wrap items-center gap-3 border-y border-slate-100 bg-slate-50/60 px-4 py-3 sm:px-5">
                <div class="flex rounded-lg bg-slate-200/60 p-0.5" role="tablist" aria-label="Tampilan">
                    <button type="button" id="tab-users-btn" role="tab" onclick="switchTab('users')"
                        class="flex items-center gap-2 rounded-md bg-white px-4 py-1.5 text-[13px] font-semibold text-indigo-700 shadow-sm transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                        Manajemen User
                    </button>
                    <button type="button" id="tab-rooms-btn" role="tab" onclick="switchTab('rooms')"
                        class="flex items-center gap-2 rounded-md px-4 py-1.5 text-[13px] font-semibold text-slate-500 transition hover:text-slate-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                        Manajemen Ruangan
                        @if($maintCount > 0)
                            <span
                                class="rounded-full bg-orange-100 px-1.5 py-0.5 text-[10px] font-extrabold leading-none text-orange-700">{{ $maintCount }}</span>
                        @endif
                    </button>
                </div>
            </div>

            {{-- ================= TAB: USER ================= --}}
            <div id="tab-users">

                {{-- Filter role (chip) + pencarian --}}
                <form method="GET" action="{{ route('admin.users.index') }}"
                    class="flex flex-wrap items-center gap-3 border-b border-slate-100 px-4 py-3 sm:px-5">
                    @if(request('role'))<input type="hidden" name="role" value="{{ request('role') }}">@endif

                    <div class="flex flex-wrap items-center gap-1.5">
                        <a href="{{ request()->fullUrlWithQuery(['role' => null, 'page' => null]) }}"
                            class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold transition {{ !request('role') ? 'border-indigo-500 bg-indigo-500 text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-indigo-300 hover:text-indigo-600' }}">
                            Semua <span class="tabular-nums opacity-80">{{ $totalUsers }}</span>
                        </a>
                        @foreach($roles as $r)
                            <a href="{{ request()->fullUrlWithQuery(['role' => $r->name, 'page' => null]) }}"
                                class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold transition {{ request('role') === $r->name ? 'border-indigo-500 bg-indigo-500 text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-indigo-300 hover:text-indigo-600' }}">
                                {{ $r->name }} <span class="tabular-nums opacity-80">{{ $roleCounts[$r->name] ?? 0 }}</span>
                            </a>
                        @endforeach
                    </div>

                    <div class="flex w-full flex-wrap items-center gap-2 sm:ml-auto sm:w-auto">
                        <div class="relative w-full sm:w-72">
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                {!! $svg('search', 'h-4 w-4', '2') !!}
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" autocomplete="off"
                                aria-label="Cari user" placeholder="Cari nama / username…"
                                class="h-9 w-full rounded-lg border border-slate-200 bg-white py-0 pl-9 pr-3 text-[13px] font-medium text-slate-700 placeholder-slate-400 focus:border-indigo-400 focus:ring-4 focus:ring-indigo-500/10">
                        </div>
                        @if($hasUFilter)
                            <a href="{{ route('admin.users.index') }}"
                                class="text-xs font-semibold text-slate-500 underline-offset-2 hover:text-indigo-600 hover:underline">Reset</a>
                        @endif
                    </div>
                </form>

                @if($users->isEmpty())
                    <div class="px-4 py-14 text-center">
                        <span
                            class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                            {!! $svg('users', 'h-6 w-6', '1.6') !!}
                        </span>
                        <div class="mt-3 text-sm font-bold text-slate-800">Tidak ada user ditemukan</div>
                        <p class="mx-auto mt-1 max-w-sm text-xs text-slate-500">Ubah kata kunci pencarian atau filter role.
                        </p>
                    </div>
                @else
                    {{-- Header kolom --}}
                    <div
                        class="hidden gap-4 border-b border-slate-100 bg-slate-50/70 px-5 py-2 text-[11px] font-semibold uppercase tracking-wide text-slate-400 xl:grid {{ $uCols }}">
                        <span>Pengguna</span>
                        <span>Username</span>
                        <span>Role</span>
                        <span>Ruangan (TU)</span>
                        <span class="text-right">Aksi</span>
                    </div>

                    @foreach($users as $u)
                        @php
                            $roleName = $u->roles->first()?->name;
                            $isTU = $u->hasRole('TU');
                            $waPhone = $u->phone ? ltrim(preg_replace('/^0/', '62', $u->phone), '+') : null;
                        @endphp

                        <div
                            class="group relative grid gap-x-4 gap-y-2 border-b border-slate-100 px-4 py-3.5 transition last:border-b-0 hover:bg-slate-50/70 sm:px-5 xl:items-center {{ $uCols }}">

                            {{-- Pengguna --}}
                            <div class="flex min-w-0 items-center gap-3">
                                <span
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-600">
                                    {{ mb_strtoupper(mb_substr($u->name, 0, 1)) }}
                                </span>
                                <div class="min-w-0">
                                    <div class="truncate text-sm font-semibold text-slate-900" title="{{ $u->name }}">
                                        {{ $u->name }}</div>
                                    @if($u->email)
                                        <div class="truncate text-xs text-slate-400" title="{{ $u->email }}">{{ $u->email }}</div>
                                    @endif
                                    @if($isTU)
                                        @if($waPhone)
                                            <a href="https://wa.me/{{ $waPhone }}" target="_blank" rel="noopener"
                                                class="mt-0.5 inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 hover:underline">
                                                {!! $svg('phone', 'h-3 w-3', '2') !!}{{ $u->phone }}
                                            </a>
                                        @else
                                            <span class="mt-0.5 inline-flex items-center gap-1 text-xs font-medium text-amber-600">
                                                {!! $svg('phone', 'h-3 w-3', '2') !!}No. WA belum diisi
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            {{-- Username --}}
                            <div class="min-w-0 truncate text-[13px] font-semibold text-indigo-600"
                                title="{{ $u->username }}">{{ $u->username ?? '—' }}</div>

                            {{-- Role --}}
                            <div>
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $roleCls[$roleName] ?? 'bg-slate-100 text-slate-500 ring-slate-400/20' }}">
                                    {{ $roleName ?? '—' }}
                                </span>
                            </div>

                            {{-- Ruangan --}}
                            <div class="min-w-0">
                                @if($isTU)
                                    <form method="POST" action="{{ route('admin.users.updateRoom', $u) }}"
                                        class="flex items-center gap-2">
                                        @csrf @method('PATCH')
                                        <select name="room_id" aria-label="Ruangan untuk {{ $u->name }}"
                                            class="h-9 min-w-0 flex-1 rounded-lg border border-slate-200 bg-white py-0 pl-3 pr-8 text-xs font-medium text-slate-700 focus:border-indigo-400 focus:ring-4 focus:ring-indigo-500/10">
                                            <option value="">— Belum ditugaskan —</option>
                                            @foreach($rooms as $r)
                                                <option value="{{ $r->id }}" {{ $u->room_id == $r->id ? 'selected' : '' }}>
                                                    {{ $r->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit"
                                            class="h-9 shrink-0 rounded-lg border border-indigo-200 bg-indigo-50 px-3 text-xs font-semibold text-indigo-700 transition hover:border-indigo-600 hover:bg-indigo-600 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500/40">Simpan</button>
                                    </form>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </div>

                            {{-- Aksi --}}
                            <div class="flex flex-wrap items-center gap-2 pt-1 xl:justify-end xl:pt-0">
                                <button type="button" onclick="openEditModal(this)"
                                    data-name="{{ $u->name }}" data-username="{{ $u->username }}"
                                    data-email="{{ $u->email }}" data-phone="{{ $u->phone }}"
                                    data-tu="{{ $isTU ? '1' : '0' }}"
                                    data-action="{{ route('admin.users.updateProfile', $u) }}"
                                    class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition hover:border-emerald-600 hover:bg-emerald-600 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40">
                                    {!! $svg('pencil', 'h-3.5 w-3.5', '2.2') !!}Edit
                                </button>
                                <button type="button" onclick="openPwdModal(this)" data-name="{{ $u->name }}"
                                    data-action="{{ route('admin.users.updatePassword', $u) }}"
                                    class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 transition hover:border-amber-600 hover:bg-amber-600 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500/40">
                                    {!! $svg('key', 'h-3.5 w-3.5', '2.2') !!}Password
                                </button>
                                @if($u->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.destroy', $u) }}"
                                        onsubmit="return confirm(@js('Hapus user ' . $u->name . '?'))">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:border-rose-600 hover:bg-rose-600 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500/40">
                                            {!! $svg('trash', 'h-3.5 w-3.5', '2.2') !!}Hapus
                                        </button>
                                    </form>
                                @else
                                    <span
                                        class="rounded-md bg-slate-100 px-2 py-1 text-[11px] font-semibold text-slate-400">Akun
                                        Anda</span>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <div
                        class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 bg-slate-50/40 px-4 py-3 sm:px-5">
                        <span class="text-xs text-slate-400">
                            Menampilkan {{ $users->firstItem() }}–{{ $users->lastItem() }} dari {{ $users->total() }} user
                        </span>
                        @if($users->hasPages())
                            <div class="w-full sm:w-auto">{{ $users->links() }}</div>
                        @endif
                    </div>
                @endif
            </div>{{-- end tab-users --}}

            {{-- ================= TAB: RUANGAN ================= --}}
            <div id="tab-rooms" style="display:none;">

                <div class="flex items-start gap-3 border-b border-slate-100 px-4 py-3 text-xs text-slate-500 sm:px-5">
                    <span class="mt-0.5 shrink-0 text-orange-500">{!! $svg('wrench', 'h-4 w-4', '2') !!}</span>
                    <p>
                        Aktifkan mode perbaikan agar ruangan <strong class="text-slate-700">tidak bisa dibooking</strong>
                        dan muncul label
                        <span
                            class="rounded-md border border-orange-200 bg-orange-50 px-1.5 py-0.5 text-[11px] font-bold text-orange-700">Perbaikan</span>
                        di kalender.
                    </p>
                </div>

                {{-- Header kolom --}}
                <div
                    class="hidden gap-4 border-b border-slate-100 bg-slate-50/70 px-5 py-2 text-[11px] font-semibold uppercase tracking-wide text-slate-400 xl:grid {{ $mCols }}">
                    <span>Ruangan</span>
                    <span>Status</span>
                    <span>Mode perbaikan</span>
                    <span>Catatan perbaikan</span>
                    <span class="text-right">Aksi</span>
                </div>

                @foreach($allRooms as $room)
                    @php $rc = $room->color ?: ($roomDotColors[$room->id] ?? '#9ca3af'); @endphp

                    <div id="room-row-{{ $room->id }}"
                        class="group relative grid gap-x-4 gap-y-2 border-b border-slate-100 px-4 py-3.5 transition last:border-b-0 hover:bg-slate-50/70 sm:px-5 xl:items-center {{ $mCols }}">
                        <span class="absolute inset-y-0 left-0 w-[3px]" style="background: {{ $rc }}"
                            aria-hidden="true"></span>

                        {{-- Ruangan --}}
                        <div class="flex min-w-0 items-center gap-2.5">
                            <span class="h-2.5 w-2.5 shrink-0 rounded-full ring-2 ring-white"
                                style="background: {{ $rc }}; box-shadow: 0 0 0 1px {{ $rc }}55;"></span>
                            <span class="truncate text-sm font-semibold text-slate-900"
                                title="{{ $room->name }}">{{ $room->name }}</span>
                        </div>

                        {{-- Status --}}
                        <div>
                            @if($room->maintenance)
                                <span
                                    class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full bg-orange-50 px-2.5 py-1 text-xs font-semibold text-orange-700 ring-1 ring-inset ring-orange-600/20">
                                    <span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span>Perbaikan
                                </span>
                            @elseif($room->active)
                                <span
                                    class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Aktif
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500 ring-1 ring-inset ring-slate-400/20">
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>Nonaktif
                                </span>
                            @endif
                        </div>

                        {{-- Mode perbaikan --}}
                        <div>
                            <label class="inline-flex cursor-pointer items-center gap-3">
                                <span class="relative inline-block h-6 w-[42px] shrink-0">
                                    <input type="checkbox" id="toggle-{{ $room->id }}" class="peer sr-only"
                                        {{ $room->maintenance ? 'checked' : '' }}
                                        onchange="toggleMaintenance({{ $room->id }}, this.checked)">
                                    <span
                                        class="absolute inset-0 rounded-full bg-slate-200 transition peer-checked:bg-red-500 peer-focus-visible:ring-4 peer-focus-visible:ring-red-200"></span>
                                    <span
                                        class="absolute left-[3px] top-[3px] h-[18px] w-[18px] rounded-full bg-white shadow transition peer-checked:translate-x-[18px]"></span>
                                </span>
                                <span id="toggle-label-{{ $room->id }}" class="text-xs font-medium text-slate-500">
                                    {{ $room->maintenance ? 'Sedang perbaikan' : 'Normal' }}
                                </span>
                            </label>
                        </div>

                        {{-- Catatan --}}
                        <div class="min-w-0">
                            <input type="text" id="note-{{ $room->id }}" value="{{ $room->maintenance_note ?? '' }}"
                                placeholder="Keterangan perbaikan…" aria-label="Catatan perbaikan {{ $room->name }}"
                                {{ !$room->maintenance ? 'disabled' : '' }}
                                class="h-9 w-full rounded-lg border border-slate-200 bg-white px-3 py-0 text-[13px] text-slate-800 placeholder-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-500/10 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:opacity-50">
                        </div>

                        {{-- Aksi --}}
                        <div class="xl:text-right">
                            <form method="POST" id="form-{{ $room->id }}"
                                action="{{ route('admin.rooms.maintenance', $room) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="maintenance" id="val-maintenance-{{ $room->id }}"
                                    value="{{ $room->maintenance ? '1' : '0' }}">
                                <input type="hidden" name="maintenance_note" id="val-note-{{ $room->id }}"
                                    value="{{ $room->maintenance_note ?? '' }}">
                                <button type="button" onclick="submitMaintenance({{ $room->id }})"
                                    class="inline-flex items-center justify-center whitespace-nowrap rounded-lg border border-orange-200 bg-orange-50 px-3.5 py-1.5 text-xs font-semibold text-orange-700 transition hover:border-orange-600 hover:bg-orange-600 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-500/40">
                                    Simpan
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach

                <div class="border-t border-slate-100 bg-slate-50/40 px-4 py-3 sm:px-5">
                    <span class="text-xs text-slate-400">{{ $allRooms->count() }} ruangan · {{ $maintCount }} sedang
                        perbaikan</span>
                </div>
            </div>{{-- end tab-rooms --}}
        </div>
    </div>

    {{-- ===== MODAL TAMBAH USER ===== --}}
    <div id="modalTambah" class="fixed inset-0 z-50 items-center justify-center p-4" style="display:none;">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('modalTambah')"></div>
        <div class="relative flex w-full max-w-md flex-col overflow-hidden rounded-2xl bg-white text-left shadow-2xl"
            role="dialog" aria-modal="true">
            <div class="flex items-start justify-between gap-3 px-5 pb-2 pt-5">
                <div class="flex min-w-0 items-center gap-3">
                    <span
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-500">
                        {!! $svg('plus', 'h-5 w-5', '2') !!}
                    </span>
                    <div class="min-w-0">
                        <h3 class="text-base font-extrabold text-slate-900">Tambah user baru</h3>
                        <p class="mt-0.5 truncate text-xs text-slate-500">Buat akun untuk Admin, PIC, atau TU.</p>
                    </div>
                </div>
                <button type="button" onclick="closeModal('modalTambah')" aria-label="Tutup"
                    class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                    {!! $svg('x', 'h-5 w-5', '2') !!}
                </button>
            </div>
            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <div class="max-h-[65vh] space-y-4 overflow-y-auto px-5 py-4">
                    <div>
                        <label class="{{ $labelCls }}">Nama</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="{{ $inputCls }}"
                            placeholder="Nama lengkap">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Username</label>
                        <input type="text" name="username" value="{{ old('username') }}" required
                            class="{{ $inputCls }}" placeholder="Contoh: budi123">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Password</label>
                        <input type="password" name="password" required class="{{ $inputCls }}"
                            placeholder="Min. 8 karakter">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Role</label>
                        <select name="role" required class="{{ $inputCls }}" id="roleSelect"
                            onchange="toggleTuFields()">
                            <option value="" disabled selected>— Pilih role —</option>
                            @foreach($roles as $r)
                                <option value="{{ $r->name }}">{{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="roomField" style="display:none;">
                        <label class="{{ $labelCls }}">Ruangan (khusus TU)</label>
                        <select name="room_id" class="{{ $inputCls }}">
                            <option value="">— Belum ditugaskan —</option>
                            @foreach($rooms as $r)
                                <option value="{{ $r->id }}">{{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="emailField" style="display:none;">
                        <label class="{{ $labelCls }}">Email (opsional)</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="{{ $inputCls }}"
                            placeholder="email@domain.com">
                    </div>
                    <div id="phoneField" style="display:none;">
                        <label class="{{ $labelCls }}">Nomor WhatsApp (khusus TU)</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="{{ $inputCls }}"
                            placeholder="08xxxxxxxxxx">
                        <p class="mt-1.5 text-xs text-slate-400">Untuk menerima notifikasi booking masuk via WA.</p>
                    </div>
                </div>
                <div class="flex justify-end gap-2 border-t border-slate-100 bg-slate-50/60 px-5 py-3.5">
                    <button type="button" onclick="closeModal('modalTambah')"
                        class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-[13px] font-semibold text-slate-700 transition hover:bg-slate-50">Batal</button>
                    <button type="submit"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-[13px] font-semibold text-white shadow-sm shadow-indigo-600/25 transition hover:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">Tambah
                        user</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== MODAL EDIT PROFIL ===== --}}
    <div id="modalEdit" class="fixed inset-0 z-50 items-center justify-center p-4" style="display:none;">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('modalEdit')"></div>
        <div class="relative flex w-full max-w-md flex-col overflow-hidden rounded-2xl bg-white text-left shadow-2xl"
            role="dialog" aria-modal="true">
            <div class="flex items-start justify-between gap-3 px-5 pb-2 pt-5">
                <div class="flex min-w-0 items-center gap-3">
                    <span
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-500">
                        {!! $svg('pencil', 'h-5 w-5', '2') !!}
                    </span>
                    <div class="min-w-0">
                        <h3 class="text-base font-extrabold text-slate-900">Edit profil user</h3>
                        <p id="editUserLabel" class="mt-0.5 truncate text-xs text-slate-500"></p>
                    </div>
                </div>
                <button type="button" onclick="closeModal('modalEdit')" aria-label="Tutup"
                    class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                    {!! $svg('x', 'h-5 w-5', '2') !!}
                </button>
            </div>
            <form method="POST" id="formEdit" action="">
                @csrf @method('PATCH')
                <div class="max-h-[65vh] space-y-4 overflow-y-auto px-5 py-4">
                    <div>
                        <label class="{{ $labelCls }}">Nama</label>
                        <input type="text" name="name" id="editName" required class="{{ $inputCls }}"
                            placeholder="Nama lengkap">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Username</label>
                        <input type="text" name="username" id="editUsername" required class="{{ $inputCls }}"
                            placeholder="Contoh: budi123">
                    </div>
                    <div id="editEmailField" style="display:none;">
                        <label class="{{ $labelCls }}">Email (opsional)</label>
                        <input type="email" name="email" id="editEmail" class="{{ $inputCls }}"
                            placeholder="email@domain.com">
                    </div>
                    <div id="editPhoneField" style="display:none;">
                        <label class="{{ $labelCls }}">Nomor WhatsApp (khusus TU)</label>
                        <input type="text" name="phone" id="editPhone" class="{{ $inputCls }}"
                            placeholder="08xxxxxxxxxx">
                        <p class="mt-1.5 text-xs text-slate-400">Untuk menerima notifikasi booking masuk via WA.</p>
                    </div>
                </div>
                <div class="flex justify-end gap-2 border-t border-slate-100 bg-slate-50/60 px-5 py-3.5">
                    <button type="button" onclick="closeModal('modalEdit')"
                        class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-[13px] font-semibold text-slate-700 transition hover:bg-slate-50">Batal</button>
                    <button type="submit"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-[13px] font-semibold text-white shadow-sm shadow-indigo-600/25 transition hover:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">Simpan
                        perubahan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== MODAL RESET PASSWORD ===== --}}
    <div id="modalPwd" class="fixed inset-0 z-50 items-center justify-center p-4" style="display:none;">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('modalPwd')"></div>
        <div class="relative flex w-full max-w-md flex-col overflow-hidden rounded-2xl bg-white text-left shadow-2xl"
            role="dialog" aria-modal="true">
            <div class="flex items-start justify-between gap-3 px-5 pb-2 pt-5">
                <div class="flex min-w-0 items-center gap-3">
                    <span
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-500">
                        {!! $svg('key', 'h-5 w-5', '2') !!}
                    </span>
                    <div class="min-w-0">
                        <h3 class="text-base font-extrabold text-slate-900">Reset password</h3>
                        <p id="pwdUserLabel" class="mt-0.5 truncate text-xs text-slate-500"></p>
                    </div>
                </div>
                <button type="button" onclick="closeModal('modalPwd')" aria-label="Tutup"
                    class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                    {!! $svg('x', 'h-5 w-5', '2') !!}
                </button>
            </div>
            <form method="POST" id="formPwd" action="">
                @csrf @method('PATCH')
                <div class="space-y-4 px-5 py-4">
                    <div>
                        <label class="{{ $labelCls }}">Password baru</label>
                        <input type="password" name="password" required class="{{ $inputCls }}"
                            placeholder="Min. 8 karakter">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Konfirmasi password</label>
                        <input type="password" name="password_confirmation" required class="{{ $inputCls }}"
                            placeholder="Ulangi password">
                    </div>
                </div>
                <div class="flex justify-end gap-2 border-t border-slate-100 bg-slate-50/60 px-5 py-3.5">
                    <button type="button" onclick="closeModal('modalPwd')"
                        class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-[13px] font-semibold text-slate-700 transition hover:bg-slate-50">Batal</button>
                    <button type="submit"
                        class="rounded-lg bg-amber-600 px-4 py-2 text-[13px] font-semibold text-white shadow-sm shadow-amber-600/25 transition hover:bg-amber-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2">Reset
                        password</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // ===== MODAL =====
        function openModal(id) { document.getElementById(id).style.display = 'flex' }
        function closeModal(id) { document.getElementById(id).style.display = 'none' }
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') ['modalTambah', 'modalEdit', 'modalPwd'].forEach(closeModal)
        })

        // ===== TAB SWITCH =====
        function switchTab(tab) {
            const ACTIVE = 'flex items-center gap-2 rounded-md bg-white px-4 py-1.5 text-[13px] font-semibold text-indigo-700 shadow-sm transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500'
            const IDLE = 'flex items-center gap-2 rounded-md px-4 py-1.5 text-[13px] font-semibold text-slate-500 transition hover:text-slate-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500'
            document.getElementById('tab-users').style.display = tab === 'users' ? 'block' : 'none'
            document.getElementById('tab-rooms').style.display = tab === 'rooms' ? 'block' : 'none'
            document.getElementById('tab-users-btn').className = tab === 'users' ? ACTIVE : IDLE
            document.getElementById('tab-rooms-btn').className = tab === 'rooms' ? ACTIVE : IDLE
            sessionStorage.setItem('adminTab', tab)
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (sessionStorage.getItem('adminTab') === 'rooms') switchTab('rooms')
        })

        // ===== MAINTENANCE TOGGLE =====
        function toggleMaintenance(roomId, isChecked) {
            const noteInput = document.getElementById('note-' + roomId)
            document.getElementById('toggle-label-' + roomId).textContent = isChecked ? 'Sedang perbaikan' : 'Normal'
            noteInput.disabled = !isChecked
            if (!isChecked) noteInput.value = ''
        }

        function submitMaintenance(roomId) {
            const isChecked = document.getElementById('toggle-' + roomId).checked
            const note = document.getElementById('note-' + roomId).value
            document.getElementById('val-maintenance-' + roomId).value = isChecked ? '1' : '0'
            document.getElementById('val-note-' + roomId).value = note
            sessionStorage.setItem('adminTab', 'rooms')
            document.getElementById('form-' + roomId).submit()
        }

        // ===== USER MODALS =====
        function toggleTuFields() {
            const isTU = document.getElementById('roleSelect').value === 'TU'
            document.getElementById('roomField').style.display = isTU ? 'block' : 'none'
            document.getElementById('emailField').style.display = isTU ? 'block' : 'none'
            document.getElementById('phoneField').style.display = isTU ? 'block' : 'none'
        }

        function openEditModal(btn) {
            const d = btn.dataset
            const isTU = d.tu === '1'
            document.getElementById('editName').value = d.name || ''
            document.getElementById('editUsername').value = d.username || ''
            document.getElementById('editEmail').value = d.email || ''
            document.getElementById('editPhone').value = d.phone || ''
            document.getElementById('editUserLabel').textContent = d.name || ''
            document.getElementById('editEmailField').style.display = isTU ? 'block' : 'none'
            document.getElementById('editPhoneField').style.display = isTU ? 'block' : 'none'
            document.getElementById('formEdit').action = d.action
            openModal('modalEdit')
        }

        function openPwdModal(btn) {
            document.getElementById('pwdUserLabel').textContent = btn.dataset.name || ''
            document.getElementById('formPwd').action = btn.dataset.action
            openModal('modalPwd')
        }

        @if($errors->any())
            openModal('modalTambah')
        @endif
    </script>
</x-app-layout>