<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('subject', config('app.name'))</title>
<style>
    body { margin:0; padding:0; background:#efe8df; font-family:'Plus Jakarta Sans', Inter, ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif; color:#312b2c; }
    a { color:#4a5f62; }
    .wrap { width:100%; padding:32px 16px; }
    .card { max-width:600px; margin:0 auto; background:#ffffff; border:1px solid rgba(49,43,44,0.13); border-radius:16px; padding:36px 34px; }
    .eyebrow { font-size:0.72rem; text-transform:uppercase; letter-spacing:0.08em; color:#94806f; font-weight:700; margin:0 0 6px; }
    h1 { font-family:'Sora','Plus Jakarta Sans',sans-serif; font-size:1.4rem; margin:0 0 4px; letter-spacing:-0.02em; color:#221d1e; }
    .lede { color:#7a6d60; margin:0 0 22px; font-size:0.95rem; line-height:1.5; }
    .pill { display:inline-block; padding:6px 13px; border-radius:999px; font-size:0.76rem; font-weight:800; margin-bottom:20px; }
    .pill--approved { background:rgba(30,107,59,0.12); color:#1e6b3b; }
    .pill--pending { background:rgba(160,124,31,0.14); color:#a07c1f; }
    .pill--rejected { background:rgba(160,48,39,0.12); color:#a03027; }
    .pill--cancelled { background:rgba(122,109,96,0.16); color:#7a6d60; }
    .rows { border-top:1px solid rgba(49,43,44,0.13); margin-top:8px; padding-top:18px; }
    .row { padding:6px 0; font-size:0.92rem; }
    .row .lbl { display:inline-block; min-width:140px; color:#7a6d60; font-weight:600; }
    .row .val { font-weight:700; color:#221d1e; }
    .note { margin-top:22px; padding:14px 16px; border-left:3px solid #94806f; background:rgba(148,128,111,0.07); border-radius:8px; font-size:0.9rem; line-height:1.55; white-space:pre-line; }
    .btn { display:inline-block; margin-top:26px; padding:12px 22px; background:#4a5f62; color:#ffffff !important; text-decoration:none; border-radius:10px; font-weight:700; font-size:0.9rem; }
    .footer { max-width:600px; margin:22px auto 0; text-align:center; color:#7a6d60; font-size:0.78rem; }
</style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <p class="eyebrow">{{ config('app.name') }}</p>
        @yield('content')
    </div>
    <p class="footer">This is an automated message from {{ config('app.name') }} &mdash; please do not reply directly to this email.</p>
</div>
</body>
</html>
