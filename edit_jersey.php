<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../classes/Jersey.php';
requireAdmin();

$jerseyModel = new Jersey();
$id = (int)($_GET['id'] ?? $_POST['jersey_id'] ?? 0);
$jersey = $jerseyModel->getJerseyById($id);

if (!$jersey) {
    header("Location: manage_jerseys.php");
    exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['jersey_name'] ?? '');
    $category    = $_POST['category'] ?? 'Custom Template';
    $price       = (float)($_POST['base_price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $baseColor   = $_POST['base_color'] ?? '#e11d2e';
    $isActive    = isset($_POST['is_active']) ? 1 : 0;

    if ($name === '') $errors[] = "Jersey name is required.";
    if ($price <= 0) $errors[] = "Base price must be greater than 0.";

    $imagePath = null;
    if (!empty($_FILES['jersey_image']['name'])) {
        $imagePath = $jerseyModel->uploadJerseyImage($_FILES['jersey_image']);
        if ($imagePath === null) {
            $errors[] = "Image upload failed. Only JPG/PNG/WEBP under 3MB are allowed.";
        }
    }

    if (empty($errors)) {
        $jerseyModel->updateJersey($id, [
            'jersey_name' => $name,
            'category'    => $category,
            'base_price'  => $price,
            'description' => $description,
            'base_color'  => $baseColor,
            'is_active'   => $isActive,
            'image_path'  => $imagePath,
        ]);
        $jersey = $jerseyModel->getJerseyById($id);
        $success = "Jersey updated successfully.";
    }
}

$pageTitle = "Edit Jersey";
$assetPrefix = "../";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
  <div class="row">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <div class="col-md-10 py-4">
      <h2 class="section-title mb-4"><i class="fa-solid fa-pen"></i> Edit Jersey</h2>

      <?php foreach ($errors as $err): ?>
        <div class="alert alert-danger py-2"><?= e($err) ?></div>
      <?php endforeach; ?>
      <?php if ($success): ?>
        <div class="alert alert-success py-2"><?= e($success) ?></div>
      <?php endif; ?>

      <div class="row g-4">
        <div class="col-lg-5">
          <div class="jersey-shop-card">
            <div class="shop-img-wrap" style="height:260px;">
              <?php if (!empty($jersey['image_path'])): ?>
                <img src="../<?= e($jersey['image_path']) ?>" alt="">
              <?php else: ?>
                <div class="no-photo"><i class="fa-solid fa-shirt"></i></div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="col-lg-7">
          <form method="POST" enctype="multipart/form-data" class="card p-4 shadow-sm">
            <input type="hidden" name="jersey_id" value="<?= $jersey['jersey_id'] ?>">
            <div class="mb-3">
              <label class="form-label">Jersey Name</label>
              <input type="text" name="jersey_name" class="form-control" value="<?= e($jersey['jersey_name']) ?>" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Category</label>
              <select name="category" class="form-select">
                <option value="Custom Template" <?= $jersey['category'] === 'Custom Template' ? 'selected' : '' ?>>Custom Template</option>
                <option value="Ready-Made" <?= $jersey['category'] === 'Ready-Made' ? 'selected' : '' ?>>Ready-Made</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Base Price (Rs.)</label>
              <input type="number" step="0.01" min="0" name="base_price" class="form-control" value="<?= e($jersey['base_price']) ?>" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Default Color</label>
              <input type="color" name="base_color" class="form-control form-control-color w-100" value="<?= e($jersey['base_color']) ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Description</label>
              <textarea name="description" class="form-control" rows="3"><?= e($jersey['description'] ?? '') ?></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Replace Product Photo <small class="text-muted">(leave empty to keep current)</small></label>
              <input type="file" name="jersey_image" class="form-control" accept="image/png, image/jpeg, image/webp">
            </div>
            <div class="form-check mb-3">
              <input type="checkbox" class="form-check-input" name="is_active" id="isActive" <?= $jersey['is_active'] ? 'checked' : '' ?>>
              <label class="form-check-label" for="isActive">Visible to customers</label>
            </div>
            <button type="submit" class="btn btn-accent w-100 btn-pill"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
            <a href="manage_jerseys.php" class="btn btn-outline-navy w-100 mt-2 btn-pill"><i class="fa-solid fa-arrow-left"></i> Back to List</a>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
