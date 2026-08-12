<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle ?? config('app.name', 'Lab Booking') }}</title>
    <style>
        :root {
            --bg: #eef3ef;
            --panel: rgba(255, 255, 255, 0.78);
            --panel-strong: #ffffff;
            --ink: #13212a;
            --muted: #5c6d75;
            --line: rgba(19, 33, 42, 0.12);
            --brand: #1e5f6b;
            --brand-2: #173845;
            --accent: #d9a441;
            --shadow: 0 24px 60px rgba(21, 38, 45, 0.12);
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at top left, rgba(30, 95, 107, 0.12), transparent 30%),
                radial-gradient(circle at 85% 10%, rgba(217, 164, 65, 0.16), transparent 24%),
                linear-gradient(180deg, #f5f8f5 0%, #eef3ef 100%);
        }

        a { color: inherit; }
        .wrap { width: min(1160px, calc(100% - 32px)); margin: 0 auto; }
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 0 22px;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: inherit;
        }

        .brand-mark {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            background: linear-gradient(145deg, var(--brand), var(--brand-2));
            box-shadow: 0 12px 24px rgba(30, 95, 107, 0.25);
            display: grid;
            place-items: center;
            color: #fff;
            font-weight: 800;
        }

        .brand-text strong { display: block; line-height: 1.1; font-size: 1rem; }
        .brand-text span { font-size: 0.82rem; color: var(--muted); }

        .nav { display: flex; flex-wrap: wrap; gap: 10px; }
        .nav a {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 14px; border-radius: 999px; border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.62); color: var(--muted); text-decoration: none; font-size: 0.92rem;
        }

        .panel { background: var(--panel); border: 1px solid rgba(255, 255, 255, 0.72); backdrop-filter: blur(16px); box-shadow: var(--shadow); border-radius: 28px; }
        .page { padding: 8px 0 44px; }
        .hero-copy, .card, .booking-row, .block-row, .summary-card { background: var(--panel-strong); border: 1px solid var(--line); border-radius: 22px; }
        .hero-copy { padding: 26px; }

        .eyebrow {
            display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; border-radius: 999px;
            background: rgba(30, 95, 107, 0.1); color: var(--brand); font-size: 0.78rem; font-weight: 700;
            letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 18px;
        }

        h1, h2, h3, p { margin-top: 0; }
        .title { margin: 0; font-size: clamp(2rem, 4vw, 3.4rem); line-height: 1; letter-spacing: -0.04em; }
        .lede, .muted { color: var(--muted); }
        .stack { display: grid; gap: 14px; }
        .grid-2 { display: grid; grid-template-columns: 1.25fr 0.75fr; gap: 16px; }
        .grid-3 { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
        .summary-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-top: 22px; }
        .summary-card { padding: 16px; }
        .summary-card strong { display: block; font-size: 1.5rem; line-height: 1; }
        .summary-card span { display: block; margin-top: 6px; font-size: 0.84rem; color: var(--muted); }

        .section-title {
            display: flex; align-items: end; justify-content: space-between; gap: 16px; margin: 26px 0 14px;
        }

        .section-title h2 { margin: 0; font-size: 1.35rem; }
        .section-title p { margin: 0; color: var(--muted); max-width: 58ch; }
        .badge {
            display: inline-flex; align-items: center; gap: 6px; padding: 7px 10px; border-radius: 999px; font-size: 0.76rem;
            font-weight: 700; color: #114f58; background: rgba(30, 95, 107, 0.12); white-space: nowrap;
        }
        .empty { padding: 20px; color: var(--muted); text-align: center; }
        .button {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 44px; padding: 0 16px;
            border-radius: 14px; text-decoration: none; font-weight: 700; border: 1px solid transparent;
            transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        }
        .button:hover { transform: translateY(-1px); }
        .button-primary { color: #fff; background: linear-gradient(145deg, var(--brand), var(--brand-2)); box-shadow: 0 16px 28px rgba(30, 95, 107, 0.22); }
        .button-secondary { color: var(--ink); background: rgba(255, 255, 255, 0.72); border-color: var(--line); }
        .footer { padding: 8px 0 32px; color: var(--muted); font-size: 0.92rem; }

        @media (max-width: 920px) {
            .grid-2, .grid-3, .summary-grid { grid-template-columns: 1fr; }
            .section-title, .topbar { align-items: flex-start; flex-direction: column; }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <header class="topbar">
            <a class="brand" href="{{ route('home') }}">
                <div class="brand-mark">LB</div>
                <div class="brand-text">
                    <strong>{{ config('app.name', 'Lab Booking') }}</strong>
                    <span>Laravel booking system</span>
                </div>
            </a>

            <nav class="nav">
                <a href="{{ route('home') }}">Home</a>
                <a href="{{ route('bookings.index') }}">Bookings</a>
                <a href="{{ route('bookings.lookup') }}">Check Booking</a>
                <a href="{{ route('bookings.mine') }}">My Bookings</a>
            </nav>
        </header>

        <main class="page">
            @yield('content')
        </main>

        <footer class="footer">
            Laravel now reads directly from the lab booking database.
        </footer>
    </div>
</body>
</html>