<x-guest-layout>
    <x-slot name="title">Login - Rupat Kemenko Pangan</x-slot>

    <style>
        :root {
            --deep: #1E1B4B;
            --teal: #6366F1;
            --teal-2: #4F46E5;
            --violet: #A5B4FC;
            --ink: #0F172A;
            --ink-soft: #64748B;
            --ink-faint: #94A3B8;
            --line: #E2E8F0;
            --red: #DC2626;
            --green: #4F46E5;
            --green-bg: #EEF2FF;
            --radius-lg: 24px;
            --radius-sm: 10px;
        }

        .wave-wrap {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
        }

        .wave-wrap svg {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 58vh;
            min-height: 400px;
        }

        .auth-topbar {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 22px 30px;
        }

        .auth-brand {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .auth-brand .seal {
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-brand .seal img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .auth-brand .seal .seal-fallback {
            display: none;
            width: 42px;
            height: 42px;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--teal), var(--violet));
            color: #fff;
            font-weight: 800;
            font-size: 12px;
        }

        .auth-brand .divider {
            width: 1px;
            height: 34px;
            background: var(--line);
            flex-shrink: 0;
        }

        .auth-brand .txt h1 {
            font-size: 15px;
            margin: 0 0 2px;
            font-weight: 700;
            color: var(--ink);
        }

        .auth-brand .txt .sub {
            font-size: 12.5px;
            color: var(--ink-soft);
            font-weight: 500;
        }

        .auth-main {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 20px 60px;
            min-height: calc(100vh - 78px);
        }

        .auth-card {
            width: 100%;
            max-width: 380px;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: var(--radius-lg);
            box-shadow: 0 0 0 6px rgba(99, 102, 241, .06), 0 24px 60px rgba(79, 70, 229, .16);
            padding: 34px 32px 30px;
        }

        .auth-card-head {
            text-align: center;
            margin-bottom: 26px;
        }

        .auth-card-head h3 {
            font-size: 21px;
            margin: 0 0 6px;
            font-weight: 800;
            color: var(--ink);
        }

        .auth-card-head p {
            font-size: 13px;
            color: var(--ink-soft);
            margin: 0;
        }

        .status-banner {
            display: flex;
            align-items: flex-start;
            gap: 9px;
            background: var(--green-bg);
            color: var(--green);
            border: 1px solid #C7D2FE;
            border-radius: var(--radius-sm);
            padding: 11px 13px;
            font-size: 12.5px;
            font-weight: 500;
            margin-bottom: 18px;
        }

        .field {
            margin-bottom: 16px;
        }

        .field label {
            display: block;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 7px;
        }

        .field-input-wrap {
            position: relative;
            display: flex;
            align-items: center;
            border: 1.5px solid var(--line);
            border-radius: var(--radius-sm);
            background: #fff;
            transition: .15s;
        }

        .field-input-wrap:focus-within {
            border-color: var(--teal);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, .12);
        }

        .field-input-wrap.has-error {
            border-color: var(--red);
        }

        .field-input-wrap .lead-ic {
            width: 17px;
            height: 17px;
            color: var(--ink-faint);
            margin-left: 13px;
            flex-shrink: 0;
        }

        .field-input-wrap input {
            flex: 1;
            min-width: 0;
            border: none !important;
            outline: none;
            background: transparent !important;
            padding: 11px 12px;
            font-family: 'Inter', sans-serif;
            font-size: 13.5px;
            color: var(--ink);
            box-shadow: none !important;
        }

        .toggle-pw {
            background: none;
            border: none;
            padding: 0 13px;
            display: flex;
            align-items: center;
            color: var(--ink-faint);
            flex-shrink: 0;
        }

        .toggle-pw:hover {
            color: var(--ink-soft);
        }

        .toggle-pw svg {
            width: 17px;
            height: 17px;
        }

        .field-meta-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: -4px 0 20px;
        }

        .remember-check {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 12.5px;
            color: var(--ink-soft);
            font-weight: 500;
            user-select: none;
        }

        .remember-check input {
            width: 15px;
            height: 15px;
            border-radius: 4px;
            border: 1.5px solid var(--line);
            accent-color: var(--teal);
        }

        .forgot-link {
            font-size: 12.5px;
            font-weight: 600;
            color: var(--teal-2);
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .btn-submit {
            width: 100%;
            background: linear-gradient(90deg, var(--teal), var(--teal-2));
            color: #fff;
            border: none;
            padding: 13px 18px;
            border-radius: 999px;
            font-size: 14px;
            font-weight: 700;
            transition: .15s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 10px 24px rgba(79, 70, 229, .35);
        }

        .btn-submit:hover {
            filter: brightness(1.05);
            transform: translateY(-1px);
        }

        .auth-foot-note {
            text-align: center;
            font-size: 12px;
            color: var(--ink-soft);
            margin-top: 20px;
        }

        .auth-page-footer {
            position: relative;
            z-index: 2;
            text-align: center;
            font-size: 11.5px;
            color: rgba(255, 255, 255, .8);
            padding: 0 20px 26px;
            margin-top: -18px;
        }

        @media (max-width: 640px) {
            .auth-topbar {
                flex-direction: column;
                gap: 14px;
                padding: 26px 20px 16px;
                text-align: center;
            }

            .auth-brand {
                flex-direction: column;
                gap: 10px;
            }

            .auth-brand .divider {
                width: 36px;
                height: 1px;
            }

            .auth-main {
                min-height: auto;
                align-items: flex-start;
                padding: 14px 18px 50px;
            }

            .wave-wrap svg {
                height: 44vh;
                min-height: 280px;
            }
        }

        @media (max-width: 480px) {
            .auth-card {
                padding: 28px 22px 24px;
            }
        }
    </style>

    {{-- Wave beda arah & 2 layer, warna indigo/violet --}}
    <div class="wave-wrap">
        <svg viewBox="0 0 1440 500" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <linearGradient id="waveGradBack" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#C7D2FE" />
                    <stop offset="55%" stop-color="#6366F1" />
                    <stop offset="100%" stop-color="#1E1B4B" />
                </linearGradient>
                <linearGradient id="waveGradFront" x1="100%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="#4F46E5" />
                    <stop offset="100%" stop-color="#1E1B4B" />
                </linearGradient>
            </defs>
            {{-- layer belakang, lengkungan lebih landai --}}
            <path fill="url(#waveGradBack)" opacity="0.55"
                d="M0,220 C220,150 380,290 620,240 C860,190 1020,80 1220,140 C1340,175 1400,210 1440,220 L1440,500 L0,500 Z" />
            {{-- layer depan, lengkungan berlawanan arah --}}
            <path fill="url(#waveGradFront)"
                d="M0,300 C240,340 340,180 560,210 C800,245 900,340 1140,300 C1280,278 1360,250 1440,260 L1440,500 L0,500 Z" />
        </svg>
    </div>

    <header class="auth-topbar">
        <div class="auth-brand">
            <div class="seal">
                <img src="{{ asset('images/logoheader.png') }}" alt="Rupat Kemenko Pangan"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <span class="seal-fallback">RK</span>
            </div>
            <div class="divider"></div>
            <div class="txt">
                <h1>Rupat Kemenko Pangan</h1>
                <div class="sub">Kementerian Koordinator Bidang Pangan</div>
            </div>
        </div>
    </header>

    <main class="auth-main">
        <div class="auth-card">
            <div class="auth-card-head">
                <h3>Masuk</h3>
                <p>Silakan login untuk mengakses platform booking ruang rapat.</p>
            </div>

            @if (session('status'))
                <div class="status-banner">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 11l3 3L22 4" />
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Username --}}
                <div class="field">
                    <label for="username">Username</label>
                    <div class="field-input-wrap {{ $errors->has('username') ? 'has-error' : '' }}">
                        <svg class="lead-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                        <x-text-input id="username" name="username" type="text" :value="old('username')" required
                            autofocus autocomplete="username" placeholder="Masukkan username" />
                    </div>
                    <x-input-error :messages="$errors->get('username')" class="mt-1.5" />
                </div>

                {{-- Password --}}
                <div class="field">
                    <label for="password">Kata Sandi</label>
                    <div class="field-input-wrap {{ $errors->has('password') ? 'has-error' : '' }}">
                        <svg class="lead-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                        <x-text-input id="password" name="password" type="password" required
                            autocomplete="current-password" placeholder="Masukkan kata sandi" />
                        <button type="button" class="toggle-pw" onclick="togglePw()" aria-label="Tampilkan kata sandi">
                            <svg id="pw-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
                </div>

                <div class="field-meta-row">
                    <label class="remember-check" for="remember_me">
                        <input id="remember_me" type="checkbox" name="remember">
                        Ingat saya
                    </label>

                    @if (Route::has('password.request'))
                        <a class="forgot-link" href="{{ route('password.request') }}">Lupa kata sandi?</a>
                    @endif
                </div>

                <button type="submit" class="btn-submit">
                    Masuk ke Akun
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </button>
            </form>

            <div class="auth-foot-note">
                Butuh bantuan akses? Hubungi Biro MKDI.
            </div>
        </div>
    </main>

    <footer class="auth-page-footer">
        Kementerian Koordinator Bidang Pangan &middot; Rupat &copy; {{ date('Y') }}
    </footer>

    <script>
        function togglePw() {
            const input = document.getElementById('password');
            const eye = document.getElementById('pw-eye');
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            eye.innerHTML = isHidden
                ? '<path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a19.7 19.7 0 0 1 4.22-5.47M9.9 4.24A10.4 10.4 0 0 1 12 4c7 0 11 8 11 8a19.7 19.7 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><path d="M1 1l22 22"></path>'
                : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"></path><circle cx="12" cy="12" r="3"></circle>';
        }
    </script>
</x-guest-layout>