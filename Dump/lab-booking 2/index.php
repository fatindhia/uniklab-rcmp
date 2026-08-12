<?php
$pageTitle = 'Home';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="hero">
  <div class="hero-inner">
    <div class="hero-tag">🔬 UniKL RCMP</div>
    <h1>Lab & Equipment<br><span>Booking System</span></h1>
    <p>Reserve laboratories and equipment at UniKL RCMP quickly and transparently. One system, one schedule, zero double booking.</p>
    <div class="hero-actions">
      <a href="#book-now" class="btn btn-gold btn-lg">Book a Lab</a>
      <a href="my-bookings.php" class="btn btn-ghost btn-lg" style="color:#fff;border-color:rgba(255,255,255,.3);">My Bookings</a>
    </div>
    <div class="hero-stats">
      <div>
        <div class="hero-stat-num">3</div>
        <div class="hero-stat-label">Lab Categories</div>
      </div>
      <div>
        <div class="hero-stat-num">30+</div>
        <div class="hero-stat-label">Rooms Available</div>
      </div>
      <div>
        <div class="hero-stat-num">50+</div>
        <div class="hero-stat-label">Equipment Items</div>
      </div>
    </div>
  </div>
</section>

<!-- How It Works Strip -->
<section class="steps-strip">
  <div class="steps-strip-inner">
    <div class="step-item">
      <div class="step-item-num">1</div>
      <div class="step-item-text">
        <strong>Select Lab Type</strong>
        <span>Equipment, CSL, or Pharma</span>
      </div>
    </div>
    <div class="step-arrow">→</div>
    <div class="step-item">
      <div class="step-item-num">2</div>
      <div class="step-item-text">
        <strong>Choose Room / Equipment</strong>
        <span>Check availability in real time</span>
      </div>
    </div>
    <div class="step-arrow">→</div>
    <div class="step-item">
      <div class="step-item-num">3</div>
      <div class="step-item-text">
        <strong>Fill Booking Details</strong>
        <span>Date, time, purpose & info</span>
      </div>
    </div>
    <div class="step-arrow">→</div>
    <div class="step-item">
      <div class="step-item-num">4</div>
      <div class="step-item-text">
        <strong>Submit & Get Confirmation</strong>
        <span>Email notification sent</span>
      </div>
    </div>
  </div>
</section>

<!-- Lab Cards -->
<section class="container" id="book-now">
  <div class="section-intro">
    <div class="overline">Choose Your Lab</div>
    <h2>Which lab do you need?</h2>
    <p>Select the category that matches your booking. Each type has its own rules and availability schedule.</p>
  </div>

  <div class="lab-cards-grid">

    <!-- Card 1: Equipment Labs -->
    <div class="lab-card">
      <div class="lab-card-stripe lab-card-stripe--navy"></div>
      <div class="lab-card-body">
        <div class="lab-card-icon lab-card-icon--navy">🧪</div>
        <h3>Equipment Labs</h3>
        <p>Book specific equipment across multiple rooms in Al Zahrawi and Avicenna blocks. You can select equipment from different rooms in a single booking.</p>
        <div class="lab-card-meta">
          <span class="badge badge-navy">10 Rooms</span>
          <span class="badge badge-navy">8:00 – 17:00</span>
          <span class="badge badge-navy">Min. 60 min</span>
          <span class="badge badge-green">Weekdays only</span>
        </div>
        <p style="font-size:.8rem;color:var(--text-light);">Includes: Plant Extraction, Molecular, Assay, Microbiology, Instrumentation, MDL3, and more.</p>
      </div>
      <div class="lab-card-footer">
        <span class="lab-card-count">Al Zahrawi &amp; Avicenna Blocks</span>
        <a href="booking/equipment.php" class="btn btn-primary btn-sm">Book Now →</a>
      </div>
    </div>

    <!-- Card 2: CSL Labs -->
    <div class="lab-card">
      <div class="lab-card-stripe lab-card-stripe--teal"></div>
      <div class="lab-card-body">
        <div class="lab-card-icon lab-card-icon--teal">🏥</div>
        <h3>CSL Labs</h3>
        <p>Clinical Skills Labs with 16 simulation rooms. Clinical instructor assistance is automatically assigned. Includes 30-minute setup and cleanup buffer automatically.</p>
        <div class="lab-card-meta">
          <span class="badge badge-teal">16 Rooms</span>
          <span class="badge badge-teal">1 Day Advance</span>
          <span class="badge badge-teal">30 min Buffer</span>
          <span class="badge badge-green">Min. 60 min</span>
        </div>
        <p style="font-size:.8rem;color:var(--text-light);">CSL 1 (Physiko, Mock Ward, Simulation) & CSL 2 (Room 1–12, Discussion).</p>
      </div>
      <div class="lab-card-footer">
        <span class="lab-card-count">CSL 1 &amp; CSL 2</span>
        <a href="booking/csl.php" class="btn btn-primary btn-sm" style="background:#1a7a6e;">Book Now →</a>
      </div>
    </div>

    <!-- Card 3: Pharma Labs -->
    <div class="lab-card">
      <div class="lab-card-stripe lab-card-stripe--violet"></div>
      <div class="lab-card-body">
        <div class="lab-card-icon lab-card-icon--violet">⚗️</div>
        <h3>Pharma Labs</h3>
        <p>Group-based bookings for CL, MDLP, PL1, and PL2. Available after working hours and weekends. Multiple lecturers and groups can be booked at once.</p>
        <div class="lab-card-meta">
          <span class="badge badge-violet">4 Labs</span>
          <span class="badge badge-violet">After Hours</span>
          <span class="badge badge-violet">Weekend OK</span>
          <span class="badge badge-violet">Max 4/group</span>
        </div>
        <p style="font-size:.8rem;color:var(--text-light);">CL (5 groups) · MDLP, PL1, PL2 (10 groups each) · Avicenna Block, Level 1.</p>
      </div>
      <div class="lab-card-footer">
        <span class="lab-card-count">Avicenna Block — Level 1</span>
        <a href="booking/pharma.php" class="btn btn-primary btn-sm" style="background:#5b3fa0;">Book Now →</a>
      </div>
    </div>

  </div>
</section>

<hr class="divider" style="margin:0;" />

<!-- Quick notice -->
<div class="container" style="padding-top:32px;padding-bottom:48px;">
  <div class="alert alert-info">
    <span>ℹ️</span>
    <div>
      <strong>Booking Policy Reminder:</strong> All bookings are subject to admin approval. You will receive an email notification once your booking status is updated. For urgent requests, contact your department admin directly.
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
