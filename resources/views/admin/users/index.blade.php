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
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
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
            transition: border-color 0.2s;
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
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
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
            letter-spacing: 0.06em;
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

        .badge-role {
            padding: 3px 10px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 700;
        }

        .badge-Admin {
            background: #fce7f3;
            color: #be185d;
        }

        .badge-TU {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge-PIC {
            background: #dcfce7;
            color: #15803d;
        }

        .badge-other {
            background: #f1f5f9;
            color: #475569;
        }

        .room-badge {
            padding: 3px 10px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 600;
            background: #f1f5f9;
            color: #64748b;
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
            transition: background 0.15s;
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
            transition: background 0.15s;
        }

        .btn-del:hover {
            background: #dc2626;
            color: #fff;
        }

        .alert-success {
            padding: 12px 16px;
            border-radius: 12px;
            background: #dcfce7;
            color: #15803d;
            font-size: 13px;
            font-weight: 600;
        }

        .alert-error {
            padding: 12px 16px;
            border-radius: 12px;
            background: #fee2e2;
            color: #b91c1c;
            font-size: 13px;
            font-weight: 600;
        }

        /* Modal */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-box {
            background: #fff;
            width: 100%;
            max-width: 480px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            margin: 16px;
        }

        .modal-header {
            padding: 18px 24px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-title {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
        }

        .modal-body {
            padding: 20px 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .field-label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 6px;
        }

        .field-input {
            width: 100%;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: 9px 14px;
            font-size: 13px;
            font-family: inherit;
            outline: none;
            transition: border-color 0.2s;
            box-sizing: border-box;
        }

        .field-input:focus {
            border-color: #6366f1;
        }

        .btn-cancel-modal {
            padding: 9px 18px;
            border-radius: 12px;
            background: #f1f5f9;
            color: #475569;
            font-size: 13px;
            font-weight: 600;
            font-family: inherit;
            border: none;
            cursor: pointer;
        }

        .btn-submit-modal {
            padding: 9px 18px;
            border-radius: 12px;
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
            padding: 16px 20px;
            border-top: 1px solid #f1f5f9;
        }

        .empty-state {
            padding: 48px;
            text-align: center;
            color: #94a3b8;
            font-size: 14px;
        }
    </style>

    <div class="py-6 admin-wrap">
        <div class="mx-auto sm:px-6 lg:px-8 space-y-4" style="max-width: 1100px;">

            @if(session('status'))
                <div class="alert-success">✓ {{ session('status') }}</div>
            @endif
            @if($errors->any())
                <div class="alert-error">⚠ {{ $errors->first() }}</div>
            @endif

            {{-- Filter Bar --}}
            <div class="filter-bar">
                <form method="GET" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                    <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama / email..."
                        class="search-input" />
                    <select name="role" class="role-select">
                        <option value="">Semua Role</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->name }}" {{ $filterRole === $r->name ? 'selected' : '' }}>{{ $r->name }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn-search">Cari</button>
                    @if($q || $filterRole)
                        <a href="{{ route('admin.users.index') }}"
                            style="font-size:13px;color:#94a3b8;text-decoration:none;">Reset</a>
                    @endif
                </form>
                <div style="font-size:13px;color:#94a3b8;">{{ $users->total() }} user</div>
            </div>

            {{-- Table --}}
            <div class="user-table-wrap">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Room (khusus TU)</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $u)
                            <tr>
                                {{-- User Info --}}
                                <td>
                                    <div style="display:flex;align-items:center;gap:10px;">
                                        <div class="avatar">{{ strtoupper(substr($u->name, 0, 1)) }}</div>
                                        <div>
                                            <div style="font-weight:700;font-size:13px;">{{ $u->name }}</div>
                                            <div style="font-size:11px;color:#94a3b8;">{{ $u->email }}</div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Role --}}
                                <td>
                                    <form method="POST" action="{{ route('admin.users.updateRole', $u) }}"
                                        style="display:flex;gap:6px;align-items:center;">
                                        @csrf @method('PATCH')
                                        <select name="role" class="inline-select">
                                            @foreach($roles as $r)
                                                <option value="{{ $r->name }}" {{ $u->hasRole($r->name) ? 'selected' : '' }}>
                                                    {{ $r->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn-save">Simpan</button>
                                    </form>
                                </td>

                                {{-- Room --}}
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

                                {{-- Aksi --}}
                                <td>
                                    @if($u->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $u) }}"
                                            onsubmit="return confirm('Hapus user {{ addslashes($u->name) }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-del">Hapus</button>
                                        </form>
                                    @else
                                        <span style="font-size:11px;color:#cbd5e1;">Akun Anda</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="empty-state">Tidak ada user ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($users->hasPages())
                    <div class="pagination-wrap">{{ $users->links() }}</div>
                @endif
            </div>

        </div>
    </div>

    {{-- Modal Tambah User --}}
    <div id="modalTambah" class="modal-overlay" style="display:none;">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-title">Tambah User Baru</div>
                <button type="button" onclick="document.getElementById('modalTambah').style.display = 'none'"
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
                        <label class="field-label">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required class="field-input"
                            placeholder="email@domain.com" />
                    </div>
                    <div>
                        <label class="field-label">Password</label>
                        <input type="password" name="password" required class="field-input"
                            placeholder="Min. 8 karakter" />
                    </div>
                    <div>
                        <label class="field-label">Role</label>
                        <select name="role" required class="field-input" id="roleSelect" onchange="toggleRoomField()">
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
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel-modal"
                        onclick="document.getElementById('modalTambah').style.display = 'none'">Batal</button>
                    <button type="submit" class="btn-submit-modal">Tambah User</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleRoomField() {
            const role = document.getElementById('roleSelect').value
            document.getElementById('roomField').style.display = role === 'TU' ? 'block' : 'none'
        }

        // Buka modal kalau ada error validasi
        @if($errors->any())
            document.getElementById('modalTambah').style.display = 'flex'
        @endif
    </script>

</x-app-layout>