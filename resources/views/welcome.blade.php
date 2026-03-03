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

        .main {
            flex: 1;
            padding: 24px 1.5rem 40px;
            max-width: 1100px;
            margin: 0 auto;
            width: 100%;
        }

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

        .filter-wrap {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
            overflow-x: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
            padding-bottom: 4px;
            margin-bottom: 16px;
        }

        .filter-wrap::-webkit-scrollbar {
            display: none;
        }

        .filter-label {
            font-size: 11px;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .filter-btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 99px;
            border: 1.5px solid var(--border);
            background: white;
            font-size: 10px;
            font-weight: 600;
            color: var(--muted);
            cursor: pointer;
            transition: all 0.15s;
            font-family: inherit;
            white-space: nowrap;
            flex-shrink: 0;
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
            width: 6px;
            height: 6px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .table-wrap {
            border-radius: 16px;
            overflow: hidden;
            animation: fadeIn 0.3s ease both;
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
            padding: 11px 16px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: var(--muted);
            text-align: left;
        }

        .schedule-table th:first-child {
            width: 52px;
            text-align: center;
        }

        .schedule-table th.th-room {
            width: 200px;
        }

        .schedule-table th.th-time {
            width: 150px;
        }

        .schedule-table th.th-pic {
            width: 130px;
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
            line-height: 1.3;
        }

        .booking-desc {
            font-size: 11px;
            color: var(--muted);
            margin-top: 2px;
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

        .time-cell {
            font-weight: 700;
            font-size: 12px;
            color: var(--indigo);
            white-space: nowrap;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 99px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.04em;
            white-space: nowrap;
        }

        .status-terjadwal {
            background: #dcfce7;
            color: #15803d;
        }

        .status-segera {
            background: #fef9c3;
            color: #a16207;
        }

        .status-berlangsung {
            background: #1e293b;
            color: white;
        }

        .status-selesai {
            background: #f1f5f9;
            color: #94a3b8;
        }

        .status-pending {
            background: #ede9fe;
            color: #6d28d9;
        }

        /* Mobile card */
        .mobile-cards {
            display: none;
        }

        .booking-card-mobile {
            display: flex;
            gap: 12px;
            padding: 14px 16px;
            border-bottom: 1px solid #f1f5f9;
        }

        .booking-card-mobile:last-child {
            border-bottom: none;
        }

        .card-bar {
            width: 3px;
            border-radius: 4px;
            flex-shrink: 0;
            align-self: stretch;
            min-height: 48px;
        }

        .card-body {
            flex: 1;
            min-width: 0;
        }

        .card-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--text);
            line-height: 1.3;
        }

        .card-desc {
            font-size: 11px;
            color: var(--muted);
            margin-top: 3px;
        }

        .card-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 8px;
            align-items: center;
        }

        .card-time {
            font-size: 12px;
            font-weight: 700;
            color: var(--indigo);
            background: var(--indigo-light);
            padding: 3px 9px;
            border-radius: 6px;
        }

        .card-room {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 600;
            color: var(--muted);
            background: #f1f5f9;
            padding: 3px 9px;
            border-radius: 6px;
        }

        .card-pic {
            font-size: 11px;
            font-weight: 600;
            color: #475569;
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

        footer {
            text-align: center;
            padding: 18px;
            font-size: 11px;
            color: var(--muted);
            border-top: 1px solid var(--border);
            background: white;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(8px)
            }

            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        @media (max-width:640px) {
            nav {
                padding: 0 1rem;
            }

            .nav-title {
                display: none;
            }

            .main {
                padding: 14px 1rem 32px;
            }

            .page-title {
                font-size: 16px;
            }

            .page-header {
                gap: 8px;
                margin-bottom: 14px;
            }

            .desktop-table {
                display: none !important;
            }

            .mobile-cards {
                display: block;
            }

            .login-cta {
                flex-direction: column;
                align-items: flex-start;
            }

            .btn-login-cta {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <nav>
        <div class="nav-left">
            <div class="nav-logo"><img src="{{ asset('images/logoheader.png') }}" alt="Logo Kemenkopangan"></div>
            <div class="nav-title">Jadwal Ruang Rapat<span>Kementerian Koordinator Bidang Pangan</span></div>
        </div>
        <div class="nav-right">
            @auth
                <a href="{{ url('/dashboard') }}" class="btn-solid">Dashboard</a>
            @else
                @if(Route::has('login'))<a href="{{ route('login') }}" class="btn-ghost">Masuk</a>@endif
            @endauth
        </div>
    </nav>

    <div class="main">
        <div class="page-header">
            <div>
                <div class="page-title">Jadwal Ruang Rapat</div>
                <div class="page-subtitle" id="dateRangeLabel">Memuat jadwal...</div>
            </div>
            <div class="range-tabs">
                <button class="range-tab active" onclick="setRange(3,this)">3 Hari</button>
                <button class="range-tab" onclick="setRange(7,this)">7 Hari</button>
                <button class="range-tab" onclick="setRange(14,this)">2 Minggu</button>
            </div>
        </div>

        <div class="filter-wrap" id="roomFilters">
            <span class="filter-label">Ruangan:</span>
            <button class="filter-btn active" data-room="all" onclick="setRoom('all',this)">Semua Ruang</button>
        </div>

        <div class="table-wrap" id="tableWrap">
            <div
                style="background:white;border-radius:16px;padding:40px;text-align:center;color:var(--muted);font-size:13px;">
                <span class="spinner"></span> Memuat jadwal...
            </div>
        </div>

        @guest
            <div class="login-cta">
                <div class="login-cta-text">🔒 Ingin mengajukan booking ruang rapat? <span>Silakan login untuk menambahkan
                        agenda.</span></div>
                <a href="{{ route('login') }}" class="btn-login-cta">Masuk Sekarang →</a>
            </div>
        @endguest
    </div>

    <footer>© {{ date('Y') }} Kementerian Koordinator Bidang Pangan Republik Indonesia</footer>

    <script>
        const RC = {
            1: { dot: '#3d3d3d', label: 'Ruang Rapat Utama' },
            2: { dot: '#5bc8af', label: 'Ruang Rapat KDKMP' },
            3: { dot: '#7b68aa', label: 'Ruang Rapat Setmenko' },
            4: { dot: '#f0c040', label: 'Ruang Rapat D2' },
            5: { dot: '#4bbfd4', label: 'Ruang Rapat D3' },
            6: { dot: '#e8604c', label: 'Ruang Rapat D4' },
            7: { dot: '#ec4899', label: 'Ruang Dharma Wanita' },
        }
        const DAYS = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
        const MON = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
        let all = [], room = 'all', range = 3
        const mobile = () => window.innerWidth <= 640
        const pad = n => String(n).padStart(2, '0')
        const dateStr = d => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`
        const fmtT = s => { const p = s.replace('T', ' ').split(/[- :]/); return `${p[3]}.${p[4]}` }
        const today = s => s === dateStr(new Date())
        const getRange = n => { const s = new Date(); s.setHours(0, 0, 0, 0); const e = new Date(s); e.setDate(e.getDate() + n); return { s, e } }
        const esc = s => s ? String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;') : ''
        function status(b) {
            const now = new Date(), st = new Date(b.start), en = new Date(b.end), d = (st - now) / 60000
            if (b.status === 'PENDING') return { c: 'status-pending', l: 'Menunggu' }
            if (now >= st && now <= en) return { c: 'status-berlangsung', l: '● Sedang Berlangsung' }
            if (now > en) return { c: 'status-selesai', l: '✓ Selesai' }
            if (d <= 30) return { c: 'status-segera', l: '⚡ Segera' }
            return { c: 'status-terjadwal', l: '✓ Terjadwal' }
        }
        function buildFilters() {
            const w = document.getElementById('roomFilters')
            w.querySelectorAll('[data-room]:not([data-room="all"])').forEach(e => e.remove())
            Object.entries(RC).forEach(([id, i]) => {
                const b = document.createElement('button')
                b.className = 'filter-btn'; b.dataset.room = id
                b.onclick = () => setRoom(id, b)
                b.innerHTML = `<span class="room-dot" style="background:${i.dot}"></span>${i.label}`
                w.appendChild(b)
            })
        }
        async function load() {
            const { s, e } = getRange(range)
            try {
                const r = await fetch(`/api/display-bookings?start=${dateStr(s)}&end=${dateStr(e)}`)
                const d = await r.json()
                all = d.map(e => ({ title: e.title, start: (e.start || '').replace(' ', 'T'), end: (e.end || '').replace(' ', 'T'), room_id: e.extendedProps?.room_id ?? e.room_id, room_name: e.extendedProps?.room_name ?? e.room_name ?? null, pic: e.extendedProps?.pic ?? e.pic ?? '-', status: e.extendedProps?.status ?? e.status ?? 'APPROVED', description: e.extendedProps?.description ?? e.description ?? '' }))
                render()
            } catch (err) { document.getElementById('tableWrap').innerHTML = '<div style="background:white;border-radius:16px;padding:48px;text-align:center;color:#94a3b8;font-size:13px;">⚠️ Gagal memuat data.</div>' }
        }
        function setRoom(r, el) { room = r; document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active')); el.classList.add('active'); render() }
        function setRange(r, el) { range = r; document.querySelectorAll('.range-tab').forEach(b => b.classList.remove('active')); el.classList.add('active'); load() }
        function render() {
            const { s, e } = getRange(range)
            const el = new Date(s); el.setDate(el.getDate() + range - 1)
            document.getElementById('dateRangeLabel').textContent = `${pad(s.getDate())} ${MON[s.getMonth()]} – ${pad(el.getDate())} ${MON[el.getMonth()]} ${el.getFullYear()}`
            let f = all.filter(b => { const d = b.start.split('T')[0]; return d >= dateStr(s) && d < dateStr(e) && ['APPROVED', 'PENDING'].includes(b.status) })
            if (room !== 'all') f = f.filter(b => String(b.room_id) === String(room))
            f.sort((a, b) => new Date(a.start) - new Date(b.start))
            const g = {}; f.forEach(b => { const k = b.start.split('T')[0]; (g[k] = g[k] || []).push(b) })
            const wrap = document.getElementById('tableWrap'); wrap.innerHTML = ''
            const keys = Object.keys(g).sort()
            if (!keys.length) { wrap.innerHTML = '<div style="background:white;border-radius:16px;border:1px solid var(--border);padding:48px 16px;text-align:center;color:#94a3b8;font-size:13px;font-weight:500;"><span style="font-size:28px;display:block;margin-bottom:8px;">📭</span>Tidak ada jadwal pada periode ini.</div>'; return }
            keys.forEach(dk => {
                const items = g[dk], d = new Date(dk + 'T00:00:00'), td = today(dk)
                const dl = `${DAYS[d.getDay()]}, ${pad(d.getDate())} ${MON[d.getMonth()]} ${d.getFullYear()}`
                const sec = document.createElement('div'); sec.style.marginBottom = '20px'
                const hdr = document.createElement('div')
                hdr.style.cssText = `padding:10px 16px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:0.07em;color:${td ? '#fff' : '#475569'};background:${td ? 'linear-gradient(135deg,#050068,#4b4dce)' : 'linear-gradient(135deg,#f1f5f9,#e2e8f0)'};border:1px solid ${td ? '#4F46E5' : '#e2e8f0'};border-bottom:none;border-radius:12px 12px 0 0;`
                hdr.innerHTML = dl + (td ? ' <span style="font-weight:500;color:#FFF;font-size:10px;text-transform:none;letter-spacing:0;margin-left:6px;">● Hari ini</span>' : '')
                const body = document.createElement('div')
                body.style.cssText = 'background:white;border:1px solid var(--border);border-radius:0 0 12px 12px;overflow:hidden;box-shadow:0 1px 6px rgba(0,0,0,0.04);'
                if (mobile()) {
                    const cards = document.createElement('div'); cards.className = 'mobile-cards'
                    items.forEach(b => {
                        const inf = RC[b.room_id] || { dot: '#94a3b8', label: `Ruang ${b.room_id}` }
                        const rn = b.room_name ?? inf.label, { c, l } = status(b)
                        const card = document.createElement('div'); card.className = 'booking-card-mobile'
                        card.innerHTML = `<div class="card-bar" style="background:${inf.dot}"></div><div class="card-body"><div class="card-title">${esc(b.title)}</div>${b.description ? `<div class="card-desc">${esc(b.description.substring(0, 80))}${b.description.length > 80 ? '…' : ''}</div>` : ''}<div class="card-meta"><span class="card-time">${fmtT(b.start)} – ${fmtT(b.end)}</span><span class="card-room"><span class="room-dot" style="background:${inf.dot}"></span>${esc(rn)}</span><span class="card-pic">👤 ${esc(b.pic ?? '-')}</span><span class="status-badge ${c}">${l}</span></div></div>`
                        cards.appendChild(card)
                    })
                    body.appendChild(cards)
                } else {
                    let t = `<table class="schedule-table desktop-table"><thead><tr><th>No.</th><th>Nama Kegiatan</th><th class="th-room">Ruangan</th><th class="th-time">Waktu</th><th class="th-pic">PIC</th><th class="th-status">Status</th></tr></thead><tbody>`
                    items.forEach((b, i) => {
                        const inf = RC[b.room_id] || { dot: '#94a3b8', label: `Ruang ${b.room_id}` }
                        const rn = b.room_name ?? inf.label, { c, l } = status(b)
                        t += `<tr><td>${i + 1}</td><td><div class="booking-title">${esc(b.title)}</div>${b.description ? `<div class="booking-desc">${esc(b.description.substring(0, 60))}${b.description.length > 60 ? '…' : ''}</div>` : ''}</td><td><span class="room-badge"><span class="room-dot" style="background:${inf.dot}"></span>${esc(rn)}</span></td><td class="time-cell">${fmtT(b.start)} – ${fmtT(b.end)}</td><td style="font-size:12px;font-weight:600;color:#475569;">${esc(b.pic ?? '-')}</td><td><span class="status-badge ${c}">${l}</span></td></tr>`
                    })
                    t += `</tbody></table>`; body.innerHTML = t
                }
                sec.appendChild(hdr); sec.appendChild(body); wrap.appendChild(sec)
            })
        }
        let rt; window.addEventListener('resize', () => { clearTimeout(rt); rt = setTimeout(render, 150) })
        buildFilters(); load()
    </script>
</body>

</html>