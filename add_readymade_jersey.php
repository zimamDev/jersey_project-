<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../classes/Jersey.php';
requireAdmin();

$jerseyModel = new Jersey();
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['jersey_name'] ?? '');
    $price       = (float)($_POST['base_price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $baseColor   = $_POST['base_color'] ?? '#e11d2e';

    if ($name === '') $errors[] = "Jersey name is required.";
    if ($price <= 0) $errors[] = "Base price must be greater than 0.";

    $imagePath = null;
    if (!empty($_FILES['jersey_image']['name'])) {
        $imagePath = $jerseyModel->uploadJerseyImage($_FILES['jersey_image']);
        if ($imagePath === null) {
            $errors[] = "Image upload failed. Only JPG/PNG/WEBP files under 3MB are allowed.";
        }
    } else {
        $errors[] = "Please upload a product photo for this ready-made jersey.";
    }

    if (empty($errors)) {
        $newId = $jerseyModel->addJersey([
            'jersey_name' => $name,
            'category'    => 'Ready-Made',
            'base_price'  => $price,
            'description' => $description,
            'image_path'  => $imagePath,
            'base_color'  => $baseColor,
            'is_active'   => 1,
        ]);
        $success = "\"$name\" was added to the Ready-Made Shop successfully!";
        $_POST = []; // clear form after success
    }
}

$readymadeJerseys = $jerseyModel->getJerseysByCategory('Ready-Made');

$pageTitle = "Add Ready-Made Jersey";
$assetPrefix = "../";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
  <div class="row">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <div class="col-md-10 py-4">
      <div class="page-banner-inline mb-4">
        <h2 class="section-title mb-1"><i class="fa-solid fa-cloud-arrow-up"></i> Add Ready-Made Jersey</h2>
        <p class="text-muted">Upload a finished jersey photo &amp; price so customers can buy it instantly from the Ready-Made Shop.</p>
      </div>

      <?php foreach ($errors as $err): ?>
        <div class="alert alert-danger py-2"><i class="fa-solid fa-circle-exclamation"></i> <?= e($err) ?></div>
      <?php endforeach; ?>
      <?php if ($success): ?>
        <div class="alert alert-success py-2"><i class="fa-solid fa-circle-check"></i> <?= e($success) ?>
          &nbsp;<a href="../shop.php" target="_blank" class="alert-link">View in Shop <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
        </div>
      <?php endif; ?>

      <div class="row g-4">
        <!-- Upload Form -->
        <div class="col-lg-5">
          <div class="upload-card">
            <form method="POST" enctype="multipart/form-data">
              <div class="mb-3">
                <label class="form-label fw-semibold">Product Photo <span class="text-danger">*</span></label>
                <label for="photoInput" class="photo-drop">
                  <img id="photoPreviewImg" src="" alt="" style="display:none;">
                  <span id="photoDropText">
                    <i class="fa-solid fa-cloud-arrow-up fa-2x mb-2"></i><br>
                    Click to upload jersey photo<br>
                    <small class="text-muted">JPG, PNG or WEBP &middot; max 3MB</small>
                  </span>
                </label>
                <input type="file" id="photoInput" name="jersey_image" accept="image/png, image/jpeg, image/webp" class="d-none" required>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Jersey Name</label>
                <input type="text" name="jersey_name" class="form-control" placeholder="e.g. Home Kit 2026" required>
              </div>

              <div class="row">
                <div class="col-md-7 mb-3">
                  <label class="form-label fw-semibold">Price (Rs.)</label>
                  <input type="number" step="0.01" min="0" name="base_price" class="form-control" placeholder="3200.00" required>
                </div>
                <div class="col-md-5 mb-3">
                  <label class="form-label fw-semibold">Accent Color</label>
                  <input type="color" name="base_color" class="form-control form-control-color w-100" value="#e11d2e">
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Description</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Fabric, fit, style notes shown to customers..."></textarea>
              </div>

              <button type="submit" class="btn btn-accent w-100 btn-pill">
                <i class="fa-solid fa-bag-shopping"></i> Publish to Ready-Made Shop
              </button>
              <a href="manage_jerseys.php" class="btn btn-outline-navy w-100 mt-2 btn-pill">
                <i class="fa-solid fa-list"></i> Go to Full Jersey Catalog
              </a>
            </form>
          </div>
        </div>

        <!-- Recently Added Ready-Made Jerseys -->
        <div class="col-lg-7">
          <h5 class="mb-3"><i class="fa-solid fa-shirt"></i> Currently in the Ready-Made Shop (<?= count($readymadeJerseys) ?>)</h5>
          <div class="row g-3">
            <?php foreach ($readymadeJerseys as $j): ?>
              <div class="col-md-6">
                <div class="jersey-shop-card">
                  <div class="shop-img-wrap" style="height:170px;">
                    <span class="price-tag">Rs. <?= number_format($j['base_price'], 2) ?></span>
                    <?php if (!empty($j['image_path'])): ?>
                      <img src="../<?= e($j['image_path']) ?>" alt="">
                    <?php else: ?>
                      <div class="no-photo"><i class="fa-solid fa-shirt"></i></div>
                    <?php endif; ?>
                  </div>
                  <div class="card-body p-3">
                    <?php if (!$j['is_active']): ?><span class="badge bg-secondary mb-1">Hidden</span><?php endif; ?>
                    <h6 class="fw-bold mb-1"><?= e($j['jersey_name']) ?></h6>
                    <p class="text-muted small mb-2"><?= e($j['description'] ?? '') ?></p>
                    <a href="edit_jersey.php?id=<?= $j['jersey_id'] ?>" class="btn btn-sm btn-outline-navy w-100"><i class="fa-solid fa-pen"></i> Edit</a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
            <?php if (empty($readymadeJerseys)): ?>
              <div class="col-12">
                <div class="alert alert-info mb-0">No ready-made jerseys yet. Upload your first one on the left!</div>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
const photoInput = document.getElementById('photoInput');
const photoPreviewImg = document.getElementById('photoPreviewImg');
const photoDropText = document.getElementById('photoDropText');

photoInput.addEventListener('change', e => {
  const file = e.target.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = evt => {
    photoPreviewImg.src = evt.target.result;
    photoPreviewImg.style.display = 'block';
    photoDropText.style.display = 'none';
  };
  reader.readAsDataURL(file);
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
