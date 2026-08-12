<?php
/**
 * pages/calendar.php
 * Booking calendar — Month / Week / Day views, filterable by lab type.
 * Layout mirrors the dashboard calendar; chips are colored by STATUS
 * (approved = green, pending = gold) with blocked slots in teal.
 */
require_once __DIR__ . '/../data/bookings.php';
require_once __DIR__ . '/../data/reports.php';

$TYPE_LABELS  = ['research'=>'Research','csl'=>'CSL','pharma'=>'Pharma'];
$BLOCK_LABELS = ['class'=>'Class','practical'=>'Practical','maintenance'=>'Maintenance','reserved'=>'Reserved','exam'=>'Exam/OSCE','event'=>'Event'];

// Traffic-light status colors used for calendar chips / day view
$CAL_COLORS = ['booking'=>'#2f8a52', 'pending'=>'#a07c1f', 'block'=>'#50a7b2'];

$calendarStats = [
  ['label'=>'Total Bookings', 'count'=>count($BOOKINGS),                 'icon'=>'📋', 'accent'=>'c-total'],
  ['label'=>'Approved',       'count'=>bookingCount($BOOKINGS,'approved'),'icon'=>'✅', 'accent'=>'c-approved'],
  ['label'=>'Pending',        'count'=>bookingCount($BOOKINGS,'pending'), 'icon'=>'⏳', 'accent'=>'c-pending'],
  ['label'=>'Blocked Slots',  'count'=>count($BLOCKED_SLOTS),            'icon'=>'🚫', 'accent'=>'c-block'],
];

// ── Recurrence expansion (same as dashboard) ──
$recurDates = function (string $base, string $rec, int $max = 4): array {
  $dates = [$base];
  if ($rec === 'none') return $dates;
  $step = $rec === 'weekly' ? 7 : 14;
  $t = strtotime($base);
  for ($i = 1; $i < $max; $i++) $dates[] = date('Y-m-d', strtotime('+' . ($step * $i) . ' days', $t));
  return $dates;
};

$bookingsByDate = [];
foreach ($BOOKINGS as $b) { $bookingsByDate[$b['date']][] = $b; }
$blocksByDate = [];
foreach ($BLOCKED_SLOTS as $bl) {
  foreach ($recurDates($bl['date'], $bl['recurring'] ?? 'none', 4) as $ds) { $blocksByDate[$ds][] = $bl; }
}

// Event payload for the JS calendar (Month / Week / Day + lab-type filter)
$CAL_EVENTS = [];
foreach (array_unique(array_merge(array_keys($bookingsByDate), array_keys($blocksByDate))) as $ds) {
  $CAL_EVENTS[$ds] = [
    'bookings' => array_map(fn($b) => [
      'ref'=>$b['ref'], 'type_label'=>$b['type_label'], 'lab_type'=>$b['lab_type'],
      'name'=>$b['name'], 'status'=>$b['status'],
      'start'=>$b['start'], 'end'=>$b['end'], 'lab'=>$b['lab'], 'purpose'=>$b['purpose'] ?? '',
    ], $bookingsByDate[$ds] ?? []),
    'blocks' => array_map(fn($bl) => [
      'category'=>$bl['category'], 'title'=>$bl['title'], 'type'=>$bl['type'],
      'start'=>$bl['start'], 'end'=>$bl['end'], 'lab'=>$bl['lab'] ?? '', 'reason'=>$bl['reason'] ?? '', 'pic'=>$bl['pic'] ?? '',
    ], $blocksByDate[$ds] ?? []),
  ];
}
?>

<style>
  /* ── Calendar View restyle (scoped to .calpage) ── */

  /* Hero */
  .cal-hero {
    position: relative; overflow: hidden;
    display: flex; align-items: center; justify-content: space-between;
    gap: 22px; flex-wrap: wrap;
    background: linear-gradient(125deg, #1b232f 0%, #244049 52%, #2f6d78 100%);
    border-radius: var(--r-xl); padding: 26px 30px; margin-bottom: 22px;
    color: #fff; box-shadow: 0 16px 38px rgba(20, 45, 60, .22);
  }
  .cal-hero::after {
    content: ''; position: absolute; right: -90px; top: -110px;
    width: 300px; height: 300px; border-radius: 50%;
    background: radial-gradient(circle, rgba(124, 195, 204, .30) 0%, rgba(124, 195, 204, 0) 70%);
    pointer-events: none;
  }
  .cal-hero-text { position: relative; z-index: 1; }
  .cal-hero-eyebrow {
    display: inline-block; font-size: .66rem; font-weight: 700;
    letter-spacing: .09em; text-transform: uppercase;
    background: rgba(255, 255, 255, .14); border: 1px solid rgba(255, 255, 255, .24);
    padding: 5px 12px; border-radius: 999px; margin-bottom: 12px;
  }
  .cal-hero h1 { font-family: var(--font-serif); font-size: 1.55rem; font-weight: 700; color: #fff; margin: 0; line-height: 1.15; }
  .cal-hero p { margin: 8px 0 0; font-size: .85rem; color: rgba(255, 255, 255, .85); max-width: 520px; }
  .cal-hero-meta { position: relative; z-index: 1; }
  .cal-hero-date {
    background: rgba(255, 255, 255, .12); border: 1px solid rgba(255, 255, 255, .24);
    border-radius: var(--r-lg); padding: 12px 22px; text-align: center; min-width: 110px;
  }
  .cal-hero-date .num { display: block; font-family: var(--font-serif); font-size: 2rem; font-weight: 700; line-height: 1; color: var(--teal-bright); }
  .cal-hero-date .lbl { display: block; font-size: .68rem; color: rgba(255, 255, 255, .82); margin-top: 6px; letter-spacing: .05em; text-transform: uppercase; }

  /* Accented stat cards */
  .calpage .stat-card { position: relative; overflow: hidden; padding: 18px 18px 15px 22px; border-radius: var(--r-lg); transition: transform .16s ease, box-shadow .16s ease; }
  .calpage .stat-card::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: linear-gradient(180deg, var(--g1), var(--g2)); }
  .calpage .stat-card:hover { transform: translateY(-3px); box-shadow: var(--shadow); }
  .calpage .stat-icon { width: 44px; height: 44px; border-radius: 13px; font-size: 1.2rem; background: linear-gradient(135deg, var(--g1), var(--g2)); box-shadow: 0 6px 16px rgba(32, 39, 52, .20); }
  .calpage .stat-num { font-size: 1.95rem; }
  .calpage .stat-card.c-total    { --g1: #3a4a55; --g2: #2d3744; }
  .calpage .stat-card.c-approved { --g1: #4bb472; --g2: #2f8a52; }
  .calpage .stat-card.c-pending  { --g1: #c79a3a; --g2: #a07c1f; }
  .calpage .stat-card.c-block    { --g1: #5cb0ba; --g2: #2a7782; }

  /* Calendar card + toolbar (mirrors the dashboard) */
  .calpage .card { border-radius: var(--r-xl); }
  .calpage .cal-toolbar { background: linear-gradient(180deg, var(--off-white), #fff); flex-wrap: wrap; gap: 12px; }
  .cal-nav-cluster { display: flex; align-items: center; gap: 10px; }
  .calpage .cal-rnd { width: 32px; height: 32px; border-radius: 50%; border: 1px solid var(--border); background: #fff; color: var(--navy); font-size: 1.15rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; line-height: 1; transition: all .14s; }
  .calpage .cal-rnd:hover { background: var(--navy); color: #fff; border-color: var(--navy); transform: translateY(-1px); }
  .calpage .cal-label { min-width: 168px; text-align: center; font-family: var(--font-serif); font-size: 1.02rem; font-weight: 700; color: var(--navy); }
  .cal-todaybtn { margin-left: 2px; border: 1px solid var(--teal); background: var(--teal-light); color: var(--teal-dark); border-radius: 999px; padding: 6px 14px; font-size: .74rem; font-weight: 700; cursor: pointer; transition: all .14s; }
  .cal-todaybtn:hover { background: var(--teal); color: #fff; }
  .cal-tools { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
  .cal-viewtabs { display: inline-flex; background: var(--off-white); border: 1px solid var(--border); border-radius: var(--r); padding: 2px; }
  .cal-viewtabs button { border: none; background: transparent; padding: 5px 13px; border-radius: 6px; font-size: .76rem; font-weight: 600; color: var(--text-mid); cursor: pointer; transition: all .14s; }
  .cal-viewtabs button:hover { color: var(--navy); }
  .cal-viewtabs button.active { background: #fff; color: var(--navy); box-shadow: var(--shadow-xs); }
  .calpage .cal-catfilter { width: 168px; padding: 6px 30px 6px 10px; font-size: .78rem; }

  /* Legend */
  .cal-legend { display: flex; gap: 18px; flex-wrap: wrap; align-items: center; font-size: .73rem; color: var(--text-mid); margin-bottom: 16px; }
  .cal-legend .lg { display: flex; align-items: center; gap: 7px; }

  /* Month grid */
  .calpage .cal-grid { border-collapse: separate; border-spacing: 7px; }
  .calpage .cal-grid th { padding: 4px 4px 8px; font-size: .66rem; color: var(--text-mid); }
  .calpage .cal-cell { padding: 0; text-align: left; }
  .calpage .cal-day { min-height: 92px; border: 1px solid var(--border-light); border-radius: 12px; background: #fff; padding: 8px 8px 7px; transition: transform .14s ease, box-shadow .14s ease, border-color .14s ease; }
  .calpage .cal-day-num { font-size: .8rem; text-align: left; }
  .calpage .cal-day.other-month { background: var(--off-white); opacity: .6; }
  .calpage .cal-day:hover { border-color: var(--teal); box-shadow: 0 8px 18px rgba(32, 39, 52, .11); transform: translateY(-2px); }
  .calpage .cal-day.today { border-color: var(--teal); background: var(--teal-light); }
  .calpage .cal-day.today .cal-day-num { color: var(--teal-dark); font-weight: 800; }
  .cal-day-more { font-size: .58rem; color: var(--text-light); font-weight: 700; margin-top: 1px; }

  /* Week view */
  .cal-week { display: grid; grid-template-columns: repeat(7, 1fr); gap: 7px; }
  .cal-weekcol { border: 1px solid var(--border-light); border-radius: 12px; overflow: hidden; min-height: 160px; display: flex; flex-direction: column; background: #fff; transition: border-color .14s, box-shadow .14s; }
  .cal-weekcol:hover { border-color: var(--teal); box-shadow: 0 8px 18px rgba(32, 39, 52, .10); }
  .cal-weekcol.today { border-color: var(--teal); }
  .cal-weekhead { padding: 7px 4px; text-align: center; background: var(--off-white); border-bottom: 1px solid var(--border-light); }
  .cal-weekhead .dow { font-size: .6rem; text-transform: uppercase; letter-spacing: .04em; color: var(--text-light); font-weight: 700; }
  .cal-weekhead .dnum { font-size: 1.05rem; font-weight: 700; color: var(--navy); }
  .cal-weekcol.today .dnum { color: var(--teal-dark); }
  .cal-weekbody { padding: 7px; display: flex; flex-direction: column; gap: 3px; cursor: pointer; flex: 1; }

  /* Day view */
  .cal-daylist { display: flex; flex-direction: column; gap: 10px; }
  .cal-dayempty { padding: 34px; text-align: center; color: var(--text-light); font-size: .85rem; }
  .cal-dayitem { display: flex; gap: 14px; padding: 13px 15px; border: 1px solid var(--border-light); border-left-width: 4px; border-radius: var(--r); background: #fff; }
  .cal-daytime { font-size: .76rem; font-weight: 700; color: var(--navy); white-space: nowrap; min-width: 96px; }
  .cal-daymain { flex: 1; min-width: 0; }
  .cal-daykind { font-size: .64rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; }
  .cal-daytitle { font-weight: 600; font-size: .85rem; color: var(--text); margin-top: 2px; }
  .cal-daysub { font-size: .76rem; color: var(--text-mid); margin-top: 2px; }

  @media (max-width: 640px) {
    .cal-hero { padding: 22px; }
    .cal-hero-meta, .cal-hero-date { width: 100%; }
    .calpage .cal-grid { border-spacing: 4px; }
    .calpage .cal-day { min-height: 64px; }
    .cal-week { grid-template-columns: repeat(7, minmax(80px, 1fr)); overflow-x: auto; }
  }
</style>

<div class="calpage">
  <!-- ── Hero ── -->
  <div class="cal-hero">
    <div class="cal-hero-text">
      <span class="cal-hero-eyebrow">📅 Schedule</span>
      <h1>Calendar View</h1>
      <p>Month, week, and day overview of bookings and blocked slots across all labs.</p>
      <p>Month, week, and day overview of bookings and blocked slots across all labs.</p>
    </div>
    <div class="cal-hero-meta">
      <div class="cal-hero-date">
        <span class="num"><?= date('j') ?></span>
        <span class="lbl"><?= date('M Y') ?></span>
      </div>
    </div>
  </div>

  <!-- ── Stat Cards ── -->
  <div class="stat-grid">
    <?php foreach ($calendarStats as $stat): ?>
    <div class="stat-card <?= $stat['accent'] ?>">
      <div class="stat-card-top">
        <div class="stat-icon"><?= $stat['icon'] ?></div>
      </div>
      <div class="stat-num"><?= $stat['count'] ?></div>
      <div class="stat-label"><?= htmlspecialchars($stat['label']) ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- ── Calendar (Month / Week / Day) ── -->
  <div class="card">
    <div class="card-header cal-toolbar">
      <div class="cal-nav-cluster">
        <button class="cal-rnd" id="calPrev" title="Previous">&#8249;</button>
        <span class="cal-label" id="calLabel">—</span>
        <button class="cal-rnd" id="calNext" title="Next">&#8250;</button>
        <button class="cal-todaybtn" id="calToday">Today</button>
      </div>
      <div class="cal-tools">
        <div class="cal-viewtabs" id="calViewTabs">
          <button type="button" data-view="month" class="active">Month</button>
          <button type="button" data-view="week">Week</button>
          <button type="button" data-view="day">Day</button>
        </div>
        <select class="form-control cal-catfilter" id="calLabFilter">
          <option value="">All Lab Types</option>
          <option value="research">Research Labs</option>
          <option value="csl">CSL Labs</option>
          <option value="pharma">Pharma Labs</option>
        </select>
      </div>
    </div>
    <div class="card-body">
      <div class="cal-legend">
        <span class="lg"><span class="cal-dot cal-dot-booking" style="width:9px;height:9px;"></span> Approved / Booking</span>
        <span class="lg"><span class="cal-dot cal-dot-pending" style="width:9px;height:9px;"></span> Pending</span>
        <span class="lg"><span class="cal-dot cal-dot-block" style="width:9px;height:9px;"></span> Blocked Slot</span>
        <span class="lg" style="margin-left:auto;"><span style="display:inline-block;width:15px;height:15px;border-radius:5px;background:var(--teal-light);border:1px solid var(--teal);"></span> Today</span>
      </div>
      <div id="calGrid"></div>
    </div>
  </div>
</div><!-- /.calpage -->

<script>
const CAL_EVENTS       = <?= json_encode($CAL_EVENTS, JSON_UNESCAPED_UNICODE) ?>;
const CAL_COLORS       = <?= json_encode($CAL_COLORS, JSON_UNESCAPED_UNICODE) ?>;
const CAL_BLOCK_LABELS = <?= json_encode($BLOCK_LABELS, JSON_UNESCAPED_UNICODE) ?>;
const CAL_TYPE_LABELS  = <?= json_encode($TYPE_LABELS, JSON_UNESCAPED_UNICODE) ?>;

const CAL_MONTHS    = ['January','February','March','April','May','June','July','August','September','October','November','December'];
const CAL_MON_SHORT = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
const CAL_DOW       = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];

let calView = 'month';
let calCat  = '';
let calCursor = new Date(); calCursor.setHours(0, 0, 0, 0);

function cPad(n) { return String(n).padStart(2, '0'); }
function cKey(dt) { return dt.getFullYear() + '-' + cPad(dt.getMonth() + 1) + '-' + cPad(dt.getDate()); }
function cEsc(s) { return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); }
function cCap(s) { s = String(s || ''); return s.charAt(0).toUpperCase() + s.slice(1); }
function cLab(lab) {
  return String(lab || '')
    .replace(/\s*\([^)]*\)/g, '')
    .replace(/\s*[—–]\s*/g, ' - ')
    .replace(/\s+/g, ' ')
    .trim();
}
function cIsToday(dt) {
  const t = new Date();
  return dt.getFullYear() === t.getFullYear() && dt.getMonth() === t.getMonth() && dt.getDate() === t.getDate();
}

// Unified, lab-type-filtered, time-sorted item list for a date key.
// Bookings are colored by STATUS (approved=green, pending=gold); blocks=teal.
function calItems(ds) {
  const ev = CAL_EVENTS[ds];
  if (!ev) return [];
  const out = [];
  (ev.bookings || []).forEach(b => {
    if (calCat && b.lab_type !== calCat) return;
    const pending = b.status === 'pending';
    out.push({
      kind: 'booking', color: pending ? CAL_COLORS.pending : CAL_COLORS.booking,
      short: cLab(b.lab), full: b.lab, name: b.name, status: b.status,
      start: b.start, end: b.end, label: b.type_label, ref: b.ref, purpose: b.purpose
    });
  });
  (ev.blocks || []).forEach(bl => {
    if (calCat && bl.type !== calCat) return;
    out.push({
      kind: 'block', color: CAL_COLORS.block,
      short: cLab(bl.lab), full: bl.lab, name: bl.title, status: 'blocked',
      start: bl.start, end: bl.end, label: CAL_BLOCK_LABELS[bl.category] || 'Block', reason: bl.reason, pic: bl.pic
    });
  });
  out.sort((a, b) => String(a.start || '').localeCompare(String(b.start || '')));
  return out;
}

// Up to 4 chips, then "+n more"
function cChips(items) {
  let html = '<div class="cal-day-labs">';
  items.slice(0, 4).forEach(it => {
    html += '<span class="cal-day-lab" style="background:' + it.color + '" title="'
          + cEsc(it.full + ' · ' + it.name) + '">' + cEsc(it.short || it.name) + '</span>';
  });
  if (items.length > 4) html += '<div class="cal-day-more">+' + (items.length - 4) + ' more</div>';
  html += '</div>';
  return html;
}

function calMonth() {
  const y = calCursor.getFullYear(), m = calCursor.getMonth();
  document.getElementById('calLabel').textContent = CAL_MONTHS[m] + ' ' + y;
  const firstDow = new Date(y, m, 1).getDay(), daysIn = new Date(y, m + 1, 0).getDate();
  let html = '<table class="cal-grid"><thead><tr>';
  CAL_DOW.forEach(d => html += '<th>' + d.slice(0, 2) + '</th>');
  html += '</tr></thead><tbody><tr>';
  let cell = 0;
  for (let i = 0; i < firstDow; i++) { html += '<td class="cal-cell"></td>'; cell++; }
  for (let day = 1; day <= daysIn; day++) {
    if (cell > 0 && cell % 7 === 0) html += '</tr><tr>';
    const dt = new Date(y, m, day), ds = cKey(dt), items = calItems(ds);
    html += '<td class="cal-cell"><div class="cal-day' + (cIsToday(dt) ? ' today' : '')
          + '" onclick="calGoDay(\'' + ds + '\')" style="cursor:pointer;">';
    html += '<div class="cal-day-num">' + day + '</div>';
    if (items.length) html += cChips(items);
    html += '</div></td>';
    cell++;
  }
  while (cell % 7 !== 0) { html += '<td class="cal-cell"></td>'; cell++; }
  html += '</tr></tbody></table>';
  return html;
}

function calWeek() {
  const start = new Date(calCursor); start.setDate(start.getDate() - start.getDay());
  const end = new Date(start); end.setDate(end.getDate() + 6);
  document.getElementById('calLabel').textContent = cRange(start, end);
  let html = '<div class="cal-week">';
  for (let i = 0; i < 7; i++) {
    const dt = new Date(start); dt.setDate(start.getDate() + i);
    const ds = cKey(dt), items = calItems(ds);
    html += '<div class="cal-weekcol' + (cIsToday(dt) ? ' today' : '') + '">';
    html += '<div class="cal-weekhead"><div class="dow">' + CAL_DOW[i] + '</div><div class="dnum">' + dt.getDate() + '</div></div>';
    html += '<div class="cal-weekbody" onclick="calGoDay(\'' + ds + '\')">';
    if (items.length) {
      items.slice(0, 4).forEach(it => {
        html += '<span class="cal-day-lab" style="background:' + it.color + '" title="'
              + cEsc(it.full + ' · ' + it.name) + '">' + cEsc(it.short || it.name) + '</span>';
      });
      if (items.length > 4) html += '<div class="cal-day-more">+' + (items.length - 4) + ' more</div>';
    }
    html += '</div></div>';
  }
  html += '</div>';
  return html;
}

function calDay() {
  const ds = cKey(calCursor);
  document.getElementById('calLabel').textContent =
    calCursor.toLocaleDateString('en-MY', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
  const items = calItems(ds);
  if (!items.length) {
    return '<div class="cal-dayempty">No ' + (calCat ? cEsc(CAL_TYPE_LABELS[calCat]) + ' ' : '') + 'events on this date.</div>';
  }
  let html = '<div class="cal-daylist">';
  items.forEach(it => {
    const kind = it.kind === 'block' ? ('🚫 ' + it.label) : (it.label + ' · ' + it.ref);
    const extra = it.kind === 'booking' ? (it.purpose ? ' · ' + it.purpose : '') : (it.reason ? ' · ' + it.reason : '');
    html += '<div class="cal-dayitem" style="border-left-color:' + it.color + '">';
    html += '<div class="cal-daytime">' + cEsc(it.start) + ' – ' + cEsc(it.end) + '</div>';
    html += '<div class="cal-daymain">';
    html += '<div class="cal-daykind" style="color:' + it.color + '">' + cEsc(kind) + '</div>';
    html += '<div class="cal-daytitle">' + cEsc(it.name)
          + (it.kind === 'booking' ? ' <span class="badge badge-' + cEsc(it.status) + '">' + cEsc(cCap(it.status)) + '</span>' : '') + '</div>';
    html += '<div class="cal-daysub">' + cEsc(it.full + extra) + '</div>';
    html += '</div></div>';
  });
  html += '</div>';
  return html;
}

function cRange(a, b) {
  if (a.getMonth() === b.getMonth()) {
    return a.getDate() + ' – ' + b.getDate() + ' ' + CAL_MON_SHORT[b.getMonth()] + ' ' + b.getFullYear();
  }
  return a.getDate() + ' ' + CAL_MON_SHORT[a.getMonth()] + ' – ' + b.getDate() + ' ' + CAL_MON_SHORT[b.getMonth()] + ' ' + b.getFullYear();
}

function calRender() {
  document.querySelectorAll('#calViewTabs button').forEach(btn => btn.classList.toggle('active', btn.dataset.view === calView));
  document.getElementById('calGrid').innerHTML =
    calView === 'month' ? calMonth() : calView === 'week' ? calWeek() : calDay();
}

function calGoDay(ds) { calCursor = new Date(ds + 'T00:00:00'); calView = 'day'; calRender(); }

function calNavigate(dir) {
  if (calView === 'month') calCursor.setMonth(calCursor.getMonth() + dir);
  else if (calView === 'week') calCursor.setDate(calCursor.getDate() + 7 * dir);
  else calCursor.setDate(calCursor.getDate() + dir);
  calRender();
}

document.getElementById('calPrev').addEventListener('click', () => calNavigate(-1));
document.getElementById('calNext').addEventListener('click', () => calNavigate(1));
document.getElementById('calToday').addEventListener('click', () => { calCursor = new Date(); calCursor.setHours(0, 0, 0, 0); calRender(); });
document.querySelectorAll('#calViewTabs button').forEach(btn => btn.addEventListener('click', () => { calView = btn.dataset.view; calRender(); }));
document.getElementById('calLabFilter').addEventListener('change', e => { calCat = e.target.value; calRender(); });

calRender();
</script>
