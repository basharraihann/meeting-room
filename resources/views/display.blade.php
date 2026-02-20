<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display – {{ $room?->name ?? 'Semua Ruang' }}</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            height: 100%;
            background: #f1f5f9;
            color: #0f172a;
            font-family: 'Plus Jakarta Sans', sans-serif;
            overflow: hidden;
        }

        .display-root {
            display: grid;
            grid-template-rows: auto 1fr;
            height: 100vh;
        }

        /* ---- TOP BAR ---- */
        .top-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 28px;
            background: #fff;
            border-bottom: 1.5px solid #e2e8f0;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
        }

        .top-bar-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .logo-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
            padding-right: 16px;
            border-right: 1.5px solid #e2e8f0;
        }

        .logo-wrap img {
            height: 36px;
            width: auto;
        }

        .logo-text {
            font-size: 10px;
            font-weight: 700;
            color: #64748b;
            line-height: 1.4;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .room-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        /* ---- DROPDOWN ---- */
        .room-dropdown-wrap {
            position: relative;
        }

        .room-dropdown-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 7px 14px 7px 10px;
            border-radius: 12px;
            border: 1.5px solid #e2e8f0;
            background: #f8fafc;
            cursor: pointer;
            font-family: inherit;
            transition: border-color 0.15s, background 0.15s;
        }

        .room-dropdown-btn:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }

        .room-dropdown-name {
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
        }

        .dropdown-chevron {
            font-size: 11px;
            color: #94a3b8;
            margin-left: 2px;
        }

        .room-dropdown-menu {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            background: #fff;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            min-width: 220px;
            z-index: 100;
            overflow: hidden;
            display: none;
        }

        .room-dropdown-menu.open {
            display: block;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            text-decoration: none;
            transition: background 0.1s;
        }

        .dropdown-item:hover {
            background: #f8fafc;
            color: #0f172a;
        }

        .dropdown-item.active {
            background: #eef2ff;
            color: #4f46e5;
        }

        .dropdown-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .room-status {
            font-size: 11px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 99px;
            letter-spacing: 0.05em;
        }

        .status-busy {
            background: #1e293b;
            color: #fff;
        }

        .status-free {
            background: #f1f5f9;
            color: #475569;
        }

        .status-soon {
            background: #334155;
            color: #fff;
        }

        .top-bar-right {
            text-align: right;
        }

        .clock {
            font-size: 28px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.03em;
            line-height: 1;
        }

        .date-str {
            font-size: 12px;
            color: #475569;
            margin-top: 3px;
            font-weight: 600;
        }

        /* ---- MAIN AREA ---- */
        .main-area {
            display: grid;
            grid-template-columns: 1fr 320px;
            overflow: hidden;
            gap: 0;
        }

        /* ---- CALENDAR PANEL ---- */
        .calendar-panel {
            padding: 20px 16px 20px 20px;
            background: #fff;
            margin: 16px 8px 16px 16px;
            border-radius: 20px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* FullCalendar light overrides */
        .fc {
            flex: 1;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            --fc-border-color: #e2e8f0;
            --fc-page-bg-color: transparent;
            --fc-today-bg-color: #f5f3ff;
            height: 100% !important;
        }

        .fc .fc-toolbar {
            padding-bottom: 12px;
        }

        .fc .fc-toolbar-title {
            font-size: 1.15rem !important;
            font-weight: 800 !important;
            color: #0f172a !important;
            letter-spacing: -0.02em;
        }

        .fc .fc-button {
            background: #f1f5f9 !important;
            border: none !important;
            color: #475569 !important;
            font-weight: 700 !important;
            font-size: 0.75rem !important;
            border-radius: 10px !important;
            padding: 6px 14px !important;
            box-shadow: none !important;
            font-family: inherit !important;
            transition: background 0.15s !important;
        }

        .fc .fc-button:hover {
            background: #e2e8f0 !important;
            color: #1e293b !important;
        }

        .fc .fc-today-button,
        .fc .fc-button-primary:not(:disabled).fc-button-active {
            background: #6366f1 !important;
            color: #fff !important;
        }

        .fc .fc-col-header-cell {
            background: #f8fafc !important;
            border-bottom: 2px solid #e2e8f0 !important;
            padding: 8px 0 !important;
        }

        .fc .fc-col-header-cell-cushion {
            font-size: 0.7rem !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.08em !important;
            color: #94a3b8 !important;
            text-decoration: none !important;
        }

        .fc .fc-day-today .fc-col-header-cell-cushion {
            color: #6366f1 !important;
        }

        .fc .fc-daygrid-day-number {
            font-size: 0.8rem !important;
            font-weight: 600 !important;
            color: #94a3b8 !important;
            text-decoration: none !important;
            padding: 4px 6px !important;
        }

        .fc .fc-day-today .fc-daygrid-day-number {
            color: #6366f1 !important;
            font-weight: 800 !important;
        }

        .fc td,
        .fc th {
            border-color: #e2e8f0 !important;
        }

        .fc .fc-scrollgrid {
            border-color: #e2e8f0 !important;
            border-radius: 12px !important;
        }

        .fc .fc-daygrid-day-events {
            overflow: visible !important;
        }

        .fc .fc-daygrid-event-harness {
            overflow: visible !important;
            z-index: 1;
            position: relative;
        }

        .fc .fc-daygrid-event-harness:hover {
            z-index: 10;
        }

        .fc .fc-daygrid-event {
            white-space: normal !important;
            overflow: visible !important;
        }

        .fc .fc-event-main {
            overflow: visible !important;
        }

        /* scrollbar */
        .fc-scroller::-webkit-scrollbar {
            width: 3px;
        }

        .fc-scroller::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 4px;
        }

        /* ---- AGENDA PANEL ---- */
        .agenda-panel {
            background: #fff;
            border-radius: 20px;
            margin: 16px 16px 16px 8px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .agenda-header {
            padding: 16px 18px 12px;
            border-bottom: 1.5px solid #f1f5f9;
        }

        .agenda-header-title {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #94a3b8;
        }

        .agenda-header-date {
            font-size: 15px;
            font-weight: 800;
            color: #0f172a;
            margin-top: 4px;
            letter-spacing: -0.02em;
        }

        .agenda-list {
            flex: 1;
            overflow-y: auto;
            padding: 12px;
        }

        .agenda-list::-webkit-scrollbar {
            width: 3px;
        }

        .agenda-list::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 4px;
        }

        .agenda-item {
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 8px;
            position: relative;
            overflow: hidden;
        }

        .agenda-item-time {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.02em;
            margin-bottom: 4px;
        }

        .agenda-item-title {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.3;
            word-break: break-word;
        }

        .agenda-item-room {
            font-size: 11px;
            margin-top: 4px;
            font-weight: 500;
        }

        .agenda-item-badge {
            display: inline-block;
            font-size: 9px;
            font-weight: 800;
            letter-spacing: 0.06em;
            padding: 2px 8px;
            border-radius: 99px;
            margin-top: 6px;
        }

        .badge-now {
            background: #1e293b;
            color: #fff;
        }

        .badge-soon {
            background: #334155;
            color: #fff;
        }

        .badge-later {
            background: #e2e8f0;
            color: #475569;
        }

        .no-agenda {
            padding: 40px 16px;
            text-align: center;
            color: #94a3b8;
            font-size: 13px;
            font-weight: 500;
        }

        .no-agenda-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }

        /* auto-refresh bar */
        .refresh-bar {
            height: 3px;
            background: #6366f1;
            animation: shrink 120s linear infinite;
            transform-origin: left;
            border-radius: 0 0 0 20px;
        }

        @keyframes shrink {
            from {
                transform: scaleX(1);
            }

            to {
                transform: scaleX(0);
            }
        }
    </style>
</head>

<body>
    <div class="display-root">

        {{-- TOP BAR --}}
        <div class="top-bar">
            <div class="top-bar-left">
                @php
                    $roomColors = [
                        1 => '#94a3b8',
                        2 => '#94a3b8',
                        3 => '#94a3b8',
                        4 => '#94a3b8',
                        5 => '#94a3b8',
                        6 => '#94a3b8',
                    ];
                    $dotColor = '#1e293b';

                    $now = now('Asia/Jakarta');
                    $currentBooking = $todayBookings->first(
                        fn($b) =>
                        $now->between(
                            \Carbon\Carbon::parse($b->start_at)->setTimezone('Asia/Jakarta'),
                            \Carbon\Carbon::parse($b->end_at)->setTimezone('Asia/Jakarta')
                        )
                    );
                    $nextBooking = $todayBookings->first(function ($b) use ($now) {
                        $start = \Carbon\Carbon::parse($b->start_at)->setTimezone('Asia/Jakarta');
                        return $start->gt($now) && $start->diffInMinutes($now) <= 30;
                    });
                @endphp

                {{-- LOGO --}}
                <div class="logo-wrap">
                    <img src="/images/logoheader.png" alt="Logo" onerror="this.style.display='none'">
                </div>

                {{-- ROOM DROPDOWN --}}
                <div class="room-dropdown-wrap" id="dropdownWrap">
                    @php
                        $btnDotColors = [
                            1 => '#94a3b8',
                            2 => '#14b8a6',
                            3 => '#8b5cf6',
                            4 => '#f59e0b',
                            5 => '#d946ef',
                            6 => '#f43f5e',
                        ];
                        $btnDot = $room ? ($btnDotColors[$room->id] ?? '#94a3b8') : '#6366f1';
                    @endphp
                    <button class="room-dropdown-btn" onclick="toggleDropdown()">
                        @if($room)
                            <div class="room-dot" style="background:{{ $btnDot }}"></div>
                        @endif
                        <span class="room-dropdown-name">{{ $room?->name ?? 'Semua Ruang' }}</span>
                        <span class="dropdown-chevron">▼</span>
                    </button>

                    <div class="room-dropdown-menu" id="dropdownMenu">
                        <a href="/display" class="dropdown-item {{ !$room ? 'active' : '' }}">
                            Semua Ruang
                        </a>
                        @foreach(\App\Models\Room::orderBy('id')->get() as $r)
                            @php
                                $dotColorMap = [
                                    1 => '#94a3b8',
                                    2 => '#14b8a6',
                                    3 => '#8b5cf6',
                                    4 => '#f59e0b',
                                    5 => '#d946ef',
                                    6 => '#f43f5e',
                                ];
                                $dc = $dotColorMap[$r->id] ?? '#94a3b8';
                            @endphp
                            <a href="/display/{{ $r->id }}"
                                class="dropdown-item {{ $room?->id === $r->id ? 'active' : '' }}">
                                <div class="dropdown-dot" style="background:{{ $dc }}"></div>
                                {{ $r->name }}
                            </a>
                        @endforeach
                    </div>
                </div>

                @if($room)
                    @if($currentBooking)
                        <span class="room-status status-busy">● SEDANG DIGUNAKAN</span>
                    @elseif($nextBooking)
                        <span class="room-status status-soon">● SEGERA DIGUNAKAN</span>
                    @else
                        <span class="room-status status-free">● TERSEDIA</span>
                    @endif
                @endif
            </div>

            <div class="top-bar-right">
                <div class="clock" id="clock">00:00:00</div>
                <div class="date-str" id="dateStr">–</div>
            </div>
        </div>

        {{-- MAIN --}}
        <div class="main-area">

            {{-- CALENDAR --}}
            <div class="calendar-panel">
                <div id="calendar" style="height:100%"></div>
            </div>

            {{-- AGENDA --}}
            <div class="agenda-panel">
                <div class="agenda-header">
                    <div class="agenda-header-title">Agenda Hari Ini</div>
                    <div class="agenda-header-date" id="agendaDateStr">–</div>
                </div>

                <div class="agenda-list">
                    @forelse($todayBookings as $b)
                        @php
                            $bStart = \Carbon\Carbon::parse($b->start_at)->setTimezone('Asia/Jakarta');
                            $bEnd = \Carbon\Carbon::parse($b->end_at)->setTimezone('Asia/Jakarta');
                            $isNow = $now->between($bStart, $bEnd);
                            $isSoon = !$isNow && $bStart->gt($now) && $bStart->diffInMinutes($now) <= 30;

                            $rid = $b->room_id ?? 0;
                            $bgMap = [
                                1 => '#f8fafc',
                                2 => '#f8fafc',
                                3 => '#f8fafc',
                                4 => '#f8fafc',
                                5 => '#f8fafc',
                                6 => '#f8fafc',
                            ];
                            $timeColorMap = [
                                1 => '#64748b',
                                2 => '#64748b',
                                3 => '#64748b',
                                4 => '#64748b',
                                5 => '#64748b',
                                6 => '#64748b',
                            ];
                            $itemBg = '#f8fafc';
                            $barColor = $roomColors[$rid] ?? '#334155';
                            $timeColor = '#64748b';
                        @endphp

                        @php
                            $agendaDotColors = [
                                1 => '#94a3b8',
                                2 => '#14b8a6',
                                3 => '#8b5cf6',
                                4 => '#f59e0b',
                                5 => '#d946ef',
                                6 => '#f43f5e',
                            ];
                            $agendaDot = $agendaDotColors[$rid] ?? '#94a3b8';
                        @endphp
                        <div class="agenda-item" style="background:#fff; border: 1.5px solid #e2e8f0;">

                            <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px;">
                                <span
                                    style="width:8px;height:8px;border-radius:50%;background:{{ $agendaDot }};flex-shrink:0;display:inline-block;"></span>
                                <span class="agenda-item-time" style="color:#64748b;">{{ $bStart->format('H:i') }} –
                                    {{ $bEnd->format('H:i') }}</span>
                            </div>

                            <div class="agenda-item-title">{{ $b->title }}</div>

                            <div class="agenda-item-room" style="color:#94a3b8;">
                                @if(!$room){{ $b->room?->name ?? '-' }} · @endif
                                PIC: <strong>{{ $b->pic?->name ?? '-' }}</strong>
                            </div>

                            @if($isNow)
                                <span class="agenda-item-badge badge-now">BERLANGSUNG</span>
                            @elseif($isSoon)
                                <span class="agenda-item-badge badge-soon">SEGERA</span>
                            @else
                                <span class="agenda-item-badge badge-later">SELESAI</span>
                            @endif
                        </div>
                    @empty
                        <div class="no-agenda">
                            <div class="no-agenda-icon">📭</div>
                            <div>Tidak ada rapat hari ini</div>
                        </div>
                    @endforelse
                </div>

                <div class="refresh-bar"></div>
            </div>

        </div>
    </div>

    <script>
        // CLOCK
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']

        function updateClock() {
            const now = new Date()
            const hh = String(now.getHours()).padStart(2, '0')
            const mm = String(now.getMinutes()).padStart(2, '0')
            const ss = String(now.getSeconds()).padStart(2, '0')
            document.getElementById('clock').textContent = `${hh}:${mm}:${ss}`
            const str = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`
            document.getElementById('dateStr').textContent = str
            document.getElementById('agendaDateStr').textContent = str
        }

        updateClock()
        setInterval(updateClock, 1000)
        setTimeout(() => location.reload(), 2 * 60 * 1000)

        // DROPDOWN
        function toggleDropdown() {
            const menu = document.getElementById('dropdownMenu')
            menu.classList.toggle('open')
        }

        document.addEventListener('click', function (e) {
            const wrap = document.getElementById('dropdownWrap')
            if (wrap && !wrap.contains(e.target)) {
                document.getElementById('dropdownMenu').classList.remove('open')
            }
        })

        // FULLCALENDAR
        const roomColors = {
            1: { bg: '#f1f5f9', text: '#1e293b', time: '#64748b' },
            2: { bg: '#f1f5f9', text: '#1e293b', time: '#64748b' },
            3: { bg: '#f1f5f9', text: '#1e293b', time: '#64748b' },
            4: { bg: '#f1f5f9', text: '#1e293b', time: '#64748b' },
            5: { bg: '#f1f5f9', text: '#1e293b', time: '#64748b' },
            6: { bg: '#f1f5f9', text: '#1e293b', time: '#64748b' },
        }

        document.addEventListener('DOMContentLoaded', function () {
            const el = document.getElementById('calendar')
            if (!el) return

            const roomId = '{{ $room?->id ?? '' }}'

            const calendar = new FullCalendar.Calendar(el, {
                locale: 'id',
                initialView: 'dayGridMonth',
                headerToolbar: { left: 'prev,next today', center: 'title', right: '' },
                buttonText: { today: 'Hari ini' },
                eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
                events: {
                    url: '/api/bookings',
                    extraParams: () => roomId ? { room_id: roomId } : {},
                },
                eventContent(arg) {
                    const p = arg.event.extendedProps || {}
                    const color = roomColors[p.room_id] || { bg: '#f1f5f9', text: '#374151', time: '#6b7280' }

                    const fmt = (d) => {
                        if (!d) return ''
                        const dt = new Date(d)
                        return `${String(dt.getHours()).padStart(2, '0')}.${String(dt.getMinutes()).padStart(2, '0')}`
                    }

                    const range = `${fmt(arg.event.start)} – ${fmt(arg.event.end)}`

                    const pic = p.pic ?? ''

                    const dotColors = {
                        1: '#94a3b8', 2: '#14b8a6', 3: '#8b5cf6',
                        4: '#f59e0b', 5: '#d946ef', 6: '#f43f5e'
                    }
                    const dot = dotColors[p.room_id] || '#94a3b8'

                    return {
                        html: `
                        <div style="background:#fff;border-radius:8px;padding:5px 8px;border:1px solid #e2e8f0;">
                            <div style="display:flex;align-items:center;gap:5px;margin-bottom:2px;">
                                <span style="width:7px;height:7px;border-radius:50%;background:${dot};flex-shrink:0;display:inline-block;"></span>
                                <span style="font-size:11px;font-weight:700;color:#64748b;">${range}</span>
                            </div>
                            <div style="font-size:13px;font-weight:700;color:#0f172a;line-height:1.3;word-break:break-word;">${arg.event.title}</div>
                            ${pic ? `<div style="font-size:11px;color:#64748b;margin-top:2px;font-weight:700;">PIC: ${pic}</div>` : ''}
                        </div>
                    `
                    }
                },
                eventClick() { }
            })

            calendar.render()
        })
    </script>
</body>

</html>