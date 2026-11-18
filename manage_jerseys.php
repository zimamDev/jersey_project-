<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../classes/Jersey.php';
requireAdmin();

$jerseyModel = new Jersey();
$errors = [];
$success = '';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $jerseyModel->deleteJersey((int)$_POST['jersey_id']);
    header("Location: manage_jerseys.php");
    exit;
}

// Handle add new jersey
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name        = trim($_POST['jersey_name'] ?? '');
    $category    = $_POST['category'] ?? 'Custom Template';
    $price       = (float)($_POST['base_price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $baseColor   = $_POST['base_color'] ?? '#e11d2e';

    if ($name === '') $errors[] = "Jersey name is required.";
    if ($price <= 0) $errors[] = "Base price must be greater than 0.";
    if (!in_array($category, ['Custom Template', 'Ready-Made'], true)) $errors[] = "Invalid category.";

    $imagePath = null;
    if (!empty($_FILES['jersey_image']['name'])) {
        $imagePath = $jerseyModel->uploadJerseyImage($_FILES['jersey_image']);
        if ($imagePath === null) {
            $errors[] = "Image upload failed. Only JPG/PNG/WEBP under 3MB are allowed.";
        }
    } elseif ($category === 'Ready-Made') {
        $errors[] = "Please upload a product photo for a Ready-Made jersey.";
    }

    if (empty($errors)) {
        $jerseyModel->addJersey([
            'jersey_name' => $name,
            'category'    => $category,
            'base_price'  => $price,
            'description' => $description,
            'image_path'  => $imagePath,
            'base_color'  => $baseColor,
            'is_active'   => 1,
        ]);
        $success = "Jersey added successfully.";
    }
}

// Handle toggle active
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle') {
    $id = (int)$_POST['jersey_id'];
    $jersey = $jerseyModel->getJerseyById($id);
    if ($jersey) {
        $jerseyModel->updateJersey($id, [
            'jersey_name' => $jersey['jersey_name'],
            'category'    => $jersey['category'],
            'base_price'  => $jersey['base_price'],
            'description' => $jersey['description'],
            'base_color'  => $jersey['base_color'],
            'is_active'   => $jersey['is_active'] ? 0 : 1,
        ]);
    }
    header("Location: manage_jerseys.php");
    exit;
}

$allJerseys = $jerseyModel->getAllJerseys();

$pageTitle = "Manage Jerseys";
$assetPrefix = "../";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
  <div class="row">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <div class="col-md-10 py-4">
      <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="section-title mb-0"><i class="fa-solid fa-shirt"></i> Manage Jerseys</h2>
        <a href="add_readymade_jersey.php" class="btn btn-accent btn-pill">
          <i class="fa-solid fa-cloud-arrow-up"></i> Add Ready-Made Jersey
        </a>
      </div>

      <?php foreach ($errors as $err): ?>
        <div class="alert alert-danger py-2"><?= e($err) ?></div>
      <?php endforeach; ?>
      <?php if ($success): ?>
        <div class="alert alert-success py-2"><?= e($success) ?></div>
      <?php endif; ?>

      <div class="row g-4">
        <!-- Add New Jersey -->
        <div class="col-lg-4">
          <div class="card p-4 shadow-sm">
            <h5 class="mb-3"><i class="fa-solid fa-circle-plus"></i> Add New Jersey</h5>
            <form method="POST" enctype="multipart/form-data">
              <input type="hidden" name="action" value="add">
              <div class="mb-3">
                <label class="form-label">Jersey Name</label>
                <input type="text" name="jersey_name" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Category</label>
                <select name="category" class="form-select" id="categorySelect">
                  <option value="Custom Template">Custom Template (used in customizer)</option>
                  <option value="Ready-Made">Ready-Made (shown in Shop with photo)</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Base Price (Rs.)</label>
                <input type="number" step="0.01" min="0" name="base_price" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Default Color</label>
                <input type="color" name="base_color" class="form-control form-control-color w-100" value="#e11d2e">
              </div>
              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="2"></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">Product Photo <small class="text-muted" id="photoHint">(required for Ready-Made)</small></label>
                <input type="file" name="jersey_image" class="form-control" accept="image/png, image/jpeg, image/webp">
              </div>
              <button type="submit" class="btn btn-accent w-100 btn-pill"><i class="fa-solid fa-plus"></i> Add Jersey</button>
            </form>
          </div>
        </div>

        <!-- Jersey List -->
        <div class="col-lg-8">
          <div class="row g-3">
            <?php foreach ($allJerseys as $j): ?>
              <div class="col-md-6">
                <div class="jersey-shop-card">
                  <div class="shop-img-wrap" style="height:160px;">
                    <span class="price-tag">Rs. <?= number_format($j['base_price'], 2) ?></span>
                    <?php if (!empty($j['image_path'])): ?>
                      <img src="../<?= e($j['image_path']) ?>" alt="">
                    <?php else: ?>
                      <div class="no-photo"><i class="fa-solid fa-shirt"></i></div>
                    <?php endif; ?>
                  </div>
                  <div class="card-body p-3">
                    <span class="badge category-badge-<?= e(str_replace(' ', '-', $j['category'])) ?> mb-2"><?= e($j['category']) ?></span>
                    <?php if (!$j['is_active']): ?><span class="badge bg-secondary mb-2">Hidden</span><?php endif; ?>
                    <h6 class="fw-bold mb-1"><?= e($j['jersey_name']) ?></h6>
                    <p class="text-muted small mb-2"><?= e($j['description'] ?? '') ?></p>
                    <div class="d-flex gap-2">
                      <a href="edit_jersey.php?id=<?= $j['jersey_id'] ?>" class="btn btn-sm btn-outline-navy flex-fill"><i class="fa-solid fa-pen"></i> Edit</a>
                      <form method="POST" class="flex-fill">
                        <input type="hidden" name="action" value="toggle">
                        <input type="hidden" name="jersey_id" value="<?= $j['jersey_id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-secondary w-100">
                          <?= $j['is_active'] ? 'Hide' : 'Show' ?>
                        </button>
                      </form>
                      <form method="POST" onsubmit="return confirm('Delete this jersey permanently?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="jersey_id" value="<?= $j['jersey_id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('categorySelect').addEventListener('change', e => {
  document.getElementById('photoHint').textContent = e.target.value === 'Ready-Made' ? '(required for Ready-Made)' : '(optional preview image)';
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
