<?php
/**
 * pages/manage-lab-staff.php
 */
require_once __DIR__ . '/../data/lab-staff.php';

$staffStats = [
  ['label' => 'Total Staff', 'count' => count($LAB_STAFF), 'icon' => 'ID', 'accent' => 'c-total'],
  ['label' => 'Admins', 'count' => labStaffCount($LAB_STAFF, 'admin'), 'icon' => 'A', 'accent' => 'c-approved'],
  ['label' => 'Lab Staff', 'count' => labStaffCount($LAB_STAFF, 'lab_staff'), 'icon' => 'LS', 'accent' => 'c-blocked'],
];
?>

<div class="listpage">
  <div class="page-hero">
    <div class="page-hero-text">
      <span class="page-hero-eyebrow">Staff Access</span>
      <h1>Manage Lab Staff</h1>
      <p>Maintain lab staff account details and assign access roles for admins and lab staff.</p>
    </div>
    <div class="page-hero-side">
      <button class="page-hero-btn page-hero-btn--solid" onclick="openAddStaffModal()">+ Add Staff</button>
    </div>
  </div>

  <div class="stat-grid stat-accent">
    <?php foreach ($staffStats as $stat): ?>
    <div class="stat-card <?= $stat['accent'] ?>">
      <div class="stat-card-top">
        <div class="stat-icon"><?= htmlspecialchars($stat['icon']) ?></div>
      </div>
      <div class="stat-num"><?= $stat['count'] ?></div>
      <div class="stat-label"><?= htmlspecialchars($stat['label']) ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="filter-bar" style="margin-bottom:16px;">
    <div class="search-input-wrap">
      <input type="text" class="form-control" id="staffSearch" placeholder="Search staff..." oninput="filterLabStaff()">
    </div>
    <select class="form-control" id="staffRoleFilter" onchange="filterLabStaff()">
      <option value="">All Roles</option>
      <option value="admin">Admin</option>
      <option value="lab_staff">Lab Staff</option>
    </select>
  </div>

  <div class="card">
    <div class="card-header">
      <span class="card-title">Lab Staff Directory</span>
      <span style="font-size:.75rem;color:var(--text-light);" id="staffCount"><?= count($LAB_STAFF) ?> staff</span>
    </div>
    <div class="card-body-flush">
      <div class="data-table-wrap">
        <table class="data-table" id="staffTable">
          <thead>
            <tr>
              <th>Staff ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>Role</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($LAB_STAFF as $staff): ?>
            <tr data-staff-id="<?= htmlspecialchars($staff['staff_id']) ?>" data-role="<?= htmlspecialchars($staff['role']) ?>">
              <td><span class="ref-code"><?= htmlspecialchars($staff['staff_id']) ?></span></td>
              <td style="font-weight:700;font-size:.84rem;"><?= htmlspecialchars($staff['name']) ?></td>
              <td style="font-size:.78rem;color:var(--text-mid);"><?= htmlspecialchars($staff['email']) ?></td>
              <td><span class="staff-role-tag staff-role-<?= htmlspecialchars($staff['role']) ?>"><?= htmlspecialchars(labStaffRoleLabel($staff['role'])) ?></span></td>
              <td>
                <div class="action-cluster">
                  <button class="btn btn-outline btn-xs" onclick="uiEditStaff('<?= htmlspecialchars($staff['staff_id']) ?>')" title="Edit">Edit</button>
                  <button class="btn btn-danger btn-xs" onclick="uiDeleteStaff('<?= htmlspecialchars($staff['staff_id']) ?>')" title="Delete">Delete</button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="staff-modal-overlay" id="addStaffModal" onclick="if(event.target===this)closeAddStaffModal()">
  <div class="staff-modal">
    <div class="staff-modal-head">
      <span class="card-title" style="color:#fff;">+ Add Lab Staff</span>
      <button class="staff-modal-x" onclick="closeAddStaffModal()">x</button>
    </div>
    <div style="padding:22px;">
      <div class="notice notice-info">This form is UI-only. Data will not be saved until a database is connected.</div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Name</label>
          <input type="text" class="form-control" placeholder="Full name">
        </div>
        <div class="form-group">
          <label class="form-label">Staff ID</label>
          <input type="text" class="form-control" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="e.g. 100004" oninput="this.value=this.value.replace(/\D/g,'').slice(0,6)">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" placeholder="name@unikl.edu.my">
        </div>
        <div class="form-group">
          <label class="form-label">Role</label>
          <select class="form-control">
            <option value="admin">Admin</option>
            <option value="lab_staff">Lab Staff</option>
          </select>
        </div>
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px;">
        <button class="btn btn-outline" onclick="closeAddStaffModal()">Cancel</button>
        <button class="btn btn-primary" onclick="showToast('Staff saved (UI demo - no DB).','success');closeAddStaffModal()">Save Staff</button>
      </div>
    </div>
  </div>
</div>

<style>
  .staff-role-tag { display:inline-block; padding:3px 9px; border-radius:10px; font-size:.68rem; font-weight:700; white-space:nowrap; }
  .staff-role-admin { background:var(--success-bg); color:var(--success); }
  .staff-role-lab_staff { background:var(--teal-light); color:var(--teal-dark); }
  .staff-modal-overlay { display:none; position:fixed; inset:0; background:rgba(16,28,38,.55); -webkit-backdrop-filter:blur(3px); backdrop-filter:blur(3px); z-index:500; align-items:center; justify-content:center; padding:20px; }
  .staff-modal { background:#fff; border-radius:var(--r-xl); width:100%; max-width:560px; box-shadow:0 24px 60px rgba(16,36,54,.34); overflow:hidden; animation:staffPop .2s cubic-bezier(.2,1.05,.4,1); }
  @keyframes staffPop { from { transform:translateY(12px) scale(.97); opacity:0; } to { transform:none; opacity:1; } }
  .staff-modal-head { display:flex; align-items:center; justify-content:space-between; padding:15px 20px; background:linear-gradient(120deg,#202734,#2a7782); }
  .staff-modal-x { background:rgba(255,255,255,.18); border:none; width:28px; height:28px; border-radius:50%; color:#fff; font-size:.85rem; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background .14s; }
  .staff-modal-x:hover { background:rgba(255,255,255,.34); }
</style>

<script>
function openAddStaffModal()  { document.getElementById('addStaffModal').style.display='flex'; }
function closeAddStaffModal() { document.getElementById('addStaffModal').style.display='none'; }

function filterLabStaff() {
  const q = document.getElementById('staffSearch').value.toLowerCase();
  const role = document.getElementById('staffRoleFilter').value;
  const rows = document.querySelectorAll('#staffTable tbody tr');
  let visible = 0;
  rows.forEach(row => {
    const ok = (!q || row.textContent.toLowerCase().includes(q)) && (!role || row.dataset.role === role);
    row.style.display = ok ? '' : 'none';
    if (ok) visible++;
  });
  document.getElementById('staffCount').textContent = visible + ' staff';
}

function uiEditStaff(id) {
  showToast('Edit staff #' + id + ' - DB not connected yet.', 'info');
}

function uiDeleteStaff(id) {
  confirmAction('Delete staff #' + id + '? (UI only - no DB)', () => showToast('Staff #' + id + ' removed (UI only).', 'danger'));
}
</script>
