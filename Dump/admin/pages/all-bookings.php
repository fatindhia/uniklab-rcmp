<?php
/**
 * pages/all-bookings.php
 */
require_once __DIR__ . '/../data/bookings.php';

$tableBookings = $BOOKINGS;
$tableId       = 'allBookTable';
$showType      = true;

$bookingStats = [
  ['label' => 'Total',    'count' => count($BOOKINGS),                  'icon' => '📋', 'accent' => 'c-total'],
  ['label' => 'Approved', 'count' => bookingCount($BOOKINGS, 'approved'),'icon' => '✅', 'accent' => 'c-approved'],
  ['label' => 'Pending',  'count' => bookingCount($BOOKINGS, 'pending'), 'icon' => '⏳', 'accent' => 'c-pending'],
  ['label' => 'Rejected', 'count' => bookingCount($BOOKINGS, 'rejected'),'icon' => '❌', 'accent' => 'c-rejected'],
];
?>

<div class="listpage">
  <!-- ── Hero ── -->
  <div class="page-hero">
    <div class="page-hero-text">
      <span class="page-hero-eyebrow">📋 Records</span>
      <h1>All Bookings</h1>
      <p>Booking records across all lab categories — search, filter, and review requests in one place.</p>
    </div>
    <div class="page-hero-side">
      <button class="page-hero-btn" onclick="window.print()">🖨 Print</button>
      <button class="page-hero-btn page-hero-btn--solid" onclick="showToast('Export to CSV — DB not connected yet.','')">⬇ Export CSV</button>
    </div>
  </div>

  <!-- ── Stat Cards ── -->
  <div class="stat-grid stat-accent">
    <?php foreach ($bookingStats as $stat): ?>
    <div class="stat-card <?= $stat['accent'] ?>">
      <div class="stat-card-top">
        <div class="stat-icon"><?= $stat['icon'] ?></div>
      </div>
      <div class="stat-num"><?= $stat['count'] ?></div>
      <div class="stat-label"><?= htmlspecialchars($stat['label']) ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- ── Bookings table ── -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">📋 All Bookings</span>
      <span style="font-size:.75rem;color:var(--text-light);"><?= count($BOOKINGS) ?> total</span>
    </div>
    <div class="card-body">
      <?php include __DIR__ . '/_booking_table.php'; ?>
    </div>
  </div>
</div><!-- /.listpage -->
