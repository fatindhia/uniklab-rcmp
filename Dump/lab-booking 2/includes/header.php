<?php require_once dirname(__DIR__) . '/config/constants.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — ' : '' ?><?= SITE_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css" />
</head>
<body class="<?= isset($bodyClass) ? htmlspecialchars($bodyClass) : '' ?>">

<!-- Top bar -->
<div class="topbar">
  <span>LabOps Control Panel · <?= SITE_SUBTITLE ?></span>
  <span class="topbar-right">
    <span class="topbar-dot"></span> System Status: Online
  </span>
</div>

<!-- Main navbar -->
<nav class="navbar">
  <a href="<?= BASE_URL ?>/index.php" class="navbar-brand">
    <div class="brand-icon">LB</div>
    <div class="brand-text">
      <span class="brand-name"><?= SITE_NAME ?></span>
      <span class="brand-sub">RCMP</span>
    </div>
  </a>

  <div class="navbar-links">
    <a href="<?= BASE_URL ?>/index.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) === 'index.php' && !isset($inSubdir)) ? 'active' : '' ?>">Home</a>
    <a href="<?= BASE_URL ?>/booking.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) === 'booking.php') ? 'active' : '' ?>">Book a Lab</a>
    <a href="<?= BASE_URL ?>/check-booking.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) === 'check-booking.php') ? 'active' : '' ?>">Check Status</a>
  </div>

  <div class="navbar-actions">
    <a href="<?= BASE_URL ?>/admin/login.php" class="btn btn-admin-link">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      Admin Login
    </a>
  </div>

  <button class="hamburger" id="hamburger" aria-label="Toggle menu">
    <span></span><span></span><span></span>
  </button>
</nav>

<!-- Mobile drawer -->
<div class="mobile-drawer" id="mobileDrawer">
  <div class="drawer-header">
    <div class="brand-icon brand-icon--sm" style="margin-bottom:4px;">LB</div>
    <span style="font-size:.75rem;color:var(--text-light);">UniKLAB RCMP Lab Booking</span>
  </div>
  <div class="drawer-section-label">Navigation</div>
  <a href="<?= BASE_URL ?>/index.php" class="nav-link">🏠 Home</a>
  <a href="<?= BASE_URL ?>/booking.php" class="nav-link">🔬 Book a Lab</a>
  <a href="<?= BASE_URL ?>/check-booking.php" class="nav-link">📋 Check Booking Status</a>
  <div class="drawer-divider"></div>
  <a href="<?= BASE_URL ?>/admin/login.php" class="nav-link nav-link--admin">🔒 Admin Login</a>
</div>
<div class="drawer-overlay" id="drawerOverlay"></div>

<main class="main-content">
