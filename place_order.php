<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/classes/Jersey.php';
require_once __DIR__ . '/classes/Order.php';
require_once __DIR__ . '/classes/User.php';
requireLogin();

$customizationId = (int)($_GET['customization_id'] ?? $_POST['customization_id'] ?? 0);

$jerseyModel = new Jersey();
$customization = $jerseyModel->getCustomizationById($customizationId);

if (!$customization || $customization['user_id'] != $_SESSION['user_id']) {
    header("Location: customize.php");
    exit;
}

$userModel = new User();
$user = $userModel->getUserById($_SESSION['user_id']);

$errors = [];
$orderPlaced = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));
    $address  = trim($_POST['delivery_address'] ?? '');
    $phone    = trim($_POST['delivery_phone'] ?? '');

    if ($address === '') $errors[] = "Delivery address is required.";
    if ($phone === '') $errors[] = "Delivery phone number is required.";

    if (empty($errors)) {
        $orderModel = new Order();
        $result = $orderModel->placeOrder(
            $_SESSION['user_id'],
            $customizationId,
            $quantity,
            (float)$customization['base_price'],
            $address,
            $phone
        );
        $orderPlaced = $result;
    }
}

$pageTitle = "Place Order";
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-banner">
  <div class="container">
    <h2><i class="fa-solid fa-cart-shopping"></i> Confirm Your Order</h2>
    <p>Review your design and enter delivery details to complete your purchase.</p>
  </div>
</div>

<div class="container my-4">

  <?php if ($orderPlaced): ?>
    <div class="alert alert-success">
      <h5><i class="fa-solid fa-circle-check"></i> Order placed successfully!</h5>
      <p class="mb-1">Your order number is <strong><?= e($orderPlaced['order_number']) ?></strong>.</p>
      <p class="mb-0">Use this number on the <a href="track_order.php">Track Order</a> page to check its status.</p>
    </div>
    <a href="my_orders.php" class="btn btn-accent">View My Orders</a>
  <?php else: ?>

    <?php foreach ($errors as $err): ?>
      <div class="alert alert-danger py-2"><?= e($err) ?></div>
    <?php endforeach; ?>

    <div class="row g-4">
      <div class="col-md-5">
        <div class="card p-3 shadow-sm">
          <span class="badge category-badge-<?= e(str_replace(' ', '-', $customization['purchase_type'])) ?> mb-2" style="width:fit-content;">
            <?= e($customization['purchase_type']) ?> Order
          </span>
          <?php if (!empty($customization['jersey_image'])): ?>
            <img src="<?= e($customization['jersey_image']) ?>" alt="" class="rounded mb-3" style="width:100%;max-height:220px;object-fit:cover;">
          <?php endif; ?>
          <h5>Design Summary</h5>
          <ul class="list-group list-group-flush">
            <li class="list-group-item d-flex justify-content-between">
              <span>Jersey</span><strong><?= e($customization['jersey_name']) ?></strong>
            </li>
            <li class="list-group-item d-flex justify-content-between">
              <span>Size</span><strong><?= e($customization['size']) ?></strong>
            </li>
            <li class="list-group-item d-flex justify-content-between">
              <span>Name (back)</span><strong><?= e($customization['player_name']) ?></strong>
            </li>
            <li class="list-group-item d-flex justify-content-between">
              <span>Number (back)</span><strong><?= e($customization['player_number']) ?></strong>
            </li>
            <?php if ($customization['purchase_type'] === 'Custom'): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span>Jersey Color</span>
              <span style="display:inline-block;width:20px;height:20px;background:<?= e($customization['jersey_color']) ?>;border:1px solid #333;border-radius:4px;"></span>
            </li>
            <li class="list-group-item d-flex justify-content-between">
              <span>Pattern</span><strong><?= e($customization['design_pattern']) ?></strong>
            </li>
            <li class="list-group-item d-flex justify-content-between">
              <span>Logo (front)</span><strong><?= !empty($customization['logo_path']) ? 'Uploaded' : 'None' ?></strong>
            </li>
            <?php endif; ?>
            <li class="list-group-item d-flex justify-content-between">
              <span>Unit Price</span><strong>Rs. <?= number_format($customization['base_price'], 2) ?></strong>
            </li>
          </ul>
        </div>
      </div>

      <div class="col-md-7">
        <form method="POST" class="card p-4 shadow-sm">
          <input type="hidden" name="customization_id" value="<?= $customizationId ?>">
          <div class="mb-3">
            <label class="form-label">Quantity</label>
            <input type="number" class="form-control" name="quantity" min="1" value="1" id="qtyInput">
          </div>
          <div class="mb-3">
            <label class="form-label">Delivery Address</label>
            <textarea class="form-control" name="delivery_address" rows="2" required><?= e($user['address'] ?? '') ?></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Delivery Phone</label>
            <input type="text" class="form-control" name="delivery_phone" value="<?= e($user['phone'] ?? '') ?>" required>
          </div>
          <div class="mb-3">
            <strong>Total: Rs. <span id="totalPrice"><?= number_format($customization['base_price'], 2) ?></span></strong>
          </div>
          <button type="submit" class="btn btn-accent w-100"><i class="fa-solid fa-check"></i> Confirm Order</button>
        </form>
      </div>
    </div>

    <script>
      const unitPrice = <?= (float)$customization['base_price'] ?>;
      const qtyInput = document.getElementById('qtyInput');
      const totalPrice = document.getElementById('totalPrice');
      qtyInput.addEventListener('input', () => {
        const qty = Math.max(1, parseInt(qtyInput.value || 1));
        totalPrice.textContent = (unitPrice * qty).toFixed(2);
      });
    </script>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
