<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../classes/Order.php';
requireAdmin();

$orderModel = new Order();
$summary        = $orderModel->getReportSummary();
$revenueByDay   = $orderModel->getRevenueByDay(14);
$topJerseys     = $orderModel->getTopJerseys(5);
$statusBreakdown = $orderModel->getStatusBreakdown();
$byPurchaseType = $orderModel->getRevenueByPurchaseType();

$dayLabels = array_map(fn($r) => date('d M', strtotime($r['day'])), $revenueByDay);
$dayRevenue = array_map(fn($r) => (float)$r['revenue'], $revenueByDay);

$statusLabels = array_map(fn($r) => $r['order_status'], $statusBreakdown);
$statusCounts = array_map(fn($r) => (int)$r['total'], $statusBreakdown);

$pageTitle = "Sales Reports";
$assetPrefix = "../";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
  <div class="row">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <div class="col-md-10 py-4">
      <h2 class="section-title mb-4"><i class="fa-solid fa-chart-line"></i> Sales Reports</h2>

      <div class="row g-4 mb-2">
        <div class="col-md-3">
          <div class="stat-card bg-navy">
            <i class="fa-solid fa-sack-dollar stat-bg-icon"></i>
            <h3>Rs. <?= number_format($summary['revenue'], 0) ?></h3>
            <p>Total Revenue</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card bg-gold">
            <i class="fa-solid fa-boxes-stacked stat-bg-icon"></i>
            <h3><?= $summary['total_orders'] ?></h3>
            <p>Total Orders</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card bg-green">
            <i class="fa-solid fa-shirt stat-bg-icon"></i>
            <h3><?= $summary['units_sold'] ?></h3>
            <p>Jerseys Sold (Units)</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card bg-blue">
            <i class="fa-solid fa-scale-balanced stat-bg-icon"></i>
            <h3>Rs. <?= number_format($summary['avg_order'], 0) ?></h3>
            <p>Average Order Value</p>
          </div>
        </div>
      </div>

      <div class="row g-4 mt-1">
        <div class="col-lg-8">
          <div class="report-chart-card">
            <h5 class="mb-3">Revenue &mdash; Last 14 Days</h5>
            <canvas id="revenueChart" height="110"></canvas>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="report-chart-card h-100">
            <h5 class="mb-3">Orders by Status</h5>
            <canvas id="statusChart" height="220"></canvas>
          </div>
        </div>
      </div>

      <div class="row g-4 mt-1">
        <div class="col-lg-5">
          <div class="report-chart-card h-100">
            <h5 class="mb-3">Custom vs Ready-Made Revenue</h5>
            <canvas id="typeChart" height="200"></canvas>
          </div>
        </div>
        <div class="col-lg-7">
          <div class="report-chart-card h-100">
            <h5 class="mb-3">Top Selling Jerseys</h5>
            <table class="table table-sm align-middle mb-0">
              <thead><tr><th></th><th>Jersey</th><th>Orders</th><th>Units</th><th>Revenue (Rs.)</th></tr></thead>
              <tbody>
                <?php foreach ($topJerseys as $t): ?>
                  <tr>
                    <td>
                      <?php if (!empty($t['image_path'])): ?>
                        <img src="../<?= e($t['image_path']) ?>" class="thumb-sm">
                      <?php else: ?>
                        <span class="thumb-sm d-inline-flex align-items-center justify-content-center bg-light text-muted"><i class="fa-solid fa-shirt"></i></span>
                      <?php endif; ?>
                    </td>
                    <td><?= e($t['jersey_name']) ?></td>
                    <td><?= (int)$t['orders_count'] ?></td>
                    <td><?= (int)$t['units_sold'] ?></td>
                    <td><strong><?= number_format($t['revenue'], 2) ?></strong></td>
                  </tr>
                <?php endforeach; ?>
                <?php if (empty($topJerseys)): ?>
                  <tr><td colspan="5" class="text-center text-muted py-3">No sales yet.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
const revCtx = document.getElementById('revenueChart');
new Chart(revCtx, {
  type: 'line',
  data: {
    labels: <?= json_encode($dayLabels) ?>,
    datasets: [{
      label: 'Revenue (Rs.)',
      data: <?= json_encode($dayRevenue) ?>,
      borderColor: '#ffb703',
      backgroundColor: 'rgba(255,183,3,0.18)',
      fill: true,
      tension: 0.35,
      pointBackgroundColor: '#0b1f3a'
    }]
  },
  options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});

const statusCtx = document.getElementById('statusChart');
new Chart(statusCtx, {
  type: 'doughnut',
  data: {
    labels: <?= json_encode($statusLabels) ?>,
    datasets: [{
      data: <?= json_encode($statusCounts) ?>,
      backgroundColor: ['#6c757d', '#0d6efd', '#198754', '#20c997', '#dc3545']
    }]
  },
  options: { plugins: { legend: { position: 'bottom' } } }
});

const typeCtx = document.getElementById('typeChart');
new Chart(typeCtx, {
  type: 'bar',
  data: {
    labels: <?= json_encode(array_map(fn($r) => $r['purchase_type'], $byPurchaseType)) ?>,
    datasets: [{
      label: 'Revenue (Rs.)',
      data: <?= json_encode(array_map(fn($r) => (float)$r['revenue'], $byPurchaseType)) ?>,
      backgroundColor: ['#2563eb', '#16a34a']
    }]
  },
  options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
