@extends('layouts.admin')

@section('content')
    <style>
        .dash-section { margin-top: 20px; }
        .dash-section:first-child { margin-top: 0; }
        .dash-section .section-title { margin-top: 0; }

        .dash-hero { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; padding: 26px 28px; background: var(--grad); border: none; box-shadow: 0 20px 40px -20px rgba(74, 95, 98, .55); position: relative; overflow: hidden; }
        .dash-hero::before { content: ''; position: absolute; inset: 0; background: radial-gradient(420px 220px at 88% -20%, rgba(255,255,255,.16), transparent 70%); pointer-events: none; }
        .dash-hero h2, .dash-hero .muted { color: #fff; position: relative; }
        .dash-hero h2 { margin: 0 0 4px; font-size: 1.4rem; }
        .dash-hero .muted { font-size: 0.9rem; opacity: .82; }
        .dash-hero-cta { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; position: relative; }
        .dash-hero .button-secondary { background: rgba(255,255,255,.14); color: #fff; border-color: rgba(255,255,255,.3); backdrop-filter: blur(4px); }
        .dash-hero .button-secondary:hover { border-color: #fff; color: #fff; background: rgba(255,255,255,.22); }
        .dash-hero .button-primary { background: #fff; color: var(--brand-2); box-shadow: 0 10px 22px -12px rgba(0,0,0,.35); }
        .dash-hero .button-primary:hover { background: #fff; }

        /* Six cards: auto-fit keeps them on one row on a wide screen and lets
           them wrap on their own as it narrows, rather than snapping at steps. */
        .kpi-grid.dash-section { grid-template-columns: repeat(auto-fit, minmax(162px, 1fr)); }
        @media (max-width: 700px) { .kpi-grid.dash-section { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        .kpi-grid.dash-section .kpi { background: linear-gradient(160deg, rgba(125,145,148,.13), var(--panel-strong) 65%); }
        .kpi-grid.dash-section .kpi--warn { background: linear-gradient(160deg, rgba(214,158,46,.16), var(--panel-strong) 65%); }
        .kpi-grid.dash-section .kpi--ok { background: linear-gradient(160deg, rgba(47,138,82,.14), var(--panel-strong) 65%); }
        .kpi-grid.dash-section .kpi--bad { background: linear-gradient(160deg, rgba(192,57,43,.14), var(--panel-strong) 65%); }
        /* Blocks carry the same earthy accent they use everywhere else. */
        .kpi-grid.dash-section .kpi--block { background: linear-gradient(160deg, rgba(176,131,95,.18), var(--panel-strong) 65%); }
        .kpi-grid.dash-section .kpi--block::before { background: var(--type-block); }
        /* Cancelled sits apart from rejected — neutral grey, not red. */
        .kpi-grid.dash-section .kpi--cancel { background: linear-gradient(160deg, rgba(138,151,160,.17), var(--panel-strong) 65%); }
        .kpi-grid.dash-section .kpi--cancel::before { background: #8a97a0; }
        .kpi-grid.dash-section .kpi::before { width: 100%; height: 4px; top: 0; left: 0; bottom: auto; border-radius: var(--radius) var(--radius) 0 0; }
        .kpi-grid.dash-section .kpi-top { min-height: 19px; }
        .kpi-grid.dash-section .kpi strong { font-size: 2.15rem; }

        .dash-split { display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr); gap: 16px; align-items: stretch; }
        .dash-split > .card { display: flex; flex-direction: column; }
        .panel-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 14px 18px; border-bottom: 1px solid var(--line); }
        .panel-head h3 { margin: 0; font-size: 1rem; display: flex; align-items: center; gap: 8px; }
        .panel-head .link { font-size: 0.8rem; font-weight: 700; color: var(--brand-2); text-decoration: none; }
        /* These sit alone in a panel header rather than inside a sentence, so
           unlike an inline text link they can take padding to reach a
           thumb-friendly height without disturbing anything around them. */
        @media (max-width: 900px) {
            .panel-head .link { display: inline-block; padding: 8px 0; }
        }
        .panel-scroll { flex: 1; max-height: 440px; overflow-y: auto; }
        .panel-scroll:has(.dash-empty) { display: flex; flex-direction: column; justify-content: center; }
        /* Default OS scrollbars (esp. macOS overlay) stay invisible until hovered,
           so a scrollable panel gives no hint it has more content — make the
           thumb permanently visible instead of hover-only. */
        .panel-scroll { scrollbar-width: thin; scrollbar-color: var(--brand-tint) transparent; }
        .panel-scroll::-webkit-scrollbar { width: 7px; }
        .panel-scroll::-webkit-scrollbar-track { background: transparent; }
        .panel-scroll::-webkit-scrollbar-thumb { background: var(--brand-tint); border-radius: 8px; }
        .panel-scroll::-webkit-scrollbar-thumb:hover { background: var(--brand); }
        /* Today's Schedule: cap to ~5 rows (measured .tl-item height incl. border ≈ 66.6px), scroll for the rest. */
        .dash-popin { position: relative; }
        .dash-popin .panel-scroll { max-height: 335px; }
        /* Scrollbar theming above is a nice-to-have but native scrollbar visibility
           is inconsistent across OS/browsers (e.g. hidden until hover on macOS) — this
           fade + label is the reliable "there's more below" cue, shown whenever the
           list is actually capped (i.e. more than fits in the 5-row window above). */
        .tl-scroll-hint {
            position: absolute; left: 0; right: 0; bottom: 0;
            padding: 8px 18px 7px; text-align: center; pointer-events: none;
            font-size: 0.68rem; font-weight: 700; color: var(--brand-2);
            background: linear-gradient(180deg, rgba(255,255,255,0), var(--panel-strong) 60%);
            border-radius: 0 0 var(--radius) var(--radius);
        }
        .dash-table-wrap { flex: 1; overflow: auto; }

        .tl-item { display: flex; gap: 12px; padding: 12px 18px; border-bottom: 1px solid var(--line); }
        .tl-item:last-child { border-bottom: none; }
        .tl-time { flex-shrink: 0; width: 46px; font-size: 0.72rem; font-weight: 800; color: var(--brand-2); line-height: 1.3; font-variant-numeric: tabular-nums; font-family: var(--mono); }
        .tl-body { min-width: 0; flex: 1; }
        .tl-body strong { font-size: 0.86rem; }
        .tl-sub { font-size: 0.75rem; color: var(--muted); margin-top: 2px; }

        .mini-item { display: flex; align-items: center; gap: 10px; padding: 12px 18px; border-bottom: 1px solid var(--line); }
        .mini-item:last-child { border-bottom: none; }
        .mini-item .mi-body { min-width: 0; flex: 1; }
        .mini-item .mi-body strong { font-size: 0.84rem; }
        .mini-item .mi-sub { font-size: 0.74rem; color: var(--muted); }

        .dash-empty { padding: 28px 18px; text-align: center; color: var(--muted); font-size: 0.86rem; }
        .dash-empty .big { font-size: 1.6rem; display: block; margin-bottom: 6px; }

        .dash-table th, .dash-table td { padding: 12px 18px; }

        .dash-popin { animation: dashPopIn .5s cubic-bezier(.34, 1.56, .64, 1) both; }
        @keyframes dashPopIn { from { opacity: 0; transform: scale(.94) translateY(10px); } to { opacity: 1; transform: none; } }

        .dash-attention { animation: dashAttention 1s ease-in-out 6; }
        @keyframes dashAttention {
            0%, 100% { box-shadow: 0 0 0 0 rgba(125,145,148,0); transform: scale(1); }
            50% { box-shadow: 0 0 0 8px rgba(125,145,148,.28); transform: scale(1.012); }
        }

        @media (max-width: 1000px) {
            .dash-split { grid-template-columns: minmax(0, 1fr); }
        }
    </style>

    @php
        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Hello' : ($hour < 18 ? 'Good afternoon' : 'Good evening');

        // Merge today's bookings and blocks into one time-ordered timeline.
        $todayTimeline = collect();
        foreach ($todayBookings as $b) {
            $todayTimeline->push([
                'start' => $b->start_time->format('H:i'),
                'end' => $b->end_time->format('H:i'),
                'title' => $b->applicant_name,
                'sub' => $b->rooms->map(fn ($r) => $r->lab?->name)->filter()->implode(', ') ?: ucfirst($b->lab_type).' lab',
                'type' => $b->lab_type,
                'status' => $b->status,
                'kind' => 'booking',
            ]);
        }
        foreach ($todayBlocks as $blk) {
            $todayTimeline->push([
                'start' => $blk['start'] ?? '',
                'end' => $blk['end'] ?? '',
                'title' => $blk['title'] ?? 'Blocked',
                'sub' => $blk['rooms'] ?: 'Blocked slot',
                'type' => 'block',
                'status' => 'blocked',
                'kind' => 'block',
            ]);
        }
        $todayTimeline = $todayTimeline->sortBy('start')->values();
    @endphp

    <div class="panel dash-hero dash-section">
        <div>
            <h2>{{ $greeting }}, {{ auth()->user()->full_name }} 👋</h2>
            <div class="muted">{{ $today->format('l, d/m/Y') }} · {{ $stats['pending'] }} booking{{ $stats['pending'] === 1 ? '' : 's' }} awaiting your decision</div>
        </div>
        <div class="dash-hero-cta">
            <a class="button button-secondary" href="{{ route('admin.calendar') }}">📅 Calendar</a>
            <a class="button button-primary" href="{{ route('admin.time-blocks.index') }}">＋ Schedule / Block</a>
        </div>
    </div>

    <section class="kpi-grid dash-section">
        <div class="kpi kpi--warn">
            <div class="kpi-top"><span class="kpi-cta">Needs action</span></div>
            <strong>{{ number_format($stats['pending']) }}</strong>
            <span class="kpi-label">Pending</span>
            @include('admin.partials.kpi-split', ['counts' => $breakdown['pending']])
        </div>
        <div class="kpi kpi--block">
            <div class="kpi-top"><span class="kpi-chip">Scheduled</span></div>
            <strong>{{ number_format($stats['blocked']) }}</strong>
            <span class="kpi-label">Blocks</span>
            @include('admin.partials.kpi-split', ['counts' => $breakdown['blocked']])
        </div>
        <div class="kpi kpi--ok">
            <div class="kpi-top"><span class="kpi-chip">{{ $stats['total'] ? round($stats['approved'] / $stats['total'] * 100) : 0 }}%</span></div>
            <strong>{{ number_format($stats['approved']) }}</strong>
            <span class="kpi-label">Approved</span>
            @include('admin.partials.kpi-split', ['counts' => $breakdown['approved']])
        </div>
        <div class="kpi kpi--bad">
            {{-- Empty top row: keeps this card's figure on the same baseline as
                 the chipped cards beside it. --}}
            <div class="kpi-top"></div>
            <strong>{{ number_format($stats['rejected']) }}</strong>
            <span class="kpi-label">Rejected</span>
            @include('admin.partials.kpi-split', ['counts' => $breakdown['rejected']])
        </div>
        <div class="kpi kpi--cancel">
            <div class="kpi-top"></div>
            <strong>{{ number_format($stats['cancelled']) }}</strong>
            <span class="kpi-label">Cancelled</span>
            @include('admin.partials.kpi-split', ['counts' => $breakdown['cancelled']])
        </div>
        <div class="kpi">
            <div class="kpi-top"><span class="kpi-chip">All time</span></div>
            <strong>{{ number_format($stats['total']) }}</strong>
            <span class="kpi-label">Total bookings</span>
            @include('admin.partials.kpi-split', ['counts' => $breakdown['total']])
        </div>
    </section>

    <div class="card dash-section dash-popin">
        <div class="panel-head">
            <h3>📌 Today's Schedule</h3>
            <span class="muted" style="font-size:.78rem;">{{ $todayTimeline->count() }} item{{ $todayTimeline->count() === 1 ? '' : 's' }}</span>
        </div>
        <div class="panel-scroll">
            @forelse ($todayTimeline as $item)
                <div class="tl-item">
                    <div class="tl-time">{{ $item['start'] }}<br>{{ $item['end'] }}</div>
                    <div class="tl-body">
                        <strong>{{ $item['title'] }}</strong>
                        @if ($item['kind'] === 'block')
                            <span class="badge" style="font-size:.64rem;">Blocked</span>
                        @else
                            <span class="badge badge-{{ $item['status'] }}" style="font-size:.64rem;">{{ ucfirst($item['status']) }}</span>
                        @endif
                        <div class="tl-sub">{{ $item['sub'] }}</div>
                    </div>
                </div>
            @empty
                <div class="dash-empty"><span class="big">🌤️</span>Nothing scheduled for today.</div>
            @endforelse
        </div>
        @if ($todayTimeline->count() > 5)
            <div class="tl-scroll-hint" aria-hidden="true">↓ Scroll for more</div>
        @endif
    </div>

    <div class="dash-section">
        <div class="section-title">
            <h2>Booking Calendar</h2>
            <a class="button button-secondary" href="{{ route('admin.calendar') }}">Open full view →</a>
        </div>
        <div>
            @include('admin.partials.calendar-widget', ['calPrefix' => 'dc', 'calendarEvents' => $calendarEvents])
        </div>
    </div>

    <div class="dash-split dash-section">
        <div class="card">
            <div class="panel-head">
                <h3>🕑 Recent Bookings</h3>
                <a class="link" href="{{ route('admin.bookings.index') }}">All →</a>
            </div>
            <div class="panel-scroll">
                @forelse ($recentBookings as $b)
                    <div class="mini-item">
                        <span class="type-dot type-dot--{{ $b->lab_type }}"></span>
                        <div class="mi-body">
                            <strong>{{ $b->applicant_name }}</strong>
                            <div class="mi-sub">{{ $b->date_range_label }} · {{ ucfirst($b->lab_type) }}</div>
                        </div>
                        <span class="badge badge-{{ $b->status }}" style="font-size:.66rem;">{{ ucfirst($b->status) }}</span>
                    </div>
                @empty
                    <div class="dash-empty">No bookings yet.</div>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="panel-head">
                <h3>🗓 Upcoming Blocks &amp; Classes</h3>
                <a class="link" href="{{ route('admin.time-blocks.index') }}">Manage →</a>
            </div>
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr><th>Title</th><th>Purpose</th><th>Date</th><th>Time</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($upcomingBlocks as $block)
                            <tr>
                                <td><strong style="font-size:.85rem;">{{ $block->title }}</strong><br><span class="muted" style="font-size:.72rem;">{{ ucfirst($block->lab_type) }}</span></td>
                                <td><span class="badge">{{ ucfirst($block->purpose) }}</span></td>
                                <td>{{ $block->block_date?->format('d/m/Y') ?? $block->block_date }}</td>
                                <td>{{ \Illuminate\Support\Str::of($block->start_time)->substr(0, 5) }} – {{ \Illuminate\Support\Str::of($block->end_time)->substr(0, 5) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="empty">No upcoming blocked slots.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Additive only: right after login, once the pop-in entrance finishes,
        // give "Today's Schedule" a handful of attention beats (6, then stop).
        setTimeout(function () {
            var card = document.querySelector('.dash-popin');
            if (!card) return;
            card.classList.add('dash-attention');
        }, 500);
    </script>
@endsection
