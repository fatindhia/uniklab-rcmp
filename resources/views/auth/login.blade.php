<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — {{ config('app.name', 'Lab Booking') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}" sizes="32x32">
    <link rel="icon" type="image/png" href="{{ asset('favicon-16x16.png') }}" sizes="16x16">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #312b2c;
            --ink-2: #221d1e;
            --gold: #7d9194;
            --gold-bright: #aebfc1;
            --white: #ffffff;
            --off-white: #f4efe7;
            --border: #e0d8cc;
            --text: #312b2c;
            --text-mid: #6f6358;
            --text-light: #9a8d80;
            --danger: #d4342a;
            --danger-bg: #fdeceb;
            --radius-sm: 10px;
            --radius: 16px;
            --radius-lg: 22px;
            --grad: linear-gradient(135deg, #7d9194 0%, #61777a 55%, #4e6568 100%);
            --grid-line: rgba(255, 255, 255, 0.055);
            --font-serif: 'Sora', system-ui, sans-serif;
            --font-sans: 'Plus Jakarta Sans', system-ui, sans-serif;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { font-size: 16px; }
        body {
            font-family: var(--font-sans);
            min-height: 100vh;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 40px 20px;
            position: relative; overflow-x: hidden;
            background:
                radial-gradient(60% 50% at 18% 8%, rgba(125, 145, 148, 0.22), transparent 60%),
                radial-gradient(50% 45% at 88% 92%, rgba(148, 128, 111, 0.16), transparent 60%),
                linear-gradient(165deg, var(--ink) 0%, var(--ink-2) 100%);
        }
        body::before {
            content: ''; position: absolute; inset: 0; pointer-events: none;
            background-image:
                linear-gradient(var(--grid-line) 1px, transparent 1px),
                linear-gradient(90deg, var(--grid-line) 1px, transparent 1px);
            background-size: 34px 34px;
            mask-image: radial-gradient(70% 60% at 50% 30%, #000 40%, transparent 100%);
        }

        .login-top { position: relative; z-index: 1; text-align: center; margin-bottom: 30px; max-width: 420px; }
        .login-mark {
            width: 62px; height: 62px; margin: 0 auto 18px; border-radius: var(--radius);
            background: #fff; display: flex; align-items: center; justify-content: center; padding: 9px;
            box-shadow: 0 20px 40px -16px rgba(125, 145, 148, 0.6);
        }
        .login-mark img { width: 100%; height: 100%; object-fit: contain; }
        .login-top h1 { font-family: var(--font-serif); font-size: 1.6rem; font-weight: 800; color: #fff; letter-spacing: -0.02em; margin-bottom: 8px; }
        .login-top p { font-size: 0.92rem; color: rgba(255, 255, 255, 0.5); line-height: 1.5; }

        .login-card {
            position: relative; z-index: 1; width: 100%; max-width: 420px;
            background: var(--white); border-radius: var(--radius-lg);
            box-shadow: 0 40px 80px -30px rgba(0, 0, 0, 0.55);
            padding: 30px 30px 26px;
        }

        .login-card-head { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
        .lock-icon {
            width: 38px; height: 38px; border-radius: var(--radius-sm); background: var(--off-white); border: 1px solid var(--border);
            display: flex; align-items: center; justify-content: center; font-size: 1.05rem; flex-shrink: 0;
        }
        .login-card-head h2 { font-family: var(--font-serif); font-size: 1.1rem; color: var(--text); font-weight: 700; }
        .login-card-head p { font-size: 0.78rem; color: var(--text-light); margin-top: 2px; }

        .restricted-notice {
            display: flex; align-items: flex-start; gap: 10px;
            background: var(--off-white); border: 1px solid var(--border); border-left: 3px solid var(--ink);
            border-radius: var(--radius-sm); padding: 12px 14px; margin-bottom: 20px; font-size: 0.82rem; color: var(--ink); line-height: 1.55;
        }

        .login-error {
            display: flex; align-items: center; gap: 8px;
            background: var(--danger-bg); border: 1px solid #f0a89f; border-left: 3px solid var(--danger); color: var(--danger);
            padding: 11px 14px; border-radius: var(--radius-sm); font-size: 0.83rem; margin-bottom: 18px;
        }
        .login-status {
            display: flex; align-items: center; gap: 8px;
            background: var(--off-white); border: 1px solid var(--border); border-left: 3px solid var(--gold); color: var(--text);
            padding: 11px 14px; border-radius: var(--radius-sm); font-size: 0.83rem; margin-bottom: 18px;
        }

        .sso-divider { display: flex; align-items: center; gap: 12px; margin: 18px 0; color: var(--text-light); font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; }
        .sso-divider::before, .sso-divider::after { content: ''; height: 1px; background: var(--border); flex: 1; }

        .btn-sso {
            width: 100%; display: flex; align-items: center; justify-content: center; gap: 9px;
            min-height: 46px; padding: 10px 16px; border: 1.5px solid var(--border); border-radius: var(--radius-sm);
            background: var(--off-white); color: var(--text-mid); font-family: var(--font-sans);
            font-size: 0.86rem; font-weight: 700; cursor: not-allowed; opacity: 0.72;
        }
        .sso-mark { width: 16px; height: 16px; display: grid; grid-template-columns: 1fr 1fr; grid-template-rows: 1fr 1fr; gap: 2px; flex-shrink: 0; }
        .sso-mark span:nth-child(1) { background: #f25022; }
        .sso-mark span:nth-child(2) { background: #7fba00; }
        .sso-mark span:nth-child(3) { background: #00a4ef; }
        .sso-mark span:nth-child(4) { background: #ffb900; }

        .back-link {
            display: flex; align-items: center; justify-content: center; gap: 6px; margin-top: 22px; position: relative; z-index: 1;
            font-size: 0.85rem; color: rgba(255, 255, 255, 0.6); text-decoration: none; min-height: 44px;
        }
        .back-link:hover { color: #fff; }

        .login-note { position: relative; z-index: 1; text-align: center; margin-top: 6px; font-size: 0.74rem; color: rgba(255, 255, 255, 0.32); line-height: 1.6; max-width: 360px; }

        @media (max-width: 480px) {
            .login-card { padding: 24px 20px 22px; }
        }
        @media (prefers-reduced-motion: reduce) { * { transition: none !important; } }
    </style>
</head>
<body>
    <div class="login-top">
        <div class="login-mark"><img src="{{ asset('images/logo-square.png') }}" alt="{{ config('app.name', 'Lab Booking') }}"></div>
        <h1>{{ config('app.name', 'Lab Booking') }} Admin</h1>
        <p>Lab booking system — lab staff &amp; super admin portal.</p>
    </div>

    <div class="login-card">
        <div class="login-card-head">
            <div class="lock-icon">🔒</div>
            <div>
                <h2>Lab Staff Access</h2>
                <p>Restricted — authorised staff only</p>
            </div>
        </div>

        <div class="restricted-notice">
            <span style="font-size:1rem; flex-shrink:0;">⚠️</span>
            <span>This portal is for <strong>lab staff and super admins only</strong>.</span>
        </div>

        @if (session('status'))
            <div class="login-status">
                <span>ℹ️</span> <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="login-error">
                <span>❌</span> <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <button type="button" class="btn-sso" disabled aria-disabled="true" title="Microsoft SSO is not enabled yet">
            <span class="sso-mark" aria-hidden="true"><span></span><span></span><span></span><span></span></span>
            Login with Microsoft SSO — Coming Soon
        </button>

        <div class="sso-divider">or</div>

        <div style="background: var(--off-white); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 14px; margin-bottom: 4px;">
            <p style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-mid); margin-bottom: 8px;">
                ⚡ Quick login <span style="font-weight:500; text-transform:none; letter-spacing:0;">(temporary, pre-SSO — no password)</span>
            </p>
            <form method="POST" action="{{ route('login.quick') }}">
                @csrf
                <select name="staff_id" required
                    style="width:100%; min-height:44px; padding:0 12px; border:1.5px solid var(--border); border-radius:var(--radius-sm); font-family:var(--font-sans); font-size:0.88rem; color:var(--text); background:var(--white); margin-bottom:10px;">
                    <option value="" disabled selected>Select a staff account…</option>
                    @foreach ($quickLoginUsers as $u)
                        <option value="{{ $u->staff_id }}">{{ $u->staff_id }} — {{ $u->full_name }} ({{ $u->role?->label }})</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-sso" style="cursor:pointer; opacity:1;">
                    <span class="sso-mark" aria-hidden="true"><span></span><span></span><span></span><span></span></span>
                    Continue as selected user
                </button>
            </form>
        </div>
    </div>

    <a href="{{ route('home') }}" class="back-link">← Back to Public Booking Site</a>
    <p class="login-note">Access is logged. Unauthorised attempts are monitored.<br>&copy; {{ date('Y') }} Universiti Kuala Lumpur RCMP</p>
</body>
</html>
