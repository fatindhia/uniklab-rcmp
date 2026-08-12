<?php
/**
 * pages/history.php
 * Read-only log of past events — bookings and blocked slots whose date has passed.
 */
require_once __DIR__ . '/../data/bookings.php';
require_once __DIR__ . '/../data/reports.php';

$today = date('Y-m-d');

$pastBookings = array_values(array_filter($BOOKINGS,        fn($b)  => $b['date'] < $today));
$pastBlocks   = array_values(array_filter($BLOCKED_SLOTS,   fn($bl) => $bl['date'] < $today));

$TYPE_COLORS  = ['research'=>'#202734','csl'=>'#5d7c86','pharma'=>'#2a7782','block'=>'#666759'];
$TYPE_LABELS  = ['research'=>'Research','csl'=>'CSL','pharma'=>'Pharma','block'=>'Block'];
$BLOCK_LABELS = ['class'=>'Class','practical'=>'Practical','maintenance'=>'Maintenance','reserved'=>'Reserved','exam'=>'Exam/OSCE','event'=>'Event'];

// Unified event list
$events = [];
foreach ($pastBookings as $b) {
  $events[] = [
    'kind'=>'booking','date'=>$b['date'],'start'=>$b['start'],'end'=>$b['end'],
    'title'=>$b['name'],'sub'=>$b['lab'],'type'=>$b['lab_type'],
    'status'=>$b['status'],'ref'=>$b['ref'],'meta'=>$b['purpose'] ?? '',
  ];
}
foreach ($pastBlocks as $bl) {
  $events[] = [
    'kind'=>'block','date'=>$bl['date'],'start'=>$bl['start'],'end'=>$bl['end'],
    'title'=>$bl['title'],'sub'=>($bl['lab'] ?? implode(', ', $bl['rooms'] ?? [])),'type'=>$bl['type'],
    'status'=>'blocked','ref'=>$bl['id'],'meta'=>$BLOCK_LABELS[$bl['category']] ?? ($bl['category'] ?? ''),
  ];
}
// Newest first
usort($events, fn($a,$b) => strcmp($b['date'].$b['start'], $a['date'].$a['start']));

$stats = [
  ['label'=>'Past Events','count'=>count($events),                                                          'icon'=>'🕓','accent'=>'c-total'],
  ['label'=>'Completed',  'count'=>count(array_filter($pastBookings, fn($b)=>$b['status']==='approved')),    'icon'=>'✅','accent'=>'c-approved'],
  ['label'=>'Rejected',   'count'=>count(array_filter($pastBookings, fn($b)=>$b['status']==='rejected')),    'icon'=>'❌','accent'=>'c-rejected'],
  ['label'=>'Blocks',     'count'=>count($pastBlocks),                                                       'icon'=>'🚫','accent'=>'c-blocked'],
];
?>

<style>
  .hist-list { position: relative; }
  .hist-item { display: grid; grid-template-columns: 92px 26px 1fr; align-items: stretch; }
  .hist-when { padding: 14px 10px 6px 0; text-align: right; }
  .hist-when-date { font-size: .8rem; font-weight: 700; color: var(--navy); }
  .hist-when-time { font-size: .67rem; color: var(--text-light); margin-top: 2px; white-space: nowrap; }
  .hist-rail { position: relative; display: flex; justify-content: center; }
  .hist-rail::before { content: ''; position: absolute; top: 0; bottom: 0; width: 2px; background: var(--border-light); }
  .hist-item:first-child .hist-rail::before { top: 20px; }
  .hist-item:last-child .hist-rail::before { bottom: calc(100% - 20px); }
  .hist-node { width: 13px; height: 13px; border-radius: 50%; margin-top: 15px; z-index: 1; border: 2px solid #fff; box-shadow: 0 0 0 1.5px rgba(0,0,0,.08); }
  .hist-card { margin: 9px 0 9px 14px; padding: 12px 15px; border: 1px solid var(--border-light); border-radius: var(--r-lg); background: #fff; flex: 1; transition: box-shadow .14s, transform .14s; }
  .hist-item:hover .hist-card { box-shadow: var(--shadow-sm); transform: translateX(2px); }
  .hist-card-top { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
  .hist-card-title { font-weight: 700; font-size: .86rem; color: var(--navy); }
  .hist-card-sub { font-size: .76rem; color: var(--text-mid); margin-top: 4px; display: flex; align-items: center; gap: 7px; flex-wrap: wrap; }
  .hist-card-ref { font-family: monospace; font-weight: 600; color: var(--navy-mid); }
  .hist-card-meta { font-size: .72rem; color: var(--text-light); margin-top: 3px; font-style: italic; }
</style>

<div class="listpage">
  <!-- ── Hero ── -->
  <div class="page-hero">
    <div class="page-hero-text">
      <span class="page-hero-eyebrow">🕓 Archive</span>
      <h1>History</h1>
      <p>A chronological log of past bookings and blocked sessions across all labs.</p>
    </div>
    <div class="page-hero-side">
      <span class="page-hero-badge">📆 <?= count($events) ?> past events</span>
    </div>
  </div>

  <!-- ── Stat Cards ── -->
  <div class="stat-grid stat-accent">
    <?php foreach ($stats as $s): ?>
    <div class="stat-card <?= $s['accent'] ?>">
      <div class="stat-card-top"><div class="stat-icon"><?= $s['icon'] ?></div></div>
      <div class="stat-num"><?= $s['count'] ?></div>
      <div class="stat-label"><?= htmlspecialchars($s['label']) ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- ── Filter ── -->
  <div class="filter-bar" style="margin-bottom:16px;">
    <div class="search-input-wrap">
      <input type="text" class="form-control" id="histSearch" placeholder="Search name, ref, lab…" oninput="filterHistory()">
    </div>
    <select class="form-control" id="histTypeFilter" onchange="filterHistory()">
      <option value="">All Types</option>
      <option value="research">Research</option>
      <option value="csl">CSL</option>
      <option value="pharma">Pharma</option>
    </select>
    <select class="form-control" id="histKindFilter" onchange="filterHistory()">
      <option value="">Bookings &amp; Blocks</option>
      <option value="booking">Bookings only</option>
      <option value="block">Blocks only</option>
    </select>
    <span style="margin-left:auto;font-size:.76rem;color:var(--text-light);" id="histCount"><?= count($events) ?> events</span>
  </div>

  <!-- ── Timeline ── -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">🕓 Activity Timeline</span>
      <span style="font-size:.74rem;color:var(--text-light);">Most recent first</span>
    </div>
    <div class="card-body-flush">
      <?php if (empty($events)): ?>
        <div class="empty-state"><div class="empty-icon">🗓️</div><p><strong>No past events yet</strong><br>Completed bookings and blocks will appear here.</p></div>
      <?php else: ?>
        <div class="hist-list" style="padding:6px 18px;">
          <?php foreach ($events as $e):
            $color  = $TYPE_COLORS[$e['type']] ?? '#666';
            $isBlk  = $e['kind'] === 'block';
            $dDate  = date('j M', strtotime($e['date']));
            $dYear  = date('Y', strtotime($e['date']));
          ?>
          <div class="hist-item"
               data-type="<?= htmlspecialchars($e['type']) ?>"
               data-kind="<?= $e['kind'] ?>"
               data-text="<?= htmlspecialchars(strtolower($e['title'].' '.$e['ref'].' '.$e['sub'])) ?>">
            <div class="hist-when">
              <div class="hist-when-date"><?= $dDate ?></div>
              <div class="hist-when-time"><?= htmlspecialchars($e['start']) ?>–<?= htmlspecialchars($e['end']) ?></div>
              <div class="hist-when-time"><?= $dYear ?></div>
            </div>
            <div class="hist-rail"><span class="hist-node" style="background:<?= $color ?>"></span></div>
            <div class="hist-card">
              <div class="hist-card-top">
                <span class="hist-card-title"><?= $isBlk ? '🚫 ' : '' ?><?= htmlspecialchars($e['title']) ?></span>
                <span class="badge badge-<?= htmlspecialchars($e['status']) ?>"><?= ucfirst($e['status']) ?></span>
              </div>
              <div class="hist-card-sub">
                <span class="lab-type-tag lab-type-<?= htmlspecialchars($e['type']) ?>"><?= $TYPE_LABELS[$e['type']] ?? strtoupper($e['type']) ?></span>
                <span class="hist-card-ref"><?= htmlspecialchars($e['ref']) ?></span>
                <span><?= htmlspecialchars($e['sub']) ?></span>
              </div>
              <?php if (!empty($e['meta'])): ?>
                <div class="hist-card-meta"><?= htmlspecialchars($e['meta']) ?></div>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div><!-- /.listpage -->

<script>
function filterHistory() {
  const q  = (document.getElementById('histSearch').value || '').toLowerCase();
  const tf = document.getElementById('histTypeFilter').value;
  const kf = document.getElementById('histKindFilter').value;
  let vis = 0;
  document.querySelectorAll('.hist-item').forEach(it => {
    const ok = (!q || (it.dataset.text || '').includes(q))
            && (!tf || it.dataset.type === tf)
            && (!kf || it.dataset.kind === kf);
    it.style.display = ok ? '' : 'none';
    if (ok) vis++;
  });
  const c = document.getElementById('histCount');
  if (c) c.textContent = vis + ' event' + (vis !== 1 ? 's' : '');
}
</script>
