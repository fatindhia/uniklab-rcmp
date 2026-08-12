<?php
/**
 * Header / Topbar component
 * Expects $pageTitle and $pageSubtitle (optional) to be set before include.
 */
$pageTitle    = $pageTitle    ?? 'Dashboard';
$pageSubtitle = $pageSubtitle ?? '';

$pageTitles = [
  'dashboard'     => 'Dashboard',
  'calendar'      => 'Calendar View',
  'all-bookings'  => 'All Bookings',
  'research-labs' => 'Research Labs',
  'csl-labs'      => 'CSL Labs',
  'pharma-labs'   => 'Pharma Labs',
  'schedule-block'=> 'Schedule & Block',
  'manage-lab-staff' => 'Manage Lab Staff',
  'manage-labs'   => 'Manage Labs',
  'system-report' => 'System Report',
  'history'       => 'Booking History',
  'notifications' => 'Notifications',
];
$displayTitle = $pageTitles[$currentPage ?? 'dashboard'] ?? $pageTitle;
?>
<header class="topbar">
  <div class="topbar-left">
    <button class="topbar-menu-btn" onclick="toggleSidebar()" title="Menu">☰</button>
    <span class="topbar-title"><?= htmlspecialchars($displayTitle) ?></span>
    <?php if ($pageSubtitle): ?>
      <span class="topbar-breadcrumb">/ <?= htmlspecialchars($pageSubtitle) ?></span>
    <?php endif; ?>
  </div>
  <div class="topbar-right">
    <span class="topbar-date"><?= date('D, d M Y') ?></span>
    <div class="admin-chip">
      <span class="admin-dot"></span>
      Administrator
    </div>
  </div>
</header>
