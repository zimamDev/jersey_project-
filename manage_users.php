<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../classes/Admin.php';
requireAdmin();

$adminModel = new Admin();
$users = $adminModel->getAllUsersWithStats();

$pageTitle = "Manage Users";
$assetPrefix = "../";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
  <div class="row">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <div class="col-md-10 py-4">
      <h2 class="section-title mb-4"><i class="fa-solid fa-users"></i> Manage Users</h2>
      <p class="text-muted mb-4">All registered customers, their jersey designs and order activity.</p>

      <div class="table-responsive">
        <table class="table table-hover bg-white shadow-sm align-middle">
          <thead>
            <tr>
              <th>Customer</th>
              <th>Contact</th>
              <th>Jerseys Created</th>
              <th>Orders</th>
              <th>Total Spent (Rs.)</th>
              <th>Joined</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $u): ?>
              <tr>
                <td>
                  <i class="fa-solid fa-circle-user fa-lg text-secondary me-1"></i>
                  <strong><?= e($u['username']) ?></strong>
                </td>
                <td>
                  <?= e($u['email']) ?><br>
                  <small class="text-muted"><?= e($u['phone'] ?? '—') ?></small>
                </td>
                <td><span class="badge bg-info-subtle text-dark"><?= (int)$u['jerseys_count'] ?></span></td>
                <td><span class="badge bg-primary-subtle text-dark"><?= (int)$u['orders_count'] ?></span></td>
                <td><strong><?= number_format($u['total_spent'], 2) ?></strong></td>
                <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                <td><a href="user_detail.php?id=<?= $u['user_id'] ?>" class="btn btn-sm btn-outline-navy"><i class="fa-solid fa-eye"></i> View</a></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($users)): ?>
              <tr><td colspan="7" class="text-center text-muted py-4">No registered customers yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
