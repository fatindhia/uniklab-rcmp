<?php
/**
 * pages/manage-labs.php
 */
require_once __DIR__ . '/../data/labs.php';

$labStats = [
  ['label' => 'Total',       'count' => count($LABS),                        'icon' => '🏛️', 'accent' => 'c-total'],
  ['label' => 'Active',      'count' => labCount($LABS, null, 'active'),      'icon' => '✅', 'accent' => 'c-approved'],
  ['label' => 'Maintenance', 'count' => labCount($LABS, null, 'maintenance'), 'icon' => '🔧', 'accent' => 'c-pending'],
  ['label' => 'Inactive',    'count' => labCount($LABS, null, 'inactive'),    'icon' => '⬜', 'accent' => 'c-inactive'],
];
?>

<div class="listpage">
  <!-- ── Hero ── -->
  <div class="page-hero">
    <div class="page-hero-text">
      <span class="page-hero-eyebrow">🏛️ Facilities</span>
      <h1>Manage Labs</h1>
      <p>Lab directory with quick status changes and an add / edit flow across all categories.</p>
    </div>
    <div class="page-hero-side">
      <button class="page-hero-btn page-hero-btn--solid" onclick="openAddLabModal()">+ Add New Lab</button>
    </div>
  </div>

  <!-- ── Stat Cards ── -->
  <div class="stat-grid stat-accent">
    <?php foreach ($labStats as $stat): ?>
    <div class="stat-card <?= $stat['accent'] ?>">
      <div class="stat-card-top">
        <div class="stat-icon"><?= $stat['icon'] ?></div>
      </div>
      <div class="stat-num"><?= $stat['count'] ?></div>
      <div class="stat-label"><?= htmlspecialchars($stat['label']) ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- ── Filter ── -->
  <div class="filter-bar" style="margin-bottom:16px;">
    <div class="search-input-wrap">
      <input type="text" class="form-control" id="labSearch" placeholder="Search lab name…" oninput="filterLabs()">
    </div>
    <select class="form-control" id="labTypeFilter" onchange="filterLabs()">
      <option value="">All Types</option>
      <option value="research">Research</option>
      <option value="csl">CSL</option>
      <option value="pharma">Pharma</option>
    </select>
    <select class="form-control" id="labStatusFilter" onchange="filterLabs()">
      <option value="">All Statuses</option>
      <option value="active">Active</option>
      <option value="maintenance">Maintenance</option>
      <option value="inactive">Inactive</option>
    </select>
  </div>

  <!-- ── Directory ── -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">🏛️ Laboratory Directory</span>
      <span style="font-size:.75rem;color:var(--text-light);" id="labCount"><?= count($LABS) ?> labs</span>
    </div>
    <div class="card-body-flush">
      <div class="data-table-wrap">
        <table class="data-table" id="labTable">
          <thead>
            <tr>
              <th>Lab ID</th>
              <th>Name</th>
              <th>Type</th>
              <th>Capacity</th>
              <th>Location</th>
              <th>In-Charge</th>
              <th>Equipment</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($LABS as $lab): ?>
            <tr data-lab-id="<?= htmlspecialchars($lab['id']) ?>" data-type="<?= $lab['type'] ?>" data-status="<?= $lab['status'] ?>">
              <td><span class="ref-code"><?= htmlspecialchars($lab['id']) ?></span></td>
              <td style="font-weight:700;font-size:.84rem;"><?= htmlspecialchars($lab['name']) ?></td>
              <td><span class="lab-type-tag lab-type-<?= $lab['type'] ?>"><?= strtoupper($lab['type']) ?></span></td>
              <td><?= $lab['capacity'] ?> pax</td>
              <td style="font-size:.78rem;"><?= htmlspecialchars($lab['location']) ?></td>
              <td style="font-size:.78rem;"><?= htmlspecialchars($lab['in_charge']) ?></td>
              <td style="font-size:.74rem;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($lab['equipment']) ?>"><?= htmlspecialchars($lab['equipment']) ?></td>
              <td><span class="badge badge-<?= $lab['status'] ?>"><?= ucfirst($lab['status']) ?></span></td>
              <td>
                <div class="action-cluster">
                  <button class="btn btn-outline btn-xs" onclick="uiEditLab('<?= htmlspecialchars($lab['id']) ?>')" title="Edit">✏️</button>
                  <?php if ($lab['status'] === 'active'): ?>
                    <button class="btn btn-warn btn-xs" onclick="uiToggleStatus('<?= htmlspecialchars($lab['id']) ?>','maintenance')" title="Set to Maintenance">🔧</button>
                  <?php elseif ($lab['status'] === 'maintenance'): ?>
                    <button class="btn btn-success btn-xs" onclick="uiToggleStatus('<?= htmlspecialchars($lab['id']) ?>','active')" title="Set to Active">✅</button>
                  <?php else: ?>
                    <button class="btn btn-success btn-xs" onclick="uiToggleStatus('<?= htmlspecialchars($lab['id']) ?>','active')" title="Activate">▶</button>
                  <?php endif; ?>
                  <button class="btn btn-danger btn-xs" onclick="uiDeleteLab('<?= htmlspecialchars($lab['id']) ?>')" title="Delete">🗑️</button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div><!-- /.listpage -->

<!-- Add Lab Modal (UI only) -->
<div class="lab-modal-overlay" id="addLabModal" onclick="if(event.target===this)closeAddLabModal()">
  <div class="lab-modal">
    <div class="lab-modal-head">
      <span class="card-title" style="color:#fff;">+ Add New Lab</span>
      <button class="lab-modal-x" onclick="closeAddLabModal()">✕</button>
    </div>
    <div style="padding:22px;">
      <div class="notice notice-info">This form is UI-only. Data will not be saved until a database is connected.</div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Lab Name</label>
          <input type="text" class="form-control" placeholder="e.g. Research Lab D">
        </div>
        <div class="form-group">
          <label class="form-label">Lab ID</label>
          <input type="text" class="form-control" placeholder="e.g. RL-D">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Type</label>
          <select class="form-control">
            <option>research</option><option>csl</option><option>pharma</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Capacity</label>
          <input type="number" class="form-control" placeholder="e.g. 12">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Location</label>
        <input type="text" class="form-control" placeholder="e.g. Block A, Level 3">
      </div>
      <div class="form-group">
        <label class="form-label">In-Charge</label>
        <input type="text" class="form-control" placeholder="Staff name">
      </div>
      <div class="form-group">
        <label class="form-label">Equipment</label>
        <textarea class="form-control" rows="2" placeholder="Comma-separated equipment list"></textarea>
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px;">
        <button class="btn btn-outline" onclick="closeAddLabModal()">Cancel</button>
        <button class="btn btn-primary" onclick="showToast('Lab added (UI demo — no DB).','success');closeAddLabModal()">Save Lab</button>
      </div>
    </div>
  </div>
</div>

<style>
  .lab-modal-overlay { display:none; position:fixed; inset:0; background:rgba(16,28,38,.55); -webkit-backdrop-filter:blur(3px); backdrop-filter:blur(3px); z-index:500; align-items:center; justify-content:center; padding:20px; }
  .lab-modal-overlay[style*="flex"] { animation:labFade .16s ease; }
  @keyframes labFade { from { opacity:0; } to { opacity:1; } }
  .lab-modal { background:#fff; border-radius:var(--r-xl); width:100%; max-width:520px; box-shadow:0 24px 60px rgba(16,36,54,.34); overflow:hidden; animation:labPop .2s cubic-bezier(.2,1.05,.4,1); }
  @keyframes labPop { from { transform:translateY(12px) scale(.97); opacity:0; } to { transform:none; opacity:1; } }
  .lab-modal-head { display:flex; align-items:center; justify-content:space-between; padding:15px 20px; background:linear-gradient(120deg,#202734,#2a7782); }
  .lab-modal-x { background:rgba(255,255,255,.18); border:none; width:28px; height:28px; border-radius:50%; color:#fff; font-size:.85rem; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background .14s; }
  .lab-modal-x:hover { background:rgba(255,255,255,.34); }
</style>

<script>
function openAddLabModal()  { document.getElementById('addLabModal').style.display='flex'; }
function closeAddLabModal() { document.getElementById('addLabModal').style.display='none'; }

function uiToggleStatus(id, newStatus) {
  const row = document.querySelector(`#labTable tbody tr[data-lab-id="${id}"]`);
  if (row) {
    row.dataset.status = newStatus;
    const statusCell = row.cells[7];
    if (statusCell) statusCell.innerHTML = `<span class="badge badge-${newStatus}">${newStatus.charAt(0).toUpperCase() + newStatus.slice(1)}</span>`;
  }
  showToast(`Lab ${id} → ${newStatus} (UI only — no DB).`, newStatus==='active'?'success':'');
}

function filterLabs() {
  const q  = document.getElementById('labSearch').value.toLowerCase();
  const tf = document.getElementById('labTypeFilter').value;
  const sf = document.getElementById('labStatusFilter').value;
  const rows = document.querySelectorAll('#labTable tbody tr');
  let vis = 0;
  rows.forEach(r => {
    const text = r.textContent.toLowerCase();
    const ok = (!q||text.includes(q)) && (!tf||r.dataset.type===tf) && (!sf||r.dataset.status===sf);
    r.style.display = ok ? '' : 'none';
    if(ok) vis++;
  });
  document.getElementById('labCount').textContent = vis + ' labs';
}
</script>
