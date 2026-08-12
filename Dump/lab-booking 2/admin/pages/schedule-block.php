<?php
/**
 * pages/schedule-block.php
 * Calendar-first block/closure wizard — layout & data ported from
 * previous-used/dashboard.php ("Schedule & Block" view).
 *
 * Flow: pick a date → choose lab & rooms → pick a time slot → block details.
 * UI-only (no DB): saved/removed blocks update the in-page state.
 */
require_once __DIR__ . '/../data/bookings.php';
require_once __DIR__ . '/../data/reports.php';

// Bookings reduced to what the time-grid occupancy check needs.
$schedBookings = array_map(fn($b) => [
  'date' => $b['date'], 'status' => $b['status'], 'rooms' => $b['lab'],
  'start' => $b['start'], 'end' => $b['end'], 'lab_type' => $b['lab_type'],
], $BOOKINGS);
?>

<div class="page-hero">
  <div class="page-hero-text">
    <span class="page-hero-eyebrow">🚫 Scheduling</span>
    <h1>Schedule &amp; Block Labs</h1>
    <p>Follow the steps: pick a date → choose lab &amp; rooms → select a time slot → set the block details.</p>
  </div>
  <div class="page-hero-side">
    <span class="page-hero-badge">📅 Calendar-first wizard</span>
  </div>
</div>

<div class="sched-layout">
  <!-- ── LEFT: calendar ── -->
  <div class="sched-cal-col">
    <div class="sc-cal-card">
      <div class="sc-cal-header">
        <button class="sc-cal-nav" id="sc-prev">&#8249;</button>
        <span class="sc-cal-label" id="sc-label"></span>
        <button class="sc-cal-nav" id="sc-next">&#8250;</button>
      </div>
      <div class="sc-cal-weekdays"><div>Su</div><div>Mo</div><div>Tu</div><div>We</div><div>Th</div><div>Fr</div><div>Sa</div></div>
      <div class="sc-cal-grid" id="sc-grid"></div>
      <div class="sc-cal-legend">
        <span class="sc-cal-leg"><span class="sc-dot" style="background:#202734"></span>Research</span>
        <span class="sc-cal-leg"><span class="sc-dot" style="background:#5d7c86"></span>CSL</span>
        <span class="sc-cal-leg"><span class="sc-dot" style="background:#2a7782"></span>Pharma</span>
        <span class="sc-cal-leg"><span class="sc-dot" style="background:#666759"></span>Blocked</span>
      </div>
    </div>
    <!-- Existing blocks for selected date -->
    <div class="sched-existing-panel" id="sc-existing" style="display:none;">
      <div class="sched-existing-hdr" id="sc-existing-hdr">Blocks on this date</div>
      <div id="sc-existing-list"></div>
    </div>
  </div>

  <!-- ── RIGHT: step wizard ── -->
  <div class="sched-steps-panel">
    <div class="sched-step-bar">
      <div class="s-step active" id="ss-1"><div class="s-step-num">1</div><div class="s-step-lbl">Date</div></div>
      <div class="s-line" id="sl-1"></div>
      <div class="s-step" id="ss-2"><div class="s-step-num">2</div><div class="s-step-lbl">Lab &amp; Rooms</div></div>
      <div class="s-line" id="sl-2"></div>
      <div class="s-step" id="ss-3"><div class="s-step-num">3</div><div class="s-step-lbl">Time Slot</div></div>
      <div class="s-line" id="sl-3"></div>
      <div class="s-step" id="ss-4"><div class="s-step-num">4</div><div class="s-step-lbl">Block Details</div></div>
    </div>

    <!-- Step 1 -->
    <div class="s-panel s-panel-empty" id="sp-1">
      <div class="s-empty-icon">📅</div>
      <div class="s-empty-title">Select a Date</div>
      <div class="s-empty-sub">Click any date on the calendar to begin scheduling a block or class session.</div>
    </div>

    <!-- Step 2 -->
    <div class="s-panel" id="sp-2" style="display:none;">
      <div class="s-panel-head">
        <div><div class="s-panel-title">Choose Lab Category</div><div class="s-panel-sub">Then select the specific rooms to block.</div></div>
        <div class="s-date-chip" id="sp2-date-chip">—</div>
      </div>
      <div class="sched-lab-grid">
        <button class="sched-lab-card" data-type="research" onclick="scSelectLab('research')">
          <div class="sched-lab-card-icon">🧪</div>
          <div class="sched-lab-card-name">Research Labs</div>
          <div class="sched-lab-card-sub">AZ &amp; Avicenna</div>
        </button>
        <button class="sched-lab-card" data-type="csl" onclick="scSelectLab('csl')">
          <div class="sched-lab-card-icon">🏥</div>
          <div class="sched-lab-card-name">CSL Labs</div>
          <div class="sched-lab-card-sub">CSL1 &amp; CSL2</div>
        </button>
        <button class="sched-lab-card" data-type="pharma" onclick="scSelectLab('pharma')">
          <div class="sched-lab-card-icon">⚗️</div>
          <div class="sched-lab-card-name">Pharma Labs</div>
          <div class="sched-lab-card-sub">CL · MDLP · PL1 · PL2</div>
        </button>
      </div>
      <div class="sched-rooms-section" id="sc-rooms-section" style="display:none;">
        <div class="sched-rooms-label">Select Room(s) *</div>
        <div class="sched-rooms-grid" id="sc-rooms-grid"></div>
        <div style="margin-top:14px;display:flex;justify-content:flex-end;">
          <button class="btn btn-primary" id="sc-step2-next" disabled onclick="scGoStep3()" style="opacity:.5;">Next: Pick Time →</button>
        </div>
      </div>
    </div>

    <!-- Step 3 -->
    <div class="s-panel" id="sp-3" style="display:none;">
      <div class="s-panel-head">
        <div><div class="s-panel-title">Pick a Time Slot</div><div class="s-panel-sub">Choose duration, then click an available circle to select your start time.</div></div>
        <div style="display:flex;align-items:center;gap:8px;">
          <div class="s-date-chip" id="sp3-date-chip">—</div>
          <button class="btn btn-outline btn-sm" onclick="scGoStep(2)">← Back</button>
        </div>
      </div>
      <div class="sched-dur-bar">
        <span class="sched-dur-lbl">Duration:</span>
        <div id="sc-dur-pills" style="display:flex;gap:6px;flex-wrap:wrap;"></div>
      </div>
      <div class="tg-legend">
        <div class="tg-legend-item"><div class="tg-dot tg-dot--avail"></div>Available</div>
        <div class="tg-legend-item"><div class="tg-dot tg-dot--selected"></div>Selected start</div>
        <div class="tg-legend-item"><div class="tg-dot tg-dot--range"></div>Duration range</div>
        <div class="tg-legend-item"><div class="tg-dot tg-dot--booked"></div>Booked</div>
        <div class="tg-legend-item"><div class="tg-dot tg-dot--blocked"></div>Blocked</div>
      </div>
      <div class="tg-wrap"><div class="tg-inner" id="sc-timegrid"></div></div>
      <div class="tg-selection-bar" id="sc-sel-bar" style="display:none;">
        <span id="sc-sel-text">—</span>
        <button class="btn btn-primary btn-sm" onclick="scGoStep4()">Next: Details →</button>
      </div>
    </div>

    <!-- Step 4 -->
    <div class="s-panel" id="sp-4" style="display:none;">
      <div class="s-panel-head">
        <div><div class="s-panel-title">Block Details</div><div class="s-panel-sub">Add a title and configure the block type.</div></div>
        <button class="btn btn-outline btn-sm" onclick="scGoStep(3)">← Back</button>
      </div>
      <div class="step4-summary" id="sp4-summary"><strong>Summary</strong>—</div>
      <div class="block-form-grid">
        <div class="form-group-block">
          <label class="form-label-block">Block Type *</label>
          <select class="form-ctrl" id="sc-blk-cat">
            <option value="class">📚 Class / Teaching</option>
            <option value="practical">🔬 Practical Session</option>
            <option value="maintenance">🔧 Maintenance</option>
            <option value="reserved">🔒 Reserved / Private</option>
            <option value="exam">📝 Exam / OSCE</option>
            <option value="event">🎓 Event</option>
          </select>
        </div>
        <div class="form-group-block">
          <label class="form-label-block">Recurring</label>
          <select class="form-ctrl" id="sc-blk-recur">
            <option value="none">One-time only</option>
            <option value="weekly">Every week (same day)</option>
            <option value="biweekly">Every 2 weeks</option>
          </select>
        </div>
        <div class="form-group-block full">
          <label class="form-label-block">Title / Event Name *</label>
          <input type="text" class="form-ctrl" id="sc-blk-title" placeholder="e.g. Year 3 CSL Suturing Class"/>
        </div>
        <div class="form-group-block full">
          <label class="form-label-block">Instructor / Person In Charge</label>
          <input type="text" class="form-ctrl" id="sc-blk-pic" placeholder="Name or department"/>
        </div>
        <div class="form-group-block full">
          <label class="form-label-block">Notes / Remarks</label>
          <textarea class="form-ctrl form-ctrl-lg" id="sc-blk-notes" placeholder="Setup requirements, equipment, class codes…"></textarea>
        </div>
      </div>
      <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:16px;">
        <button class="btn btn-outline" onclick="scGoStep(3)">← Back</button>
        <button class="btn btn-danger" onclick="scSaveBlock()">🚫 Save Block</button>
      </div>
    </div>
  </div><!-- /sched-steps-panel -->
</div><!-- /sched-layout -->

<!-- All blocks list -->
<div style="margin-top:30px;">
  <div class="section-header" style="margin-bottom:14px;"><div class="section-title" style="font-size:1rem;">🗂️ All Scheduled Blocks</div></div>
  <div class="tab-bar">
    <button class="tab-btn active" onclick="switchTab(this,'stab-all')">All <span class="tab-count" id="btc-all">0</span></button>
    <button class="tab-btn" onclick="switchTab(this,'stab-research')">🧪 Research <span class="tab-count" id="btc-research">0</span></button>
    <button class="tab-btn" onclick="switchTab(this,'stab-csl')">🏥 CSL <span class="tab-count" id="btc-csl">0</span></button>
    <button class="tab-btn" onclick="switchTab(this,'stab-pharma')">⚗️ Pharma <span class="tab-count" id="btc-pharma">0</span></button>
  </div>
  <div id="stab-all" class="tab-panel active"><div class="tbl-card"><div id="block-list-all"></div></div></div>
  <div id="stab-research" class="tab-panel"><div class="tbl-card"><div id="block-list-research"></div></div></div>
  <div id="stab-csl" class="tab-panel"><div class="tbl-card"><div id="block-list-csl"></div></div></div>
  <div id="stab-pharma" class="tab-panel"><div class="tbl-card"><div id="block-list-pharma"></div></div></div>
</div>

<style>
/* ── Schedule & Block — organized wizard (page-local) ── */
.sched-layout{display:grid;grid-template-columns:300px 1fr;gap:18px;align-items:start;}
@media(max-width:960px){.sched-layout{grid-template-columns:1fr;}}

/* ── Mini calendar ── */
.sc-cal-card{background:#fff;border:1px solid var(--border-light);border-radius:var(--r-xl);overflow:hidden;box-shadow:var(--shadow-xs);}
.sc-cal-header{display:flex;align-items:center;justify-content:space-between;padding:13px 16px;border-bottom:1px solid var(--border-light);background:linear-gradient(180deg,var(--off-white),#fff);}
.sc-cal-label{font-family:var(--font-serif);font-size:.92rem;font-weight:700;color:var(--navy);}
.sc-cal-nav{background:#fff;border:1px solid var(--border);border-radius:50%;width:28px;height:28px;font-size:1.05rem;cursor:pointer;color:var(--navy);display:flex;align-items:center;justify-content:center;line-height:1;transition:all .14s;}
.sc-cal-nav:hover{border-color:var(--navy);background:var(--navy);color:#fff;}
.sc-cal-weekdays{display:grid;grid-template-columns:repeat(7,1fr);border-bottom:1px solid var(--border-light);}
.sc-cal-weekdays>div{padding:7px 2px;text-align:center;font-size:.58rem;font-weight:700;color:var(--text-light);text-transform:uppercase;letter-spacing:.04em;}
.sc-cal-grid{display:grid;grid-template-columns:repeat(7,1fr);}
.sc-cal-day{min-height:54px;padding:5px 3px;border-right:1px solid var(--border-light);border-bottom:1px solid var(--border-light);cursor:pointer;transition:background .12s;display:flex;flex-direction:column;align-items:center;}
.sc-cal-day:nth-child(7n){border-right:none;}
.sc-cal-day:hover{background:var(--teal-light);}
.sc-cal-day.other-month{background:var(--off-white);}
.sc-cal-day.other-month .sc-cal-day-num{color:var(--border);}
.sc-cal-day.today .sc-cal-day-num{background:var(--navy);color:#fff;border-radius:50%;}
.sc-cal-day.selected-day{background:var(--teal-light)!important;box-shadow:inset 0 0 0 2px var(--teal);}
.sc-cal-day.selected-day .sc-cal-day-num{background:var(--teal);color:#fff;border-radius:50%;font-weight:700;}
.sc-cal-day-num{font-size:.72rem;font-weight:600;color:var(--text);width:22px;height:22px;display:flex;align-items:center;justify-content:center;margin-bottom:3px;}
.sc-cal-dots{display:flex;flex-wrap:wrap;gap:2px;justify-content:center;}
.sc-cal-dot{width:6px;height:6px;border-radius:50%;}
.sc-cal-dot.pending{opacity:.4;}
.sc-cal-legend{display:flex;gap:12px;padding:10px 14px;border-top:1px solid var(--border-light);flex-wrap:wrap;background:var(--off-white);}
.sc-cal-leg{display:flex;align-items:center;gap:5px;font-size:.68rem;color:var(--text-mid);font-weight:500;}
.sc-dot{width:8px;height:8px;border-radius:50%;}

/* ── Existing blocks on selected date ── */
.sched-existing-panel{background:#fff;border:1px solid var(--border-light);border-radius:var(--r-lg);margin-top:14px;overflow:hidden;box-shadow:var(--shadow-xs);}
.sched-existing-hdr{padding:10px 14px;background:linear-gradient(120deg,#a07c1f,#c79a3a);color:#fff;font-size:.74rem;font-weight:700;letter-spacing:.02em;}

/* ── Step wizard shell ── */
.sched-steps-panel{background:#fff;border:1px solid var(--border-light);border-radius:var(--r-xl);overflow:hidden;box-shadow:var(--shadow-sm);min-height:460px;display:flex;flex-direction:column;}
.sched-step-bar{display:flex;align-items:center;padding:18px 24px;border-bottom:1px solid var(--border-light);background:linear-gradient(180deg,var(--off-white),#fff);flex-shrink:0;}
.s-step{display:flex;align-items:center;gap:9px;}
.s-step-num{width:30px;height:30px;border-radius:50%;background:#fff;border:2px solid var(--border);color:var(--text-light);font-size:.82rem;font-weight:800;display:flex;align-items:center;justify-content:center;transition:all .25s;flex-shrink:0;}
.s-step.active .s-step-num{background:linear-gradient(135deg,#5cb0ba,#2a7782);border-color:transparent;color:#fff;box-shadow:0 5px 14px rgba(42,119,130,.36);transform:scale(1.08);}
.s-step.done .s-step-num{background:var(--success);border-color:transparent;color:#fff;font-size:0;}
.s-step.done .s-step-num::after{content:'✓';font-size:.92rem;}
.s-step-lbl{font-size:.74rem;font-weight:700;color:var(--text-light);white-space:nowrap;}
.s-step.active .s-step-lbl{color:var(--navy);}
.s-step.done .s-step-lbl{color:var(--success);}
.s-line{flex:1;height:3px;border-radius:3px;background:var(--border);margin:0 9px;min-width:12px;transition:background .25s;}
.s-line.done{background:var(--success);}

/* ── Step panels ── */
.s-panel{padding:28px 26px;flex:1;display:flex;flex-direction:column;animation:scFade .26s ease;}
@keyframes scFade{from{opacity:0;transform:translateY(7px);}to{opacity:1;transform:none;}}
.s-panel-empty{align-items:center;justify-content:center;text-align:center;}
.s-empty-icon{font-size:3rem;margin-bottom:14px;opacity:.75;}
.s-empty-title{font-family:var(--font-serif);font-size:1.12rem;font-weight:700;color:var(--navy);margin-bottom:7px;}
.s-empty-sub{font-size:.83rem;color:var(--text-light);line-height:1.55;max-width:330px;}
.s-panel-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:20px;flex-wrap:wrap;}
.s-panel-title{font-family:var(--font-serif);font-size:1rem;font-weight:700;color:var(--navy);margin-bottom:3px;}
.s-panel-sub{font-size:.78rem;color:var(--text-light);}
.s-date-chip{background:linear-gradient(135deg,#5cb0ba,#2a7782);color:#fff;padding:5px 13px;border-radius:999px;font-size:.74rem;font-weight:700;white-space:nowrap;box-shadow:0 3px 9px rgba(42,119,130,.26);}

/* ── Lab category cards ── */
.sched-lab-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:11px;}
@media(max-width:680px){.sched-lab-grid{grid-template-columns:1fr;}}
.sched-lab-card{position:relative;overflow:hidden;border:2px solid var(--border-light);border-radius:var(--r-lg);padding:18px 10px;text-align:center;cursor:pointer;background:#fff;transition:all .18s;font-family:var(--font-sans);}
.sched-lab-card::before{content:'';position:absolute;left:0;top:0;right:0;height:4px;background:var(--lc,#50a7b2);opacity:0;transition:opacity .18s;}
.sched-lab-card[data-type="research"]{--lc:#202734;}
.sched-lab-card[data-type="csl"]{--lc:#5d7c86;}
.sched-lab-card[data-type="pharma"]{--lc:#2a7782;}
.sched-lab-card:hover{border-color:var(--lc);transform:translateY(-2px);box-shadow:var(--shadow-sm);}
.sched-lab-card:hover::before,.sched-lab-card.selected::before{opacity:1;}
.sched-lab-card.selected{border-color:var(--lc);background:var(--teal-light);box-shadow:0 0 0 3px rgba(80,167,178,.16);}
.sched-lab-card-icon{font-size:1.7rem;margin-bottom:7px;}
.sched-lab-card-name{font-size:.84rem;font-weight:700;color:var(--navy);margin-bottom:2px;}
.sched-lab-card-sub{font-size:.67rem;color:var(--text-light);}

/* ── Rooms ── */
.sched-rooms-section{margin-top:18px;border-top:1px dashed var(--border);padding-top:16px;}
.sched-rooms-label{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-light);margin-bottom:10px;}
.sched-rooms-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:8px;}
.sched-room-item{display:flex;align-items:center;gap:9px;padding:9px 12px;border:1.5px solid var(--border);border-radius:var(--r);cursor:pointer;font-size:.78rem;color:var(--text);transition:all .15s;background:#fff;user-select:none;}
.sched-room-item:hover{border-color:var(--teal);background:var(--teal-light);}
.sched-room-item.checked{border-color:var(--teal);background:var(--teal-light);color:var(--teal-dark);font-weight:600;}
.sched-room-item input{accent-color:var(--teal);width:14px;height:14px;flex-shrink:0;pointer-events:none;}

/* ── Duration ── */
.sched-dur-bar{display:flex;align-items:center;gap:9px;margin-bottom:16px;flex-wrap:wrap;}
.sched-dur-lbl{font-size:.74rem;font-weight:700;color:var(--text-mid);flex-shrink:0;}
.dur-btn{padding:5px 13px;border:1.5px solid var(--border);border-radius:999px;font-size:.74rem;font-weight:600;color:var(--text-mid);background:#fff;cursor:pointer;transition:all .14s;font-family:var(--font-sans);}
.dur-btn:hover{border-color:var(--teal);color:var(--teal-dark);}
.dur-btn.active{background:linear-gradient(135deg,#5cb0ba,#2a7782);color:#fff;border-color:transparent;box-shadow:0 3px 8px rgba(42,119,130,.28);}

/* ── Time grid ── */
.tg-legend{display:flex;gap:14px;margin-bottom:12px;flex-wrap:wrap;}
.tg-legend-item{display:flex;align-items:center;gap:5px;font-size:.72rem;color:var(--text-mid);}
.tg-dot{width:13px;height:13px;border-radius:50%;flex-shrink:0;}
.tg-dot--avail{background:#fff;border:2px solid #c4d2d6;}
.tg-dot--booked{background:var(--text-light);}
.tg-dot--blocked{background:#666759;}
.tg-dot--selected{background:var(--teal-dark);}
.tg-dot--range{background:rgba(80,167,178,.18);border:2px solid var(--teal);}
.tg-wrap{overflow-x:auto;border:1px solid var(--border-light);border-radius:var(--r-lg);background:#fbfcfa;}
.tg-inner{display:inline-block;min-width:100%;}
.tg-header-row{display:flex;background:var(--off-white);border-bottom:1px solid var(--border);position:sticky;top:0;z-index:3;}
.tg-corner{width:170px;min-width:170px;padding:7px 12px;font-size:.64rem;font-weight:700;color:var(--text-light);border-right:1px solid var(--border);flex-shrink:0;display:flex;align-items:center;position:sticky;left:0;background:var(--off-white);z-index:4;}
.tg-time-hdr{width:62px;min-width:62px;padding:6px 4px;text-align:center;font-size:.62rem;font-weight:700;color:var(--text-light);border-right:1px solid var(--border-light);flex-shrink:0;line-height:1.3;}
.tg-time-hdr:last-child{border-right:none;}
.tg-row{display:flex;border-bottom:1px solid var(--border-light);}
.tg-row:last-child{border-bottom:none;}
.tg-room-lbl{width:170px;min-width:170px;padding:8px 12px;font-size:.73rem;font-weight:600;color:var(--navy);border-right:1px solid var(--border);flex-shrink:0;display:flex;align-items:center;background:#fff;position:sticky;left:0;z-index:2;line-height:1.3;}
.tg-cell{width:62px;min-width:62px;height:48px;border-right:1px solid var(--border-light);display:flex;align-items:center;justify-content:center;flex-shrink:0;position:relative;}
.tg-cell:last-child{border-right:none;}
.slot-btn{width:34px;height:34px;border-radius:50%;border:2px solid #c4d2d6;background:#fff;display:flex;align-items:center;justify-content:center;font-size:.56rem;font-weight:700;color:var(--text-light);transition:all .14s;cursor:pointer;}
.slot-btn:hover:not(.slot-blocked):not(.slot-booked){border-color:var(--teal);background:var(--teal-light);color:var(--teal-dark);}
.slot-btn.slot-blocked{background:#666759;border-color:#666759;color:#fff;cursor:not-allowed;}
.slot-btn.slot-booked{background:var(--text-light);border-color:var(--text-light);color:#fff;cursor:not-allowed;}
.slot-btn.slot-selected{background:var(--teal-dark);border-color:var(--teal-dark);color:#fff;box-shadow:0 0 0 3px rgba(80,167,178,.25);}
.slot-btn.slot-range{background:rgba(80,167,178,.16);border-color:var(--teal);color:var(--teal-dark);}
.tg-selection-bar{display:flex;align-items:center;justify-content:space-between;margin-top:14px;padding:12px 16px;background:var(--teal-light);border:1px solid #bfe0e4;border-radius:var(--r-lg);font-size:.82rem;font-weight:700;color:var(--teal-dark);flex-wrap:wrap;gap:8px;}

/* ── Step 4 ── */
.step4-summary{background:var(--teal-light);border:1px solid #bfe0e4;border-radius:var(--r-lg);padding:13px 16px;font-size:.78rem;color:var(--teal-dark);margin-bottom:18px;line-height:1.6;}
.step4-summary strong{display:block;font-size:.83rem;font-weight:700;margin-bottom:5px;color:var(--navy);}
.block-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
@media(max-width:560px){.block-form-grid{grid-template-columns:1fr;}}
.form-group-block{display:flex;flex-direction:column;gap:5px;}
.form-group-block.full{grid-column:1/-1;}
.form-label-block{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-light);}
.form-ctrl{padding:9px 12px;border:1.5px solid var(--border);border-radius:var(--r-sm);font-family:var(--font-sans);font-size:.82rem;outline:none;background:#fff;color:var(--text);transition:border .15s,box-shadow .15s;}
.form-ctrl:focus{border-color:var(--teal);box-shadow:0 0 0 3px rgba(80,167,178,.16);}
.form-ctrl-lg{min-height:70px;resize:vertical;}

/* ── All blocks: tabs + entries ── */
.section-header{display:flex;align-items:center;justify-content:space-between;}
.section-title{font-family:var(--font-serif);font-weight:700;color:var(--navy);}
.tbl-card{background:#fff;border:1px solid var(--border-light);border-radius:var(--r-xl);overflow:hidden;box-shadow:var(--shadow-xs);}
.tab-bar{display:flex;gap:6px;margin-bottom:16px;flex-wrap:wrap;}
.tab-btn{padding:8px 16px;font-size:.8rem;font-weight:600;color:var(--text-mid);background:var(--off-white);border:1px solid var(--border-light);border-radius:999px;cursor:pointer;transition:all .15s;display:flex;align-items:center;gap:7px;}
.tab-btn:hover{color:var(--navy);border-color:var(--teal);}
.tab-btn.active{background:linear-gradient(135deg,#5cb0ba,#2a7782);color:#fff;border-color:transparent;box-shadow:0 3px 9px rgba(42,119,130,.26);}
.tab-count{display:inline-flex;align-items:center;justify-content:center;min-width:18px;height:18px;padding:0 5px;border-radius:9px;font-size:.62rem;font-weight:700;background:var(--navy-light);color:var(--navy);}
.tab-btn.active .tab-count{background:rgba(255,255,255,.26);color:#fff;}
.tab-panel{display:none;}
.tab-panel.active{display:block;}
.block-entry{display:flex;align-items:flex-start;gap:12px;padding:13px 16px;border-bottom:1px solid var(--border-light);font-size:.79rem;transition:background .12s;}
.block-entry:hover{background:var(--off-white);}
.block-entry:last-child{border-bottom:none;}
.block-entry-icon{width:34px;height:34px;border-radius:9px;background:linear-gradient(135deg,#f6efe2,#f9f1d8);display:flex;align-items:center;justify-content:center;font-size:.95rem;flex-shrink:0;margin-top:1px;}
.block-entry-body{flex:1;min-width:0;}
.block-entry-title{font-weight:700;color:var(--navy);}
.block-entry-meta{color:var(--text-light);font-size:.72rem;margin-top:3px;}
.block-entry-rooms{color:var(--text-mid);font-size:.72rem;margin-top:2px;}
.recurring-badge{display:inline-flex;align-items:center;gap:3px;font-size:.62rem;font-weight:700;padding:1px 7px;border-radius:8px;background:var(--teal-light);color:var(--teal-dark);margin-left:6px;}
</style>

<script>
(function () {
  // ── Data ──
  let ALL_BOOKINGS = <?= json_encode($schedBookings, JSON_UNESCAPED_UNICODE) ?>;
  let ALL_BLOCKS   = <?= json_encode(array_values($BLOCKED_SLOTS), JSON_UNESCAPED_UNICODE) ?>;
  let blockIdCounter = ALL_BLOCKS.length + 1;

  const MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
  const CSL_ROOMS = ['CSL1 – Physiko Room','CSL1 – Mock Ward','CSL1 – Simulation Room','CSL2 – Room 1','CSL2 – Room 2','CSL2 – Room 3','CSL2 – Room 4','CSL2 – Room 5','CSL2 – Room 6','CSL2 – Room 7','CSL2 – Room 8','CSL2 – Room 9','CSL2 – Room 10','CSL2 – Room 11','CSL2 – Room 12','CSL2 – Discussion Room'];
  const RESEARCH_ROOMS = ['AZ – Plant Extraction Room (A2052)','AZ – Molecular Room (A2051)','AZ – Media Preparation Room (A2055)','AZ – Assay Room (A2054)','AZ – Microbiology Room (A2012-A2013)','AZ – Cell Culture Room 1','AZ – Cell Culture Room 2','AZ – Cell Culture Room 3','AZ – Instrumentation Room','AV – MDL 3 (2A-31)','AV – Lab Level 2'];
  const PHARMA_ROOMS = ['Chemistry Lab (CL)','Multidisciplinary Pharma Lab (MDLP)','Pharmaceutical Lab 1 (PL1)','Pharmaceutical Lab 2 (PL2)'];
  const ROOMS_BY_TYPE = { research: RESEARCH_ROOMS, csl: CSL_ROOMS, pharma: PHARMA_ROOMS };
  const BLOCK_ICONS = { class:'📚', practical:'🔬', maintenance:'🔧', reserved:'🔒', exam:'📝', event:'🎓' };
  const BLOCK_LABELS = { class:'Class', practical:'Practical', maintenance:'Maintenance', reserved:'Reserved', exam:'Exam/OSCE', event:'Event' };
  const TYPE_LABELS = { research:'Research', csl:'CSL', pharma:'Pharma' };
  const TYPE_COLORS = { research:'#202734', csl:'#5d7c86', pharma:'#2a7782' };
  const DURATIONS = [{m:60,l:'1 hr'},{m:90,l:'1.5 hrs'},{m:120,l:'2 hrs'},{m:150,l:'2.5 hrs'},{m:180,l:'3 hrs'},{m:210,l:'3.5 hrs'},{m:240,l:'4 hrs'},{m:300,l:'5 hrs'},{m:360,l:'6 hrs'},{m:480,l:'8 hrs (full day)'}];

  const pad2 = n => String(n).padStart(2,'0');
  const toMin = t => { if (!t) return 0; const [h,m] = t.split(':').map(Number); return h*60+m; };
  const fromMin = m => `${pad2(Math.floor(m/60))}:${pad2(m%60)}`;
  const esc = s => String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');

  let SC = { step:1, date:'', labType:'', rooms:[], durMins:60, startTime:'', endTime:'' };
  let scYear, scMonth;

  function getRecurDates(base, rec, max = 4) {
    const dates = [base];
    if (rec === 'none') return dates;
    const dt = new Date(base + 'T00:00:00'), step = rec === 'weekly' ? 7 : 14;
    for (let i = 1; i < max; i++) { const nx = new Date(dt); nx.setDate(dt.getDate() + step*i); dates.push(`${nx.getFullYear()}-${pad2(nx.getMonth()+1)}-${pad2(nx.getDate())}`); }
    return dates;
  }
  function getBlocksByDate() {
    const map = {};
    ALL_BLOCKS.forEach(b => getRecurDates(b.date, b.recurring, 4).forEach(ds => { (map[ds] = map[ds] || []).push(b); }));
    return map;
  }
  function getBookingsByDate() {
    const map = {};
    ALL_BOOKINGS.forEach(b => { (map[b.date] = map[b.date] || []).push(b); });
    return map;
  }

  // ── Steps ──
  window.scGoStep = function (n) {
    SC.step = n;
    for (let i = 1; i <= 4; i++) {
      const el = document.getElementById('ss-'+i);
      if (el) { el.classList.remove('active','done'); if (i < n) el.classList.add('done'); else if (i === n) el.classList.add('active'); }
      const line = document.getElementById('sl-'+i);
      if (line) line.classList.toggle('done', i < n);
    }
    for (let i = 1; i <= 4; i++) { const p = document.getElementById('sp-'+i); if (p) p.style.display = (i === n) ? 'flex' : 'none'; }
    if (n === 1) { const p = document.getElementById('sp-1'); if (p) { p.style.display = 'flex'; p.classList.add('s-panel-empty'); } }
  };

  function scSelectDate(dateStr) {
    document.querySelectorAll('#sc-grid .selected-day').forEach(c => c.classList.remove('selected-day'));
    document.querySelectorAll('#sc-grid .sc-cal-day[data-date="'+dateStr+'"]').forEach(c => c.classList.add('selected-day'));
    SC.date = dateStr; SC.labType=''; SC.rooms=[]; SC.startTime=''; SC.endTime='';
    const d = new Date(dateStr + 'T00:00:00');
    const fmt = d.toLocaleDateString('en-MY', { weekday:'short', day:'numeric', month:'short', year:'numeric' });
    document.getElementById('sp2-date-chip').textContent = fmt;
    document.getElementById('sp3-date-chip').textContent = fmt;
    document.querySelectorAll('.sched-lab-card').forEach(c => c.classList.remove('selected'));
    document.getElementById('sc-rooms-section').style.display = 'none';
    const nb = document.getElementById('sc-step2-next'); nb.disabled = true; nb.style.opacity = '.5';
    scRenderExistingBlocks(dateStr);
    scGoStep(2);
  }

  function scRenderExistingBlocks(dateStr) {
    const blocks = getBlocksByDate()[dateStr] || [];
    const panel = document.getElementById('sc-existing');
    const list = document.getElementById('sc-existing-list');
    const hdr = document.getElementById('sc-existing-hdr');
    if (!blocks.length) { panel.style.display = 'none'; return; }
    const d = new Date(dateStr + 'T00:00:00');
    const fmt = d.toLocaleDateString('en-MY', { day:'numeric', month:'short' });
    hdr.textContent = blocks.length + ' block' + (blocks.length > 1 ? 's' : '') + ' on ' + fmt;
    list.innerHTML = blocks.map(b => `
      <div class="block-entry" style="padding:8px 12px;">
        <div class="block-entry-icon" style="width:26px;height:26px;font-size:.75rem;">${BLOCK_ICONS[b.category] || '🚫'}</div>
        <div class="block-entry-body">
          <div class="block-entry-title" style="font-size:.77rem;">${esc(b.title)}</div>
          <div class="block-entry-meta">${esc(b.start)}–${esc(b.end)} · ${(b.rooms||[]).join(', ')}</div>
        </div>
        <button class="btn btn-danger btn-xs" onclick="scDeleteBlock('${b.id}')">✕</button>
      </div>`).join('');
    panel.style.display = '';
  }

  window.scSelectLab = function (type) {
    SC.labType = type; SC.rooms = [];
    document.querySelectorAll('.sched-lab-card').forEach(c => c.classList.toggle('selected', c.dataset.type === type));
    const rooms = ROOMS_BY_TYPE[type] || [];
    document.getElementById('sc-rooms-grid').innerHTML = rooms.map(r => `
      <label class="sched-room-item"><input type="checkbox" value="${esc(r)}" onchange="scToggleRoom(this)"/> ${esc(r)}</label>`).join('');
    document.getElementById('sc-rooms-section').style.display = '';
    const nb = document.getElementById('sc-step2-next'); nb.disabled = true; nb.style.opacity = '.5';
  };

  window.scToggleRoom = function (cb) {
    if (cb.checked) { if (!SC.rooms.includes(cb.value)) SC.rooms.push(cb.value); }
    else SC.rooms = SC.rooms.filter(r => r !== cb.value);
    cb.closest('.sched-room-item').classList.toggle('checked', cb.checked);
    const nb = document.getElementById('sc-step2-next');
    nb.disabled = SC.rooms.length === 0; nb.style.opacity = SC.rooms.length ? '1' : '.5';
  };

  window.scGoStep3 = function () {
    if (!SC.rooms.length) { showToast('Select at least one room.', ''); return; }
    document.getElementById('sc-dur-pills').innerHTML = DURATIONS.map(d => `<button class="dur-btn${d.m===SC.durMins?' active':''}" onclick="scSetDur(${d.m},this)">${d.l}</button>`).join('');
    scGoStep(3);
    scRenderTimeGrid();
  };

  window.scSetDur = function (mins, btn) {
    SC.durMins = mins; SC.startTime=''; SC.endTime='';
    document.querySelectorAll('.dur-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    scRenderTimeGrid();
    document.getElementById('sc-sel-bar').style.display = 'none';
  };

  function scRenderTimeGrid() {
    const grid = document.getElementById('sc-timegrid');
    if (!grid) return;
    const slots = [];
    for (let h = 7; h <= 21; h++) for (let mi of [0,30]) slots.push(h*60+mi);

    const occ = [];
    ALL_BOOKINGS.forEach(bk => {
      if (bk.date !== SC.date || bk.status === 'rejected' || bk.status === 'cancelled') return;
      const bkRooms = [bk.rooms].flat();
      if (!SC.rooms.some(r => bkRooms.includes(r))) return;
      occ.push({ s: toMin(bk.start), e: toMin(bk.end), kind: 'booked' });
    });
    const byBlock = getBlocksByDate();
    (byBlock[SC.date] || []).forEach(bl => {
      if (!SC.rooms.some(r => (bl.rooms||[]).includes(r))) return;
      occ.push({ s: toMin(bl.start), e: toMin(bl.end), kind: 'blocked' });
    });

    const slotKind = sm => { for (const o of occ) if (sm >= o.s && sm < o.e) return o.kind; return 'available'; };
    const canFit = sm => { for (let t = sm; t < sm + SC.durMins; t += 30) if (slotKind(t) !== 'available') return false; return true; };
    const inRange = sm => { if (!SC.startTime) return false; const s = toMin(SC.startTime), e = toMin(SC.endTime); return sm >= s && sm < e; };

    let html = '<div class="tg-header-row"><div class="tg-corner">Room / Lab</div>';
    slots.forEach(sm => { html += `<div class="tg-time-hdr">${pad2(Math.floor(sm/60))}<br>${pad2(sm%60)}</div>`; });
    html += '</div>';

    SC.rooms.forEach(room => {
      html += `<div class="tg-row"><div class="tg-room-lbl">${esc(room)}</div>`;
      slots.forEach(sm => {
        const kind = slotKind(sm);
        const isStart = SC.startTime && toMin(SC.startTime) === sm;
        const isRange = inRange(sm) && !isStart;
        const clickable = kind === 'available' && canFit(sm);
        let cls = 'slot-btn', txt = '', title = '';
        if (isStart) { cls += ' slot-selected'; txt = '▶'; title = 'Selected start'; }
        else if (isRange) { cls += ' slot-range'; title = 'In duration range'; }
        else if (kind === 'blocked') { cls += ' slot-blocked'; txt = '🚫'; title = 'Blocked'; }
        else if (kind === 'booked') { cls += ' slot-booked'; txt = '✕'; title = 'Booked'; }
        else if (!clickable) { title = 'Cannot fit duration here'; }
        else { title = `Select ${fromMin(sm)}–${fromMin(sm+SC.durMins)}`; }
        const click = clickable ? `onclick="scSelectSlot(${sm})"` : '';
        const style = (!clickable && kind === 'available') ? 'style="opacity:.35;cursor:not-allowed"' : '';
        html += `<div class="tg-cell"><button class="${cls}" ${click} title="${title}" ${style}>${txt}</button></div>`;
      });
      html += '</div>';
    });
    grid.innerHTML = html;
  }

  window.scSelectSlot = function (sm) {
    SC.startTime = fromMin(sm);
    SC.endTime = fromMin(sm + SC.durMins);
    scRenderTimeGrid();
    const durLabel = (DURATIONS.find(d => d.m === SC.durMins) || {}).l || SC.durMins + 'min';
    document.getElementById('sc-sel-text').textContent = `⏰ ${SC.startTime} – ${SC.endTime}  ·  ${durLabel}`;
    document.getElementById('sc-sel-bar').style.display = 'flex';
  };

  window.scGoStep4 = function () {
    if (!SC.startTime) { showToast('Please select a time slot.', ''); return; }
    const d = new Date(SC.date + 'T00:00:00');
    const fmt = d.toLocaleDateString('en-MY', { weekday:'long', day:'numeric', month:'long', year:'numeric' });
    const durLabel = (DURATIONS.find(d => d.m === SC.durMins) || {}).l || SC.durMins + 'min';
    document.getElementById('sp4-summary').innerHTML =
      `<strong>📋 Block Summary</strong>📅 ${fmt}<br>⏰ ${SC.startTime} – ${SC.endTime} (${durLabel})<br>🏷️ ${TYPE_LABELS[SC.labType]} · ${SC.rooms.join(', ')}`;
    document.getElementById('sc-blk-cat').value = 'class';
    document.getElementById('sc-blk-recur').value = 'none';
    document.getElementById('sc-blk-title').value = '';
    document.getElementById('sc-blk-pic').value = '';
    document.getElementById('sc-blk-notes').value = '';
    scGoStep(4);
  };

  window.scSaveBlock = function () {
    const title = document.getElementById('sc-blk-title').value.trim();
    if (!title) { showToast('Please enter a title/event name.', ''); document.getElementById('sc-blk-title').focus(); return; }
    const blk = {
      id: `BLK-${String(blockIdCounter++).padStart(3,'0')}`,
      type: SC.labType,
      category: document.getElementById('sc-blk-cat').value,
      title,
      pic: document.getElementById('sc-blk-pic').value.trim(),
      date: SC.date,
      start: SC.startTime,
      end: SC.endTime,
      rooms: SC.rooms.slice(),
      recurring: document.getElementById('sc-blk-recur').value,
      notes: document.getElementById('sc-blk-notes').value.trim(),
    };
    ALL_BLOCKS.push(blk);
    renderBlockLists(); renderScCal();
    showToast(`Block "${title}" saved (UI demo — no DB).`, 'success');
    SC = { step:1, date:'', labType:'', rooms:[], durMins:60, startTime:'', endTime:'' };
    document.querySelectorAll('#sc-grid .selected-day').forEach(c => c.classList.remove('selected-day'));
    document.getElementById('sc-existing').style.display = 'none';
    document.getElementById('sc-sel-bar').style.display = 'none';
    scGoStep(1);
  };

  window.scDeleteBlock = function (id) {
    uiConfirm({
      title: 'Remove this block?',
      message: 'This scheduled block will be removed. (UI only — no DB)',
      variant: 'danger',
      confirmText: 'Yes, remove',
      onConfirm: function () {
        ALL_BLOCKS = ALL_BLOCKS.filter(b => b.id !== id);
        renderBlockLists(); renderScCal();
        if (SC.date) scRenderExistingBlocks(SC.date);
        showToast('Block removed (UI demo).', 'danger');
      }
    });
  };

  // ── Block lists ──
  function blockEntryHtml(blk) {
    const recur = blk.recurring !== 'none' ? `<span class="recurring-badge">🔄 ${blk.recurring === 'weekly' ? 'Weekly' : 'Bi-weekly'}</span>` : '';
    return `<div class="block-entry">
      <div class="block-entry-icon">${BLOCK_ICONS[blk.category] || '🚫'}</div>
      <div class="block-entry-body">
        <div class="block-entry-title">${esc(blk.title)}${recur}</div>
        <div class="block-entry-meta"><span class="lab-type-tag lab-type-${esc(blk.type)}">${TYPE_LABELS[blk.type]}</span> ${BLOCK_LABELS[blk.category] || blk.category} · ${esc(blk.date)} · ${esc(blk.start)}–${esc(blk.end)}${blk.pic ? ' · ' + esc(blk.pic) : ''}</div>
        <div class="block-entry-rooms">${(blk.rooms||[]).join(', ')}</div>
        ${blk.notes ? `<div style="font-size:.71rem;color:var(--text-light);margin-top:2px;font-style:italic;">${esc(blk.notes)}</div>` : ''}
      </div>
      <div><button class="btn btn-danger btn-xs" onclick="scDeleteBlock('${blk.id}')">✕ Remove</button></div>
    </div>`;
  }
  function renderBlockList(id, blocks) {
    const el = document.getElementById(id); if (!el) return;
    el.innerHTML = blocks.length ? blocks.map(b => blockEntryHtml(b)).join('') : '<div class="empty-state"><p><strong>No blocks scheduled</strong><br>All slots open for booking</p></div>';
  }
  function renderBlockLists() {
    const res = ALL_BLOCKS.filter(b => b.type === 'research'), csl = ALL_BLOCKS.filter(b => b.type === 'csl'), pha = ALL_BLOCKS.filter(b => b.type === 'pharma');
    renderBlockList('block-list-all', ALL_BLOCKS); renderBlockList('block-list-research', res); renderBlockList('block-list-csl', csl); renderBlockList('block-list-pharma', pha);
    document.getElementById('btc-all').textContent = ALL_BLOCKS.length;
    document.getElementById('btc-research').textContent = res.length;
    document.getElementById('btc-csl').textContent = csl.length;
    document.getElementById('btc-pharma').textContent = pha.length;
  }

  // ── Schedule calendar ──
  function renderScCal() {
    const grid = document.getElementById('sc-grid'); if (!grid) return;
    document.getElementById('sc-label').textContent = MONTHS[scMonth] + ' ' + scYear;
    grid.innerHTML = '';
    const byDate = getBookingsByDate(), byBlock = getBlocksByDate();
    const first = new Date(scYear, scMonth, 1).getDay();
    const dInM = new Date(scYear, scMonth+1, 0).getDate();
    const dInP = new Date(scYear, scMonth, 0).getDate();
    const today = new Date();
    function mkCell(d, cy, cm, other) {
      const ds = `${cy}-${pad2(cm+1)}-${pad2(d)}`;
      const isToday = d === today.getDate() && cm === today.getMonth() && cy === today.getFullYear();
      const isSel = ds === SC.date;
      const cell = document.createElement('div');
      cell.className = 'sc-cal-day' + (other ? ' other-month' : '') + (isToday ? ' today' : '') + (isSel ? ' selected-day' : '');
      cell.dataset.date = ds;
      const num = document.createElement('div'); num.className = 'sc-cal-day-num'; num.textContent = d; cell.appendChild(num);
      const bks = byDate[ds] || [], blks = byBlock[ds] || [];
      if (bks.length || blks.length) {
        const dots = document.createElement('div'); dots.className = 'sc-cal-dots';
        bks.slice(0,4).forEach(b => { const dot = document.createElement('span'); dot.className = 'sc-cal-dot' + (b.status === 'pending' ? ' pending' : ''); dot.style.background = TYPE_COLORS[b.lab_type] || TYPE_COLORS[b.type] || '#202734'; dots.appendChild(dot); });
        blks.slice(0,2).forEach(() => { const dot = document.createElement('span'); dot.className = 'sc-cal-dot'; dot.style.background = '#666759'; dots.appendChild(dot); });
        cell.appendChild(dots);
      }
      if (!other) cell.addEventListener('click', () => scSelectDate(ds));
      return cell;
    }
    for (let i = first-1; i >= 0; i--) grid.appendChild(mkCell(dInP-i, scMonth===0?scYear-1:scYear, scMonth===0?11:scMonth-1, true));
    for (let d = 1; d <= dInM; d++) grid.appendChild(mkCell(d, scYear, scMonth, false));
    const total = first + dInM, rem = total % 7 === 0 ? 0 : 7 - (total % 7);
    for (let d = 1; d <= rem; d++) grid.appendChild(mkCell(d, scMonth===11?scYear+1:scYear, scMonth===11?0:scMonth+1, true));
  }

  window.switchTab = function (btn, tabId) {
    btn.closest('.tab-bar').querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.tab-panel').forEach(p => { if (p.id.startsWith('stab-')) p.classList.remove('active'); });
    document.getElementById(tabId)?.classList.add('active');
  };

  // ── Init ──
  document.getElementById('sc-prev').addEventListener('click', () => { scMonth--; if (scMonth < 0) { scMonth = 11; scYear--; } renderScCal(); });
  document.getElementById('sc-next').addEventListener('click', () => { scMonth++; if (scMonth > 11) { scMonth = 0; scYear++; } renderScCal(); });

  const n = new Date(); scYear = n.getFullYear(); scMonth = n.getMonth();
  renderScCal();
  renderBlockLists();
  scGoStep(1);
})();
</script>
