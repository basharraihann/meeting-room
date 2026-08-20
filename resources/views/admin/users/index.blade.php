<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Manajemen User
            </h2>
            <button onclick="document.getElementById('modalTambah').style.display = 'flex'"
                class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition">
                + Tambah User
            </button>
        </div>
    </x-slot>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        .admin-wrap {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .filter-bar {
            background: #fff;
            border-radius: 16px;
            padding: 16px 20px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .06);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
        }

        .search-input {
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: 8px 14px;
            font-size: 13px;
            font-family: inherit;
            outline: none;
            width: 240px;
            transition: border-color .2s;
        }

        .search-input:focus {
            border-color: #6366f1;
        }

        .btn-search {
            padding: 8px 16px;
            border-radius: 10px;
            background: #6366f1;
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            font-family: inherit;
            border: none;
            cursor: pointer;
        }

        .btn-search:hover {
            background: #4f46e5;
        }

        .role-select {
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: 8px 14px;
            font-size: 13px;
            font-family: inherit;
            outline: none;
            color: #475569;
        }

        .user-table-wrap {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .06);
            overflow: hidden;
        }

        .user-table {
            width: 100%;
            border-collapse: collapse;
        }

        .user-table thead tr {
            background: #f8fafc;
            border-bottom: 2px solid #f1f5f9;
        }

        .user-table th {
            padding: 12px 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #94a3b8;
            text-align: left;
        }

        .user-table td {
            padding: 14px 20px;
            font-size: 13px;
            border-bottom: 1px solid #f8fafc;
            vertical-align: middle;
            color: #1e293b;
        }

        .user-table tbody tr:last-child td {
            border-bottom: none;
        }

        .user-table tbody tr:hover {
            background: #fafafa;
        }

        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: #eef2ff;
            color: #6366f1;
            font-size: 14px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .inline-select {
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            padding: 5px 10px;
            font-size: 12px;
            font-family: inherit;
            outline: none;
            color: #1e293b;
            background: #f8fafc;
            -webkit-appearance: none;
            appearance: none;
        }

        .inline-select:focus {
            border-color: #6366f1;
            background: #fff;
        }

        .btn-save {
            padding: 5px 12px;
            border-radius: 8px;
            background: #6366f1;
            color: #fff;
            font-size: 12px;
            font-weight: 600;
            font-family: inherit;
            border: none;
            cursor: pointer;
            transition: background .15s;
        }

        .btn-save:hover {
            background: #4f46e5;
        }

        .btn-del {
            padding: 5px 12px;
            border-radius: 8px;
            background: #fee2e2;
            color: #b91c1c;
            font-size: 12px;
            font-weight: 600;
            font-family: inherit;
            border: none;
            cursor: pointer;
            transition: background .15s;
        }

        .btn-del:hover {
            background: #dc2626;
            color: #fff;
        }

        .btn-edit {
            padding: 5px 12px;
            border-radius: 8px;
            background: #f0fdf4;
            color: #15803d;
            font-size: 12px;
            font-weight: 600;
            font-family: inherit;
            border: none;
            cursor: pointer;
            transition: background .15s;
        }

        .btn-edit:hover {
            background: #dcfce7;
        }

        .btn-pwd {
            padding: 5px 12px;
            border-radius: 8px;
            background: #fef9c3;
            color: #a16207;
            font-size: 12px;
            font-weight: 600;
            font-family: inherit;
            border: none;
            cursor: pointer;
            transition: background .15s;
        }

        .btn-pwd:hover {
            background: #fde047;
        }

        .aksi-group {
            display: flex;
            gap: 6px;
            align-items: center;
            flex-wrap: wrap;
        }

        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .45);
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .modal-box {
            background: #fff;
            border-radius: 20px;
            width: 100%;
            max-width: 460px;
            box-shadow: 0 8px 40px rgba(0, 0, 0, .12);
            overflow: hidden;
        }

        .modal-header {
            padding: 18px 22px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-title {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
        }

        .modal-body {
            padding: 20px 22px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            max-height: 70vh;
            overflow-y: auto;
        }

        .modal-footer {
            padding: 14px 22px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .field-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 5px;
        }

        .field-input {
            width: 100%;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: 9px 13px;
            font-size: 13px;
            font-family: inherit;
            outline: none;
            color: #1e293b;
            background: #f8fafc;
            box-sizing: border-box;
            -webkit-appearance: none;
            appearance: none;
        }

        .field-input:focus {
            border-color: #6366f1;
            background: #fff;
        }

        .btn-cancel-modal {
            padding: 8px 18px;
            border-radius: 10px;
            background: #f1f5f9;
            color: #475569;
            font-size: 13px;
            font-weight: 600;
            font-family: inherit;
            border: none;
            cursor: pointer;
        }

        .btn-submit-modal {
            padding: 8px 18px;
            border-radius: 10px;
            background: #6366f1;
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            font-family: inherit;
            border: none;
            cursor: pointer;
        }

        .btn-submit-modal:hover {
            background: #4f46e5;
        }

        .pagination-wrap {
            padding: 14px 20px;
            border-top: 1px solid #f1f5f9;
        }

        .empty-state {
            text-align: center;
            color: #94a3b8;
            padding: 40px !important;
        }

        /* ===== TAB NAVIGATION ===== */
        .tab-nav {
            display: flex;
            gap: 4px;
            background: #fff;
            border-radius: 14px;
            padding: 6px;
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
            margin-bottom: 16px;
            width: fit-content;
        }

        .tab-btn {
            padding: 7px 20px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            font-family: inherit;
            border: none;
            cursor: pointer;
            background: transparent;
            color: #64748b;
            transition: all .15s;
        }

        .tab-btn.active {
            background: #6366f1;
            color: #fff;
        }

        .tab-btn:hover:not(.active) {
            background: #f1f5f9;
            color: #1e293b;
        }

        /* ===== ROOM TABLE ===== */
        .room-table-wrap {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
            overflow: hidden;
        }

        .room-table {
            width: 100%;
            border-collapse: collapse;
        }

        .room-table thead tr {
            background: #f8fafc;
            border-bottom: 2px solid #f1f5f9;
        }

        .room-table th {
            padding: 12px 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #94a3b8;
            text-align: left;
        }

        .room-table td {
            padding: 14px 20px;
            font-size: 13px;
            border-bottom: 1px solid #f8fafc;
            vertical-align: middle;
            color: #1e293b;
        }

        .room-table tbody tr:last-child td {
            border-bottom: none;
        }

        .room-table tbody tr:hover {
            background: #fafafa;
        }

        /* Toggle switch */
        .toggle-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .toggle-switch {
            position: relative;
            width: 42px;
            height: 24px;
            flex-shrink: 0;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            inset: 0;
            background: #e2e8f0;
            border-radius: 99px;
            cursor: pointer;
            transition: background .2s;
        }

        .toggle-slider:before {
            content: '';
            position: absolute;
            width: 18px;
            height: 18px;
            left: 3px;
            top: 3px;
            background: #fff;
            border-radius: 50%;
            transition: transform .2s;
            box-shadow: 0 1px 3px rgba(0,0,0,.2);
        }

        .toggle-switch input:checked + .toggle-slider {
            background: #ef4444;
        }

        .toggle-switch input:checked + .toggle-slider:before {
            transform: translateX(18px);
        }

        .maintenance-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            background: #fff7ed;
            color: #c2410c;
            border: 1px solid #fed7aa;
        }

        .active-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .note-input {
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            padding: 5px 10px;
            font-size: 12px;
            font-family: inherit;
            outline: none;
            color: #1e293b;
            background: #f8fafc;
            width: 200px;
            transition: border-color .2s;
        }

        .note-input:focus {
            border-color: #f97316;
            background: #fff;
        }
    </style>

    <div class="py-6 admin-wrap">
        <div class="mx-auto sm:px-6 lg:px-8" style="max-width:90%">

            @if(session('success'))
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:#15803d;">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:#b91c1c;">
                    @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
                </div>
            @endif

            {{-- TAB NAVIGATION --}}
            <div class="tab-nav">
                <button class="tab-btn active" id="tab-users-btn" onclick="switchTab('users')">
                    👥 Manajemen User
                </button>
                <button class="tab-btn" id="tab-rooms-btn" onclick="switchTab('rooms')">
                    🏢 Manajemen Ruangan
                </button>
            </div>

            {{-- ===== TAB: USER ===== --}}
            <div id="tab-users">

                {{-- Filter --}}
                <div class="filter-bar" style="margin-bottom:16px;">
                    <form method="GET" action="{{ route('admin.users.index') }}"
                        style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;width:100%;">
                        <input type="text" name="search" value="{{ request('search') }}" class="search-input"
                            placeholder="Cari nama / username..." />
                        <select name="role" class="role-select" style="-webkit-appearance:none;appearance:none;">
                            <option value="">Semua Role</option>
                            @foreach($roles as $r)
                                <option value="{{ $r->name }}" {{ request('role') == $r->name ? 'selected' : '' }}>{{ $r->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn-search">Cari</button>
                        @if(request('search') || request('role'))
                            <a href="{{ route('admin.users.index') }}"
                                style="font-size:12px;color:#94a3b8;text-decoration:none;">Reset</a>
                        @endif
                        <span style="margin-left:auto;font-size:12px;color:#94a3b8;">{{ $users->total() }} user</span>
                    </form>
                </div>

                {{-- Table --}}
                <div class="user-table-wrap">
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Ruangan (TU)</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $u)
                                <tr>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:10px;">
                                            <div class="avatar">{{ strtoupper(substr($u->name, 0, 1)) }}</div>
                                            <div>
                                                <div style="font-weight:600;">{{ $u->name }}</div>
                                                @if($u->email)
                                                    <div style="font-size:11px;color:#94a3b8;">{{ $u->email }}</div>
                                                @endif
                                                @if($u->hasRole('TU'))
                                                    @if($u->phone)
                                                        @php
                                                            $waPhone = preg_replace('/^0/', '62', $u->phone);
                                                            $waPhone = ltrim($waPhone, '+');
                                                        @endphp
                                                        <a href="https://wa.me/{{ $waPhone }}" target="_blank"
                                                            style="font-size:11px;color:#15803d;font-weight:600;text-decoration:none;">
                                                            💬 {{ $u->phone }}
                                                        </a>
                                                    @else
                                                        <span style="font-size:11px;color:#fbbf24;">⚠ No. WA belum diisi</span>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <span style="font-size:13px;color:#6366f1;font-weight:500;">{{ $u->username ?? '—' }}</span>
                                    </td>

                                    <td>
                                        <span style="font-size:12px;font-weight:600;padding:3px 10px;border-radius:20px;background:#eef2ff;color:#4f46e5;">
                                            {{ $u->roles->first()?->name ?? '—' }}
                                        </span>
                                    </td>

                                    <td>
                                        @if($u->hasRole('TU'))
                                            <form method="POST" action="{{ route('admin.users.updateRoom', $u) }}"
                                                style="display:flex;gap:6px;align-items:center;">
                                                @csrf @method('PATCH')
                                                <select name="room_id" class="inline-select">
                                                    <option value="">— Belum ditugaskan —</option>
                                                    @foreach($rooms as $r)
                                                        <option value="{{ $r->id }}" {{ $u->room_id == $r->id ? 'selected' : '' }}>
                                                            {{ $r->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="btn-save">Simpan</button>
                                            </form>
                                        @else
                                            <span style="font-size:12px;color:#cbd5e1;">—</span>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="aksi-group">
                                            <button type="button" class="btn-edit" onclick="openEditModal(
                                                        '{{ addslashes($u->name) }}',
                                                        '{{ addslashes($u->username ?? '') }}',
                                                        '{{ addslashes($u->email ?? '') }}',
                                                        '{{ addslashes($u->phone ?? '') }}',
                                                        {{ $u->hasRole('TU') ? 'true' : 'false' }},
                                                        '{{ route('admin.users.updateProfile', $u) }}'
                                                    )">
                                                Edit
                                            </button>
                                            <button type="button" class="btn-pwd"
                                                onclick="openPwdModal('{{ addslashes($u->name) }}', '{{ route('admin.users.updatePassword', $u) }}')">
                                                Password
                                            </button>
                                            @if($u->id !== auth()->id())
                                                <form method="POST" action="{{ route('admin.users.destroy', $u) }}"
                                                    onsubmit="return confirm('Hapus user {{ addslashes($u->name) }}?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn-del">Hapus</button>
                                                </form>
                                            @else
                                                <span style="font-size:11px;color:#cbd5e1;">Akun Anda</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="empty-state">Tidak ada user ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    @if($users->hasPages())
                        <div class="pagination-wrap">{{ $users->links() }}</div>
                    @endif
                </div>

            </div>{{-- end tab-users --}}

            {{-- ===== TAB: ROOMS ===== --}}
            <div id="tab-rooms" style="display:none;">

                <div style="margin-bottom:12px;font-size:13px;color:#64748b;">
                    Nonaktifkan ruangan agar <strong>tidak bisa dibooking</strong> dan muncul label
                    <span style="background:#fff7ed;color:#c2410c;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:700;border:1px solid #fed7aa;">🔧 Perbaikan</span>
                    di kalender.
                </div>

                <div class="room-table-wrap">
                    <table class="room-table">
                        <thead>
                            <tr>
                                <th>Ruangan</th>
                                <th>Status</th>
                                <th>Mode Perbaikan</th>
                                <th>Catatan Perbaikan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(\App\Models\Room::orderBy('id')->get() as $room)
                                <tr id="room-row-{{ $room->id }}">
                                    <td>
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            @php
                                                $roomDotColors = [
                                                    1 => '#1a1a1a', 2 => '#a855f7', 3 => '#92400e',
                                                    4 => '#facc15', 5 => '#22d3ee', 6 => '#ef4444',
                                                    7 => '#ec4899', 8 => '#468432',
                                                ];
                                            @endphp
                                            <span style="width:10px;height:10px;border-radius:50%;background:{{ $roomDotColors[$room->id] ?? '#9ca3af' }};flex-shrink:0;display:inline-block;"></span>
                                            <span style="font-weight:600;">{{ $room->name }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($room->maintenance)
                                            <span class="maintenance-badge">🔧 Perbaikan</span>
                                        @elseif($room->active)
                                            <span class="active-badge">✓ Aktif</span>
                                        @else
                                            <span style="font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;background:#f1f5f9;color:#94a3b8;">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <label class="toggle-wrap" style="cursor:pointer;">
                                            <div class="toggle-switch">
                                                <input type="checkbox"
                                                    id="toggle-{{ $room->id }}"
                                                    {{ $room->maintenance ? 'checked' : '' }}
                                                    onchange="toggleMaintenance({{ $room->id }}, this.checked)">
                                                <span class="toggle-slider"></span>
                                            </div>
                                            <span style="font-size:12px;color:#64748b;" id="toggle-label-{{ $room->id }}">
                                                {{ $room->maintenance ? 'Sedang perbaikan' : 'Normal' }}
                                            </span>
                                        </label>
                                    </td>
                                    <td>
                                        <input type="text"
                                            class="note-input"
                                            id="note-{{ $room->id }}"
                                            value="{{ $room->maintenance_note ?? '' }}"
                                            placeholder="Keterangan perbaikan..."
                                            {{ !$room->maintenance ? 'disabled' : '' }}
                                            style="{{ !$room->maintenance ? 'opacity:0.4;' : '' }}" />
                                    </td>
                                    <td>
                                        <form method="POST"
                                            id="form-{{ $room->id }}"
                                            action="{{ route('admin.rooms.maintenance', $room) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="maintenance" id="val-maintenance-{{ $room->id }}"
                                                value="{{ $room->maintenance ? '1' : '0' }}">
                                            <input type="hidden" name="maintenance_note" id="val-note-{{ $room->id }}"
                                                value="{{ $room->maintenance_note ?? '' }}">
                                            <button type="button"
                                                class="btn-save"
                                                style="background:#f97316;"
                                                onmouseover="this.style.background='#ea580c'"
                                                onmouseout="this.style.background='#f97316'"
                                                onclick="submitMaintenance({{ $room->id }})">
                                                Simpan
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>{{-- end tab-rooms --}}

        </div>
    </div>

    {{-- Modal Tambah User --}}
    <div id="modalTambah" class="modal-overlay" style="display:none;">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-title">Tambah User Baru</div>
                <button type="button" onclick="document.getElementById('modalTambah').style.display='none'"
                    style="background:#f1f5f9;border:none;border-radius:8px;padding:5px 10px;cursor:pointer;font-size:14px;color:#64748b;">✕</button>
            </div>
            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <div class="modal-body">
                    <div>
                        <label class="field-label">Nama</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="field-input"
                            placeholder="Nama lengkap" />
                    </div>
                    <div>
                        <label class="field-label">Username</label>
                        <input type="text" name="username" value="{{ old('username') }}" required class="field-input"
                            placeholder="Contoh: budi123" />
                    </div>
                    <div>
                        <label class="field-label">Password</label>
                        <input type="password" name="password" required class="field-input"
                            placeholder="Min. 8 karakter" />
                    </div>
                    <div>
                        <label class="field-label">Role</label>
                        <select name="role" required class="field-input" id="roleSelect" onchange="toggleTuFields()">
                            <option value="" disabled selected>— Pilih role —</option>
                            @foreach($roles as $r)
                                <option value="{{ $r->name }}">{{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="roomField" style="display:none;">
                        <label class="field-label">Room (khusus TU)</label>
                        <select name="room_id" class="field-input">
                            <option value="">— Belum ditugaskan —</option>
                            @foreach($rooms as $r)
                                <option value="{{ $r->id }}">{{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="emailField" style="display:none;">
                        <label class="field-label">Email (opsional)</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="field-input"
                            placeholder="email@domain.com" />
                    </div>
                    <div id="phoneField" style="display:none;">
                        <label class="field-label">Nomor WhatsApp (khusus TU)</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="field-input"
                            placeholder="08xxxxxxxxxx" />
                        <p style="font-size:11px;color:#94a3b8;margin-top:4px;">Untuk menerima notifikasi booking masuk via WA.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel-modal"
                        onclick="document.getElementById('modalTambah').style.display='none'">Batal</button>
                    <button type="submit" class="btn-submit-modal">Tambah User</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit Profil --}}
    <div id="modalEdit" class="modal-overlay" style="display:none;">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-title">Edit Profil User</div>
                <button type="button" onclick="document.getElementById('modalEdit').style.display='none'"
                    style="background:#f1f5f9;border:none;border-radius:8px;padding:5px 10px;cursor:pointer;font-size:14px;color:#64748b;">✕</button>
            </div>
            <form method="POST" id="formEdit" action="">
                @csrf @method('PATCH')
                <div class="modal-body">
                    <div>
                        <label class="field-label">Nama</label>
                        <input type="text" name="name" id="editName" required class="field-input"
                            placeholder="Nama lengkap" />
                    </div>
                    <div>
                        <label class="field-label">Username</label>
                        <input type="text" name="username" id="editUsername" required class="field-input"
                            placeholder="Contoh: budi123" />
                    </div>
                    <div id="editEmailField" style="display:none;">
                        <label class="field-label">Email (opsional)</label>
                        <input type="email" name="email" id="editEmail" class="field-input"
                            placeholder="email@domain.com" />
                    </div>
                    <div id="editPhoneField" style="display:none;">
                        <label class="field-label">Nomor WhatsApp (khusus TU)</label>
                        <input type="text" name="phone" id="editPhone" class="field-input" placeholder="08xxxxxxxxxx" />
                        <p style="font-size:11px;color:#94a3b8;margin-top:4px;">Untuk menerima notifikasi booking masuk via WA.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel-modal"
                        onclick="document.getElementById('modalEdit').style.display='none'">Batal</button>
                    <button type="submit" class="btn-submit-modal">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Reset Password --}}
    <div id="modalPwd" class="modal-overlay" style="display:none;">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-title">Reset Password</div>
                <button type="button" onclick="document.getElementById('modalPwd').style.display='none'"
                    style="background:#f1f5f9;border:none;border-radius:8px;padding:5px 10px;cursor:pointer;font-size:14px;color:#64748b;">✕</button>
            </div>
            <form method="POST" id="formPwd" action="">
                @csrf @method('PATCH')
                <div class="modal-body">
                    <p id="pwdUserLabel" style="font-size:13px;color:#64748b;"></p>
                    <div>
                        <label class="field-label">Password Baru</label>
                        <input type="password" name="password" required class="field-input"
                            placeholder="Min. 8 karakter" />
                    </div>
                    <div>
                        <label class="field-label">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" required class="field-input"
                            placeholder="Ulangi password" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel-modal"
                        onclick="document.getElementById('modalPwd').style.display='none'">Batal</button>
                    <button type="submit" class="btn-submit-modal">Reset Password</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // ===== TAB SWITCH =====
        function switchTab(tab) {
            document.getElementById('tab-users').style.display = tab === 'users' ? 'block' : 'none'
            document.getElementById('tab-rooms').style.display = tab === 'rooms' ? 'block' : 'none'
            document.getElementById('tab-users-btn').classList.toggle('active', tab === 'users')
            document.getElementById('tab-rooms-btn').classList.toggle('active', tab === 'rooms')
            // simpan tab aktif di sessionStorage agar tidak reset saat reload
            sessionStorage.setItem('adminTab', tab)
        }

        // Restore tab saat page load (misal setelah submit form)
        document.addEventListener('DOMContentLoaded', () => {
            const saved = sessionStorage.getItem('adminTab')
            if (saved === 'rooms') switchTab('rooms')
        })

        // ===== MAINTENANCE TOGGLE =====
        function toggleMaintenance(roomId, isChecked) {
            const label = document.getElementById('toggle-label-' + roomId)
            const noteInput = document.getElementById('note-' + roomId)
            label.textContent = isChecked ? 'Sedang perbaikan' : 'Normal'
            noteInput.disabled = !isChecked
            noteInput.style.opacity = isChecked ? '1' : '0.4'
            if (!isChecked) noteInput.value = ''
        }

        function submitMaintenance(roomId) {
            const isChecked = document.getElementById('toggle-' + roomId).checked
            const note = document.getElementById('note-' + roomId).value
            document.getElementById('val-maintenance-' + roomId).value = isChecked ? '1' : '0'
            document.getElementById('val-note-' + roomId).value = note
            // Simpan tab rooms agar tetap terbuka setelah submit
            sessionStorage.setItem('adminTab', 'rooms')
            document.getElementById('form-' + roomId).submit()
        }

        // ===== USER MODALS =====
        function toggleTuFields() {
            const role = document.getElementById('roleSelect').value
            const isTU = role === 'TU'
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