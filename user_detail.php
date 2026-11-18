<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../classes/Admin.php';
requireAdmin();

$adminModel = new Admin();
$userId = (int)($_GET['id'] ?? 0);
$user = $adminModel->getUserById($userId);

if (!$user) {
    header("Location: manage_users.php");
    exit;
}

$jerseys = $adminModel->getUserJerseys($userId);
$orders  = $adminModel->getUserOrders($userId);

$pageTitle = "Customer: " . $user['username'];
$assetPrefix = "../";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
  <div class="row">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <div class="col-md-10 py-4">
      <a href="manage_users.php" class="btn btn-sm btn-outline-navy mb-3"><i class="fa-solid fa-arrow-left"></i> Back to Users</a>

      <div class="card p-4 shadow-sm mb-4">
        <div class="d-flex align-items-center gap-3">
          <i class="fa-solid fa-circle-user fa-3x text-secondary"></i>
          <div>
            <h4 class="mb-0"><?= e($user['username']) ?></h4>
            <p class="text-muted mb-0"><?= e($user['email']) ?> &middot; <?= e($user['phone'] ?? 'No phone') ?></p>
            <p class="text-muted mb-0 small"><?= e($user['address'] ?? '') ?></p>
          </div>
        </div>
      </div>

      <h5 class="section-title mb-3"><i class="fa-solid fa-shirt"></i> Jerseys Created / Purchased (<?= count($jerseys) ?>)</h5>
      <div class="row g-3 mb-4">
        <?php foreach ($jerseys as $j): ?>
          <div class="col-md-4">
            <div class="jersey-shop-card">
              <div class="shop-img-wrap" style="height:150px;">
                <?php if (!empty($j['logo_path'])): ?>
                  <img src="../<?= e($j['logo_path']) ?>" alt="">
                <?php elseif (!empty($j['jersey_image'])): ?>
                  <img src="../<?= e($j['jersey_image']) ?>" alt="">
                <?php else: ?>
                  <div class="no-photo" style="background:<?= e($j['jersey_color']) ?>;"><i class="fa-solid fa-shirt"></i></div>
                <?php endif; ?>
              </div>
              <div class="card-body p-3">
                <span class="badge category-badge-<?= e(str_replace(' ', '-', $j['purchase_type'])) ?> mb-1"><?= e($j['purchase_type']) ?></span>
                <h6 class="fw-bold mb-1"><?= e($j['jersey_name']) ?></h6>
                <p class="small text-muted mb-0">#<?= e($j['player_number']) ?> &middot; <?= e($j['player_name']) ?> &middot; Size <?= e($j['size']) ?></p>
                <?php if ($j['purchase_type'] === 'Custom'): ?>
                  <p class="small text-muted mb-0">Pattern: <?= e($j['design_pattern']) ?></p>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
        <?php if (empty($jerseys)): ?>
          <div class="col-12 text-muted">This customer hasn't designed or bought a jersey yet.</div>
        <?php endif; ?>
      </div>

      <h5 class="section-title mb-3"><i class="fa-solid fa-boxes-stacked"></i> Order History (<?= count($orders) ?>)</h5>
      <div class="table-responsive">
        <table class="table table-hover bg-white shadow-sm">
          <thead>
            <tr>
              <th>Order #</th>
              <th>Jersey</th>
              <th>Name / No.</th>
              <th>Size</th>
              <th>Qty</th>
              <th>Total (Rs.)</th>
              <th>Status</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $o): ?>
              <tr>
                <td><?= e($o['order_number']) ?></td>
                <td><?= e($o['jersey_name']) ?></td>
                <td><?= e($o['player_name']) ?> / #<?= e($o['player_number']) ?></td>
                <td><?= e($o['size']) ?></td>
                <td><?= (int)$o['quantity'] ?></td>
                <td><?= number_format($o['total_price'], 2) ?></td>
                <td><span class="badge status-badge-<?= e($o['order_status']) ?>"><?= e($o['order_status']) ?></span></td>
                <td><?= date('d M Y', strtotime($o['order_date'])) ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($orders)): ?>
              <tr><td colspan="8" class="text-center text-muted py-3">No orders placed yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
