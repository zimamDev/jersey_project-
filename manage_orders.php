<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../classes/Admin.php';
requireAdmin();

$adminModel = new Admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = (int)($_POST['order_id'] ?? 0);
    $status  = $_POST['order_status'] ?? '';
    if ($orderId && in_array($status, ['Pending','Processing','Completed','Delivered','Cancelled'], true)) {
        $adminModel->updateOrderStatus($orderId, $status);
    }
    header("Location: manage_orders.php");
    exit;
}

$orders = $adminModel->getAllOrders();

$pageTitle = "Manage Orders";
$assetPrefix = "../";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
  <div class="row">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <div class="col-md-10 py-4">
      <h2 class="section-title mb-4"><i class="fa-solid fa-boxes-stacked"></i> Manage Orders</h2>

      <div class="table-responsive">
        <table class="table table-hover bg-white shadow-sm">
          <thead>
            <tr>
              <th></th>
              <th>Order #</th>
              <th>Customer</th>
              <th>Jersey</th>
              <th>Type</th>
              <th>Name / No.</th>
              <th>Size</th>
              <th>Qty</th>
              <th>Total (Rs.)</th>
              <th>Status</th>
              <th>Update</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $o): ?>
              <tr>
                <td>
                  <?php if (!empty($o['jersey_image'])): ?>
                    <img src="../<?= e($o['jersey_image']) ?>" class="thumb-sm">
                  <?php else: ?>
                    <span class="thumb-sm d-inline-flex align-items-center justify-content-center bg-light text-muted"><i class="fa-solid fa-shirt"></i></span>
                  <?php endif; ?>
                </td>
                <td><?= e($o['order_number']) ?></td>
                <td><?= e($o['username']) ?><br><small class="text-muted"><?= e($o['email']) ?></small></td>
                <td><?= e($o['jersey_name']) ?></td>
                <td><span class="badge category-badge-<?= e(str_replace(' ', '-', $o['purchase_type'])) ?>"><?= e($o['purchase_type']) ?></span></td>
                <td><?= e($o['player_name']) ?> / #<?= e($o['player_number']) ?></td>
                <td><?= e($o['size']) ?></td>
                <td><?= (int)$o['quantity'] ?></td>
                <td><?= number_format($o['total_price'], 2) ?></td>
                <td><span class="badge status-badge-<?= e($o['order_status']) ?>"><?= e($o['order_status']) ?></span></td>
                <td>
                  <form method="POST" class="d-flex gap-1">
                    <input type="hidden" name="order_id" value="<?= $o['order_id'] ?>">
                    <select name="order_status" class="form-select form-select-sm">
                      <?php foreach (['Pending','Processing','Completed','Delivered','Cancelled'] as $s): ?>
                        <option value="<?= $s ?>" <?= $s === $o['order_status'] ? 'selected' : '' ?>><?= $s ?></option>
                      <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-sm btn-accent">Update</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
