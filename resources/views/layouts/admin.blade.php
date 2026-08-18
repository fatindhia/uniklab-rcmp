<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle ?? 'Admin' }} · {{ config('app.name', 'Lab Booking') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}" sizes="32x32">
    <link rel="icon" type="image/png" href="{{ asset('favicon-16x16.png') }}" sizes="16x16">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">
</head>
<body>
    @php
        $__adminLabsByType = ($adminLabsByType ?? collect())->map(fn ($labs) => $labs->map(fn ($l) => [
            'id' => $l->id,
            'name' => $l->name,
            'equipment' => $l->relationLoaded('equipment') ? $l->equipment->pluck('equipment_name')->values() : [],
        ])->values());
        $__pendingByType = (array) ($adminPendingByType ?? []);
        $__pendingTotal = array_sum($__pendingByType);
        $__initials = collect(preg_split('/\s+/', trim(auth()->user()->full_name ?? '')))
            ->filter()->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->take(2)->implode('') ?: '?';
    @endphp
    <script>
        window.ADMIN_LABS_BY_TYPE = @json($__adminLabsByType);
        window.ADMIN_RECENT_PENDING = @json($adminRecentPending->map(fn ($b) => ['ref' => $b->ref, 'submitted_at' => $b->submitted_at]));
        window.ADMIN_TIME_BLOCKS_URL = @json(route('admin.time-blocks.index'));
        window.ADMIN_BOOKING_DETAILS_URL = @json(route('admin.bookings.details', ['booking' => '__REF__']));
        window.ADMIN_BLOCK_DETAILS_URL = @json(route('admin.time-blocks.details', ['timeBlock' => '__ID__']));
    </script>
    <script src="{{ asset('js/admin.js') }}?v={{ filemtime(public_path('js/admin.js')) }}"></script>
    <script src="{{ asset('js/sweetalert2.min.js') }}?v={{ filemtime(public_path('js/sweetalert2.min.js')) }}"></script>
    <script src="{{ asset('js/notify.js') }}?v={{ filemtime(public_path('js/notify.js')) }}"></script>
    @if (session('status'))
        <script>document.addEventListener('DOMContentLoaded', function () { notifySuccess(@json(session('status'))); });</script>
    @endif
    <div class="shell" id="admShell">
        <div class="sidebar-backdrop" id="admSidebarBackdrop"></div>
        <aside class="sidebar" id="admSidebar">
            <div class="sidebar-head">
                <a href="{{ route('admin.dashboard') }}" class="sidebar-logo-link">
                    <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name', 'Lab Booking') }}" class="sidebar-logo-full">
                    <img src="{{ asset('images/logo-square.png') }}" alt="{{ config('app.name', 'Lab Booking') }}" class="sidebar-logo-compact">
                </a>
                <button type="button" class="sidebar-collapse-btn" id="admCollapseBtn" title="Collapse sidebar" aria-label="Collapse sidebar">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 6l-6 6 6 6"/></svg>
                </button>
            </div>

            <nav class="sidebar-nav">
            @if (auth()->user()->isSuperAdmin())
                {{-- Super Admin gets a curated "control tower" nav — day-to-day
                     booking operations (Calendar, All Bookings, Schedule & Block,
                     History) stay with lab staff; this role is strategic oversight
                     + configuration, per the Super Admin Panel spec. --}}
                <div class="group-label">Overview</div>
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" title="Dashboard">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="8" height="8" rx="2"/><rect x="13" y="3" width="8" height="5" rx="2"/><rect x="13" y="12" width="8" height="9" rx="2"/><rect x="3" y="15" width="8" height="6" rx="2"/></svg>
                    <span class="lbl">Dashboard</span>
                </a>

                <div class="group-label">Management</div>
                <a href="{{ route('admin.labs.index') }}" class="{{ request()->routeIs('admin.labs.index') ? 'active' : '' }}" title="Manage Labs">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21V9l9-6 9 6v12"/><path d="M9 21v-6h6v6"/></svg>
                    <span class="lbl">Manage Labs</span>
                </a>
                <a href="{{ route('admin.staff.index') }}" class="{{ request()->routeIs('admin.staff.index') ? 'active' : '' }}" title="Manage Users">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.2"/><path d="M3.5 20c.8-3.6 3.2-5.5 5.5-5.5s4.7 1.9 5.5 5.5"/><circle cx="17.5" cy="8.5" r="2.4"/><path d="M15.5 14.3c1.8.3 3.6 1.9 4.2 4.7"/></svg>
                    <span class="lbl">Manage Users</span>
                </a>

                <div class="group-label">Insights</div>
                <a href="{{ route('admin.report') }}" class="{{ request()->routeIs('admin.report') ? 'active' : '' }}" title="System Reports">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20V10M11 20V4M18 20v-7"/></svg>
                    <span class="lbl">System Reports</span>
                </a>
                <a href="{{ route('admin.activity-log') }}" class="{{ request()->routeIs('admin.activity-log') ? 'active' : '' }}" title="Activity Log">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M8 4h9a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/><path d="M6 8H4M6 12H4M6 16H4"/><path d="M10 9h5M10 13h5M10 17h3"/></svg>
                    <span class="lbl">Activity Log</span>
                </a>

                <div class="group-label">System</div>
                <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" title="Settings">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    <span class="lbl">Settings</span>
                </a>
            @else
                <div class="group-label">Overview</div>
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" title="Dashboard">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="8" height="8" rx="2"/><rect x="13" y="3" width="8" height="5" rx="2"/><rect x="13" y="12" width="8" height="9" rx="2"/><rect x="3" y="15" width="8" height="6" rx="2"/></svg>
                    <span class="lbl">Dashboard</span>
                </a>

                <div class="group-label">Bookings</div>
                <a href="{{ route('admin.bookings.index') }}" class="{{ request()->routeIs('admin.bookings.index') ? 'active' : '' }}" title="All Bookings">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 3h6a1 1 0 0 1 1 1v1H8V4a1 1 0 0 1 1-1z"/><rect x="5" y="5" width="14" height="16" rx="2"/><path d="M9 12h6M9 16h6"/></svg>
                    <span class="lbl">All Bookings</span>
                </a>
                <a href="{{ route('admin.bookings.research') }}" class="{{ request()->routeIs('admin.bookings.research') ? 'active' : '' }}" title="Research Labs">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10 3h4M10 3v6l-5.5 9.5A1.8 1.8 0 0 0 6.1 21h11.8a1.8 1.8 0 0 0 1.6-2.7L14 9V3"/></svg>
                    <span class="lbl">Research Labs</span>
                    @if (($__pendingByType['research'] ?? 0) > 0)<span class="badge-count">{{ $__pendingByType['research'] }}</span>@endif
                </a>
                <a href="{{ route('admin.bookings.csl') }}" class="{{ request()->routeIs('admin.bookings.csl') ? 'active' : '' }}" title="CSL Labs">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 2h6M10 2v6.5L4.8 18a1.6 1.6 0 0 0 1.4 2.4h11.6A1.6 1.6 0 0 0 19.2 18L14 8.5V2"/></svg>
                    <span class="lbl">CSL Labs</span>
                    @if (($__pendingByType['csl'] ?? 0) > 0)<span class="badge-count">{{ $__pendingByType['csl'] }}</span>@endif
                </a>
                <a href="{{ route('admin.bookings.pharma') }}" class="{{ request()->routeIs('admin.bookings.pharma') ? 'active' : '' }}" title="Pharma Labs">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2.5" y="8.5" width="19" height="7" rx="3.5" transform="rotate(-45 12 12)"/></svg>
                    <span class="lbl">Pharma Labs</span>
                    @if (($__pendingByType['pharma'] ?? 0) > 0)<span class="badge-count">{{ $__pendingByType['pharma'] }}</span>@endif
                </a>

                <div class="group-label">Management</div>
                <a href="{{ route('admin.time-blocks.index') }}" class="{{ request()->routeIs('admin.time-blocks.index') ? 'active' : '' }}" title="Schedule &amp; Block">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18"/><path d="M8.5 15.5l3 3 5-5.5"/></svg>
                    <span class="lbl">Schedule &amp; Block</span>
                </a>
                <a href="{{ route('admin.labs.index') }}" class="{{ request()->routeIs('admin.labs.index') ? 'active' : '' }}" title="Manage Labs">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21V9l9-6 9 6v12"/><path d="M9 21v-6h6v6"/></svg>
                    <span class="lbl">Manage Labs</span>
                </a>
                {{-- Admin and above only; plain lab staff don't see these. --}}
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.staff.index') }}" class="{{ request()->routeIs('admin.staff.index') ? 'active' : '' }}" title="Manage Staff">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.2"/><path d="M3.5 20c.8-3.6 3.2-5.5 5.5-5.5s4.7 1.9 5.5 5.5"/><circle cx="17.5" cy="8.5" r="2.4"/><path d="M15.5 14.3c1.8.3 3.6 1.9 4.2 4.7"/></svg>
                        <span class="lbl">Manage Staff</span>
                    </a>
                    <a href="{{ route('admin.report') }}" class="{{ request()->routeIs('admin.report') ? 'active' : '' }}" title="System Report">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20V10M11 20V4M18 20v-7"/></svg>
                        <span class="lbl">System Report</span>
                    </a>
                @endif

                <div class="group-label">Activity</div>
                <a href="{{ route('admin.history') }}" class="{{ request()->routeIs('admin.history') ? 'active' : '' }}" title="History">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="13" r="8"/><path d="M12 9v4l2.5 2.5"/><path d="M9 2h6"/></svg>
                    <span class="lbl">History</span>
                </a>
            @endif
            </nav>

            <div class="sidebar-foot">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="sidebar-logout-btn" title="Log out">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
                        <span class="lbl">Log out</span>
                    </button>
                </form>
            </div>
        </aside>

        <main class="main">
            <header class="topbar">
                <button type="button" class="menu-btn" id="admMenuBtn" aria-label="Open menu" aria-controls="admSidebar" aria-expanded="false">
                    <svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
                </button>

                <div class="breadcrumb">
                    <span class="crumb-root">Admin</span>
                    <span class="crumb-sep">/</span>
                    <h1>{{ $pageTitle ?? 'Admin' }}</h1>
                </div>

                <div class="topbar-actions">
                    <div class="dropdown" id="admNotifDropdown">
                        <button type="button" class="icon-btn" id="admNotifBtn" aria-label="Notifications">
                            <svg viewBox="0 0 24 24" width="21" height="21" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 5 2 6 2 6H4s2-1 2-6"/><path d="M10 20a2 2 0 0 0 4 0"/></svg>
                            @if ($__pendingTotal > 0)<span class="dot">{{ $__pendingTotal > 99 ? '99+' : $__pendingTotal }}</span>@endif
                        </button>
                        <div class="dropdown-panel dropdown-panel--wide">
                            <div class="dropdown-panel-head notif-panel-head">
                                <span>New bookings pending review</span>
                                @if ($adminRecentPending->isNotEmpty())
                                    <button type="button" id="admNotifMarkRead" class="notif-mark-read">Mark all read</button>
                                @endif
                            </div>
                            @if ($adminRecentPending->isNotEmpty())
                                <div id="admNotifItems">
                                    <div class="notif-list">
                                        @foreach ($adminRecentPending as $b)
                                            <a class="notif-booking" data-ref="{{ $b->ref }}" href="{{ route('admin.bookings.' . $b->lab_type) }}">
                                                <span class="type-dot type-dot--{{ $b->lab_type }}"></span>
                                                <span class="notif-booking-body">
                                                    <span class="notif-booking-name">{{ $b->applicant_name }}</span>
                                                    <span class="notif-booking-meta">{{ $b->lab_type === 'csl' ? 'CSL' : ucfirst($b->lab_type) }} lab · {{ $b->ref }}</span>
                                                </span>
                                                <span class="notif-booking-time">{{ $b->submitted_at?->diffForHumans() }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                    <a class="notif-footer" href="{{ route('admin.bookings.index') }}">View all {{ $__pendingTotal }} pending →</a>
                                </div>
                                <div class="notif-empty" id="admNotifCaughtUp" style="display:none;">
                                    <strong>No notifications</strong>
                                    <span>You're all caught up!</span>
                                </div>
                            @else
                                <div class="notif-empty">No pending approvals 🎉</div>
                            @endif
                        </div>
                    </div>

                    <div class="dropdown" id="admProfileDropdown">
                        <button type="button" class="profile-btn" id="admProfileBtn">
                            <span class="profile-avatar">{{ $__initials }}</span>
                            <span class="profile-btn-name">{{ auth()->user()->full_name }}</span>
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                        </button>
                        <div class="dropdown-panel">
                            <div class="profile-panel-head">
                                <span class="profile-avatar">{{ $__initials }}</span>
                                <div>
                                    <strong>{{ auth()->user()->full_name }}</strong>
                                    <span>{{ auth()->user()->email }}</span>
                                    <span>{{ auth()->user()->role?->label ?? 'Staff' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <div class="main-content">
                {{-- Flash messages surface as toasts (see public/js/notify.js).
                     Validation errors stay on screen as a block as well, since
                     they usually list several things to fix and a toast that
                     disappears would take the detail with it. --}}
                @if ($errors->any())
                    <div class="card" style="padding:14px 16px; margin-bottom:18px; color:#a03027;">
                        <ul style="margin:0; padding-left:20px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

</body>
</html>
