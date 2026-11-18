<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/classes/Order.php';
requireLogin();

$orderModel = new Order();
$orders = $orderModel->getOrdersByUser($_SESSION['user_id']);

$pageTitle = "My Orders";
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-banner">
  <div class="container">
    <h2><i class="fa-solid fa-list-check"></i> My Orders</h2>
    <p>All the jerseys you've customized or purchased, in one place.</p>
  </div>
</div>

<div class="container my-4">

  <?php if (empty($orders)): ?>
    <div class="alert alert-info">You haven't placed any orders yet. <a href="customize.php">Start customizing a jersey</a>.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover bg-white shadow-sm">
        <thead>
          <tr>
            <th></th>
            <th>Order #</th>
            <th>Jersey</th>
            <th>Type</th>
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
              <td>
                <?php if (!empty($o['jersey_image'])): ?>
                  <img src="<?= e($o['jersey_image']) ?>" class="thumb-sm">
                <?php else: ?>
                  <span class="thumb-sm d-inline-flex align-items-center justify-content-center bg-light text-muted"><i class="fa-solid fa-shirt"></i></span>
                <?php endif; ?>
              </td>
              <td><?= e($o['order_number']) ?></td>
              <td><?= e($o['jersey_name']) ?></td>
              <td><span class="badge category-badge-<?= e(str_replace(' ', '-', $o['purchase_type'])) ?>"><?= e($o['purchase_type']) ?></span></td>
              <td><?= e($o['player_name']) ?> / #<?= e($o['player_number']) ?></td>
              <td><?= e($o['size']) ?></td>
              <td><?= (int)$o['quantity'] ?></td>
              <td><?= number_format($o['total_price'], 2) ?></td>
              <td><span class="badge status-badge-<?= e($o['order_status']) ?>"><?= e($o['order_status']) ?></span></td>
              <td><?= date('d M Y', strtotime($o['order_date'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
