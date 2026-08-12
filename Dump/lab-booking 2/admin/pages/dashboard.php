<?php
/**
 * pages/dashboard.php
 * Overview — stat cards · Month/Week/Day booking calendar (filterable by
 * category) · Latest Submissions + Upcoming Blocks & Classes.
 * Data sourced from the shared data files so every page stays in sync.
 */
require_once __DIR__ . '/../data/bookings.php';
require_once __DIR__ . '/../data/reports.php';

$ALL_BOOKINGS = $BOOKINGS;       // ref,name,id,lab_type,type_label,lab,date,start,end,purpose,status,audit
$ALL_BLOCKS   = $BLOCKED_SLOTS;  // id,type,category,title,pic,date,start,end,rooms[],recurring,notes,lab,reason

// ── Lookups ──
$BLOCK_ICONS  = ['class'=>'📚','practical'=>'🔬','maintenance'=>'🔧','reserved'=>'🔒','exam'=>'📝','event'=>'🎓'];
$BLOCK_LABELS = ['class'=>'Class','practical'=>'Practical','maintenance'=>'Maintenance','reserved'=>'Reserved','exam'=>'Exam/OSCE','event'=>'Event'];
$TYPE_LABELS  = ['research'=>'Research','csl'=>'CSL','pharma'=>'Pharma'];
$TYPE_COLORS  = ['research'=>'#202734','csl'=>'#5d7c86','pharma'=>'#2a7782','block'=>'#666759'];

// ── Stats ──
$statTotal    = count($ALL_BOOKINGS);
$statPending  = bookingCount($ALL_BOOKINGS, 'pending');
$statResearch = bookingCount($ALL_BOOKINGS, null, 'research');
$statCsl      = bookingCount($ALL_BOOKINGS, null, 'csl');
$statPharma   = bookingCount($ALL_BOOKINGS, null, 'pharma');
$statBlocks   = count($ALL_BLOCKS);

// ── Recurrence expansion (max 4) ──
$recurDates = function (string $base, string $rec, int $max = 4): array {
  $dates = [$base];
  if ($rec === 'none') return $dates;
  $step = $rec === 'weekly' ? 7 : 14;
  $t = strtotime($base);
  for ($i = 1; $i < $max; $i++) {
    $dates[] = date('Y-m-d', strtotime('+' . ($step * $i) . ' days', $t));
  }
  return $dates;
};

// ── Events grouped by date (for the JS calendar) ──
$bookingsByDate = [];
foreach ($ALL_BOOKINGS as $b) { $bookingsByDate[$b['date']][] = $b; }

$blocksByDate = [];
foreach ($ALL_BLOCKS as $bl) {
  foreach ($recurDates($bl['date'], $bl['recurring'], 4) as $ds) { $blocksByDate[$ds][] = $bl; }
}

// Event payload for the JS calendar (Month / Week / Day + category filter)
$DASH_EVENTS = [];
foreach (array_unique(array_merge(array_keys($bookingsByDate), array_keys($blocksByDate))) as $ds) {
  $DASH_EVENTS[$ds] = [
    'bookings' => array_map(fn($b) => [
      'ref' => $b['ref'], 'type_label' => $b['type_label'], 'lab_type' => $b['lab_type'],
      'name' => $b['name'], 'status' => $b['status'],
      'start' => $b['start'], 'end' => $b['end'], 'lab' => $b['lab'], 'purpose' => $b['purpose'] ?? '',
    ], $bookingsByDate[$ds] ?? []),
    'blocks' => array_map(fn($bl) => [
      'category' => $bl['category'], 'title' => $bl['title'], 'type' => $bl['type'],
      'start' => $bl['start'], 'end' => $bl['end'], 'lab' => $bl['lab'], 'pic' => $bl['pic'] ?? '',
    ], $blocksByDate[$ds] ?? []),
  ];
}

// ── Upcoming blocks (date >= today OR recurring, first 5) ──
$todayStr = date('Y-m-d');
$upcoming = array_slice(array_values(array_filter(
  $ALL_BLOCKS,
  fn($b) => $b['date'] >= $todayStr || $b['recurring'] !== 'none'
)), 0, 5);

// ── Latest submissions (newest first, by created-audit timestamp) ──
$dashSubmittedAt = function (array $b): string {
  foreach ($b['audit'] ?? [] as $a) {
    if (($a['type'] ?? '') === 'created') return $a['at'] ?? '';
  }
  return $b['audit'][0]['at'] ?? '';
};
$latest = $ALL_BOOKINGS;
usort($latest, fn($a, $b) => strcmp($dashSubmittedAt($b), $dashSubmittedAt($a)));
$latest = array_slice($latest, 0, 6);
?>

<style>
  /* ── Dashboard creative restyle (scoped to .dash) ── */
  .dash-hero {
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 22px;
    flex-wrap: wrap;
    background: linear-gradient(125deg, #1b232f 0%, #244049 52%, #2f6d78 100%);
    border-radius: var(--r-xl);
    padding: 26px 30px;
    margin-bottom: 22px;
    color: #fff;
    box-shadow: 0 16px 38px rgba(20, 45, 60, .22);
  }
  .dash-hero::after {
    content: '';
    position: absolute;
    right: -90px;
    top: -110px;
    width: 300px;
    height: 300px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(124, 195, 204, .30) 0%, rgba(124, 195, 204, 0) 70%);
    pointer-events: none;
  }
  .dash-hero-text { position: relative; z-index: 1; }
  .dash-hero-eyebrow {
    display: inline-block;
    font-size: .66rem;
    font-weight: 700;
    letter-spacing: .09em;
    text-transform: uppercase;
    background: rgba(255, 255, 255, .14);
    border: 1px solid rgba(255, 255, 255, .24);
    padding: 5px 12px;
    border-radius: 999px;
    margin-bottom: 12px;
  }
  .dash-hero h1 {
    font-family: var(--font-serif);
    font-size: 1.55rem;
    font-weight: 700;
    color: #fff;
    margin: 0;
    line-height: 1.15;
  }
  .dash-hero p { margin: 8px 0 0; font-size: .85rem; color: rgba(255, 255, 255, .85); }
  .dash-hero-meta { position: relative; z-index: 1; }
  .dash-hero-pending {
    background: rgba(255, 255, 255, .12);
    border: 1px solid rgba(255, 255, 255, .24);
    border-radius: var(--r-lg);
    padding: 14px 24px;
    text-align: center;
    min-width: 128px;
  }
  .dash-hero-pending .num {
    display: block;
    font-family: var(--font-serif);
    font-size: 2rem;
    font-weight: 700;
    line-height: 1;
    color: var(--teal-bright);
  }
  .dash-hero-pending .lbl {
    display: block;
    font-size: .66rem;
    color: rgba(255, 255, 255, .82);
    margin-top: 7px;
    letter-spacing: .05em;
    text-transform: uppercase;
  }

  /* Category-accented stat cards */
  .dash .stat-grid { grid-template-columns: repeat(auto-fill, minmax(196px, 1fr)); }
  .dash .stat-card {
    position: relative;
    overflow: hidden;
    padding: 18px 18px 15px 22px;
    border-radius: var(--r-lg);
    transition: transform .16s ease, box-shadow .16s ease;
  }
  .dash .stat-card::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: linear-gradient(180deg, var(--g1), var(--g2));
  }
  .dash .stat-card:hover { transform: translateY(-3px); box-shadow: var(--shadow); }
  .dash .stat-icon {
    width: 44px;
    height: 44px;
    border-radius: 13px;
    font-size: 1.2rem;
    background: linear-gradient(135deg, var(--g1), var(--g2));
    box-shadow: 0 6px 16px rgba(32, 39, 52, .20);
  }
  .dash .stat-num { font-size: 1.95rem; margin-top: 2px; }
  .dash .stat-cap { font-size: .68rem; color: var(--text-light); margin-top: 3px; }

  .dash .stat-card.c-total    { --g1: #5cb0ba; --g2: #3f97a2; }
  .dash .stat-card.c-research { --g1: #39424f; --g2: #202734; }
  .dash .stat-card.c-csl      { --g1: #709099; --g2: #5d7c86; }
  .dash .stat-card.c-pharma   { --g1: #3a96a3; --g2: #2a7782; }
  .dash .stat-card.c-block    { --g1: #828373; --g2: #666759; }

  /* Section cards */
  .dash .card { border-radius: var(--r-xl); }
  .dash .card-header { background: linear-gradient(180deg, var(--off-white), #fff); }

  /* ── Calendar control bar ── */
  .dash-cal-bar { flex-wrap: wrap; gap: 12px; }
  .dash-cal-navgroup { display: flex; align-items: center; gap: 6px; }
  .dash-navbtn {
    background: var(--white); border: 1px solid var(--border);
    border-radius: var(--r-sm); min-width: 30px; height: 30px; padding: 0 10px;
    font-size: .95rem; font-weight: 700; color: var(--navy); cursor: pointer;
    transition: all .14s; line-height: 1;
  }
  .dash-navbtn:hover { background: var(--navy-light); border-color: var(--teal); }
  .dash-navbtn.dash-today { font-size: .75rem; }
  .dash-cal-tools { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
  .dash-viewtabs {
    display: inline-flex; background: var(--off-white);
    border: 1px solid var(--border); border-radius: var(--r); padding: 2px;
  }
  .dash-viewtabs button {
    border: none; background: transparent; padding: 5px 13px; border-radius: 6px;
    font-size: .76rem; font-weight: 600; color: var(--text-mid); cursor: pointer; transition: all .14s;
  }
  .dash-viewtabs button:hover { color: var(--navy); }
  .dash-viewtabs button.active { background: var(--white); color: var(--navy); box-shadow: var(--shadow-xs); }
  .dash-catfilter { width: 158px; padding: 6px 30px 6px 10px; font-size: .78rem; }

  /* Legend */
  .dash-legend { display: flex; gap: 16px; flex-wrap: wrap; font-size: .72rem; color: var(--text-mid); margin-bottom: 14px; }
  .dash-legend span { display: inline-flex; align-items: center; gap: 6px; }
  .dash-legend i { width: 9px; height: 9px; border-radius: 50%; display: inline-block; }

  /* "+n more" indicator in calendar cells */
  .cal-day-more { font-size: .58rem; color: var(--text-light); font-weight: 700; margin-top: 1px; }

  /* ── Week view ── */
  .dash-week { display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; }
  .dash-week-col {
    border: 1px solid var(--border-light); border-radius: var(--r);
    overflow: hidden; min-height: 150px; display: flex; flex-direction: column;
  }
  .dash-week-col.today { border-color: var(--teal); box-shadow: 0 0 0 1px var(--teal) inset; }
  .dash-week-head { padding: 7px 4px; text-align: center; background: var(--off-white); border-bottom: 1px solid var(--border-light); }
  .dash-week-head .dow { font-size: .6rem; text-transform: uppercase; letter-spacing: .04em; color: var(--text-light); font-weight: 700; }
  .dash-week-head .dnum { font-size: 1.05rem; font-weight: 700; color: var(--navy); }
  .dash-week-col.today .dnum { color: var(--teal-dark); }
  .dash-week-body { padding: 6px; display: flex; flex-direction: column; gap: 3px; cursor: pointer; flex: 1; }

  /* ── Day view ── */
  .dash-day-list { display: flex; flex-direction: column; gap: 10px; }
  .dash-day-empty { padding: 34px; text-align: center; color: var(--text-light); font-size: .85rem; }
  .dash-day-item {
    display: flex; gap: 14px; padding: 13px 15px;
    border: 1px solid var(--border-light); border-left-width: 4px; border-radius: var(--r); background: var(--white);
  }
  .dash-day-time { font-size: .76rem; font-weight: 700; color: var(--navy); white-space: nowrap; min-width: 96px; }
  .dash-day-main { flex: 1; min-width: 0; }
  .dash-day-kind { font-size: .64rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; }
  .dash-day-title { font-weight: 600; font-size: .85rem; color: var(--text); margin-top: 2px; }
  .dash-day-sub { font-size: .76rem; color: var(--text-mid); margin-top: 2px; }

  /* ── Bottom two-column section ── */
  .dash-cols { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
  .dash-cols .card { margin-bottom: 0; }

  /* Latest submissions feed */
  .dash-sub-row { display: flex; gap: 12px; padding: 12px 20px; border-bottom: 1px solid var(--border-light); }
  .dash-sub-row:last-child { border-bottom: none; }
  .dash-sub-ava {
    width: 34px; height: 34px; border-radius: 50%; color: #fff; font-weight: 700; font-size: .85rem;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
  }
  .dash-sub-top { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
  .dash-sub-name { font-weight: 600; font-size: .84rem; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .dash-sub-meta { font-size: .74rem; color: var(--text-mid); margin-top: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .dash-sub-foot { font-size: .7rem; color: var(--text-light); margin-top: 4px; display: flex; align-items: center; gap: 7px; flex-wrap: wrap; }
  .dash-sub-ref { font-family: monospace; font-weight: 600; color: var(--navy-mid); }
  .dash-sub-when { margin-left: auto; white-space: nowrap; }

  @media (max-width: 960px) { .dash-cols { grid-template-columns: 1fr; } }
  @media (max-width: 640px) {
    .dash-hero { padding: 22px; }
    .dash-hero-meta, .dash-hero-pending { width: 100%; }
    .dash-week { grid-template-columns: repeat(7, minmax(78px, 1fr)); overflow-x: auto; }
  }
</style>

<div class="dash">
  <!-- ── Hero ── -->
  <div class="dash-hero">
    <div class="dash-hero-text">
      <span class="dash-hero-eyebrow">UniKLAB RCMP · Admin</span>
      <h1>Welcome back, Administrator</h1>
      <p>Here's your lab activity overview for <?= date('l, j F Y') ?>.</p>
    </div>
    <div class="dash-hero-meta">
      <div class="dash-hero-pending">
        <span class="num"><?= $statPending ?></span>
        <span class="lbl">Pending review</span>
      </div>
    </div>
  </div>

  <!-- ── Stat Cards ── -->
  <div class="stat-grid">
    <div class="stat-card c-total">
      <div class="stat-card-top"><div class="stat-icon">📋</div></div>
      <div class="stat-num"><?= $statTotal ?></div>
      <div class="stat-label">Total Bookings</div>
      <div class="stat-cap">All categories</div>
    </div>
    <div class="stat-card c-research">
      <div class="stat-card-top"><div class="stat-icon">🧪</div></div>
      <div class="stat-num"><?= $statResearch ?></div>
      <div class="stat-label">Research Labs</div>
      <div class="stat-cap">AZ + Avicenna</div>
    </div>
    <div class="stat-card c-csl">
      <div class="stat-card-top"><div class="stat-icon">🏥</div></div>
      <div class="stat-num"><?= $statCsl ?></div>
      <div class="stat-label">CSL Labs</div>
      <div class="stat-cap">CSL1 &amp; CSL2</div>
    </div>
    <div class="stat-card c-pharma">
      <div class="stat-card-top"><div class="stat-icon">⚗️</div></div>
      <div class="stat-num"><?= $statPharma ?></div>
      <div class="stat-label">Pharma Labs</div>
      <div class="stat-cap">CL · MDLP · PL1 · PL2</div>
    </div>
    <div class="stat-card c-block">
      <div class="stat-card-top"><div class="stat-icon">🚫</div></div>
      <div class="stat-num"><?= $statBlocks ?></div>
      <div class="stat-label">Blocked / Classes</div>
      <div class="stat-cap">Scheduled blocks</div>
    </div>
  </div>

  <!-- ── Booking Calendar (Month / Week / Day) ── -->
  <div class="card">
    <div class="card-header dash-cal-bar">
      <div class="dash-cal-navgroup">
        <button class="dash-navbtn" id="dashPrev" title="Previous">&#8249;</button>
        <button class="dash-navbtn dash-today" id="dashToday">Today</button>
        <button class="dash-navbtn" id="dashNext" title="Next">&#8250;</button>
        <span class="card-title" id="dashCalLabel" style="margin-left:6px;">—</span>
      </div>
      <div class="dash-cal-tools">
        <div class="dash-viewtabs" id="dashViewTabs">
          <button type="button" data-view="month" class="active">Month</button>
          <button type="button" data-view="week">Week</button>
          <button type="button" data-view="day">Day</button>
        </div>
        <select class="form-control dash-catfilter" id="dashCatFilter">
          <option value="">All categories</option>
          <option value="research">Research</option>
          <option value="csl">CSL</option>
          <option value="pharma">Pharma</option>
        </select>
      </div>
    </div>
    <div class="card-body">
      <div class="dash-legend">
        <span><i style="background:<?= $TYPE_COLORS['research'] ?>"></i>Research</span>
        <span><i style="background:<?= $TYPE_COLORS['csl'] ?>"></i>CSL</span>
        <span><i style="background:<?= $TYPE_COLORS['pharma'] ?>"></i>Pharma</span>
        <span><i style="background:<?= $TYPE_COLORS['block'] ?>"></i>Blocked</span>
      </div>
      <div id="dashCal"></div>
    </div>
  </div>

  <!-- ── Latest Submissions + Upcoming Blocks (side by side) ── -->
  <div class="dash-cols">

    <!-- Latest Submissions -->
    <div class="card">
      <div class="card-header">
        <span class="card-title">🆕 Latest Submissions</span>
        <a href="index.php?page=all-bookings" class="btn btn-outline btn-sm">View all →</a>
      </div>
      <div class="card-body-flush">
        <?php if (empty($latest)): ?>
          <div class="empty-state"><div class="empty-icon">📭</div><p>No submissions yet</p></div>
        <?php else: ?>
          <?php foreach ($latest as $bk):
            $subAt  = $dashSubmittedAt($bk);
            $subFmt = $subAt ? date('M j, H:i', strtotime($subAt)) : '—';
          ?>
          <div class="dash-sub-row">
            <div class="dash-sub-ava" style="background:<?= $TYPE_COLORS[$bk['lab_type']] ?? '#666' ?>;"><?= htmlspecialchars(strtoupper(substr($bk['name'], 0, 1))) ?></div>
            <div style="flex:1;min-width:0;">
              <div class="dash-sub-top">
                <span class="dash-sub-name"><?= htmlspecialchars($bk['name']) ?></span>
                <span class="badge badge-<?= htmlspecialchars($bk['status']) ?>"><?= ucfirst($bk['status']) ?></span>
              </div>
              <div class="dash-sub-meta">
                <span class="lab-type-tag lab-type-<?= $bk['lab_type'] ?>"><?= $TYPE_LABELS[$bk['lab_type']] ?? ucfirst($bk['lab_type']) ?></span>
                <?= htmlspecialchars($bk['lab']) ?>
              </div>
              <div class="dash-sub-foot">
                <span class="dash-sub-ref"><?= htmlspecialchars($bk['ref']) ?></span>
                · <?= htmlspecialchars($bk['date']) ?> · <?= htmlspecialchars($bk['start']) ?>–<?= htmlspecialchars($bk['end']) ?>
                <span class="dash-sub-when">⏱ <?= $subFmt ?></span>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Upcoming Blocks & Classes -->
    <div class="card">
      <div class="card-header">
        <span class="card-title">🚫 Upcoming Blocks &amp; Classes</span>
        <a href="index.php?page=schedule-block" class="btn btn-outline btn-sm">Manage →</a>
      </div>
      <div class="card-body-flush">
        <?php if (empty($upcoming)): ?>
          <div class="empty-state"><div class="empty-icon">🗓️</div><p>No upcoming blocks</p></div>
        <?php else: ?>
          <?php foreach ($upcoming as $blk): ?>
          <div style="display:flex;gap:12px;padding:12px 20px;border-bottom:1px solid var(--border-light);">
            <div style="width:34px;height:34px;border-radius:8px;background:#fdeceb;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;">
              <?= $BLOCK_ICONS[$blk['category']] ?? '🚫' ?>
            </div>
            <div style="flex:1;min-width:0;">
              <div style="font-weight:600;font-size:.84rem;color:var(--text);">
                <?= htmlspecialchars($blk['title']) ?>
                <?php if ($blk['recurring'] !== 'none'): ?>
                  <span class="badge badge-pending" style="margin-left:6px;">🔄 <?= $blk['recurring'] === 'weekly' ? 'Weekly' : 'Bi-weekly' ?></span>
                <?php endif; ?>
              </div>
              <div style="font-size:.74rem;color:var(--text-mid);margin-top:3px;">
                <span class="lab-type-tag lab-type-<?= $blk['type'] ?>"><?= $TYPE_LABELS[$blk['type']] ?? ucfirst($blk['type']) ?></span>
                <?= $BLOCK_LABELS[$blk['category']] ?? ucfirst($blk['category']) ?>
                · <?= htmlspecialchars($blk['date']) ?>
                · <?= htmlspecialchars($blk['start']) ?>–<?= htmlspecialchars($blk['end']) ?>
                <?= $blk['pic'] ? ' · ' . htmlspecialchars($blk['pic']) : '' ?>
              </div>
              <div style="font-size:.73rem;color:var(--text-mid);margin-top:2px;"><?= htmlspecialchars(implode(', ', $blk['rooms'])) ?></div>
              <?php if (!empty($blk['notes'])): ?>
                <div style="font-size:.71rem;color:var(--text-light);margin-top:2px;font-style:italic;"><?= htmlspecialchars($blk['notes']) ?></div>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div><!-- /.dash -->

<script>
const DASH_EVENTS      = <?= json_encode($DASH_EVENTS, JSON_UNESCAPED_UNICODE) ?>;
const DASH_BLOCK_LABELS = <?= json_encode($BLOCK_LABELS, JSON_UNESCAPED_UNICODE) ?>;
const DASH_TYPE_COLORS  = <?= json_encode($TYPE_COLORS, JSON_UNESCAPED_UNICODE) ?>;
const DASH_TYPE_LABELS  = <?= json_encode($TYPE_LABELS, JSON_UNESCAPED_UNICODE) ?>;

const DASH_MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
const DASH_MON_SHORT = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
const DASH_DOW = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];

let dashView = 'month';
let dashCat  = '';
let dashCursor = new Date(); dashCursor.setHours(0, 0, 0, 0);

function dPad(n) { return String(n).padStart(2, '0'); }
function dKey(dt) { return dt.getFullYear() + '-' + dPad(dt.getMonth() + 1) + '-' + dPad(dt.getDate()); }
function dEsc(s) { return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); }
function dCap(s) { s = String(s || ''); return s.charAt(0).toUpperCase() + s.slice(1); }
function dLab(lab) {
  return String(lab || '')
    .replace(/\s*\([^)]*\)/g, '')
    .replace(/\s*[—–]\s*/g, ' - ')
    .replace(/\s+/g, ' ')
    .trim();
}
function dIsToday(dt) {
  const t = new Date();
  return dt.getFullYear() === t.getFullYear() && dt.getMonth() === t.getMonth() && dt.getDate() === t.getDate();
}

// Unified, category-filtered, time-sorted item list for a date key
function dItems(ds) {
  const ev = DASH_EVENTS[ds];
  if (!ev) return [];
  const out = [];
  (ev.bookings || []).forEach(b => {
    if (dashCat && b.lab_type !== dashCat) return;
    out.push({
      kind: 'booking', type: b.lab_type, color: DASH_TYPE_COLORS[b.lab_type] || '#666',
      pending: b.status === 'pending', short: dLab(b.lab), full: b.lab, name: b.name,
      status: b.status, start: b.start, end: b.end, label: b.type_label, ref: b.ref, purpose: b.purpose
    });
  });
  (ev.blocks || []).forEach(bl => {
    if (dashCat && bl.type !== dashCat) return;
    out.push({
      kind: 'block', type: bl.type, color: DASH_TYPE_COLORS.block, pending: false,
      short: dLab(bl.lab), full: bl.lab, name: bl.title, status: 'blocked',
      start: bl.start, end: bl.end, label: DASH_BLOCK_LABELS[bl.category] || 'Block', pic: bl.pic
    });
  });
  out.sort((a, b) => String(a.start || '').localeCompare(String(b.start || '')));
  return out;
}

// Up to 4 chips, then "+n more"
function dChips(items) {
  let html = '<div class="cal-day-labs">';
  items.slice(0, 4).forEach(it => {
    const op = it.pending ? ';opacity:.6' : '';
    html += '<span class="cal-day-lab" style="background:' + it.color + op + '" title="'
          + dEsc(it.full + ' · ' + it.name) + '">' + dEsc(it.short || it.name) + '</span>';
  });
  if (items.length > 4) html += '<div class="cal-day-more">+' + (items.length - 4) + ' more</div>';
  html += '</div>';
  return html;
}

function dashMonth() {
  const y = dashCursor.getFullYear(), m = dashCursor.getMonth();
  document.getElementById('dashCalLabel').textContent = DASH_MONTHS[m] + ' ' + y;
  const firstDow = new Date(y, m, 1).getDay(), daysIn = new Date(y, m + 1, 0).getDate();
  let html = '<table class="cal-grid"><thead><tr>';
  DASH_DOW.forEach(d => html += '<th>' + d.slice(0, 2) + '</th>');
  html += '</tr></thead><tbody><tr>';
  let cell = 0;
  for (let i = 0; i < firstDow; i++) { html += '<td class="cal-cell"></td>'; cell++; }
  for (let day = 1; day <= daysIn; day++) {
    if (cell > 0 && cell % 7 === 0) html += '</tr><tr>';
    const dt = new Date(y, m, day), ds = dKey(dt), items = dItems(ds);
    html += '<td class="cal-cell"><div class="cal-day' + (dIsToday(dt) ? ' today' : '')
          + '" onclick="dashGoDay(\'' + ds + '\')" style="cursor:pointer;">';
    html += '<div class="cal-day-num">' + day + '</div>';
    if (items.length) html += dChips(items);
    html += '</div></td>';
    cell++;
  }
  while (cell % 7 !== 0) { html += '<td class="cal-cell"></td>'; cell++; }
  html += '</tr></tbody></table>';
  return html;
}

function dashWeek() {
  const start = new Date(dashCursor); start.setDate(start.getDate() - start.getDay());
  const end = new Date(start); end.setDate(end.getDate() + 6);
  document.getElementById('dashCalLabel').textContent = dRange(start, end);
  let html = '<div class="dash-week">';
  for (let i = 0; i < 7; i++) {
    const dt = new Date(start); dt.setDate(start.getDate() + i);
    const ds = dKey(dt), items = dItems(ds);
    html += '<div class="dash-week-col' + (dIsToday(dt) ? ' today' : '') + '">';
    html += '<div class="dash-week-head"><div class="dow">' + DASH_DOW[i] + '</div><div class="dnum">' + dt.getDate() + '</div></div>';
    html += '<div class="dash-week-body" onclick="dashGoDay(\'' + ds + '\')">';
    if (items.length) {
      items.slice(0, 4).forEach(it => {
        const op = it.pending ? ';opacity:.6' : '';
        html += '<span class="cal-day-lab" style="background:' + it.color + op + '" title="'
              + dEsc(it.full + ' · ' + it.name) + '">' + dEsc(it.short || it.name) + '</span>';
      });
      if (items.length > 4) html += '<div class="cal-day-more">+' + (items.length - 4) + ' more</div>';
    }
    html += '</div></div>';
  }
  html += '</div>';
  return html;
}

function dashDay() {
  const ds = dKey(dashCursor);
  document.getElementById('dashCalLabel').textContent =
    dashCursor.toLocaleDateString('en-MY', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
  const items = dItems(ds);
  if (!items.length) {
    return '<div class="dash-day-empty">No ' + (dashCat ? dEsc(DASH_TYPE_LABELS[dashCat]) + ' ' : '') + 'events on this date.</div>';
  }
  let html = '<div class="dash-day-list">';
  items.forEach(it => {
    html += '<div class="dash-day-item" style="border-left-color:' + it.color + '">';
    html += '<div class="dash-day-time">' + dEsc(it.start) + ' – ' + dEsc(it.end) + '</div>';
    html += '<div class="dash-day-main">';
    html += '<div class="dash-day-kind" style="color:' + it.color + '">' + (it.kind === 'block' ? '🚫 ' : '') + dEsc(it.label) + '</div>';
    html += '<div class="dash-day-title">' + dEsc(it.name)
          + (it.kind === 'booking' ? ' <span class="badge badge-' + dEsc(it.status) + '">' + dEsc(dCap(it.status)) + '</span>' : '') + '</div>';
    const extra = it.kind === 'booking' ? (it.purpose ? ' · ' + it.purpose : '') : (it.pic ? ' · ' + it.pic : '');
    html += '<div class="dash-day-sub">' + dEsc(it.full + extra) + '</div>';
    html += '</div></div>';
  });
  html += '</div>';
  return html;
}

function dRange(a, b) {
  if (a.getMonth() === b.getMonth()) {
    return a.getDate() + ' – ' + b.getDate() + ' ' + DASH_MON_SHORT[b.getMonth()] + ' ' + b.getFullYear();
  }
  return a.getDate() + ' ' + DASH_MON_SHORT[a.getMonth()] + ' – ' + b.getDate() + ' ' + DASH_MON_SHORT[b.getMonth()] + ' ' + b.getFullYear();
}

function dashRender() {
  document.querySelectorAll('#dashViewTabs button').forEach(btn => btn.classList.toggle('active', btn.dataset.view === dashView));
  const cal = document.getElementById('dashCal');
  cal.innerHTML = dashView === 'month' ? dashMonth() : dashView === 'week' ? dashWeek() : dashDay();
}

function dashGoDay(ds) { dashCursor = new Date(ds + 'T00:00:00'); dashView = 'day'; dashRender(); }

function dashNavigate(dir) {
  if (dashView === 'month') dashCursor.setMonth(dashCursor.getMonth() + dir);
  else if (dashView === 'week') dashCursor.setDate(dashCursor.getDate() + 7 * dir);
  else dashCursor.setDate(dashCursor.getDate() + dir);
  dashRender();
}

document.getElementById('dashPrev').addEventListener('click', () => dashNavigate(-1));
document.getElementById('dashNext').addEventListener('click', () => dashNavigate(1));
document.getElementById('dashToday').addEventListener('click', () => { dashCursor = new Date(); dashCursor.setHours(0, 0, 0, 0); dashRender(); });
document.querySelectorAll('#dashViewTabs button').forEach(btn => btn.addEventListener('click', () => { dashView = btn.dataset.view; dashRender(); }));
document.getElementById('dashCatFilter').addEventListener('change', e => { dashCat = e.target.value; dashRender(); });

dashRender();
</script>
