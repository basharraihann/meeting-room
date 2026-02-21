<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ruang Rapat – Kemenkopangan</title>
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
            --text-soft: #334155;
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
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border);
            padding: 0 2rem;
            height: 62px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .nav-logo img {
            height: 38px;
            width: auto;
        }

        .nav-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .btn-ghost {
            padding: 7px 16px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-soft);
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
            padding: 7px 18px;
            background: var(--indigo);
            border-radius: 10px;
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

        .btn-display-nav {
            padding: 7px 14px;
            background: #f1f5f9;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            color: var(--muted);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s;
            white-space: nowrap;
        }

        .btn-display-nav:hover {
            background: #e2e8f0;
            color: var(--text);
        }

        /* ===== HERO ===== */
        .hero {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 80px 1.5rem 60px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero-glow {
            position: absolute;
            top: -100px;
            left: 50%;
            transform: translateX(-50%);
            width: 800px;
            height: 800px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.09) 0%, transparent 65%);
            pointer-events: none;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: var(--indigo-light);
            color: var(--indigo);
            border: 1px solid #c7d2fe;
            border-radius: 99px;
            padding: 6px 16px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            margin-bottom: 28px;
            animation: fadeUp 0.5s ease both;
        }

        .badge-dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: var(--indigo);
            flex-shrink: 0;
        }

        h1 {
            font-size: clamp(2.4rem, 6vw, 3.75rem);
            font-weight: 900;
            line-height: 1.08;
            color: var(--text);
            max-width: 620px;
            margin-bottom: 20px;
            letter-spacing: -0.035em;
            animation: fadeUp 0.5s 0.07s ease both;
        }

        .hero-sub {
            font-size: 15.5px;
            color: var(--muted);
            line-height: 1.8;
            max-width: 420px;
            margin-bottom: 40px;
            font-weight: 400;
            animation: fadeUp 0.5s 0.14s ease both;
        }

        .hero-cta {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeUp 0.5s 0.21s ease both;
        }

        .btn-cta-primary {
            padding: 13px 30px;
            background: var(--indigo);
            color: white;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s;
            box-shadow: 0 4px 18px rgba(79, 70, 229, 0.32);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-cta-primary:hover {
            background: var(--indigo-dark);
            transform: translateY(-1px);
            box-shadow: 0 6px 22px rgba(79, 70, 229, 0.4);
        }

        .btn-cta-outline {
            padding: 13px 28px;
            background: white;
            color: var(--text-soft);
            border: 1.5px solid var(--border);
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-cta-outline:hover {
            border-color: var(--indigo);
            color: var(--indigo);
        }

        .btn-cta-display {
            padding: 13px 32px;
            background: var(--indigo-light);
            color: var(--indigo);
            border: 1.5px solid #c7d2fe;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 10px rgba(79, 70, 229, 0.12);
        }

        .btn-cta-display:hover {
            background: #e0e7ff;
            border-color: var(--indigo);
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(79, 70, 229, 0.18);
        }

        /* ===== DIVIDER ===== */
        .divider {
            width: 1px;
            height: 36px;
            background: var(--border);
            margin: 0 4px;
        }

        /* ===== FEATURES ===== */
        .features {
            padding: 0 1.5rem 72px;
            max-width: 900px;
            margin: 0 auto;
            width: 100%;
            animation: fadeUp 0.5s 0.28s ease both;
        }

        .features-label {
            text-align: center;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.09em;
            color: #94a3b8;
            margin-bottom: 20px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }

        .feature-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 22px 20px;
            transition: all 0.2s;
        }

        .feature-card:hover {
            border-color: #c7d2fe;
            box-shadow: 0 4px 20px rgba(79, 70, 229, 0.07);
            transform: translateY(-2px);
        }

        .feature-icon {
            width: 38px;
            height: 38px;
            background: var(--indigo-light);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            margin-bottom: 13px;
        }

        .feature-title {
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 6px;
            color: var(--text);
        }

        .feature-desc {
            font-size: 12px;
            color: var(--muted);
            line-height: 1.65;
        }

        /* ===== FOOTER ===== */
        footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: var(--muted);
            border-top: 1px solid var(--border);
            background: white;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(14px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 640px) {
            nav {
                padding: 0 1rem;
                height: 56px;
            }

            .btn-display-nav {
                display: none;
            }

            .hero {
                padding: 52px 1rem 44px;
            }

            h1 {
                font-size: 2.1rem;
            }

            .hero-sub {
                font-size: 14px;
            }

            .hero-cta {
                flex-direction: column;
                width: 100%;
                max-width: 280px;
            }

            .btn-cta-primary,
            .btn-cta-outline,
            .btn-cta-display {
                width: 100%;
                justify-content: center;
                padding: 13px 20px;
            }

            .divider {
                display: none;
            }

            .features {
                padding: 0 1rem 52px;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 400px) {

            .btn-ghost,
            .btn-solid {
                padding: 6px 12px;
                font-size: 12px;
            }
        }
    </style>
</head>

<body>

    <nav>
        <div class="nav-logo">
            <img src="{{ asset('images/logoheader.png') }}" alt="Logo Kemenkopangan">
        </div>

        @if(Route::has('login'))
            <div class="nav-actions">

                @auth
                    <a href="{{ url('/dashboard') }}" class="btn-solid">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn-ghost">Masuk</a>
                    @if(Route::has('register'))
                        <a href="{{ route('register') }}" class="btn-solid">Daftar</a>
                    @endif
                @endauth
            </div>
        @endif
    </nav>

    <section class="hero">
        <div class="hero-glow"></div>

        <div class="hero-badge">
            <div class="badge-dot"></div>
            Sistem Manajemen Internal
        </div>

        <h1>Booking Ruang Rapat</h1>

        <p class="hero-sub">
            Platform terpusat untuk menjadwalkan dan mengelola penggunaan ruang rapat di lingkungan Kementerian
            Koordinator Bidang Pangan.
        </p>

        <div class="hero-cta">
            @if(Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn-cta-primary">Buka Dashboard →</a>
                    <a href="{{ route('display') }}" class="btn-cta-display">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                            stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="14" rx="2" />
                            <path d="M8 21h8M12 17v4" />
                        </svg>
                        Lihat Display
                    </a>
                @else
                    <a href="{{ route('display') }}" class="btn-cta-display">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                            stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="14" rx="2" />
                            <path d="M8 21h8M12 17v4" />
                        </svg>
                        Cek Ketersediaan Ruang Rapat
                    </a>
                @endauth
            @endif
        </div>
    </section>



    <footer>
        © {{ date('Y') }} Kementerian Koordinator Bidang Pangan Republik Indonesia
    </footer>

</body>

</html>