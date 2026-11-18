<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/Admin.php';

if (isLoggedIn()) { header("Location: index.php"); exit; }
if (isAdminLoggedIn()) { header("Location: admin/dashboard.php"); exit; }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $userModel = new User();
    $result = $userModel->login($email, $password);

    if ($result['success']) {
        $_SESSION['user_id']  = $result['user']['user_id'];
        $_SESSION['username'] = $result['user']['username'];
        header("Location: index.php");
        exit;
    } else {
        $error = $result['message'];
    }
}

$pageTitle = "Login";
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-page"><div class="container">
  <div class="card auth-card p-4">
    <h3 class="text-center mb-3"><i class="fa-solid fa-right-to-bracket"></i> Login</h3>

    <?php if ($error): ?>
      <div class="alert alert-danger py-2"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Email Address</label>
        <input type="email" class="form-control" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" class="form-control" name="password" required>
      </div>
      <button type="submit" class="btn btn-accent w-100">Login</button>
    </form>
    <p class="text-center mt-3 mb-0">Don't have an account? <a href="register.php">Register</a></p>
    <p class="text-center mt-1"><a href="admin/login.php"><i class="fa-solid fa-user-shield"></i> Admin Login</a></p>
  </div>
</div>

</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
