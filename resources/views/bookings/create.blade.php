@extends('layouts.app')

@section('content')
    <style>
        :root {
            --bk-ok: #1e6b3b;
            --bk-danger: #c0392b;
            --bk-field-bg: #fdfcfb;
        }

        @keyframes bkFadeUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: none; } }
        @media (prefers-reduced-motion: reduce) { * { animation: none !important; scroll-behavior: auto !important; } }

        /* ---- Hero band ---- */
        .booking-hero { padding: 56px 0 0; }
        .booking-hero .title { font-size: clamp(1.9rem, 4vw, 2.9rem); }

        .type-tabs { display: flex; gap: 0; margin-top: 30px; border-bottom: 1px solid var(--line); overflow-x: auto; }
        .type-tabs a {
            display: flex; align-items: center; gap: 8px; padding: 14px 4px; margin-right: 30px;
            color: var(--muted); font-weight: 700; font-size: 0.95rem; text-decoration: none;
            border-bottom: 3px solid transparent; white-space: nowrap; transition: color 0.15s ease, border-color 0.15s ease;
        }
        .type-tabs a:hover { color: var(--ink); }
        .type-tabs a.active { color: var(--ink); border-color: var(--brand); }

        /* ---- Step rail ---- */
        .step-rail-band { padding: 26px 0; }    
        .step-rail { display: flex; align-items: center; }
        .step-item { display: flex; align-items: center; gap: 14px; }
        .step-circle {
            width: 42px; height: 42px; border-radius: 50%; background: #fff; color: var(--muted);
            border: 2px solid var(--line); font-family: 'Sora', sans-serif; font-weight: 800; font-size: 0.98rem;
            display: grid; place-items: center; flex-shrink: 0;
            transition: transform .25s ease, background .25s ease, color .25s ease, border-color .25s ease, box-shadow .25s ease;
        }
        .step-item.active .step-circle { background: var(--grad); color: #fff; border-color: transparent; box-shadow: 0 0 0 6px rgba(125, 145, 148,.15); }
        .step-item.done .step-circle { background: var(--bk-ok); color: #fff; border-color: transparent; font-size: 0; }
        .step-item.done .step-circle::after { content: '✓'; font-size: 1.1rem; }
        .step-item-label { font-size: 0.96rem; font-weight: 700; color: var(--muted); }
        .step-item.active .step-item-label, .step-item.done .step-item-label { color: var(--ink); }
        .step-line { flex: 1; height: 3px; background: var(--line); margin: 0 22px; min-width: 24px; border-radius: 2px; transition: background .3s ease; }
        .step-line.done { background: var(--bk-ok); }
        @media (max-width: 700px) { .step-item-label { display: none; } .step-line { margin: 0 10px; } }

        /* ---- Two-column layout: form + sticky ticket ---- */
        .booking-band { padding: 8px 0 64px; }
        .booking-layout { display: grid; grid-template-columns: 1fr 280px; gap: 28px; align-items: start; }
        .booking-main { min-width: 0; display: grid; gap: 20px; }

        .booking-ticket {
            position: sticky; top: 96px; background: var(--panel-strong); border: 1px solid var(--line);
            border-radius: var(--radius); box-shadow: var(--shadow); padding: 22px;
        }
        .ticket-head { font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: var(--muted); margin-bottom: 14px; }
        .ticket-rows { display: grid; gap: 12px; }
        .ticket-row { padding-bottom: 12px; border-bottom: 1px solid var(--line); }
        .ticket-row:last-child { border-bottom: none; padding-bottom: 0; }
        .ticket-row dt { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.04em; color: var(--muted); font-weight: 700; margin-bottom: 3px; }
        .ticket-row dd { margin: 0; font-size: 0.92rem; font-weight: 700; color: var(--ink); font-family: var(--mono); word-break: break-word; }

        @media (max-width: 880px) {
            .booking-layout { grid-template-columns: 1fr; }
            .booking-ticket {
                position: fixed; top: auto; left: 0; right: 0; bottom: calc(64px + env(safe-area-inset-bottom)); z-index: 65;
                border-radius: 0; border-left: none; border-right: none; box-shadow: 0 -8px 24px -16px rgba(49, 43, 44, 0.25);
                padding: 10px 16px;
            }
            .ticket-head { display: none; }
            .ticket-rows { display: flex; gap: 18px; overflow-x: auto; -webkit-overflow-scrolling: touch; }
            .ticket-row { flex: 0 0 auto; border-bottom: none; padding-bottom: 0; white-space: nowrap; }
            body { padding-bottom: calc(64px + 64px + env(safe-area-inset-bottom)); }
        }

        /* ---- Section cards ---- */
        .section-card {
            padding: 26px 28px; border-radius: var(--radius);
            border: 1px solid var(--line); background: #fff; box-shadow: var(--shadow);
            animation: bkFadeUp .4s ease both;
        }
        .section-heading {
            display: flex; align-items: center; gap: 14px; margin-bottom: 22px;
            padding-bottom: 18px; border-bottom: 1px solid var(--line);
        }
        .section-num {
            width: 36px; height: 36px; border-radius: 11px; background: var(--grad); color: #fff;
            font-family: 'Sora', sans-serif; font-weight: 800; font-size: 0.96rem; display: grid; place-items: center;
            flex-shrink: 0;
        }
        .section-heading h3 { margin: 0; font-size: 1.16rem; }
        .subsection-heading { margin: 20px 0 10px; font-size: 0.74rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--brand-2); font-weight: 800; }

        /* ---- Form fields ---- */
        .field { display: grid; gap: 7px; }
        .field > span.muted, .field > .muted { font-size: 0.82rem; font-weight: 600; color: var(--muted); }
        .field input[type="text"], .field input[type="email"], .field input[type="number"],
        .field input[type="date"], .field input[type="time"], .field select, .field textarea {
            width: 100%; min-width: 0; min-height: 46px; padding: 0 14px; border-radius: var(--radius-sm); border: 1.5px solid var(--line); font-family: inherit;
            font-size: 0.94rem; color: var(--ink); background: var(--bk-field-bg);
            transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
            box-sizing: border-box;
        }
        .field textarea { padding: 12px 14px; min-height: auto; line-height: 1.5; resize: vertical; }
        .field input:hover, .field select:hover, .field textarea:hover { border-color: rgba(125, 145, 148,.4); }
        .field input:focus, .field select:focus, .field textarea:focus {
            outline: none; border-color: var(--brand); background: #fff; box-shadow: 0 0 0 3px rgba(125, 145, 148,.14);
        }
        .field .req { color: var(--bk-danger); font-weight: 800; margin-left: 2px; }
        .field input#duration-display { background: rgba(125, 145, 148,.07); color: var(--brand-2); font-weight: 700; font-family: var(--mono); cursor: default; }
        .field input#duration-display:hover, .field input#duration-display:focus { border-color: var(--line); box-shadow: none; background: rgba(125, 145, 148,.07); }

        /* ---- Building filter chips ---- */
        .filter-chips { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; }
        .chip {
            padding: 9px 18px; border-radius: 999px; border: 1.5px solid var(--line); background: #fff;
            font-size: 0.86rem; font-weight: 700; color: var(--muted); cursor: pointer; transition: all .15s ease;
        }
        .chip:hover { border-color: rgba(125, 145, 148,.5); color: var(--brand-2); }
        .chip.active { border-color: transparent; background: var(--grad); color: #fff; }

        /* ---- Room / lab selection card grid ---- */
        .room-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 14px; }
        .room-group { border: 1.5px solid var(--line); border-radius: var(--radius); background: #fff; overflow: hidden; transition: border-color .15s ease, box-shadow .15s ease; }
        .room-group:has(input[name="lab_ids[]"]:checked) { border-color: var(--brand); box-shadow: 0 0 0 3px rgba(125, 145, 148,.12); }
        .room-group-label { list-style: none; cursor: pointer; margin: 0; padding: 18px 18px 6px; display: block; }
        .room-group-label::-webkit-details-marker { display: none; }
        .room-check { display: flex; align-items: flex-start; gap: 10px; }
        .room-check input[type="checkbox"], .room-check input[type="radio"] { width: 19px; height: 19px; accent-color: var(--brand); margin-top: 2px; flex-shrink: 0; }
        .room-check-text { flex: 1; min-width: 0; }
        .room-check-text .name { font-weight: 800; font-size: 0.98rem; display: block; }
        .room-tile-badges { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
        .room-more-toggle { display: flex; align-items: center; gap: 6px; padding: 12px 18px 16px; font-size: 0.8rem; font-weight: 700; color: var(--brand-2); }
        .room-more-toggle svg { transition: transform .2s ease; flex-shrink: 0; }
        .room-group[open] .room-more-toggle svg { transform: rotate(180deg); }
        .room-group-body { padding: 0 18px 18px; border-top: 1px solid var(--line); margin-top: 4px; padding-top: 14px; }

        .equip-list { display: flex; flex-direction: column; gap: 10px; }
        .equip-list label {
            display: flex; align-items: flex-start; gap: 8px; font-size: 0.88rem; padding: 10px 12px;
            border: 1px solid var(--line); border-radius: var(--radius-sm); background: var(--bk-field-bg); cursor: pointer;
            transition: border-color .15s ease, background .15s ease;
        }
        .equip-list label:hover { border-color: rgba(125, 145, 148,.4); }
        .equip-list label:has(input:checked) { border-color: var(--brand); background: rgba(125, 145, 148,.07); }
        .equip-list input[type="checkbox"] { width: 17px; height: 17px; margin-top: 1px; accent-color: var(--brand); flex-shrink: 0; }
        .equip-list label.equip-booked, .equip-list label.equip-closed { opacity: 0.5; }
        .equip-list label.equip-booked [data-equip-name]::after { content: ' — Booked for this time'; color: #a03027; font-weight: 700; }
        .equip-list label.equip-closed [data-equip-name]::after { content: ' — Room closed on weekends'; color: var(--brand-2); font-weight: 700; }

        .badge-strong { font-weight: 800 !important; border: 1.5px solid currentColor; }

        /* ---- CSL: card grid of room tiles, filtered by the chosen discipline ---- */
        .csl-room-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 10px; padding: 16px 18px; }
        .csl-room-tile {
            display: flex; align-items: flex-start; gap: 10px; padding: 14px; border: 1.5px solid var(--line);
            border-radius: var(--radius-sm); cursor: pointer; transition: border-color .15s ease, background .15s ease;
        }
        .csl-room-tile:hover { border-color: rgba(125, 145, 148,.4); }
        .csl-room-tile:has(input:checked) { border-color: var(--brand); background: rgba(125, 145, 148,.06); }
        .csl-room-tile input { width: 17px; height: 17px; accent-color: var(--brand); margin-top: 2px; flex-shrink: 0; }
        .csl-room-tile .tile-text { display: flex; flex-direction: column; gap: 3px; min-width: 0; }
        .csl-room-tile .name { font-weight: 700; font-size: 0.9rem; }
        .csl-room-tile .note { font-size: 0.76rem; color: var(--muted); line-height: 1.4; }
        /* Package disciplines book their whole room set — tiles stay ticked
           (and submitted) but can't be toggled. */
        .csl-room-tile--locked { pointer-events: none; border-color: var(--brand); background: rgba(125, 145, 148,.06); }
        .csl-room-tile--locked input { opacity: .65; }

        /* ---- Toggle cards ---- */
        .toggle-card {
            display: flex; align-items: center; gap: 12px; padding: 15px 16px; border-radius: var(--radius);
            border: 1.5px solid var(--line); background: var(--bk-field-bg); cursor: pointer; font-weight: 700; color: var(--ink);
            transition: border-color .15s ease, background .15s ease;
        }
        .toggle-card:hover { border-color: rgba(125, 145, 148,.45); }
        .toggle-card:has(input:checked) { border-color: var(--brand); background: rgba(125, 145, 148,.07); }
        .toggle-card input { width: 20px; height: 20px; accent-color: var(--brand); flex-shrink: 0; }

        /* ---- Pax repeater rows ---- */
        .pax-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .pax-rows { display: grid; gap: 10px; margin-top: 12px; }
        .pax-row input {
            min-height: 42px; padding: 0 14px; border-radius: var(--radius-sm); border: 1.5px solid var(--line);
            font-family: inherit; font-size: 0.94rem; background: var(--bk-field-bg);
            transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
        }
        .pax-row input:focus { outline: none; border-color: var(--brand); background: #fff; box-shadow: 0 0 0 3px rgba(125, 145, 148,.14); }

        /* ---- Terms & conditions gate ---- */
        .tc-gate {
            border: 1.5px solid rgba(148, 128, 111,.5); border-radius: var(--radius); padding: 24px;
            background: rgba(148, 128, 111,.06);
        }
        .tc-gate h3 { color: #7a5f18; }
        .tc-gate summary { cursor: pointer; font-weight: 800; }
        .tc-gate ul { margin: 12px 0 0; padding-left: 20px; display: grid; gap: 8px; font-size: 0.92rem; color: #6b5518; }
        .tc-gate label.accept-row {
            display: flex; align-items: center; gap: 12px; margin-top: 18px; padding: 14px 16px;
            border-radius: var(--radius-sm); background: #fff; border: 1.5px solid rgba(148, 128, 111,.6); font-weight: 800;
        }
        .tc-gate input[type="checkbox"] { width: 20px; height: 20px; accent-color: #a0791f; flex-shrink: 0; }
        #pharma-gate[disabled] { opacity: 0.4; pointer-events: none; filter: grayscale(.3); }

        /* ---- Review step ---- */
        .review-section { padding: 18px 0; border-top: 1px solid var(--line); }
        .review-section:first-child { padding-top: 0; border-top: none; }
        .review-section h4 { margin: 0 0 14px; font-size: 0.74rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--brand-2); font-weight: 800; }
        .review-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px 28px; }
        .review-label { display: block; font-size: 0.7rem; color: var(--muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 4px; }
        .review-value { display: block; font-size: 0.98rem; font-weight: 700; color: var(--ink); word-break: break-word; }
        .review-tags { display: flex; flex-wrap: wrap; gap: 8px; }
        .review-tag {
            display: inline-flex; align-items: center; padding: 7px 14px; border-radius: 999px;
            background: rgba(125, 145, 148,.12); color: var(--brand-2); font-size: 0.83rem; font-weight: 800;
        }
        .review-tag--muted { background: rgba(49, 43, 44,.07); color: var(--ink); }
        .review-banner {
            display: flex; gap: 12px; align-items: flex-start; padding: 15px 16px; border-radius: var(--radius-sm);
            background: rgba(224, 168, 32,.16);
            border: 1.5px solid rgba(196, 142, 20,.55); color: #7a5f18; font-size: 0.87rem;
            font-weight: 700; margin-bottom: 20px;
        }
        @media (max-width: 640px) { .review-grid { grid-template-columns: 1fr; } }
    </style>

    @if ($maintenanceBlocked ?? false)
        <section class="band band--tint" style="padding:80px 0;">
            <div class="band-inner" style="max-width:600px;">
                <div class="card" style="padding:48px 40px; text-align:center;">
                    <div style="width:64px; height:64px; margin:0 auto 20px; border-radius:50%; background:rgba(125,145,148,.14); display:flex; align-items:center; justify-content:center; font-size:1.8rem;">🛠️</div>
                    <h1 class="title" style="font-size:clamp(1.5rem, 3vw, 2.1rem);">{{ $maintenanceTitle }}</h1>
                    <p class="lede" style="margin-top:14px;">{{ $maintenanceMessage }}</p>
                    <div style="margin-top:28px; display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
                        <a href="{{ route('home') }}" class="button button-secondary">← Back to home</a>
                        <a href="{{ route('bookings.lookup') }}" class="button button-primary">Check an existing booking</a>
                    </div>
                </div>
            </div>
        </section>
    @else

    <section class="band band--cream booking-hero">
        <div class="band-inner">
            <div class="eyebrow">Book a lab</div>
            <h1 class="title">
                {{ $type === 'equipment' ? 'Research & Development lab' : ($type === 'csl' ? 'CSL lab (Clinical Skills)' : 'Pharma lab (Pharmaceutical)') }}
            </h1>
            <p class="lede" style="margin-top:14px; max-width:62ch;">Fields adapt to the lab type. All bookings start as pending and require admin approval.</p>

            <nav class="type-tabs">
                <a href="{{ route('booking.create', ['type' => 'equipment']) }}" class="{{ $type === 'equipment' ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10 3h4M10 3v6l-5.5 9.5A1.8 1.8 0 0 0 6.1 21h11.8a1.8 1.8 0 0 0 1.6-2.7L14 9V3"/></svg>
                    Research &amp; Development
                </a>
                <a href="{{ route('booking.create', ['type' => 'csl']) }}" class="{{ $type === 'csl' ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 2h6M10 2v6.5L4.8 18a1.6 1.6 0 0 0 1.4 2.4h11.6A1.6 1.6 0 0 0 19.2 18L14 8.5V2"/></svg>
                    CSL labs
                </a>
                <a href="{{ route('booking.create', ['type' => 'pharma']) }}" class="{{ $type === 'pharma' ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2.5" y="8.5" width="19" height="7" rx="3.5" transform="rotate(-45 12 12)"/></svg>
                    Pharma labs
                </a>
            </nav>
        </div>
    </section>

    @if ($errors->any())
        <section class="band band--cream" style="padding-top:20px;">
            <div class="band-inner">
                <div class="card empty" style="border-left:4px solid #a03027; color:#a03027; text-align:left; padding:14px 16px;">
                    <strong>Please fix the following:</strong>
                    <ul style="margin:8px 0 0; padding-left:20px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </section>
    @endif

    <section class="band band--tint step-rail-band">
        <div class="band-inner">
            <div class="step-rail" id="step-bar">
                <div class="step-item active" data-step="1">
                    <span class="step-circle">1</span>
                    <span class="step-item-label">Date, Time &amp; Lab</span>
                </div>
                <div class="step-line"></div>
                <div class="step-item" data-step="2">
                    <span class="step-circle">2</span>
                    <span class="step-item-label">Your Details</span>
                </div>
                <div class="step-line"></div>
                <div class="step-item" data-step="3">
                    <span class="step-circle">3</span>
                    <span class="step-item-label">Review &amp; Submit</span>
                </div>
            </div>
        </div>
    </section>

    <section class="band band--cream booking-band">
        <div class="band-inner">
            <div class="booking-layout">
                <div class="booking-main">

                <form method="POST" action="{{ route('booking.store') }}" style="display:grid; gap:20px;" id="booking-form">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">

                @if ($type === 'pharma')
                    <section class="card tc-gate" id="pharma-tc-section">
                        <h3 style="margin-bottom:10px;">⚠ Pharmaceutical Lab — Terms &amp; Conditions</h3>
                        <p>Pharma lab bookings are restricted to <strong>{{ implode(', ', config('booking.pharma_allowed_roles')) }}</strong> (staff email only). Please read and accept before continuing:</p>
                        <ul>
                            <li>Bookings are available on <strong>weekdays {{ $pharmaRules['weekday_start'] }}–{{ $pharmaRules['weekday_end'] }}</strong> and <strong>weekends {{ $pharmaRules['weekend_start'] }}–{{ $pharmaRules['weekend_end'] }}</strong>.</li>
                            <li>The lab must be left clean and in the same condition as found. Any damage must be reported immediately.</li>
                            <li>All chemicals must be stored appropriately. Hazardous waste must be disposed of per RCMP guidelines.</li>
                            <li>Students must be supervised at all times. The applicant is responsible for all students during the session.</li>
                            <li>Unauthorised use of equipment outside the booked time slot is strictly prohibited.</li>
                            <li>Bookings must be cancelled at least <strong>24 hours</strong> in advance if not proceeding.</li>
                            <li>Repeated no-shows may result in suspension of booking privileges.</li>
                            <li>RCMP reserves the right to cancel any booking due to maintenance or unforeseen circumstances.</li>
                            <li>1 piece of equipment can only be booked by 1 person per time slot.</li>
                        </ul>
                        <label class="accept-row">
                            <input type="checkbox" id="pharma-tc-gate-check" name="pharma_tc_accepted" value="1" required>
                            I have read and accept the Pharma Lab Terms &amp; Conditions
                        </label>
                    </section>
                @endif

                <fieldset id="pharma-gate" style="border:none; padding:0; margin:0; display:grid; gap:20px;" @if ($type === 'pharma') disabled @endif>

                <div id="step-1">

                    <section class="card section-card">
                        <div class="section-heading"><span class="section-num">1</span><h3>Date & Time</h3></div>
                        @if ($type === 'equipment')
                            <ul class="muted" style="margin:0 0 12px; padding-left:20px; display:grid; gap:4px;">
                                <li><strong>Operating hours:</strong> {{ $researchRules['weekday_start'] }}–{{ $researchRules['weekday_end'] }}, any day of the week.</li>
                                <li><strong>Weekends:</strong> Most rooms open on weekends, but a few are weekday-only — those are greyed out once you pick a Saturday or Sunday.</li>
                                <li><strong>Equipment availability:</strong> Some equipment has additional operating restrictions (e.g., Monday–Thursday only). Please refer to the equipment notes before booking.</li>
                                <li><strong>Eligible users:</strong> Postgraduate students and lecturers only.</li>
                            </ul>
                        @elseif ($type === 'pharma')
                            <p class="muted" style="margin-bottom:12px;">Pharma weekday slots: <strong>{{ $pharmaRules['weekday_start'] }}–{{ $pharmaRules['weekday_end'] }}</strong>. Weekend slots: <strong>{{ $pharmaRules['weekend_start'] }}–{{ $pharmaRules['weekend_end'] }}</strong>.</p>
                        @elseif ($type === 'csl')
                            <p class="muted" style="margin-bottom:12px;">CSL bookings are available on <strong>weekdays only</strong> and must be made at least <strong>{{ $cslRules['advance_working_days'] }} working day(s) in advance</strong>. A <strong>{{ $cslRules['buffer_minutes'] }}-minute buffer</strong> is applied between sessions in the same room ({{ $cslRules['day_start'] }}–{{ $cslRules['day_end'] }}).</p>
                            <p class="muted" style="margin-bottom:12px;"><strong>Next week's bookings are open from Thursday onwards.</strong> Same-week or last-minute requests may be reassigned to another available CSL or rejected by the administrator, as priority will always be given to scheduled classes.</p>
                        @endif
                        <label class="toggle-card" style="margin-bottom:14px;">
                            <input type="checkbox" id="multi-day-toggle" @checked(old('booking_date_to'))>
                            Extended Booking (multiple days)
                        </label>
                        @php
                            $rangeStart = match ($type) {
                                'equipment' => $researchRules['weekday_start'],
                                'pharma' => min($pharmaRules['weekday_start'], $pharmaRules['weekend_start']),
                                default => $cslRules['day_start'],
                            };
                            $rangeEnd = match ($type) {
                                'equipment' => $researchRules['weekday_end'],
                                'pharma' => max($pharmaRules['weekday_end'], $pharmaRules['weekend_end']),
                                default => $cslRules['day_end'],
                            };
                            $startMinutes = (int) substr($rangeStart, 0, 2) * 60 + (int) substr($rangeStart, 3, 2);
                            $endMinutes = (int) substr($rangeEnd, 0, 2) * 60 + (int) substr($rangeEnd, 3, 2);
                            $timeSlots = [];
                            for ($m = $startMinutes; $m <= $endMinutes; $m += 30) {
                                $timeSlots[] = sprintf('%02d:%02d', intdiv($m, 60), $m % 60);
                            }
                        @endphp
                        <div class="grid-3" style="display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px;">
                            <label class="field">
                                <span class="muted">Date from<span class="req">*</span></span>
                                <input type="date" name="booking_date_from" value="{{ old('booking_date_from') }}" required>
                            </label>
                            <div class="field" id="date-to-field" style="{{ old('booking_date_to') ? '' : 'display:none;' }}">
                                <span class="muted">Date to<span class="req">*</span></span>
                                <input type="date" name="booking_date_to" value="{{ old('booking_date_to') }}">
                            </div>
                        </div>
                        <div class="grid-3" style="display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; margin-top:12px;">
                            <label class="field">
                                <span class="muted">Start time<span class="req">*</span></span>
                                <select name="start_time" required>
                                    <option value="">— Select —</option>
                                    @foreach ($timeSlots as $slot)
                                        <option value="{{ $slot }}" @selected(old('start_time') === $slot)>{{ $slot }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="field">
                                <span class="muted">End time<span class="req">*</span></span>
                                <select name="end_time" required>
                                    <option value="">— Select —</option>
                                    @foreach ($timeSlots as $slot)
                                        <option value="{{ $slot }}" @selected(old('end_time') === $slot)>{{ $slot }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="field">
                                <span class="muted">Duration</span>
                                <input type="text" id="duration-display" value="—" readonly tabindex="-1" aria-readonly="true">
                            </label>
                        </div>

                        <div id="schedule-alert" class="review-banner" style="display:none; margin-top:14px; background:rgba(212,52,42,.1); border-color:#c0392b; color:#7a2018;"></div>
                    </section>

                    @if ($type === 'pharma')
                        <section class="card section-card">
                            <div class="section-heading"><span class="section-num">2</span><h3>Rooms &amp; equipment</h3></div>

                            <label class="field" style="max-width:220px;">
                                <span class="muted">Number of students<span class="req">*</span></span>
                                <input type="number" id="pax-count" name="pax_count" min="1" max="{{ $paxMax }}" value="{{ old('pax_count', 1) }}" required>
                            </label>
                            <div class="pax-rows" id="pax-rows" style="margin-top:10px; margin-bottom:24px;"></div>

                            <h4 class="subsection-heading">Choose lab(s)</h4>
                            <p class="muted" style="margin-bottom:14px;">You can select more than one lab. Each lab you use — whether you book the room itself or just its equipment — reserves the same number of students from that lab's own capacity, since equipment can't leave its room. Only labs with enough remaining capacity for the number of students above, at your chosen date/time, can be selected.</p>

                            @php $oldPharmaLabIds = array_map('strval', (array) old('lab_ids', [])); @endphp
                            <div class="room-grid">
                            @forelse ($labs as $lab)
                                <details class="room-group" id="choose-lab-{{ $lab->id }}" data-lab-name="{{ $lab->name }}" @if (in_array((string) $lab->id, $oldPharmaLabIds, true)) open @endif>
                                    <summary class="room-group-label">
                                        <label class="room-check">
                                            <input type="checkbox" name="lab_ids[]" value="{{ $lab->id }}" data-lab-name="{{ $lab->name }}" disabled @checked(in_array((string) $lab->id, $oldPharmaLabIds, true))>
                                            <span class="room-check-text">
                                                <span class="name">{{ $lab->name }}</span>
                                                <span class="room-tile-badges">
                                                    <span class="badge">Capacity {{ $lab->capacity }}</span>
                                                    <span class="badge" id="pharma-status-{{ $lab->id }}" style="background:rgba(49, 43, 44,.07); color:var(--muted);">Fill in schedule &amp; students first</span>
                                                </span>
                                            </span>
                                        </label>
                                        @if ($lab->equipment->isNotEmpty())
                                            <span class="room-more-toggle">
                                                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                                                {{ $lab->equipment->count() }} equipment option{{ $lab->equipment->count() === 1 ? '' : 's' }}
                                            </span>
                                        @endif
                                    </summary>
                                    <div class="room-group-body">
                                        @if ($lab->equipment->isNotEmpty())
                                            <div class="equip-list">
                                                @foreach ($lab->equipment as $equipment)
                                                    <label>
                                                        <input type="checkbox" name="equipment_names[]" value="{{ $lab->id }}::{{ $equipment->equipment_name }}">
                                                        <span>
                                                            <span data-equip-name>{{ $equipment->equipment_name }}</span>
                                                            @if ($equipment->special_conditions_note)
                                                                <span style="display:block; font-size:0.76rem; color:#a0791f; font-weight:700;">⚠ {{ $equipment->special_conditions_note }}</span>
                                                            @endif
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="muted" style="margin:0;">No equipment listed for this lab.</p>
                                        @endif
                                    </div>
                                </details>
                            @empty
                                <div class="empty">No active labs available for this type right now.</div>
                            @endforelse
                            </div>

                            <p class="muted" id="pharma-lab-empty" style="display:none; margin-top:12px; color:#a03027; font-weight:700;">⚠ No pharma labs currently have enough remaining capacity for this date/time/pax. Try a different time or reduce the number of students.</p>

                            <div id="rooms-alert" class="review-banner" style="display:none; margin-top:14px; background:rgba(212,52,42,.1); border-color:#c0392b; color:#7a2018;"></div>
                        </section>
                    @endif

                    @if ($type === 'equipment')
                    <section class="card section-card">
                        <div class="section-heading">
                            <span class="section-num">2</span>
                            <h3>Labs &amp; Equipments</h3>
                        </div>

                        <p class="muted" style="margin-bottom:12px;">You can select more than one room within the same building. Equipment-based rooms (e.g. MDL 3) are shared: only the specific equipment you pick becomes unavailable. Room-only rooms (e.g. Cell Culture Room 1) are exclusive: booking one reserves the whole room, with no equipment to pick.</p>
                        <div class="filter-chips" id="building-chips">
                            @foreach ($buildings as $building)
                                <button type="button" class="chip" data-building="{{ $building }}">{{ $building }}</button>
                            @endforeach
                        </div>
                        <label class="field" style="display:none;">
                            <span class="muted">Building</span>
                            <select id="block-filter">
                                @foreach ($buildings as $building)
                                    <option value="{{ $building }}">{{ $building }}</option>
                                @endforeach
                            </select>
                        </label>

                        @php $oldLabIds = array_map('strval', (array) old('lab_ids', [])); @endphp
                            <div class="room-grid">
                            @forelse ($labs as $lab)
                                <details class="room-group" id="choose-room-{{ $lab->id }}" data-block="{{ $lab->building }}" data-lab-name="{{ $lab->name }}" @if (in_array((string) $lab->id, $oldLabIds, true)) open @endif>
                                    <summary class="room-group-label">
                                        <label class="room-check">
                                            <input type="checkbox" name="lab_ids[]" value="{{ $lab->id }}" data-lab-name="{{ $lab->name }}" @checked(in_array((string) $lab->id, $oldLabIds, true))>
                                            <span class="room-check-text">
                                                <span class="name">{{ $lab->name }}</span>
                                                <span class="room-tile-badges">
                                                    @if ($lab->is_room_only)
                                                        <span class="badge badge-strong" style="background:rgba(148, 128, 111,.2); color:#7a5f18;">Room booking only</span>
                                                    @endif
                                                    @if ($lab->requires_special_conditions)
                                                        <span class="badge badge-strong" style="background:rgba(212,52,42,.14); color:#a03027;">⚠ Special conditions</span>
                                                    @endif
                                                    <span class="badge" id="research-status-{{ $lab->id }}" style="display:none; background:rgba(212,52,42,.1); color:#a03027;"></span>
                                                </span>
                                            </span>
                                        </label>
                                        @if ($lab->equipment->isNotEmpty())
                                            <span class="room-more-toggle">
                                                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                                                {{ $lab->equipment->count() }} equipment option{{ $lab->equipment->count() === 1 ? '' : 's' }}
                                            </span>
                                        @endif
                                    </summary>
                                    <div class="room-group-body">
                                        @if ($lab->equipment->isNotEmpty())
                                            <div class="equip-list">
                                                @foreach ($lab->equipment as $equipment)
                                                    <label>
                                                        <input type="checkbox" name="equipment_names[]" value="{{ $lab->id }}::{{ $equipment->equipment_name }}">
                                                        <span>
                                                            <span data-equip-name>{{ $equipment->equipment_name }}</span>
                                                            @if ($equipment->special_conditions_note)
                                                                <span style="display:block; font-size:0.76rem; color:#a0791f; font-weight:700;">⚠ {{ $equipment->special_conditions_note }}</span>
                                                            @endif
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="muted" style="margin:0;">This room has no separate equipment — booking it reserves the whole room.</p>
                                        @endif
                                    </div>
                                </details>
                            @empty
                                <div class="empty">No active labs available for this type right now.</div>
                            @endforelse
                            </div>

                        <div id="rooms-alert" class="review-banner" style="display:none; margin-top:14px; background:rgba(212,52,42,.1); border-color:#c0392b; color:#7a2018;"></div>
                    </section>
                    @endif

                    @if ($type === 'csl')
                        {{-- CSL session details and room selection are one flow: the
                             discipline decides which rooms are on offer, so both live
                             in a single section (session type → discipline → rooms →
                             group members → procedure). --}}
                        @php $oldLabIds = array_map('strval', (array) old('lab_ids', [])); @endphp
                        <section class="card section-card">
                            <div class="section-heading"><span class="section-num">2</span><h3>CSL session &amp; rooms</h3></div>

                            <div class="grid-3" style="display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px;">
                                <label class="field">
                                    <span class="muted">Session type<span class="req">*</span></span>
                                    <select name="csl_session_type" required>
                                        <option value="">-- Select --</option>
                                        @foreach ($cslSessionTypes as $sessionType)
                                            <option value="{{ $sessionType }}" @selected(old('csl_session_type') === $sessionType)>{{ $sessionType }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="field">
                                    <span class="muted">Discipline<span class="req">*</span></span>
                                    <select id="csl-discipline" name="csl_discipline" required>
                                        <option value="">-- Select --</option>
                                        @foreach ($cslDisciplines as $discipline)
                                            <option value="{{ $discipline }}"
                                                data-package="{{ in_array($discipline, $cslPackageDisciplines, true) ? '1' : '0' }}"
                                                data-lab-ids="{{ $cslDisciplineLabIds->get($discipline, collect())->implode(',') }}"
                                                @selected(old('csl_discipline') === $discipline)>{{ $discipline }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </div>

                            <h4 class="subsection-heading">CSL room selection</h4>
                            <p class="muted" id="csl-room-hint" style="margin-bottom:14px;">Choose a discipline first — the rooms it can be booked in will appear here.</p>
                            <div id="csl-package-note" class="review-banner" style="display:none; margin-bottom:14px;"></div>

                            <div class="csl-room-grid" id="csl-room-grid" style="padding:0; display:none;">
                                @forelse ($labs as $lab)
                                    @php $cslZone = trim(explode(',', (string) $lab->location, 2)[1] ?? ''); @endphp
                                    <label class="csl-room-tile" data-csl-room="{{ $lab->id }}" style="display:none;">
                                        <input type="checkbox" name="lab_ids[]" value="{{ $lab->id }}" data-lab-name="{{ $lab->name }}" @checked(in_array((string) $lab->id, $oldLabIds, true))>
                                        <span class="tile-text">
                                            <span class="name">{{ $lab->name }}</span>
                                            @if ($cslZone)
                                                <span class="note">{{ $cslZone }}</span>
                                            @endif
                                            @if ($lab->notes)
                                                <span class="note">{{ $lab->notes }}</span>
                                            @endif
                                        </span>
                                    </label>
                                @empty
                                    <div class="empty">No active labs available for this type right now.</div>
                                @endforelse
                            </div>

                            <h4 class="subsection-heading">Group members</h4>
                            <label class="toggle-card">
                                <input type="checkbox" id="more-pax-toggle" name="more_pax" value="1" @checked(old('more_pax'))>
                                Group Members
                            </label>
                            <div id="pax-fields" style="display:{{ old('more_pax') ? 'block' : 'none' }}; margin-top:14px;">
                                <label class="field" style="max-width:220px;">
                                    <span class="muted">Number of group members</span>
                                    <input type="number" id="pax-count" name="pax_count" min="1" max="{{ $paxMax }}" value="{{ old('pax_count', 1) }}">
                                </label>
                                <div class="pax-rows" id="pax-rows"></div>
                            </div>

                            <h4 class="subsection-heading">Procedure</h4>
                            <label class="field">
                                <span class="muted">Procedure / activity for this session<span class="req">*</span></span>
                                <textarea name="csl_procedure" rows="4" maxlength="2000" placeholder="One procedure per line, e.g.&#10;- NG tube insertion&#10;- PR examination&#10;- Wound dressing" required>{{ old('csl_procedure') }}</textarea>
                                <span class="muted" style="font-size:0.78rem;">List every procedure to be covered — this is what the lab staff prepare the room for.</span>
                            </label>

                            <div id="rooms-alert" class="review-banner" style="display:none; margin-top:14px; background:rgba(212,52,42,.1); border-color:#c0392b; color:#7a2018;"></div>
                        </section>
                    @endif

                    <div style="display:flex; justify-content:flex-end; margin-top:4px;">
                        <button type="button" id="to-step2-btn" class="button button-primary">Next: Your details →</button>
                    </div>
                </div>

                <div id="step-2" style="display:none;">

                    <section class="card section-card">
                        <div class="section-heading"><span class="section-num">1</span><h3>Applicant details</h3></div>

                        <div class="field" style="margin-bottom:14px;">
                            <span class="muted">Subject / Purpose<span class="req">*</span></span>
                            <textarea name="purpose" rows="3" required>{{ old('purpose') }}</textarea>
                        </div>

                        <div class="grid-3" style="display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px;">
                            <label class="field">
                                <span class="muted">Full name<span class="req">*</span></span>
                                <input type="text" name="applicant_name" value="{{ old('applicant_name') }}" required>
                            </label>
                            <label class="field">
                                <span class="muted">Email<span class="req">*</span></span>
                                <input type="email" id="applicant_email" name="applicant_email" value="{{ old('applicant_email') }}" placeholder="{{ '@' . $staffEmailDomain }} or {{ '@' . $studentEmailDomain }}" required>
                            </label>
                            <label class="field">
                                <span class="muted" id="id-label">Staff / Student ID<span class="req">*</span></span>
                                <input type="text" name="applicant_id" value="{{ old('applicant_id') }}" required>
                            </label>
                            <label class="field">
                                <span class="muted">Phone number</span>
                                <input type="text" name="applicant_phone" value="{{ old('applicant_phone') }}"
                                       {{-- maxlength leaves room for separators in a pasted number; the
                                            input handler below strips them and caps the result at 11 digits. --}}
                                       inputmode="numeric" pattern="[0-9]{10,11}" minlength="10" maxlength="20"
                                       placeholder="e.g. 01114354678"
                                       title="Digits only, 10 or 11 numbers (e.g. 01114354678)">
                            </label>
                            <label class="field">
                                <span class="muted">Department / Programme</span>
                                <input type="text" name="applicant_department" value="{{ old('applicant_department') }}">
                            </label>
                            <label class="field">
                                <span class="muted">Role<span class="req">*</span></span>
                                <select id="applicant_role" name="applicant_role" required>
                                    <option value="">-- Select --</option>
                                    @if ($type === 'equipment')
                                        @foreach (config('booking.research_allowed_roles') as $role)
                                            <option value="{{ $role }}" data-category="{{ in_array($role, $studentRoles, true) ? 'student' : 'staff' }}" @selected(old('applicant_role') === $role)>{{ $role }}</option>
                                        @endforeach
                                    @elseif ($type === 'pharma')
                                        @foreach (config('booking.pharma_allowed_roles') as $role)
                                            <option value="{{ $role }}" data-category="staff" @selected(old('applicant_role') === $role)>{{ $role }}</option>
                                        @endforeach
                                    @else
                                        @foreach ($staffRoles as $role)
                                            <option value="{{ $role }}" data-category="staff" @selected(old('applicant_role') === $role)>{{ $role }}</option>
                                        @endforeach
                                        @foreach ($studentRoles as $role)
                                            <option value="{{ $role }}" data-category="student" @selected(old('applicant_role') === $role)>{{ $role }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </label>
                            @if ($type === 'csl')
                                <label class="field">
                                    <span class="muted">Group</span>
                                    <input type="text" name="applicant_group" maxlength="30" value="{{ old('applicant_group') }}" placeholder="e.g. 4A">
                                </label>
                            @endif
                        </div>

                        @if ($type === 'equipment')
                            <label class="toggle-card" style="margin-top:14px;">
                                <input type="checkbox" id="more-pax-toggle" name="more_pax" value="1">
                                Group Members (if applicable)
                            </label>
                            <div id="pax-fields" style="display:none; margin-top:14px;">
                                <label class="field" style="max-width:220px;">
                                    <span class="muted">Number of members</span>
                                    <input type="number" id="pax-count" name="pax_count" min="1" max="{{ $paxMax }}" value="{{ old('pax_count', 1) }}">
                                </label>
                                <div class="pax-rows" id="pax-rows"></div>
                            </div>
                        @endif

                        <div class="field" style="margin-top:14px;">
                            <span class="muted">Remark (optional)</span>
                            <textarea name="applicant_remark" rows="2">{{ old('applicant_remark') }}</textarea>
                        </div>
                    </section>

                    @if ($type === 'pharma')
                        <div id="pharma-denied" class="card empty" style="display:none; padding:22px; text-align:left; border-left:4px solid #a03027; background:rgba(212,52,42,.06);">
                            <strong style="color:#a03027; font-size:1.05rem;">🚫 Pharma Lab access is restricted to staff</strong>
                            <p class="muted" style="margin-top:8px;">
                                Pharma Lab bookings can only be made by {{ implode(', ', config('booking.pharma_allowed_roles')) }} using a staff email ({{ '@' . $staffEmailDomain }}).
                                It looks like you entered a student email — please correct it above, or use the <a href="{{ route('booking.create', ['type' => 'equipment']) }}">Research &amp; Development lab</a> or <a href="{{ route('booking.create', ['type' => 'csl']) }}">CSL lab</a> booking form instead.
                            </p>
                        </div>
                    @endif

                    <div style="display:flex; justify-content:space-between; margin-top:4px;">
                        <button type="button" id="back-to-step1-btn" class="button button-secondary">← Back</button>
                        <button type="button" id="to-review-btn" class="button button-primary">Review booking →</button>
                    </div>
                </div>

                </fieldset>

                <div id="step-3" style="display:none;">
                    <div class="review-banner">
                        ⚠ Please check every detail carefully. Once submitted, your booking will be sent for admin approval.
                    </div>

                    <section class="card section-card">
                        <div class="section-heading"><h3 style="margin:0;">Review your booking</h3></div>

                        <div class="review-section">
                            <h4>Applicant</h4>
                            <div class="review-grid">
                                <div><span class="review-label">Subject / Purpose</span><span class="review-value" data-review="purpose"></span></div>
                                <div><span class="review-label">Full name</span><span class="review-value" data-review="applicant_name"></span></div>
                                <div><span class="review-label">Email</span><span class="review-value" data-review="applicant_email"></span></div>
                                <div><span class="review-label" id="review-id-label">Staff / Student ID</span><span class="review-value" data-review="applicant_id"></span></div>
                                <div><span class="review-label">Phone number</span><span class="review-value" data-review="applicant_phone"></span></div>
                                <div><span class="review-label">Department / Programme</span><span class="review-value" data-review="applicant_department"></span></div>
                                <div><span class="review-label">Role</span><span class="review-value" data-review="applicant_role"></span></div>
                                @if ($type === 'csl')
                                    <div><span class="review-label">Group</span><span class="review-value" data-review="applicant_group"></span></div>
                                @endif
                                <div><span class="review-label">Remark</span><span class="review-value" data-review="applicant_remark"></span></div>
                            </div>
                        </div>

                        <div class="review-section">
                            <h4>Schedule</h4>
                            <div class="review-grid">
                                <div><span class="review-label">Date</span><span class="review-value" data-review="dates"></span></div>
                                <div><span class="review-label">Time</span><span class="review-value" data-review="times"></span></div>
                            </div>
                        </div>

                        @if (in_array($type, ['equipment', 'pharma'], true))
                            <div class="review-section">
                                <h4>Rooms &amp; Equipment</h4>
                                <div id="review-rooms-equipment"></div>
                            </div>
                        @else
                            <div class="review-section">
                                <h4>Rooms</h4>
                                <div id="review-rooms" class="review-tags"></div>
                            </div>

                            @if ($type !== 'csl')
                                <div class="review-section">
                                    <h4>Equipment</h4>
                                    <div id="review-equipment" class="review-tags"></div>
                                </div>
                            @endif
                        @endif

                        @if ($type === 'csl')
                            <div class="review-section">
                                <h4>Session details</h4>
                                <div class="review-grid">
                                    <div><span class="review-label">Session type</span><span class="review-value" data-review="csl_session_type"></span></div>
                                    <div><span class="review-label">Discipline</span><span class="review-value" data-review="csl_discipline"></span></div>
                                    <div><span class="review-label">Procedure</span><span class="review-value" data-review="csl_procedure" style="white-space:pre-line;"></span></div>
                                </div>
                            </div>
                        @endif

                        @if ($type === 'pharma')
                            <div class="review-section">
                                <h4>Pharma details</h4>
                                <div class="review-grid">
                                    <div><span class="review-label">Number of students</span><span class="review-value" data-review="pax_count"></span></div>
                                </div>
                            </div>
                        @endif

                        @if (in_array($type, ['equipment', 'csl', 'pharma'], true))
                            <div class="review-section">
                                <h4>{{ $type === 'pharma' ? 'Students' : ($type === 'csl' ? 'Group members' : 'Additional pax') }}</h4>
                                <p class="review-value" id="review-pax-empty">Just the applicant — no additional pax.</p>
                                <ul id="review-pax" style="margin:0; padding-left:20px; display:grid; gap:6px;"></ul>
                            </div>
                        @endif
                    </section>

                    <div style="display:flex; justify-content:space-between; margin-top:16px;">
                        <button type="button" id="back-to-edit-btn" class="button button-secondary">← Back to edit</button>
                        <button type="submit" id="submit-booking-btn" class="button button-primary">Confirm &amp; submit booking</button>
                    </div>
                </div>
                </form>

                </div>

                <aside class="booking-ticket" id="booking-ticket">
                    <div class="ticket-head">Your selection</div>
                    <dl class="ticket-rows">
                        <div class="ticket-row"><dt>Lab / Room</dt><dd id="ticket-lab">—</dd></div>
                        <div class="ticket-row"><dt>Date</dt><dd id="ticket-date">—</dd></div>
                        <div class="ticket-row"><dt>Time</dt><dd id="ticket-time">—</dd></div>
                        <div class="ticket-row"><dt>Duration</dt><dd id="ticket-duration">—</dd></div>
                    </dl>
                </aside>
            </div>
        </div>
    </section>

    <template id="pax-row-template">
        <div class="pax-row">
            <input type="text" placeholder="Name" data-pax-name>
            <input type="text" placeholder="Staff / Student ID" data-pax-id>
        </div>
    </template>

    <script>
        (function () {
            const bookingType = @json($type);
            const form = document.getElementById('booking-form');
            const staffDomain = @json($staffEmailDomain);
            const studentDomain = @json($studentEmailDomain);
            const emailInput = document.getElementById('applicant_email');
            const idLabel = document.getElementById('id-label');
            const roleSelect = document.getElementById('applicant_role');

            function detectCategory(email) {
                const domain = (email.split('@')[1] || '').toLowerCase().trim();
                if (domain === studentDomain) return 'student';
                if (domain === staffDomain) return 'staff';
                return null;
            }

            // Pharma: live access gate. Students can't book this lab — as soon
            // as a student email is detected, show an access-denied notice
            // right in "Your details" (own fields stay editable so a mistyped
            // email can be fixed) and block advancing to Review from there.
            const pharmaDenied = document.getElementById('pharma-denied');

            function applyCategory() {
                const category = detectCategory(emailInput.value);
                const label = category === 'staff' ? 'Staff ID' : category === 'student' ? 'Student ID' : 'Staff / Student ID';
                idLabel.childNodes[0].textContent = label;
                const reviewIdLabel = document.getElementById('review-id-label');
                if (reviewIdLabel) reviewIdLabel.textContent = label;

                if (bookingType === 'pharma' && pharmaDenied) {
                    pharmaDenied.style.display = category === 'student' ? 'block' : 'none';
                }

                if (!roleSelect) return;
                let currentValid = false;
                Array.from(roleSelect.options).forEach((opt) => {
                    if (!opt.value) { return; }
                    const matches = !category || opt.dataset.category === category;
                    opt.hidden = !matches;
                    if (opt.selected && matches) currentValid = true;
                });
                if (!currentValid) roleSelect.value = '';
            }

            if (emailInput) {
                emailInput.addEventListener('input', applyCategory);
                applyCategory();
            }

            // Phone is digits only — strip anything pasted or typed in between
            // (spaces, dashes, "+60") rather than failing validation on submit.
            const phoneInput = form.elements['applicant_phone'];
            if (phoneInput) {
                phoneInput.addEventListener('input', () => {
                    const digits = phoneInput.value.replace(/\D/g, '').slice(0, 11);
                    if (digits !== phoneInput.value) phoneInput.value = digits;
                });
            }

            // Equipment: building filter narrows "Labs & Equipments" to the
            // chosen building. Each room's own equipment is already nested
            // inline under it, so hiding a room for a building mismatch must
            // also clear any equipment ticked inside it — otherwise a hidden
            // (but still checked) item from the other building would still
            // be submitted, silently mixing buildings in one booking. Rooms
            // are checkboxes (not radios) — more than one room can be booked
            // together, as long as they're all in the same building.
            const blockFilter = document.getElementById('block-filter');
            const chooseRoomGroups = document.querySelectorAll('[id^="choose-room-"]');

            function applyBlockFilter() {
                const value = blockFilter?.value;

                chooseRoomGroups.forEach((group) => {
                    group.style.display = (!value || group.dataset.block === value) ? '' : 'none';
                    if (group.style.display === 'none') {
                        const roomCheckbox = group.querySelector('input[name="lab_ids[]"]');
                        if (roomCheckbox?.checked) { roomCheckbox.checked = false; roomCheckbox.dispatchEvent(new Event('change', { bubbles: true })); }
                        group.querySelectorAll('input[name="equipment_names[]"]:checked').forEach((cb) => {
                            cb.checked = false;
                            cb.dispatchEvent(new Event('change', { bubbles: true }));
                        });
                    }
                });
            }

            if (blockFilter && chooseRoomGroups.length) {
                blockFilter.addEventListener('change', applyBlockFilter);
                applyBlockFilter();
            }

            // Equipment can't leave its room, so the two stay in sync both
            // ways: ticking an equipment item auto-selects its room (same as
            // Pharma), and un-selecting a room clears whatever equipment was
            // ticked inside it.
            document.querySelectorAll('[id^="choose-room-"] input[name="lab_ids[]"]').forEach((roomCheckbox) => {
                roomCheckbox.addEventListener('change', () => {
                    roomCheckbox.closest('details').open = true;
                    if (!roomCheckbox.checked) {
                        roomCheckbox.closest('details').querySelectorAll('input[name="equipment_names[]"]:checked').forEach((cb) => {
                            cb.checked = false;
                            cb.dispatchEvent(new Event('change', { bubbles: true }));
                        });
                    }
                    scheduleAvailabilityCheck();
                });
            });

            document.querySelectorAll('[id^="choose-room-"] input[name="equipment_names[]"]').forEach((equipCheckbox) => {
                equipCheckbox.addEventListener('change', () => {
                    if (!equipCheckbox.checked) return;
                    const roomCheckbox = equipCheckbox.closest('details')?.querySelector('input[name="lab_ids[]"]');
                    if (roomCheckbox && !roomCheckbox.checked && !roomCheckbox.disabled) {
                        roomCheckbox.checked = true;
                        roomCheckbox.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                });
            });

            // Pharma: labs are checkboxes too (mirroring Equipment's multi-room
            // model) — more than one lab can be booked at once. Each starts
            // disabled until schedule + student count are filled; a live fetch
            // then enables only labs with enough remaining capacity for the
            // headcount, showing the rest as unavailable. Equipment can't
            // leave its room, so ticking an equipment checkbox auto-selects
            // its lab too (reserving that lab's capacity), and un-selecting a
            // lab clears whatever equipment was ticked inside it.
            const pharmaLabEmpty = document.getElementById('pharma-lab-empty');
            const pharmaLabCheckboxes = document.querySelectorAll('[id^="choose-lab-"] input[name="lab_ids[]"]');

            pharmaLabCheckboxes.forEach((checkbox) => {
                checkbox.addEventListener('change', () => {
                    checkbox.closest('details').open = true;
                    if (!checkbox.checked) {
                        checkbox.closest('details').querySelectorAll('input[name="equipment_names[]"]:checked').forEach((cb) => {
                            cb.checked = false;
                            cb.dispatchEvent(new Event('change', { bubbles: true }));
                        });
                    }
                    scheduleAvailabilityCheck();
                });
            });

            document.querySelectorAll('[id^="choose-lab-"] input[name="equipment_names[]"]').forEach((equipCheckbox) => {
                equipCheckbox.addEventListener('change', () => {
                    if (!equipCheckbox.checked) return;
                    const roomCheckbox = equipCheckbox.closest('details')?.querySelector('input[name="lab_ids[]"]');
                    if (roomCheckbox && !roomCheckbox.checked && !roomCheckbox.disabled) {
                        roomCheckbox.checked = true;
                        roomCheckbox.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                });
            });

            let pharmaLabsTimer = null;

            function schedulePharmaLabsFetch() {
                if (!pharmaLabCheckboxes.length) return;
                clearTimeout(pharmaLabsTimer);
                pharmaLabsTimer = setTimeout(fetchPharmaLabs, 400);
            }

            function setPharmaLabStatus(labId, text, eligible) {
                const badge = document.getElementById('pharma-status-' + labId);
                if (!badge) return;
                badge.textContent = text;
                badge.style.background = eligible ? 'rgba(125, 145, 148,.12)' : 'rgba(212,52,42,.1)';
                badge.style.color = eligible ? 'var(--brand-2)' : '#a03027';
            }

            async function fetchPharmaLabs() {
                const dateFrom = textVal('booking_date_from');
                const dateTo = textVal('booking_date_to');
                const startTime = textVal('start_time');
                const endTime = textVal('end_time');
                const paxCount = parseInt(textVal('pax_count'), 10) || 0;

                if (!dateFrom || !startTime || !endTime || paxCount < 1) {
                    pharmaLabCheckboxes.forEach((checkbox) => {
                        checkbox.disabled = true;
                        setPharmaLabStatus(checkbox.value, 'Fill in schedule & students first', false);
                        checkbox.closest('details')?.querySelectorAll('input[name="equipment_names[]"]').forEach((cb) => {
                            cb.disabled = true;
                            cb.closest('label')?.classList.remove('equip-booked');
                        });
                    });
                    if (pharmaLabEmpty) pharmaLabEmpty.style.display = 'none';
                    return;
                }

                try {
                    const token = form.querySelector('input[name="_token"]').value;
                    const res = await fetch(@json(route('booking.pharma-labs')), {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                        body: JSON.stringify({ booking_date_from: dateFrom, booking_date_to: dateTo || dateFrom, start_time: startTime, end_time: endTime, pax_count: paxCount }),
                    });
                    const json = await res.json();
                    const byLabId = new Map((json.labs || []).map((lab) => [String(lab.id), lab]));
                    let anyEligible = false;

                    pharmaLabCheckboxes.forEach((checkbox) => {
                        const lab = byLabId.get(checkbox.value);
                        const eligible = !!lab?.eligible;
                        checkbox.disabled = !eligible;
                        if (eligible) anyEligible = true;

                        if (lab?.fully_equipped) {
                            setPharmaLabStatus(checkbox.value, 'Fully equipped', false);
                        } else if (lab) {
                            setPharmaLabStatus(checkbox.value, lab.remaining !== null ? 'Remaining capacity: ' + lab.remaining : 'Available', eligible);
                        } else {
                            setPharmaLabStatus(checkbox.value, 'Not enough remaining capacity', false);
                        }

                        if (!eligible && checkbox.checked) {
                            checkbox.checked = false;
                            checkbox.dispatchEvent(new Event('change', { bubbles: true }));
                        }

                        // Equipment is disabled if this specific item is already
                        // booked by an overlapping booking (same "1 item = 1
                        // booking at a time" rule as Equipment/CSL), or if the
                        // lab itself has no remaining capacity — equipment
                        // can't leave its room, so a full room blocks its
                        // equipment too.
                        const bookedNames = new Set((lab?.equipment || []).filter((e) => e.booked).map((e) => e.equipment_name));
                        checkbox.closest('details')?.querySelectorAll('input[name="equipment_names[]"]').forEach((cb) => {
                            const equipmentName = cb.value.slice(cb.value.indexOf('::') + 2);
                            const isBooked = bookedNames.has(equipmentName);
                            cb.disabled = isBooked || !eligible;
                            cb.closest('label')?.classList.toggle('equip-booked', isBooked);
                            if (cb.disabled && cb.checked) { cb.checked = false; cb.dispatchEvent(new Event('change', { bubbles: true })); }
                        });
                    });

                    if (pharmaLabEmpty) pharmaLabEmpty.style.display = anyEligible ? 'none' : 'block';
                } catch (e) {
                    // Network hiccup — leave checkboxes as-is; server-side still validates on submit.
                }
            }

            if (bookingType === 'pharma') {
                ['booking_date_from', 'booking_date_to', 'start_time', 'end_time', 'pax_count'].forEach((name) => {
                    const el = form.elements[name];
                    if (el) el.addEventListener('input', schedulePharmaLabsFetch);
                });
            }

            // Equipment: live availability snapshot for every active research
            // lab — grey out equipment/rooms already booked for the chosen
            // date/time, same live pattern as Pharma above. Runs across every
            // building regardless of the current pill filter, so switching
            // buildings never needs a re-fetch.
            let equipmentAvailabilityTimer = null;

            function scheduleEquipmentAvailabilityFetch() {
                if (bookingType !== 'equipment') return;
                clearTimeout(equipmentAvailabilityTimer);
                equipmentAvailabilityTimer = setTimeout(fetchEquipmentAvailability, 400);
            }

            function setResearchLabStatus(labId, text) {
                const badge = document.getElementById('research-status-' + labId);
                if (!badge) return;
                badge.textContent = text || '';
                badge.style.display = text ? 'inline-flex' : 'none';
            }

            function clearEquipmentAvailability() {
                chooseRoomGroups.forEach((group) => {
                    const labId = group.id.replace('choose-room-', '');
                    setResearchLabStatus(labId, '');
                    const roomCheckbox = group.querySelector('input[name="lab_ids[]"]');
                    if (roomCheckbox) roomCheckbox.disabled = false;
                    group.querySelectorAll('input[name="equipment_names[]"]').forEach((cb) => {
                        cb.disabled = false;
                        cb.closest('label')?.classList.remove('equip-booked');
                    });
                });
            }

            async function fetchEquipmentAvailability() {
                const dateFrom = textVal('booking_date_from');
                const startTime = textVal('start_time');
                const endTime = textVal('end_time');

                if (!dateFrom || !startTime || !endTime) {
                    clearEquipmentAvailability();
                    return;
                }

                try {
                    const token = form.querySelector('input[name="_token"]').value;
                    const res = await fetch(@json(route('booking.equipment-availability')), {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                        body: JSON.stringify({ booking_date_from: dateFrom, booking_date_to: textVal('booking_date_to') || dateFrom, start_time: startTime, end_time: endTime }),
                    });
                    const json = await res.json();
                    const byLabId = new Map((json.labs || []).map((lab) => [String(lab.lab_id), lab]));

                    chooseRoomGroups.forEach((group) => {
                        const labId = group.id.replace('choose-room-', '');
                        const lab = byLabId.get(labId);
                        const roomCheckbox = group.querySelector('input[name="lab_ids[]"]');
                        const bookedNames = new Set((lab?.equipment || []).filter((e) => e.booked).map((e) => e.equipment_name));

                        // A weekend-closed room's equipment isn't booked, it's
                        // just unreachable that day — label it as such rather
                        // than claiming someone else took it.
                        const weekendClosed = !!lab?.weekend_blocked;

                        group.querySelectorAll('input[name="equipment_names[]"]').forEach((cb) => {
                            const equipmentName = cb.value.slice(cb.value.indexOf('::') + 2);
                            const isBooked = bookedNames.has(equipmentName);
                            cb.disabled = isBooked || weekendClosed;
                            cb.closest('label')?.classList.toggle('equip-booked', isBooked && !weekendClosed);
                            cb.closest('label')?.classList.toggle('equip-closed', weekendClosed);
                            if (cb.disabled && cb.checked) { cb.checked = false; cb.dispatchEvent(new Event('change', { bubbles: true })); }
                        });

                        const roomBooked = !!lab?.booked;
                        if (roomCheckbox) roomCheckbox.disabled = roomBooked;
                        if (roomBooked && roomCheckbox?.checked) { roomCheckbox.checked = false; roomCheckbox.dispatchEvent(new Event('change', { bubbles: true })); }

                        setResearchLabStatus(labId, lab?.weekend_blocked
                            ? 'Weekdays only'
                            : roomBooked ? (lab.is_room_only ? 'Already booked for this time' : 'Fully equipped') : '');
                    });
                } catch (e) {
                    // Network hiccup — server-side conflict checks still guard submission.
                }
            }

            if (bookingType === 'equipment') {
                ['booking_date_from', 'booking_date_to', 'start_time', 'end_time'].forEach((name) => {
                    const el = form.elements[name];
                    if (el) el.addEventListener('input', scheduleEquipmentAvailabilityFetch);
                });
            }

            // CSL: the discipline decides which rooms may be booked. Package
            // disciplines (Surgical, BCC Surgery, BCC Medicine, IPE) reserve
            // their whole room set at once — every room is ticked and locked,
            // never picked individually. Every other discipline lets the
            // applicant tick any subset of its rooms. Rooms outside the
            // discipline are hidden and always unticked, so a leftover
            // selection from a previous discipline can't be submitted (the
            // server re-checks the same mapping on submit).
            const cslDisciplineSelect = document.getElementById('csl-discipline');
            const cslRoomGrid = document.getElementById('csl-room-grid');
            const cslRoomHint = document.getElementById('csl-room-hint');
            const cslPackageNote = document.getElementById('csl-package-note');

            function applyCslDiscipline({ keepSelection = false, recheck = true } = {}) {
                if (!cslDisciplineSelect || !cslRoomGrid) return;

                const option = cslDisciplineSelect.selectedOptions[0];
                const discipline = cslDisciplineSelect.value;
                const isPackage = option?.dataset.package === '1';
                const allowedIds = new Set((option?.dataset.labIds || '').split(',').filter(Boolean));

                cslRoomGrid.style.display = discipline ? 'grid' : 'none';
                cslRoomHint.textContent = !discipline
                    ? 'Choose a discipline first — the rooms it can be booked in will appear here.'
                    : isPackage
                        ? 'This is a package booking — all rooms below are reserved together.'
                        : 'Select one or more rooms for this session.';

                if (cslPackageNote) {
                    cslPackageNote.style.display = isPackage ? 'flex' : 'none';
                    cslPackageNote.textContent = isPackage
                        ? '📦 ' + discipline + ' is a package-based discipline: every room listed below is booked together and cannot be selected individually.'
                        : '';
                }

                cslRoomGrid.querySelectorAll('[data-csl-room]').forEach((tile) => {
                    const checkbox = tile.querySelector('input[name="lab_ids[]"]');
                    const allowed = discipline && allowedIds.has(checkbox.value);

                    tile.style.display = allowed ? '' : 'none';
                    tile.classList.toggle('csl-room-tile--locked', allowed && isPackage);

                    // Package rooms stay enabled (disabled inputs aren't
                    // submitted) — the lock is visual + pointer-events only.
                    const shouldCheck = allowed && (isPackage || (keepSelection && checkbox.checked));

                    if (checkbox.checked !== shouldCheck) {
                        checkbox.checked = shouldCheck;
                    }
                });

                if (recheck) scheduleAvailabilityCheck();
            }

            if (cslDisciplineSelect) {
                cslDisciplineSelect.addEventListener('change', () => applyCslDiscipline());
                // Package rooms are locked, not disabled, so guard against a
                // stray click (e.g. keyboard) unticking one.
                cslRoomGrid?.addEventListener('change', (e) => {
                    const tile = e.target.closest('.csl-room-tile--locked');
                    if (tile && !e.target.checked) e.target.checked = true;
                });
                // First pass only syncs the UI: scheduleAvailabilityCheck()'s
                // "const" dependencies further down are still in their temporal
                // dead zone here, and the on-load check at the end of this
                // script covers the initial state anyway.
                applyCslDiscipline({ keepSelection: true, recheck: false });
            }

            // Pharma T&C gate: disable the rest of the form until accepted.
            const tcCheck = document.getElementById('pharma-tc-gate-check');
            const gate = document.getElementById('pharma-gate');
            if (tcCheck && gate) {
                tcCheck.addEventListener('change', () => { gate.disabled = !tcCheck.checked; });
            }

            // Schedule: "Date to" only matters for multi-day bookings — keep it
            // hidden (and cleared, so a stale value can't submit) until asked for.
            // Note: only the initial display sync runs immediately here; the
            // schedule/equipment re-checks below are deferred to the "change"
            // listener since scheduleAvailabilityCheck() isn't defined yet at
            // this point in the script (its "const" dependencies further down
            // are still in their temporal dead zone during this first pass).
            const multiDayToggle = document.getElementById('multi-day-toggle');
            const dateToField = document.getElementById('date-to-field');
            if (multiDayToggle && dateToField) {
                dateToField.style.display = multiDayToggle.checked ? 'block' : 'none';
                multiDayToggle.addEventListener('change', () => {
                    dateToField.style.display = multiDayToggle.checked ? 'block' : 'none';
                    if (!multiDayToggle.checked) form.elements['booking_date_to'].value = '';
                    scheduleAvailabilityCheck();
                    scheduleEquipmentAvailabilityFetch();
                });
            }

            // Live availability check: as soon as date/time/room/equipment change,
            // ask the server (min. booking length, CSL advance-notice + room buffer,
            // room-only exclusivity, equipment double-booking) and show an inline
            // alert right in the relevant section, instead of waiting for a submit attempt.
            const scheduleAlert = document.getElementById('schedule-alert');
            const roomsAlert = document.getElementById('rooms-alert');
            let availabilityTimer = null;

            function renderAlert(el, messages) {
                if (!el) return;
                if (messages && messages.length) {
                    el.innerHTML = messages.map((m) => '⚠ ' + escapeHtml(m)).join('<br>');
                    el.style.display = 'flex';
                } else {
                    el.style.display = 'none';
                }
            }

            // Operating-hours window for the current lab type. Used for an
            // INSTANT (no round-trip) client-side check so a wrong start/end
            // time — e.g. picking 08:00–11:00 for a pharma weekday, when only
            // 17:00–21:00 is allowed — surfaces its alert the moment it's
            // picked, not after the whole form is filled and submitted. The
            // server re-validates the same rules on submit (store()); this only
            // mirrors them for immediate feedback. The window is date-aware:
            // pharma weekends run 08:00–17:00 but weekday evenings 17:00–21:00.
            @php
                $hoursRules = match ($type) {
                    'equipment' => $researchRules,
                    'pharma' => $pharmaRules,
                    default => $cslRules,
                };
            @endphp
            const hoursRules = @json($hoursRules);

            function localScheduleMessages() {
                const dateFrom = textVal('booking_date_from');
                const startTime = textVal('start_time');
                const endTime = textVal('end_time');
                if (!dateFrom || !startTime || !endTime) return [];

                if (endTime <= startTime) return ['End time must be after start time.'];

                const day = new Date(dateFrom + 'T00:00:00').getDay();
                const isWeekend = day === 0 || day === 6;
                const messages = [];

                if (bookingType === 'equipment') {
                    if (startTime < hoursRules.weekday_start || endTime > hoursRules.weekday_end) {
                        messages.push('Research & Development lab hours are ' + hoursRules.weekday_start + '–' + hoursRules.weekday_end + '.');
                    }
                } else if (bookingType === 'pharma') {
                    const windowStart = isWeekend ? hoursRules.weekend_start : hoursRules.weekday_start;
                    const windowEnd = isWeekend ? hoursRules.weekend_end : hoursRules.weekday_end;
                    if (startTime < windowStart || endTime > windowEnd) {
                        messages.push('Pharma lab hours are ' + windowStart + '–' + windowEnd + (isWeekend ? ' on weekends.' : ' on weekday evenings.'));
                    }
                } else if (bookingType === 'csl') {
                    if (hoursRules.weekdays_only && isWeekend) {
                        messages.push('CSL bookings are only available on weekdays.');
                    }
                    if (startTime < hoursRules.day_start || endTime > hoursRules.day_end) {
                        messages.push('CSL lab hours are ' + hoursRules.day_start + '–' + hoursRules.day_end + '.');
                    }
                }

                return messages;
            }

            // Latest known result, so the "Review booking" button can gate on it
            // without waiting for the debounce to fire again.
            let latestAvailability = { schedule: [], rooms: [] };

            // Merge the instant local operating-hours messages with the latest
            // server availability messages (deduped) into the schedule alert.
            function renderScheduleAlert() {
                const combined = [...localScheduleMessages(), ...(latestAvailability.schedule || [])];
                renderAlert(scheduleAlert, [...new Set(combined)]);
            }

            function scheduleAvailabilityCheck() {
                if (!scheduleAlert && !roomsAlert) return;
                renderScheduleAlert();
                clearTimeout(availabilityTimer);
                availabilityTimer = setTimeout(runAvailabilityCheck, 300);
            }

            async function runAvailabilityCheck() {
                const dateFrom = textVal('booking_date_from');
                const startTime = textVal('start_time');
                const endTime = textVal('end_time');

                if (!dateFrom || !startTime || !endTime) {
                    latestAvailability = { schedule: [], rooms: [] };
                    renderAlert(scheduleAlert, []);
                    renderAlert(roomsAlert, []);
                    return latestAvailability;
                }

                const payload = {
                    type: bookingType,
                    booking_date_from: dateFrom,
                    booking_date_to: textVal('booking_date_to') || dateFrom,
                    start_time: startTime,
                    end_time: endTime,
                    lab_ids: selectedLabIds(),
                    equipment_names: Array.from(form.querySelectorAll('input[name="equipment_names[]"]:checked')).map((el) => el.value),
                };

                try {
                    const token = form.querySelector('input[name="_token"]').value;
                    const res = await fetch(@json(route('booking.availability')), {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                        body: JSON.stringify(payload),
                    });
                    const json = await res.json();

                    latestAvailability = { schedule: json.schedule || [], rooms: json.rooms || [] };
                    renderScheduleAlert();
                    renderAlert(roomsAlert, latestAvailability.rooms);
                } catch (e) {
                    // Network hiccup — server-side validation still guards submission.
                }

                return latestAvailability;
            }

            // "Real time": react to every keystroke/selection (input), not just
            // on blur (change), so a wrong date/time surfaces its alert immediately.
            ['booking_date_from', 'booking_date_to', 'start_time', 'end_time'].forEach((name) => {
                const el = form.elements[name];
                if (el) el.addEventListener('input', scheduleAvailabilityCheck);
            });
            form.addEventListener('change', (e) => {
                if (e.target.matches('input[name="lab_ids[]"], input[name="equipment_names[]"]')) {
                    scheduleAvailabilityCheck();
                }
            });

            // Pax repeaters (one per "additional members" section, plus the always-on pharma one).
            const template = document.getElementById('pax-row-template');

            function buildPaxRows(countInput, rowsContainer) {
                const count = Math.max(0, parseInt(countInput.value, 10) || 0);
                const existing = Array.from(rowsContainer.children).map((row) => ({
                    name: row.querySelector('[data-pax-name]')?.value ?? '',
                    id: row.querySelector('[data-pax-id]')?.value ?? '',
                }));
                rowsContainer.innerHTML = '';
                for (let i = 0; i < count; i++) {
                    const node = template.content.cloneNode(true);
                    const nameInput = node.querySelector('[data-pax-name]');
                    const idInput = node.querySelector('[data-pax-id]');
                    nameInput.name = 'pax_names[]';
                    idInput.name = 'pax_ids[]';
                    if (existing[i]) {
                        nameInput.value = existing[i].name;
                        idInput.value = existing[i].id;
                    }
                    rowsContainer.appendChild(node);
                }
            }

            document.querySelectorAll('#pax-count').forEach((countInput) => {
                const rowsContainer = countInput.closest('section')?.querySelector('#pax-rows') ?? document.getElementById('pax-rows');
                if (!rowsContainer) return;
                countInput.addEventListener('input', () => buildPaxRows(countInput, rowsContainer));
                buildPaxRows(countInput, rowsContainer);
            });

            document.querySelectorAll('#more-pax-toggle').forEach((toggle) => {
                const section = toggle.closest('section');
                const fields = section?.querySelector('#pax-fields');
                if (!fields) return;
                toggle.addEventListener('change', () => {
                    fields.style.display = toggle.checked ? 'block' : 'none';
                    const countInput = fields.querySelector('#pax-count');
                    const rowsContainer = fields.querySelector('#pax-rows');
                    if (toggle.checked && countInput && rowsContainer) buildPaxRows(countInput, rowsContainer);
                });
            });

            // --- Step navigation: Date & Time + Lab & Equipment -> Your details -> Review & submit ---
            const step1 = document.getElementById('step-1');
            const step2 = document.getElementById('step-2');
            const step3 = document.getElementById('step-3');
            const stepItems = document.querySelectorAll('.step-item');
            const stepLines = document.querySelectorAll('.step-line');
            const toStep2Btn = document.getElementById('to-step2-btn');
            const backToStep1Btn = document.getElementById('back-to-step1-btn');
            const toReviewBtn = document.getElementById('to-review-btn');
            const backBtn = document.getElementById('back-to-edit-btn');

            function setActiveStep(n) {
                stepItems.forEach((el) => {
                    const step = parseInt(el.dataset.step, 10);
                    el.classList.toggle('active', step === n);
                    el.classList.toggle('done', step < n);
                });
                stepLines.forEach((el, i) => {
                    el.classList.toggle('done', n > i + 1);
                });
                // Pharma T&C only needs accepting once, on step 1 — hide the
                // gate section on Your Details / Review & Submit (the checkbox
                // stays checked while hidden, so the gate and submit still pass).
                const tcSection = document.getElementById('pharma-tc-section');
                if (tcSection) tcSection.style.display = (n === 1) ? '' : 'none';
            }

            function escapeHtml(s) {
                return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            }

            // form.reportValidity() checks every control in the whole form,
            // including ones sitting in a later, still-hidden step (being
            // display:none does NOT exempt a required field from constraint
            // validation) — so it always failed on step-2/3's empty required
            // fields even while still on step 1. Scope validation to just the
            // controls inside the step actually being left.
            function reportStepValidity(stepEl) {
                for (const el of stepEl.querySelectorAll('input, select, textarea')) {
                    if (!el.checkValidity()) {
                        el.reportValidity();
                        return false;
                    }
                }
                return true;
            }

            function textVal(name) {
                const el = form.elements[name];
                return el ? (el.value ?? '') : '';
            }

            // "lab_ids[]" is multiple checkboxes for Equipment and Pharma
            // (both multi-room), and multiple radios for CSL (single-room) —
            // normalise all three into a value array.
            function selectedLabIds() {
                const el = form.elements['lab_ids[]'];
                if (!el) return [];
                if (el instanceof RadioNodeList) {
                    return Array.from(el)
                        .filter((node) => (node.type === 'radio' || node.type === 'checkbox') ? node.checked : true)
                        .map((node) => node.value)
                        .filter((v) => v !== '');
                }
                if (el.tagName === 'SELECT') return el.value ? [el.value] : [];
                if (el.type === 'radio' || el.type === 'checkbox') return el.checked ? [el.value] : [];
                return el.value ? [el.value] : [];
            }

            function setReview(name, text) {
                const el = form.querySelector(`[data-review="${name}"]`);
                if (el) el.textContent = (text && String(text).trim() !== '') ? text : '—';
            }

            function buildReview() {
                setReview('purpose', textVal('purpose'));
                setReview('applicant_name', textVal('applicant_name'));
                setReview('applicant_email', textVal('applicant_email'));
                setReview('applicant_id', textVal('applicant_id'));
                setReview('applicant_phone', textVal('applicant_phone'));
                setReview('applicant_department', textVal('applicant_department'));
                setReview('applicant_remark', textVal('applicant_remark'));
                setReview('applicant_group', textVal('applicant_group'));

                const roleEl = form.elements['applicant_role'];
                setReview('applicant_role', roleEl?.selectedOptions?.[0]?.text ?? '');

                // <input type="date"> values are ISO (yyyy-mm-dd); display as dd-mm-yyyy.
                const fmtDMY = (iso) => { const p = String(iso).split('-'); return p.length === 3 ? `${p[2]}/${p[1]}/${p[0]}` : iso; };
                const dateFrom = textVal('booking_date_from');
                const dateTo = textVal('booking_date_to');
                setReview('dates', (dateTo && dateTo !== dateFrom) ? (fmtDMY(dateFrom) + ' to ' + fmtDMY(dateTo)) : fmtDMY(dateFrom));
                setReview('times', textVal('start_time') + ' – ' + textVal('end_time'));

                if (bookingType === 'equipment' || bookingType === 'pharma') {
                    // Rooms & Equipment, grouped per room — each selected room
                    // (plus, for Equipment only, any room whose equipment was
                    // ticked without the room itself being selected, i.e. "alt
                    // lab" equipment — Pharma doesn't allow that, equipment
                    // always implies its room) shows its own equipment nested
                    // underneath it; a room with nothing ticked for it just
                    // shows the room by itself.
                    const groupsContainer = document.getElementById('review-rooms-equipment');
                    groupsContainer.innerHTML = '';
                    const roomGroups = new Map();

                    const roomGroupFor = (labId) => {
                        if (!roomGroups.has(labId)) {
                            const roomEl = document.getElementById('choose-room-' + labId) || document.getElementById('choose-lab-' + labId);
                            roomGroups.set(labId, { name: roomEl?.dataset.labName || labId, equipment: [] });
                        }
                        return roomGroups.get(labId);
                    };

                    form.querySelectorAll('input[name="lab_ids[]"]:checked').forEach((input) => {
                        roomGroupFor(input.value);
                    });

                    form.querySelectorAll('input[name="equipment_names[]"]:checked').forEach((input) => {
                        const separatorIndex = input.value.indexOf('::');
                        const labId = input.value.slice(0, separatorIndex);
                        const nameEl = input.closest('label')?.querySelector('[data-equip-name]');
                        const text = nameEl ? nameEl.textContent.trim() : input.value.slice(separatorIndex + 2);
                        roomGroupFor(labId).equipment.push(text);
                    });

                    roomGroups.forEach((group) => {
                        const equipmentHtml = group.equipment.length
                            ? `<div class="review-tags" style="margin:8px 0 0 4px;">${group.equipment.map((name) => `<span class="review-tag review-tag--muted">${escapeHtml(name)}</span>`).join('')}</div>`
                            : '';
                        groupsContainer.insertAdjacentHTML('beforeend', `<div style="margin-bottom:14px;"><span class="review-tag">${escapeHtml(group.name)}</span>${equipmentHtml}</div>`);
                    });

                    if (!roomGroups.size) groupsContainer.innerHTML = '<span class="review-value">No rooms selected.</span>';
                } else {
                    // CSL — plain room list, never has equipment.
                    const roomsContainer = document.getElementById('review-rooms');
                    roomsContainer.innerHTML = '';
                    form.querySelectorAll('input[name="lab_ids[]"]:checked').forEach((input) => {
                        roomsContainer.insertAdjacentHTML('beforeend', `<span class="review-tag">${escapeHtml(input.dataset.labName || input.value)}</span>`);
                    });
                    if (!roomsContainer.children.length) roomsContainer.innerHTML = '<span class="review-value">No rooms selected.</span>';
                }

                // Pax / students
                const paxContainer = document.getElementById('review-pax');
                if (paxContainer) {
                    paxContainer.innerHTML = '';
                    const names = form.querySelectorAll('[data-pax-name]');
                    const ids = form.querySelectorAll('[data-pax-id]');
                    names.forEach((nameInput, i) => {
                        const idInput = ids[i];
                        if (!nameInput.value && !idInput?.value) return;
                        paxContainer.insertAdjacentHTML('beforeend', `<li class="review-value">${escapeHtml(nameInput.value)} (${escapeHtml(idInput?.value ?? '')})</li>`);
                    });
                    const emptyEl = document.getElementById('review-pax-empty');
                    if (emptyEl) emptyEl.style.display = paxContainer.children.length ? 'none' : 'block';
                }

                if (bookingType === 'csl') {
                    setReview('csl_session_type', form.elements['csl_session_type']?.selectedOptions?.[0]?.text ?? '');
                    setReview('csl_discipline', form.elements['csl_discipline']?.selectedOptions?.[0]?.text ?? '');
                    setReview('csl_procedure', textVal('csl_procedure'));
                }

                if (bookingType === 'pharma') {
                    setReview('pax_count', textVal('pax_count'));
                }
            }

            if (toStep2Btn) {
                toStep2Btn.addEventListener('click', async () => {
                    if (!reportStepValidity(step1)) return;

                    // Rooms are checkboxes, so the browser can't require them —
                    // check here rather than letting the server bounce it back.
                    if (bookingType === 'csl' && !selectedLabIds().length) {
                        renderAlert(roomsAlert, ['Please select at least one CSL room for this session.']);
                        roomsAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        return;
                    }

                    // Re-check availability right now (not the debounced version) so
                    // stale/no-longer-relevant alerts can't slip through, and a check
                    // that hasn't fired yet (e.g. clicked right after typing) still runs.
                    const originalLabel = toStep2Btn.textContent;
                    toStep2Btn.disabled = true;
                    toStep2Btn.textContent = 'Checking availability…';
                    clearTimeout(availabilityTimer);
                    const result = await runAvailabilityCheck();
                    toStep2Btn.disabled = false;
                    toStep2Btn.textContent = originalLabel;

                    const blockingAlert = (localScheduleMessages().length || (result.schedule && result.schedule.length)) ? scheduleAlert
                        : (result.rooms && result.rooms.length) ? roomsAlert
                        : null;

                    if (blockingAlert) {
                        blockingAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        return;
                    }

                    step1.style.display = 'none';
                    step2.style.display = 'block';
                    setActiveStep(2);
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            }

            if (backToStep1Btn) {
                backToStep1Btn.addEventListener('click', () => {
                    step2.style.display = 'none';
                    step1.style.display = 'block';
                    setActiveStep(1);
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            }

            if (toReviewBtn) {
                toReviewBtn.addEventListener('click', () => {
                    if (!reportStepValidity(step2)) return;

                    if (bookingType === 'pharma' && pharmaDenied && pharmaDenied.style.display === 'block') {
                        pharmaDenied.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        return;
                    }

                    buildReview();
                    step2.style.display = 'none';
                    step3.style.display = 'block';
                    setActiveStep(3);
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            }

            if (backBtn) {
                backBtn.addEventListener('click', () => {
                    step3.style.display = 'none';
                    step2.style.display = 'block';
                    setActiveStep(2);
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            }

            // Guard against double-submits (e.g. double-click, slow network)
            // and give the applicant feedback that the booking is in flight.
            const submitBtn = document.getElementById('submit-booking-btn');
            form.addEventListener('submit', () => {
                if (!submitBtn) return;
                submitBtn.disabled = true;
                submitBtn.classList.add('is-loading');
                submitBtn.textContent = 'Submitting…';
            });

            // Duration: derive a friendly "Xh Ym" label from the selected
            // start/end time slots (read-only, not submitted).
            const durationDisplay = document.getElementById('duration-display');
            function updateDuration() {
                if (!durationDisplay) return;
                const s = textVal('start_time'), e = textVal('end_time');
                const toMin = (t) => parseInt(t.slice(0, 2), 10) * 60 + parseInt(t.slice(3, 5), 10);
                if (!s || !e) { durationDisplay.value = '—'; return; }
                const diff = toMin(e) - toMin(s);
                if (diff <= 0) { durationDisplay.value = 'Invalid range'; return; }
                const h = Math.floor(diff / 60), m = diff % 60;
                durationDisplay.value = [h ? h + 'h' : '', m ? m + 'm' : ''].filter(Boolean).join(' ');
            }
            ['start_time', 'end_time'].forEach((name) => {
                const el = form.elements[name];
                if (el) el.addEventListener('change', updateDuration);
            });
            updateDuration();

            // Run the live checks once on load in case fields are pre-filled
            // (e.g. after a validation-error redirect brings old() values back).
            scheduleAvailabilityCheck();
            if (bookingType === 'pharma') schedulePharmaLabsFetch();
            if (bookingType === 'equipment') scheduleEquipmentAvailabilityFetch();
        })();
    </script>

    <script>
        // Additive only: syncs the building filter chips to the existing
        // native <select id="block-filter"> (kept in the DOM, hidden, still
        // the source of truth) by dispatching a native "change" event — the
        // original applyBlockFilter() listener above fires unmodified.
        (function () {
            const select = document.getElementById('block-filter');
            const chipsWrap = document.getElementById('building-chips');
            if (!select || !chipsWrap) return;
            const chips = chipsWrap.querySelectorAll('.chip');

            function syncActive() {
                chips.forEach((chip) => chip.classList.toggle('active', chip.dataset.building === select.value));
            }

            chips.forEach((chip) => {
                chip.addEventListener('click', () => {
                    select.value = chip.dataset.building;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    syncActive();
                });
            });

            syncActive();
        })();
    </script>

    <script>
        // Additive only: populates the floating "ticket" summary from state
        // the form already tracks. Read-only against existing fields — does
        // not define, wrap, or override anything in the scripts above.
        (function () {
            const form = document.getElementById('booking-form');
            const ticketLab = document.getElementById('ticket-lab');
            const ticketDate = document.getElementById('ticket-date');
            const ticketTime = document.getElementById('ticket-time');
            const ticketDuration = document.getElementById('ticket-duration');
            if (!form || !ticketLab) return;

            function fmtDate(v) {
                if (!v) return '';
                const parts = v.split('-');
                return parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : v;
            }

            function updateTicket() {
                const labNames = Array.from(form.querySelectorAll('input[name="lab_ids[]"]:checked'))
                    .map((el) => el.dataset.labName)
                    .filter(Boolean);
                ticketLab.textContent = labNames.length ? labNames.join(', ') : '—';

                const dateFrom = form.elements['booking_date_from']?.value || '';
                const dateTo = form.elements['booking_date_to']?.value || '';
                ticketDate.textContent = dateFrom
                    ? (dateTo && dateTo !== dateFrom ? `${fmtDate(dateFrom)} – ${fmtDate(dateTo)}` : fmtDate(dateFrom))
                    : '—';

                const start = form.elements['start_time']?.value || '';
                const end = form.elements['end_time']?.value || '';
                ticketTime.textContent = start && end ? `${start} – ${end}` : '—';

                const durationDisplay = document.getElementById('duration-display');
                ticketDuration.textContent = (durationDisplay && durationDisplay.value && durationDisplay.value !== '—')
                    ? durationDisplay.value
                    : '—';
            }

            form.addEventListener('input', updateTicket);
            form.addEventListener('change', updateTicket);
            updateTicket();
        })();
    </script>
    @endif
@endsection
