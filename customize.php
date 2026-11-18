<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/classes/Jersey.php';
requireLogin();

$jerseyModel = new Jersey();
$jerseys = $jerseyModel->getJerseysByCategory('Custom Template');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jerseyId      = (int)($_POST['jersey_id'] ?? 0);
    $jerseyColor   = $_POST['jersey_color'] ?? '#e11d2e';
    $sleeveColor   = $_POST['sleeve_color'] ?? '#FFFFFF';
    $designPattern = $_POST['design_pattern'] ?? 'Plain';
    $patternColor  = $_POST['pattern_color'] ?? '#FFD60A';
    $size          = $_POST['size'] ?? 'M';
    $playerName    = trim($_POST['player_name'] ?? '');
    $playerNumber  = trim($_POST['player_number'] ?? '');

    $allowedPatterns = ['Plain', 'Sash', 'Hoops', 'Stripes'];
    if (!in_array($designPattern, $allowedPatterns, true)) $designPattern = 'Plain';

    if ($jerseyId <= 0) $errors[] = "Please select a jersey template.";
    if ($playerName === '') $errors[] = "Player name is required (printed on the back).";
    if ($playerNumber === '' || !ctype_digit($playerNumber)) $errors[] = "Player number must be numeric (printed on the back).";

    $logoPath = null;
    if (!empty($_FILES['logo']['name'])) {
        $logoPath = $jerseyModel->uploadLogo($_FILES['logo']);
        if ($logoPath === null) {
            $errors[] = "Logo upload failed. Only JPG/PNG/WEBP files under 2MB are allowed.";
        }
    }

    if (empty($errors)) {
        $customizationId = $jerseyModel->saveCustomization([
            'user_id'        => $_SESSION['user_id'],
            'jersey_id'      => $jerseyId,
            'purchase_type'  => 'Custom',
            'jersey_color'   => $jerseyColor,
            'sleeve_color'   => $sleeveColor,
            'design_pattern' => $designPattern,
            'pattern_color'  => $patternColor,
            'size'           => $size,
            'player_name'    => $playerName,
            'player_number'  => $playerNumber,
            'logo_path'      => $logoPath,
        ]);

        header("Location: place_order.php?customization_id=" . $customizationId);
        exit;
    }
}

$pageTitle = "Customize Jersey";
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-banner">
  <div class="container">
    <span class="hero-badge mb-2 d-inline-block"><i class="fa-solid fa-star"></i> Official Kit Builder</span>
    <h2><i class="fa-solid fa-shirt"></i> Customize Your Jersey</h2>
    <p>Name &amp; number printed on the back, your logo on the front &mdash; just like a real club kit.</p>
  </div>
</div>

<div class="container my-4">

  <?php foreach ($errors as $err): ?>
    <div class="alert alert-danger py-2"><i class="fa-solid fa-circle-exclamation"></i> <?= e($err) ?></div>
  <?php endforeach; ?>

  <?php if (empty($jerseys)): ?>
    <div class="alert alert-warning">No custom jersey templates are available right now. Please check the <a href="shop.php">Ready-Made Shop</a> instead.</div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    <div class="row g-4">
      <!-- Live Preview -->
      <div class="col-lg-5">
        <div class="jersey-preview-wrap">
          <span class="official-tag"><i class="fa-solid fa-shield-halved"></i> Live Preview</span>

          <div class="btn-group view-toggle-group mb-3" role="group">
            <button type="button" class="btn btn-outline-secondary btn-sm active" id="btnFront">
              <i class="fa-solid fa-shirt"></i> Front
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="btnBack">
              <i class="fa-solid fa-arrow-rotate-right"></i> Back
            </button>
          </div>

          <!-- FRONT VIEW: logo on chest -->
          <svg id="jerseyFrontSvg" viewBox="0 0 300 320" width="100%" height="320">
            <path id="jerseyBodyFront" class="jersey-svg-body"
              d="M90 20 L130 5 L150 20 L170 5 L210 20 L230 60 L200 80 L200 300 L100 300 L100 80 L70 60 Z"
              fill="#e11d2e" stroke="#1c2433" stroke-width="2"/>
            <path id="patternFront" class="jersey-svg-pattern" d="" fill="none"/>
            <path id="jerseySleeveLF" class="jersey-svg-sleeve"
              d="M90 20 L70 60 L45 75 L60 30 Z" fill="#FFFFFF" stroke="#1c2433" stroke-width="2"/>
            <path id="jerseySleeveRF" class="jersey-svg-sleeve"
              d="M210 20 L230 60 L255 75 L240 30 Z" fill="#FFFFFF" stroke="#1c2433" stroke-width="2"/>
            <circle cx="150" cy="45" r="12" fill="#ffffff" fill-opacity="0.15" stroke="#1c2433" stroke-width="1.5"/>
            <image id="jerseyLogo" x="132" y="70" width="36" height="36" href="" />
            <rect x="132" y="70" width="36" height="36" fill="none" stroke="#ffffff" stroke-opacity="0.4" stroke-dasharray="4 3" id="logoPlaceholder"/>
            <text x="150" y="92" font-size="8" text-anchor="middle" fill="#ffffff" fill-opacity="0.55" id="logoHint">LOGO</text>
          </svg>

          <!-- BACK VIEW: name + number -->
          <svg id="jerseyBackSvg" viewBox="0 0 300 320" width="100%" height="320" style="display:none;">
            <path id="jerseyBodyBack" class="jersey-svg-body"
              d="M90 20 L130 5 L150 20 L170 5 L210 20 L230 60 L200 80 L200 300 L100 300 L100 80 L70 60 Z"
              fill="#e11d2e" stroke="#1c2433" stroke-width="2"/>
            <path id="patternBack" class="jersey-svg-pattern" d="" fill="none"/>
            <path id="jerseySleeveLB" class="jersey-svg-sleeve"
              d="M90 20 L70 60 L45 75 L60 30 Z" fill="#FFFFFF" stroke="#1c2433" stroke-width="2"/>
            <path id="jerseySleeveRB" class="jersey-svg-sleeve"
              d="M210 20 L230 60 L255 75 L240 30 Z" fill="#FFFFFF" stroke="#1c2433" stroke-width="2"/>
            <text id="jerseyNameText" x="150" y="105" font-size="16" font-weight="bold"
                  text-anchor="middle" fill="#FFFFFF">NAME</text>
            <text id="jerseyNumber" x="150" y="210" font-size="70" font-weight="bold"
                  text-anchor="middle" fill="#FFFFFF" stroke="#1c2433" stroke-width="1">0</text>
          </svg>

          <p class="mt-2 mb-0"><strong>Size:</strong> <span id="previewSize">M</span>
            &nbsp;|&nbsp; <strong>Pattern:</strong> <span id="previewPattern">Plain</span></p>
        </div>
      </div>

      <!-- Customization Controls -->
      <div class="col-lg-7">
        <div class="card p-4 shadow-sm">
          <div class="mb-3">
            <label class="form-label fw-semibold">Jersey Template</label>
            <select name="jersey_id" id="jerseyTemplate" class="form-select" required>
              <option value="">-- Select Jersey --</option>
              <?php foreach ($jerseys as $j): ?>
                <option value="<?= $j['jersey_id'] ?>" data-color="<?= e($j['base_color']) ?>">
                  <?= e($j['jersey_name']) ?> (Rs. <?= number_format($j['base_price'], 2) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label fw-semibold">Jersey Color</label>
              <input type="color" class="form-control form-control-color w-100" name="jersey_color" id="jerseyColor" value="#e11d2e">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label fw-semibold">Sleeve Color</label>
              <input type="color" class="form-control form-control-color w-100" name="sleeve_color" id="sleeveColor" value="#FFFFFF">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Design Pattern</label>
            <input type="hidden" name="design_pattern" id="designPatternInput" value="Plain">
            <input type="hidden" name="pattern_color" id="patternColorInput" value="#FFD60A">
            <div class="row g-2">
              <div class="col-3">
                <div class="pattern-swatch active" data-pattern="Plain" style="background:#e11d2e;"></div>
                <small>Plain</small>
              </div>
              <div class="col-3">
                <div class="pattern-swatch" data-pattern="Sash" style="background:linear-gradient(135deg,#e11d2e 40%, #FFD60A 40%, #FFD60A 55%, #e11d2e 55%);"></div>
                <small>Sash</small>
              </div>
              <div class="col-3">
                <div class="pattern-swatch" data-pattern="Hoops" style="background:repeating-linear-gradient(#e11d2e 0 10px, #FFD60A 10px 20px);"></div>
                <small>Hoops</small>
              </div>
              <div class="col-3">
                <div class="pattern-swatch" data-pattern="Stripes" style="background:repeating-linear-gradient(90deg,#e11d2e 0 8px, #FFD60A 8px 16px);"></div>
                <small>Stripes</small>
              </div>
            </div>
            <div class="mt-2">
              <label class="form-label small">Pattern Color</label>
              <input type="color" class="form-control form-control-color" id="patternColorPicker" value="#FFD60A" style="width:60px;">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Size</label>
            <select name="size" id="jerseySize" class="form-select">
              <option>XS</option><option>S</option><option selected>M</option>
              <option>L</option><option>XL</option><option>XXL</option>
            </select>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label fw-semibold">Player Name <small class="text-muted">(back)</small></label>
              <input type="text" class="form-control" name="player_name" id="playerName" maxlength="20" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label fw-semibold">Player Number <small class="text-muted">(back)</small></label>
              <input type="number" class="form-control" name="player_number" id="playerNumber" min="0" max="99" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Upload Logo <small class="text-muted">(front, JPG/PNG, max 2MB)</small></label>
            <input type="file" class="form-control" name="logo" id="logoUpload" accept="image/png, image/jpeg, image/webp">
          </div>

          <button type="submit" class="btn btn-accent w-100 mt-2 btn-pill">
            <i class="fa-solid fa-cart-plus"></i> Save Design & Continue to Order
          </button>
          <a href="shop.php" class="btn btn-outline-navy w-100 mt-2 btn-pill">
            <i class="fa-solid fa-bag-shopping"></i> Browse Ready-Made Jerseys Instead
          </a>
        </div>
      </div>
    </div>
  </form>
</div>

<script>
const frontBody = document.getElementById('jerseyBodyFront');
const backBody  = document.getElementById('jerseyBodyBack');
const sleeveLF  = document.getElementById('jerseySleeveLF');
const sleeveRF  = document.getElementById('jerseySleeveRF');
const sleeveLB  = document.getElementById('jerseySleeveLB');
const sleeveRB  = document.getElementById('jerseySleeveRB');
const patternFront = document.getElementById('patternFront');
const patternBack  = document.getElementById('patternBack');
const numberText   = document.getElementById('jerseyNumber');
const nameText      = document.getElementById('jerseyNameText');
const logoImage     = document.getElementById('jerseyLogo');
const logoPlaceholder = document.getElementById('logoPlaceholder');
const logoHint       = document.getElementById('logoHint');
const previewSize   = document.getElementById('previewSize');
const previewPattern = document.getElementById('previewPattern');
const designPatternInput = document.getElementById('designPatternInput');
const patternColorInput  = document.getElementById('patternColorInput');

const frontSvg = document.getElementById('jerseyFrontSvg');
const backSvg  = document.getElementById('jerseyBackSvg');
const btnFront = document.getElementById('btnFront');
const btnBack  = document.getElementById('btnBack');

btnFront.addEventListener('click', () => {
  frontSvg.style.display = '';
  backSvg.style.display = 'none';
  btnFront.classList.add('active');
  btnBack.classList.remove('active');
});
btnBack.addEventListener('click', () => {
  frontSvg.style.display = 'none';
  backSvg.style.display = '';
  btnBack.classList.add('active');
  btnFront.classList.remove('active');
});

function setBodyColor(color) {
  frontBody.setAttribute('fill', color);
  backBody.setAttribute('fill', color);
}
function setSleeveColor(color) {
  sleeveLF.setAttribute('fill', color);
  sleeveRF.setAttribute('fill', color);
  sleeveLB.setAttribute('fill', color);
  sleeveRB.setAttribute('fill', color);
}

const PATTERN_PATHS = {
  Plain:   "",
  Sash:    "M90 20 L120 300 L150 300 L120 20 Z",
  Hoops:   "M100 110 H200 V130 H100 Z M100 150 H200 V170 H100 Z M100 190 H200 V210 H100 Z M100 230 H200 V250 H100 Z",
  Stripes: "M112 20 H124 V300 H112 Z M148 15 H160 V300 H148 Z M184 20 H196 V300 H184 Z"
};

function setPattern(pattern, color) {
  const d = PATTERN_PATHS[pattern] || "";
  patternFront.setAttribute('d', d);
  patternBack.setAttribute('d', d);
  patternFront.setAttribute('fill', color);
  patternBack.setAttribute('fill', color);
  patternFront.setAttribute('opacity', pattern === 'Plain' ? '0' : '0.9');
  patternBack.setAttribute('opacity', pattern === 'Plain' ? '0' : '0.9');
  previewPattern.textContent = pattern;
  designPatternInput.value = pattern;
}

document.getElementById('jerseyColor').addEventListener('input', e => setBodyColor(e.target.value));
document.getElementById('sleeveColor').addEventListener('input', e => setSleeveColor(e.target.value));

document.getElementById('jerseyTemplate').addEventListener('change', e => {
  const opt = e.target.selectedOptions[0];
  if (opt && opt.dataset.color) {
    document.getElementById('jerseyColor').value = opt.dataset.color;
    setBodyColor(opt.dataset.color);
  }
});

document.querySelectorAll('.pattern-swatch').forEach(sw => {
  sw.addEventListener('click', () => {
    document.querySelectorAll('.pattern-swatch').forEach(s => s.classList.remove('active'));
    sw.classList.add('active');
    setPattern(sw.dataset.pattern, patternColorInput.value);
  });
});

document.getElementById('patternColorPicker').addEventListener('input', e => {
  patternColorInput.value = e.target.value;
  const active = document.querySelector('.pattern-swatch.active');
  setPattern(active ? active.dataset.pattern : 'Plain', e.target.value);
});

document.getElementById('playerNumber').addEventListener('input', e => {
  numberText.textContent = e.target.value || '0';
});

document.getElementById('playerName').addEventListener('input', e => {
  nameText.textContent = (e.target.value || 'NAME').toUpperCase();
});

document.getElementById('jerseySize').addEventListener('change', e => {
  previewSize.textContent = e.target.value;
});

document.getElementById('logoUpload').addEventListener('change', e => {
  const file = e.target.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = evt => {
    logoImage.setAttribute('href', evt.target.result);
    logoPlaceholder.style.display = 'none';
    logoHint.style.display = 'none';
  };
  reader.readAsDataURL(file);
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
