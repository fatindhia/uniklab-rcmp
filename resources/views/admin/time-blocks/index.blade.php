@extends('layouts.admin')

@section('content')
    <style>
        .tb-kpi-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); margin-bottom: 18px; }
        .kpi--csl::before { background: var(--type-csl); }
        .kpi--pharma::before { background: var(--type-pharma); }

        .tb-hint { display: flex; align-items: center; gap: 10px; padding: 13px 16px; margin-bottom: 18px; font-size: 0.85rem; color: var(--muted); border-left: 3px solid var(--brand); }

        .tab-bar a[data-type="research"].active { color: var(--type-research); border-bottom-color: var(--type-research); background: color-mix(in srgb, var(--type-research) 12%, transparent); }
        .tab-bar a[data-type="csl"].active { color: var(--type-csl); border-bottom-color: var(--type-csl); background: color-mix(in srgb, var(--type-csl) 12%, transparent); }
        .tab-bar a[data-type="pharma"].active { color: var(--type-pharma); border-bottom-color: var(--type-pharma); background: color-mix(in srgb, var(--type-pharma) 12%, transparent); }
        .tab-bar a[data-type="research"].active .tab-count { background: var(--type-research); color: #fff; }
        .tab-bar a[data-type="csl"].active .tab-count { background: var(--type-csl); color: #fff; }
        .tab-bar a[data-type="pharma"].active .tab-count { background: var(--type-pharma); color: #fff; }

        .tb-search { min-height: 40px; padding: 0 14px; border-radius: var(--radius-sm); border: 1.5px solid var(--line); font-size: 0.84rem; width: 240px; max-width: 100%; }
        .tb-search:focus { outline: none; border-color: var(--brand); box-shadow: 0 0 0 3px rgba(125,145,148,.15); }

        @media (max-width: 1000px) { .tb-kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }

        /* Room + equipment picker (mirrors the user booking flow) */
        .sched-room-list { display: flex; flex-direction: column; gap: 8px; margin-top: 14px; }
        .sched-room-block { border: 1.5px solid var(--line); border-radius: var(--radius-sm); background: #fff; overflow: hidden; transition: border-color .15s ease, box-shadow .15s ease; }
        .sched-room-block:hover { border-color: var(--brand); }
        .sched-room-block.checked { border-color: var(--brand); box-shadow: 0 0 0 3px rgba(125,145,148,.1); }
        .sched-room-row { display: flex; align-items: center; gap: 9px; padding: 10px 12px; cursor: pointer; font-size: 0.82rem; margin: 0; }
        .sched-room-row input { width: 17px; height: 17px; accent-color: var(--brand); flex-shrink: 0; }
        .sched-room-block.checked .sched-room-row { background: rgba(125, 145, 148,.08); font-weight: 700; }
        .sched-room-count { margin-left: auto; font-size: 0.68rem; color: var(--muted); font-weight: 600; }
        .sched-equip-list { display: none; flex-direction: column; gap: 3px; padding: 7px 12px 11px 36px; border-top: 1px dashed var(--line); }
        .sched-equip-item { display: flex; align-items: center; gap: 8px; font-size: 0.78rem; color: var(--muted); cursor: pointer; padding: 3px 0; margin: 0; }
        .sched-equip-item input { accent-color: var(--brand); }
        .sched-equip-item input:checked + span { color: var(--ink); font-weight: 600; }

        /* Existing-on-date list (left panel) */
        .sc-existing-hdr { padding: 10px 15px; background: rgba(125,145,148,.1); border-bottom: 1px solid var(--line); font-size: .78rem; font-weight: 700; color: var(--brand-2); }

        /* The right wizard card's height is set via JS (scMatchWizardHeight)
           to match the left column's natural height, since that varies with
           how many bookings/blocks exist on the selected date. min-height:0
           lets the card actually shrink to a JS-applied height instead of
           being forced tall by its content; the room list then scrolls
           inside that space instead of growing the card further. */
        .sched-wizard-card { min-height: 0; }
        .sched-room-list {
            max-height: 320px; overflow-y: auto; scrollbar-width: thin; scrollbar-color: var(--brand) rgba(125,145,148,.15);
        }
        .sched-room-list::-webkit-scrollbar { width: 8px; }
        .sched-room-list::-webkit-scrollbar-track { background: rgba(125,145,148,.12); border-radius: 999px; }
        .sched-room-list::-webkit-scrollbar-thumb { background: var(--brand); border-radius: 999px; }
        .sched-room-list::-webkit-scrollbar-thumb:hover { background: var(--brand-2); }
        .sched-room-block { flex-shrink: 0; }
        .sc-rooms-scroll-hint {
            display: none; align-items: center; gap: 5px; margin-top: 7px; font-size: .72rem; font-weight: 700;
            color: var(--brand-2);
        }
        .sc-rooms-scroll-hint.show { display: flex; }

        /* Scheduled block list rows */
        .sb-entry { display: flex; gap: 12px; padding: 14px 18px; border-bottom: 1px solid var(--line); border-left: 3px solid transparent; transition: background .12s ease; }
        .sb-entry:hover { background: rgba(125, 145, 148,.04); }
        .sb-entry:last-child { border-bottom: none; }
        .sb-icon { width: 34px; height: 34px; border-radius: var(--radius-sm); background: rgba(194,101,15,.12); display: flex; align-items: center; justify-content: center; font-size: .92rem; flex-shrink: 0; }
        .sb-body { flex: 1; min-width: 0; }
        .sb-title { font-weight: 700; }
        .sb-meta { font-size: .76rem; color: var(--muted); margin-top: 3px; }
        .sb-rooms { font-size: .74rem; color: var(--muted); margin-top: 2px; }
        .sb-note { font-size: .72rem; color: var(--muted); font-style: italic; margin-top: 2px; }
        /* Below 16px, iOS Safari zooms the page in on focus and never zooms
           back out. This page sets its own control size, and its <style>
           block loads after admin.css, so the override has to live here. */
        .sc-today-btn { border: 1px solid var(--line); background: #fff; border-radius: 8px; padding: 2px 8px; font-size: .68rem; font-weight: 700; color: var(--muted); cursor: pointer; }
        @media (max-width: 900px) {
            .tb-search { font-size: 16px; }
            /* Was an 18px-tall button — too small to hit reliably with a thumb. */
            .sc-today-btn { min-height: 34px; padding: 0 12px; font-size: .76rem; }
        }
    </style>

    <section class="kpi-grid tb-kpi-grid">
        <div class="kpi"><strong>{{ $blockCounts['all'] }}</strong><span class="kpi-label">Upcoming Blocks</span></div>
        <div class="kpi"><strong>{{ $blockCounts['research'] }}</strong><span class="kpi-label">Research Labs</span></div>
        <div class="kpi kpi--csl"><strong>{{ $blockCounts['csl'] }}</strong><span class="kpi-label">CSL Labs</span></div>
        <div class="kpi kpi--pharma"><strong>{{ $blockCounts['pharma'] }}</strong><span class="kpi-label">Pharma Labs</span></div>
    </section>

    <div class="card tb-hint">
        <span>🗓️</span>
        <span>Click a date on the calendar → choose lab &amp; rooms → pick a time slot → set block details. Blocked slots stop new bookings from being made in that window.</span>
    </div>

    <div class="sched-layout">
        <!-- LEFT: mini calendar + existing blocks for selected date -->
        <div class="sc-left-col">
            <div class="cal-card">
                <div class="cal-header">
                    <button type="button" class="cal-nav" id="sc-prev">&#8249;</button>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span class="cal-month-label" id="sc-label">—</span>
                        <button type="button" onclick="scGoToday()" class="sc-today-btn">Today</button>
                    </div>
                    <button type="button" class="cal-nav" id="sc-next">&#8250;</button>
                </div>
                <div class="cal-weekdays"><div>Su</div><div>Mo</div><div>Tu</div><div>We</div><div>Th</div><div>Fr</div><div>Sa</div></div>
                <div class="cal-grid" id="sc-grid"></div>
                <div class="cal-legend">
                    <span><span class="type-dot type-dot--research"></span>Research</span>
                    <span><span class="type-dot type-dot--csl"></span>CSL</span>
                    <span><span class="type-dot type-dot--pharma"></span>Pharma</span>
                    <span><span class="type-dot type-dot--block"></span>Blocked</span>
                    <span><span class="type-dot type-dot--pending"></span>Pending</span>
                </div>
            </div>

            <div class="card" id="sc-existing" style="display:none; margin-top:12px;">
                <div class="sc-existing-hdr" id="sc-existing-hdr">On this date</div>
                <div id="sc-existing-list"></div>
            </div>
        </div>

        <!-- RIGHT: 4-step wizard -->
        <div class="card sched-wizard-card">
            <div class="sched-step-bar">
                <div class="s-step active" id="ss-1"><div class="s-step-num">1</div><div class="s-step-lbl">Date</div></div>
                <div class="s-line" id="sl-1"></div>
                <div class="s-step" id="ss-2"><div class="s-step-num">2</div><div class="s-step-lbl">Lab &amp; Rooms</div></div>
                <div class="s-line" id="sl-2"></div>
                <div class="s-step" id="ss-3"><div class="s-step-num">3</div><div class="s-step-lbl">Time Slot</div></div>
                <div class="s-line" id="sl-3"></div>
                <div class="s-step" id="ss-4"><div class="s-step-num">4</div><div class="s-step-lbl">Block Details</div></div>
            </div>

            <!-- Step 1: empty prompt -->
            <div class="s-panel s-panel-empty" id="sp-1">
                <div style="font-size:2.4rem; margin-bottom:10px;">📅</div>
                <div style="font-weight:700; font-size:1.05rem; margin-bottom:6px;">Select a Date</div>
                <div>Click any date on the calendar to begin scheduling a block or class session.</div>
            </div>

            <!-- Step 2: lab category + rooms -->
            <div class="s-panel" id="sp-2" style="display:none;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; margin-bottom:16px;">
                    <div>
                        <div style="font-weight:700;">Choose Lab Category</div>
                        <div class="muted" style="font-size:.8rem;">Then select the specific rooms to block.</div>
                    </div>
                    <span class="badge" id="sp2-date-chip">—</span>
                </div>
                <div class="sched-lab-grid">
                    <button type="button" class="sched-lab-card" data-type="research" onclick="scSelectLab('research')">
                        <div class="sched-lab-icon">🧪</div>
                        <div style="font-weight:700; font-size:.86rem;">Research Labs</div>
                    </button>
                    <button type="button" class="sched-lab-card" data-type="csl" onclick="scSelectLab('csl')">
                        <div class="sched-lab-icon">🏥</div>
                        <div style="font-weight:700; font-size:.86rem;">CSL Labs</div>
                    </button>
                    <button type="button" class="sched-lab-card" data-type="pharma" onclick="scSelectLab('pharma')">
                        <div class="sched-lab-icon">⚗️</div>
                        <div style="font-weight:700; font-size:.86rem;">Pharma Labs</div>
                    </button>
                </div>
                <div id="sc-rooms-section" style="display:none; margin-top:16px;">
                    <div style="font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:var(--muted); margin-bottom:9px;">Select Room(s) *<span style="font-weight:600; text-transform:none; letter-spacing:0;"> — tick a room, then choose its equipment (optional)</span></div>
                    <div class="sched-room-list" id="sc-rooms-grid"></div>
                    <div class="sc-rooms-scroll-hint" id="sc-rooms-scroll-hint">↓ Scroll for more rooms</div>
                    <div style="margin-top:14px; display:flex; justify-content:flex-end;">
                        <button type="button" class="button button-primary" id="sc-step2-next" disabled style="opacity:.5;" onclick="scGoStep3()">Next: Pick Time →</button>
                    </div>
                </div>
            </div>

            <!-- Step 3: per-room date + time slot board -->
            <div class="s-panel" id="sp-3" style="display:none;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; margin-bottom:14px;">
                    <div>
                        <div style="font-weight:700;">Pick a Time Slot</div>
                        <div class="muted" style="font-size:.8rem;">Each room keeps its own date and slot — set one room, then move to the next.</div>
                    </div>
                    <button type="button" class="button button-secondary" style="min-height:30px; padding:0 10px; font-size:.74rem;" onclick="scGoStep(2)">← Back</button>
                </div>

                <div class="sc-room-tabs" id="sc-room-tabs"></div>

                <div class="sc-slot-bar">
                    <label class="sc-bar-group" style="margin:0;">
                        <span class="sc-bar-lbl">Date</span>
                        {{-- Sizing lives in .tb-field--compact rather than inline, so the
                             mobile rule that lifts controls to 16px can override it. --}}
                        {{-- min keeps the native picker off past dates; TimeBlockController::store()
                             re-checks, since the attribute is trivially bypassed. --}}
                        <input type="date" id="sc-room-date" class="tb-field tb-field--compact" min="{{ today()->toDateString() }}" onchange="scSetRoomDate(this.value)">
                    </label>
                    <span class="sc-bar-sep"></span>
                    <div class="sc-bar-group">
                        <span class="sc-bar-lbl">Duration</span>
                        <div class="sc-dur-pills" id="sc-dur-pills"></div>
                    </div>
                </div>

                <div class="sc-legend">
                    <span><span class="sc-swatch"></span>Available</span>
                    <span><span class="sc-swatch sc-swatch--sel"></span>Selected start</span>
                    <span><span class="sc-swatch sc-swatch--range"></span>Selected range</span>
                    <span><span class="sc-swatch sc-swatch--booked"></span>Booked</span>
                    <span><span class="sc-swatch sc-swatch--blocked"></span>Blocked</span>
                </div>

                <div class="sc-board" id="sc-slot-board"></div>
                <div class="sc-slot-hint" id="sc-slot-hint">Click a slot to start · drag across slots for a longer block · click the slot right after your selection to add 30 min.</div>

                <div class="sc-selbar" id="sc-sel-bar">
                    <span class="sc-selbar-main">
                        <span class="sc-selbar-time" id="sc-sel-time">—</span>
                        <span class="sc-selbar-meta" id="sc-sel-meta"></span>
                    </span>
                    <span class="sc-selbar-actions">
                        <button type="button" class="button button-secondary" onclick="scClearSlot()">Clear</button>
                        <button type="button" class="button button-secondary" id="sc-apply-all" onclick="scApplySlotToAll()">Use for all rooms</button>
                        <button type="button" class="button button-secondary" id="sc-next-room" style="display:none;" onclick="scNextUnscheduledRoom()">Next room →</button>
                    </span>
                </div>

                <div style="margin-top:14px; display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;">
                    <span class="muted" id="sc-sched-progress" style="font-size:.78rem; font-weight:600;">—</span>
                    <button type="button" class="button button-primary" id="sc-step3-next" disabled style="opacity:.5;" onclick="scGoStep4()">Next: Details →</button>
                </div>
            </div>

            <!-- Step 4: block details -->
            <div class="s-panel" id="sp-4" style="display:none;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; margin-bottom:16px;">
                    <div>
                        <div style="font-weight:700;">Block Details</div>
                        <div class="muted" style="font-size:.8rem;" id="sp4-summary">Fill in the details for each room's block.</div>
                    </div>
                    <button type="button" class="button button-secondary" style="min-height:30px; padding:0 10px; font-size:.74rem;" onclick="scGoStep(3)">← Back</button>
                </div>

                <form id="scBlockForm" method="POST" action="{{ route('admin.time-blocks.store') }}">
                    @csrf
                    <input type="hidden" name="lab_type" id="sc-input-lab_type">
                    <div id="sc-input-rooms-container"></div>

                    {{-- One details card per room — each block is saved with its
                         own purpose, title, PIC, recurrence and notes. --}}
                    <div id="sc-details-list"></div>

                    <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:16px;">
                        <button type="button" class="button button-secondary" onclick="scGoStep(3)">← Back</button>
                        <button type="button" class="button button-primary" onclick="scSaveBlock()">🚫 Save Block</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- All Scheduled Blocks -->
    <div class="section-title" style="margin-top:28px;">
        <div>
            <h2>All Scheduled Blocks</h2>
            <div class="muted" style="font-size:.78rem; margin-top:3px;">Today and upcoming only — past blocks stay on record in <a href="{{ route('admin.history') }}" style="color:var(--brand-2); font-weight:700;">History</a>.</div>
        </div>
        <input type="text" id="tbSearch" class="tb-search" placeholder="Search title, PIC, room…" oninput="tbFilterBlocks()">
    </div>
    <div class="tab-bar">
        <a href="{{ route('admin.time-blocks.index') }}" class="{{ $blockTab === 'all' ? 'active' : '' }}">All <span class="tab-count">{{ $blockCounts['all'] }}</span></a>
        <a href="{{ route('admin.time-blocks.index', ['blockTab' => 'research']) }}" data-type="research" class="{{ $blockTab === 'research' ? 'active' : '' }}">🧪 Research <span class="tab-count">{{ $blockCounts['research'] }}</span></a>
        <a href="{{ route('admin.time-blocks.index', ['blockTab' => 'csl']) }}" data-type="csl" class="{{ $blockTab === 'csl' ? 'active' : '' }}">🏥 CSL <span class="tab-count">{{ $blockCounts['csl'] }}</span></a>
        <a href="{{ route('admin.time-blocks.index', ['blockTab' => 'pharma']) }}" data-type="pharma" class="{{ $blockTab === 'pharma' ? 'active' : '' }}">⚗️ Pharma <span class="tab-count">{{ $blockCounts['pharma'] }}</span></a>
    </div>

    <div class="card" id="tbBlockList">
        @forelse ($blocks as $block)
            <div class="block-entry sb-entry" data-text="{{ strtolower($block->title.' '.$block->pic.' '.implode(' ', $block->rooms ?? []).' '.implode(' ', $block->equipment ?? [])) }}" style="border-left-color:var(--type-{{ $block->lab_type }});">
                <div class="sb-icon">{{ ['class'=>'📚','practical'=>'🔬','maintenance'=>'🔧','reserved'=>'🔒','exam'=>'📝','event'=>'🎓'][$block->purpose] ?? '🚫' }}</div>
                <div class="sb-body">
                    <div class="sb-title">
                        {{ $block->title }}
                        @if ($block->recurring !== 'none')
                            <span class="badge" style="margin-left:6px; font-size:.66rem;">🔄 {{ $block->recurring === 'weekly' ? 'Weekly' : 'Bi-weekly' }}</span>
                        @endif
                        @if (($groupSizes[$block->group_id] ?? 0) > 1)
                            <span class="badge" style="margin-left:6px; font-size:.66rem;" title="Part of a multi-room block request ({{ $block->group_id }})">🔗 1 of {{ $groupSizes[$block->group_id] }} rooms</span>
                        @endif
                    </div>
                    <div class="sb-meta">
                        <span class="type-dot type-dot--{{ $block->lab_type }}" style="margin-right:5px;"></span>{{ ucfirst($block->lab_type) }}
                        · {{ ucfirst($block->purpose) }} · {{ $block->block_date?->format('d/m/Y') ?? $block->block_date }} · {{ substr($block->start_time, 0, 5) }}–{{ substr($block->end_time, 0, 5) }}
                        @if ($block->pic) · {{ $block->pic }} @endif
                    </div>
                    <div class="sb-rooms">{{ implode(', ', $block->rooms ?? []) }}</div>
                    @if (!empty($block->equipment))
                        <div class="sb-rooms">🔧 {{ implode(', ', array_map(fn ($e) => \Illuminate\Support\Str::afterLast($e, '::'), $block->equipment)) }}</div>
                    @endif
                    @if ($block->notes)
                        <div class="sb-note">{{ $block->notes }}</div>
                    @endif
                </div>
                <form method="POST" action="{{ route('admin.time-blocks.destroy', $block) }}" onsubmit="return scConfirmRemoveBlock(this);" style="flex-shrink:0;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="button button-danger" style="min-height:32px; padding:0 12px; font-size:.74rem;">✕ Remove</button>
                </form>
            </div>
        @empty
            <div class="empty">No blocks from today onwards — all upcoming slots are open for booking.</div>
        @endforelse
        @if ($blocks->count())
            <div class="empty" id="tbNoMatch" style="display:none;">No blocks match your search.</div>
        @endif
    </div>

    <script>
        const SC_EVENTS = @json($calendarEvents);
        // Bookable window: 08:00–17:00 (matches the user-facing booking hours).
        const SC_DAY_START = 8 * 60;
        const SC_DAY_END = 17 * 60;
        const SC_DURATIONS = [{m:60,l:'1 hr'},{m:90,l:'1.5 hrs'},{m:120,l:'2 hrs'},{m:150,l:'2.5 hrs'},{m:180,l:'3 hrs'},{m:210,l:'3.5 hrs'},{m:240,l:'4 hrs'},{m:300,l:'5 hrs'},{m:360,l:'6 hrs'},{m:540,l:'9 hrs (full day)'}];
        // schedules: room name -> its own { date, durMins, startTime, endTime }.
        // The calendar date picked in step 1 is only the default each room
        // starts from — every room can be moved to its own date and slot.
        // Server's today, not the browser's — a device with a wrong clock would
        // otherwise disagree with the rule TimeBlockController::store() applies.
        const SC_TODAY = @json(today()->toDateString());
        let SC = { step: 1, date: '', labType: '', rooms: [], equipment: [], schedules: {}, activeRoom: '' };

        function scToMin(t) { if (!t) return 0; const [h, m] = t.split(':').map(Number); return h * 60 + m; }
        function scFromMin(m) { return String(Math.floor(m / 60)).padStart(2, '0') + ':' + String(m % 60).padStart(2, '0'); }
        function scFmtDate(ds) { const p = String(ds).split('-'); return p.length === 3 ? `${p[2]}/${p[1]}/${p[0]}` : ds; }

        // Right wizard card matches the left column's height so a long room
        // list scrolls inside it instead of stretching the card taller than
        // the calendar. Below the layout's stacked breakpoint (900px) the two
        // columns sit one above the other, so the match is dropped there.
        function scMatchWizardHeight() {
            const left = document.querySelector('.sc-left-col');
            const wizard = document.querySelector('.sched-wizard-card');
            if (!left || !wizard) return;
            if (window.innerWidth <= 900) { wizard.style.height = ''; return; }
            wizard.style.height = left.offsetHeight + 'px';
        }
        window.addEventListener('resize', scMatchWizardHeight);

        function scGoToday() {
            const now = new Date();
            if (ADM_CAL['sc']) { ADM_CAL['sc'].year = now.getFullYear(); ADM_CAL['sc'].month = now.getMonth(); }
            admRenderCalendar('sc');
        }

        function scGoStep(n) {
            SC.step = n;
            for (let i = 1; i <= 4; i++) {
                const el = document.getElementById('ss-' + i);
                if (!el) continue;
                el.classList.remove('active', 'done');
                if (i < n) el.classList.add('done'); else if (i === n) el.classList.add('active');
                document.getElementById('sl-' + i)?.classList.toggle('done', i < n);
            }
            for (let i = 1; i <= 4; i++) {
                const p = document.getElementById('sp-' + i);
                if (p) p.style.display = (i === n) ? 'flex' : 'none';
            }
            if (n === 1) document.getElementById('sp-1').style.display = 'flex';
        }

        function scSelectDate(ds) {
            // Stop the wizard at the first step rather than letting the admin
            // pick rooms and times for a date that can never be saved.
            if (ds < SC_TODAY) {
                notifyError('That date has already passed — a block can only be set for today onwards.');
                return;
            }

            SC = { step: 1, date: ds, labType: '', rooms: [], equipment: [], schedules: {}, activeRoom: '' };
            const scDate = new Date(ds + 'T00:00:00');
            const fmt = scDate.toLocaleDateString('en-MY', { weekday: 'short' }) + ', ' + String(scDate.getDate()).padStart(2, '0') + '/' + String(scDate.getMonth() + 1).padStart(2, '0') + '/' + scDate.getFullYear();
            document.getElementById('sp2-date-chip').textContent = fmt;
            document.querySelectorAll('.sched-lab-card').forEach(c => c.classList.remove('selected'));
            document.getElementById('sc-rooms-section').style.display = 'none';
            scRenderExistingBlocks(ds);
            scGoStep(2);
            scMatchWizardHeight();
        }

        // Show what's already on the selected day — existing bookings AND blocks
        // — so the admin can review occupancy before scheduling anything.
        function scRenderExistingBlocks(ds) {
            const ev = SC_EVENTS[ds] || {};
            const bookings = ev.bookings || [];
            const blocks = ev.blocks || [];
            const panel = document.getElementById('sc-existing');
            const hdr = document.getElementById('sc-existing-hdr');
            const list = document.getElementById('sc-existing-list');
            panel.style.display = '';

            if (!bookings.length && !blocks.length) {
                hdr.textContent = 'Nothing on this date';
                list.innerHTML = '<div class="muted" style="padding:12px 14px; font-size:.78rem;">✓ This day is free — no bookings or blocks yet.</div>';
                return;
            }

            const parts = [];
            if (bookings.length) parts.push(bookings.length + ' booking' + (bookings.length > 1 ? 's' : ''));
            if (blocks.length) parts.push(blocks.length + ' block' + (blocks.length > 1 ? 's' : ''));
            hdr.textContent = parts.join(' · ') + ' on this date';

            let html = '';
            bookings.forEach(b => {
                const sc = { approved: '#1e6b3b', pending: '#a07c1f' }[b.status] || 'var(--muted)';
                html += `
                    <div class="block-entry" style="padding:8px 12px;">
                        <span class="type-dot type-dot--${admEsc(b.type)}" style="margin-top:5px; flex-shrink:0;"></span>
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:600; font-size:.8rem;">${admEsc(b.rooms || (b.type + ' lab'))} <span style="font-weight:700; font-size:.68rem; color:${sc};">· ${admEsc(b.status)}</span></div>
                            <div class="muted" style="font-size:.72rem;">${admEsc(b.start)}–${admEsc(b.end)} · ${admEsc(b.name)}</div>
                        </div>
                    </div>`;
            });
            blocks.forEach(b => {
                html += `
                    <div class="block-entry" style="padding:8px 12px;">
                        <span class="type-dot type-dot--block" style="margin-top:5px; flex-shrink:0;"></span>
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:600; font-size:.8rem;">🚫 ${admEsc(b.title)}</div>
                            <div class="muted" style="font-size:.72rem;">${admEsc(b.start)}–${admEsc(b.end)} · ${admEsc(b.rooms)}</div>
                        </div>
                    </div>`;
            });
            list.innerHTML = html;
        }

        function scSelectLab(type) {
            SC.labType = type; SC.rooms = []; SC.equipment = [];
            document.querySelectorAll('.sched-lab-card').forEach(c => c.classList.toggle('selected', c.dataset.type === type));
            const rooms = (window.ADMIN_LABS_BY_TYPE || {})[type] || [];
            document.getElementById('sc-rooms-grid').innerHTML = rooms.map(r => {
                const equip = r.equipment || [];
                const equipRows = equip.map(e => `
                    <label class="sched-equip-item">
                        <input type="checkbox" value="${admEsc(r.name)}::${admEsc(e)}" onchange="scToggleEquip(this)">
                        <span>${admEsc(e)}</span>
                    </label>`).join('');
                return `
                    <div class="sched-room-block" data-room="${admEsc(r.name)}">
                        <label class="sched-room-row">
                            <input type="checkbox" value="${admEsc(r.name)}" onchange="scToggleRoom(this)">
                            <span>${admEsc(r.name)}</span>
                            <span class="sched-room-count">${equip.length ? equip.length + ' equipment' : 'room only'}</span>
                        </label>
                        ${equip.length ? `<div class="sched-equip-list">${equipRows}</div>` : ''}
                    </div>`;
            }).join('');
            document.getElementById('sc-rooms-section').style.display = '';
            const nb = document.getElementById('sc-step2-next');
            nb.disabled = true; nb.style.opacity = '.5';
            scUpdateRoomsScrollHint();
        }

        // Room list scrollbar is easy to miss (esp. on trackpads/macOS where
        // scrollbars auto-hide), so surface an explicit hint whenever the
        // list actually overflows — and drop it once the user has scrolled
        // near the bottom, so it doesn't linger once its job is done.
        function scUpdateRoomsScrollHint() {
            const list = document.getElementById('sc-rooms-grid');
            const hint = document.getElementById('sc-rooms-scroll-hint');
            if (!list || !hint) return;
            const overflowing = list.scrollHeight > list.clientHeight + 2;
            hint.classList.toggle('show', overflowing);
            list.onscroll = overflowing ? function () {
                const nearBottom = list.scrollTop + list.clientHeight >= list.scrollHeight - 4;
                hint.classList.toggle('show', !nearBottom);
            } : null;
        }

        function scToggleRoom(cb) {
            const block = cb.closest('.sched-room-block');
            if (cb.checked) {
                if (!SC.rooms.includes(cb.value)) SC.rooms.push(cb.value);
            } else {
                SC.rooms = SC.rooms.filter(r => r !== cb.value);
                // Unchecking a room clears any equipment picked inside it.
                block.querySelectorAll('.sched-equip-item input:checked').forEach(e => { e.checked = false; });
                SC.equipment = SC.equipment.filter(v => !v.startsWith(cb.value + '::'));
            }
            block.classList.toggle('checked', cb.checked);
            const nb = document.getElementById('sc-step2-next');
            nb.disabled = SC.rooms.length === 0;
            nb.style.opacity = SC.rooms.length ? '1' : '.5';
        }

        function scToggleEquip(cb) {
            if (cb.checked) { if (!SC.equipment.includes(cb.value)) SC.equipment.push(cb.value); }
            else SC.equipment = SC.equipment.filter(v => v !== cb.value);
        }

        // --- Step 3: each selected room carries its own date + slot ---

        function scRoomSched(room) {
            if (!SC.schedules[room]) SC.schedules[room] = { date: SC.date, durMins: 60, startTime: '', endTime: '' };
            return SC.schedules[room];
        }

        function scGoStep3() {
            if (!SC.rooms.length) return;
            // Rooms unticked back on step 2 lose their schedule.
            Object.keys(SC.schedules).forEach(r => { if (!SC.rooms.includes(r)) delete SC.schedules[r]; });
            SC.rooms.forEach(scRoomSched);
            SC.activeRoom = SC.rooms.find(r => !SC.schedules[r].startTime) || SC.rooms[0];
            scGoStep(3);
            scRenderRoomEditor();
        }

        function scRenderRoomEditor() {
            const s = scRoomSched(SC.activeRoom);
            document.getElementById('sc-room-tabs').innerHTML = SC.rooms.map((r, i) => {
                const rs = scRoomSched(r);
                const slot = rs.startTime ? `${scFmtDate(rs.date)} · ${rs.startTime}–${rs.endTime}` : 'No slot yet';
                return `<button type="button" class="sc-room-tab${r === SC.activeRoom ? ' active' : ''}${rs.startTime ? ' done' : ''}" onclick="scSelectRoomTab(${i})">
                    <span class="rt-name">${admEsc(r)}</span>
                    <span class="rt-slot">${admEsc(slot)}</span>
                </button>`;
            }).join('');

            document.getElementById('sc-room-date').value = s.date;
            // A slot dragged out on the board can land on a length that isn't one
            // of the presets — surface it as its own pill so the active duration
            // is always visible rather than silently unrepresented.
            const durations = SC_DURATIONS.some(d => d.m === s.durMins)
                ? SC_DURATIONS
                : [{ m: s.durMins, l: scDurLabel(s.durMins) }, ...SC_DURATIONS];
            document.getElementById('sc-dur-pills').innerHTML = durations.map(d =>
                `<button type="button" class="dur-btn${d.m === s.durMins ? ' active' : ''}" onclick="scSetDur(${d.m})">${d.l}</button>`).join('');

            scRenderSlotBoard();

            const bar = document.getElementById('sc-sel-bar');
            bar.classList.toggle('show', !!s.startTime);
            if (s.startTime) {
                document.getElementById('sc-sel-time').textContent = `${s.startTime} – ${s.endTime}`;
                document.getElementById('sc-sel-meta').textContent =
                    `${scDurLabel(scToMin(s.endTime) - scToMin(s.startTime))} · ${SC.activeRoom} · ${scFmtDate(s.date)}`;
                document.getElementById('sc-apply-all').style.display = SC.rooms.length > 1 ? '' : 'none';
                document.getElementById('sc-next-room').style.display = SC.rooms.some(r => !scRoomSched(r).startTime) ? '' : 'none';
            }

            const done = SC.rooms.filter(r => scRoomSched(r).startTime).length;
            document.getElementById('sc-sched-progress').textContent = `${done} of ${SC.rooms.length} room${SC.rooms.length > 1 ? 's' : ''} scheduled`;
            const next = document.getElementById('sc-step3-next');
            next.disabled = done < SC.rooms.length;
            next.style.opacity = next.disabled ? '.5' : '1';
        }

        function scSelectRoomTab(i) {
            SC.activeRoom = SC.rooms[i];
            scRenderRoomEditor();
        }

        function scNextUnscheduledRoom() {
            const nextRoom = SC.rooms.find(r => !scRoomSched(r).startTime);
            if (nextRoom) { SC.activeRoom = nextRoom; scRenderRoomEditor(); }
        }

        // Moving a room to another date invalidates the slot picked against the
        // old date's occupancy, so the choice is cleared rather than carried over.
        function scSetRoomDate(value) {
            if (!value) return;
            // Typed dates bypass the picker's min, so clamp here as well —
            // otherwise the whole slot is built against a date the server will
            // reject only at the very end.
            if (value < SC_TODAY) {
                notifyError('That date has already passed. Pick today or a later date.');
                value = SC_TODAY;
            }
            const s = scRoomSched(SC.activeRoom);
            s.date = value; s.startTime = ''; s.endTime = '';
            scRenderRoomEditor();
        }

        function scSetDur(mins) {
            const s = scRoomSched(SC.activeRoom);
            s.durMins = mins; s.startTime = ''; s.endTime = '';
            scRenderRoomEditor();
        }

        // Copies the active room's slot onto every other room, but only where
        // that room is actually free then — rooms that clash keep whatever they
        // had and are named in the notice, so nothing is silently double-booked.
        function scApplySlotToAll() {
            const src = scRoomSched(SC.activeRoom);
            if (!src.startTime) return;
            const startMin = scToMin(src.startTime);
            const clashed = [];

            SC.rooms.forEach(r => {
                if (r === SC.activeRoom) return;
                if (scSlotFree(r, src.date, startMin, src.durMins)) {
                    SC.schedules[r] = { date: src.date, durMins: src.durMins, startTime: src.startTime, endTime: src.endTime };
                } else {
                    clashed.push(r);
                }
            });

            scRenderRoomEditor();

            if (clashed.length) {
                notifyError('This slot isn\'t free for: ' + clashed.join(', ') + '. Those rooms were left as they are — set their times individually.');
            }
        }

        // What already occupies a room on a given date: existing bookings and
        // existing blocks, both from the calendar snapshot.
        function scOccupancy(room, date) {
            const ev = SC_EVENTS[date] || {};
            const occ = [];
            (ev.bookings || []).forEach(b => {
                if (b.status === 'rejected' || b.status === 'cancelled') return;
                if ((b.rooms || '').split(', ').includes(room)) occ.push({ s: scToMin(b.start), e: scToMin(b.end), kind: 'booked', detail: b });
            });
            (ev.blocks || []).forEach(bl => {
                if ((bl.rooms || '').split(', ').includes(room)) occ.push({ s: scToMin(bl.start), e: scToMin(bl.end), kind: 'blocked', detail: bl });
            });
            return occ;
        }

        function scSlotFree(room, date, startMin, durMins) {
            if (startMin + durMins > SC_DAY_END) return false;
            const occ = scOccupancy(room, date);
            return !occ.some(o => startMin < o.e && startMin + durMins > o.s);
        }

        // Occupancy of the room/date currently on screen. Rebuilt on every
        // board render and read by the pointer handlers, which run outside
        // the render closure.
        let SC_OCC = [];
        // In-flight drag across the board: { anchor, end, moved }.
        let SC_DRAG = null;

        // admEsc() leaves quotes alone (it's for text nodes) — tooltips go into
        // a double-quoted attribute, so they need the quotes escaped too.
        function scAttr(s) { return admEsc(s).replace(/"/g, '&quot;'); }

        function scDurLabel(mins) {
            if (mins < 60) return mins + ' min';
            const h = mins / 60;
            return (Number.isInteger(h) ? h : h.toFixed(1)) + (h > 1 ? ' hrs' : ' hr');
        }

        function scOccAt(sm) { return SC_OCC.find(o => sm >= o.s && sm < o.e) || null; }
        function scSlotKind(sm) { const hit = scOccAt(sm); return hit ? hit.kind : 'available'; }
        // Every 30-min slot from `from` (inclusive) to `to` (exclusive) is free.
        function scRangeFree(from, to) {
            if (to > SC_DAY_END) return false;
            for (let t = from; t < to; t += 30) if (scSlotKind(t) !== 'available') return false;
            return true;
        }
        function scCanFit(sm, durMins) { return scRangeFree(sm, sm + durMins); }

        // Builds the "why can't I pick this" message for a slot that can't take
        // the current duration — either it's directly booked/blocked, the
        // duration would run into something later, or it would spill past
        // closing time.
        function scUnavailableReason(sm, durMins) {
            const hit = scOccAt(sm);
            if (hit && hit.kind === 'blocked') {
                const b = hit.detail;
                return '🚫 Blocked: ' + (b.title || 'Untitled') + (b.purpose ? ' (' + b.purpose + ')' : '')
                    + '\n' + b.start + '–' + b.end + ' · ' + (b.rooms || SC.activeRoom)
                    + (b.pic ? '\nPIC: ' + b.pic : '');
            }
            if (hit && hit.kind === 'booked') {
                const b = hit.detail;
                return 'Already booked: ' + (b.name || 'Applicant') + (b.status ? ' (' + b.status + ')' : '')
                    + '\n' + b.start + '–' + b.end
                    + (b.subject ? '\nSubject: ' + b.subject : '');
            }
            if (sm + durMins > SC_DAY_END) {
                return 'This duration doesn\'t fit — it would run past closing time (17:00).\nPick a shorter duration or an earlier start time.';
            }
            for (let t = sm; t < sm + durMins; t += 30) {
                const c = scOccAt(t);
                if (c) {
                    return 'This duration would overlap ' + (c.kind === 'blocked' ? 'a block' : 'a booking')
                        + ' starting at ' + scFromMin(t) + (c.detail.title || c.detail.name ? ': ' + (c.detail.title || c.detail.name) : '') + '.'
                        + '\nPick a shorter duration or a different start time.';
                }
            }
            return 'This time slot is not available.';
        }

        // The bookable day split into readable chunks, so slots are scanned a
        // period at a time instead of as one long undifferentiated strip.
        const SC_HINT = 'Click a slot to start · drag across slots for a longer block · click the slot right after your selection to add 30 min.';
        const SC_PERIODS = [
            { name: 'Morning', from: SC_DAY_START, to: 12 * 60 },
            { name: 'Afternoon', from: 12 * 60, to: SC_DAY_END },
        ];

        function scRenderSlotBoard() {
            const board = document.getElementById('sc-slot-board');
            const sched = scRoomSched(SC.activeRoom);
            SC_OCC = scOccupancy(SC.activeRoom, sched.date);
            SC_DRAG = null;

            const startMin = sched.startTime ? scToMin(sched.startTime) : null;
            const endMin = sched.endTime ? scToMin(sched.endTime) : null;

            let html = '';
            SC_PERIODS.forEach(period => {
                const from = Math.max(period.from, SC_DAY_START);
                const to = Math.min(period.to, SC_DAY_END);
                if (from >= to) return;

                let free = 0, cells = '';
                for (let sm = from; sm < to; sm += 30) {
                    const kind = scSlotKind(sm);
                    const hit = scOccAt(sm);
                    // Only the first slot of a booked/blocked run is labelled with
                    // what occupies it — repeating it on every slot is noise.
                    const runStart = !hit || sm === from || scOccAt(sm - 30) !== hit;
                    const isStart = startMin === sm;
                    const inRange = startMin !== null && sm > startMin && sm < endMin;
                    const fits = kind === 'available' && scCanFit(sm, sched.durMins);
                    if (kind === 'available') free++;

                    let cls = 'sc-slot', tag = '';
                    const isFree = kind === 'available' ? '1' : '0';
                    let title = scFromMin(sm) + '–' + scFromMin(sm + 30);
                    // The last slot of a selection is tagged with the time the
                    // block actually ends, so the board reads as a span rather
                    // than a run of start times.
                    if (isStart) { cls += ' is-start'; tag = sm + 30 === endMin ? '→ ' + sched.endTime : 'Start'; }
                    else if (inRange) { cls += ' is-range'; tag = sm + 30 === endMin ? '→ ' + sched.endTime : 'Selected'; }
                    else if (kind === 'blocked') {
                        cls += ' is-blocked';
                        tag = runStart ? (hit.detail.title || 'Blocked') : 'Blocked';
                        title = scUnavailableReason(sm, sched.durMins);
                    } else if (kind === 'booked') {
                        cls += ' is-booked';
                        tag = runStart ? (hit.detail.name || 'Booked') : 'Booked';
                        title = scUnavailableReason(sm, sched.durMins);
                    } else if (!fits) {
                        // Free, but the chosen duration can't start here. Still a
                        // valid drag target, so it keeps free="1".
                        cls += ' is-tight';
                        tag = "Won't fit";
                        title = scUnavailableReason(sm, sched.durMins);
                    } else {
                        tag = 'Free';
                    }

                    cells += `<button type="button" class="${cls}" data-sm="${sm}" data-free="${isFree}" title="${scAttr(title)}">
                        <span class="sc-slot-time">${scFromMin(sm)}</span>
                        <span class="sc-slot-tag">${admEsc(tag)}</span>
                    </button>`;
                }

                html += `
                    <div class="sc-period">
                        <div class="sc-period-hd">
                            <span class="sc-period-name">${period.name}</span>
                            <span class="sc-period-range">${scFromMin(from)}–${scFromMin(to)}</span>
                            <span class="sc-period-rule"></span>
                            <span class="sc-period-free">${free} of ${(to - from) / 30} free</span>
                        </div>
                        <div class="sc-slot-grid">${cells}</div>
                    </div>`;
            });

            board.innerHTML = html || '<div class="sc-board-empty">No bookable hours on this day.</div>';
            document.getElementById('sc-slot-hint').textContent = SC_HINT;
        }

        // --- Slot board interaction -------------------------------------
        // Three ways to set a slot, all landing on the same { startTime,
        // endTime, durMins } the rest of the wizard already reads:
        //   • click a free slot        → places the selected duration there
        //   • drag across free slots   → a block of exactly that span
        //   • click the slot just after the selection → grows it by 30 min
        //     (clicking inside the selection trims it back to that point)

        function scSlotChip(e) {
            const el = e.target.closest ? e.target.closest('.sc-slot') : null;
            return el && document.getElementById('sc-slot-board').contains(el) ? el : null;
        }

        function scPaintDrag() {
            const from = SC_DRAG ? Math.min(SC_DRAG.anchor, SC_DRAG.end) : 0;
            const to = SC_DRAG ? Math.max(SC_DRAG.anchor, SC_DRAG.end) + 30 : 0;
            document.querySelectorAll('#sc-slot-board .sc-slot').forEach(el => {
                const sm = Number(el.dataset.sm);
                el.classList.toggle('is-preview', !!SC_DRAG && sm >= from && sm < to);
            });
            const hint = document.getElementById('sc-slot-hint');
            if (SC_DRAG && SC_DRAG.moved) {
                hint.textContent = `Selecting ${scFromMin(from)} – ${scFromMin(to)} · ${scDurLabel(to - from)}`;
            }
        }

        function scApplySelection(startSm, endSm) {
            const s = scRoomSched(SC.activeRoom);
            s.startTime = scFromMin(startSm);
            s.endTime = scFromMin(endSm);
            s.durMins = endSm - startSm;
            scRenderRoomEditor();
        }

        function scClearSlot() {
            const s = scRoomSched(SC.activeRoom);
            s.startTime = ''; s.endTime = '';
            scRenderRoomEditor();
        }

        // A press-and-release on a single slot (no drag) — the classic click.
        function scClickSlot(sm) {
            const s = scRoomSched(SC.activeRoom);
            const start = s.startTime ? scToMin(s.startTime) : null;
            const end = s.endTime ? scToMin(s.endTime) : null;

            if (start !== null) {
                if (sm === start) { scClearSlot(); return; }
                if (sm > start && sm < end) { scApplySelection(start, sm); return; }   // trim
                if (sm === end && scRangeFree(start, sm + 30)) { scApplySelection(start, sm + 30); return; }  // extend
            }
            if (scCanFit(sm, s.durMins)) { scApplySelection(sm, sm + s.durMins); return; }
            scSlotUnavailable(scUnavailableReason(sm, s.durMins));
        }

        function scInitSlotBoard() {
            const board = document.getElementById('sc-slot-board');
            if (!board) return;

            board.addEventListener('pointerdown', e => {
                const chip = scSlotChip(e);
                if (!chip) return;
                const sm = Number(chip.dataset.sm);
                if (chip.dataset.free !== '1') { scSlotUnavailable(scUnavailableReason(sm, scRoomSched(SC.activeRoom).durMins)); return; }
                e.preventDefault();
                SC_DRAG = { anchor: sm, end: sm, moved: false };
                scPaintDrag();
            });

            board.addEventListener('pointerover', e => {
                if (!SC_DRAG) return;
                const chip = scSlotChip(e);
                if (!chip || chip.dataset.free !== '1') return;
                const sm = Number(chip.dataset.sm);
                if (sm === SC_DRAG.end) return;
                // A dragged block has to be contiguous free time end to end.
                if (!scRangeFree(Math.min(sm, SC_DRAG.anchor), Math.max(sm, SC_DRAG.anchor) + 30)) return;
                SC_DRAG.end = sm;
                SC_DRAG.moved = SC_DRAG.moved || sm !== SC_DRAG.anchor;
                scPaintDrag();
            });

            window.addEventListener('pointerup', () => {
                const drag = SC_DRAG;
                if (!drag) return;
                SC_DRAG = null;
                if (drag.moved) {
                    scApplySelection(Math.min(drag.anchor, drag.end), Math.max(drag.anchor, drag.end) + 30);
                } else {
                    scClickSlot(drag.anchor);
                }
            });

            // Dragging out of the board (or cancelling the gesture) drops the
            // preview rather than committing a half-made range.
            window.addEventListener('pointercancel', () => { SC_DRAG = null; scPaintDrag(); });

            // Keyboard activation (Enter/Space) fires a click with detail 0 and
            // no pointer events at all, so it needs its own way in.
            board.addEventListener('click', e => {
                if (e.detail !== 0) return;
                const chip = scSlotChip(e);
                if (chip) scClickSlot(Number(chip.dataset.sm));
            });
        }

        function scSlotUnavailable(reason) {
            notifyError(reason);
        }

        // Async confirm, so block the submit and replay it once confirmed —
        // same pattern as confirmDeleteLab() on the Manage Labs page.
        function scConfirmRemoveBlock(form) {
            if (form.dataset.confirmed === '1') return true;

            confirmAction({
                title: 'Remove this block?',
                text: 'The slot will become bookable again.',
                confirmText: 'Remove',
                danger: true,
            }).then(function (ok) {
                if (!ok) return;
                form.dataset.confirmed = '1';
                form.submit();
            });

            return false;
        }

        const SC_PURPOSES = [
            { v: 'class', l: '📚 Class / Teaching' },
            { v: 'practical', l: '🔬 Practical Session' },
            { v: 'maintenance', l: '🔧 Maintenance' },
            { v: 'reserved', l: '🔒 Reserved / Private' },
            { v: 'exam', l: '📝 Exam / OSCE' },
            { v: 'event', l: '🎓 Event' },
        ];
        const SC_RECURRING = [
            { v: 'none', l: 'One-time only' },
            { v: 'weekly', l: 'Every week (same day)' },
            { v: 'biweekly', l: 'Every 2 weeks' },
        ];

        // Every room lands as its own block, so each gets its own details card
        // (purpose, title, PIC, recurrence, notes) headed by its own slot.
        function scGoStep4() {
            if (SC.rooms.some(r => !scRoomSched(r).startTime)) return;

            document.getElementById('sp4-summary').textContent =
                `${SC.labType} · ${SC.rooms.length} block${SC.rooms.length > 1 ? 's' : ''} — fill in the details for each room.`;

            document.getElementById('sc-details-list').innerHTML = SC.rooms.map((r, i) => {
                const s = scRoomSched(r);
                const equip = SC.equipment.filter(v => v.startsWith(r + '::')).map(v => v.split('::')[1]);
                return `
                <div class="sc-detail-card">
                    <div class="sc-detail-head">
                        <div>
                            <div class="sc-detail-room">🏷️ ${admEsc(r)}</div>
                            <div class="sc-detail-slot">📅 ${scFmtDate(s.date)} · ⏰ ${s.startTime} – ${s.endTime}${equip.length ? ' · 🔧 ' + admEsc(equip.join(', ')) : ''}</div>
                        </div>
                        ${i === 0 && SC.rooms.length > 1
                            ? '<button type="button" class="button button-secondary" style="min-height:30px; padding:0 10px; font-size:.72rem;" onclick="scCopyDetailsToAll()">Copy to all rooms</button>'
                            : ''}
                    </div>
                    <div class="grid-2" style="gap:10px;">
                        <label style="display:grid; gap:5px;">
                            <span class="muted" style="font-size:.72rem; text-transform:uppercase; font-weight:700;">Purpose *</span>
                            <select name="blocks[${i}][purpose]" data-detail="purpose" class="tb-field" style="min-height:40px; padding:0 12px;">
                                ${SC_PURPOSES.map(p => `<option value="${p.v}">${p.l}</option>`).join('')}
                            </select>
                        </label>
                        <label style="display:grid; gap:5px;">
                            <span class="muted" style="font-size:.72rem; text-transform:uppercase; font-weight:700;">Recurring</span>
                            <select name="blocks[${i}][recurring]" data-detail="recurring" class="tb-field" style="min-height:40px; padding:0 12px;">
                                ${SC_RECURRING.map(p => `<option value="${p.v}">${p.l}</option>`).join('')}
                            </select>
                        </label>
                        <label style="grid-column:1/-1; display:grid; gap:5px;">
                            <span class="muted" style="font-size:.72rem; text-transform:uppercase; font-weight:700;">Title / Event Name *</span>
                            <input type="text" name="blocks[${i}][title]" data-detail="title" class="tb-field" placeholder="e.g. Year 3 CSL Suturing Class" style="min-height:40px; padding:0 12px;">
                        </label>
                        <label style="grid-column:1/-1; display:grid; gap:5px;">
                            <span class="muted" style="font-size:.72rem; text-transform:uppercase; font-weight:700;">Instructor / Person In Charge</span>
                            <input type="text" name="blocks[${i}][pic]" data-detail="pic" class="tb-field" placeholder="Name or department" style="min-height:40px; padding:0 12px;">
                        </label>
                        <label style="grid-column:1/-1; display:grid; gap:5px;">
                            <span class="muted" style="font-size:.72rem; text-transform:uppercase; font-weight:700;">Notes / Remarks</span>
                            <textarea name="blocks[${i}][notes]" data-detail="notes" class="tb-field" rows="2" placeholder="Setup requirements, equipment, class codes…" style="padding:10px 12px; font-family:inherit;"></textarea>
                        </label>
                    </div>
                </div>`;
            }).join('');

            scGoStep(4);
        }

        // Convenience for the common case where every room shares the same
        // class/event — copies the first card's details onto the rest.
        function scCopyDetailsToAll() {
            const cards = document.querySelectorAll('.sc-detail-card');
            if (cards.length < 2) return;
            ['purpose', 'recurring', 'title', 'pic', 'notes'].forEach(field => {
                const source = cards[0].querySelector(`[data-detail="${field}"]`);
                cards.forEach((card, i) => {
                    if (i === 0) return;
                    card.querySelector(`[data-detail="${field}"]`).value = source.value;
                });
            });
        }

        function scSaveBlock() {
            if (SC.rooms.some(r => !scRoomSched(r).startTime)) { scGoStep(3); return; }

            // Every room's card needs its own title before anything is saved.
            const missing = Array.from(document.querySelectorAll('.sc-detail-card [data-detail="title"]'))
                .find(input => !input.value.trim());
            if (missing) { missing.focus(); return; }

            document.getElementById('sc-input-lab_type').value = SC.labType;

            // One blocks[] entry per room, each carrying its own date/time and
            // the equipment ticked inside that room.
            const container = document.getElementById('sc-input-rooms-container');
            container.innerHTML = '';
            const addInput = (name, value) => {
                const input = document.createElement('input');
                input.type = 'hidden'; input.name = name; input.value = value;
                container.appendChild(input);
            };

            SC.rooms.forEach((r, i) => {
                const s = scRoomSched(r);
                addInput(`blocks[${i}][room]`, r);
                addInput(`blocks[${i}][block_date]`, s.date);
                addInput(`blocks[${i}][start_time]`, s.startTime);
                addInput(`blocks[${i}][end_time]`, s.endTime);
                SC.equipment.filter(v => v.startsWith(r + '::')).forEach(v => addInput(`blocks[${i}][equipment][]`, v));
            });

            document.getElementById('scBlockForm').submit();
        }

        function tbFilterBlocks() {
            const q = (document.getElementById('tbSearch')?.value || '').toLowerCase().trim();
            let visible = 0;
            document.querySelectorAll('#tbBlockList .block-entry').forEach(el => {
                const match = !q || (el.dataset.text || '').includes(q);
                el.style.display = match ? '' : 'none';
                if (match) visible++;
            });
            const noMatch = document.getElementById('tbNoMatch');
            if (noMatch) noMatch.style.display = (q && visible === 0) ? '' : 'none';
        }

        initAdminCalendar('sc', SC_EVENTS, { onDayClick: scSelectDate });
        scInitSlotBoard();
        scGoStep(1);
        scMatchWizardHeight();
    </script>
@endsection
