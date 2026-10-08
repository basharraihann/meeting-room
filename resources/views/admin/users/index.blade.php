<x-app-layout>
    @php
        $roomDotColors = [
            1 => '#1a1a1a', 2 => '#a855f7', 3 => '#92400e', 4 => '#facc15',
            5 => '#22d3ee', 6 => '#ef4444', 7 => '#ec4899', 8 => '#468432',
        ];
        $inputCls = 'w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-500/10';
        $labelCls = 'mb-1.5 block text-xs font-bold text-slate-600';
        $closeBtn = 'rounded-lg bg-slate-100 px-2.5 py-1.5 text-sm text-slate-500 hover:bg-slate-200';

        $roleCls = [
            'Admin' => 'bg-violet-50 text-violet-700',
            'PIC'   => 'bg-indigo-50 text-indigo-600',
            'TU'    => 'bg-emerald-50 text-emerald-700',
        ];
        $countRole = fn ($r) => \App\Models\User::whereHas('roles', fn ($q) => $q->where('name', $r))->count();
    @endphp

    <div class="space-y-6 px-4 py-8 sm:px-8">

        {{-- Judul --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Manajemen User</h1>
                <p class="mt-1.5 max-w-md text-sm text-slate-500">Kelola akun pengguna dan status ruang rapat di lingkungan Kementerian Koordinator Bidang Pangan.</p>
            </div>
            <button type="button" onclick="document.getElementById('modalTambah').style.display = 'flex'"
                class="w-full sm:w-auto rounded-full bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-600/25 transition hover:bg-indigo-700 text-center">
                + Tambah User
            </button>
        </div>

        {{-- Notifikasi --}}
        @if(session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-medium text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="space-y-1 rounded-2xl border border-red-200 bg-red-50 px-5 py-3 text-sm text-red-700">
                @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
            </div>
        @endif

        {{-- Ringkasan --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
            @foreach([
                ['Total User', \App\Models\User::count(), 'akun terdaftar', 'bg-indigo-100 text-indigo-600', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                ['Admin', $countRole('Admin'), 'pengelola sistem', 'bg-violet-100 text-violet-600', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                ['PIC', $countRole('PIC'), 'pengaju rapat', 'bg-sky-100 text-sky-600', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                ['TU', $countRole('TU'), 'pemberi approval', 'bg-emerald-100 text-emerald-600', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
            ] as [$sLabel, $sValue, $sSub, $sCls, $sIcon])
                <div class="flex items-center gap-3 sm:gap-4 rounded-2xl border border-slate-100 bg-white p-4 sm:p-5 shadow-sm">
                    <span class="flex h-12 w-12 sm:h-14 sm:w-14 shrink-0 items-center justify-center rounded-full {{ $sCls }}">
                        <svg class="h-6 w-6 sm:h-7 sm:w-7" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $sIcon }}"/></svg>
                    </span>
                    <div class="min-w-0">
                        <div class="text-xs font-medium text-slate-500">{{ $sLabel }}</div>
                        <div class="text-2xl sm:text-3xl font-extrabold leading-tight text-slate-900">{{ $sValue }}</div>
                        <div class="truncate text-xs text-slate-400">{{ $sSub }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Tab --}}
        <div class="flex max-w-full overflow-x-auto rounded-full bg-white p-1 shadow-sm sm:w-fit">
            <button type="button" id="tab-users-btn" onclick="switchTab('users')"
                class="whitespace-nowrap rounded-full bg-indigo-500 px-4 py-2 text-xs font-semibold text-white shadow transition sm:px-5 sm:text-sm">
                Manajemen User
            </button>
            <button type="button" id="tab-rooms-btn" onclick="switchTab('rooms')"
                class="whitespace-nowrap rounded-full px-4 py-2 text-xs font-semibold text-slate-600 transition hover:text-slate-900 sm:px-5 sm:text-sm">
                Manajemen Ruangan
            </button>
        </div>

        {{-- ===== TAB: USER ===== --}}
        <div id="tab-users" class="space-y-4">

            {{-- Filter --}}
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-center gap-3">
                    <div class="relative w-full sm:w-64">
                        <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / username..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm placeholder-slate-400 focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
                    </div>
                    <select name="role"
                        class="w-full sm:w-auto rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-4 pr-9 text-sm text-slate-600 focus:border-indigo-400 focus:ring-4 focus:ring-indigo-500/10">
                        <option value="">Semua Role</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->name }}" {{ request('role') == $r->name ? 'selected' : '' }}>{{ $r->name }}</option>
                        @endforeach
                    </select>
                    <div class="flex w-full sm:w-auto items-center gap-2">
                        <button type="submit" class="flex-1 sm:flex-initial rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-indigo-700">Cari</button>
                        @if(request('search') || request('role'))
                            <a href="{{ route('admin.users.index') }}" class="px-3 py-2 text-sm font-semibold text-slate-400 hover:text-slate-600">Reset</a>
                        @endif
                    </div>
                    <span class="w-full sm:w-auto sm:ml-auto text-xs font-semibold text-slate-400">{{ $users->total() }} user</span>
                </form>
            </div>

            {{-- Tabel user --}}
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm sm:p-6">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[820px] text-left text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-xs font-semibold text-slate-500">
                                <th class="rounded-l-xl px-4 py-3">Nama</th>
                                <th class="px-4 py-3">Username</th>
                                <th class="px-4 py-3">Role</th>
                                <th class="px-4 py-3">Ruangan (TU)</th>
                                <th class="rounded-r-xl px-4 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($users as $u)
                                <tr class="transition hover:bg-slate-50/70">
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-600">
                                                {{ strtoupper(substr($u->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="font-semibold text-slate-900">{{ $u->name }}</div>
                                                @if($u->email)
                                                    <div class="text-xs text-slate-400">{{ $u->email }}</div>
                                                @endif
                                                @if($u->hasRole('TU'))
                                                    @if($u->phone)
                                                        @php
                                                            $waPhone = preg_replace('/^0/', '62', $u->phone);
                                                            $waPhone = ltrim($waPhone, '+');
                                                        @endphp
                                                        <a href="https://wa.me/{{ $waPhone }}" target="_blank"
                                                            class="text-xs font-semibold text-emerald-600 hover:underline">
                                                            WA: {{ $u->phone }}
                                                        </a>
                                                    @else
                                                        <span class="text-xs font-medium text-amber-500">No. WA belum diisi</span>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-4 py-4 font-medium text-indigo-600">{{ $u->username ?? '—' }}</td>

                                    <td class="px-4 py-4">
                                        <span class="inline-flex rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-600">
                                            {{ $u->roles->first()?->name ?? '—' }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-4">
                                        @if($u->hasRole('TU'))
                                            <form method="POST" action="{{ route('admin.users.updateRoom', $u) }}" class="flex items-center gap-2">
                                                @csrf @method('PATCH')
                                                <select name="room_id"
                                                    class="rounded-lg border border-slate-200 bg-slate-50 py-1.5 pl-3 pr-8 text-xs text-slate-700 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-500/10">
                                                    <option value="">— Belum ditugaskan —</option>
                                                    @foreach($rooms as $r)
                                                        <option value="{{ $r->id }}" {{ $u->room_id == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-indigo-700">Simpan</button>
                                            </form>
                                        @else
                                            <span class="text-slate-300">—</span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-4">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <button type="button"
                                                class="rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700 transition hover:bg-emerald-100"
                                                onclick="openEditModal(
                                                    '{{ addslashes($u->name) }}',
                                                    '{{ addslashes($u->username ?? '') }}',
                                                    '{{ addslashes($u->email ?? '') }}',
                                                    '{{ addslashes($u->phone ?? '') }}',
                                                    {{ $u->hasRole('TU') ? 'true' : 'false' }},
                                                    '{{ route('admin.users.updateProfile', $u) }}'
                                                )">
                                                Edit
                                            </button>
                                            <button type="button"
                                                class="rounded-lg bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700 transition hover:bg-amber-100"
                                                onclick="openPwdModal('{{ addslashes($u->name) }}', '{{ route('admin.users.updatePassword', $u) }}')">
                                                Password
                                            </button>
                                            @if($u->id !== auth()->id())
                                                <form method="POST" action="{{ route('admin.users.destroy', $u) }}"
                                                    onsubmit="return confirm('Hapus user {{ addslashes($u->name) }}?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-bold text-red-600 transition hover:bg-red-600 hover:text-white">Hapus</button>
                                                </form>
                                            @else
                                                <span class="text-xs text-slate-300">Akun Anda</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-12 text-center text-slate-400">Tidak ada user ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($users->hasPages())
                    <div class="mt-4 border-t border-slate-100 pt-4">{{ $users->links() }}</div>
                @endif
            </div>
        </div>{{-- end tab-users --}}

        {{-- ===== TAB: RUANGAN ===== --}}
        <div id="tab-rooms" class="space-y-4" style="display:none;">

            <p class="text-sm text-slate-500">
                Aktifkan mode perbaikan agar ruangan <strong class="text-slate-700">tidak bisa dibooking</strong> dan muncul label
                <span class="rounded-md border border-orange-200 bg-orange-50 px-2 py-0.5 text-xs font-bold text-orange-700">Perbaikan</span>
                di kalender.
            </p>

            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm sm:p-6">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[820px] text-left text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-xs font-semibold text-slate-500">
                                <th class="rounded-l-xl px-4 py-3">Ruangan</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Mode Perbaikan</th>
                                <th class="px-4 py-3">Catatan Perbaikan</th>
                                <th class="rounded-r-xl px-4 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach(\App\Models\Room::orderBy('id')->get() as $room)
                                <tr id="room-row-{{ $room->id }}" class="transition hover:bg-slate-50/70">
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-2.5">
                                            <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background: {{ $roomDotColors[$room->id] ?? '#9ca3af' }}"></span>
                                            <span class="font-semibold text-slate-900">{{ $room->name }}</span>
                                        </div>
                                    </td>

                                    <td class="px-4 py-4">
                                        @if($room->maintenance)
                                            <span class="inline-flex items-center gap-1.5 rounded-full border border-orange-200 bg-orange-50 px-3 py-1 text-xs font-bold text-orange-700">
                                                <span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span>Perbaikan
                                            </span>
                                        @elseif($room->active)
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-400">Nonaktif</span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-4">
                                        <label class="inline-flex cursor-pointer items-center gap-3">
                                            <span class="relative inline-block h-6 w-[42px] shrink-0">
                                                <input type="checkbox" id="toggle-{{ $room->id }}" class="peer sr-only"
                                                    {{ $room->maintenance ? 'checked' : '' }}
                                                    onchange="toggleMaintenance({{ $room->id }}, this.checked)">
                                                <span class="absolute inset-0 rounded-full bg-slate-200 transition peer-checked:bg-red-500 peer-focus-visible:ring-4 peer-focus-visible:ring-red-200"></span>
                                                <span class="absolute left-[3px] top-[3px] h-[18px] w-[18px] rounded-full bg-white shadow transition peer-checked:translate-x-[18px]"></span>
                                            </span>
                                            <span id="toggle-label-{{ $room->id }}" class="text-xs text-slate-500">
                                                {{ $room->maintenance ? 'Sedang perbaikan' : 'Normal' }}
                                            </span>
                                        </label>
                                    </td>

                                    <td class="px-4 py-4">
                                        <input type="text" id="note-{{ $room->id }}"
                                            value="{{ $room->maintenance_note ?? '' }}"
                                            placeholder="Keterangan perbaikan..."
                                            {{ !$room->maintenance ? 'disabled' : '' }}
                                            class="w-56 rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs text-slate-800 placeholder-slate-400 focus:border-orange-400 focus:bg-white focus:ring-2 focus:ring-orange-200 disabled:opacity-40">
                                    </td>

                                    <td class="px-4 py-4">
                                        <form method="POST" id="form-{{ $room->id }}" action="{{ route('admin.rooms.maintenance', $room) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="maintenance" id="val-maintenance-{{ $room->id }}" value="{{ $room->maintenance ? '1' : '0' }}">
                                            <input type="hidden" name="maintenance_note" id="val-note-{{ $room->id }}" value="{{ $room->maintenance_note ?? '' }}">
                                            <button type="button" onclick="submitMaintenance({{ $room->id }})"
                                                class="rounded-lg bg-orange-500 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-orange-600">
                                                Simpan
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>{{-- end tab-rooms --}}
    </div>

    {{-- ===== MODAL TAMBAH USER ===== --}}
    <div id="modalTambah" class="fixed inset-0 z-50 items-center justify-center bg-slate-900/50 p-4" style="display:none;">
        <div class="w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <h3 class="text-base font-extrabold text-slate-900">Tambah User Baru</h3>
                <button type="button" class="{{ $closeBtn }}" onclick="document.getElementById('modalTambah').style.display='none'">✕</button>
            </div>
            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <div class="max-h-[70vh] space-y-4 overflow-y-auto px-6 py-5">
                    <div>
                        <label class="{{ $labelCls }}">Nama</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="{{ $inputCls }}" placeholder="Nama lengkap">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Username</label>
                        <input type="text" name="username" value="{{ old('username') }}" required class="{{ $inputCls }}" placeholder="Contoh: budi123">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Password</label>
                        <input type="password" name="password" required class="{{ $inputCls }}" placeholder="Min. 8 karakter">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Role</label>
                        <select name="role" required class="{{ $inputCls }}" id="roleSelect" onchange="toggleTuFields()">
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
                        <input type="email" name="email" value="{{ old('email') }}" class="{{ $inputCls }}" placeholder="email@domain.com">
                    </div>
                    <div id="phoneField" style="display:none;">
                        <label class="{{ $labelCls }}">Nomor WhatsApp (khusus TU)</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="{{ $inputCls }}" placeholder="08xxxxxxxxxx">
                        <p class="mt-1.5 text-xs text-slate-400">Untuk menerima notifikasi booking masuk via WA.</p>
                    </div>
                </div>
                <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 border-t border-slate-100 bg-slate-50 px-6 py-4">
                    <button type="button" onclick="document.getElementById('modalTambah').style.display='none'"
                        class="w-full sm:w-auto rounded-xl bg-slate-200 px-5 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-300 text-center">Batal</button>
                    <button type="submit"
                        class="w-full sm:w-auto rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-600/25 transition hover:bg-indigo-700 text-center">Tambah User</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== MODAL EDIT PROFIL ===== --}}
    <div id="modalEdit" class="fixed inset-0 z-50 items-center justify-center bg-slate-900/50 p-4" style="display:none;">
        <div class="w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <h3 class="text-base font-extrabold text-slate-900">Edit Profil User</h3>
                <button type="button" class="{{ $closeBtn }}" onclick="document.getElementById('modalEdit').style.display='none'">✕</button>
            </div>
            <form method="POST" id="formEdit" action="">
                @csrf @method('PATCH')
                <div class="max-h-[70vh] space-y-4 overflow-y-auto px-6 py-5">
                    <div>
                        <label class="{{ $labelCls }}">Nama</label>
                        <input type="text" name="name" id="editName" required class="{{ $inputCls }}" placeholder="Nama lengkap">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Username</label>
                        <input type="text" name="username" id="editUsername" required class="{{ $inputCls }}" placeholder="Contoh: budi123">
                    </div>
                    <div id="editEmailField" style="display:none;">
                        <label class="{{ $labelCls }}">Email (opsional)</label>
                        <input type="email" name="email" id="editEmail" class="{{ $inputCls }}" placeholder="email@domain.com">
                    </div>
                    <div id="editPhoneField" style="display:none;">
                        <label class="{{ $labelCls }}">Nomor WhatsApp (khusus TU)</label>
                        <input type="text" name="phone" id="editPhone" class="{{ $inputCls }}" placeholder="08xxxxxxxxxx">
                        <p class="mt-1.5 text-xs text-slate-400">Untuk menerima notifikasi booking masuk via WA.</p>
                    </div>
                </div>
                <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 border-t border-slate-100 bg-slate-50 px-6 py-4">
                    <button type="button" onclick="document.getElementById('modalEdit').style.display='none'"
                        class="w-full sm:w-auto rounded-xl bg-slate-200 px-5 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-300 text-center">Batal</button>
                    <button type="submit"
                        class="w-full sm:w-auto rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-600/25 transition hover:bg-indigo-700 text-center">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== MODAL RESET PASSWORD ===== --}}
    <div id="modalPwd" class="fixed inset-0 z-50 items-center justify-center bg-slate-900/50 p-4" style="display:none;">
        <div class="w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <h3 class="text-base font-extrabold text-slate-900">Reset Password</h3>
                <button type="button" class="{{ $closeBtn }}" onclick="document.getElementById('modalPwd').style.display='none'">✕</button>
            </div>
            <form method="POST" id="formPwd" action="">
                @csrf @method('PATCH')
                <div class="space-y-4 px-6 py-5">
                    <p id="pwdUserLabel" class="text-sm text-slate-500"></p>
                    <div>
                        <label class="{{ $labelCls }}">Password Baru</label>
                        <input type="password" name="password" required class="{{ $inputCls }}" placeholder="Min. 8 karakter">
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" required class="{{ $inputCls }}" placeholder="Ulangi password">
                    </div>
                </div>
                <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 border-t border-slate-100 bg-slate-50 px-6 py-4">
                    <button type="button" onclick="document.getElementById('modalPwd').style.display='none'"
                        class="w-full sm:w-auto rounded-xl bg-slate-200 px-5 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-300 text-center">Batal</button>
                    <button type="submit"
                        class="w-full sm:w-auto rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-600/25 transition hover:bg-indigo-700 text-center">Reset Password</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // ===== TAB SWITCH =====
        function switchTab(tab) {
            const ACTIVE = 'rounded-full bg-indigo-500 px-5 py-2 text-sm font-semibold text-white shadow transition'
            const IDLE = 'rounded-full px-5 py-2 text-sm font-semibold text-slate-600 transition hover:text-slate-900'
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

        function openEditModal(name, username, email, phone, isTU, actionUrl) {
            document.getElementById('editName').value = name
            document.getElementById('editUsername').value = username
            document.getElementById('editEmail').value = email
            document.getElementById('editPhone').value = phone
            document.getElementById('editEmailField').style.display = isTU ? 'block' : 'none'
            document.getElementById('editPhoneField').style.display = isTU ? 'block' : 'none'
            document.getElementById('formEdit').action = actionUrl
            document.getElementById('modalEdit').style.display = 'flex'
        }

        function openPwdModal(name, actionUrl) {
            document.getElementById('pwdUserLabel').textContent = `Reset password untuk: ${name}`
            document.getElementById('formPwd').action = actionUrl
            document.getElementById('modalPwd').style.display = 'flex'
        }

        @if($errors->any())
            document.getElementById('modalTambah').style.display = 'flex'
        @endif
    </script>
</x-app-layout>