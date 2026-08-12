<?php
/**
 * _booking_table.php  — shared helper
 * Renders a filterable booking table + the booking detail modal
 * (view · approve/reject with remark · CSL room reassign · audit trail)
 * ported from previous-used/dashboard.php.
 *
 * Expects:
 *   $tableBookings  — array of booking rows to display
 *   $tableId        — unique HTML id for the <table>
 *   $showType       — bool: show lab-type column?
 */
$tableBookings = $tableBookings ?? [];
$tableId       = $tableId       ?? 'bookingTable';
$showType      = $showType      ?? true;

// CSL rooms — used by the "Reassign Room" control (from the original CSL_ROOMS).
$CSL_ROOMS = [
  'CSL1 – Physiko Room','CSL1 – Mock Ward','CSL1 – Simulation Room',
  'CSL2 – Room 1','CSL2 – Room 2','CSL2 – Room 3','CSL2 – Room 4','CSL2 – Room 5',
  'CSL2 – Room 6','CSL2 – Room 7','CSL2 – Room 8','CSL2 – Room 9','CSL2 – Room 10',
  'CSL2 – Room 11','CSL2 – Room 12','CSL2 – Discussion Room',
];

// Data for the modal, keyed by ref.
$bkData = [];
foreach ($tableBookings as $b) { $bkData[$b['ref']] = $b; }
?>

<div class="filter-bar">
  <div class="search-input-wrap">
    <input type="text" class="form-control" id="<?= $tableId ?>Search"
           placeholder="Search name, ref, lab…"
           oninput="liveSearch('<?= $tableId ?>')">
  </div>
  <select class="form-control" id="<?= $tableId ?>StatusFilter"
          onchange="liveFilter('<?= $tableId ?>')">
    <option value="">All Statuses</option>
    <option value="approved">Approved</option>
    <option value="pending">Pending</option>
    <option value="rejected">Rejected</option>
    <option value="cancelled">Cancelled</option>
  </select>
  <?php if ($showType): ?>
  <select class="form-control" id="<?= $tableId ?>TypeFilter"
          onchange="liveFilter('<?= $tableId ?>')">
    <option value="">All Types</option>
    <option value="research">Research</option>
    <option value="csl">CSL</option>
    <option value="pharma">Pharma</option>
  </select>
  <?php endif; ?>
  <span style="margin-left:auto;font-size:.76rem;color:var(--text-light);" id="<?= $tableId ?>Count">
    <?= count($tableBookings) ?> record<?= count($tableBookings) !== 1 ? 's' : '' ?>
  </span>
</div>

<div class="data-table-wrap">
  <table class="data-table" id="<?= $tableId ?>">
    <thead>
      <tr>
        <th>Ref #</th>
        <th>Applicant</th>
        <th>Lab</th>
        <?php if ($showType): ?><th>Type</th><?php endif; ?>
        <th>Date</th>
        <th>Time</th>
        <th>Equipment</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($tableBookings)): ?>
        <tr><td colspan="<?= $showType ? 9 : 8 ?>" style="text-align:center;padding:30px;color:var(--text-light);font-style:italic;">No bookings found.</td></tr>
      <?php else: ?>
        <?php foreach ($tableBookings as $b): ?>
        <tr data-ref="<?= htmlspecialchars($b['ref']) ?>"
            data-status="<?= htmlspecialchars($b['status']) ?>"
            data-type="<?= htmlspecialchars($b['lab_type']) ?>">
          <td><span class="ref-code"><?= htmlspecialchars($b['ref']) ?></span></td>
          <td>
            <div style="font-weight:600;font-size:.82rem;"><?= htmlspecialchars($b['name']) ?></div>
            <div style="font-size:.71rem;color:var(--text-light);"><?= htmlspecialchars($b['id']) ?></div>
          </td>
          <td class="bk-lab-cell"><?= htmlspecialchars($b['lab']) ?></td>
          <?php if ($showType): ?>
          <td><span class="lab-type-tag lab-type-<?= $b['lab_type'] ?>"><?= strtoupper($b['lab_type']) ?></span></td>
          <?php endif; ?>
          <td style="white-space:nowrap;"><?= htmlspecialchars($b['date']) ?></td>
          <td style="white-space:nowrap;"><?= htmlspecialchars($b['start']) ?> – <?= htmlspecialchars($b['end']) ?></td>
          <td style="font-size:.75rem;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($b['equipment']) ?>"><?= htmlspecialchars($b['equipment']) ?></td>
          <td class="bk-status-cell"><span class="badge badge-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
          <td class="bk-action-cell">
            <div class="action-cluster">
              <button class="btn btn-outline btn-xs bk-view-btn" onclick="viewBooking('<?= htmlspecialchars($b['ref']) ?>')" title="View &amp; review">👁 View</button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- ── Booking Detail Modal ── -->
<div class="bk-modal-overlay" id="bkModalOverlay" onclick="bkCloseModal(event)">
  <div class="bk-modal" onclick="event.stopPropagation()">
    <div class="bk-modal-header">
      <div class="bk-modal-head-main">
        <span class="bk-modal-ref" id="bkModalRef">—</span>
        <h3 id="bkModalTitle">Booking Details</h3>
      </div>
      <button class="bk-modal-close" onclick="bkCloseModal()">✕</button>
    </div>
    <div class="bk-modal-body" id="bkModalBody"></div>
    <div class="bk-modal-footer" id="bkModalFooter"></div>
  </div>
</div>

<style>
/* Booking detail modal (bk- prefixed to stay isolated) */
.bk-modal-overlay{display:none;position:fixed;inset:0;background:rgba(16,28,38,.55);backdrop-filter:blur(3px);z-index:999;align-items:center;justify-content:center;padding:20px;}
.bk-modal-overlay.open{display:flex;animation:bkFade .16s ease;}
@keyframes bkFade{from{opacity:0;}to{opacity:1;}}
.bk-modal{background:#fff;border-radius:var(--r-xl);box-shadow:var(--shadow-lg);width:100%;max-width:680px;max-height:90vh;overflow-y:auto;animation:bkPop .18s ease;}
@keyframes bkPop{from{transform:translateY(10px) scale(.985);opacity:0;}to{transform:none;opacity:1;}}
.bk-modal-header{position:sticky;top:0;z-index:2;overflow:hidden;display:flex;align-items:flex-start;justify-content:space-between;gap:12px;padding:18px 22px;background:linear-gradient(125deg,#1b232f 0%,#244049 55%,#2f6d78 100%);color:#fff;}
.bk-modal-header::after{content:'';position:absolute;right:-60px;top:-80px;width:200px;height:200px;border-radius:50%;background:radial-gradient(circle,rgba(124,195,204,.28) 0%,rgba(124,195,204,0) 70%);pointer-events:none;}
.bk-modal-head-main{position:relative;z-index:1;min-width:0;}
.bk-modal-ref{display:inline-block;font-family:monospace;font-size:.7rem;font-weight:700;letter-spacing:.04em;background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.26);padding:3px 10px;border-radius:999px;margin-bottom:9px;}
.bk-modal-header h3{font-family:var(--font-serif);font-size:1.05rem;font-weight:700;color:#fff;margin:0;line-height:1.2;}
.bk-modal-close{position:relative;z-index:1;background:rgba(255,255,255,.18);border:none;width:30px;height:30px;border-radius:50%;color:#fff;font-size:.9rem;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background .14s;flex-shrink:0;}
.bk-modal-close:hover{background:rgba(255,255,255,.34);}
.bk-modal-body{padding:20px 22px;}

/* Applicant summary strip */
.bk-summary{display:flex;align-items:center;gap:14px;padding:13px 15px;border:1px solid var(--border-light);border-radius:var(--r-lg);background:var(--off-white);margin-bottom:18px;}
.bk-summary-ava{width:46px;height:46px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:1.05rem;flex-shrink:0;box-shadow:0 4px 12px rgba(32,39,52,.18);}
.bk-summary-main{flex:1;min-width:0;}
.bk-summary-name{font-weight:700;font-size:.95rem;color:var(--navy);}
.bk-summary-sub{font-size:.74rem;color:var(--text-mid);margin-top:4px;display:flex;align-items:center;gap:9px;flex-wrap:wrap;}

.bk-section{margin-bottom:18px;}
.bk-section:last-child{margin-bottom:0;}
.bk-section-title{font-size:.66rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--text-light);margin-bottom:11px;display:flex;align-items:center;gap:9px;}
.bk-section-title::after{content:'';flex:1;height:1px;background:var(--border);}
.bk-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;}
.bk-field{display:flex;flex-direction:column;gap:3px;padding:10px 12px;background:#fff;border:1px solid var(--border-light);border-radius:var(--r);}
.bk-field span{font-size:.61rem;color:var(--text-light);font-weight:700;text-transform:uppercase;letter-spacing:.05em;}
.bk-field strong{font-size:.82rem;color:var(--text);font-weight:600;}
.bk-field.full{grid-column:1/-1;}

/* Reassign (CSL) */
.bk-reassign{background:var(--blue-light);border:1px solid #c4d2d6;border-radius:var(--r);padding:13px 15px;}
.bk-reassign-title{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#5d7c86;margin-bottom:9px;}
.bk-reassign-current{font-size:.8rem;color:var(--text-mid);margin-bottom:7px;}
.bk-reassign-row{display:flex;align-items:center;gap:7px;flex-wrap:wrap;}
.bk-reassign-row select{flex:1;min-width:160px;padding:7px 10px;border:1.5px solid #c4d2d6;border-radius:var(--r-sm);font-family:var(--font-sans);font-size:.8rem;background:#fff;color:var(--text);}
.bk-reassign-btn{padding:7px 14px;font-size:.76rem;font-weight:700;background:#5d7c86;color:#fff;border:none;border-radius:var(--r-sm);cursor:pointer;transition:filter .14s;}
.bk-reassign-btn:hover{filter:brightness(1.08);}

/* Admin response zone — highlighted so it stands apart from the read-only details */
.bk-action-zone{border:1.5px solid #e3b94f;border-radius:var(--r-lg);overflow:hidden;background:linear-gradient(180deg,#fffaf0,#fffdf9);box-shadow:0 10px 26px rgba(160,124,31,.18);animation:bkPulse 1.7s ease-out 1;}
@keyframes bkPulse{0%{box-shadow:0 0 0 0 rgba(199,154,58,.5);}70%{box-shadow:0 0 0 12px rgba(199,154,58,0);}100%{box-shadow:0 10px 26px rgba(160,124,31,.18);}}
.bk-action-head{display:flex;align-items:center;gap:12px;padding:12px 16px;background:linear-gradient(120deg,#a07c1f 0%,#c79a3a 100%);color:#fff;}
.bk-action-icon{width:34px;height:34px;border-radius:10px;background:rgba(255,255,255,.22);display:flex;align-items:center;justify-content:center;font-size:1.05rem;flex-shrink:0;}
.bk-action-title{font-weight:700;font-size:.9rem;}
.bk-action-sub{font-size:.71rem;opacity:.92;margin-top:1px;}
.bk-action-body{padding:15px 16px;}
.bk-action-btns{display:flex;justify-content:flex-end;gap:9px;margin-top:13px;}
.bk-remark-label{display:block;font-size:.68rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#8a6a16;margin-bottom:6px;}
.bk-remark-label em{font-style:normal;font-weight:600;text-transform:none;letter-spacing:0;color:var(--text-light);font-size:.66rem;}
.bk-remark{width:100%;min-height:78px;padding:10px 12px;border:1.5px solid #e0cf9c;border-radius:var(--r-sm);font-family:var(--font-sans);font-size:.81rem;color:var(--text);resize:vertical;outline:none;background:#fff;}
.bk-remark:focus{border-color:#c79a3a;box-shadow:0 0 0 3px rgba(199,154,58,.18);}

/* Audit trail */
.bk-audit{background:var(--off-white);border:1px solid var(--border-light);border-radius:var(--r);padding:11px 14px;}
.bk-audit-row{display:flex;align-items:flex-start;gap:10px;font-size:.76rem;padding:7px 0;border-bottom:1px solid var(--border);}
.bk-audit-row:last-child{border-bottom:none;}
.bk-audit-dot{width:8px;height:8px;border-radius:50%;margin-top:4px;flex-shrink:0;background:var(--text-light);}
.bk-audit-dot.created{background:var(--teal);}
.bk-audit-dot.approved{background:var(--success);}
.bk-audit-dot.rejected{background:var(--danger);}
.bk-audit-dot.reassigned{background:#5d7c86;}
.bk-audit-dot.cancelled{background:var(--inactive);}
.bk-audit-action{font-weight:600;color:var(--text);}
.bk-audit-meta{color:var(--text-light);font-size:.7rem;margin-top:1px;}
.bk-audit-detail{color:var(--text-mid);font-size:.73rem;margin-top:2px;font-style:italic;}

/* Footer + decision buttons */
.bk-modal-footer{padding:14px 22px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:9px;background:#fff;position:sticky;bottom:0;}
.bk-btn-approve,.bk-btn-reject,.bk-btn-ghost{border:none;border-radius:var(--r-sm);padding:9px 20px;font-family:var(--font-sans);font-size:.82rem;font-weight:700;cursor:pointer;transition:filter .14s,background .14s;}
.bk-btn-approve{background:var(--success);color:#fff;}
.bk-btn-reject{background:var(--danger);color:#fff;}
.bk-btn-approve:hover,.bk-btn-reject:hover{filter:brightness(1.08);}
.bk-btn-ghost{background:var(--off-white);color:var(--text-mid);border:1px solid var(--border);font-weight:600;}
.bk-btn-ghost:hover{background:var(--navy-light);}
.bk-btn-cancel{background:#fff;color:var(--danger);border:1.5px solid #f0a89f;border-radius:var(--r-sm);padding:9px 18px;font-family:var(--font-sans);font-size:.82rem;font-weight:700;cursor:pointer;transition:all .14s;}
.bk-btn-cancel:hover{background:var(--danger);color:#fff;border-color:var(--danger);}
.bk-view-btn{white-space:nowrap;}
</style>

<script>
(function () {
  const BK_DATA      = <?= json_encode($bkData, JSON_UNESCAPED_UNICODE) ?>;
  const BK_CSL_ROOMS = <?= json_encode($CSL_ROOMS, JSON_UNESCAPED_UNICODE) ?>;
  const CURRENT_ADMIN = 'Administrator';
  const BK_TYPE_COLORS = { research: '#202734', csl: '#5d7c86', pharma: '#2a7782' };

  function esc(s) { return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
  function ucfirst(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; }

  function badge(status) { return `<span class="badge badge-${esc(status)}">${ucfirst(status)}</span>`; }

  function buildAudit(audit) {
    if (!audit || !audit.length) {
      return '<div class="bk-audit"><div style="font-size:.78rem;color:var(--text-light);font-style:italic;">No records yet.</div></div>';
    }
    return '<div class="bk-audit">' + audit.map(a => `
      <div class="bk-audit-row">
        <div class="bk-audit-dot ${esc(a.type)}"></div>
        <div>
          <div class="bk-audit-action">${esc(a.action)}</div>
          <div class="bk-audit-meta">By <strong>${esc(a.by)}</strong> · ${esc(a.at)}</div>
          ${a.detail ? `<div class="bk-audit-detail">${esc(a.detail)}</div>` : ''}
        </div>
      </div>`).join('') + '</div>';
  }

  window.viewBooking = function (ref) {
    const d = BK_DATA[ref];
    if (!d) return;
    document.getElementById('bkModalRef').textContent = d.ref;
    document.getElementById('bkModalTitle').textContent = d.type_label;

    const tcol = BK_TYPE_COLORS[d.lab_type] || '#50a7b2';
    const summary = `<div class="bk-summary">
      <div class="bk-summary-ava" style="background:${tcol}">${esc(String(d.name || '?').charAt(0).toUpperCase())}</div>
      <div class="bk-summary-main">
        <div class="bk-summary-name">${esc(d.name)}</div>
        <div class="bk-summary-sub">
          <span class="lab-type-tag lab-type-${esc(d.lab_type)}">${esc(String(d.lab_type || '').toUpperCase())}</span>
          <span>🆔 ${esc(d.id)}</span>
          ${badge(d.status)}
        </div>
      </div>
    </div>`;

    const fields = [
      ['Email', esc(d.email || '—'), 'full'],
      ['Date', esc(d.date)],
      ['Time', `${esc(d.start)} – ${esc(d.end)}`],
      ['Rooms / Lab', esc(d.lab), 'full'],
      ['Equipment', esc(d.equipment || '—'), 'full'],
      ['Purpose', esc(d.purpose || '—'), 'full'],
    ];
    const fHtml = `<div class="bk-grid">${fields.map(([l, v, c]) =>
      `<div class="bk-field${c ? ' ' + c : ''}"><span>${l}</span><strong>${v}</strong></div>`).join('')}</div>`;

    let reassign = '';
    if (d.lab_type === 'csl') {
      const opts = BK_CSL_ROOMS.map(r => `<option value="${esc(r)}"${r === d.lab ? ' selected' : ''}>${esc(r)}</option>`).join('');
      reassign = `<div class="bk-section"><div class="bk-section-title">Room Management</div>
        <div class="bk-reassign"><div class="bk-reassign-title">🔄 Reassign Room</div>
          <div class="bk-reassign-current">Currently: <strong>${esc(d.lab)}</strong></div>
          <div class="bk-reassign-row"><select id="bkReassignSelect">${opts}</select>
            <button class="bk-reassign-btn" onclick="bkReassign('${esc(ref)}')">Reassign</button></div>
        </div></div>`;
    }

    let decision = '';
    if (d.status === 'pending') {
      decision = `<div class="bk-section">
        <div class="bk-action-zone">
          <div class="bk-action-head">
            <span class="bk-action-icon">⚖️</span>
            <div>
              <div class="bk-action-title">Action Required</div>
              <div class="bk-action-sub">Review the details above, then approve or reject with a remark.</div>
            </div>
          </div>
          <div class="bk-action-body">
            <label class="bk-remark-label">Remark <em>(required)</em></label>
            <textarea id="bkRemark" class="bk-remark" placeholder="Write reason / notes…">${esc(d.admin_remark || '')}</textarea>
            <div class="bk-action-btns">
              <button class="bk-btn-reject" onclick="bkDecide('${esc(ref)}','rejected')">✕ Reject</button>
              <button class="bk-btn-approve" onclick="bkDecide('${esc(ref)}','approved')">✓ Approve</button>
            </div>
          </div>
        </div>
      </div>`;
    }

    let remarkView = '';
    if (d.status !== 'pending' && d.admin_remark) {
      remarkView = `<div class="bk-section"><div class="bk-section-title">Admin Remark</div>
        <div class="bk-field full"><span>Remark</span><strong>${esc(d.admin_remark)}</strong></div></div>`;
    }

    document.getElementById('bkModalBody').innerHTML =
      summary +
      `<div class="bk-section"><div class="bk-section-title">Submission Details</div>${fHtml}</div>` +
      reassign + decision + remarkView +
      `<div class="bk-section"><div class="bk-section-title">📋 Audit Trail</div>${buildAudit(d.audit || [])}</div>`;

    const cancelBtn = d.status !== 'cancelled'
      ? `<button class="bk-btn-cancel" onclick="bkCancel('${esc(ref)}')">🚫 Cancel Booking</button>` : '';
    document.getElementById('bkModalFooter').innerHTML =
      cancelBtn + `<button class="bk-btn-ghost" onclick="bkCloseModal()" style="margin-left:auto;">Close</button>`;

    document.getElementById('bkModalOverlay').classList.add('open');
  };

  window.bkCloseModal = function (e) {
    if (!e || e.target === document.getElementById('bkModalOverlay')) {
      document.getElementById('bkModalOverlay').classList.remove('open');
    }
  };

  function addAudit(d, action, by, detail, type) {
    const n = new Date();
    const p = x => String(x).padStart(2, '0');
    const at = `${n.getFullYear()}-${p(n.getMonth()+1)}-${p(n.getDate())} ${p(n.getHours())}:${p(n.getMinutes())}`;
    d.audit = d.audit || [];
    d.audit.push({ action, by, at, detail, type });
  }

  function refreshRow(ref) {
    const d = BK_DATA[ref];
    document.querySelectorAll(`tr[data-ref="${CSS.escape(ref)}"]`).forEach(row => {
      row.dataset.status = d.status;
      const sc = row.querySelector('.bk-status-cell');
      if (sc) sc.innerHTML = badge(d.status);
      const lc = row.querySelector('.bk-lab-cell');
      if (lc) lc.textContent = d.lab;
      const ac = row.querySelector('.bk-action-cell .action-cluster');
      if (ac) {
        ac.innerHTML = `<button class="btn btn-outline btn-xs bk-view-btn" onclick="viewBooking('${esc(ref)}')" title="View & review">👁 View</button>`;
      }
    });
  }

  function applyDecision(ref, status, remark) {
    const d = BK_DATA[ref];
    if (!d) return;
    d.status = status;
    d.admin_remark = remark;
    addAudit(d, status === 'approved' ? 'Approved' : 'Rejected', CURRENT_ADMIN, remark, status);
    refreshRow(ref);
    showToast(`Booking ${ref} ${status} (UI demo).`, status === 'approved' ? 'success' : 'danger');
  }

  // Decision from inside the modal (uses the remark textarea).
  window.bkDecide = function (ref, status) {
    const remark = document.getElementById('bkRemark')?.value?.trim();
    if (!remark) { showToast('Please provide a remark first.', 'danger'); document.getElementById('bkRemark')?.focus(); return; }
    applyDecision(ref, status, remark);
    bkCloseModal();
  };

  // (Quick approve/reject removed — decisions are now made inside the detail modal.)

  // Cancel a booking — available for any status (admin override).
  window.bkCancel = function (ref) {
    const d = BK_DATA[ref];
    if (!d || d.status === 'cancelled') return;
    uiPrompt({
      title: 'Cancel this booking?',
      message: `${ref} will be marked as cancelled. Add an optional reason below.`,
      variant: 'danger',
      placeholder: 'Reason (optional)…',
      confirmText: '🚫 Cancel Booking',
      cancelText: 'Keep Booking',
      onConfirm: function (reason) {
        d.status = 'cancelled';
        if (reason) d.admin_remark = reason;
        addAudit(d, 'Cancelled', CURRENT_ADMIN, reason || 'Cancelled by admin.', 'cancelled');
        refreshRow(ref);
        showToast(`Booking ${ref} cancelled (UI demo).`, 'danger');
        bkCloseModal();
      }
    });
  };

  window.bkReassign = function (ref) {
    const d = BK_DATA[ref];
    const nr = document.getElementById('bkReassignSelect')?.value;
    if (!nr || nr === d.lab) { showToast('Select a different room.', ''); return; }
    const old = d.lab;
    d.lab = nr;
    addAudit(d, 'Room Reassigned', CURRENT_ADMIN, `Changed from "${old}" to "${nr}"`, 'reassigned');
    refreshRow(ref);
    viewBooking(ref);
    showToast(`Room reassigned to ${nr} (UI demo).`, 'success');
  };
})();

function liveSearch(tid) {
  const q   = document.getElementById(tid+'Search')?.value?.toLowerCase() ?? '';
  const sf  = document.getElementById(tid+'StatusFilter')?.value?.toLowerCase() ?? '';
  const tf  = document.getElementById(tid+'TypeFilter')?.value?.toLowerCase() ?? '';
  const rows = document.querySelectorAll('#'+tid+' tbody tr[data-status]');
  let vis = 0;
  rows.forEach(row => {
    const text   = row.textContent.toLowerCase();
    const status = row.dataset.status ?? '';
    const type   = row.dataset.type   ?? '';
    const show = (!q || text.includes(q)) && (!sf || status === sf) && (!tf || type === tf);
    row.style.display = show ? '' : 'none';
    if (show) vis++;
  });
  const cEl = document.getElementById(tid+'Count');
  if (cEl) cEl.textContent = vis + ' record' + (vis !== 1 ? 's' : '');
}
function liveFilter(tid) { liveSearch(tid); }
</script>
