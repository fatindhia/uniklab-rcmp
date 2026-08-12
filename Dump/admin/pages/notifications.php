<?php
/**
 * pages/notifications.php
 * Notification feed — alerts admins to new booking requests (and recent decisions).
 * Pending bookings are treated as unread / needs-action.
 */
require_once __DIR__ . '/../data/bookings.php';

$submittedAt = function (array $b): string {
  foreach ($b['audit'] ?? [] as $a) {
    if (($a['type'] ?? '') === 'created') return $a['at'] ?? '';
  }
  return $b['audit'][0]['at'] ?? '';
};

// Friendly relative time (falls back to an absolute date for older items)
$relTime = function (string $ts): string {
  if (!$ts) return '';
  $t = strtotime($ts);
  $diff = time() - $t;
  if ($diff < 0)        return date('j M, H:i', $t);
  if ($diff < 60)       return 'just now';
  if ($diff < 3600)     return floor($diff / 60) . 'm ago';
  if ($diff < 86400)    return floor($diff / 3600) . 'h ago';
  if ($diff < 604800)   return floor($diff / 86400) . 'd ago';
  return date('j M Y', $t);
};

$notifs = $BOOKINGS;
usort($notifs, fn($a, $b) => strcmp($submittedAt($b), $submittedAt($a)));

$newCount = bookingCount($BOOKINGS, 'pending');

$stats = [
  ['label'=>'New / Unread','count'=>$newCount,                          'icon'=>'🔔','accent'=>'c-pending'],
  ['label'=>'Approved',    'count'=>bookingCount($BOOKINGS,'approved'), 'icon'=>'✅','accent'=>'c-approved'],
  ['label'=>'Rejected',    'count'=>bookingCount($BOOKINGS,'rejected'), 'icon'=>'❌','accent'=>'c-rejected'],
  ['label'=>'Total',       'count'=>count($BOOKINGS),                   'icon'=>'📋','accent'=>'c-total'],
];

// Per-status notification presentation
$KIND = [
  'pending'   => ['cls'=>'k-new',       'ico'=>'📩'],
  'approved'  => ['cls'=>'k-approved',  'ico'=>'✅'],
  'rejected'  => ['cls'=>'k-rejected',  'ico'=>'⛔'],
  'cancelled' => ['cls'=>'k-cancelled', 'ico'=>'🚫'],
];
?>

<style>
  .notif-tabs { display: flex; gap: 6px; margin-bottom: 16px; flex-wrap: wrap; }
  .ntab { padding: 8px 16px; font-size: .8rem; font-weight: 600; color: var(--text-mid); background: var(--off-white); border: 1px solid var(--border-light); border-radius: 999px; cursor: pointer; transition: all .15s; display: inline-flex; align-items: center; gap: 7px; }
  .ntab:hover { color: var(--navy); border-color: var(--teal); }
  .ntab.active { background: linear-gradient(135deg, #5cb0ba, #2a7782); color: #fff; border-color: transparent; box-shadow: 0 3px 9px rgba(42,119,130,.26); }
  .ntab-count { display: inline-flex; align-items: center; justify-content: center; min-width: 18px; height: 18px; padding: 0 5px; border-radius: 9px; font-size: .62rem; font-weight: 700; background: var(--navy-light); color: var(--navy); }
  .ntab.active .ntab-count { background: rgba(255,255,255,.26); color: #fff; }

  /* Notification rows (compact inbox style) */
  .notif-item { display: flex; gap: 12px; align-items: flex-start; position: relative; padding: 13px 18px 13px 30px; border-bottom: 1px solid var(--border-light); cursor: pointer; transition: background .12s; }
  .notif-item:last-child { border-bottom: none; }
  .notif-item:hover { background: var(--off-white); }
  .notif-item.unread { background: rgba(80,167,178,.06); }
  .notif-unread-dot { position: absolute; left: 13px; top: 21px; width: 8px; height: 8px; border-radius: 50%; background: var(--teal); }
  .notif-item:not(.unread) .notif-unread-dot { display: none; }
  .notif-ico { width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.02rem; }
  .notif-ico.k-new       { background: var(--teal-light); }
  .notif-ico.k-approved  { background: var(--success-bg); }
  .notif-ico.k-rejected  { background: var(--danger-bg); }
  .notif-ico.k-cancelled { background: var(--inactive-bg); }
  .notif-body { flex: 1; min-width: 0; padding-right: 6px; }
  .notif-title { font-size: .84rem; color: var(--text-mid); line-height: 1.45; }
  .notif-title strong { color: var(--navy); font-weight: 700; }
  .notif-item.unread .notif-title { color: var(--text); }
  .notif-sub { font-size: .74rem; color: var(--text-light); margin-top: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .notif-ref { font-family: monospace; font-weight: 600; color: var(--navy-mid); }
  .notif-side { display: flex; flex-direction: column; align-items: flex-end; gap: 5px; flex-shrink: 0; }
  .notif-time { font-size: .69rem; color: var(--text-light); white-space: nowrap; }
  .notif-review { font-size: .72rem; font-weight: 700; color: var(--teal-dark); text-decoration: none; white-space: nowrap; }
  .notif-review:hover { text-decoration: underline; }
</style>

<div class="listpage">
  <!-- ── Hero ── -->
  <div class="page-hero">
    <div class="page-hero-text">
      <span class="page-hero-eyebrow">🔔 Alerts</span>
      <h1>Notifications</h1>
      <p>Stay on top of new booking requests and recent approvals across all labs.</p>
    </div>
    <div class="page-hero-side">
      <button class="page-hero-btn page-hero-btn--solid" onclick="notifMarkAll()">✓ Mark all as read</button>
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

  <!-- ── Tabs ── -->
  <div class="notif-tabs">
    <button class="ntab active" data-filter="all" onclick="filterNotif('all', this)">All</button>
    <button class="ntab" data-filter="unread" onclick="filterNotif('unread', this)">Unread <span class="ntab-count" id="notifUnreadCount"><?= $newCount ?></span></button>
  </div>

  <!-- ── Feed ── -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">🔔 Recent Activity</span>
      <span style="font-size:.74rem;color:var(--text-light);">Newest first</span>
    </div>
    <div class="card-body-flush">
      <?php if (empty($notifs)): ?>
        <div class="empty-state"><div class="empty-icon">📭</div><p><strong>No notifications</strong><br>New booking requests will show up here.</p></div>
      <?php else: ?>
        <?php foreach ($notifs as $b):
          $isNew = $b['status'] === 'pending';
          $k     = $KIND[$b['status']] ?? $KIND['pending'];
          $name  = '<strong>' . htmlspecialchars($b['name']) . '</strong>';
          switch ($b['status']) {
            case 'approved':  $msg = "You approved $name's booking"; break;
            case 'rejected':  $msg = "You rejected $name's booking"; break;
            case 'cancelled': $msg = "$name's booking was cancelled"; break;
            default:          $msg = "New booking request from $name";
          }
        ?>
        <div class="notif-item<?= $isNew ? ' unread' : '' ?>" onclick="notifMarkRead(this)">
          <span class="notif-unread-dot"></span>
          <div class="notif-ico <?= $k['cls'] ?>"><?= $k['ico'] ?></div>
          <div class="notif-body">
            <div class="notif-title"><?= $msg ?></div>
            <div class="notif-sub"><span class="notif-ref"><?= htmlspecialchars($b['ref']) ?></span> · <?= htmlspecialchars($b['lab']) ?></div>
          </div>
          <div class="notif-side">
            <span class="notif-time"><?= htmlspecialchars($relTime($submittedAt($b))) ?></span>
            <?php if ($isNew): ?><a class="notif-review" href="index.php?page=all-bookings">Review →</a><?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div><!-- /.listpage -->

<script>
function notifUpdateCount() {
  const n = document.querySelectorAll('.notif-item.unread').length;
  const el = document.getElementById('notifUnreadCount');
  if (el) el.textContent = n;
  return n;
}

function notifMarkRead(el) {
  if (el.classList.contains('unread')) {
    el.classList.remove('unread');
    notifUpdateCount();
    const active = document.querySelector('.ntab.active');
    if (active && active.dataset.filter === 'unread') el.style.display = 'none';
  }
}

function notifMarkAll() {
  const unread = document.querySelectorAll('.notif-item.unread');
  if (!unread.length) { showToast('No unread notifications.', ''); return; }
  unread.forEach(n => n.classList.remove('unread'));
  notifUpdateCount();
  const active = document.querySelector('.ntab.active');
  if (active && active.dataset.filter === 'unread') {
    document.querySelectorAll('.notif-item').forEach(n => n.style.display = 'none');
  }
  showToast('All notifications marked as read.', 'success');
}

function filterNotif(which, btn) {
  document.querySelectorAll('.ntab').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('.notif-item').forEach(n => {
    const show = which === 'all' || n.classList.contains('unread');
    n.style.display = show ? '' : 'none';
  });
}
</script>
