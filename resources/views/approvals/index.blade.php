<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Approval Inbox (TU)
        </h2>
    </x-slot>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        .approvals-wrap { font-family: 'Plus Jakarta Sans', sans-serif; }

        /* ---- SIDEBAR ---- */
        .sidebar-card {
            background: #fff;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }

        .sidebar-title {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
        }

        .sidebar-sub {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 4px;
        }

        .sidebar-list { margin-top: 16px; display: flex; flex-direction: column; gap: 4px; }

        .sidebar-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 9px 12px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 500;
            color: #475569;
            text-decoration: none;
            transition: background 0.15s;
        }

        .sidebar-item:hover { background: #f8fafc; }

        .sidebar-item.active {
            background: #eef2ff;
            color: #4f46e5;
            font-weight: 700;
        }

        .sidebar-badge {
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 99px;
            background: #f1f5f9;
            color: #64748b;
        }

        .sidebar-badge.has-pending {
            background: #eef2ff;
            color: #4f46e5;
        }

        /* ---- MAIN CONTENT ---- */
        .content-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
            overflow: hidden;
        }

        .content-header {
            padding: 16px 24px;
            border-bottom: 1.5px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .content-title {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
        }

        .pending-count {
            font-size: 12px;
            font-weight: 700;
            color: #d97706;
            background: #fef3c7;
            padding: 3px 10px;
            border-radius: 99px;
        }

        /* ---- CLUSTER ---- */
        .cluster {
            border: 1.5px solid #f1f5f9;
            border-radius: 16px;
            overflow: hidden;
            margin: 16px;
        }

        .cluster-header {
            padding: 14px 18px;
            background: #f8fafc;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            border-bottom: 1.5px solid #f1f5f9;
        }

        .cluster-room {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
        }

        .cluster-time {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 3px;
        }

        .cluster-right { display: flex; align-items: center; gap: 8px; }

        .badge-bentrok {
            padding: 4px 12px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 700;
            background: #fef3c7;
            color: #b45309;
        }

        .badge-tunggal {
            padding: 4px 12px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 700;
            background: #f1f5f9;
            color: #64748b;
        }

        .bentrok-note {
            font-size: 11px;
            color: #b45309;
            font-weight: 600;
        }

        /* ---- BOOKING CARDS GRID ---- */
        .cluster-body { padding: 16px; }

        .booking-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 12px;
        }

        .booking-card {
            border: 1.5px solid #f1f5f9;
            border-radius: 14px;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            transition: box-shadow 0.15s;
        }

        .booking-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.07); }

        .booking-card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
        }

        .booking-card-title {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.3;
            word-break: break-word;
        }

        .badge-pending {
            padding: 3px 10px;
            border-radius: 99px;
            font-size: 10px;
            font-weight: 700;
            background: #fef9c3;
            color: #a16207;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .booking-card-meta {
            font-size: 12px;
            color: #64748b;
            line-height: 1.6;
        }

        .booking-card-meta b { color: #334155; font-weight: 600; }

        .booking-card-desc {
            font-size: 12px;
            color: #94a3b8;
            padding: 8px 10px;
            background: #f8fafc;
            border-radius: 8px;
            line-height: 1.5;
        }

        .booking-card-actions {
            display: flex;
            gap: 8px;
            margin-top: auto;
        }

        .btn-approve, .btn-reject {
            flex: 1;
            padding: 8px 12px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
            font-family: inherit;
            border: none;
            cursor: pointer;
            transition: all 0.15s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .btn-approve { background: #dcfce7; color: #15803d; }
        .btn-approve:hover { background: #16a34a; color: #fff; }

        .btn-reject { background: #fee2e2; color: #b91c1c; }
        .btn-reject:hover { background: #dc2626; color: #fff; }

        /* empty */
        .empty-state {
            padding: 60px 24px;
            text-align: center;
            color: #94a3b8;
        }

        .empty-icon { font-size: 40px; margin-bottom: 12px; }
        .empty-text { font-size: 14px; font-weight: 500; }

        /* alert */
        .alert-success {
            padding: 12px 16px;
            border-radius: 12px;
            background: #dcfce7;
            color: #15803d;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 16px;
        }

        .alert-error {
            padding: 12px 16px;
            border-radius: 12px;
            background: #fee2e2;
            color: #b91c1c;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 16px;
        }

        /* modal */
        .modal-box {
            background: #fff;
            width: 100%;
            max-width: 460px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
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

        .modal-title { font-size: 15px; font-weight: 700; color: #0f172a; }
        .modal-sub { font-size: 12px; color: #94a3b8; margin-top: 2px; }

        .modal-body { padding: 20px 24px; }

        .modal-label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 8px;
        }

        .modal-textarea {
            width: 100%;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 10px 14px;
            font-size: 13px;
            font-family: inherit;
            color: #1e293b;
            outline: none;
            resize: none;
            transition: border-color 0.2s;
            box-sizing: border-box;
        }

        .modal-textarea:focus { border-color: #dc2626; }

        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .btn-modal-close {
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

        .btn-modal-close:hover { background: #e2e8f0; }

        .btn-modal-reject {
            padding: 9px 18px;
            border-radius: 12px;
            background: #dc2626;
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            font-family: inherit;
            border: none;
            cursor: pointer;
        }

        .btn-modal-reject:hover { background: #b91c1c; }
    </style>

    <div class="py-6 approvals-wrap">
        <div class="mx-auto sm:px-6 lg:px-8" style="max-width:90%">

            @if(session('status'))
                <div class="alert-success">✓ {{ session('status') }}</div>
            @endif

            @if($errors->any())
                <div class="alert-error">⚠ {{ $errors->first() }}</div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

                {{-- Sidebar --}}
                <div class="lg:col-span-3">
                    <div class="sidebar-card">
                        <div class="sidebar-title">Filter Ruang</div>
                        <div class="sidebar-sub">Klik untuk melihat request per ruang.</div>

                        <div class="sidebar-list">
                            <a href="{{ route('approvals.index') }}"
                                class="sidebar-item {{ empty($roomId) ? 'active' : '' }}">
                                <span>Semua Ruang</span>
                                <span class="sidebar-badge {{ $pendingCounts->sum() ? 'has-pending' : '' }}">
                                    {{ $pendingCounts->sum() }}
                                </span>
                            </a>

                            @foreach($rooms as $r)
                                @php $cnt = (int)($pendingCounts[$r->id] ?? 0); @endphp
                                <a href="{{ route('approvals.index', ['room_id' => $r->id]) }}"
                                    class="sidebar-item {{ (string)$roomId === (string)$r->id ? 'active' : '' }}">
                                    <span>{{ $r->name }}</span>
                                    <span class="sidebar-badge {{ $cnt ? 'has-pending' : '' }}">{{ $cnt }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Content --}}
                <div class="lg:col-span-9">
                    <div class="content-card">
                        <div class="content-header">
                            <div class="content-title">Pending Requests</div>
                            @php $totalPending = $clusters->sum(fn($c) => $c['items']->count()); @endphp
                            @if($totalPending > 0)
                                <span class="pending-count">{{ $totalPending }} menunggu</span>
                            @endif
                        </div>

                        @forelse($clusters as $c)
                            <div class="cluster">
                                {{-- Cluster Header --}}
                                <div class="cluster-header">
                                    <div>
                                        <div class="cluster-room">{{ $c['room_name'] ?? 'Ruang #' . $c['room_id'] }}</div>
                                        <div class="cluster-time">
                                            {{ \Carbon\Carbon::parse($c['start'])->format('d M Y H:i') }}
                                            –
                                            {{ \Carbon\Carbon::parse($c['end'])->format('H:i') }}
                                        </div>
                                    </div>

                                    <div class="cluster-right">
                                        @php $count = $c['items']->count(); @endphp
                                        @if($count > 1)
                                            <span class="badge-bentrok">⚠ BENTROK ({{ $count }})</span>
                                            <span class="bentrok-note">Pilih salah satu untuk approve</span>
                                        @else
                                            <span class="badge-tunggal">TUNGGAL</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Booking Cards --}}
                                <div class="cluster-body">
                                    <div class="booking-grid">
                                        @foreach($c['items'] as $b)
                                            <div class="booking-card">
                                                <div class="booking-card-top">
                                                    <div class="booking-card-title">{{ $b->title }}</div>
                                                    <span class="badge-pending">PENDING</span>
                                                </div>

                                                <div class="booking-card-meta">
                                                    Ruang: <b>{{ $b->room->name }}</b><br>
                                                    PIC: <b>{{ $b->pic->name }}</b><br>
                                                    {{ \Carbon\Carbon::parse($b->start_at)->format('d M Y H:i') }}
                                                    –
                                                    {{ \Carbon\Carbon::parse($b->end_at)->format('H:i') }}
                                                </div>

                                                @if($b->description)
                                                    <div class="booking-card-desc">{{ $b->description }}</div>
                                                @endif

                                                <div class="booking-card-actions">
                                                    <form method="POST" action="{{ route('approvals.approve', $b) }}" style="flex:1">
                                                        @csrf
                                                        <button type="submit" class="btn-approve" style="width:100%">
                                                            ✓ Approve
                                                        </button>
                                                    </form>

                                                    <div x-data="{ open: false }" style="flex:1">
                                                        <button type="button" class="btn-reject" style="width:100%" @click="open=true">
                                                            ✕ Reject
                                                        </button>

                                                        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
                                                            <div class="absolute inset-0 bg-black/40" @click="open=false"></div>
                                                            <div class="modal-box" style="position:relative">
                                                                <div class="modal-header">
                                                                    <div>
                                                                        <div class="modal-title">Reject Request</div>
                                                                        <div class="modal-sub">{{ $b->title }} · {{ $b->room->name }}</div>
                                                                    </div>
                                                                    <button type="button" @click="open=false"
                                                                        style="background:#f1f5f9;border:none;border-radius:8px;padding:5px 10px;cursor:pointer;font-size:14px;color:#64748b;">✕</button>
                                                                </div>

                                                                <form method="POST" action="{{ route('approvals.reject', $b) }}">
                                                                    @csrf
                                                                    <div class="modal-body">
                                                                        <label class="modal-label">Alasan Reject</label>
                                                                        <textarea name="tu_note" required rows="3"
                                                                            class="modal-textarea"
                                                                            placeholder="Tulis alasan reject..."></textarea>
                                                                    </div>

                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn-modal-close" @click="open=false">Batal</button>
                                                                        <button type="submit" class="btn-modal-reject">Kirim Reject</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">
                                <div class="empty-icon">✅</div>
                                <div class="empty-text">Tidak ada request pending.</div>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>