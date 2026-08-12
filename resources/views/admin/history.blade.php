@extends('layouts.admin')

@section('content')
    <style>
        .hist-list { position: relative; }
        .hist-item { display: grid; grid-template-columns: 104px 26px 1fr; align-items: stretch; }
        .hist-when { padding: 15px 10px 6px 0; text-align: right; }
        .hist-when-date { font-size: .78rem; font-weight: 700; color: var(--ink); font-family: var(--mono); }
        .hist-when-time { font-size: .7rem; color: var(--muted); margin-top: 2px; white-space: nowrap; font-family: var(--mono); }
        .hist-rail { position: relative; display: flex; justify-content: center; }
        .hist-rail::before { content: ''; position: absolute; top: 0; bottom: 0; width: 2px; background: var(--line); }
        .hist-item:first-child .hist-rail::before { top: 22px; }
        .hist-item:last-child .hist-rail::before { bottom: calc(100% - 22px); }
        .hist-node { width: 26px; height: 26px; border-radius: 50%; margin-top: 10px; z-index: 1; border: 2px solid #fff; box-shadow: 0 0 0 1.5px rgba(0,0,0,.06); display: grid; place-items: center; font-size: .74rem; }
        .hist-card { margin: 9px 0 9px 14px; padding: 12px 16px; border: 1px solid var(--line); border-radius: var(--radius-sm); background: #fff; flex: 1; transition: box-shadow .14s, transform .14s; border-left: 3px solid var(--line); }
        .hist-item:hover .hist-card { box-shadow: var(--shadow-sm); transform: translateX(2px); }
        .hist-card-top { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
        .hist-action { font-weight: 800; font-size: .84rem; }
        .hist-card-ref { font-family: var(--mono); font-weight: 700; color: var(--brand-2); font-size: .76rem; }
        .hist-card-sub { font-size: .8rem; color: var(--ink); margin-top: 4px; display: flex; align-items: center; gap: 7px; flex-wrap: wrap; }
        .hist-card-who { font-size: .74rem; color: var(--muted); margin-top: 4px; }
        .hist-card-who b { color: var(--ink); font-weight: 700; }
        .hist-card-detail-note { font-size: .74rem; color: var(--muted); margin-top: 3px; font-style: italic; }

        .hist-card summary { list-style: none; cursor: pointer; position: relative; padding-right: 22px; }
        .hist-card summary::-webkit-details-marker { display: none; }
        .hist-card summary::after {
            content: '▾'; position: absolute; right: 0; top: 2px; color: var(--muted); font-size: .72rem;
            transition: transform .15s ease;
        }
        .hist-card details[open] summary::after { transform: rotate(180deg); }
        .hist-card-detail { margin-top: 10px; padding-top: 10px; border-top: 1px dashed var(--line); display: grid; gap: 7px; }
        .hist-card-detail .row { display: flex; gap: 10px; font-size: .8rem; }
        .hist-card-detail .row .lbl { flex: 0 0 130px; color: var(--muted); font-weight: 600; }
        .hist-card-detail .row .val { flex: 1; min-width: 0; word-break: break-word; }
        .hist-card-detail ul { margin: 0; padding-left: 18px; }

        @media (max-width: 480px) {
            .hist-item { grid-template-columns: 72px 20px 1fr; }
            .hist-card { margin-left: 10px; padding: 10px 12px; }
            .hist-card-detail .row { flex-direction: column; gap: 2px; }
            .hist-card-detail .row .lbl { flex: none; }
        }

        .hist-filter-bar { display: flex; gap: 10px; align-items: center; margin: 20px 0 16px; flex-wrap: wrap; }
        .hist-filter-bar input, .hist-filter-bar select {
            min-height: 42px; padding: 0 13px; border-radius: var(--radius-sm); border: 1.5px solid var(--line);
            font-family: inherit; font-size: .88rem; background: var(--panel-strong);
        }
        .hist-filter-bar input { flex: 1; min-width: 200px; }
        .hist-filter-bar input:focus, .hist-filter-bar select:focus { outline: none; border-color: var(--brand); box-shadow: 0 0 0 3px rgba(125,145,148,.14); }

        .hist-panel-head { padding: 15px 18px; border-bottom: 1px solid var(--line); display: flex; justify-content: space-between; align-items: center; }
        .hist-week-pager { display: flex; align-items: center; justify-content: center; gap: 16px; padding: 12px 18px; border-bottom: 1px solid var(--line); background: rgba(125,145,148,.05); flex-wrap: wrap; }
        .hist-week-pager .wk-label { font-weight: 700; font-size: .84rem; }
        .hist-week-pager .wk-sub { font-size: .72rem; color: var(--muted); margin-top: 2px; }
        @media (max-width: 480px) {
            .hist-week-pager { gap: 8px; padding: 10px 12px; }
            .hist-week-pager > div { min-width: 0 !important; }
        }

        /* Decision taken after the booking date had already passed. */
        .hist-late { display: inline-block; padding: 1px 7px; border-radius: 999px; font-size: .6rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #c0392b; background: rgba(192,57,43,.1); border: 1px solid rgba(192,57,43,.28); }
    </style>

    @php
        $actionMeta = [
            'created'    => ['label' => 'Submitted',  'icon' => '📝', 'color' => 'var(--brand-2, #4a5f62)'],
            'approved'   => ['label' => 'Approved',   'icon' => '✓',  'color' => '#1e6b3b'],
            'rejected'   => ['label' => 'Rejected',   'icon' => '✕',  'color' => '#a03027'],
            'cancelled'  => ['label' => 'Cancelled',  'icon' => '⊘',  'color' => '#8a97a0'],
            'reassigned' => ['label' => 'Reassigned', 'icon' => '⇄',  'color' => '#a07c1f'],
            'blocked'    => ['label' => 'Blocked',    'icon' => '🚫', 'color' => 'var(--type-block, #c2650f)'],
        ];
        // Only the newest week is shown on first paint (the pager reveals the
        // rest) — hiding older weeks server-side avoids a flash of all events.
        $firstWeek = $weeks->first()['key'] ?? null;
    @endphp

    <section class="kpi-grid">
        <div class="kpi"><strong>{{ $stats['total'] }}</strong><span class="kpi-label">Total Bookings</span></div>
        <div class="kpi"><strong>{{ $stats['submitted'] }}</strong><span class="kpi-label">Submitted</span></div>
        <div class="kpi kpi--ok"><strong>{{ $stats['approved'] }}</strong><span class="kpi-label">Approved</span></div>
        <div class="kpi kpi--bad"><strong>{{ $stats['rejected'] }}</strong><span class="kpi-label">Rejected</span></div>
        {{-- Answered only after the booked day had passed (the red badges below). --}}
        <div class="kpi {{ $stats['late'] ? 'kpi--bad' : 'kpi--ok' }}">
            <strong>{{ $stats['late'] }}</strong>
            <span class="kpi-label">Late Responses</span>
        </div>
        <div class="kpi kpi--warn"><strong>{{ $stats['blocked'] }}</strong><span class="kpi-label">Blocks</span></div>
    </section>

    <div class="hist-filter-bar">
        <input type="text" id="histSearch" placeholder="Search name, ref, room, person…" oninput="filterHistory()">
        <select id="histActionFilter" onchange="filterHistory()">
            <option value="">All Actions</option>
            <option value="created">Submitted</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
            <option value="cancelled">Cancelled</option>
            <option value="reassigned">Reassigned</option>
            <option value="blocked">Blocked</option>
        </select>
        <select id="histTypeFilter" onchange="filterHistory()">
            <option value="">All Types</option>
            <option value="research">Research</option>
            <option value="csl">CSL</option>
            <option value="pharma">Pharma</option>
        </select>
        <span class="muted" style="font-size:.78rem;" id="histCount">{{ $events->count() }} bookings</span>
    </div>

    <div class="card">
        <div class="hist-panel-head">
            <strong>🕓 Activity Timeline</strong>
            <span class="muted" style="font-size:.74rem;">Audit trail · most recent first</span>
        </div>

        @if (!$events->isEmpty())
            <div id="hist-week-pager" class="hist-week-pager">
                <button type="button" class="button button-secondary" id="hist-week-prev" style="min-height:34px; padding:0 14px; font-size:.78rem;" onclick="weekStep(1)">← Older</button>
                <div style="text-align:center; min-width:230px;">
                    <div class="wk-label" id="hist-week-label">—</div>
                    <div class="wk-sub" id="hist-week-sub"></div>
                </div>
                <button type="button" class="button button-secondary" id="hist-week-next" style="min-height:34px; padding:0 14px; font-size:.78rem;" onclick="weekStep(-1)">Newer →</button>
            </div>
        @endif

        @if ($events->isEmpty())
            <div class="empty">No activity yet — booking and block actions will appear here.</div>
        @else
            <div class="hist-list" style="padding:6px 18px;">
                @foreach ($events as $e)
                    @php
                        $meta = $actionMeta[$e['action']] ?? ['label' => ucfirst($e['action']), 'icon' => '•', 'color' => '#666'];
                        $typeColor = ['research' => 'var(--type-research, #7d9194)', 'csl' => 'var(--type-csl, #8c7a9c)', 'pharma' => 'var(--type-pharma, #7d9068)'][$e['type']] ?? '#666';
                    @endphp
                    <div class="hist-item"
                        data-action="{{ $e['action'] }}"
                        data-type="{{ $e['type'] }}"
                        data-week="{{ $e['week'] }}"
                        data-text="{{ strtolower($e['title'].' '.$e['ref'].' '.$e['sub'].' '.$e['by']) }}"
                        style="{{ $e['week'] !== $firstWeek ? 'display:none;' : '' }}">
                        <div class="hist-when">
                            <div class="hist-when-date">{{ $e['at']?->format('d/m/Y') ?? '—' }}</div>
                            <div class="hist-when-time">{{ $e['at']?->format('H:i') ?? '' }}</div>
                        </div>
                        <div class="hist-rail">
                            <span class="hist-node" style="background:{{ $meta['color'] }}; color:#fff;">{{ $meta['icon'] }}</span>
                        </div>
                        <div class="hist-card" style="border-left-color:{{ $meta['color'] }};">
                            <details>
                                <summary>
                                    <div class="hist-card-top">
                                        <span class="hist-action" style="color:{{ $meta['color'] }};">{{ $meta['label'] }}</span>
                                        @if (!empty($e['late']))
                                            <span class="hist-late" title="Answered after the booking date had passed">Late response</span>
                                        @endif
                                        <span class="hist-card-ref">{{ $e['ref'] }}</span>
                                    </div>
                                    <div class="hist-card-sub">
                                        <strong>{{ $e['kind'] === 'block' ? '🚫 '.$e['title'] : $e['title'] }}</strong>
                                        <span class="badge" style="background:rgba(0,0,0,.05); color:{{ $typeColor }};">{{ ucfirst($e['type']) }}</span>
                                        @if ($e['status'] && $e['kind'] === 'booking')
                                            <span class="badge badge-{{ $e['status'] }}">{{ ucfirst($e['status']) }}</span>
                                        @endif
                                    </div>
                                    <div class="hist-card-who">
                                        by <b>{{ $e['by'] }}</b>
                                        @if ($e['booking_date']) · {{ $e['booking_date'] }} {{ $e['time'] }}@endif
                                        @if ($e['sub']) · {{ $e['sub'] }}@endif
                                    </div>
                                    @if (!empty($e['detail']))
                                        <div class="hist-card-detail-note">“{{ $e['detail'] }}”</div>
                                    @endif
                                </summary>

                                <div class="hist-card-detail">
                                    @if ($e['kind'] === 'booking')
                                        @if ($e['applicant_email'])
                                            <div class="row"><span class="lbl">Email</span><span class="val">{{ $e['applicant_email'] }}</span></div>
                                        @endif
                                        @if ($e['applicant_id'])
                                            <div class="row"><span class="lbl">Staff / Student ID</span><span class="val">{{ $e['applicant_id'] }}</span></div>
                                        @endif
                                        @if ($e['applicant_phone'])
                                            <div class="row"><span class="lbl">Phone number</span><span class="val">{{ $e['applicant_phone'] }}</span></div>
                                        @endif
                                        @if ($e['applicant_department'])
                                            <div class="row"><span class="lbl">Department</span><span class="val">{{ $e['applicant_department'] }}</span></div>
                                        @endif
                                        @if ($e['applicant_role'])
                                            <div class="row"><span class="lbl">Role</span><span class="val">{{ $e['applicant_role'] }}</span></div>
                                        @endif
                                        @if (!empty($e['rooms_equipment']))
                                            <div class="row"><span class="lbl">Rooms &amp; equipment</span>
                                                <ul class="val">
                                                    @foreach ($e['rooms_equipment'] as $re)
                                                        <li>{{ $re['name'] }}{{ !empty($re['equipment']) ? ' — '.implode(', ', $re['equipment']) : '' }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                        @if (!empty($e['students']))
                                            <div class="row"><span class="lbl">Students</span>
                                                <ul class="val">
                                                    @foreach ($e['students'] as $student)
                                                        <li>{{ $student }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                        @if (!empty($e['audit_trail']))
                                            <div class="row" style="align-items:flex-start;"><span class="lbl">Audit trail</span>
                                                <ul class="val" style="display:grid; gap:7px; list-style:none; padding-left:0; margin:0;">
                                                    @foreach ($e['audit_trail'] as $log)
                                                        @php $lm = $actionMeta[$log['action']] ?? ['label' => ucfirst($log['action']), 'color' => 'var(--ink)']; @endphp
                                                        <li>
                                                            <span style="font-weight:700; color:{{ !empty($log['late']) ? '#c0392b' : $lm['color'] }};">{{ $lm['label'] }}</span>
                                                            @if (!empty($log['late']))
                                                                <span class="hist-late" title="Answered after the booking date had passed">Late response</span>
                                                            @endif
                                                            <span class="muted" style="font-size:.72rem;">— {{ $log['by'] }}{{ $log['at'] ? ' · '.$log['at'] : '' }}</span>
                                                            @if ($log['detail'])
                                                                <div style="font-size:.79rem; color:var(--ink); margin-top:1px;">“{{ $log['detail'] }}”</div>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    @else
                                        @if ($e['pic'])
                                            <div class="row"><span class="lbl">PIC</span><span class="val">{{ $e['pic'] }}</span></div>
                                        @endif
                                        <div class="row"><span class="lbl">Rooms</span><span class="val">{{ $e['sub'] ?: '—' }}</span></div>
                                        @if (!empty($e['equipment']))
                                            <div class="row"><span class="lbl">Equipment</span><span class="val">{{ implode(', ', $e['equipment']) }}</span></div>
                                        @endif
                                        @if ($e['purpose'])
                                            <div class="row"><span class="lbl">Purpose</span><span class="val">{{ $e['purpose'] }}</span></div>
                                        @endif
                                        @if ($e['recurring'] && $e['recurring'] !== 'none')
                                            <div class="row"><span class="lbl">Recurring</span><span class="val">{{ ucfirst($e['recurring']) }}</span></div>
                                        @endif
                                        @if ($e['notes'])
                                            <div class="row"><span class="lbl">Notes</span><span class="val">{{ $e['notes'] }}</span></div>
                                        @endif
                                    @endif
                                </div>
                            </details>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <script>
        const HIST_WEEKS = @json($weeks);   // newest-first: [{ key, label, count }, …]
        let weekIdx = 0;

        function histFiltersActive() {
            return (document.getElementById('histSearch').value || '') !== ''
                || document.getElementById('histActionFilter').value !== ''
                || document.getElementById('histTypeFilter').value !== '';
        }

        // Week view by default (one week at a time to keep scrolling short);
        // as soon as any search/filter is used, show every matching event across
        // all weeks so nothing is hidden behind the pager.
        function renderHistory() {
            const q = (document.getElementById('histSearch').value || '').toLowerCase();
            const af = document.getElementById('histActionFilter').value;
            const tf = document.getElementById('histTypeFilter').value;
            const filtering = histFiltersActive();
            const wkKey = HIST_WEEKS[weekIdx] ? HIST_WEEKS[weekIdx].key : null;

            const pager = document.getElementById('hist-week-pager');
            if (pager) pager.style.display = (filtering || !HIST_WEEKS.length) ? 'none' : 'flex';

            let vis = 0;
            document.querySelectorAll('.hist-item').forEach(it => {
                const pass = (!q || (it.dataset.text || '').includes(q))
                    && (!af || it.dataset.action === af)
                    && (!tf || it.dataset.type === tf);
                const show = filtering ? pass : (pass && it.dataset.week === wkKey);
                it.style.display = show ? '' : 'none';
                if (show) vis++;
            });

            if (!filtering && HIST_WEEKS[weekIdx]) {
                document.getElementById('hist-week-label').textContent = 'Week of ' + HIST_WEEKS[weekIdx].label;
                document.getElementById('hist-week-sub').textContent = HIST_WEEKS[weekIdx].count + ' event' + (HIST_WEEKS[weekIdx].count !== 1 ? 's' : '');
            }
            const prev = document.getElementById('hist-week-prev');
            const next = document.getElementById('hist-week-next');
            if (prev) { prev.disabled = weekIdx >= HIST_WEEKS.length - 1; prev.style.opacity = prev.disabled ? '.4' : '1'; }
            if (next) { next.disabled = weekIdx <= 0; next.style.opacity = next.disabled ? '.4' : '1'; }

            const c = document.getElementById('histCount');
            if (c) c.textContent = vis + ' event' + (vis !== 1 ? 's' : '') + (filtering ? ' (all weeks)' : ' this week');
        }

        function weekStep(delta) {
            const n = weekIdx + delta;
            if (n < 0 || n > HIST_WEEKS.length - 1) return;
            weekIdx = n;
            renderHistory();
        }

        // Filter inputs call this (kept name for the inline handlers).
        function filterHistory() { renderHistory(); }

        renderHistory();
    </script>
@endsection
