<?php
$pageTitle = 'My Bookings';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div class="page-header-inner">
    <div class="breadcrumb">
      <a href="index.php">Home</a>
      <span class="breadcrumb-sep">/</span>
      <span>My Bookings</span>
    </div>
    <h1>My Bookings</h1>
    <p>View and track all your lab booking requests and their current status.</p>
  </div>
</div>

<div class="container container--narrow">
  <div class="card">
    <div class="card-header">
      <h2>Booking History</h2>
      <span class="myb-chip">Coming Soon</span>
    </div>
    <div class="card-body">
      <div class="placeholder-body" style="padding:24px 0 10px;">
        <div class="placeholder-icon">📋</div>
        <h2>Track all your submissions</h2>
        <p>This page will display your booking history, schedule summaries, and latest status updates once database integration is enabled.</p>
      </div>
    </div>
    <div class="card-footer">
      <a href="index.php" class="btn btn-ghost">← Back to Home</a>
    </div>
  </div>
</div>

<style>
.myb-chip{font-size:.68rem;text-transform:uppercase;letter-spacing:.08em;font-weight:700;color:#202734;background:#e7ecec;border:1px solid #d0deee;border-radius:999px;padding:4px 10px;}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
