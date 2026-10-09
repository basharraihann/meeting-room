<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Jadwal Ruang Rapat – Kemenkopangan</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
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
            --navy: #0f1e5a;
            --indigo: #4F46E5;
            --indigo-dark: #3730A3;
            --indigo-soft: #EEF2FF;
            --text: #0f172a;
            --body: #334155;
            --muted: #64748b;
            --faint: #94a3b8;
            --border: #e2e8f0;
            --line: #f1f5f9;
            --bg: #f6f8fb;
            --card: #ffffff;
        }

        html {
            -webkit-text-size-adjust: 100%;
            text-size-adjust: 100%;
        }

        body {
            overflow-x: hidden;
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            -webkit-font-smoothing: antialiased;
        }

        :focus-visible {
            outline: 2px solid var(--indigo);
            outline-offset: 2px;
        }

        /* ===== NAV ===== */
        nav {
            background: rgba(255, 255, 255, .96);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border);
            padding: 0 1.5rem;
            height: 64px;
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
            gap: 14px;
            min-width: 0;
        }

        .nav-logo img {
            height: 36px;
            width: auto;
            display: block;
        }

        .nav-title {
            font-size: 13px;
            font-weight: 800;
            color: var(--navy);
            line-height: 1.3;
            border-left: 1px solid var(--border);
            padding-left: 14px;
        }

        .nav-title span {
            display: block;
            font-size: 11px;
            font-weight: 500;
            color: var(--muted);
        }

        .nav-right {
            display: flex;
            gap: 16px;
            align-items: center;
        }

        .clock {
            text-align: right;
            line-height: 1.25;
        }

        .clock-time {
            font-size: 15px;
            font-weight: 800;
            color: var(--navy);
            font-variant-numeric: tabular-nums;
        }

        .clock-date {
            font-size: 11px;
            font-weight: 500;
            color: var(--muted);
        }

        .btn-ghost,
        .btn-solid {
            padding: 7px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
            transition: all .15s;
        }

        .btn-ghost {
            border: 1px solid var(--border);
            color: var(--body);
            background: #fff;
        }

        .btn-ghost:hover {
            border-color: var(--indigo);
            color: var(--indigo);
        }

        .btn-solid {
            background: var(--indigo);
            color: #fff;
            border: 1px solid var(--indigo);
        }

        .btn-solid:hover {
            background: var(--indigo-dark);
        }

        /* ===== LAYOUT ===== */
        .main {
            flex: 1;
            padding: 28px 2rem 56px;
            max-width: 1360px;
            margin: 0 auto;
            width: 100%;
        }

        .page-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .page-title {
            font-size: 20px;
            font-weight: 800;
            color: var(--navy);
            letter-spacing: -.01em;
        }

        .page-subtitle {
            font-size: 13px;
            color: var(--muted);
            margin-top: 4px;
            font-weight: 500;
        }

        .range-tabs {
            display: flex;
            gap: 2px;
            background: #e8edf4;
            border-radius: 10px;
            padding: 3px;
        }

        .range-tab {
            padding: 7px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            color: var(--muted);
            cursor: pointer;
            border: none;
            background: transparent;
            font-family: inherit;
            transition: all .15s;
        }

        .range-tab:hover {
            color: var(--text);
        }

        .range-tab.active {
            background: #fff;
            color: var(--indigo-dark);
            box-shadow: 0 1px 3px rgba(15, 23, 42, .1);
        }

        /* ===== FILTER (sebaris) ===== */
        .filter-wrap {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .filter-label {
            font-size: 12px;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .filter-buttons {
            display: flex;
            flex-wrap: nowrap;
            gap: 5px;
            overflow-x: auto;
            scrollbar-width: none;
            padding: 3px 2px;
        }

        .filter-buttons::-webkit-scrollbar {
            display: none;
        }

        .filter-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 9px;
            border-radius: 99px;
            border: 1px solid var(--border);
            background: #fff;
            font-size: 11.5px;
            font-weight: 600;
            color: var(--body);
            cursor: pointer;
            transition: all .15s;
            font-family: inherit;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .filter-btn:hover {
            border-color: #94a3b8;
        }

        .filter-btn.active {
            background: var(--indigo);
            border-color: var(--indigo);
            color: #fff;
        }

        .filter-btn.active .room-dot {
            box-shadow: 0 0 0 2px rgba(255, 255, 255, .85);
        }

        .room-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        /* ===== HARI ===== */
        .day {
            margin-bottom: 18px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
            animation: fadeIn .3s ease both;
        }

        .day-head {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            background: #f8fafc;
            border-bottom: 1px solid var(--border);
        }

        .day-name {
            font-size: 14px;
            font-weight: 800;
            color: var(--text);
        }

        .day-tag {
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 6px;
            background: var(--indigo-soft);
            color: var(--indigo-dark);
        }

        .day-count {
            margin-left: auto;
            font-size: 12px;
            font-weight: 600;
            color: var(--muted);
        }

        .day.today {
            border-color: var(--indigo);
        }

        .day.today .day-head {
            background: linear-gradient(135deg, #050068, #4b4dce);
            border-bottom-color: var(--indigo);
        }

        .day.today .day-name {
            color: #fff;
        }

        .day.today .day-tag {
            background: rgba(255, 255, 255, .2);
            color: #fff;
        }

        .day.today .day-tag::before {
            content: '● ';
            font-size: 8px;
            vertical-align: 1px;
        }

        .day.today .day-count {
            color: rgba(255, 255, 255, .75);
        }

        /* ===== TABEL ===== */
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .schedule-table th {
            padding: 10px 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--faint);
            text-align: left;
            background: #fff;
            border-bottom: 1px solid var(--line);
        }

        .schedule-table .th-no {
            width: 56px;
            text-align: center;
        }

        .schedule-table td.td-no {
            text-align: center;
            font-size: 12px;
            font-weight: 700;
            color: var(--faint);
            font-variant-numeric: tabular-nums;
        }

        .schedule-table .th-time {
            width: 150px;
        }

        .schedule-table .th-room {
            width: 220px;
        }

        .schedule-table .th-pic {
            width: 200px;
        }

        .schedule-table .th-status {
            width: 170px;
        }

        .schedule-table td {
            padding: 15px 20px;
            font-size: 14px;
            border-bottom: 1px solid var(--line);
            vertical-align: middle;
            color: var(--body);
        }

        .schedule-table tbody tr:last-child td {
            border-bottom: none;
        }

        .schedule-table tbody tr:hover {
            background: #fafbff;
        }

        .schedule-table td:first-child {
            box-shadow: inset 3px 0 0 var(--bar, transparent);
        }

        tr.is-done td {
            opacity: .55;
        }

        tr.is-live {
            background: #f5f6ff;
        }

        .time-main {
            font-weight: 800;
            font-size: 14px;
            color: var(--navy);
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }

        .time-dur {
            font-size: 11px;
            color: var(--faint);
            margin-top: 2px;
            font-weight: 500;
        }

        .booking-title {
            font-weight: 700;
            font-size: 14px;
            color: var(--text);
            line-height: 1.35;
        }

        .booking-desc {
            font-size: 12px;
            color: var(--muted);
            margin-top: 3px;
            line-height: 1.4;
        }

        .room-name {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
            color: var(--body);
        }

        .pic {
            font-size: 13px;
            font-weight: 600;
            color: var(--body);
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 11px;
            border-radius: 99px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .status-badge::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
        }

        .status-terjadwal {
            background: #ecfdf5;
            color: #047857;
        }

        .status-segera {
            background: #fffbeb;
            color: #b45309;
        }

        .status-berlangsung {
            background: var(--navy);
            color: #fff;
        }

        .status-berlangsung::before {
            animation: pulse 1.6s ease-in-out infinite;
        }

        .status-selesai {
            background: #f1f5f9;
            color: var(--muted);
        }

        .status-pending {
            background: #f5f3ff;
            color: #6d28d9;
        }

        /* ===== MOBILE ===== */
        .mobile-cards {
            display: none;
        }

        .booking-card-mobile {
            display: flex;
            gap: 12px;
            padding: 14px 16px;
            border-bottom: 1px solid var(--line);
        }

        .booking-card-mobile:last-child {
            border-bottom: none;
        }

        .booking-card-mobile.is-done {
            opacity: .55;
        }

        .card-bar {
            width: 3px;
            border-radius: 4px;
            flex-shrink: 0;
            align-self: stretch;
        }

        .card-body {
            flex: 1;
            min-width: 0;
        }

        .card-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--text);
            line-height: 1.35;
        }

        .card-desc {
            font-size: 12px;
            color: var(--muted);
            margin-top: 3px;
        }

        .card-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 6px 10px;
            margin-top: 9px;
            align-items: center;
            font-size: 12px;
            color: var(--body);
            font-weight: 600;
        }

        .card-time {
            font-weight: 800;
            color: var(--navy);
            font-variant-numeric: tabular-nums;
        }

        /* ===== STATE ===== */
        .state {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 56px 16px;
            text-align: center;
            color: var(--muted);
            font-size: 14px;
            font-weight: 500;
        }

        .state strong {
            display: block;
            color: var(--text);
            font-size: 15px;
            margin-bottom: 4px;
        }

        .spinner {
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 2px solid var(--border);
            border-top-color: var(--indigo);
            border-radius: 50%;
            animation: spin .7s linear infinite;
            margin-right: 8px;
            vertical-align: middle;
        }

        .login-cta {
            margin-top: 8px;
            background: var(--indigo-soft);
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
            color: var(--indigo-dark);
            font-weight: 700;
        }

        .login-cta-text span {
            font-weight: 500;
            color: #4f46e5;
        }

        .btn-login-cta {
            padding: 8px 18px;
            background: var(--indigo);
            color: #fff;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            transition: background .15s;
            white-space: nowrap;
        }

        .btn-login-cta:hover {
            background: var(--indigo-dark);
        }

        footer {
            text-align: center;
            padding: 18px;
            font-size: 12px;
            color: var(--muted);
            border-top: 1px solid var(--border);
            background: #fff;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes pulse {
            50% {
                opacity: .3;
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(6px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            * {
                animation: none !important;
            }
        }

        @media (max-width: 860px) {
            .clock {
                display: none;
            }
        }

        @media (max-width: 640px) {
            nav {
                padding: 0 1rem;
            }

            .nav-title {
                display: none;
            }

            .main {
                padding: 16px 1rem 36px;
            }

            .page-title {
                font-size: 16px;
            }

            .day-head {
                padding: 10px 16px;
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

        /* ===== RESPONSIVE ===== */
        .main {
            min-width: 0;
        }

        .day-name {
            min-width: 0;
        }

        /* Layar sangat lebar / TV */
        @media (min-width: 1600px) {
            .main {
                max-width: 1560px;
            }
        }

        @media (min-width: 2200px) {
            html {
                font-size: 118%;
            }

            .main {
                max-width: 1900px;
            }
        }

        /* Desktop / laptop: filter tetap SEBARIS (lebih rapat supaya semua ruangan muat) */
        @media (min-width: 1101px) and (max-width: 1400px) {
            .filter-buttons {
                flex-wrap: nowrap;
                gap: 4px;
            }

            .filter-btn {
                font-size: 11.5x;
                padding: 5px 8px;
                gap: 4px;
            }
        }

        /* Tablet landscape: terlalu sempit untuk sebaris, tombol turun ke baris berikutnya */
        @media (min-width: 901px) and (max-width: 1100px) {
            .filter-buttons {
                flex-wrap: wrap;
                overflow: visible;
                gap: 6px;
            }

            .filter-btn {
                font-size: 12px;
                padding: 6px 12px;
                flex-shrink: 1;
            }
        }

        /* Tablet landscape / laptop sempit: rapatkan tabel */
        @media (max-width: 1200px) {
            .main {
                padding: 24px 1.5rem 48px;
            }

            .schedule-table th,
            .schedule-table td {
                padding: 12px 14px;
            }

            .schedule-table .th-room {
                width: 180px;
            }

            .schedule-table .th-pic {
                width: 150px;
            }

            .schedule-table .th-status {
                width: 140px;
            }

            .schedule-table .th-time {
                width: 130px;
            }
        }

        /* Tablet portrait & HP: tabel berubah jadi kartu */
        @media (max-width: 900px) {
            .desktop-table {
                display: none !important;
            }

            .mobile-cards {
                display: block;
            }

            .main {
                padding: 20px 1.25rem 40px;
            }

            .clock {
                display: none;
            }

            .page-header {
                align-items: stretch;
            }

            .range-tabs {
                width: 100%;
            }

            .range-tab {
                flex: 1;
                text-align: center;
                padding: 8px 10px;
            }

            .booking-card-mobile {
                padding: 14px 18px;
            }
        }

        /* Layar kecil: semua ruangan tampil, tombol turun ke baris berikutnya */
        @media (max-width: 900px) {
            .filter-buttons {
                flex-wrap: wrap;
                overflow: visible;
                gap: 6px;
                margin-right: 0;
                padding: 2px 0;
            }

            .filter-btn {
                flex-shrink: 1;
            }
        }

        /* HP */
        @media (max-width: 640px) {
            nav {
                height: 56px;
                padding: 0 max(1rem, env(safe-area-inset-left));
            }

            .nav-logo img {
                height: 30px;
            }

            .btn-ghost,
            .btn-solid {
                padding: 6px 14px;
                font-size: 12px;
            }

            .main {
                padding: 16px 1rem 32px;
            }

            .page-subtitle {
                font-size: 12px;
            }

            .range-tab {
                font-size: 12px;
            }

            .day {
                border-radius: 12px;
                margin-bottom: 14px;
            }

            .day-head {
                flex-wrap: wrap;
                gap: 6px 8px;
            }

            .day-name {
                font-size: 13px;
            }

            .filter-buttons {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 6px;
            }

            .filter-btn {
                font-size: 12px;
                padding: 8px 12px;
                border-radius: 10px;
                justify-content: flex-start;
                min-width: 0;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .filter-btn[data-room="all"] {
                grid-column: 1 / -1;
                justify-content: center;
            }

            .card-title {
                font-size: 14px;
            }

            footer {
                padding: 16px 1rem calc(16px + env(safe-area-inset-bottom));
            }
        }

        /* HP kecil (<= 380px) */
        @media (max-width: 380px) {
            .nav-logo img {
                height: 26px;
            }

            .page-title {
                font-size: 15px;
            }

            .booking-card-mobile {
                padding: 12px;
                gap: 10px;
            }

            .card-meta {
                gap: 5px 8px;
            }
        }

        /* Layar sentuh: target tap lebih besar */
        @media (hover: none) and (pointer: coarse) {
            .filter-btn {
                min-height: 36px;
            }

            .range-tab,
            .btn-ghost,
            .btn-solid {
                min-height: 36px;
            }

            .schedule-table tbody tr:hover {
                background: transparent;
            }
        }

        /* HP landscape (tinggi pendek): navbar tidak menempel */
        @media (max-height: 480px) and (orientation: landscape) {
            nav {
                position: static;
            }
        }

        /* ===== KARTU MOBILE: gaya chip ===== */
        .booking-card-mobile {
            padding: 16px 18px;
            gap: 12px;
        }

        .booking-card-mobile.is-done {
            opacity: .75;
        }

        .card-title {
            font-size: 15px;
            font-weight: 800;
            line-height: 1.35;
        }

        .card-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 10px;
            align-items: center;
        }

        .chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 9px;
            border-radius: 7px;
            background: #f1f5f9;
            color: var(--body);
            font-size: 11.5px;
            font-weight: 600;
            line-height: 1.2;
            max-width: 100%;
            min-width: 0;
        }

        .chip svg {
            width: 12px;
            height: 12px;
            flex-shrink: 0;
            color: var(--muted);
        }

        .chip-time {
            background: var(--indigo-soft);
            color: var(--indigo-dark);
            font-weight: 800;
            font-size: 12px;
            font-variant-numeric: tabular-nums;
        }

        .card-status {
            margin-top: 10px;
        }

        .status-badge.has-icon::before {
            display: none;
        }

        @media (max-width: 900px) {
            .day-name {
                text-transform: uppercase;
                letter-spacing: .03em;
                font-size: 12px;
            }

            .day:not(.today) .day-head {
                background: #eef2f7;
            }

            .day-tag {
                text-transform: none;
                letter-spacing: 0;
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
            <div class="clock" aria-live="off">
                <div class="clock-time" id="clockTime">--.--</div>
                <div class="clock-date" id="clockDate"></div>
            </div>
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
                <h1 class="page-title">Jadwal Ruang Rapat</h1>
                <div class="page-subtitle" id="dateRangeLabel">Memuat jadwal…</div>
            </div>
            <div class="range-tabs" role="tablist" aria-label="Rentang jadwal">
                <button class="range-tab active" role="tab" onclick="setRange(3,this)">3 Hari</button>
                <button class="range-tab" role="tab" onclick="setRange(7,this)">7 Hari</button>
                <button class="range-tab" role="tab" onclick="setRange(14,this)">2 Minggu</button>
            </div>
        </div>

        <div class="filter-wrap">
            <span class="filter-label">Ruangan:</span>
            <div class="filter-buttons" id="roomFilters">
                <button class="filter-btn active" data-room="all" onclick="setRoom('all',this)">Semua Ruang</button>
            </div>
        </div>

        <div id="tableWrap">
            <div class="state"><span class="spinner"></span>Memuat jadwal…</div>
        </div>

        @guest
            <div class="login-cta">
                <div class="login-cta-text">Ingin mengajukan booking ruang rapat? <span>Silakan masuk untuk menambahkan
                        agenda.</span></div>
                <a href="{{ route('login') }}" class="btn-login-cta">Masuk sekarang</a>
            </div>
        @endguest
    </div>

    <footer>© {{ date('Y') }} Kementerian Koordinator Bidang Pangan Republik Indonesia</footer>

    <script>
        const RC = {
            1: { dot: '#3d3d3d', label: 'Ruang Rapat Utama' },
            2: { dot: '#7b68aa', label: 'Ruang Rapat Setmenko' },
            3: { dot: '#92400e', label: 'Ruang Rapat D1' },
            4: { dot: '#f0c040', label: 'Ruang Rapat D2' },
            5: { dot: '#4bbfd4', label: 'Ruang Rapat D3' },
            6: { dot: '#e8604c', label: 'Ruang Rapat D4' },
            7: { dot: '#ec4899', label: 'Ruang Dharma Wanita' },
            8: { dot: '#468432', label: 'Ruang Rapat ABT' },
        }
        const DAYS = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
        const MON = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
        let all = [], room = 'all', range = 3
        const mobile = () => window.innerWidth <= 900
        const pad = n => String(n).padStart(2, '0')
        const dateStr = d => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`
        const fmtT = s => { const p = s.replace('T', ' ').split(/[- :]/); return `${p[3]}.${p[4]}` }
        const isToday = s => s === dateStr(new Date())
        const getRange = n => { const s = new Date(); s.setHours(0, 0, 0, 0); const e = new Date(s); e.setDate(e.getDate() + n); return { s, e } }
        const esc = s => s ? String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;') : ''
        const clip = (s, n) => esc(s.length > n ? s.substring(0, n) + '…' : s)
        const dur = b => { const m = Math.round((new Date(b.end) - new Date(b.start)) / 60000); if (m <= 0) return ''; return (m >= 60 ? Math.floor(m / 60) + ' jam ' : '') + (m % 60 ? (m % 60) + ' menit' : '') }

        function status(b) {
            const now = new Date(), st = new Date(b.start), en = new Date(b.end), d = (st - now) / 60000
            if (b.status === 'PENDING') return { c: 'status-pending', l: 'Menunggu', k: 'wait' }
            if (now >= st && now <= en) return { c: 'status-berlangsung', l: 'Berlangsung', k: 'live' }
            if (now > en) return { c: 'status-selesai', l: 'Selesai', k: 'done' }
            if (d <= 30) return { c: 'status-segera', l: 'Segera dimulai', k: 'soon' }
            return { c: 'status-terjadwal', l: 'Terjadwal', k: 'ok' }
        }

        function tickClock() {
            const n = new Date()
            document.getElementById('clockTime').textContent = `${pad(n.getHours())}.${pad(n.getMinutes())}`
            document.getElementById('clockDate').textContent = `${DAYS[n.getDay()]}, ${n.getDate()} ${MON[n.getMonth()]} ${n.getFullYear()}`
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

        async function load(silent) {
            const { s, e } = getRange(range)
            try {
                const r = await fetch(`/api/display-bookings?start=${dateStr(s)}&end=${dateStr(e)}`)
                const d = await r.json()
                all = d.map(e => ({ title: e.title, start: (e.start || '').replace(' ', 'T'), end: (e.end || '').replace(' ', 'T'), room_id: e.extendedProps?.room_id ?? e.room_id, room_name: e.extendedProps?.room_name ?? e.room_name ?? null, unit_kerja: e.extendedProps?.unit_kerja ?? e.unit_kerja ?? '-', status: e.extendedProps?.status ?? e.status ?? 'APPROVED', description: e.extendedProps?.description ?? e.description ?? '' }))
                render()
            } catch (err) {
                if (!silent) document.getElementById('tableWrap').innerHTML = '<div class="state"><strong>Gagal memuat data</strong>Periksa koneksi Anda, lalu muat ulang halaman.</div>'
            }
        }

        function setRoom(r, el) { room = r; document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active')); el.classList.add('active'); render() }
        function setRange(r, el) { range = r; document.querySelectorAll('.range-tab').forEach(b => b.classList.remove('active')); el.classList.add('active'); load() }

        function rowDesktop(b, i) {
            const inf = RC[b.room_id] || { dot: '#94a3b8', label: `Ruang ${b.room_id}` }
            const rn = b.room_name ?? inf.label, st = status(b)
            const cls = st.k === 'done' ? 'is-done' : st.k === 'live' ? 'is-live' : ''
            return `<tr class="${cls}"><td class="td-no" style="--bar:${inf.dot}">${i + 1}</td>`
                + `<td><div class="booking-title">${esc(b.title)}</div>${b.description ? `<div class="booking-desc">${clip(b.description, 70)}</div>` : ''}</td>`
                + `<td><span class="room-name"><span class="room-dot" style="background:${inf.dot}"></span>${esc(rn)}</span></td>`
                + `<td><div class="time-main">${fmtT(b.start)} – ${fmtT(b.end)}</div><div class="time-dur">${dur(b)}</div></td>`
                + `<td><div class="pic" title="${esc(b.unit_kerja)}">${esc(b.unit_kerja ?? '-')}</div></td>`
                + `<td><span class="status-badge ${st.c}">${st.l}</span></td></tr>`
        }

        const ICON_BLD = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18M5 21V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v16M19 21V11a1 1 0 0 0-1-1h-3M9 7h2M9 11h2M9 15h2"/></svg>'

        function cardMobile(b) {
            const inf = RC[b.room_id] || { dot: '#94a3b8', label: `Ruang ${b.room_id}` }
            const rn = b.room_name ?? inf.label, st = status(b)
            const icon = st.k === 'done' ? '✓ ' : ''
            return `<div class="booking-card-mobile ${st.k === 'done' ? 'is-done' : ''}"><div class="card-bar" style="background:${inf.dot}"></div><div class="card-body">`
                + `<div class="card-title">${esc(b.title)}</div>${b.description ? `<div class="card-desc">${clip(b.description, 80)}</div>` : ''}`
                + `<div class="card-meta"><span class="chip chip-time">${fmtT(b.start)} – ${fmtT(b.end)}</span>`
                + `<span class="chip"><span class="room-dot" style="background:${inf.dot}"></span>${esc(rn)}</span>`
                + `<span class="chip">${ICON_BLD}${esc(b.unit_kerja ?? '-')}</span></div>`
                + `<div class="card-status"><span class="status-badge ${st.c}${icon ? ' has-icon' : ''}">${icon}${st.l}</span></div></div></div>`
        }

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

            if (!keys.length) {
                const maint = (window.MAINTENANCE_ROOMS || []).map(Number)
                const msg = (room !== 'all' && maint.includes(Number(room)))
                    ? '<strong>Ruang tidak tersedia</strong>Ruang rapat ini digunakan untuk Kegiatan BPK.'
                    : '<strong>Belum ada jadwal</strong>Tidak ada kegiatan pada periode ini.'
                wrap.innerHTML = `<div class="state">${msg}</div>`
                return
            }

            const tmr = new Date(); tmr.setDate(tmr.getDate() + 1)
            keys.forEach(dk => {
                const items = g[dk], d = new Date(dk + 'T00:00:00'), td = isToday(dk)
                const tag = td ? 'Hari ini' : dk === dateStr(tmr) ? 'Besok' : ''
                const sec = document.createElement('section'); sec.className = 'day' + (td ? ' today' : '')
                let h = `<div class="day-head"><span class="day-name">${DAYS[d.getDay()]}, ${pad(d.getDate())} ${MON[d.getMonth()]} ${d.getFullYear()}</span>${tag ? `<span class="day-tag">${tag}</span>` : ''}<span class="day-count">${items.length} kegiatan</span></div>`
                if (mobile()) {
                    h += `<div class="mobile-cards">${items.map(cardMobile).join('')}</div>`
                } else {
                    h += `<table class="schedule-table desktop-table"><thead><tr><th class="th-no">No.</th><th>Nama Kegiatan</th><th class="th-room">Ruangan</th><th class="th-time">Waktu</th><th class="th-pic">Pengusul</th><th class="th-status">Status</th></tr></thead><tbody>${items.map(rowDesktop).join('')}</tbody></table>`
                }
                sec.innerHTML = h; wrap.appendChild(sec)
            })
        }

        let rt; window.addEventListener('resize', () => { clearTimeout(rt); rt = setTimeout(render, 150) })
        window.MAINTENANCE_ROOMS = []
        fetch('/api/maintenance-rooms').then(r => r.json()).then(ids => { window.MAINTENANCE_ROOMS = ids; render() }).catch(() => { })

        tickClock(); setInterval(tickClock, 30000)
        setInterval(() => load(true), 60000) // segarkan jadwal & status tiap 1 menit
        buildFilters(); load()
    </script>
</body>

</html>