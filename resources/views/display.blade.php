<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="120">
    <title>Display Jadwal – {{ $room?->name ?? 'Semua Ruang' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --indigo: #4F46E5;
            --indigo-dark: #3730A3;
            --indigo-light: #EEF2FF;
            --text: #0f172a;
            --muted: #64748b;
            --border: #e2e8f0;
            --bg: #f8fafc;
        }

        html,
        body {
            height: 100%;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ===== HEADER ===== */
        .display-header {
            background: white;
            border-bottom: 1.5px solid var(--border);
            padding: 14px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .header-logo img {
            height: 40px;
            width: auto;
        }

        .header-title {
            border-left: 2px solid var(--border);
            padding-left: 14px;
        }

        .header-title h1 {
            font-size: 16px;
            font-weight: 900;
            color: var(--text);
            letter-spacing: -0.02em;
        }

        .header-title p {
            font-size: 11px;
            color: var(--muted);
            font-weight: 500;
            margin-top: 2px;
        }

        /* ===== DROPDOWN ===== */
        .room-dropdown-wrap {
            position: relative;
        }

        .room-dropdown-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 14px;
            background: var(--indigo-light);
            border: 1.5px solid #c7d2fe;
            border-radius: 99px;
            font-size: 12px;
            font-weight: 800;
            color: var(--indigo);
            cursor: pointer;
            font-family: inherit;
            transition: all 0.15s;
            white-space: nowrap;
        }

        .room-dropdown-btn:hover {
            background: #e0e7ff;
            border-color: var(--indigo);
        }

        .room-dot-hdr {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .chevron {
            font-size: 10px;
            margin-left: 2px;
            transition: transform 0.15s;
        }

        .room-dropdown-btn.open .chevron {
            transform: rotate(180deg);
        }

        .room-dropdown-menu {
            display: none;
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            background: white;
            border: 1.5px solid var(--border);
            border-radius: 14px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            min-width: 200px;
            z-index: 100;
            overflow: hidden;
            padding: 6px;
        }

        .room-dropdown-menu.open {
            display: block;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 9px 12px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
            cursor: pointer;
            text-decoration: none;
            transition: background 0.12s;
        }

        .dropdown-item:hover {
            background: var(--bg);
        }

        .dropdown-item.active {
            background: var(--indigo-light);
            color: var(--indigo);
            font-weight: 800;
        }

        /* ===== STATUS HEADER BADGE ===== */
        .room-status-badge {
            display: inline-block;
            padding: 5px 14px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.05em;
            white-space: nowrap;
        }

        .hdr-berlangsung {
            background: #1e293b;
            color: white;
        }

        .hdr-segera {
            background: #fef9c3;
            color: #a16207;
        }

        .hdr-tersedia {
            background: #dcfce7;
            color: #15803d;
        }

        /* ===== CLOCK ===== */
        .header-right {
            text-align: right;
            flex-shrink: 0;
        }

        .clock-display {
            font-size: 32px;
            font-weight: 900;
            color: var(--text);
            letter-spacing: -0.04em;
            line-height: 1;
        }

        .date-display {
            font-size: 12px;
            font-weight: 700;
            color: var(--muted);
            margin-top: 3px;
        }

        /* ===== CONTENT ===== */
        .content {
            flex: 1;
            padding: 20px 28px 28px;
            overflow-y: auto;
        }

        /* ===== DAY SECTIONS ===== */
        .day-section {
            margin-bottom: 24px;
        }

        .day-header {
            padding: 10px 16px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            border-radius: 12px 12px 0 0;
            border: 1px solid var(--border);
            border-bottom: none;
        }

        .day-header.today {
            background: linear-gradient(135deg, #060074, #6c6ee5);
            color: white;
            border-color: var(--indigo);
        }

        .day-header.other {
            background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
            color: #475569;
        }

        .today-tag {
            font-weight: 500;
            font-size: 10px;
            text-transform: none;
            letter-spacing: 0;
            margin-left: 8px;
            opacity: 0.9;
        }

        .day-table-wrap {
            background: white;
            border: 1px solid var(--border);
            border-radius: 0 0 12px 12px;
            overflow: hidden;
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.04);
        }

        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .schedule-table thead tr {
            background: #f8fafc;
            border-bottom: 2px solid var(--border);
        }

        .schedule-table th {
            padding: 10px 16px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: var(--muted);
            text-align: left;
        }

        .schedule-table th:first-child {
            width: 48px;
            text-align: center;
        }

        .schedule-table th.th-room {
            width: 180px;
        }

        .schedule-table th.th-time {
            width: 140px;
        }

        .schedule-table th.th-pic {
            width: 120px;
        }

        .schedule-table th.th-status {
            width: 160px;
        }

        .schedule-table td {
            padding: 13px 16px;
            font-size: 13px;
            border-bottom: 1px solid #f1f5f9;
            color: var(--text);
            vertical-align: middle;
        }

        .schedule-table td:first-child {
            text-align: center;
            font-size: 12px;
            font-weight: 600;
            color: #94a3b8;
        }

        .schedule-table tbody tr:last-child td {
            border-bottom: none;
        }

        .schedule-table tbody tr:hover {
            background: #fafbff;
        }

        .booking-title {
            font-weight: 700;
            font-size: 13px;
            color: var(--text);
            line-height: 1.35;
        }

        .room-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 600;
            background: #f1f5f9;
            color: var(--muted);
            white-space: nowrap;
        }

        .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .time-cell {
            font-weight: 700;
            font-size: 13px;
            color: var(--indigo);
            white-space: nowrap;
        }

        /* STATUS PILLS */
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 99px;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.03em;
            white-space: nowrap;
        }

        .pill-berlangsung {
            background: #1e293b;
            color: white;
        }

        .pill-segera {
            background: #fef9c3;
            color: #a16207;
        }

        .pill-selesai {
            background: #f1f5f9;
            color: #94a3b8;
        }

        .pill-terjadwal {
            background: #dcfce7;
            color: #15803d;
        }

        .pill-pending {
            background: #fff7ed;
            color: #c2410c;
        }

        /* ===== REFRESH BAR ===== */
        .refresh-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--indigo);
            transform-origin: left;
            animation: shrink 120s linear forwards;
        }

        @keyframes shrink {
            from {
                transform: scaleX(1);
            }

            to {
                transform: scaleX(0);
            }
        }

        /* ===== EMPTY ===== */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #94a3b8;
            font-size: 14px;
            font-weight: 600;
        }

        .empty-state .icon {
            font-size: 36px;
            display: block;
            margin-bottom: 12px;
        }
    </style>
</head>

<body>

    @php
        $now = now('Asia/Jakarta');
        $dotColorMap = [
            1 => '#94a3b8',
            2 => '#14b8a6',
            3 => '#8b5cf6',
            4 => '#f59e0b',
            5 => '#d946ef',
            6 => '#f43f5e',
        ];
        $activeDot = $room ? ($dotColorMap[$room->id] ?? '#94a3b8') : '#6366f1';
        $rooms = \App\Models\Room::orderBy('id')->get();

        $currentBooking = isset($todayBookings) ? $todayBookings->first(
            fn($b) => $now->between(
                \Carbon\Carbon::parse($b->start_at)->setTimezone('Asia/Jakarta'),
                \Carbon\Carbon::parse($b->end_at)->setTimezone('Asia/Jakarta')
            )
        ) : null;

        $nextBooking = isset($todayBookings) ? $todayBookings->first(function ($b) use ($now) {
            $start = \Carbon\Carbon::parse($b->start_at)->setTimezone('Asia/Jakarta');
            return $start->gt($now) && $start->diffInMinutes($now) <= 30;
        }) : null;
    @endphp

    {{-- HEADER --}}
    <div class="display-header">
        <div class="header-left">
            <div class="header-logo">
                <img src="/images/logoheader.png" alt="Logo" onerror="this.style.display='none'">
            </div>
            <div class="header-title">
                <h1>Jadwal Ruang Rapat</h1>
                <p>Kementerian Koordinator Bidang Pangan</p>
            </div>

            {{-- DROPDOWN RUANGAN --}}
            <div class="room-dropdown-wrap">
                <button class="room-dropdown-btn" id="dropdownBtn" onclick="toggleDropdown()">
                    <span class="room-dot-hdr" style="background:{{ $activeDot }}"></span>
                    {{ $room?->name ?? 'Semua Ruang' }}
                    <span class="chevron">▼</span>
                </button>
                <div class="room-dropdown-menu" id="dropdownMenu">
                    <a href="/display" class="dropdown-item {{ !$room ? 'active' : '' }}">
                        <span class="dot" style="background:#6366f1"></span>
                        Semua Ruang
                    </a>
                    @foreach($rooms as $r)
                        @php $dc = $dotColorMap[$r->id] ?? '#94a3b8'; @endphp
                        <a href="/display/{{ $r->id }}" class="dropdown-item {{ $room?->id === $r->id ? 'active' : '' }}">
                            <span class="dot" style="background:{{ $dc }}"></span>
                            {{ $r->name }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- STATUS RUANGAN --}}
            @if($room)
                @if($currentBooking)
                    <span class="room-status-badge hdr-berlangsung">● SEDANG BERLANGSUNG</span>
                @elseif($nextBooking)
                    <span class="room-status-badge hdr-segera">● SEGERA DIGUNAKAN</span>
                @else
                    <span class="room-status-badge hdr-tersedia">● TERSEDIA</span>
                @endif
            @endif
        </div>

        <div class="header-right">
            <div class="clock-display" id="clock">00:00:00</div>
            <div class="date-display" id="dateStr">–</div>
        </div>
    </div>

    {{-- CONTENT --}}
    <div class="content" id="scheduleContent">
        <div class="empty-state"><span class="icon">⏳</span>Memuat jadwal...</div>
    </div>

    <div class="refresh-bar"></div>

    <script>
        const roomColors = {
            1: '#94a3b8', 2: '#14b8a6', 3: '#8b5cf6',
            4: '#f59e0b', 5: '#d946ef', 6: '#f43f5e',
        }
        const daysArr = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
        const monthsArr = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']

        // ===== DROPDOWN =====
        function toggleDropdown() {
            document.getElementById('dropdownBtn').classList.toggle('open')
            document.getElementById('dropdownMenu').classList.toggle('open')
        }
        document.addEventListener('click', function (e) {
            if (!document.querySelector('.room-dropdown-wrap').contains(e.target)) {
                document.getElementById('dropdownBtn').classList.remove('open')
                document.getElementById('dropdownMenu').classList.remove('open')
            }
        })

        // ===== CLOCK =====
        function updateClock() {
            const now = new Date()
            document.getElementById('clock').textContent =
                `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}:${String(now.getSeconds()).padStart(2, '0')}`
            document.getElementById('dateStr').textContent =
                `${daysArr[now.getDay()]}, ${now.getDate()} ${monthsArr[now.getMonth()]} ${now.getFullYear()}`
        }
        updateClock()
        setInterval(updateClock, 1000)

        // ===== HELPERS =====
        function toLocalDateStr(d) {
            return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
        }
        function fmtTime(isoStr) {
            const p = isoStr.replace('T', ' ').split(/[- :]/)
            return `${p[3]}.${p[4]}`
        }
        function isToday(dateStr) { return dateStr === toLocalDateStr(new Date()) }
        function escHtml(str) {
            if (!str) return ''
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;')
        }

        function getStatusPill(startIso, endIso, status) {
            if (status === 'PENDING') return `<span class="status-pill pill-pending">⏳ Menunggu</span>`
            const now = new Date()
            const s = new Date(startIso)
            const e = new Date(endIso)
            if (now >= s && now <= e) return `<span class="status-pill pill-berlangsung">● Sedang Berlangsung</span>`
            if (now < s && (s - now) <= 30 * 60 * 1000) return `<span class="status-pill pill-segera">⚡ Segera</span>`
            if (now > e) return `<span class="status-pill pill-selesai">✓ Selesai</span>`
            return `<span class="status-pill pill-terjadwal">✓ Terjadwal</span>`
        }

        // ===== LOAD & RENDER =====
        async function loadAndRender() {
            const start = new Date(); start.setHours(0, 0, 0, 0)
            const end = new Date(start); end.setDate(end.getDate() + 3)
            const startStr = toLocalDateStr(start)
            const endStr = toLocalDateStr(end)

            const roomId = '{{ $room?->id ?? '' }}'
            let url = `/api/display-bookings?start=${startStr}&end=${endStr}`
            if (roomId) url += `&room_id=${roomId}`

            try {
                const res = await fetch(url)
                const data = await res.json()

                const bookings = data.map(e => ({
                    title: e.title,
                    start: e.start?.replace(' ', 'T'),
                    end: e.end?.replace(' ', 'T'),
                    room_id: e.extendedProps?.room_id ?? e.room_id,
                    room_name: e.extendedProps?.room_name ?? e.room_name ?? null,
                    pic: e.extendedProps?.pic ?? e.pic ?? '-',
                    status: e.extendedProps?.status ?? e.status ?? 'APPROVED',
                }))

                const filtered = bookings
                    .filter(b => {
                        const bDate = b.start.split('T')[0]
                        return bDate >= startStr && bDate < endStr && ['APPROVED', 'PENDING'].includes(b.status)
                    })
                    .sort((a, b) => new Date(a.start) - new Date(b.start))

                const grouped = {}
                filtered.forEach(b => {
                    const key = b.start.split('T')[0]
                    if (!grouped[key]) grouped[key] = []
                    grouped[key].push(b)
                })

                const content = document.getElementById('scheduleContent')

                if (Object.keys(grouped).length === 0) {
                    content.innerHTML = `<div class="empty-state"><span class="icon">📭</span>Tidak ada jadwal dalam 3 hari ke depan</div>`
                    return
                }

                let html = ''
                Object.keys(grouped).sort().forEach(dateKey => {
                    const items = grouped[dateKey]
                    const d = new Date(dateKey + 'T00:00:00')
                    const todayFlag = isToday(dateKey)
                    const dayLabel = `${daysArr[d.getDay()]}, ${String(d.getDate()).padStart(2, '0')} ${monthsArr[d.getMonth()]} ${d.getFullYear()}`

                    html += `<div class="day-section">
                        <div class="day-header ${todayFlag ? 'today' : 'other'}">
                            ${escHtml(dayLabel)}
                            ${todayFlag ? '<span class="today-tag">● Hari ini</span>' : ''}
                        </div>
                        <div class="day-table-wrap">
                        <table class="schedule-table">
                            <thead><tr>
                                <th>No.</th>
                                <th>Nama Kegiatan</th>
                                <th class="th-room">Ruangan</th>
                                <th class="th-time">Waktu</th>
                                <th class="th-pic">PIC</th>
                                <th class="th-status">Status</th>
                            </tr></thead>
                            <tbody>`

                    items.forEach((b, i) => {
                        const dotColor = roomColors[b.room_id] || '#94a3b8'
                        const roomName = b.room_name || `Ruang ${b.room_id}`
                        html += `<tr>
                            <td>${i + 1}</td>
                            <td><div class="booking-title">${escHtml(b.title)}</div></td>
                            <td>
                                <span class="room-badge">
                                    <span class="dot" style="background:${dotColor}"></span>
                                    ${escHtml(roomName)}
                                </span>
                            </td>
                            <td class="time-cell">${fmtTime(b.start)} – ${fmtTime(b.end)}</td>
                            <td style="font-size:12px;font-weight:600;color:#475569;">${escHtml(b.pic ?? '-')}</td>
                            <td>${getStatusPill(b.start, b.end, b.status)}</td>
                        </tr>`
                    })

                    html += `</tbody></table></div></div>`
                })

                content.innerHTML = html

            } catch (err) {
                document.getElementById('scheduleContent').innerHTML =
                    `<div class="empty-state"><span class="icon">⚠️</span>Gagal memuat data jadwal</div>`
            }
        }

        loadAndRender()
        setInterval(loadAndRender, 60 * 1000)
    </script>
</body>

</html>