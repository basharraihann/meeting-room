<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ruang Rapat – Kemenkopangan</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
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
            --blue: #4F46E5;
            --blue-dark: #3730A3;
            --blue-light: #EEF2FF;
            --blue-mid: #6366F1;
            --text: #1E1B4B;
            --muted: #6B7280;
            --border: #E5E7EB;
            --white: #FFFFFF;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #F8F9FF;
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* NAV */
        nav {
            background: white;
            border-bottom: 1px solid var(--border);
            padding: 0 2rem;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .nav-logo {
            width: 40px;
            height: 40px;
            background: var(--blue);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .nav-logo svg {
            width: 22px;
            height: 22px;
            fill: white;
        }

        .nav-title {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .nav-title span:first-child {
            font-size: 11px;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .nav-title span:last-child {
            font-size: 14px;
            font-weight: 700;
            color: var(--text);
        }

        .nav-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .btn-outline {
            padding: 8px 18px;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            color: var(--text);
            text-decoration: none;
            transition: all 0.2s;
            background: white;
        }

        .btn-outline:hover {
            border-color: var(--blue);
            color: var(--blue);
        }

        .btn-primary {
            padding: 8px 18px;
            background: var(--blue);
            border: 1.5px solid var(--blue);
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            color: white;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-primary:hover {
            background: var(--blue-dark);
            border-color: var(--blue-dark);
        }

        /* HERO */
        .hero {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 80px 2rem 60px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -60px;
            left: 50%;
            transform: translateX(-50%);
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.12) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--blue-light);
            color: var(--blue);
            border: 1px solid #C7D2FE;
            border-radius: 100px;
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-bottom: 28px;
            animation: fadeUp 0.6s ease both;
        }

        .hero-badge::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--blue);
        }

        h1 {
            font-size: clamp(2rem, 5vw, 3.25rem);
            font-weight: 800;
            line-height: 1.15;
            color: var(--text);
            max-width: 640px;
            margin-bottom: 20px;
            animation: fadeUp 0.6s 0.1s ease both;
        }

        h1 span {
            color: var(--blue);
        }

        .hero-sub {
            font-size: 16px;
            color: var(--muted);
            line-height: 1.7;
            max-width: 480px;
            margin-bottom: 40px;
            font-weight: 400;
            animation: fadeUp 0.6s 0.2s ease both;
        }

        .hero-cta {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeUp 0.6s 0.3s ease both;
        }

        .btn-cta {
            padding: 12px 28px;
            background: var(--blue);
            color: white;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);
        }

        .btn-cta:hover {
            background: var(--blue-dark);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4);
        }

        .btn-cta-ghost {
            padding: 12px 28px;
            background: white;
            color: var(--text);
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-cta-ghost:hover {
            border-color: var(--blue);
            color: var(--blue);
        }

        /* STATS */
        .stats {
            display: flex;
            gap: 2px;
            justify-content: center;
            margin-top: 56px;
            animation: fadeUp 0.6s 0.4s ease both;
        }

        .stat {
            background: white;
            border: 1px solid var(--border);
            padding: 20px 32px;
            text-align: center;
            flex: 1;
            max-width: 160px;
        }

        .stat:first-child {
            border-radius: 12px 0 0 12px;
        }

        .stat:last-child {
            border-radius: 0 12px 12px 0;
        }

        .stat-number {
            font-size: 24px;
            font-weight: 800;
            color: var(--blue);
            display: block;
        }

        .stat-label {
            font-size: 12px;
            color: var(--muted);
            font-weight: 500;
            margin-top: 2px;
        }

        /* FEATURES */
        .features {
            padding: 60px 2rem;
            max-width: 900px;
            margin: 0 auto;
            width: 100%;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px;
        }

        .feature-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
            transition: all 0.2s;
        }

        .feature-card:hover {
            border-color: #C7D2FE;
            box-shadow: 0 4px 20px rgba(79, 70, 229, 0.08);
            transform: translateY(-2px);
        }

        .feature-icon {
            width: 40px;
            height: 40px;
            background: var(--blue-light);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
            font-size: 18px;
        }

        .feature-title {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 6px;
            color: var(--text);
        }

        .feature-desc {
            font-size: 13px;
            color: var(--muted);
            line-height: 1.6;
        }

        /* FOOTER */
        footer {
            text-align: center;
            padding: 24px;
            font-size: 12px;
            color: var(--muted);
            border-top: 1px solid var(--border);
            background: white;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 600px) {
            .stat {
                padding: 16px 18px;
            }

            .nav-title span:last-child {
                font-size: 12px;
            }
        }
    </style>
</head>

<body>

    <!-- NAV -->
    <nav>
        <div class="nav-brand">
            <img src="{{ asset('images/logoheader.png') }}" alt="Logo Kemenkopangan" style="height:40px;width:auto;">
        </div>

        @if (Route::has('login'))
            <div class="nav-actions">
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn-primary">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn-outline">Masuk</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn-primary">Daftar</a>
                    @endif
                @endauth
            </div>
        @endif
    </nav>

    <!-- HERO -->
    <section class="hero">
        <div class="hero-badge">Sistem Manajemen Internal</div>
        <h1>Booking Ruang Rapat<br></h1>
        <p class="hero-sub">
            Platform terpusat untuk menjadwalkan dan mengelola penggunaan ruang rapat di lingkungan Kementerian
            Koordinator Bidang Pangan.
        </p>
        <div class="hero-cta">
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn-cta">Buka Dashboard →</a>
                @else
                    <a href="{{ route('login') }}" class="btn-cta">Masuk Sekarang →</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn-cta-ghost">Buat Akun</a>
                    @endif
                @endauth
            @endif
        </div>


    </section>

    <footer>
        © {{ date('Y') }} Kementerian Koordinator Bidang Pangan Republik Indonesia
    </footer>

</body>

</html>