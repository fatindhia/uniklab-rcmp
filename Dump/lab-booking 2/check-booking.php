<?php
$pageTitle = 'Check Booking Status';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div class="page-header-inner">
    <div class="breadcrumb">
      <a href="index.php">Home</a><span class="breadcrumb-sep">/</span><span>Check Booking Status</span>
    </div>
    <h1>Check Booking Status</h1>
    <p>Enter your UniKL ID or email address to look up your booking requests. No login required.</p>
  </div>
</div>

<div class="container container--narrow check-page">

  <!-- Search Form -->
  <div class="card check-search-card">
    <div class="card-header">
      <h2>Find Your Booking</h2>
      <span class="check-chip">Trace Request</span>
    </div>
    <div class="card-body">
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">UniKL ID / Staff ID</label>
          <input type="text" id="searchId" class="form-control" placeholder="e.g. 20231234 or S12345" />
        </div>
        <div class="form-group">
          <label class="form-label">— or — Email Address</label>
          <input type="email" id="searchEmail" class="form-control" placeholder="your@unikl.edu.my" />
        </div>
      </div>
      <div class="check-actions">
        <button class="btn btn-primary" onclick="searchBookings()">Search Bookings</button>
        <button class="btn btn-ghost" onclick="clearSearch()">Clear</button>
      </div>
    </div>
  </div>

  <!-- Results area -->
  <div id="resultsArea" style="display:none;">

    <!-- Empty state -->
    <div id="emptyState" style="display:none;">
      <div class="placeholder-body" style="padding:48px 0;">
        <div class="placeholder-icon">🔍</div>
        <h2>No Bookings Found</h2>
        <p>We couldn't find any bookings linked to that ID or email. Please double-check and try again.</p>
      </div>
    </div>

    <!-- Results list -->
    <div id="resultsList"></div>

  </div>

  <!-- Info note -->
  <div class="alert alert-info" style="margin-top:24px;">
    <span>ℹ️</span>
    <div>
      <strong>Note:</strong> Booking status is updated by the department admin. If your booking is still <em>Pending</em>, please allow 1–2 working days for processing. For urgent matters, contact your department directly.
    </div>
  </div>

  <!-- Status legend -->
  <div class="status-legend">
    <div class="sl-title">Status Guide</div>
    <div class="sl-items">
      <div class="sl-item"><span class="status-badge status-pending">Pending</span><span>Submitted, awaiting admin review</span></div>
      <div class="sl-item"><span class="status-badge status-approved">Approved</span><span>Confirmed — room/equipment reserved</span></div>
      <div class="sl-item"><span class="status-badge status-rejected">Rejected</span><span>Not approved — see admin remarks</span></div>
      <div class="sl-item"><span class="status-badge status-cancelled">Cancelled</span><span>Cancelled by user or admin</span></div>
    </div>
  </div>

</div>

<style>
.check-page{padding-top:34px;padding-bottom:64px;}
.check-search-card{margin-bottom:24px;}
.check-chip{font-size:.67rem;letter-spacing:.08em;text-transform:uppercase;font-weight:700;color:#202734;background:#e7ecec;border:1px solid #d0deee;border-radius:999px;padding:4px 10px;}
.check-actions{margin-top:16px;display:flex;gap:10px;flex-wrap:wrap;}
.status-legend{margin-top:24px;padding:18px 20px;background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);}
.sl-title{font-size:.75rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--text-light);margin-bottom:12px;}
.sl-items{display:flex;flex-wrap:wrap;gap:12px;}
.sl-item{display:flex;align-items:center;gap:10px;font-size:.82rem;color:var(--text-mid);}
.status-badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:.72rem;font-weight:700;letter-spacing:.04em;flex-shrink:0;}
.status-pending  {background:#f9f1d8;color:#92620a;border:1px solid #f0d080;}
.status-approved {background:var(--success-bg);color:var(--success);border:1px solid #a3d9b8;}
.status-rejected {background:var(--danger-bg);color:var(--danger);border:1px solid #f0a89f;}
.status-cancelled{background:var(--off-white);color:var(--text-light);border:1px solid var(--border);}

.booking-result-card{background:linear-gradient(180deg,#fff 0%,#fbfcfa 100%);border:1.5px solid var(--border);border-radius:var(--radius-lg);margin-bottom:14px;overflow:hidden;box-shadow:var(--shadow-sm);}
.brc-header{display:flex;align-items:flex-start;justify-content:space-between;padding:16px 20px;gap:12px;flex-wrap:wrap;}
.brc-ref{font-size:.72rem;font-weight:700;color:var(--text-light);letter-spacing:.06em;text-transform:uppercase;margin-bottom:4px;}
.brc-name{font-size:1rem;font-weight:700;color:var(--navy);font-family:var(--font-serif);}
.brc-type{font-size:.78rem;color:var(--text-mid);margin-top:2px;}
.brc-body{padding:14px 20px;border-top:1px solid var(--border);background:var(--off-white);display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:10px;}
.brc-field span{display:block;font-size:.68rem;color:var(--text-light);font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px;}
.brc-field strong{font-size:.85rem;color:var(--text);}
.brc-footer{padding:12px 20px;border-top:1px solid var(--border);background:var(--white);display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;font-size:.8rem;color:var(--text-light);}
.brc-admin-remark{font-size:.82rem;color:var(--text-mid);font-style:italic;}
@media(max-width:600px){.brc-body{grid-template-columns:1fr 1fr;}}
</style>

<script>
// --- Demo data (replace with real PHP/DB fetch later) ---
const DEMO_BOOKINGS = [
  {
    ref: 'BK-2024-0042', type: 'Equipment Labs', type_icon: '🧪',
    name: 'Ahmad Firdaus bin Zulkifli', id: '20231234', email: '20231234@student.unikl.edu.my',
    date: '2024-11-18', time: '09:00 – 11:00', rooms: 'Molecular Room (A2051), Assay Room (A2054)',
    purpose: 'FYP Sample Analysis', status: 'approved',
    submitted: '2024-11-15', admin_remark: 'Approved. Please ensure you follow ISO17025 protocols.',
  },
  {
    ref: 'BK-2024-0051', type: 'CSL Labs', type_icon: '🏥',
    name: 'Ahmad Firdaus bin Zulkifli', id: '20231234', email: '20231234@student.unikl.edu.my',
    date: '2024-11-22', time: '14:00 – 16:00', rooms: 'CSL2 Room 3 (Wound Dressing), CSL2 Room 11 (Suturing)',
    purpose: 'Year 3 Surgical Skills Practice', status: 'pending',
    submitted: '2024-11-20', admin_remark: '',
  },
  {
    ref: 'BK-2024-0038', type: 'Pharma Labs', type_icon: '⚗️',
    name: 'Ahmad Firdaus bin Zulkifli', id: '20231234', email: '20231234@student.unikl.edu.my',
    date: '2024-11-10', time: '18:00 – 21:00', rooms: 'CL — 2 groups, PL1 — 1 group',
    purpose: 'Pharmaceutical Chemistry Lab Session', status: 'rejected',
    submitted: '2024-11-08', admin_remark: 'Lab not available on this date — public holiday. Please rebook.',
  },
];

function searchBookings() {
  const sid   = document.getElementById('searchId').value.trim();
  const email = document.getElementById('searchEmail').value.trim().toLowerCase();
  const area  = document.getElementById('resultsArea');
  const list  = document.getElementById('resultsList');
  const empty = document.getElementById('emptyState');

  if (!sid && !email) { alert('Please enter your UniKL ID or email address.'); return; }

  // Filter demo data
  const results = DEMO_BOOKINGS.filter(b =>
    (sid   && b.id.toLowerCase() === sid.toLowerCase()) ||
    (email && b.email.toLowerCase() === email.toLowerCase())
  );

  area.style.display = 'block';

  if (!results.length) {
    empty.style.display = 'block';
    list.innerHTML = '';
    return;
  }

  empty.style.display = 'none';
  list.innerHTML = results.map(b => `
    <div class="booking-result-card">
      <div class="brc-header">
        <div>
          <div class="brc-ref">Booking Ref: ${b.ref}</div>
          <div class="brc-name">${b.type_icon} ${b.type}</div>
          <div class="brc-type">Submitted: ${b.submitted}</div>
        </div>
        <span class="status-badge status-${b.status}">${b.status.charAt(0).toUpperCase()+b.status.slice(1)}</span>
      </div>
      <div class="brc-body">
        <div class="brc-field"><span>Booking Date</span><strong>${b.date}</strong></div>
        <div class="brc-field"><span>Time Slot</span><strong>${b.time}</strong></div>
        <div class="brc-field"><span>Rooms / Labs</span><strong>${b.rooms}</strong></div>
        <div class="brc-field"><span>Purpose</span><strong>${b.purpose}</strong></div>
      </div>
      ${b.admin_remark ? `<div class="brc-footer"><span>Admin Remark:</span> <span class="brc-admin-remark">${b.admin_remark}</span></div>` : ''}
    </div>
  `).join('');
}

function clearSearch() {
  document.getElementById('searchId').value = '';
  document.getElementById('searchEmail').value = '';
  document.getElementById('resultsArea').style.display = 'none';
}

// Allow Enter key
document.addEventListener('keydown', e => { if (e.key === 'Enter') searchBookings(); });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
