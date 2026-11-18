<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/classes/User.php';

if (isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $phone    = trim($_POST['phone'] ?? '');
    $address  = trim($_POST['address'] ?? '');

    if ($username === '' || $email === '' || $password === '') {
        $errors[] = "Please fill in all required fields.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }
    if ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        $userModel = new User();
        $result = $userModel->register($username, $email, $password, $phone, $address);
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $errors[] = $result['message'];
        }
    }
}

$pageTitle = "Register";
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-page"><div class="container">
  <div class="card auth-card p-4">
    <h3 class="text-center mb-3"><i class="fa-solid fa-user-plus"></i> Create Account</h3>

    <?php foreach ($errors as $err): ?>
      <div class="alert alert-danger py-2"><?= e($err) ?></div>
    <?php endforeach; ?>

    <?php if ($success): ?>
      <div class="alert alert-success py-2"><?= e($success) ?> <a href="login.php">Login here</a></div>
    <?php else: ?>
    <form method="POST" novalidate>
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" class="form-control" name="username" required value="<?= e($_POST['username'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Email Address</label>
        <input type="email" class="form-control" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Phone</label>
        <input type="text" class="form-control" name="phone" value="<?= e($_POST['phone'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Address</label>
        <textarea class="form-control" name="address" rows="2"><?= e($_POST['address'] ?? '') ?></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" class="form-control" name="password" required minlength="6">
      </div>
      <div class="mb-3">
        <label class="form-label">Confirm Password</label>
        <input type="password" class="form-control" name="confirm_password" required minlength="6">
      </div>
      <button type="submit" class="btn btn-accent w-100">Register</button>
    </form>
    <p class="text-center mt-3 mb-0">Already have an account? <a href="login.php">Login</a></p>
    <?php endif; ?>
  </div>
</div>

</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
