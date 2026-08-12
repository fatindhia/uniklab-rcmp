@extends('layouts.admin')

@section('content')
    <style>
        /* ---------- Hero ---------- */
        .rep-hero { display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap; padding: 24px 28px; background: var(--grad); border: none; box-shadow: 0 20px 40px -22px rgba(74, 95, 98, .55); position: relative; overflow: hidden; }
        .rep-hero::before { content: ''; position: absolute; inset: 0; background: radial-gradient(440px 240px at 86% -30%, rgba(255,255,255,.18), transparent 70%); pointer-events: none; }
        .rep-hero > * { position: relative; }
        .rep-eyebrow { display: inline-flex; align-items: center; gap: 7px; font-size: .68rem; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; color: rgba(255,255,255,.72); }
        .rep-hero h2 { margin: 6px 0 5px; font-size: 1.42rem; color: #fff; }
        .rep-hero .rep-hero-sub { font-size: .86rem; color: rgba(255,255,255,.8); }
        .rep-hero-side { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
        .rep-hero-stat { padding: 10px 16px; border-radius: var(--radius-sm); background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.24); backdrop-filter: blur(4px); text-align: right; }
        .rep-hero-stat strong { display: block; font-family: 'Sora', sans-serif; font-size: 1.05rem; line-height: 1.2; color: #fff; white-space: nowrap; }
        .rep-hero-stat span { font-size: .68rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: rgba(255,255,255,.75); }
        .rep-hero .button-secondary { background: rgba(255,255,255,.14); color: #fff; border-color: rgba(255,255,255,.3); backdrop-filter: blur(4px); }
        .rep-hero .button-secondary:hover { background: rgba(255,255,255,.24); border-color: #fff; color: #fff; }

        /* ---------- KPI row ---------- */
        /* Seven cards: auto-fit lets them sit on one row on a wide screen and
           wrap on their own as it narrows, instead of snapping at fixed steps. */
        .rep-kpis { grid-template-columns: repeat(auto-fit, minmax(148px, 1fr)); margin-top: 16px; }
        @media (max-width: 700px) { .rep-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        .rep-kpi { background: linear-gradient(160deg, rgba(125,145,148,.13), var(--panel-strong) 62%); padding: 16px 16px 14px; }
        .rep-kpi::before { width: 100%; height: 4px; top: 0; left: 0; bottom: auto; border-radius: var(--radius) var(--radius) 0 0; }
        .rep-kpi.kpi--ok { background: linear-gradient(160deg, rgba(47,138,82,.15), var(--panel-strong) 62%); }
        .rep-kpi.kpi--warn { background: linear-gradient(160deg, rgba(214,158,46,.17), var(--panel-strong) 62%); }
        .rep-kpi.kpi--bad { background: linear-gradient(160deg, rgba(192,57,43,.15), var(--panel-strong) 62%); }
        /* min-height keeps every card's figure on one baseline whether or not
           it carries a chip; nowrap stops "Needs action" wrapping to two lines
           in a narrow card and pushing its figure down. */
        .rep-kpi-top { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 10px; min-height: 30px; }
        .rep-kpi-top .kpi-cta, .rep-kpi-top .kpi-chip { white-space: nowrap; font-size: .6rem; }
        .rep-kpi-icon { width: 30px; height: 30px; border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: .9rem; background: rgba(125,145,148,.16); }
        .kpi--ok .rep-kpi-icon { background: rgba(47,138,82,.18); }
        .kpi--warn .rep-kpi-icon { background: rgba(214,158,46,.2); }
        .kpi--bad .rep-kpi-icon { background: rgba(192,57,43,.18); }
        .rep-kpi strong { font-size: 1.85rem; }
        .rep-kpi .kpi-label { margin-top: 3px; font-size: .76rem; }
        .rep-kpi.rep-kpi--cancel { background: linear-gradient(160deg, rgba(138,151,160,.16), var(--panel-strong) 62%); }
        .rep-kpi.rep-kpi--cancel::before { background: #8a97a0; }
        .rep-kpi.rep-kpi--block { background: linear-gradient(160deg, rgba(176,131,95,.17), var(--panel-strong) 62%); }
        .rep-kpi.rep-kpi--block::before { background: var(--type-block); }
        .rep-kpi--cancel .rep-kpi-icon { background: rgba(138,151,160,.22); }
        .rep-kpi--block .rep-kpi-icon { background: rgba(176,131,95,.22); }
        /* The per-lab-type split itself lives in admin.css — the dashboard uses
           it too. Only its fit inside a report card is tuned here. */

        /* ---------- Card shell ---------- */
        .chart-card { padding: 20px 22px; }
        .chart-card h3 { margin: 0 0 4px; font-size: .96rem; display: flex; align-items: center; gap: 8px; }
        .chart-card .sub { font-size: .78rem; color: var(--muted); margin-bottom: 18px; }
        .rep-split { display: grid; gap: 16px; margin-top: 16px; align-items: stretch; }
        .rep-split--gauge { grid-template-columns: minmax(0,1fr) minmax(0,1.55fr); }
        .rep-split--usage { grid-template-columns: minmax(0,1fr) minmax(0,1fr); }
        @media (max-width: 1000px) { .rep-split--gauge, .rep-split--usage { grid-template-columns: 1fr; } }

        /* ---------- Response health ---------- */
        .gauge-wrap { display: flex; align-items: center; gap: 22px; flex-wrap: wrap; }
        .gauge-ring { position: relative; flex-shrink: 0; }
        .gauge-ring svg { display: block; transform: rotate(-90deg); }
        .gauge-ring .ring-value { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .ring-value b { font-family: 'Sora', sans-serif; font-size: 1.7rem; line-height: 1; color: var(--ink); }
        .ring-value span { max-width: 82px; text-align: center; font-size: .58rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: var(--muted); margin-top: 4px; line-height: 1.3; }
        .gauge-ring circle.arc { stroke-dasharray: var(--dash) var(--gap); animation: ringDraw 1.1s cubic-bezier(.4,0,.2,1) both; }
        @keyframes ringDraw { from { stroke-dasharray: 0 999; } }
        .rh-list { display: grid; gap: 9px; flex: 1; min-width: 190px; }
        .rh-row { display: flex; align-items: center; gap: 10px; padding: 8px 11px; border: 1px solid var(--line); border-radius: var(--radius-sm); background: #fff; }
        .rh-dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }
        .rh-row .rh-label { font-size: .78rem; font-weight: 600; color: var(--muted); }
        .rh-row .rh-num { margin-left: auto; font-family: var(--mono); font-size: .84rem; font-weight: 800; color: var(--ink); }
        .rh-note { margin-top: 12px; padding: 9px 12px; border-left: 3px solid var(--accent); border-radius: 0 var(--radius-sm) var(--radius-sm) 0; background: rgba(148,128,111,.09); font-size: .74rem; color: var(--muted); }
        .rh-note strong { color: var(--ink); }

        /* ---------- Monthly bars ---------- */
        .chart-plot { position: relative; padding-top: 6px; }
        .chart-grid { position: absolute; inset: 6px 0 30px; display: flex; flex-direction: column; justify-content: space-between; pointer-events: none; }
        .chart-grid i { display: block; height: 1px; background: var(--line); opacity: .7; }
        .chart-bars { display: flex; align-items: flex-end; gap: 10px; padding: 0 2px; overflow-x: auto; min-height: 178px; position: relative; }
        .chart-bar-col { display: flex; flex-direction: column; align-items: center; gap: 7px; flex: 1; min-width: 34px; }
        /* The stack is a fixed-height well the bar sits at the bottom of; the
           total rides directly on top of its own bar rather than floating at
           the top of the column, so short months stay readable. */
        .chart-bar-stack { display: flex; align-items: flex-end; justify-content: center; width: 100%; height: 148px; }
        .chart-bar-plot { display: flex; flex-direction: column; align-items: center; gap: 5px; width: 100%; }
        .chart-bar { width: 100%; max-width: 30px; display: flex; flex-direction: column-reverse; border-radius: 6px 6px 3px 3px; overflow: hidden; background: rgba(125,145,148,.12); position: relative; transform-origin: bottom; animation: barGrow .6s cubic-bezier(.34,1.2,.64,1) both; }
        @keyframes barGrow { from { transform: scaleY(0); opacity: .4; } }
        .chart-bar-col:hover .chart-bar { filter: brightness(1.06); }
        .chart-bar-total { font-size: .66rem; font-weight: 800; font-family: var(--mono); color: var(--brand-2); }
        .chart-bar-label { font-size: .64rem; color: var(--muted); font-weight: 700; white-space: nowrap; }
        .chart-bar-col.is-peak .chart-bar-label { color: var(--brand-2); }
        .chart-bar-col.is-peak .chart-bar-label::after { content: ' ▲'; font-size: .55rem; }
        .chart-legend { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 14px; padding-top: 12px; border-top: 1px solid var(--line); font-size: .74rem; color: var(--muted); font-weight: 600; }
        .chart-legend span { display: inline-flex; align-items: center; gap: 6px; }
        .chart-legend .sw { width: 9px; height: 9px; border-radius: 3px; }

        /* ---------- Top labs ---------- */
        .hbar-list { display: grid; gap: 11px; }
        .hbar-row { display: grid; grid-template-columns: 24px minmax(0,1fr) 58px; align-items: center; gap: 11px; }
        .hbar-rank { width: 24px; height: 24px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: .68rem; font-weight: 800; font-family: var(--mono); background: rgba(125,145,148,.14); color: var(--brand-2); }
        .hbar-row:first-child .hbar-rank { background: rgba(148,128,111,.24); color: #6d5a46; }
        .hbar-main { min-width: 0; }
        .hbar-label { font-size: .8rem; font-weight: 700; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-bottom: 5px; }
        .hbar-track { height: 9px; border-radius: 999px; background: rgba(125,145,148,.14); overflow: hidden; }
        .hbar-fill { height: 100%; border-radius: 999px; background: var(--grad); animation: hbarGrow .8s cubic-bezier(.4,0,.2,1) both; }
        /* Only a from-frame: the 100% state stays whatever the inline width is,
           and an animation still outranks that inline declaration while running. */
        @keyframes hbarGrow { from { width: 0; } }
        .hbar-row:first-child .hbar-fill { background: linear-gradient(90deg, var(--brand), var(--accent)); }
        .hbar-value { font-size: .8rem; font-weight: 800; text-align: right; font-family: var(--mono); }
        .hbar-value small { display: block; font-size: .6rem; font-weight: 700; color: var(--muted); }

        /* ---------- Activity timeline ---------- */
        .ract-list { padding: 6px 0; }
        .ract-item { position: relative; padding: 11px 18px 11px 46px; }
        .ract-item::before { content: ''; position: absolute; left: 25px; top: 0; bottom: 0; width: 2px; background: var(--line); }
        .ract-item:first-child::before { top: 20px; }
        .ract-item:last-child::before { bottom: calc(100% - 20px); }
        .ract-mark { position: absolute; left: 18px; top: 13px; width: 16px; height: 16px; border-radius: 50%; background: #fff; border: 2px solid var(--brand); display: flex; align-items: center; justify-content: center; font-size: .5rem; }
        .ract-mark--approved { border-color: #2f8a52; }
        .ract-mark--rejected { border-color: #c0392b; }
        .ract-mark--cancelled { border-color: var(--muted); }
        .ract-mark--block { border-color: var(--type-block); }
        .ract-item strong { font-size: .84rem; display: block; }
        .ract-item .muted { font-size: .73rem; margin-top: 2px; }
        .ract-late { display: inline-block; margin-left: 6px; padding: 1px 7px; border-radius: 999px; font-size: .6rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; background: rgba(192,57,43,.12); color: #b03a2c; }

        @media (prefers-reduced-motion: reduce) {
            .gauge-ring circle.arc, .chart-bar, .hbar-fill { animation: none; }
        }

        @media print {
            .rep-hero { box-shadow: none; padding: 18px 20px; }
            .rep-hero-stat { border-color: rgba(255,255,255,.5); }
            .panel-scroll { max-height: none !important; overflow: visible !important; }
            .section-title { break-after: avoid; }
            .rep-split, .rep-split--gauge, .rep-split--usage, .chart-card, .ract-item, .rh-row { break-inside: avoid; }
            /* Nothing on a printed page can be scrolled to reveal clipped content,
               so any horizontal-scroll container must wrap/reflow instead. */
            .chart-bars { overflow-x: visible; flex-wrap: wrap; }
            .gauge-ring circle.arc, .chart-bar, .hbar-fill { animation: none; }
        }
    </style>

    @php
        $health = $responseHealth;
        $avgLabel = $health['avg_hours'] === null
            ? '—'
            : ($health['avg_hours'] < 24
                ? round($health['avg_hours']).' h'
                : round($health['avg_hours'] / 24, 1).' d');

        // Span of booking months the figures below actually cover.
        $months = collect($monthlyStats)->keys()->filter()->sort()->values();
        $periodLabel = $months->isEmpty()
            ? 'No data yet'
            : collect([$months->first(), $months->last()])
                ->unique()
                ->map(fn ($m) => rescue(fn () => \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $m.'-01')->format('M Y'), $m, false))
                ->implode(' – ');
    @endphp

    <div class="panel rep-hero">
        <div>
            <span class="rep-eyebrow">📈 System Report</span>
            <h2>UniKLAB RCMP</h2>
            <div class="rep-hero-sub">
                Generated {{ now()->format('d/m/Y') }} · {{ number_format($summary['total']) }} booking{{ $summary['total'] === 1 ? '' : 's' }} on record
                · {{ number_format($summary['active_blocks']) }} active block{{ $summary['active_blocks'] === 1 ? '' : 's' }}
            </div>
        </div>
        <div class="rep-hero-side">
            <div class="rep-hero-stat">
                <strong>{{ $periodLabel }}</strong>
                <span>Booking period covered</span>
            </div>
            <button type="button" class="button button-secondary no-print" onclick="window.print()">🖨 Print / Save as PDF</button>
        </div>
    </div>

    <section class="kpi-grid rep-kpis">
        <div class="kpi rep-kpi">
            <div class="rep-kpi-top"><span class="rep-kpi-icon">📋</span><span class="kpi-chip">All time</span></div>
            <strong>{{ number_format($summary['total']) }}</strong>
            <span class="kpi-label">Total Bookings</span>
            @include('admin.partials.kpi-split', ['counts' => $breakdown['total']])
        </div>
        <div class="kpi kpi--warn rep-kpi">
            <div class="rep-kpi-top"><span class="rep-kpi-icon">⏳</span>@if ($summary['pending'])<span class="kpi-cta">Needs action</span>@endif</div>
            <strong>{{ number_format($summary['pending']) }}</strong>
            <span class="kpi-label">Pending</span>
            @include('admin.partials.kpi-split', ['counts' => $breakdown['pending']])
        </div>
        {{-- A late response is always a decided booking, so it's reported as a
             footnote on each outcome rather than as a card of its own. --}}
        <div class="kpi kpi--ok rep-kpi">
            <div class="rep-kpi-top"><span class="rep-kpi-icon">✅</span></div>
            <strong>{{ number_format($summary['approved']) }}</strong>
            <span class="kpi-label">Approved</span>
            @include('admin.partials.kpi-split', ['counts' => $breakdown['approved'], 'late' => $lateByStatus['approved']])
        </div>
        <div class="kpi kpi--bad rep-kpi">
            <div class="rep-kpi-top"><span class="rep-kpi-icon">❌</span></div>
            <strong>{{ number_format($summary['rejected']) }}</strong>
            <span class="kpi-label">Rejected</span>
            @include('admin.partials.kpi-split', ['counts' => $breakdown['rejected'], 'late' => $lateByStatus['rejected']])
        </div>
        <div class="kpi rep-kpi rep-kpi--cancel">
            <div class="rep-kpi-top"><span class="rep-kpi-icon">⊘</span></div>
            <strong>{{ number_format($summary['cancelled']) }}</strong>
            <span class="kpi-label">Cancelled</span>
            @include('admin.partials.kpi-split', ['counts' => $breakdown['cancelled'], 'late' => $lateByStatus['cancelled']])
        </div>
        <div class="kpi rep-kpi rep-kpi--block">
            <div class="rep-kpi-top"><span class="rep-kpi-icon">🚫</span></div>
            <strong>{{ number_format($summary['active_blocks']) }}</strong>
            <span class="kpi-label">Active Blocks</span>
            @include('admin.partials.kpi-split', ['counts' => $breakdown['active_blocks']])
        </div>
        <div class="kpi rep-kpi">
            <div class="rep-kpi-top"><span class="rep-kpi-icon">📆</span></div>
            <strong>{{ number_format($summary['upcoming_14_days']) }}</strong>
            <span class="kpi-label">Next 14 Days</span>
            @include('admin.partials.kpi-split', ['counts' => $breakdown['upcoming_14_days']])
        </div>
    </section>

    <div class="rep-split rep-split--gauge">
        <div class="card chart-card">
            <h3>⚡ Response Health</h3>
            <div class="sub">How decided requests were answered against their lab date</div>
            @php
                $onTimeRate = min(100, max(0, (int) $health['on_time_rate']));
                $circumference = 2 * M_PI * 15.9155;
                $arcLen = $onTimeRate / 100 * $circumference;
                $ringColor = $onTimeRate >= 85 ? '#2f8a52' : ($onTimeRate >= 60 ? '#d69e2e' : '#c0392b');
            @endphp
            <div class="gauge-wrap">
                {{-- The arc still shows the on-time share visually; the centre
                     reports plain counts rather than a percentage figure. --}}
                <div class="gauge-ring">
                    <svg viewBox="0 0 36 36" width="122" height="122" role="img"
                         aria-label="{{ $health['on_time'] }} of {{ $health['decided'] }} decided bookings answered on time">
                        <circle cx="18" cy="18" r="15.9155" fill="none" stroke="var(--line)" stroke-width="3.4"/>
                        <circle class="arc" cx="18" cy="18" r="15.9155" fill="none" stroke="{{ $ringColor }}" stroke-width="3.4"
                            stroke-linecap="round"
                            style="--dash: {{ round($arcLen, 2) }}; --gap: {{ round($circumference - $arcLen, 2) }};"/>
                    </svg>
                    <div class="ring-value">
                        <b>{{ number_format($health['on_time']) }}</b>
                        <span>of {{ number_format($health['decided']) }} on time</span>
                    </div>
                </div>
                <div class="rh-list">
                    <div class="rh-row">
                        <span class="rh-dot" style="background:#2f8a52;"></span>
                        <span class="rh-label">Answered on time</span>
                        <span class="rh-num">{{ number_format($health['on_time']) }}</span>
                    </div>
                    <div class="rh-row">
                        <span class="rh-dot" style="background:#c0392b;"></span>
                        <span class="rh-label">Answered late</span>
                        <span class="rh-num">{{ number_format($health['late']) }}</span>
                    </div>
                    <div class="rh-row">
                        <span class="rh-dot" style="background:#d69e2e;"></span>
                        <span class="rh-label">Still awaiting a decision</span>
                        <span class="rh-num">{{ number_format($summary['pending']) }}</span>
                    </div>
                    <div class="rh-row">
                        <span class="rh-dot" style="background:var(--brand);"></span>
                        <span class="rh-label">Avg. time to decide</span>
                        <span class="rh-num">{{ $avgLabel }}</span>
                    </div>
                </div>
            </div>
            @if ($health['oldest_pending_days'] !== null)
                <div class="rh-note">
                    Longest wait right now — <strong>{{ $health['oldest_pending_ref'] }}</strong>
                    ({{ $health['oldest_pending_name'] }}) has been pending
                    <strong>{{ (int) $health['oldest_pending_days'] }} day{{ (int) $health['oldest_pending_days'] === 1 ? '' : 's' }}</strong>.
                </div>
            @else
                <div class="rh-note">No requests are waiting for a decision right now.</div>
            @endif
        </div>

        <div class="card chart-card">
            <h3>📊 Monthly Breakdown</h3>
            <div class="sub">Bookings by outcome, plus the blocks scheduled that month</div>
            @php
                $statusColors = ['approved' => '#2f8a52', 'pending' => '#d69e2e', 'rejected' => '#c0392b', 'cancelled' => '#8a97a0', 'blocked' => '#b0835f'];
                $monthMax = max(1, collect($monthlyStats)->map(fn ($counts) => array_sum($counts))->max() ?? 1);
            @endphp
            @if (count($monthlyStats))
                <div class="chart-plot">
                    <div class="chart-grid" aria-hidden="true"><i></i><i></i><i></i><i></i></div>
                    <div class="chart-bars">
                        @foreach ($monthlyStats as $month => $counts)
                            @php
                                $segs = [];
                                foreach ($statusColors as $st => $color) {
                                    if (($counts[$st] ?? 0) > 0) $segs[] = ['status' => $st, 'value' => $counts[$st]];
                                }
                                $monthTotal = array_sum($counts);
                                $bookingTotal = $monthTotal - ($counts['blocked'] ?? 0);
                                $barH = $monthTotal > 0 ? max(6, round($monthTotal / $monthMax * 130)) : 2;
                                $monthDate = rescue(fn () => \Illuminate\Support\Carbon::createFromFormat('Y-m-d', $month.'-01'), null, false);
                            @endphp
                            <div class="chart-bar-col {{ $monthTotal === $monthMax ? 'is-peak' : '' }}"
                                 title="{{ $monthDate?->format('F Y') ?? $month }}: {{ $bookingTotal }} booking{{ $bookingTotal === 1 ? '' : 's' }} · {{ $counts['blocked'] ?? 0 }} block{{ ($counts['blocked'] ?? 0) === 1 ? '' : 's' }}">
                                <div class="chart-bar-stack">
                                    <div class="chart-bar-plot">
                                        <span class="chart-bar-total">{{ $monthTotal ?: '' }}</span>
                                        <div class="chart-bar" style="height:{{ $barH }}px; animation-delay:{{ $loop->index * 55 }}ms;">
                                            @foreach ($segs as $seg)
                                                @php $segH = $monthTotal > 0 ? round($seg['value'] / $monthTotal * $barH) : 0; @endphp
                                                <div style="height:{{ $segH }}px; background:{{ $statusColors[$seg['status']] }};"></div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <span class="chart-bar-label">{{ $monthDate?->format('M y') ?? $month }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="chart-legend">
                    @foreach ($statusColors as $st => $color)
                        <span><span class="sw" style="background:{{ $color }}"></span>{{ $st === 'blocked' ? 'Blocked slots' : ucfirst($st) }}</span>
                    @endforeach
                </div>
            @else
                <div class="empty">No booking or block history yet.</div>
            @endif
        </div>
    </div>

    <div class="rep-split rep-split--usage">
        <div class="card chart-card">
            <h3>🏆 Top 5 Labs by Usage</h3>
            <div class="sub">Most-booked rooms across all time</div>
            @php $labMax = max(1, $labUsage->max('sessions') ?? 1); @endphp
            <div class="hbar-list">
                @forelse ($labUsage as $row)
                    <div class="hbar-row">
                        <span class="hbar-rank">{{ $loop->iteration }}</span>
                        <div class="hbar-main">
                            <div class="hbar-label" title="{{ $row->name }}">{{ $row->name }}</div>
                            <div class="hbar-track">
                                <div class="hbar-fill" style="width:{{ $row->sessions > 0 ? max(4, round($row->sessions / $labMax * 100)) : 0 }}%; animation-delay:{{ $loop->index * 80 }}ms;"></div>
                            </div>
                        </div>
                        <span class="hbar-value">{{ $row->sessions }}<small>session{{ $row->sessions === 1 ? '' : 's' }}</small></span>
                    </div>
                @empty
                    <div class="empty">No usage data yet.</div>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="panel-head"><h3 style="margin:0; font-size:.96rem;">🕓 Recent Activities</h3></div>
            <div class="panel-scroll ract-list">
                @forelse ($recentActivities as $activity)
                    @php
                        $icon = ['approved' => '✓', 'rejected' => '✕', 'cancelled' => '−', 'block' => '🚫'][$activity['action'] ?? ''] ?? '•';
                    @endphp
                    <div class="ract-item">
                        <span class="ract-mark ract-mark--{{ $activity['action'] ?? 'other' }}">{{ $icon }}</span>
                        <strong>
                            {{ $activity['title'] }}
                            @if (!empty($activity['late']))
                                <span class="ract-late">Late response</span>
                            @endif
                        </strong>
                        <div class="muted">{{ $activity['time']?->format('d/m/Y H:i') }} · {{ $activity['meta'] }}</div>
                    </div>
                @empty
                    <div class="ract-item muted">No recent activity</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
