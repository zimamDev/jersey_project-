<?php require_once __DIR__ . '/session.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? e($pageTitle) . ' - JerseyCraft' : 'JerseyCraft' ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link href="<?= isset($assetPrefix) ? $assetPrefix : '' ?>assets/css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark navbar-brand-custom sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?= isset($assetPrefix) ? $assetPrefix : '' ?>index.php">
      <i class="fa-solid fa-shirt me-2"></i>JerseyCraft
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
        <li class="nav-item"><a class="nav-link" href="<?= isset($assetPrefix) ? $assetPrefix : '' ?>index.php">Home</a></li>
        <?php if (isLoggedIn()): ?>
          <li class="nav-item"><a class="nav-link" href="<?= isset($assetPrefix) ? $assetPrefix : '' ?>customize.php">Customize Jersey</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= isset($assetPrefix) ? $assetPrefix : '' ?>shop.php">Ready-Made Shop</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= isset($assetPrefix) ? $assetPrefix : '' ?>my_orders.php">My Orders</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= isset($assetPrefix) ? $assetPrefix : '' ?>track_order.php">Track Order</a></li>
          <li class="nav-item"><span class="nav-link text-warning"><i class="fa-solid fa-user"></i> <?= e($_SESSION['username']) ?></span></li>
          <li class="nav-item"><a class="btn btn-outline-light btn-sm" href="<?= isset($assetPrefix) ? $assetPrefix : '' ?>logout.php">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?= isset($assetPrefix) ? $assetPrefix : '' ?>login.php">Login</a></li>
          <li class="nav-item"><a class="btn btn-warning btn-sm text-dark fw-semibold" href="<?= isset($assetPrefix) ? $assetPrefix : '' ?>register.php">Sign Up</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<main>
