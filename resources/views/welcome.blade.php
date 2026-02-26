<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Jadwal Ruang Rapat – Kemenkopangan</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
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

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ===== NAV ===== */
        nav {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border);
            padding: 0 1.5rem;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
            gap: 12px;
        }

        .nav-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .nav-logo img {
            height: 36px;
            width: auto;
        }

        .nav-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--text);
            border-left: 1.5px solid var(--border);
            padding-left: 12px;
            line-height: 1.3;
        }

        .nav-title span {
            display: block;
            font-size: 11px;
            font-weight: 500;
            color: var(--muted);
        }

        .nav-right {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .btn-ghost {
            padding: 6px 14px;
            border: 1.5px solid var(--border);
            border-radius: 9px;
            font-size: 13px;
            font-weight: 600;
            color: var(--muted);
            text-decoration: none;
            background: white;
            transition: all 0.15s;
            white-space: nowrap;
        }

        .btn-ghost:hover {
            border-color: var(--indigo);
            color: var(--indigo);
        }

        .btn-solid {
            padding: 6px 16px;
            background: var(--indigo);
            border-radius: 9px;
            font-size: 13px;
            font-weight: 700;
            color: white;
            text-decoration: none;
            transition: all 0.15s;
            white-space: nowrap;
        }

        .btn-solid:hover {
            background: var(--indigo-dark);
        }

        /* ===== MAIN CONTENT ===== */
        .main {
            flex: 1;
            padding: 24px 1.5rem 40px;
            max-width: 1100px;
            margin: 0 auto;
            width: 100%;
        }

        /* ===== HEADER BAR ===== */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .page-title {
            font-size: 18px;
            font-weight: 800;
            color: var(--text);
            letter-spacing: -0.02em;
        }

        .page-subtitle {
            font-size: 12px;
            color: var(--muted);
            margin-top: 2px;
            font-weight: 500;
        }

        /* ===== FILTER TABS ===== */
        .filter-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .filter-label {
            font-size: 11px;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-right: 4px;
        }

        .filter-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 99px;
            border: 1.5px solid var(--border);
            background: white;
            font-size: 12px;
            font-weight: 600;
            color: var(--muted);
            cursor: pointer;
            transition: all 0.15s;
            font-family: inherit;
        }

        .filter-btn:hover {
            border-color: #94a3b8;
            color: var(--text);
        }

        .filter-btn.active {
            background: var(--indigo);
            border-color: var(--indigo);
            color: white;
        }

        .filter-btn.active .room-dot {
            background: white !important;
        }

        .room-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        /* ===== DATE RANGE TABS ===== */
        .range-tabs {
            display: flex;
            gap: 4px;
            background: #f1f5f9;
            border-radius: 10px;
            padding: 3px;
        }

        .range-tab {
            padding: 5px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            color: var(--muted);
            cursor: pointer;
            border: none;
            background: transparent;
            font-family: inherit;
            transition: all 0.15s;
        }

        .range-tab.active {
            background: white;
            color: var(--text);
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
        }

        /* ===== TABLE ===== */
        .table-wrap {
            background: white;
            border-radius: 16px;
            border: 1px solid var(--border);
            overflow: hidden;
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.04);
        }

        .schedule-table {
            width: 100%;
            border-collapse: collapse;
        }

        .schedule-table thead tr {
            background: #f8fafc;
            border-bottom: 2px solid var(--border);
        }

        .schedule-table th {
            padding: 11px 16px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: var(--muted);
            text-align: left;
        }

        .schedule-table th:first-child {
            width: 40px;
            text-align: center;
        }

        .schedule-table th.th-room {
            width: 160px;
            text-align: left;
        }

        .schedule-table td:nth-child(3) {
            text-align: left;
        }

        .schedule-table th.th-time {
            width: 130px;
        }

        .schedule-table th.th-pic {
            width: 130px;
        }

        .schedule-table th.th-status {
            width: 110px;
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

        /* Day separator row */
        .day-row td {
            padding: 8px 16px;
            background: #f8fafc;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: var(--muted);
            border-bottom: 1px solid var(--border);
        }

        .day-row.today td {
            color: var(--indigo);
            background: #f5f3ff;
        }

        .day-row td span {
            font-weight: 500;
            color: #94a3b8;
            margin-left: 6px;
            font-size: 10px;
            text-transform: none;
            letter-spacing: 0;
        }

        /* Title cell */
        .booking-title {
            font-weight: 700;
            font-size: 13px;
            color: var(--text);
            line-height: 1.3;
        }

        .booking-desc {
            font-size: 11px;
            color: var(--muted);
            margin-top: 2px;
        }

        /* Room badge */
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

        /* Status badge */
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 99px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.04em;
            white-space: nowrap;
        }

        .status-approved {
            background: #dcfce7;
            color: #15803d;
        }

        .status-pending {
            background: #fef9c3;
            color: #a16207;
        }

        /* Time cell */
        .time-cell {
            font-weight: 700;
            font-size: 12px;
            color: var(--indigo);
            white-space: nowrap;
        }

        /* Empty state */
        .empty-row td {
            text-align: center;
            padding: 48px 16px;
            color: #94a3b8;
            font-size: 13px;
            font-weight: 500;
        }

        .empty-icon {
            font-size: 28px;
            display: block;
            margin-bottom: 8px;
        }

        /* Loading state */
        .loading-row td {
            text-align: center;
            padding: 40px;
            color: var(--muted);
            font-size: 13px;
        }

        .spinner {
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 2px solid #e2e8f0;
            border-top-color: var(--indigo);
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            margin-right: 8px;
            vertical-align: middle;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Login CTA banner */
        .login-cta {
            margin-top: 16px;
            background: var(--indigo-light);
            border: 1px solid #c7d2fe;
            border-radius: 12px;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .login-cta-text {
            font-size: 13px;
            color: var(--indigo);
            font-weight: 600;
        }

        .login-cta-text span {
            font-weight: 400;
            color: #6366f1;
        }

        .btn-login-cta {
            padding: 7px 18px;
            background: var(--indigo);
            color: white;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.15s;
            white-space: nowrap;
        }

        .btn-login-cta:hover {
            background: var(--indigo-dark);
        }

        /* Footer */
        footer {
            text-align: center;
            padding: 18px;
            font-size: 11px;
            color: var(--muted);
            border-top: 1px solid var(--border);
            background: white;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 640px) {
            nav {
                padding: 0 1rem;
            }

            .nav-title {
                display: none;
            }

            .main {
                padding: 16px 1rem 32px;
            }

            .page-title {
                font-size: 15px;
            }

            .schedule-table th.th-room,
            .schedule-table td:nth-child(3),
            .schedule-table th:nth-child(5),
            .schedule-table td:nth-child(5) {
                display: none;
            }

            .schedule-table th,
            .schedule-table td {
                padding: 10px 10px;
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .table-wrap {
            animation: fadeIn 0.3s ease both;
        }
    </style>
</head>

<body>

    <nav>
        <div class="nav-left">
            <div class="nav-logo">
                <img src="{{ asset('images/logoheader.png') }}" alt="Logo Kemenkopangan">
            </div>
            <div class="nav-title">
                Jadwal Ruang Rapat
                <span>Kementerian Koordinator Bidang Pangan</span>
            </div>
        </div>

        <div class="nav-right">
            @auth
                <a href="{{ url('/dashboard') }}" class="btn-solid">Dashboard</a>
            @else
                @if(Route::has('login'))
                    <a href="{{ route('login') }}" class="btn-ghost">Masuk</a>
                @endif
                @if(Route::has('register'))
                    <a href="{{ route('register') }}" class="btn-solid">Daftar</a>
                @endif
            @endauth
        </div>
    </nav>

    <div class="main">
        <!-- Header -->
        <div class="page-header">
            <div>
                <div class="page-title">Jadwal Ruang Rapat</div>
                <div class="page-subtitle" id="dateRangeLabel">Memuat jadwal...</div>
            </div>
            <div class="range-tabs">
                <button class="range-tab active" data-range="3" onclick="setRange(3, this)">3 Hari</button>
                <button class="range-tab" data-range="7" onclick="setRange(7, this)">7 Hari</button>
                <button class="range-tab" data-range="14" onclick="setRange(14, this)">2 Minggu</button>
            </div>
        </div>

        <!-- Filter Ruangan -->
        <div class="filter-wrap" style="margin-bottom: 16px;" id="roomFilters">
            <span class="filter-label">Ruangan:</span>
            <button class="filter-btn active" data-room="all" onclick="setRoom('all', this)">Semua Ruang</button>
            <!-- filled by JS -->
        </div>

        <!-- Table -->
        <div class="table-wrap">
            <table class="schedule-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama Kegiatan</th>
                        <th class="th-room">Ruangan</th>
                        <th class="th-time">Waktu</th>
                        <th class="th-pic">PIC</th>
                        <th class="th-status">Status</th>
                    </tr>
                </thead>
                <tbody id="scheduleBody">
                    <tr class="loading-row">
                        <td colspan="6">
                            <span class="spinner"></span> Memuat jadwal...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        @guest
            <div class="login-cta">
                <div class="login-cta-text">
                    🔒 Ingin mengajukan booking ruang rapat?
                    <span>Silakan login untuk menambahkan agenda.</span>
                </div>
                <a href="{{ route('login') }}" class="btn-login-cta">Masuk Sekarang →</a>
            </div>
        @endguest
    </div>

    <footer>
        © {{ date('Y') }} Kementerian Koordinator Bidang Pangan Republik Indonesia
    </footer>

    <script>
        const roomColors = {
            1: { dot: '#94a3b8', label: 'Ruang Utama' },
            2: { dot: '#14b8a6', label: 'Ruang KDKMP' },
            3: { dot: '#8b5cf6', label: 'Ruang Setmenko' },
            4: { dot: '#f59e0b', label: 'Ruang D2' },
            5: { dot: '#d946ef', label: 'Ruang D3' },
            6: { dot: '#f43f5e', label: 'Ruang D4' },
        }

        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
        const monthsFull = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']

        let allBookings = []
        let activeRoom = 'all'
        let activeRange = 3

        function toLocalDateStr(date) {
            return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
        }

        function fmtTime(isoStr) {
            // parse manual untuk hindari timezone issue
            const parts = isoStr.replace('T', ' ').split(/[- :]/)
            return `${parts[3]}.${parts[4]}`
        }

        function isToday(dateStr) {
            return dateStr === toLocalDateStr(new Date())
        }

        function getDateRange(numDays) {
            const start = new Date()
            start.setHours(0, 0, 0, 0)
            const end = new Date(start)
            end.setDate(end.getDate() + numDays)
            return { start, end }
        }

        function getAllDatesInRange(start, numDays) {
            const dates = []
            for (let i = 0; i < numDays; i++) {
                const d = new Date(start)
                d.setDate(d.getDate() + i)
                dates.push(toLocalDateStr(d))
            }
            return dates
        }

        async function loadBookings() {
            try {
                const { start, end } = getDateRange(activeRange)
                const startStr = toLocalDateStr(start)
                const endStr = toLocalDateStr(end)

                const res = await fetch(`/api/display-bookings?start=${startStr}&end=${endStr}`)
                const data = await res.json()

                allBookings = data.map(e => ({
                    title: e.title,
                    start: e.start?.replace(' ', 'T'),
                    end: e.end?.replace(' ', 'T'),
                    room_id: e.extendedProps?.room_id ?? e.room_id,
                    room_name: e.extendedProps?.room_name ?? e.room_name ?? null,
                    pic: e.extendedProps?.pic ?? e.pic ?? '-',
                    status: e.extendedProps?.status ?? e.status ?? 'APPROVED',
                    description: e.extendedProps?.description ?? e.description ?? '',
                }))

                buildRoomFilters(allBookings)
                render()
            } catch (e) {
                console.error('ERROR:', e)
                document.getElementById('scheduleBody').innerHTML =
                    `<tr class="empty-row"><td colspan="6"><span class="empty-icon">⚠️</span>Gagal memuat data.</td></tr>`
            }
        }

        function buildRoomFilters(bookings) {
            const roomIds = [...new Set(bookings.map(b => b.room_id).filter(Boolean))].sort((a, b) => a - b)
            const wrap = document.getElementById('roomFilters')
            wrap.querySelectorAll('[data-room]:not([data-room="all"])').forEach(el => el.remove())

            roomIds.forEach(rid => {
                const info = roomColors[rid] || { dot: '#94a3b8', label: `Ruang ${rid}` }
                const sample = bookings.find(b => b.room_id == rid)
                const label = sample?.room_name ?? info.label
                const btn = document.createElement('button')
                btn.className = 'filter-btn'
                btn.dataset.room = rid
                btn.onclick = function () { setRoom(rid, this) }
                btn.innerHTML = `<span class="room-dot" style="background:${info.dot}"></span>${label}`
                wrap.appendChild(btn)
            })
        }

        function setRoom(room, el) {
            activeRoom = room
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'))
            el.classList.add('active')
            render()
        }

        function setRange(range, el) {
            activeRange = range
            document.querySelectorAll('.range-tab').forEach(b => b.classList.remove('active'))
            el.classList.add('active')
            loadBookings() // reload API dengan range baru
        }

        function render() {
            const { start, end } = getDateRange(activeRange)

            // update subtitle
            const endLabel = new Date(start)
            endLabel.setDate(endLabel.getDate() + activeRange - 1)
            document.getElementById('dateRangeLabel').textContent =
                `${String(start.getDate()).padStart(2, '0')} ${monthsFull[start.getMonth()]} – ${String(endLabel.getDate()).padStart(2, '0')} ${monthsFull[endLabel.getMonth()]} ${endLabel.getFullYear()}`

            // filter bookings
            let filtered = allBookings.filter(b => {
                const bDate = b.start.split('T')[0]
                const startDate = toLocalDateStr(start)
                const endDate = toLocalDateStr(end)
                return bDate >= startDate && bDate < endDate && ['APPROVED', 'PENDING'].includes(b.status)
            })

            if (activeRoom !== 'all') {
                filtered = filtered.filter(b => String(b.room_id) === String(activeRoom))
            }

            filtered.sort((a, b) => new Date(a.start) - new Date(b.start))

            // group by date
            const grouped = {}
            filtered.forEach(b => {
                const key = b.start.split('T')[0]
                if (!grouped[key]) grouped[key] = []
                grouped[key].push(b)
            })

            // generate SEMUA tanggal dalam range, termasuk yang kosong
            const allDates = Object.keys(grouped).sort()

            const wrap = document.querySelector('.table-wrap')
            wrap.innerHTML = ''

            allDates.forEach(dateKey => {
                const items = grouped[dateKey] || []
                const d = new Date(dateKey + 'T00:00:00')
                const todayFlag = isToday(dateKey)
                const dayLabel = `${days[d.getDay()]}, ${String(d.getDate()).padStart(2, '0')} ${monthsFull[d.getMonth()]} ${d.getFullYear()}`

                const section = document.createElement('div')
                section.style.cssText = 'margin-bottom: 20px;'

                const dateHeader = document.createElement('div')
                dateHeader.style.cssText = `
                padding: 10px 16px;
                font-size: 11px;
                font-weight: 800;
                text-transform: uppercase;
                letter-spacing: 0.07em;
                color: ${todayFlag ? '#ffffff' : '#475569'};
                background: ${todayFlag ? 'linear-gradient(135deg, #050068, #4b4dce)' : 'linear-gradient(135deg, #f1f5f9, #e2e8f0)'};
                border: 1px solid ${todayFlag ? '#4F46E5' : '#e2e8f0'};
                border-bottom: none;
                border-radius: 12px 12px 0 0;
            `
                dateHeader.innerHTML = dayLabel + (todayFlag ? ' <span style="font-weight:500;color:#FFF;font-size:10px;text-transform:none;letter-spacing:0;margin-left:6px;">● Hari ini</span>' : '')

                const tableWrap = document.createElement('div')
                tableWrap.style.cssText = `
                background: white;
                border: 1px solid var(--border);
                border-radius: 0 0 12px 12px;
                overflow: hidden;
                box-shadow: 0 1px 6px rgba(0,0,0,0.04);
            `

                let tableHtml = `
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nama Kegiatan</th>
                            <th class="th-room">Ruangan</th>
                            <th class="th-time">Waktu</th>
                            <th class="th-pic">PIC</th>
                            <th class="th-status">Status</th>
                        </tr>
                    </thead>
                    <tbody>
            `

                if (items.length === 0) {
                    tableHtml += `<tr><td colspan="6" style="text-align:center;padding:20px;color:#94a3b8;font-size:13px;font-weight:500;">Tidak ada jadwal</td></tr>`
                } else {
                    items.forEach((b, i) => {
                        const rid = b.room_id
                        const info = roomColors[rid] || { dot: '#94a3b8', label: `Ruang ${rid}` }
                        const startFmt = fmtTime(b.start)
                        const endFmt = fmtTime(b.end)
                        const statusClass = b.status === 'APPROVED' ? 'status-approved' : 'status-pending'
                        const statusLabel = b.status === 'APPROVED' ? 'Disetujui' : 'Menunggu'
                        const roomName = b.room_name ?? info.label

                        tableHtml += `<tr>
                        <td>${i + 1}</td>
                        <td>
                            <div class="booking-title">${escHtml(b.title)}</div>
                            ${b.description ? `<div class="booking-desc">${escHtml(b.description.substring(0, 60))}${b.description.length > 60 ? '…' : ''}</div>` : ''}
                        </td>
                        <td style="text-align:left;">
                            <span class="room-badge">
                                <span class="room-dot" style="background:${info.dot}"></span>
                                ${escHtml(roomName)}
                            </span>
                        </td>
                        <td class="time-cell">${startFmt} – ${endFmt}</td>
                        <td style="font-size:12px;font-weight:600;color:#475569;">${escHtml(b.pic ?? '-')}</td>
                        <td><span class="status-badge ${statusClass}">${statusLabel}</span></td>
                    </tr>`
                    })
                }

                tableHtml += `</tbody></table>`
                tableWrap.innerHTML = tableHtml

                section.appendChild(dateHeader)
                section.appendChild(tableWrap)
                wrap.appendChild(section)
            })
        }

        function escHtml(str) {
            if (!str) return ''
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;')
        }

        loadBookings()
    </script>
</body>

</html>