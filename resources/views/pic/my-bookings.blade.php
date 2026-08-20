<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Riwayat Ajukan Rapat
            </h2>
            <a href="{{ route('calendar') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium transition-colors">
                ← Kembali ke Kalender
            </a>
        </div>
    </x-slot>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        .riwayat-wrap {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .filter-bar {
            background: #fff;
            border-radius: 20px;
            padding: 16px 20px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .tab-group {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .tab {
            padding: 7px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            font-family: inherit;
            text-decoration: none;
            transition: all 0.15s;
            cursor: pointer;
        }

        .tab-active {
            background: #6366f1;
            color: #fff;
        }

        .tab-ghost {
            background: #f1f5f9;
            color: #475569;
        }

        .tab-ghost:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        .search-group {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .search-input {
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 8px 14px;
            font-size: 13px;
            font-family: inherit;
            color: #1e293b;
            outline: none;
            width: 260px;
            transition: border-color 0.2s;
        }

        .search-input:focus {
            border-color: #6366f1;
        }

        .btn-search {
            padding: 8px 18px;
            border-radius: 12px;
            background: #6366f1;
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            font-family: inherit;
            border: none;
            cursor: pointer;
            transition: background 0.15s;
        }

        .btn-search:hover {
            background: #4f46e5;
        }

        .booking-list {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        .booking-list-header {
            padding: 14px 24px;
            border-bottom: 1.5px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .booking-list-title {
            font-size: 13px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .total-count {
            font-size: 12px;
            font-weight: 600;
            color: #6366f1;
            background: #eef2ff;
            padding: 3px 10px;
            border-radius: 99px;
        }

        .date-separator {
            padding: 8px 24px;
            background: #f8fafc;
            border-bottom: 1px solid #f1f5f9;
            border-top: 1px solid #f1f5f9;
        }

        .date-separator span {
            font-size: 11px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .date-separator.today span {
            color: #6366f1;
        }

        .booking-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 24px;
            border-bottom: 1px solid #f8fafc;
            transition: background 0.1s;
        }

        .booking-row:last-child {
            border-bottom: none;
        }

        .booking-row:hover {
            background: #fafafa;
        }

        .booking-left {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
        }

        .room-bar {
            width: 4px;
            height: 44px;
            border-radius: 4px;
            flex-shrink: 0;
        }

        .room-bar-1 {
            background: #94a3b8;
        }

        .room-bar-2 {
            background: #14b8a6;
        }

        .room-bar-3 {
            background: #8b5cf6;
        }

        .room-bar-4 {
            background: #f59e0b;
        }

        .room-bar-5 {
            background: #d946ef;
        }

        .room-bar-6 {
            background: #f43f5e;
        }

        .room-bar-default {
            background: #e2e8f0;
        }

        .booking-title {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.3;
        }

        .booking-meta {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 2px;
        }

        .booking-desc {
            font-size: 12px;
            color: #64748b;
            margin-top: 2px;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .booking-right {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .badge {
            padding: 4px 12px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.04em;
            white-space: nowrap;
        }

        .badge-approved {
            background: #dcfce7;
            color: #15803d;
        }

        .badge-pending {
            background: #fef9c3;
            color: #a16207;
        }

        .badge-rejected {
            background: #fee2e2;
            color: #b91c1c;
        }

        .badge-canceled {
            background: #f1f5f9;
            color: #64748b;
        }

        .badge-done {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge-default {
            background: #f1f5f9;
            color: #475569;
        }

        .btn-cancel {
            padding: 5px 14px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 700;
            background: #1e293b;
            color: #fff;
            border: none;
            cursor: pointer;
            transition: background 0.15s;
            font-family: inherit;
        }

        .btn-cancel:hover {
            background: #334155;
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

        .pagination-wrap {
            padding: 16px 24px;
            border-top: 1px solid #f1f5f9;
        }

        /* cancel modal */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
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

        .modal-close {
            background: #f1f5f9;
            border: none;
            border-radius: 8px;
            padding: 4px 10px;
            font-size: 16px;
            cursor: pointer;
            color: #64748b;
            font-family: inherit;
        }

        .modal-close:hover {
            background: #e2e8f0;
        }

        .modal-body {
            padding: 20px 24px;
        }

        .modal-label {
            display: block;
            font-size: 12px;
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
            border-color: #6366f1;
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
            transition: background 0.15s;
        }

        .btn-modal-close:hover {
            background: #e2e8f0;
        }

        .btn-modal-confirm {
            padding: 9px 18px;
            border-radius: 12px;
            background: #dc2626;
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            font-family: inherit;
            border: none;
            cursor: pointer;
            transition: background 0.15s;
        }

        .btn-modal-confirm:hover {
            background: #b91c1c;
        }
    </style>

    <div class="py-6 riwayat-wrap">
        <div class="mx-auto sm:px-6 lg:px-8 space-y-4" style="max-width:90%">

            {{-- Filter Bar --}}
            <div class="filter-bar" style="flex-direction:column;align-items:stretch;gap:10px;">
                {{-- Row 1: Status tabs + Search --}}
                <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px;">
                    <div class="tab-group">
                        @php
                            $tabs = [
                                '' => 'Semua',
                                'PENDING' => 'Pending',
                                'APPROVED' => 'Approved',
                                'REJECTED' => 'Rejected',
                                'CANCELED' => 'Canceled',
                            ];
                        @endphp
                        @foreach($tabs as $key => $label)
                            <a href="{{ route('my_bookings.index', array_filter(['status' => $key, 'q' => $q, 'unit_kerja' => $unitKerja])) }}"
                                class="tab {{ ($status === $key || (empty($status) && $key === '')) ? 'tab-active' : 'tab-ghost' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>

                    <form method="GET" class="search-group">
                        <input type="text" name="q" value="{{ $q }}" placeholder="Cari judul rapat..."
                            class="search-input" />
                        @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
                        @if($unitKerja)<input type="hidden" name="unit_kerja" value="{{ $unitKerja }}">@endif
                        <button type="submit" class="btn-search">Cari</button>
                    </form>
                </div>

                {{-- Row 2: Filter Unit Kerja --}}
                @if($unitKerjaOptions->isNotEmpty())
                    <div style="display:flex;flex-wrap:wrap;gap:6px;align-items:center;">
                        <span
                            style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-right:2px;">Unit
                            Kerja:</span>
                        <a href="{{ route('my_bookings.index', array_filter(['status' => $status, 'q' => $q])) }}"
                            class="tab {{ !$unitKerja ? 'tab-active' : 'tab-ghost' }}"
                            style="padding:4px 12px;font-size:12px;">
                            Semua
                        </a>
                        @foreach($unitKerjaOptions as $uk)
                            <a href="{{ route('my_bookings.index', array_filter(['status' => $status, 'q' => $q, 'unit_kerja' => $uk])) }}"
                                class="tab {{ $unitKerja === $uk ? 'tab-active' : 'tab-ghost' }}"
                                style="padding:4px 12px;font-size:12px;">
                                {{ $uk }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Booking List --}}
            <div class="booking-list">
                <div class="booking-list-header">
                    <div class="booking-list-title">Daftar Rapat</div>
                    @if($bookings->total() > 0)
                        <span class="total-count">{{ $bookings->total() }} total</span>
                    @endif
                </div>

                @php $lastDate = null;
                $todayStr = now()->format('Y-m-d'); @endphp

                @forelse($bookings as $b)
                    @php
                        $now = now();
                        $bDateStr = \Carbon\Carbon::parse($b->start_at)->format('Y-m-d');
                        $bDateLabel = \Carbon\Carbon::parse($b->start_at)->translatedFormat('l, d F Y');
                        $isToday = $bDateStr === $todayStr;

                        $displayStatus = strtoupper($b->status);
                        $isDone = ($b->status === 'APPROVED' && \Carbon\Carbon::parse($b->end_at)->lt($now));
                        if ($isDone)
                            $displayStatus = 'DONE';

                        $badge = match ($displayStatus) {
                            'APPROVED' => 'badge-approved',
                            'PENDING' => 'badge-pending',
                            'REJECTED' => 'badge-rejected',
                            'CANCELED' => 'badge-canceled',
                            'CANCELLED' => 'badge-canceled',
                            'DONE' => 'badge-done',
                            default => 'badge-default',
                        };

                        $roomBarClass = [
                            1 => 'room-bar-1',
                            2 => 'room-bar-2',
                            3 => 'room-bar-3',
                            4 => 'room-bar-4',
                            5 => 'room-bar-5',
                            6 => 'room-bar-6',
                        ][$b->room_id ?? 0] ?? 'room-bar-default';
                    @endphp

                    {{-- Date separator --}}
                    @if($bDateStr !== $lastDate)
                        @php $lastDate = $bDateStr; @endphp
                        <div class="date-separator {{ $isToday ? 'today' : '' }}">
                            <span>
                                {{ $bDateLabel }}
                                @if($isToday) &nbsp;· Hari ini @endif
                            </span>
                        </div>
                    @endif

                    <div class="booking-row">
                        <div class="booking-left">
                            <div class="room-bar {{ $roomBarClass }}"></div>
                            <div style="min-width:0">
                                <div class="booking-title">{{ $b->title }}</div>
                                <div class="booking-meta">
                                    {{ $b->room?->name ?? '-' }}
                                    @if($b->room?->tuUser?->phone && $b->status === 'PENDING')
                                        @php
                                            $tuPhone = preg_replace('/^0/', '62', $b->room->tuUser->phone);
                                            $tuPhone = ltrim($tuPhone, '+');
                                            $start = \Carbon\Carbon::parse($b->start_at)->translatedFormat('d F Y');
                                            $jam = \Carbon\Carbon::parse($b->start_at)->format('H:i') . ' - ' . \Carbon\Carbon::parse($b->end_at)->format('H:i');
                                            $waMsg = "Halo Bapak/Ibu {$b->room->tuUser->name},\n\n"
                                                . "Saya PIC {$b->unit_kerja} ingin mengkonfirmasi pengajuan peminjaman ruang rapat:\n\n"
                                                . "*{$b->title}*\n"
                                                . "Ruangan: {$b->room->name}\n"
                                                . "Tanggal: {$start}\n"
                                                . "Waktu: {$jam}\n\n"
                                                . "Mohon konfirmasinya apakah jadwal tersebut tersedia. Jika tersedia, mohon untuk melakukan approval di sistem.\n\nTerima kasih.";
                                            $waUrl = 'https://wa.me/' . $tuPhone . '?text=' . rawurlencode($waMsg);
                                        @endphp
                                        &nbsp;·&nbsp;
                                        <a href="{{ $waUrl }}" target="_blank"
                                            style="color:#25d366;font-weight:600;text-decoration:none;font-size:11px;"
                                            title="Chat TU {{ $b->room->tuUser->name }}">
                                            💬 Chat TU
                                        </a>
                                    @endif
                                    &nbsp;·&nbsp;
                                    {{ \Carbon\Carbon::parse($b->start_at)->format('H:i') }}
                                    –
                                    {{ \Carbon\Carbon::parse($b->end_at)->format('H:i') }}
                                    @if($b->unit_kerja)
                                        &nbsp;·&nbsp;{{ $b->unit_kerja }}
                                    @endif
                                </div>
                                @if($b->description)
                                    <div class="booking-desc">{{ $b->description }}</div>
                                @endif
                            </div>
                        </div>

                        <div class="booking-right">
                            <span class="badge {{ $badge }}">{{ $displayStatus }}</span>
                            @if(in_array($b->status, ['PENDING', 'APPROVED']) && !$isDone)
                                <button type="button" class="btn-cancel" onclick="openCancelModal({{ $b->id }})">
                                    Cancel
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <div class="empty-icon">📋</div>
                        <div class="empty-text">Belum ada riwayat rapat.</div>
                    </div>
                @endforelse

                @if($bookings->hasPages())
                    <div class="pagination-wrap">
                        {{ $bookings->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    {{-- Cancel Modal --}}
    <div id="cancelModal" class="modal-overlay" style="display:none;">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-title">Batalkan Booking</div>
                <button type="button" class="modal-close" onclick="closeCancelModal()">✕</button>
            </div>
            <form id="cancelForm" method="POST">
                @csrf
                <div class="modal-body">
                    <label class="modal-label">Alasan Pembatalan</label>
                    <textarea name="cancel_reason" rows="4" required class="modal-textarea"
                        placeholder="Masukkan alasan pembatalan..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-modal-close" onclick="closeCancelModal()">Tutup</button>
                    <button type="submit" class="btn-modal-confirm">Konfirmasi Batal</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCancelModal(bookingId) {
            document.getElementById('cancelForm').action = `/bookings/${bookingId}/cancel`;
            document.getElementById('cancelModal').style.display = 'flex';
        }
        function closeCancelModal() {
            document.getElementById('cancelModal').style.display = 'none';
        }
    </script>
</x-app-layout>