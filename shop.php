<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/classes/Jersey.php';
requireLogin();

$jerseyModel = new Jersey();
$readymade = $jerseyModel->getJerseysByCategory('Ready-Made');

$pageTitle = "Ready-Made Jerseys";
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-banner">
  <div class="container">
    <span class="hero-badge mb-2 d-inline-block"><i class="fa-solid fa-bag-shopping"></i> Shop Now</span>
    <h2><i class="fa-solid fa-shirt"></i> Ready-Made Jerseys</h2>
    <p>Pick a ready-designed jersey and simply add your name &amp; number &mdash; ready to ship.</p>
  </div>
</div>

<div class="container my-4">
  <?php if (empty($readymade)): ?>
    <div class="alert alert-info">
      No ready-made jerseys are available right now. Want a one-of-a-kind design instead?
      <a href="customize.php">Build your own jersey</a>.
    </div>
  <?php else: ?>
    <div class="row g-4">
      <?php foreach ($readymade as $j): ?>
        <div class="col-md-4 col-sm-6">
          <div class="jersey-shop-card">
            <div class="shop-img-wrap">
              <span class="price-tag">Rs. <?= number_format($j['base_price'], 2) ?></span>
              <?php if (!empty($j['image_path'])): ?>
                <img src="<?= e($j['image_path']) ?>" alt="<?= e($j['jersey_name']) ?>">
              <?php else: ?>
                <div class="no-photo"><i class="fa-solid fa-shirt"></i></div>
              <?php endif; ?>
            </div>
            <div class="card-body p-3">
              <h5><?= e($j['jersey_name']) ?></h5>
              <p class="text-muted small mb-3"><?= e($j['description'] ?? '') ?></p>
              <a href="buy_readymade.php?jersey_id=<?= $j['jersey_id'] ?>" class="btn btn-accent w-100 btn-pill">
                <i class="fa-solid fa-cart-plus"></i> Buy This Jersey
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
