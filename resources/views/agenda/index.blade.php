<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Agenda Saya
            </h2>
            <a href="{{ route('calendar') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium transition-colors">
                ← Kembali ke Calendar
            </a>
        </div>
    </x-slot>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        .agenda-wrap {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .filter-card {
            background: #fff;
            border-radius: 20px;
            padding: 20px 24px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            justify-content: space-between;
            gap: 16px;
        }

        .filter-group {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 10px;
        }

        .filter-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #94a3b8;
            margin-bottom: 6px;
        }

        .date-input {
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 9px 14px;
            font-size: 14px;
            font-family: inherit;
            color: #1e293b;
            outline: none;
            transition: border-color 0.2s;
        }

        .date-input:focus {
            border-color: #6366f1;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 18px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.15s;
            border: none;
            text-decoration: none;
        }

        .btn-primary {
            background: #6366f1;
            color: #fff;
        }

        .btn-primary:hover {
            background: #4f46e5;
            color: #fff;
        }

        .btn-active {
            background: #6366f1;
            color: #fff;
        }

        .btn-ghost {
            background: #f1f5f9;
            color: #475569;
        }

        .btn-ghost:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        .btn-copy {
            background: #10b981;
            color: #fff;
        }

        .btn-copy:hover {
            background: #059669;
        }

        .agenda-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        .agenda-card-header {
            padding: 16px 24px;
            border-bottom: 1.5px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .agenda-card-title {
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
        }

        .agenda-count {
            font-size: 12px;
            font-weight: 600;
            color: #6366f1;
            background: #eef2ff;
            padding: 3px 10px;
            border-radius: 99px;
        }

        .day-header {
            padding: 10px 24px;
            background: #f8fafc;
            font-size: 12px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid #f1f5f9;
        }

        .booking-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 24px;
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
            gap: 16px;
        }

        .room-bar {
            width: 4px;
            height: 44px;
            border-radius: 4px;
            flex-shrink: 0;
        }

        .booking-time {
            font-size: 13px;
            font-weight: 700;
            color: #6366f1;
            white-space: nowrap;
            min-width: 90px;
        }

        .booking-title {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.3;
        }

        .booking-meta {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 2px;
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

        .badge-default {
            background: #f1f5f9;
            color: #475569;
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
    </style>

    <div class="py-6 agenda-wrap">
        <div class="mx-auto sm:px-6 lg:px-8 space-y-4" style="max-width:90%">

            {{-- Filter Card --}}
            <div class="filter-card">
                <form method="GET" action="{{ route('agenda') }}" class="filter-group">
                    <input type="hidden" name="mode" value="{{ $mode ?? 'day' }}">

                    <div>
                        <div class="filter-label">Tanggal</div>
                        <input type="date" name="date" value="{{ $date }}" class="date-input" />
                    </div>

                    <button type="submit" class="btn btn-primary">Lihat</button>

                    <a href="{{ route('agenda', ['mode' => 'day', 'date' => now()->toDateString()]) }}"
                        class="btn {{ (($mode ?? 'day') === 'day') ? 'btn-active' : 'btn-ghost' }}">
                        Hari ini
                    </a>

                    <a href="{{ route('agenda', ['mode' => 'week', 'date' => now()->toDateString()]) }}"
                        class="btn {{ (($mode ?? 'day') === 'week') ? 'btn-active' : 'btn-ghost' }}">
                        Minggu ini
                    </a>

                    <a href="{{ route('agenda', ['mode' => 'month', 'date' => now()->toDateString()]) }}"
                        class="btn {{ (($mode ?? 'day') === 'month') ? 'btn-active' : 'btn-ghost' }}">
                        Bulan ini
                    </a>
                </form>

                <button type="button" class="btn btn-copy" onclick="copyAgenda()">
                    📋 Copy agenda
                </button>
            </div>

            {{-- Filter Unit Kerja --}}
            @if($unitKerjaOptions->isNotEmpty())
                <div class="filter-card" style="padding:14px 24px;">
                    <div style="display:flex;flex-wrap:wrap;gap:6px;align-items:center;">
                        <span
                            style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-right:2px;">Unit
                            Kerja:</span>
                        <a href="{{ route('agenda', ['mode' => $mode, 'date' => $date]) }}"
                            class="btn {{ !$unitKerja ? 'btn-active' : 'btn-ghost' }}"
                            style="padding:4px 12px;font-size:12px;">
                            Semua
                        </a>
                        @foreach($unitKerjaOptions as $uk)
                            <a href="{{ route('agenda', ['mode' => $mode, 'date' => $date, 'unit_kerja' => $uk]) }}"
                                class="btn {{ $unitKerja === $uk ? 'btn-active' : 'btn-ghost' }}"
                                style="padding:4px 12px;font-size:12px;">
                                {{ $uk }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Agenda List --}}
            <div class="agenda-card">
                <div class="agenda-card-header">
                    <div class="agenda-card-title">
                        Jadwal: {{ $title ?? \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}
                    </div>
                    @if(!$bookings->isEmpty())
                        <span class="agenda-count">{{ $bookings->count() }} rapat</span>
                    @endif
                </div>

                @if($bookings->isEmpty())
                    <div class="empty-state">
                        <div class="empty-icon">📭</div>
                        <div class="empty-text">Tidak ada rapat pada periode ini.</div>
                    </div>
                @else
                    @php
                        $isRange = in_array(($mode ?? 'day'), ['week', 'month']);
                        $grouped = $isRange
                            ? $bookings->groupBy(fn($b) => \Carbon\Carbon::parse($b->start_at)->toDateString())
                            : collect([\Carbon\Carbon::parse($date)->toDateString() => $bookings]);

                        $roomBarClass = [
                            1 => 'room-bar-1',
                            2 => 'room-bar-2',
                            3 => 'room-bar-3',
                            4 => 'room-bar-4',
                            5 => 'room-bar-5',
                            6 => 'room-bar-6',
                        ];
                    @endphp

                    @foreach($grouped as $day => $items)
                        @if($isRange)
                            <div class="day-header">
                                {{ \Carbon\Carbon::parse($day)->translatedFormat('l, d M Y') }}
                            </div>
                        @endif

                        @foreach($items as $b)
                            @php
                                $barClass = $roomBarClass[$b->room_id ?? 0] ?? 'room-bar-default';
                                $badge = match ($b->status) {
                                    'APPROVED' => 'badge-approved',
                                    'PENDING' => 'badge-pending',
                                    'REJECTED' => 'badge-rejected',
                                    default => 'badge-default',
                                };
                            @endphp

                            <div class="booking-row">
                                <div class="booking-left">
                                    <div class="room-bar {{ $barClass }}"></div>

                                    <div class="booking-time">
                                        {{ \Carbon\Carbon::parse($b->start_at)->format('H:i') }}
                                        –
                                        {{ \Carbon\Carbon::parse($b->end_at)->format('H:i') }}
                                    </div>

                                    <div>
                                        <div class="booking-title">{{ $b->title }}</div>
                                        <div class="booking-meta">
                                            {{ $b->room?->name ?? '-' }}
                                            @if($b->unit_kerja)
                                                · {{ $b->unit_kerja }}
                                            @endif
                                            @if($b->description)
                                                · {{ Str::limit($b->description, 60) }}
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <span class="badge {{ $badge }}">{{ $b->status }}</span>
                            </div>
                        @endforeach
                    @endforeach
                @endif
            </div>

        </div>
    </div>

    <textarea id="agendaText" class="hidden">{{ $summaryText }}</textarea>

    <script>
        function copyAgenda() {
            const text = document.getElementById('agendaText').value;
            navigator.clipboard.writeText(text).then(() => {
                alert('Agenda berhasil di-copy. Tinggal paste ke WA/Email pimpinan.');
            });
        }
    </script>
</x-app-layout>