<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/classes/Jersey.php';
requireLogin();

$jerseyModel = new Jersey();
$jerseyId = (int)($_GET['jersey_id'] ?? $_POST['jersey_id'] ?? 0);
$jersey = $jerseyModel->getJerseyById($jerseyId);

if (!$jersey || $jersey['category'] !== 'Ready-Made') {
    header("Location: shop.php");
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $size         = $_POST['size'] ?? 'M';
    $playerName   = trim($_POST['player_name'] ?? '');
    $playerNumber = trim($_POST['player_number'] ?? '');

    if ($playerName === '') $errors[] = "Player name is required.";
    if ($playerNumber === '' || !ctype_digit($playerNumber)) $errors[] = "Player number must be numeric.";

    if (empty($errors)) {
        $customizationId = $jerseyModel->saveCustomization([
            'user_id'        => $_SESSION['user_id'],
            'jersey_id'      => $jersey['jersey_id'],
            'purchase_type'  => 'Ready-Made',
            'jersey_color'   => $jersey['base_color'],
            'sleeve_color'   => $jersey['base_color'],
            'design_pattern' => 'Plain',
            'pattern_color'  => $jersey['base_color'],
            'size'           => $size,
            'player_name'    => $playerName,
            'player_number'  => $playerNumber,
            'logo_path'      => null,
        ]);

        header("Location: place_order.php?customization_id=" . $customizationId);
        exit;
    }
}

$pageTitle = "Buy " . $jersey['jersey_name'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-4">
  <h2 class="section-title mb-4"><i class="fa-solid fa-bag-shopping"></i> Buy Ready-Made Jersey</h2>

  <?php foreach ($errors as $err): ?>
    <div class="alert alert-danger py-2"><?= e($err) ?></div>
  <?php endforeach; ?>

  <div class="row g-4">
    <div class="col-md-5">
      <div class="jersey-shop-card">
        <div class="shop-img-wrap" style="height:320px;">
          <?php if (!empty($jersey['image_path'])): ?>
            <img src="<?= e($jersey['image_path']) ?>" alt="<?= e($jersey['jersey_name']) ?>">
          <?php else: ?>
            <div class="no-photo"><i class="fa-solid fa-shirt"></i></div>
          <?php endif; ?>
        </div>
        <div class="card-body p-3">
          <h5><?= e($jersey['jersey_name']) ?></h5>
          <p class="text-muted small"><?= e($jersey['description'] ?? '') ?></p>
          <h4 class="text-success mb-0">Rs. <?= number_format($jersey['base_price'], 2) ?></h4>
        </div>
      </div>
    </div>

    <div class="col-md-7">
      <form method="POST" class="card p-4 shadow-sm">
        <input type="hidden" name="jersey_id" value="<?= $jersey['jersey_id'] ?>">
        <p class="text-muted">Just add your name &amp; number &mdash; this jersey design is ready as shown in the photo.</p>

        <div class="mb-3">
          <label class="form-label fw-semibold">Size</label>
          <select name="size" class="form-select">
            <option>XS</option><option>S</option><option selected>M</option>
            <option>L</option><option>XL</option><option>XXL</option>
          </select>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label fw-semibold">Player Name</label>
            <input type="text" class="form-control" name="player_name" maxlength="20" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-semibold">Player Number</label>
            <input type="number" class="form-control" name="player_number" min="0" max="99" required>
          </div>
        </div>

        <button type="submit" class="btn btn-accent w-100 mt-2 btn-pill">
          <i class="fa-solid fa-cart-plus"></i> Continue to Order
        </button>
        <a href="shop.php" class="btn btn-outline-navy w-100 mt-2 btn-pill">
          <i class="fa-solid fa-arrow-left"></i> Back to Shop
        </a>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
