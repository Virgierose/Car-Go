<?php
$pageTitle  = 'Add New Car';
$activePage = 'admin-cars-create';

$error  = $error  ?? null;
$errors = $errors ?? [];
$old    = $old    ?? [];
?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/cars.css">

<!-- PAGE HEADER -->
<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">Add New Car</h1>
        <p class="page-subtitle">Register a new vehicle to your fleet</p>
    </div>
    <a href="<?= BASE_URL ?>?page=admin-cars" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i> Back to Fleet
    </a>
</div>

<!-- ALERTS -->
<?php if ($error): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-triangle"></i>
        <div><?= htmlspecialchars($error) ?></div>
    </div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-triangle"></i>
        <ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<!-- FORM CARD -->
<div class="form-card">
    <div class="form-card-header">
        <span class="form-card-title">Vehicle Information</span>
    </div>

    <form method="POST" action="<?= BASE_URL ?>?page=admin-cars-store" enctype="multipart/form-data">

        <div class="form-card-body">

            <!-- Vehicle Details -->
            <div class="form-section-label">Basic Details</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Brand <span class="req">*</span></label>
                    <input type="text" name="brand" class="form-input" placeholder="e.g. Toyota"
                           value="<?= htmlspecialchars($old['brand'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Model <span class="req">*</span></label>
                    <input type="text" name="model_name" class="form-input" placeholder="e.g. Vios"
                           value="<?= htmlspecialchars($old['model_name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Plate Number <span class="req">*</span></label>
                    <input type="text" name="plate_number" class="form-input" placeholder="e.g. ABC 1234"
                           value="<?= htmlspecialchars($old['plate_number'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Year <span class="req">*</span></label>
                    <input type="number" name="year" class="form-input" placeholder="e.g. 2022"
                           min="1990" max="2030"
                           value="<?= htmlspecialchars($old['year'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Color <span class="req">*</span></label>
                    <input type="text" name="color" class="form-input" placeholder="e.g. Black"
                           value="<?= htmlspecialchars($old['color'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Seats</label>
                    <input type="number" name="seats" class="form-input" placeholder="e.g. 5"
                           min="1" max="20" value="<?= htmlspecialchars($old['seats'] ?? '5') ?>">
                </div>
            </div>

            <div class="form-divider"></div>

            <!-- Specs -->
            <div class="form-section-label">Specifications</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Transmission</label>
                    <select name="transmission" class="form-select">
                        <option value="Automatic" <?= ($old['transmission'] ?? 'Automatic') === 'Automatic' ? 'selected' : '' ?>>Automatic</option>
                        <option value="Manual"    <?= ($old['transmission'] ?? '') === 'Manual'             ? 'selected' : '' ?>>Manual</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Fuel Type</label>
                    <select name="fuel_type" class="form-select">
                        <option value="Gasoline" <?= ($old['fuel_type'] ?? 'Gasoline') === 'Gasoline' ? 'selected' : '' ?>>Gasoline</option>
                        <option value="Diesel"   <?= ($old['fuel_type'] ?? '') === 'Diesel'           ? 'selected' : '' ?>>Diesel</option>
                        <option value="Electric" <?= ($old['fuel_type'] ?? '') === 'Electric'         ? 'selected' : '' ?>>Electric</option>
                        <option value="Hybrid"   <?= ($old['fuel_type'] ?? '') === 'Hybrid'           ? 'selected' : '' ?>>Hybrid</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Engine</label>
                    <input type="text" name="engine" class="form-input" placeholder="e.g. 2.0L"
                           value="<?= htmlspecialchars($old['engine'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="available"   <?= ($old['status'] ?? 'available') === 'available'   ? 'selected' : '' ?>>Available</option>
                        <option value="maintenance" <?= ($old['status'] ?? '') === 'maintenance'           ? 'selected' : '' ?>>Maintenance</option>
                        <option value="rented"      <?= ($old['status'] ?? '') === 'rented'               ? 'selected' : '' ?>>Rented</option>
                    </select>
                </div>
            </div>

            <div class="form-divider"></div>

            <!-- Pricing -->
            <div class="form-section-label">Pricing</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Daily Rate (₱) <span class="req">*</span></label>
                    <div class="input-prefix-wrap">
                        <span class="input-prefix">₱</span>
                        <input type="number" name="daily_rate" class="form-input has-prefix"
                               placeholder="0.00" step="0.01" min="0"
                               value="<?= htmlspecialchars($old['daily_rate'] ?? '0') ?>" required>
                    </div>
                </div>
            </div>

            <div class="form-divider"></div>

            <!-- Image Upload -->
            <div class="form-section-label">Car Image</div>
            <div class="form-group">
                <label class="upload-area" id="uploadArea">
                    <input type="file" name="car_image" id="carImage" accept="image/*"
                           style="display:none" onchange="previewImage(this)">
                    <div id="uploadPlaceholder">
                        <i class="fas fa-cloud-upload-alt upload-icon"></i>
                        <p class="upload-text">Click to upload car image</p>
                        <p class="upload-hint">PNG, JPG, GIF, WEBP up to 5MB</p>
                    </div>
                    <img id="imagePreview" src="" alt=""
                         style="display:none;max-height:180px;border-radius:8px;object-fit:contain;">
                </label>
            </div>

        </div><!-- /.form-card-body -->

        <div class="form-actions">
            <a href="<?= BASE_URL ?>?page=admin-cars" class="btn btn-outline">
                <i class="fas fa-times"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Car
            </button>
        </div>

    </form>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const preview = document.getElementById('imagePreview');
            const placeholder = document.getElementById('uploadPlaceholder');
            preview.src = e.target.result;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>