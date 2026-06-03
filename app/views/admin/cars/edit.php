<?php
$pageTitle  = 'Edit Car';
$activePage = 'cars';
ob_start();
?>

<link rel="stylesheet" href="<?= ASSET_URL ?>assets/css/admin-drivers.css">

<!-- PAGE HEADER -->
<div class="page-header">
    <div>
        <h1 class="page-title-main">Edit Car</h1>
        <p class="page-subtitle">Update vehicle details &amp; information</p>
    </div>
    <a href="<?= BASE_URL ?>?page=admin-cars" class="btn-ghost">
        <i class="fas fa-arrow-left"></i> Back to Fleet
    </a>
</div>

<!-- ALERTS -->
<?php if (!empty($errors)): ?>
<div class="alert alert-error">
    <i class="fas fa-exclamation-circle" style="flex-shrink:0;margin-top:2px"></i>
    <ul style="margin:0;padding-left:18px;">
        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>
<?php if (!empty($success)): ?>
<div class="alert" style="background:rgba(39,174,96,.12);border:1px solid rgba(39,174,96,.3);color:#2ecc71;display:flex;align-items:center;gap:10px;padding:13px 18px;border-radius:10px;margin-bottom:18px;font-size:.875rem;">
    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
</div>
<?php endif; ?>

<!-- FORM -->
<div class="form-card">
    <form method="POST" action="<?= BASE_URL ?>?page=admin-cars-update" enctype="multipart/form-data">
        <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
        <input type="hidden" name="_method" value="PUT">
        <input type="hidden" name="car_id" value="<?= htmlspecialchars($car['car_id'] ?? '') ?>">

        <!-- ── Basic Details ─────────────────────────────── -->
        <div class="form-section-title">
            <i class="fas fa-car"></i> Basic Details
            <span style="margin-left:auto;font-size:.75rem;color:var(--silver);font-weight:400;letter-spacing:.06em;text-transform:none;">
                #<?= htmlspecialchars($car['car_id'] ?? '') ?>
            </span>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Brand <span class="req">*</span></label>
                <input type="text" name="brand" class="form-input"
                       value="<?= htmlspecialchars($car['brand'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Model <span class="req">*</span></label>
                <input type="text" name="model_name" class="form-input"
                       value="<?= htmlspecialchars($car['model_name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Year <span class="req">*</span></label>
                <input type="number" name="year" class="form-input" min="1990" max="2030"
                       value="<?= htmlspecialchars($car['year'] ?? '') ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Plate Number <span class="req">*</span></label>
                <div class="icon-wrap">
                    <i class="fas fa-hashtag fi"></i>
                    <input type="text" name="plate_number" class="form-input fi-pad"
                           value="<?= htmlspecialchars($car['plate_number'] ?? '') ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Color</label>
                <input type="text" name="color" class="form-input"
                       value="<?= htmlspecialchars($car['color'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Seats</label>
                <input type="number" name="seats" class="form-input" min="1" max="20"
                       value="<?= htmlspecialchars($car['seats'] ?? '5') ?>">
            </div>
        </div>

        <!-- ── Specifications ────────────────────────────── -->
        <div class="form-section-title" style="margin-top:28px">
            <i class="fas fa-cogs"></i> Specifications
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Transmission</label>
                <select name="transmission" class="form-input">
                    <option value="Automatic" <?= ($car['transmission'] ?? '') === 'Automatic' ? 'selected' : '' ?>>Automatic</option>
                    <option value="Manual"    <?= ($car['transmission'] ?? '') === 'Manual'    ? 'selected' : '' ?>>Manual</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Fuel Type</label>
                <select name="fuel_type" class="form-input">
                    <?php foreach (['Gasoline', 'Diesel', 'Electric', 'Hybrid'] as $ft): ?>
                    <option value="<?= $ft ?>" <?= ($car['fuel_type'] ?? '') === $ft ? 'selected' : '' ?>><?= $ft ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Engine</label>
                <input type="text" name="engine" class="form-input" placeholder="e.g. 2.0L"
                       value="<?= htmlspecialchars($car['engine'] ?? '') ?>">
            </div>
        </div>

        <!-- ── Pricing & Status ───────────────────────────── -->
        <div class="form-section-title" style="margin-top:28px">
            <i class="fas fa-coins"></i> Pricing &amp; Status
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Daily Rate (₱) <span class="req">*</span></label>
                <div class="icon-wrap">
                    <i class="fas fa-peso-sign fi"></i>
                    <input type="number" name="daily_rate" class="form-input fi-pad"
                           step="0.01" min="0"
                           value="<?= htmlspecialchars($car['daily_rate'] ?? '0') ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-input">
                    <?php foreach (['available' => 'Available', 'rented' => 'Rented', 'maintenance' => 'Maintenance'] as $val => $label): ?>
                    <option value="<?= $val ?>" <?= strtolower($car['status'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- ── Car Image ─────────────────────────────────── -->
        <div class="form-section-title" style="margin-top:28px">
            <i class="fas fa-image"></i> Car Image
        </div>

        <div class="form-row">
            <div class="form-group" style="grid-column:1/-1">

                <?php if (!empty($car['image'])): ?>
                <div style="margin-bottom:14px;">
                    <p class="form-label" style="margin-bottom:8px;text-transform:uppercase;letter-spacing:.08em;">Current Image</p>
                    <img src="<?= BASE_URL . htmlspecialchars($car['image']) ?>"
                         alt="Car" onerror="this.style.display='none'"
                         style="max-height:140px;max-width:100%;border-radius:8px;border:1px solid var(--border);object-fit:contain;display:block;">
                </div>
                <?php endif; ?>

                <label id="uploadArea" for="carImageInput" style="
                    display:flex;flex-direction:column;align-items:center;justify-content:center;
                    border:2px dashed var(--border);border-radius:10px;padding:32px 20px;
                    cursor:pointer;transition:border-color .2s,background .2s;text-align:center;
                    min-height:120px;box-sizing:border-box;
                ">
                    <input type="file" name="car_image" id="carImageInput" accept="image/*"
                           style="display:none" onchange="previewImage(this)">
                    <div id="uploadPlaceholder">
                        <i class="fas fa-cloud-upload-alt" style="font-size:1.8rem;color:var(--red);opacity:.7;display:block;margin-bottom:8px;"></i>
                        <p style="color:#fff;font-weight:600;margin:0 0 3px;font-size:.88rem;">Click to replace image</p>
                        <p style="color:var(--silver);font-size:.75rem;margin:0;">PNG, JPG up to 5MB</p>
                    </div>
                    <img id="imagePreview" src="" alt=""
                         style="display:none;max-width:100%;max-height:150px;border-radius:8px;object-fit:contain;">
                </label>
            </div>
        </div>

        <!-- ── Actions ───────────────────────────────────── -->
        <div class="form-actions">
            <a href="<?= BASE_URL ?>?page=admin-cars" class="btn-ghost">Cancel</a>
            <button type="submit" class="btn-primary">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </div>

    </form>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const r = new FileReader();
        r.onload = e => {
            const preview     = document.getElementById('imagePreview');
            const placeholder = document.getElementById('uploadPlaceholder');
            preview.src = e.target.result;
            preview.style.display    = 'block';
            placeholder.style.display = 'none';
        };
        r.readAsDataURL(input.files[0]);
    }
}

const area = document.getElementById('uploadArea');
if (area) {
    area.addEventListener('mouseenter', () => {
        area.style.borderColor = 'var(--red)';
        area.style.background  = 'rgba(192,17,31,.04)';
    });
    area.addEventListener('mouseleave', () => {
        area.style.borderColor = 'var(--border)';
        area.style.background  = '';
    });
}
</script>