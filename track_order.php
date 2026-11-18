<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/classes/Order.php';
requireLogin();

$order = null;
$notFound = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderNumber = trim($_POST['order_number'] ?? '');
    $orderModel = new Order();
    $order = $orderModel->getOrderByNumber($orderNumber, $_SESSION['user_id']);
    if (!$order) $notFound = true;
}

$statuses = ['Pending', 'Processing', 'Completed', 'Delivered'];

$pageTitle = "Track Order";
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-banner">
  <div class="container">
    <h2><i class="fa-solid fa-truck-fast"></i> Track Your Order</h2>
    <p>Enter your order number to see its live status.</p>
  </div>
</div>

<div class="container my-4">
  <form method="POST" class="row g-2 mb-4">
    <div class="col-md-6">
      <input type="text" class="form-control" name="order_number" placeholder="Enter Order Number e.g. ORD64F1..." required value="<?= e($_POST['order_number'] ?? '') ?>">
    </div>
    <div class="col-md-3">
      <button type="submit" class="btn btn-accent w-100">Track</button>
    </div>
  </form>

  <?php if ($notFound): ?>
    <div class="alert alert-danger">No order found with that number for your account.</div>
  <?php endif; ?>

  <?php if ($order): ?>
    <div class="card p-4 shadow-sm">
      <div class="d-flex align-items-center gap-3 mb-2">
        <?php if (!empty($order['jersey_image'])): ?>
          <img src="<?= e($order['jersey_image']) ?>" class="rounded" style="width:64px;height:64px;object-fit:cover;">
        <?php endif; ?>
        <div>
          <h5 class="mb-0">Order <?= e($order['order_number']) ?></h5>
          <span class="badge category-badge-<?= e(str_replace(' ', '-', $order['purchase_type'])) ?>"><?= e($order['purchase_type']) ?></span>
        </div>
      </div>
      <p class="text-muted"><?= e($order['jersey_name']) ?> &mdash; <?= e($order['player_name']) ?> / #<?= e($order['player_number']) ?> (<?= e($order['size']) ?>)</p>

      <?php if ($order['order_status'] === 'Cancelled'): ?>
        <div class="alert alert-danger">This order has been cancelled.</div>
      <?php else: ?>
        <div class="d-flex justify-content-between mb-4 mt-3">
          <?php foreach ($statuses as $s):
            $currentIndex = array_search($order['order_status'], $statuses);
            $stepIndex = array_search($s, $statuses);
            $active = $stepIndex <= $currentIndex;
          ?>
            <div class="text-center flex-fill">
              <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center"
                   style="width:40px;height:40px;background:<?= $active ? '#198754' : '#dee2e6' ?>;color:#fff;">
                <i class="fa-solid <?= $active ? 'fa-check' : 'fa-clock' ?>"></i>
              </div>
              <small class="d-block mt-1"><?= e($s) ?></small>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <p class="mb-0"><strong>Delivery Address:</strong> <?= e($order['delivery_address']) ?></p>
      <p><strong>Order Date:</strong> <?= date('d M Y, h:i A', strtotime($order['order_date'])) ?></p>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
