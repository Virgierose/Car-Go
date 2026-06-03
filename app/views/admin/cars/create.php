<?php
$pageTitle  = 'Add New Car';
$activePage = 'admin-cars-create';

$error  = $error  ?? null;
$errors = $errors ?? [];
$old    = $old    ?? [];
?>

<link rel="stylesheet" href="<?= ASSET_URL ?>assets/css/admin-drivers.css">

<!-- PAGE HEADER -->
<div class="page-header">
    <div>
        <h1 class="page-title-main">Add New Car</h1>
        <p class="page-subtitle">Register a new vehicle to your fleet</p>
    </div>
    <a href="<?= BASE_URL ?>?page=admin-cars" class="btn-ghost">
        <i class="fas fa-arrow-left"></i> Back to Fleet
    </a>
</div>

<!-- VALIDATION ERRORS -->
<?php if ($error): ?>
<div class="alert alert-error">
    <i class="fas fa-exclamation-circle" style="flex-shrink:0;margin-top:2px"></i>
    <div><?= htmlspecialchars($error) ?></div>
</div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
<div class="alert alert-error">
    <i class="fas fa-exclamation-circle" style="flex-shrink:0;margin-top:2px"></i>
    <ul style="margin:0;padding-left:18px;">
        <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<!-- FORM -->
<div class="form-card">
    <form method="POST" action="<?= BASE_URL ?>?page=admin-cars-store" enctype="multipart/form-data">

        <!-- ── Basic Details ─────────────────────────────── -->
        <div class="form-section-title">
            <i class="fas fa-car"></i> Basic Details
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Brand <span class="req">*</span></label>
                <input type="text" name="brand" class="form-input"
                       placeholder="e.g. Toyota"
                       value="<?= htmlspecialchars($old['brand'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Model <span class="req">*</span></label>
                <input type="text" name="model_name" class="form-input"
                       placeholder="e.g. Vios"
                       value="<?= htmlspecialchars($old['model_name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Year <span class="req">*</span></label>
                <input type="number" name="year" class="form-input"
                       placeholder="e.g. 2022" min="1990" max="2030"
                       value="<?= htmlspecialchars($old['year'] ?? '') ?>" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Plate Number <span class="req">*</span></label>
                <div class="icon-wrap">
                    <i class="fas fa-hashtag fi"></i>
                    <input type="text" name="plate_number" class="form-input fi-pad"
                           placeholder="e.g. ABC 1234"
                           value="<?= htmlspecialchars($old['plate_number'] ?? '') ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Color <span class="req">*</span></label>
                <input type="text" name="color" class="form-input"
                       placeholder="e.g. Black"
                       value="<?= htmlspecialchars($old['color'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Seats</label>
                <input type="number" name="seats" class="form-input"
                       placeholder="e.g. 5" min="1" max="20"
                       value="<?= htmlspecialchars($old['seats'] ?? '5') ?>">
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
                    <option value="Automatic" <?= ($old['transmission'] ?? 'Automatic') === 'Automatic' ? 'selected' : '' ?>>Automatic</option>
                    <option value="Manual"    <?= ($old['transmission'] ?? '') === 'Manual'             ? 'selected' : '' ?>>Manual</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Fuel Type</label>
                <select name="fuel_type" class="form-input">
                    <option value="Gasoline" <?= ($old['fuel_type'] ?? 'Gasoline') === 'Gasoline' ? 'selected' : '' ?>>Gasoline</option>
                    <option value="Diesel"   <?= ($old['fuel_type'] ?? '') === 'Diesel'           ? 'selected' : '' ?>>Diesel</option>
                    <option value="Electric" <?= ($old['fuel_type'] ?? '') === 'Electric'         ? 'selected' : '' ?>>Electric</option>
                    <option value="Hybrid"   <?= ($old['fuel_type'] ?? '') === 'Hybrid'           ? 'selected' : '' ?>>Hybrid</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Engine</label>
                <input type="text" name="engine" class="form-input"
                       placeholder="e.g. 2.0L"
                       value="<?= htmlspecialchars($old['engine'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-input">
                    <option value="available"   <?= ($old['status'] ?? 'available') === 'available'   ? 'selected' : '' ?>>Available</option>
                    <option value="maintenance" <?= ($old['status'] ?? '') === 'maintenance'           ? 'selected' : '' ?>>Maintenance</option>
                    <option value="rented"      <?= ($old['status'] ?? '') === 'rented'               ? 'selected' : '' ?>>Rented</option>
                </select>
            </div>
        </div>

        <!-- ── Pricing ───────────────────────────────────── -->
        <div class="form-section-title" style="margin-top:28px">
            <i class="fas fa-coins"></i> Pricing
        </div>

        <div class="form-row" style="max-width:340px">
            <div class="form-group">
                <label class="form-label">Daily Rate (₱) <span class="req">*</span></label>
                <div class="icon-wrap">
                    <i class="fas fa-peso-sign fi"></i>
                    <input type="number" name="daily_rate" class="form-input fi-pad"
                           placeholder="e.g. 1500" step="0.01" min="0"
                           value="<?= htmlspecialchars($old['daily_rate'] ?? '') ?>" required>
                </div>
            </div>
        </div>

        <!-- ── Car Image ─────────────────────────────────── -->
        <div class="form-section-title" style="margin-top:28px">
            <i class="fas fa-image"></i> Car Image
        </div>

        <div class="form-row">
            <div class="form-group" style="grid-column:1/-1">
                <label class="upload-label" id="uploadArea" for="carImage" style="
                    display:flex;flex-direction:column;align-items:center;justify-content:center;
                    border:2px dashed var(--border);border-radius:10px;padding:36px 20px;
                    cursor:pointer;transition:border-color .2s,background .2s;text-align:center;
                    min-height:130px;box-sizing:border-box;
                ">
                    <input type="file" name="car_image" id="carImage" accept="image/*"
                           style="display:none" onchange="previewImage(this)">
                    <div id="uploadPlaceholder">
                        <i class="fas fa-cloud-upload-alt" style="font-size:2rem;color:var(--red);opacity:.7;display:block;margin-bottom:10px;"></i>
                        <p style="color:#fff;font-weight:600;margin:0 0 4px;font-size:.88rem;">Click to upload car image</p>
                        <p style="color:var(--silver);font-size:.75rem;margin:0;">PNG, JPG, GIF, WEBP up to 5MB</p>
                    </div>
                    <img id="imagePreview" src="" alt=""
                         style="display:none;max-width:100%;max-height:180px;border-radius:8px;object-fit:contain;">
                </label>
            </div>
        </div>

        <!-- ── Actions ───────────────────────────────────── -->
        <div class="form-actions">
            <a href="<?= BASE_URL ?>?page=admin-cars" class="btn-ghost">Cancel</a>
            <button type="submit" class="btn-primary">
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
            const preview     = document.getElementById('imagePreview');
            const placeholder = document.getElementById('uploadPlaceholder');
            preview.src           = e.target.result;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Hover effect on upload area (no extra CSS class needed)
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