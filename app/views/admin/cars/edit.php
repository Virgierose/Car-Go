<?php
$pageTitle  = 'Edit Car';
$activePage = 'cars';
ob_start();
?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/cars.css">

<!-- PAGE HEADER -->
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Edit Car</h1>
        <p class="page-subtitle">Update vehicle details &amp; information</p>
    </div>
    <a href="<?= BASE_URL ?>?page=admin-cars" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i> Back to Fleet
    </a>
</div>

<!-- ALERTS -->
<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-triangle"></i>
        <ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<!-- FORM CARD -->
<div class="form-card">
    <div class="form-card-header">
        <span class="form-card-title">Vehicle Information</span>
        <span class="record-count">#<?= htmlspecialchars($car['car_id'] ?? '') ?></span>
    </div>

    <form method="POST" action="<?= BASE_URL ?>?page=admin-cars-update" enctype="multipart/form-data">
        <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
        <input type="hidden" name="_method" value="PUT">
        <input type="hidden" name="car_id" value="<?= htmlspecialchars($car['car_id'] ?? '') ?>">

        <div class="form-card-body">

            <!-- Basic Details -->
            <div class="form-section-label">Basic Details</div>
            <div class="form-grid">
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
                    <label class="form-label">Plate Number <span class="req">*</span></label>
                    <input type="text" name="plate_number" class="form-input"
                           value="<?= htmlspecialchars($car['plate_number'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Year <span class="req">*</span></label>
                    <input type="number" name="year" class="form-input" min="1990" max="2030"
                           value="<?= htmlspecialchars($car['year'] ?? '') ?>" required>
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

            <div class="form-divider"></div>

            <!-- Specifications -->
            <div class="form-section-label">Specifications</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Transmission</label>
                    <select name="transmission" class="form-select">
                        <option value="Automatic" <?= ($car['transmission'] ?? '') === 'Automatic' ? 'selected' : '' ?>>Automatic</option>
                        <option value="Manual"    <?= ($car['transmission'] ?? '') === 'Manual'    ? 'selected' : '' ?>>Manual</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Fuel Type</label>
                    <select name="fuel_type" class="form-select">
                        <?php foreach (['Gasoline', 'Diesel', 'Electric', 'Hybrid'] as $ft): ?>
                        <option value="<?= $ft ?>" <?= ($car['fuel_type'] ?? '') === $ft ? 'selected' : '' ?>><?= $ft ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Engine</label>
                    <input type="text" name="engine" class="form-input" placeholder="e.g., 2.0L"
                           value="<?= htmlspecialchars($car['engine'] ?? '') ?>">
                </div>
            </div>

            <div class="form-divider"></div>

            <!-- Pricing & Status -->
            <div class="form-section-label">Pricing &amp; Status</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Daily Rate (₱) <span class="req">*</span></label>
                    <div class="input-prefix-wrap">
                        <span class="input-prefix">₱</span>
                        <input type="number" name="daily_rate" class="form-input has-prefix"
                               step="0.01" min="0"
                               value="<?= htmlspecialchars($car['daily_rate'] ?? '0') ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <?php foreach (['Available', 'Rented', 'Maintenance'] as $st): ?>
                        <option value="<?= $st ?>" <?= ($car['status'] ?? '') === $st ? 'selected' : '' ?>><?= $st ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-divider"></div>

            <!-- Car Image -->
            <div class="form-section-label">Car Image</div>
            <div class="form-group">
                <?php if (!empty($car['image'])): ?>
                    <div class="current-image-wrap">
                        <p class="form-label" style="margin-bottom:8px;">Current Image</p>
                        <img src="<?= htmlspecialchars($car['image']) ?>" alt="Car" class="current-image">
                    </div>
                <?php endif; ?>
                <label class="upload-area" id="uploadArea">
                    <input type="file" name="car_image" accept="image/*" style="display:none"
                           onchange="previewImage(this)">
                    <i class="fas fa-cloud-upload-alt upload-icon"></i>
                    <p class="upload-text">Click to replace image</p>
                    <p class="upload-hint">PNG, JPG up to 5MB</p>
                    <img id="imagePreview" src="" alt=""
                         style="display:none;max-height:150px;border-radius:8px;margin-top:12px;">
                </label>
            </div>

        </div><!-- /.form-card-body -->

        <div class="form-actions">
            <a href="<?= BASE_URL ?>?page=admin-cars" class="btn btn-outline">
                <i class="fas fa-times"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
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
            document.getElementById('imagePreview').src = e.target.result;
            document.getElementById('imagePreview').style.display = 'block';
        };
        r.readAsDataURL(input.files[0]);
    }
}
</script>