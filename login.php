<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../classes/Admin.php';

if (isAdminLoggedIn()) { header("Location: dashboard.php"); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $adminModel = new Admin();
    $result = $adminModel->login($username, $password);

    if ($result['success']) {
        $_SESSION['admin_id'] = $result['admin']['admin_id'];
        $_SESSION['admin_username'] = $result['admin']['username'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = $result['message'];
    }
}

$pageTitle = "Admin Login";
$assetPrefix = "../";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-page"><div class="container">
  <div class="card auth-card p-4">
    <h3 class="text-center mb-3"><i class="fa-solid fa-user-shield"></i> Admin Login</h3>
    <?php if ($error): ?>
      <div class="alert alert-danger py-2"><?= e($error) ?></div>
    <?php endif; ?>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" class="form-control" name="username" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" class="form-control" name="password" required>
      </div>
      <button type="submit" class="btn btn-accent w-100">Login</button>
    </form>
    <p class="text-center mt-3 mb-0"><small>Default: admin / admin123</small></p>
  </div>
</div>

</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
