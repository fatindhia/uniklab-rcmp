<?php
/**
 * Sidebar component
 * Expects $currentPage to be set before include.
 */
$currentPage = $currentPage ?? 'dashboard';

$nav = [
  'overview' => [
    'label' => 'Overview',
    'items' => [
      ['page' => 'dashboard',   'icon' => '▣',  'label' => 'Dashboard'],
      ['page' => 'calendar',    'icon' => '📅', 'label' => 'Calendar View'],
    ]
  ],
  'bookings' => [
    'label' => 'Bookings',
    'items' => [
      ['page' => 'all-bookings',   'icon' => '📋', 'label' => 'All Bookings',   'badge' => null],
      ['page' => 'research-labs',  'icon' => '🔬', 'label' => 'Research Labs'],
      ['page' => 'csl-labs',       'icon' => '🧪', 'label' => 'CSL Labs'],
      ['page' => 'pharma-labs',    'icon' => '💊', 'label' => 'Pharma Labs'],
    ]
  ],
  'management' => [
    'label' => 'Management',
    'items' => [
      ['page' => 'schedule-block', 'icon' => '🚫', 'label' => 'Schedule & Block'],
      ['page' => 'manage-lab-staff', 'icon' => 'ID', 'label' => 'Manage Lab Staff'],
      ['page' => 'manage-labs',    'icon' => '🏛️', 'label' => 'Manage Labs'],
      ['page' => 'system-report',  'icon' => '📊', 'label' => 'System Report'],
    ]
  ],
  'activity' => [
    'label' => 'Activity',
    'items' => [
      ['page' => 'history',       'icon' => '🕓', 'label' => 'History'],
      ['page' => 'notifications', 'icon' => '🔔', 'label' => 'Notifications'],
    ]
  ],
];

// Count pending for badge
require_once __DIR__ . '/../data/bookings.php';
$pendingCount = bookingCount($BOOKINGS, 'pending');
?>
<aside class="sidebar" id="sidebar">

  <div class="sb-brand">
    <div class="sb-brand-row">
      <div class="sb-icon">LB</div>
      <div>
        <div class="sb-name">UniKLAB RCMP</div>
        <div class="sb-sub">Admin Panel</div>
      </div>
    </div>
  </div>

  <nav class="sb-nav">
    <?php foreach ($nav as $groupKey => $group): ?>
      <div class="sb-section"><?= htmlspecialchars($group['label']) ?></div>
      <?php foreach ($group['items'] as $item):
        $isActive = ($currentPage === $item['page']);
        $badge = '';
        if (($item['page'] === 'all-bookings' || $item['page'] === 'notifications') && $pendingCount > 0) {
          $badge = '<span class="sb-badge">' . $pendingCount . '</span>';
        }
      ?>
        <a class="sb-link<?= $isActive ? ' active' : '' ?>"
           href="index.php?page=<?= htmlspecialchars($item['page']) ?>"
           data-page="<?= htmlspecialchars($item['page']) ?>">
          <span class="sb-link-icon"><?= $item['icon'] ?></span>
          <span><?= htmlspecialchars($item['label']) ?></span>
          <?= $badge ?>
        </a>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </nav>

  <div class="sb-bottom">
    <div class="sb-user">
      <div class="sb-avatar">AD</div>
      <div class="sb-user-info">
        <div class="sb-user-name">Administrator</div>
        <div class="sb-user-role">Super Admin</div>
      </div>
    </div>
    <a href="login.php" class="sb-logout">
      <span>↩</span> Sign Out
    </a>
  </div>

</aside>
