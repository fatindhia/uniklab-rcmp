<?php
/**
 * pages/research-labs.php
 */
require_once __DIR__ . '/../data/bookings.php';
require_once __DIR__ . '/../data/labs.php';

$tableBookings = array_values(array_filter($BOOKINGS, fn($b) => $b['lab_type'] === 'research'));
$tableId       = 'researchTable';
$showType      = false;

$researchLabs  = array_values(array_filter($LABS, fn($l) => $l['type'] === 'research'));
?>

<div class="listpage">
  <!-- ── Hero ── -->
  <div class="page-hero">
    <div class="page-hero-text">
      <span class="page-hero-eyebrow">🔬 Research</span>
      <h1>Research Labs</h1>
      <p>Bookings and facility status for Research Lab rooms across Al-Zahrawi and Avicenna.</p>
    </div>
    <div class="page-hero-side">
      <span class="page-hero-badge">🔬 <?= count($researchLabs) ?> Labs</span>
      <span class="page-hero-badge"><?= count($tableBookings) ?> Bookings</span>
    </div>
  </div>

  <!-- ── Lab cards ── -->
  <div class="lab-mini-grid" style="--lab-accent:#202734;">
    <?php foreach ($researchLabs as $lab): ?>
    <div class="lab-mini">
      <div class="lab-mini-body">
        <div class="lab-mini-top">
          <div class="lab-mini-name"><?= htmlspecialchars($lab['name']) ?></div>
          <span class="badge badge-<?= $lab['status'] ?>"><?= ucfirst($lab['status']) ?></span>
        </div>
        <div class="lab-mini-row">📍 <?= htmlspecialchars($lab['location']) ?></div>
        <div class="lab-mini-row">👤 <?= htmlspecialchars($lab['in_charge']) ?></div>
        <span class="lab-mini-cap">👥 <?= $lab['capacity'] ?> pax</span>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- ── Bookings ── -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">🔬 Research Lab Bookings</span>
      <span style="font-size:.75rem;color:var(--text-light);"><?= count($tableBookings) ?> records</span>
    </div>
    <div class="card-body">
      <?php include __DIR__ . '/_booking_table.php'; ?>
    </div>
  </div>
</div><!-- /.listpage -->
