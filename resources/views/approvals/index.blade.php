<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Approval Inbox (TU)
        </h2>
    </x-slot>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        .approvals-wrap {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .room-banner {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 20px;
            padding: 20px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .room-banner-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .room-banner-icon {
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .room-banner-title {
            font-size: 18px;
            font-weight: 800;
            color: #fff;
        }

        .room-banner-sub {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.75);
            margin-top: 2px;
        }

        .room-banner-badge {
            padding: 6px 16px;
            border-radius: 99px;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            font-size: 13px;
            font-weight: 700;
        }

        .content-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
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

        .cluster-right {
            display: flex;
            align-items: center;
            gap: 8px;
        }

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

        .cluster-body {
            padding: 16px;
        }

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

        .booking-card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.07);
        }

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

        .booking-card-meta b {
            color: #334155;
            font-weight: 600;
        }

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

        .btn-approve,
        .btn-reject {
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

        .btn-approve {
            background: #dcfce7;
            color: #15803d;
        }

        .btn-approve:hover {
            background: #16a34a;
            color: #fff;
        }

        .btn-reject {
            background: #fee2e2;
            color: #b91c1c;
        }

        .btn-reject:hover {
            background: #dc2626;
            color: #fff;
        }

        .empty-state {
            padding: 60px 24px;
            text-align: center;
            color: #94a3b8;
        }

        .empty-icon {
            font-size: 40px;
            margin-bottom: 12px;
        }

        .empty-text {
            font-size: 14px;
            font-weight: 500;
        }

        .no-room-state {
            padding: 60px 24px;
            text-align: center;
            color: #94a3b8;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
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

        .modal-box {
            background: #fff;
            width: 100%;
            max-width: 460px;
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
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
        }

        .modal-sub {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 2px;
        }

        .modal-body {
            padding: 20px 24px;
        }

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

        .modal-textarea:focus {
            border-color: #dc2626;
        }

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

        .btn-modal-close:hover {
            background: #e2e8f0;
        }

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

        .btn-modal-reject:hover {
            background: #b91c1c;
        }
    </style>

    <div class="py-6 approvals-wrap">
        <div class="mx-auto sm:px-6 lg:px-8 space-y-4" style="max-width:90%">

            @if(session('status'))
                <div class="alert-success">✓ {{ session('status') }}</div>
            @endif
            @if($errors->any())
                <div class="alert-error">⚠ {{ $errors->first() }}</div>
            @endif

            {{-- Belum ditugaskan ke room --}}
            @if($noRoom)
                <div class="no-room-state">
                    <div class="empty-icon">🏢</div>
                    <div class="empty-text">Anda belum ditugaskan ke ruangan manapun.</div>
                    <p style="font-size:12px;margin-top:8px;color:#cbd5e1;">Hubungi admin untuk mendapatkan penugasan
                        ruangan.</p>
                </div>
            @else
                {{-- Room Banner --}}
                <div class="room-banner">
                    <div class="room-banner-left">
                        <div class="room-banner-icon">
                            <svg width="24" height="24" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <div class="room-banner-title">{{ $assignedRoom->name }}</div>
                            <div class="room-banner-sub">Ruangan yang Anda kelola</div>
                        </div>
                    </div>
                    @php $totalPending = $clusters->sum(fn($c) => $c['items']->count()); @endphp
                    <div class="room-banner-badge">
                        {{ $totalPending }} request pending
                    </div>
                </div>

                {{-- Content --}}
                <div class="content-card">
                    <div class="content-header">
                        <div class="content-title">Pending Requests</div>
                        @if($totalPending > 0)
                            <span class="pending-count">{{ $totalPending }} menunggu</span>
                        @endif
                    </div>

                    @forelse($clusters as $c)
                        <div class="cluster">
                            <div class="cluster-header">
                                <div>
                                    <div class="cluster-room">{{ $c['room_name'] ?? '-' }}</div>
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

                            <div class="cluster-body">
                                <div class="booking-grid">
                                    @foreach($c['items'] as $b)
                                        <div class="booking-card">
                                            <div class="booking-card-top">
                                                <div class="booking-card-title">{{ $b->title }}</div>
                                                <span class="badge-pending">PENDING</span>
                                            </div>

                                            <div class="booking-card-meta">
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
                                                    <button type="submit" class="btn-approve" style="width:100%">✓ Approve</button>
                                                </form>

                                                <div x-data="{ open: false }" style="flex:1">
                                                    <button type="button" class="btn-reject" style="width:100%" @click="open=true">✕
                                                        Reject</button>

                                                    <div x-show="open" x-cloak
                                                        class="fixed inset-0 z-50 flex items-center justify-center">
                                                        <div class="absolute inset-0 bg-black/40" @click="open=false"></div>
                                                        <div class="modal-box" style="position:relative">
                                                            <div class="modal-header">
                                                                <div>
                                                                    <div class="modal-title">Reject Request</div>
                                                                    <div class="modal-sub">{{ $b->title }}</div>
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
                                                                    <button type="button" class="btn-modal-close"
                                                                        @click="open=false">Batal</button>
                                                                    <button type="submit" class="btn-modal-reject">Kirim
                                                                        Reject</button>
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
                            <div class="empty-text">Tidak ada request pending untuk ruangan ini.</div>
                        </div>
                    @endforelse
                </div>
            @endif

        </div>
    </div>
</x-app-layout>