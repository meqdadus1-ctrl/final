<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <meta name="theme-color" content="#3b1a08" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <title>Fox Plus HRM — Sign In</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --brown:        #3b1a08;
            --brown-mid:    #5c2d0e;
            --orange:       #f47c20;
            --orange-dark:  #c85f0a;
            --white:        #ffffff;
            --off-white:    #fdf7f3;
            --gray-soft:    #e8ddd5;
            --gray-text:    #8a7060;
            --error:        #d94f4f;
        }

        html, body {
            min-height: 100%;
            min-height: 100dvh;
        }

        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--brown);
            background-image:
                radial-gradient(ellipse 80% 50% at 50% 0%,   rgba(244,124,32,.15) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 50% 100%, rgba(244,124,32,.10) 0%, transparent 55%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: max(env(safe-area-inset-top), 24px) 16px
                     max(env(safe-area-inset-bottom), 24px) 16px;
        }

        /* ── Logo above the card ── */
        .logo {
            width: clamp(180px, 40vw, 260px);
            height: auto;
            display: block;
            margin-bottom: 24px;
            filter: drop-shadow(0 6px 18px rgba(0,0,0,.5));
        }

        /* ── Card ── */
        .card {
            width: 100%;
            max-width: 420px;
            background: var(--off-white);
            border-radius: 16px;
            box-shadow: 0 24px 60px rgba(0,0,0,.4), 0 4px 16px rgba(0,0,0,.25);
            padding: 36px 36px 32px;
        }

        .card-title {
            font-size: 22px;
            font-weight: 700;
            color: var(--brown);
            text-align: center;
            margin-bottom: 4px;
        }

        .card-subtitle {
            font-size: 13.5px;
            color: var(--gray-text);
            text-align: center;
            margin-bottom: 28px;
        }

        /* ── Alerts ── */
        .alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            border-radius: 10px;
            padding: 11px 14px;
            font-size: 13px;
            margin-bottom: 18px;
            line-height: 1.45;
        }

        .alert svg { flex-shrink: 0; margin-top: 1px; }

        .alert-error {
            background: rgba(217,79,79,.1);
            border: 1.5px solid rgba(217,79,79,.3);
            color: var(--error);
        }

        .alert-success {
            background: rgba(34,150,90,.1);
            border: 1.5px solid rgba(34,150,90,.3);
            color: #1d7a4a;
        }

        /* ── Fields ── */
        .field { margin-bottom: 18px; }

        .field label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--brown-mid);
            margin-bottom: 6px;
        }

        .input-wrap { position: relative; }

        .input-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-text);
            pointer-events: none;
            transition: color .2s;
            line-height: 0;
        }

        .input-wrap:focus-within .input-icon { color: var(--orange); }

        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 2px solid var(--gray-soft);
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
            color: var(--brown);
            background: var(--white);
            outline: none;
            transition: border-color .2s, box-shadow .2s;
            -webkit-appearance: none;
            appearance: none;
        }

        input::placeholder { color: #c0a898; }

        input:focus {
            border-color: var(--orange);
            box-shadow: 0 0 0 4px rgba(244,124,32,.14);
        }

        input.is-invalid { border-color: var(--error); }
        input.is-invalid:focus { box-shadow: 0 0 0 4px rgba(217,79,79,.14); }

        .field-error {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            color: var(--error);
            margin-top: 5px;
        }

        .toggle-pw {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--gray-text);
            padding: 4px;
            display: flex;
            align-items: center;
            transition: color .2s;
            line-height: 0;
        }

        .toggle-pw:hover { color: var(--orange); }

        /* ── Options row ── */
        .options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 22px;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 7px;
            cursor: pointer;
            user-select: none;
        }

        .remember input[type="checkbox"] {
            width: 16px;
            height: 16px;
            padding: 0;
            border-radius: 4px;
            accent-color: var(--orange);
            cursor: pointer;
            flex-shrink: 0;
        }

        .remember span { font-size: 13px; color: var(--gray-text); }

        .forgot {
            font-size: 13px;
            font-weight: 600;
            color: var(--orange);
            text-decoration: none;
        }

        .forgot:hover { color: var(--orange-dark); text-decoration: underline; }

        /* ── Submit ── */
        .btn-login {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--orange), var(--orange-dark));
            color: var(--white);
            font-family: inherit;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: .04em;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(244,124,32,.4);
            transition: transform .2s, box-shadow .2s;
            -webkit-tap-highlight-color: transparent;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(244,124,32,.5);
        }

        .btn-login:active {
            transform: translateY(0);
            box-shadow: 0 4px 12px rgba(244,124,32,.35);
        }

        .btn-login .btn-text { transition: opacity .2s; }
        .btn-login .spinner {
            display: none;
            position: absolute;
            inset: 0;
            align-items: center;
            justify-content: center;
        }

        .btn-login.loading .btn-text { opacity: 0; }
        .btn-login.loading .spinner { display: flex; }
        .spinner svg { animation: spin .8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── Footer ── */
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: rgba(255,255,255,.3);
        }

        /* ── Responsive ── */
        @media (max-width: 480px) {
            .card { padding: 28px 20px 24px; border-radius: 14px; }
            .logo { width: clamp(160px, 55vw, 220px); }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { transition: none !important; animation: none !important; }
        }
    </style>
</head>
<body>

    <img src="/fox.png" alt="Fox Plus HRM" class="logo" />

    <div class="card">

        <h1 class="card-title">Welcome back</h1>
        <p class="card-subtitle">Sign in to your HRM account</p>

        {{-- Session status --}}
        @if (session('status'))
            <div class="alert alert-success" role="alert">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                {{ session('status') }}
            </div>
        @endif

        {{-- Auth errors --}}
        @if ($errors->any())
            <div class="alert alert-error" role="alert">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" id="loginForm" novalidate>
            @csrf

            {{-- Email --}}
            <div class="field">
                <label for="email">Email address</label>
                <div class="input-wrap">
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="you@company.com"
                        autocomplete="email"
                        inputmode="email"
                        required
                        class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                    />
                    <span class="input-icon" aria-hidden="true">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    </span>
                </div>
                @error('email')
                    <p class="field-error">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Password --}}
            <div class="field">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                        class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                    />
                    <span class="input-icon" aria-hidden="true">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </span>
                    <button type="button" class="toggle-pw" id="togglePw" aria-label="Show password">
                        <svg id="eyeShow" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg id="eyeHide" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </button>
                </div>
                @error('password')
                    <p class="field-error">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Remember / Forgot --}}
            <div class="options">
                <label class="remember">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }} />
                    <span>Remember me</span>
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="forgot">Forgot password?</a>
                @endif
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn-login" id="submitBtn">
                <span class="btn-text">Sign In</span>
                <span class="spinner" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round"><path d="M12 2a10 10 0 1 0 10 10" opacity=".35"/><path d="M12 2a10 10 0 0 1 10 10"/></svg>
                </span>
            </button>

        </form>

    </div>

    <p class="footer">© {{ date('Y') }} Fox Plus HRM · All rights reserved</p>

<script>
    const pwInput  = document.getElementById('password');
    const togglePw = document.getElementById('togglePw');
    const eyeShow  = document.getElementById('eyeShow');
    const eyeHide  = document.getElementById('eyeHide');

    togglePw.addEventListener('click', () => {
        const hidden = pwInput.type === 'password';
        pwInput.type = hidden ? 'text' : 'password';
        eyeShow.style.display = hidden ? 'none' : '';
        eyeHide.style.display = hidden ? '' : 'none';
        togglePw.setAttribute('aria-label', hidden ? 'Hide password' : 'Show password');
    });

    document.getElementById('loginForm').addEventListener('submit', function () {
        document.getElementById('submitBtn').classList.add('loading');
    });

    document.querySelectorAll('input[type="email"], input[type="password"]').forEach(el => {
        el.addEventListener('input', () => el.classList.remove('is-invalid'));
    });
</script>

</body>
</html>
