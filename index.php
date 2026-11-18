<?php
require_once __DIR__ . '/includes/session.php';
$pageTitle = "Home";
require_once __DIR__ . '/includes/header.php';
?>

<section class="hero-section">
  <div class="container text-center position-relative">
    <span class="hero-badge mb-3 d-inline-block"><i class="fa-solid fa-shield-halved"></i> Official Kit Studio</span>
    <h1 class="display-5">Design Your Own Jersey, Your Way</h1>
    <p class="lead mt-3">Name &amp; number printed on the back, your logo on the front &mdash; customize a fully official-looking kit, or grab a Ready-Made jersey from our shop.</p>
    <?php if (isLoggedIn()): ?>
      <a href="customize.php" class="btn btn-accent btn-lg mt-3 btn-pill me-2"><i class="fa-solid fa-shirt"></i> Start Customizing</a>
      <a href="shop.php" class="btn btn-outline-light btn-lg mt-3 btn-pill"><i class="fa-solid fa-bag-shopping"></i> Ready-Made Shop</a>
    <?php else: ?>
      <a href="register.php" class="btn btn-accent btn-lg mt-3 btn-pill"><i class="fa-solid fa-user-plus"></i> Get Started</a>
    <?php endif; ?>
  </div>
</section>

<section class="container my-5">
  <div class="row text-center g-4">
    <div class="col-md-3">
      <div class="card card-feature p-4 h-100">
        <div class="icon-circle"><i class="fa-solid fa-palette"></i></div>
        <h5>Interactive Customization</h5>
        <p class="text-muted small">Choose colors, size, name and number in real time.</p>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card card-feature p-4 h-100">
        <div class="icon-circle"><i class="fa-solid fa-eye"></i></div>
        <h5>Live Preview</h5>
        <p class="text-muted small">See your jersey design update instantly as you customize.</p>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card card-feature p-4 h-100">
        <div class="icon-circle"><i class="fa-solid fa-image"></i></div>
        <h5>Logo Upload</h5>
        <p class="text-muted small">Upload your team or club logo to place on the jersey.</p>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card card-feature p-4 h-100">
        <div class="icon-circle"><i class="fa-solid fa-truck-fast"></i></div>
        <h5>Order Tracking</h5>
        <p class="text-muted small">Track your order status from pending to delivered.</p>
      </div>
    </div>
  </div>

  <div class="row text-center g-4 mt-2">
    <div class="col-md-6">
      <div class="card card-feature p-4 h-100">
        <div class="icon-circle"><i class="fa-solid fa-bag-shopping"></i></div>
        <h5>Ready-Made Shop</h5>
        <p class="text-muted small">Don't want to design from scratch? Pick a ready-designed jersey and just add your name &amp; number.</p>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card card-feature p-4 h-100">
        <div class="icon-circle"><i class="fa-solid fa-shield-halved"></i></div>
        <h5>Official Front &amp; Back Layout</h5>
        <p class="text-muted small">Logo on the front, name &amp; number on the back &mdash; just like a real club jersey.</p>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
