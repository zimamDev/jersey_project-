<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../classes/Admin.php';
requireAdmin();

$adminModel = new Admin();
$stats = $adminModel->getDashboardStats();

$pageTitle = "Admin Dashboard";
$assetPrefix = "../";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
  <div class="row">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <div class="col-md-10 py-4">
      <h2 class="section-title mb-4"><i class="fa-solid fa-gauge"></i> Dashboard Overview</h2>
      <div class="row g-4">
        <div class="col-md-3">
          <div class="stat-card bg-navy">
            <i class="fa-solid fa-boxes-stacked stat-bg-icon"></i>
            <h3><?= $stats['total_orders'] ?></h3>
            <p>Total Orders</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card bg-gold">
            <i class="fa-solid fa-hourglass-half stat-bg-icon"></i>
            <h3><?= $stats['pending'] ?></h3>
            <p>Pending Orders</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card bg-green">
            <i class="fa-solid fa-circle-check stat-bg-icon"></i>
            <h3><?= $stats['completed'] ?></h3>
            <p>Completed Orders</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card bg-blue">
            <i class="fa-solid fa-users stat-bg-icon"></i>
            <h3><?= $stats['total_users'] ?></h3>
            <p>Registered Customers</p>
          </div>
        </div>
      </div>

      <div class="row g-4 mt-1">
        <div class="col-md-4">
          <div class="stat-card bg-red">
            <i class="fa-solid fa-shirt stat-bg-icon"></i>
            <h3><?= $stats['total_jerseys'] ?></h3>
            <p>Jerseys in Catalog</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="stat-card bg-navy">
            <i class="fa-solid fa-bag-shopping stat-bg-icon"></i>
            <h3><?= $stats['readymade'] ?></h3>
            <p>Ready-Made Products</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card card-feature p-4">
            <h6 class="text-muted mb-1">Total Revenue</h6>
            <h2 class="text-success mb-0">Rs. <?= number_format($stats['revenue'], 2) ?></h2>
            <a href="reports.php" class="small">View full sales report <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </div>
      </div>

      <div class="row mt-4 g-3">
        <div class="col-md-4">
          <a href="add_readymade_jersey.php" class="btn btn-accent w-100 py-3 btn-pill"><i class="fa-solid fa-cloud-arrow-up"></i> Add Ready-Made Jersey</a>
        </div>
        <div class="col-md-4">
          <a href="manage_jerseys.php" class="btn btn-outline-navy w-100 py-3 btn-pill"><i class="fa-solid fa-shirt"></i> Manage Jerseys</a>
        </div>
        <div class="col-md-4">
          <a href="manage_users.php" class="btn btn-outline-navy w-100 py-3 btn-pill"><i class="fa-solid fa-users"></i> View All Customers</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
