<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle ?? config('app.name', 'Lab Booking') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}" sizes="32x32">
    <link rel="icon" type="image/png" href="{{ asset('favicon-16x16.png') }}" sizes="16x16">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}?v={{ filemtime(public_path('css/site.css')) }}">
</head>
<body>
    <header class="site-header" id="site-header">
        <div class="band-inner site-header-inner">
            <a class="brand" href="{{ route('home') }}">
                <img class="brand-logo" src="{{ asset('images/logo.png') }}" alt="{{ config('app.name', 'Lab Booking') }}">
            </a>

            <nav class="site-nav">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                <a href="{{ route('bookings.lookup') }}" class="{{ request()->routeIs('bookings.lookup') ? 'active' : '' }}">Check Booking</a>
                <a href="#" role="button" onclick="event.preventDefault(); openInfoModal();">Info</a>
                @auth
                    <a class="pill" href="{{ route('admin.dashboard') }}">Admin</a>
                @else
                    <a class="pill" href="{{ route('login') }}">Admin Login</a>
                @endauth
                <a class="cta" href="{{ route('booking.create') }}">Book a Lab</a>
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="site-footer band--dark">
        <div class="band-inner">
            <div class="footer-grid">
                <div>
                    <div class="footer-brand">
                        <img class="footer-logo" src="{{ asset('images/logo.png') }}" alt="{{ config('app.name', 'Lab Booking') }}">
                    </div>
                    <p class="footer-desc">Real-time lab, equipment, and room booking for research, clinical skills, and pharmaceutical labs — built for UniKL RCMP.</p>
                </div>
                <div class="footer-col">
                    <h4>Quick links</h4>
                    <ul>
                        <li><a href="{{ route('home') }}">Home</a></li>
                        <li><a href="{{ route('booking.create') }}">Book a Lab</a></li>
                        <li><a href="{{ route('bookings.lookup') }}">Check Booking</a></li>
                        <li><a href="{{ route('login') }}">Admin Login</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Information</h4>
                    <ul>
                        <li><a href="#" onclick="event.preventDefault(); openInfoModal();">Terms &amp; how to book</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <span>&copy; {{ date('Y') }} {{ config('app.name', 'Lab Booking') }}. All rights reserved.</span>
                <span>Universiti Kuala Lumpur RCMP</span>
            </div>
        </div>
    </footer>

    <nav class="mobile-tabbar">
        <div class="mobile-tabbar-inner">
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l9-7 9 7"/><path d="M5 10v10h14V10"/></svg>
                Home
            </a>
            <a href="{{ route('booking.create') }}" class="{{ request()->routeIs('booking.create') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18M12 14v5M9.5 16.5h5"/></svg>
                Book
            </a>
            <a href="{{ route('bookings.lookup') }}" class="{{ request()->routeIs('bookings.lookup') || request()->routeIs('bookings.show') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                Check
            </a>
            @auth
                <a href="{{ route('admin.dashboard') }}">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6z"/></svg>
                    Admin
                </a>
            @else
                <a href="{{ route('login') }}" class="{{ request()->routeIs('login') ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6z"/></svg>
                    Admin
                </a>
            @endauth
        </div>
    </nav>

    @include('partials.info-modal')

    <script>
        (function () {
            var header = document.getElementById('site-header');
            if (!header) return;
            function onScroll() {
                header.classList.toggle('scrolled', window.scrollY > 8);
            }
            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();
        })();
    </script>
</body>
</html>
