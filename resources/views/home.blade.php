@extends('layouts.app')

@section('content')
    <style>
        :root {
            --type-research: var(--brand);
            --type-csl: #8c7a9c;
            --type-pharma: #7d9068;
            --type-block: #b0835f;
            --status-approved: #1e6b3b;
            --status-pending: #a07c1f;
            --status-rejected: #a03027;
            --cal-research: #4a5f62;
            --cal-csl: #5a4d6b;
            --cal-pharma: #4e5c3c;
            --cal-block: #7a5533;
            --cal-pending: #e0b429;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeUp .5s ease both;
        }

        @media (prefers-reduced-motion: reduce) {
            .fade-in {
                animation: none;
            }
        }

        /* ---- Hero band ---- */
        .hero-band {
            padding: 64px 0 56px;
        }

        .hero-layout {
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 40px;
            align-items: center;
        }

        .hero-copy .title span {
            color: var(--brand-2);
        }

        .hero-actions {
            display: flex;
            gap: 14px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .hero-visual {
            border-radius: 22px;
            overflow: hidden;
            position: relative;
            min-height: 320px;
            background: linear-gradient(155deg, rgba(125, 145, 148, 0.16), rgba(148, 128, 111, 0.1)), var(--panel-strong);
            border: 1px solid var(--line);
            box-shadow: var(--shadow);
        }

        .hero-visual-grid {
            position: absolute;
            inset: 0;
            opacity: 0.5;
            background-image: linear-gradient(var(--line) 1px, transparent 1px), linear-gradient(90deg, var(--line) 1px, transparent 1px);
            background-size: 30px 30px;
        }

        .hero-visual-body {
            position: relative;
            padding: 26px;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .hero-visual-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            align-self: flex-start;
            padding: 8px 14px;
            border-radius: 999px;
            background: #fff;
            border: 1px solid var(--line);
            font-size: 0.78rem;
            font-weight: 800;
            color: var(--brand-2);
            box-shadow: var(--shadow-sm);
        }

        .hero-visual-stats {
            display: grid;
            gap: 10px;
        }

        .hero-visual-stat {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 16px;
            border-radius: 14px;
            background: #fff;
            border: 1px solid var(--line);
            box-shadow: var(--shadow-sm);
        }

        .hero-visual-stat strong {
            font-family: 'Sora', sans-serif;
            font-size: 1.3rem;
        }

        .hero-visual-stat span {
            font-size: 0.82rem;
            color: var(--muted);
            font-weight: 600;
        }

        @media (max-width: 920px) {
            .hero-layout {
                grid-template-columns: 1fr;
            }

            .hero-band {
                padding: 44px 0 40px;
            }

            .hero-visual {
                min-height: 260px;
            }
        }

        /* ---- Stat band (dark) ---- */
        .stat-band {
            padding: 52px 0;
        }

        .stat-band-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 32px;
        }

        .stat-item {
            border-left: 1px solid rgba(255, 255, 255, 0.14);
            padding-left: 24px;
        }

        .stat-item:first-child {
            border-left: none;
            padding-left: 0;
        }

        .stat-num {
            font-family: 'Sora', sans-serif;
            font-weight: 800;
            font-size: clamp(2.4rem, 5vw, 3.4rem);
            line-height: 1;
            color: #fff;
        }

        .stat-label {
            margin-top: 10px;
            font-size: 0.92rem;
            color: rgba(255, 255, 255, 0.6);
            font-weight: 600;
        }

        @media (max-width: 760px) {
            .stat-band-grid {
                grid-template-columns: 1fr;
                gap: 24px;
            }

            .stat-item {
                border-left: none;
                padding-left: 0;
                border-top: 1px solid rgba(255, 255, 255, .14);
                padding-top: 18px;
            }

            .stat-item:first-child {
                border-top: none;
                padding-top: 0;
            }
        }

        /* ---- Calendar band ---- */
        .cal-band {
            padding: 64px 0;
        }

        .pc-card {
            background: var(--panel-strong);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            overflow: hidden;
            min-width: 0;
            display: flex;
            flex-direction: column;
            box-shadow: var(--shadow);
        }

        .pc-header {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 14px 16px;
            border-bottom: 1px solid var(--line);
            flex-wrap: wrap;
        }

        .pc-label {
            font-weight: 800;
            font-size: 1.02rem;
            margin-left: 4px;
            margin-right: auto;
        }

        .pc-nav {
            background: none;
            border: 1px solid var(--line);
            border-radius: 8px;
            width: 30px;
            height: 30px;
            cursor: pointer;
            font-size: 1.1rem;
            color: var(--muted);
            transition: border-color .15s ease, color .15s ease;
        }

        .pc-nav:hover {
            border-color: var(--brand);
            color: var(--brand-2);
        }

        .pc-today {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 0 14px;
            height: 30px;
            cursor: pointer;
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--ink);
            transition: border-color .15s ease, color .15s ease;
        }

        .pc-today:hover {
            border-color: var(--brand);
            color: var(--brand-2);
        }

        .pc-filter {
            min-height: 32px;
            padding: 0 10px;
            border-radius: 8px;
            border: 1px solid var(--line);
            background: #fff;
            font-size: 0.82rem;
            color: var(--ink);
        }

        .pc-weekdays {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            border-bottom: 1px solid var(--line);
            background: rgba(49, 43, 44, .02);
        }

        .pc-weekdays>div {
            padding: 6px 2px;
            text-align: center;
            font-size: 0.64rem;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
        }

        .pc-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
        }

        .pc-day {
            min-height: 68px;
            min-width: 0;
            padding: 4px;
            border-right: 1px solid var(--line);
            border-bottom: 1px solid var(--line);
            cursor: pointer;
            transition: background .15s ease;
            display: flex;
            flex-direction: column;
        }

        .pc-day:nth-child(7n) {
            border-right: none;
        }

        .pc-day:hover {
            background: rgba(125, 145, 148, .05);
        }

        .pc-day.selected {
            background: rgba(125, 145, 148, .09) !important;
            box-shadow: inset 0 0 0 2px var(--brand);
        }

        .pc-day.other {
            background: rgba(49, 43, 44, .015);
            cursor: default;
        }

        .pc-day.other .pc-day-num {
            color: rgba(49, 43, 44, .28);
        }

        .pc-day-num {
            font-family: var(--mono);
            font-size: 0.7rem;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 999px;
            align-self: flex-start;
        }

        .pc-day.today .pc-day-num {
            background: rgba(125, 145, 148, .14);
            color: var(--brand-2);
        }

        .pc-bars {
            display: flex;
            flex-direction: column;
            gap: 2px;
            margin-top: 4px;
            min-width: 0;
        }

        .pc-bar {
            display: block;
            font-size: 0.6rem;
            font-weight: 700;
            line-height: 1.5;
            color: #fff;
            padding: 1px 6px;
            border-radius: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pc-bar--research {
            background: var(--cal-research);
        }

        .pc-bar--csl {
            background: var(--cal-csl);
        }

        .pc-bar--pharma {
            background: var(--cal-pharma);
        }

        .pc-bar--block {
            background: var(--cal-block);
        }

        .pc-bar--pending {
            background: var(--cal-pending);
            color: #3d2f00;
            opacity: 1;
        }

        .pc-more {
            font-size: 0.6rem;
            font-weight: 700;
            color: var(--muted);
            padding: 1px 6px;
        }

        .pc-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            display: inline-block;
        }

        .pc-dot--research {
            background: var(--cal-research);
        }

        .pc-dot--csl {
            background: var(--cal-csl);
        }

        .pc-dot--pharma {
            background: var(--cal-pharma);
        }

        .pc-dot--block {
            background: var(--cal-block);
        }

        .pc-dot--pending {
            background: var(--cal-pending);
        }

        .pc-legend {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            padding: 10px 16px;
            font-size: 0.76rem;
            font-weight: 800;
            color: var(--ink);
            border-top: 1px solid var(--line);
        }

        .pc-legend span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .pc-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.3fr) minmax(0, 1fr);
            gap: 18px;
            align-items: stretch;
        }

        /* The calendar is a fixed 6-row grid, so it alone sets the row height.
           The details card is taken out of flow (absolute) so a long list can't
           stretch the row — it fills the calendar's height and scrolls inside. */
        .pc-detail-col {
            position: relative;
            min-height: 0;
        }

        .pc-detail-col > .pc-card {
            position: absolute;
            inset: 0;
        }

        .pc-detail-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 16px;
            border-bottom: 1px solid var(--line);
        }

        .pc-detail-header strong {
            font-size: 0.92rem;
            font-family: var(--mono);
            font-weight: 700;
        }

        .pc-detail-body {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            overscroll-behavior: contain;
        }

        /* macOS hides overlay scrollbars until you scroll, so the list never
           looked scrollable. Styling ::-webkit-scrollbar opts out of overlay
           and keeps a real, always-visible bar. Note: setting scrollbar-width
           or scrollbar-color here would make Chrome ignore these rules. */

        /* macOS keeps overlay scrollbars hidden until you actually scroll, so
           the styled bar below isn't enough on its own — this fade + hint sits
           on the panel's bottom edge whenever there's more to see, and clears
           once you've scrolled to the end. */
        .pc-detail-scroll-cue {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 48px;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            padding-bottom: 7px;
            pointer-events: none;
            font-size: 0.7rem;
            font-weight: 800;
            color: var(--brand-2);
            background: linear-gradient(to top, var(--panel-strong) 30%, rgba(255, 255, 255, 0));
            opacity: 0;
            transition: opacity .18s ease;
        }

        .pc-card.has-overflow .pc-detail-scroll-cue {
            opacity: 1;
        }

        .pc-detail-body::-webkit-scrollbar {
            width: 10px;
        }

        .pc-detail-body::-webkit-scrollbar-track {
            background: rgba(49, 43, 44, .05);
            border-left: 1px solid var(--line);
        }

        .pc-detail-body::-webkit-scrollbar-thumb {
            background: rgba(125, 145, 148, .55);
            border: 2px solid transparent;
            background-clip: padding-box;
            border-radius: 999px;
        }

        .pc-detail-body::-webkit-scrollbar-thumb:hover {
            background: var(--brand);
            border: 2px solid transparent;
            background-clip: padding-box;
        }

        .pc-item {
            padding: 12px 16px;
            border-bottom: 1px solid var(--line);
            font-size: 0.84rem;
            display: flex;
            gap: 10px;
            align-items: flex-start;
            transition: background .15s ease;
        }

        .pc-item:hover {
            background: rgba(125, 145, 148, .04);
        }

        .pc-item:last-child {
            border-bottom: none;
        }

        .pc-item .pc-dot {
            margin-top: 5px;
            flex-shrink: 0;
        }

        /* Each fact about a booking gets its own line — room, time, subject,
           then status — so a long room or subject never runs into the next. */
        .pc-item-lines {
            display: grid;
            gap: 3px;
            min-width: 0;
        }

        .pc-item-room {
            font-weight: 800;
        }

        .pc-item-meta {
            color: var(--muted);
            font-size: 0.8rem;
        }

        .pc-status {
            font-weight: 700;
            text-transform: capitalize;
        }

        .pc-status--approved {
            color: var(--status-approved);
        }

        .pc-status--pending {
            color: var(--status-pending);
        }

        .pc-status--rejected {
            color: var(--status-rejected);
        }

        .pc-status--cancelled {
            color: var(--muted);
        }

        .pc-status--blocked {
            color: var(--cal-block);
        }

        .pc-empty {
            padding: 26px 16px;
            text-align: center;
            color: var(--muted);
            font-size: 0.86rem;
        }

        @media (max-width: 920px) {
            .pc-layout {
                grid-template-columns: 1fr;
            }

            /* Stacked: the details card sits below the calendar in normal flow,
               capped so it scrolls rather than running down the page. */
            .pc-detail-col {
                position: static;
            }

            .pc-detail-col > .pc-card {
                position: relative;
                inset: auto;
            }

            .pc-detail-body {
                max-height: 320px;
            }

            .pc-day {
                min-height: 60px;
            }

            .pc-bar {
                font-size: 0.58rem;
            }
        }

        /* ---- Lab categories band ---- */
        .labs-band {
            padding: 64px 0;
        }

        .lab-cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 22px;
        }

        .lab-card {
            position: relative;
            background: var(--panel-strong);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform .2s ease, box-shadow .2s ease;
            box-shadow: var(--shadow-sm);
        }

        .lab-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow);
        }

        .lab-card-top {
            padding: 26px 26px 18px;
            flex: 1;
        }

        /* Each card is keyed to its lab type via --lab-accent: a stripe on top,
           the room count, and the CTA all pick the colour up from one place. */
        .lab-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--lab-accent, var(--brand));
        }

        .lab-card-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
        }

        .lab-card-icon {
            width: 52px;
            height: 52px;
            border-radius: 15px;
            display: grid;
            place-items: center;
        }

        .lab-count-big {
            text-align: right;
            line-height: 1.05;
        }

        .lab-count-big strong {
            display: block;
            font-family: 'Sora', sans-serif;
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--lab-accent, var(--brand));
        }

        .lab-count-big span {
            font-size: 0.64rem;
            text-transform: uppercase;
            letter-spacing: 0.09em;
            font-weight: 800;
            color: var(--muted);
        }

        .lab-card h3 {
            font-size: 1.18rem;
            margin-bottom: 8px;
        }

        .lab-card .desc {
            font-size: 0.88rem;
            color: var(--muted);
            line-height: 1.6;
            margin-bottom: 16px;
        }

        /* Hours / policy as scannable label-value rows instead of prose. */
        .lab-specs {
            margin: 0;
            border-top: 1px solid var(--line);
        }

        .lab-specs > div {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 14px;
            padding: 9px 0;
            border-bottom: 1px solid var(--line);
        }

        .lab-specs > div:last-child {
            border-bottom: none;
        }

        .lab-specs dt {
            font-size: 0.66rem;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            font-weight: 800;
            color: var(--muted);
            flex-shrink: 0;
        }

        .lab-specs dd {
            margin: 0;
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--ink);
            text-align: right;
        }

        .lab-card-footer {
            padding: 16px 26px;
            border-top: 1px solid var(--line);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }

        .lab-card-count {
            font-size: 0.78rem;
            color: var(--muted);
            font-weight: 600;
        }

    </style>

    <section class="band band--cream hero-band">
        <div class="band-inner hero-layout">
            <div class="hero-copy fade-in">
                <div class="eyebrow">Labs &amp; Equipment Booking</div>
                <h1 class="title">Book a lab in <span>minutes</span>.</h1>
                <p class="lede" style="margin-top:20px; max-width:52ch;">
                    Book Research & Development, CSL, and Pharma labs with ease. Check real-time availability, choose
                    preferred slot, and complete your booking in just a few steps. </p>
                <div class="hero-actions">
                    <a class="button button-primary" href="{{ route('booking.create') }}">Book a lab →</a>
                    <a class="button button-secondary" href="#calendar">View availability</a>
                </div>
            </div>

            <div class="hero-visual fade-in" style="animation-delay:80ms;">
                <div class="hero-visual-grid" aria-hidden="true"></div>
                <div class="hero-visual-body">
                    <span class="hero-visual-tag">◎ Live snapshot</span>
                    <div class="hero-visual-stats">
                        <div class="hero-visual-stat"><span>Research &amp;
                                Development</span><strong>{{ number_format((int) ($labCounts['research'] ?? 0)) }}
                                rooms</strong></div>
                        <div class="hero-visual-stat"><span>CSL
                                Labs</span><strong>{{ number_format((int) ($labCounts['csl'] ?? 0)) }} rooms</strong></div>
                        <div class="hero-visual-stat"><span>Pharma
                                Labs</span><strong>{{ number_format((int) ($labCounts['pharma'] ?? 0)) }} labs</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="band band--dark stat-band">
        <div class="band-inner">
            <div class="stat-band-grid">
                <div class="stat-item fade-in">
                    <div class="stat-num">{{ number_format((int) ($labCounts['research'] ?? 0)) }}</div>
                    <div class="stat-label">Research &amp; Development rooms across Al-Zahrawi &amp; Avicenna</div>
                </div>
                <div class="stat-item fade-in" style="animation-delay:60ms;">
                    <div class="stat-num">{{ number_format((int) ($labCounts['csl'] ?? 0)) }}</div>
                    <div class="stat-label">CSL rooms, with instructor assistance auto-assigned</div>
                </div>
                <div class="stat-item fade-in" style="animation-delay:120ms;">
                    <div class="stat-num">{{ number_format((int) ($labCounts['pharma'] ?? 0)) }}</div>
                    <div class="stat-label">Pharma labs bookable after hours &amp; weekends</div>
                </div>
            </div>
        </div>
    </section>

    <section class="band band--white cal-band" id="calendar">
        <div class="band-inner">
            <div class="section-title fade-in">
                <div>
                    <h2>Lab availability</h2>
                    <p>Tap any date to check what's booked.</p>
                </div>
            </div>

            <div class="pc-layout fade-in">
                <div class="pc-card">
                    <div class="pc-header">
                        <button type="button" class="pc-nav" id="pc-prev" aria-label="Previous month">&#8249;</button>
                        <button type="button" class="pc-today" id="pc-today">Today</button>
                        <button type="button" class="pc-nav" id="pc-next" aria-label="Next month">&#8250;</button>
                        <span class="pc-label" id="pc-label">—</span>
                        <select class="pc-filter" id="pc-filter">
                            <option value="">All categories</option>
                            <option value="research">Research</option>
                            <option value="csl">CSL</option>
                            <option value="pharma">Pharma</option>
                        </select>
                    </div>
                    <div class="pc-weekdays">
                        <div>Su</div>
                        <div>Mo</div>
                        <div>Tu</div>
                        <div>We</div>
                        <div>Th</div>
                        <div>Fr</div>
                        <div>Sa</div>
                    </div>
                    <div class="pc-grid" id="pc-grid"></div>
                    <div class="pc-legend">
                        <span><span class="pc-dot pc-dot--research"></span>Research</span>
                        <span><span class="pc-dot pc-dot--csl"></span>CSL</span>
                        <span><span class="pc-dot pc-dot--pharma"></span>Pharma</span>
                        <span><span class="pc-dot pc-dot--block"></span>Blocked</span>
                        <span><span class="pc-dot pc-dot--pending"></span>Pending</span>
                    </div>
                </div>

                <div class="pc-detail-col">
                    <div class="pc-card">
                        <div class="pc-detail-header">
                            <strong id="pc-detail-date">—</strong>
                        </div>
                        <div class="pc-detail-body" id="pc-detail-body"></div>
                        <div class="pc-detail-scroll-cue">↓ Scroll for more</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="band band--tint labs-band" id="labs">
        <div class="band-inner">
            <div class="section-title fade-in">
                <div>
                    <h2>Which lab do you need?</h2>
                    <p>Each category has its own rules and availability schedule.</p>
                </div>
            </div>

            <div class="lab-cards-grid">
                <div class="lab-card fade-in" style="--lab-accent:var(--brand-2);">
                    <div class="lab-card-top">
                        <div class="lab-card-head">
                            <div class="lab-card-icon" style="background:rgba(49, 43, 44,.08); color:var(--brand-2)">
                                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor"
                                    stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M10 3h4M10 3v6l-5.5 9.5A1.8 1.8 0 0 0 6.1 21h11.8a1.8 1.8 0 0 0 1.6-2.7L14 9V3" />
                                    <path d="M8.5 15h7" />
                                </svg>
                            </div>
                            <div class="lab-count-big">
                                <strong>{{ number_format((int) ($labCounts['research'] ?? 0)) }}</strong>
                                <span>rooms</span>
                            </div>
                        </div>
                        <h3>Research &amp; Development</h3>
                        <p class="desc">Book a lab in Al-Zahrawi or Avicenna, plus the equipment you need.</p>
                        <dl class="lab-specs">
                            <div>
                                <dt>Weekdays</dt>
                                <dd>8:00 AM – 5:00 PM</dd>
                            </div>
                            <div>
                                <dt>Weekends</dt>
                                <dd>Open — justify in Remarks</dd>
                            </div>
                        </dl>
                    </div>
                    <div class="lab-card-footer">
                        <span class="lab-card-count">Al-Zahrawi &amp; Avicenna</span>
                        <a class="button button-primary" style="min-height:38px; padding:0 16px; font-size:0.82rem;"
                            href="{{ route('booking.create', ['type' => 'equipment']) }}">Book now →</a>
                    </div>
                </div>

                <div class="lab-card fade-in" style="--lab-accent:var(--type-csl); animation-delay:60ms;">
                    <div class="lab-card-top">
                        <div class="lab-card-head">
                            <div class="lab-card-icon" style="background:rgba(140,122,156,.10); color:var(--type-csl)">
                                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor"
                                    stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 2h6M10 2v6.5L4.8 18a1.6 1.6 0 0 0 1.4 2.4h11.6A1.6 1.6 0 0 0 19.2 18L14 8.5V2" />
                                    <path d="M7 15h10" />
                                </svg>
                            </div>
                            <div class="lab-count-big">
                                <strong>{{ number_format((int) ($labCounts['csl'] ?? 0)) }}</strong>
                                <span>rooms</span>
                            </div>
                        </div>
                        <h3>CSL — Clinical Skills</h3>
                        <p class="desc">Skills and simulation rooms, with a 30-minute setup and cleanup buffer.</p>
                        <dl class="lab-specs">
                            <div>
                                <dt>Hours</dt>
                                <dd>Weekdays · 8:00 AM – 5:00 PM</dd>
                            </div>
                            <div>
                                <dt>Opens</dt>
                                <dd>Next week, every Thursday</dd>
                            </div>
                            <div>
                                <dt>Priority</dt>
                                <dd>Scheduled classes come first</dd>
                            </div>
                        </dl>
                    </div>
                    <div class="lab-card-footer">
                        <span class="lab-card-count">Avicenna — CSL 1 &amp; CSL 2</span>
                        <a class="button button-primary"
                            style="min-height:38px; padding:0 16px; font-size:0.82rem; background:var(--type-csl);"
                            href="{{ route('booking.create', ['type' => 'csl']) }}">Book now →</a>
                    </div>
                </div>

                <div class="lab-card fade-in" style="--lab-accent:var(--type-pharma); animation-delay:120ms;">
                    <div class="lab-card-top">
                        <div class="lab-card-head">
                            <div class="lab-card-icon" style="background:rgba(125,144,104,.12); color:var(--type-pharma)">
                                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor"
                                    stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2.5" y="8.5" width="19" height="7" rx="3.5" transform="rotate(-45 12 12)" />
                                    <path d="M9.5 9.5l5 5" />
                                </svg>
                            </div>
                            <div class="lab-count-big">
                                <strong>{{ number_format((int) ($labCounts['pharma'] ?? 0)) }}</strong>
                                <span>labs</span>
                            </div>
                        </div>
                        <h3>Pharma</h3>
                        <p class="desc">CL, MDLP, PL1 and PL2 for group practical sessions.</p>
                        <dl class="lab-specs">
                            <div>
                                <dt>Weekdays</dt>
                                <dd>5:00 PM – 9:00 PM</dd>
                            </div>
                            <div>
                                <dt>Weekends</dt>
                                <dd>8:00 AM – 5:00 PM</dd>
                            </div>
                            <div>
                                <dt>Booked by</dt>
                                <dd>Staff &amp; lecturers</dd>
                            </div>
                        </dl>
                    </div>
                    <div class="lab-card-footer">
                        <span class="lab-card-count">Avicenna — Level 1</span>
                        <a class="button button-primary"
                            style="min-height:38px; padding:0 16px; font-size:0.82rem; background:var(--type-pharma);"
                            href="{{ route('booking.create', ['type' => 'pharma']) }}">Book now →</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        (function() {
            const EVENTS = @json($calendarEvents);
            const MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September',
                'October', 'November', 'December'
            ];
            const state = {
                year: (new Date()).getFullYear(),
                month: (new Date()).getMonth(),
                filter: '',
                selected: null
            };

            function pad2(n) {
                return String(n).padStart(2, '0');
            }

            function dateKey(y, m, d) {
                return `${y}-${pad2(m + 1)}-${pad2(d)}`;
            }

            function esc(s) {
                return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            }

            function eventsFor(ds) {
                const ev = EVENTS[ds];
                if (!ev) return {
                    bookings: [],
                    blocks: []
                };
                const bookings = (ev.bookings || []).filter(b => !state.filter || b.type === state.filter);
                const blocks = (ev.blocks || []).filter(b => !state.filter || b.type === state.filter);
                return {
                    bookings,
                    blocks
                };
            }

            function render() {
                document.getElementById('pc-label').textContent = MONTHS[state.month] + ' ' + state.year;
                const grid = document.getElementById('pc-grid');
                grid.innerHTML = '';

                const first = new Date(state.year, state.month, 1).getDay();
                const daysInMonth = new Date(state.year, state.month + 1, 0).getDate();
                const daysInPrev = new Date(state.year, state.month, 0).getDate();
                const today = new Date();

                function cell(d, y, m, other) {
                    const ds = dateKey(y, m, d);
                    const isToday = !other && d === today.getDate() && m === today.getMonth() && y === today
                        .getFullYear();
                    const el = document.createElement('div');
                    el.className = 'pc-day' + (other ? ' other' : '') + (isToday ? ' today' : '') + (ds === state
                        .selected ? ' selected' : '');
                    const num = document.createElement('div');
                    num.className = 'pc-day-num';
                    num.textContent = d;
                    el.appendChild(num);

                    if (!other) {
                        const {
                            bookings,
                            blocks
                        } = eventsFor(ds);
                        const items = [
                            ...blocks.map(b => ({
                                label: b.rooms || b.title || 'Blocked',
                                type: 'block',
                                pending: false
                            })),
                            ...bookings.map(b => ({
                                label: b.rooms || (b.type.charAt(0).toUpperCase() + b.type.slice(1)),
                                type: b.type,
                                pending: b.status === 'pending'
                            })),
                        ];

                        if (items.length) {
                            const bars = document.createElement('div');
                            bars.className = 'pc-bars';
                            items.slice(0, 3).forEach(item => {
                                const bar = document.createElement('span');
                                bar.className = 'pc-bar pc-bar--' + item.type + (item.pending ?
                                    ' pc-bar--pending' : '');
                                bar.textContent = item.label;
                                bars.appendChild(bar);
                            });
                            if (items.length > 3) {
                                const more = document.createElement('span');
                                more.className = 'pc-more';
                                more.textContent = `+${items.length - 3} more`;
                                bars.appendChild(more);
                            }
                            el.appendChild(bars);
                        }
                        el.addEventListener('click', () => {
                            state.selected = ds;
                            render();
                            showDetail(ds);
                        });
                    }
                    return el;
                }

                for (let i = first - 1; i >= 0; i--) {
                    grid.appendChild(cell(daysInPrev - i, state.month === 0 ? state.year - 1 : state.year, state
                        .month === 0 ? 11 : state.month - 1, true));
                }
                for (let d = 1; d <= daysInMonth; d++) {
                    grid.appendChild(cell(d, state.year, state.month, false));
                }
                const total = first + daysInMonth;
                const rem = total % 7 === 0 ? 0 : 7 - (total % 7);
                for (let d = 1; d <= rem; d++) {
                    grid.appendChild(cell(d, state.month === 11 ? state.year + 1 : state.year, state.month === 11 ? 0 :
                        state.month + 1, true));
                }
            }

            function showDetail(ds) {
                const d = new Date(ds + 'T00:00:00');
                document.getElementById('pc-detail-date').textContent = d.toLocaleDateString('en-MY', {
                        weekday: 'long'
                    }) + ', ' + String(d.getDate()).padStart(2, '0') + '/' + String(d.getMonth() + 1).padStart(2, '0') +
                    '/' + d.getFullYear();

                const {
                    bookings,
                    blocks
                } = eventsFor(ds);
                const body = document.getElementById('pc-detail-body');
                let html = '';

                // Room, time, subject and status each get their own line.
                const line = (text, cls) => text ? `<span class="${cls}">${esc(text)}</span>` : '';

                blocks.forEach(b => {
                    html += `<div class="pc-item"><span class="pc-dot pc-dot--block"></span><div class="pc-item-lines">`
                        + line(b.rooms || b.title || 'Blocked', 'pc-item-room')
                        + line(`${b.start}–${b.end}`, 'pc-item-meta')
                        + line(b.title, 'pc-item-meta')
                        + `<span class="pc-status pc-status--blocked">🚫 Blocked</span>`
                        + `</div></div>`;
                });
                bookings.forEach(b => {
                    html += `<div class="pc-item"><span class="pc-dot pc-dot--${b.status === 'pending' ? 'pending' : esc(b.type)}"></span><div class="pc-item-lines">`
                        + line(b.rooms || b.type, 'pc-item-room')
                        + line(`${b.start}–${b.end}`, 'pc-item-meta')
                        + line(b.subject, 'pc-item-meta')
                        + `<span class="pc-status pc-status--${esc(b.status)}">${esc(b.status)}</span>`
                        + `</div></div>`;
                });
                if (!blocks.length && !bookings.length) {
                    html = '<div class="pc-empty">No bookings on this date.</div>';
                }
                body.innerHTML = html;
                body.scrollTop = 0;
                updateDetailOverflow();
            }

            // Shows the scroll cue only while there is genuinely more below.
            function updateDetailOverflow() {
                const body = document.getElementById('pc-detail-body');
                const card = body?.closest('.pc-card');
                if (!card) return;
                const overflowing = body.scrollHeight > body.clientHeight + 2;
                const nearBottom = body.scrollTop + body.clientHeight >= body.scrollHeight - 6;
                card.classList.toggle('has-overflow', overflowing && !nearBottom);
            }

            document.getElementById('pc-detail-body').addEventListener('scroll', updateDetailOverflow);
            window.addEventListener('resize', updateDetailOverflow);

            document.getElementById('pc-prev').addEventListener('click', () => {
                state.month--;
                if (state.month < 0) {
                    state.month = 11;
                    state.year--;
                }
                render();
            });
            document.getElementById('pc-next').addEventListener('click', () => {
                state.month++;
                if (state.month > 11) {
                    state.month = 0;
                    state.year++;
                }
                render();
            });
            document.getElementById('pc-today').addEventListener('click', () => {
                const now = new Date();
                state.year = now.getFullYear();
                state.month = now.getMonth();
                state.selected = dateKey(state.year, state.month, now.getDate());
                render();
                showDetail(state.selected);
            });
            document.getElementById('pc-filter').addEventListener('change', (e) => {
                state.filter = e.target.value || '';
                render();
                if (state.selected) showDetail(state.selected);
            });

            render();
            const todayKey = dateKey(state.year, state.month, (new Date()).getDate());
            state.selected = todayKey;
            render();
            showDetail(todayKey);
        })();
    </script>
@endsection
