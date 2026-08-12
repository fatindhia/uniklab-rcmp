<?php
/**
 * pages/system-report.php
 */
require_once __DIR__ . '/../data/bookings.php';
require_once __DIR__ . '/../data/labs.php';
require_once __DIR__ . '/../data/reports.php';

$total      = count($BOOKINGS);
$approved   = bookingCount($BOOKINGS, 'approved');
$pending    = bookingCount($BOOKINGS, 'pending');
$rejected   = bookingCount($BOOKINGS, 'rejected');
$approvalRate = $total > 0 ? round(($approved / $total) * 100) : 0;

$maxBookings = max(array_column($MONTHLY_STATS, 'bookings')) ?: 1;
$maxUsage    = max(array_column($LAB_USAGE, 'sessions')) ?: 1;

$kpis = [
  ['label' => 'Total Bookings', 'value' => $total,                       'icon' => '📋', 'accent' => 'c-total'],
  ['label' => 'Approved',       'value' => $approved,                    'icon' => '✅', 'accent' => 'c-approved'],
  ['label' => 'Pending',        'value' => $pending,                     'icon' => '⏳', 'accent' => 'c-pending'],
  ['label' => 'Rejected',       'value' => $rejected,                    'icon' => '❌', 'accent' => 'c-rejected'],
  ['label' => 'Approval Rate',  'value' => $approvalRate . '%',          'icon' => '📈', 'accent' => 'c-blocked'],
  ['label' => 'Active Labs',    'value' => labCount($LABS, null, 'active'),'icon' => '🏛️', 'accent' => 'c-inactive'],
];
?>

<div class="listpage">
  <!-- ── Hero ── -->
  <div class="page-hero">
    <div class="page-hero-text">
      <span class="page-hero-eyebrow">📊 Analytics</span>
      <h1>System Report</h1>
      <p>Overview of booking volume, approval rates, and lab utilisation across the platform.</p>
    </div>
    <div class="page-hero-side">
      <button class="page-hero-btn" onclick="window.print()">🖨 Print Report</button>
    </div>
  </div>

  <!-- ── KPI Summary ── -->
  <div class="stat-grid stat-accent" style="grid-template-columns:repeat(auto-fill,minmax(168px,1fr));">
    <?php foreach ($kpis as $k): ?>
    <div class="stat-card <?= $k['accent'] ?>">
      <div class="stat-card-top"><div class="stat-icon"><?= $k['icon'] ?></div></div>
      <div class="stat-num"><?= $k['value'] ?></div>
      <div class="stat-label"><?= htmlspecialchars($k['label']) ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">

    <!-- ── Monthly Bookings Chart ── -->
    <div class="card" style="margin:0;">
      <div class="card-header">
        <span class="card-title">📊 Monthly Bookings (2025)</span>
      </div>
      <div class="card-body">
        <div style="display:flex;align-items:flex-end;gap:6px;height:130px;margin-bottom:6px;">
          <?php foreach ($MONTHLY_STATS as $m):
            $bPct = round(($m['bookings'] / $maxBookings) * 100);
            $aPct = round(($m['approved'] / $maxBookings) * 100);
          ?>
          <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:3px;height:100%;justify-content:flex-end;">
            <div style="font-size:.6rem;color:var(--navy);font-weight:700;"><?= $m['bookings'] ?></div>
            <div style="width:100%;position:relative;display:flex;flex-direction:column;justify-content:flex-end;height:<?= $bPct ?>%;">
              <div style="width:100%;height:100%;background:var(--teal-light);border-radius:5px 5px 0 0;position:absolute;bottom:0;"></div>
              <div style="width:100%;height:<?= $aPct ?>%;background:linear-gradient(180deg,#5cb0ba,#2a7782);border-radius:5px 5px 0 0;position:absolute;bottom:0;min-height:4px;"></div>
            </div>
            <div style="font-size:.62rem;color:var(--text-light);font-weight:600;"><?= $m['month'] ?></div>
          </div>
          <?php endforeach; ?>
        </div>
        <div style="display:flex;gap:14px;margin-top:10px;font-size:.72rem;">
          <span style="display:flex;align-items:center;gap:5px;"><span style="width:10px;height:10px;background:var(--teal-light);border-radius:3px;"></span> Total</span>
          <span style="display:flex;align-items:center;gap:5px;"><span style="width:10px;height:10px;background:#2a7782;border-radius:3px;"></span> Approved</span>
        </div>
      </div>
    </div>

    <!-- ── Booking Breakdown ── -->
    <div class="card" style="margin:0;">
      <div class="card-header">
        <span class="card-title">🎯 Booking Breakdown</span>
      </div>
      <div class="card-body">
        <div style="display:flex;flex-direction:column;gap:12px;">
          <?php
          $segments = [
            ['label'=>'Approved', 'count'=>$approved, 'color'=>'var(--success)'],
            ['label'=>'Pending',  'count'=>$pending,  'color'=>'#a07c1f'],
            ['label'=>'Rejected', 'count'=>$rejected, 'color'=>'var(--danger)'],
          ];
          foreach ($segments as $seg):
            $pct = $total > 0 ? round(($seg['count'] / $total) * 100) : 0;
          ?>
          <div>
            <div style="display:flex;justify-content:space-between;margin-bottom:5px;font-size:.8rem;">
              <span style="font-weight:600;color:var(--text);"><?= $seg['label'] ?></span>
              <span style="color:var(--text-mid);"><?= $seg['count'] ?> <span style="color:var(--text-light);">(<?= $pct ?>%)</span></span>
            </div>
            <div style="height:8px;background:var(--border-light);border-radius:4px;overflow:hidden;">
              <div style="height:100%;width:<?= $pct ?>%;background:<?= $seg['color'] ?>;border-radius:4px;transition:width .6s;"></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--border-light);">
          <div style="font-size:.76rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-mid);margin-bottom:10px;">Bookings by Lab Type</div>
          <?php
          $types = [
            ['key'=>'research','label'=>'Research Labs','icon'=>'🔬'],
            ['key'=>'csl',     'label'=>'CSL Labs',     'icon'=>'🧪'],
            ['key'=>'pharma',  'label'=>'Pharma Labs',  'icon'=>'💊'],
          ];
          foreach ($types as $t):
            $cnt = bookingCount($BOOKINGS, null, $t['key']);
            $pct = $total > 0 ? round(($cnt / $total) * 100) : 0;
          ?>
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;font-size:.8rem;">
            <span style="font-size:.88rem;"><?= $t['icon'] ?></span>
            <span style="flex:1;color:var(--text);"><?= $t['label'] ?></span>
            <span style="font-weight:700;color:var(--navy);min-width:24px;text-align:right;"><?= $cnt ?></span>
            <span style="color:var(--text-light);font-size:.74rem;min-width:36px;text-align:right;"><?= $pct ?>%</span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- ── Most-Used Labs ── -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">🏆 Most Used Labs</span>
      <span style="font-size:.74rem;color:var(--text-light);">By session count (2025 YTD)</span>
    </div>
    <div class="card-body">
      <div style="display:flex;flex-direction:column;gap:12px;">
        <?php foreach ($LAB_USAGE as $i => $usage):
          $pct = round(($usage['sessions'] / $maxUsage) * 100);
          $medal = ['🥇','🥈','🥉'][$i] ?? '';
        ?>
        <div>
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px;">
            <span style="font-size:.82rem;font-weight:600;color:var(--text);"><?= $medal ?> <?= htmlspecialchars($usage['lab']) ?></span>
            <div style="display:flex;align-items:center;gap:10px;">
              <span class="lab-type-tag lab-type-<?= $usage['type'] ?>"><?= strtoupper($usage['type']) ?></span>
              <span style="font-size:.78rem;font-weight:700;color:var(--navy);"><?= $usage['sessions'] ?> sessions</span>
              <span style="font-size:.74rem;color:var(--text-light);"><?= $usage['hours'] ?> hrs</span>
            </div>
          </div>
          <div style="height:7px;background:var(--border-light);border-radius:4px;overflow:hidden;">
            <div style="height:100%;width:<?= $pct ?>%;background:linear-gradient(90deg,var(--navy) 0%,var(--teal) 100%);border-radius:4px;"></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- ── Monthly Detail Table ── -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">📅 Monthly Statistics</span>
    </div>
    <div class="card-body-flush">
      <div class="data-table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Month</th>
              <th>Total Bookings</th>
              <th>Approved</th>
              <th>Rejected</th>
              <th>Pending</th>
              <th>Approval Rate</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($MONTHLY_STATS as $m):
              $rate = $m['bookings'] > 0 ? round(($m['approved'] / $m['bookings']) * 100) : 0;
            ?>
            <tr>
              <td style="font-weight:700;"><?= htmlspecialchars($m['month']) ?> 2025</td>
              <td><?= $m['bookings'] ?></td>
              <td><span class="badge badge-approved"><?= $m['approved'] ?></span></td>
              <td><span class="badge badge-rejected"><?= $m['rejected'] ?></span></td>
              <td><span class="badge badge-pending"><?= $m['pending'] ?></span></td>
              <td>
                <div style="display:flex;align-items:center;gap:8px;">
                  <div style="flex:1;height:5px;background:var(--border-light);border-radius:3px;overflow:hidden;">
                    <div style="height:100%;width:<?= $rate ?>%;background:var(--success);"></div>
                  </div>
                  <span style="font-size:.76rem;font-weight:700;color:var(--success);min-width:32px;"><?= $rate ?>%</span>
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
