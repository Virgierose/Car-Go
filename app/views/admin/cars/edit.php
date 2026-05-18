<?php
$pageTitle = 'Edit Car';
$activePage = 'cars';
ob_start();
?>

<div class="red-rule"></div>

<div class="page-header">
    <div>
        <h2 class="page-title-main">Edit Car</h2>
        <p class="page-subtitle">Update vehicle — #<?= $car['car_id'] ?? '—' ?></p>
    </div>
    <a href="/admin/cars" class="btn btn-outline btn-icon"><i class="fas fa-arrow-left"></i> Back to Fleet</a>
</div>

<div class="form-card">
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-triangle"></i>
            <ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>?page=admin-cars-update" enctype="multipart/form-data">
        <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
        <input type="hidden" name="_method" value="PUT">

        <div class="form-section-label">Vehicle Information</div>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Brand <span class="req">*</span></label>
                <input type="text" name="brand" class="form-input" value="<?= htmlspecialchars($car['brand'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Model <span class="req">*</span></label>
                <input type="text" name="model_name" class="form-input" value="<?= htmlspecialchars($car['model_name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Plate Number <span class="req">*</span></label>
                <input type="text" name="plate_number" class="form-input" value="<?= htmlspecialchars($car['plate_number'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Year <span class="req">*</span></label>
                <input type="number" name="year" class="form-input" min="1990" max="2030" value="<?= htmlspecialchars($car['year'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Color</label>
                <input type="text" name="color" class="form-input" value="<?= htmlspecialchars($car['color'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Transmission</label>
                <select name="transmission" class="form-select">
                    <option value="Automatic" <?= ($car['transmission'] ?? '') === 'Automatic' ? 'selected' : '' ?>>Automatic</option>
                    <option value="Manual" <?= ($car['transmission'] ?? '') === 'Manual' ? 'selected' : '' ?>>Manual</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Fuel Type</label>
                <select name="fuel_type" class="form-select">
                    <?php foreach (['Gasoline','Diesel','Electric','Hybrid'] as $ft): ?>
                    <option value="<?= $ft ?>" <?= ($car['fuel_type'] ?? '') === $ft ? 'selected' : '' ?>><?= $ft ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Seats</label>
                <input type="number" name="seats" class="form-input" min="1" max="20" value="<?= htmlspecialchars($car['seats'] ?? '5') ?>">
            </div>
        </div>

        <div class="form-divider"></div>
        <div class="form-section-label">Pricing & Status</div>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Daily Rate (₱) <span class="req">*</span></label>
                <div class="input-prefix-wrap">
                    <span class="input-prefix">₱</span>
                    <input type="number" name="daily_rate" class="form-input has-prefix" step="0.01" min="0" value="<?= htmlspecialchars($car['daily_rate'] ?? '0') ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <?php foreach (['Available','Rented','Maintenance'] as $st): ?>
                    <option value="<?= $st ?>" <?= ($car['status'] ?? '') === $st ? 'selected' : '' ?>><?= $st ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-divider"></div>
        <div class="form-section-label">Car Image</div>
        <div class="form-group">
            <?php if (!empty($car['image'])): ?>
                <div class="current-image-wrap">
                    <p class="form-label">Current Image</p>
                    <img src="<?= htmlspecialchars($car['image']) ?>" alt="Car" class="current-image">
                </div>
            <?php endif; ?>
            <label class="upload-area" id="uploadArea">
                <input type="file" name="car_image" accept="image/*" style="display:none" onchange="previewImage(this)">
                <i class="fas fa-cloud-upload-alt upload-icon"></i>
                <p class="upload-text">Click to replace image</p>
                <p class="upload-hint">PNG, JPG up to 5MB</p>
                <img id="imagePreview" src="" alt="" style="display:none;max-height:150px;border-radius:8px;">
            </label>
        </div>

        <div class="form-actions">
            <a href="/admin/cars" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
        </div>
    </form>
</div>

<style>
.page-header { display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:24px; }
.page-title-main { font-size:1.6rem; font-weight:700; color:var(--white); letter-spacing:.04em; margin:0; }
.page-subtitle { color:var(--silver); font-size:.8rem; margin:4px 0 0; letter-spacing:.12em; text-transform:uppercase; }
.form-card { background:var(--card-bg); border:1px solid var(--border); border-radius:12px; padding:32px; max-width:900px; }
.form-section-label { color:var(--red); font-size:.75rem; font-weight:700; letter-spacing:.14em; text-transform:uppercase; margin-bottom:18px; }
.form-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:16px; margin-bottom:24px; }
.form-group { display:flex; flex-direction:column; gap:6px; }
.form-label { color:var(--silver); font-size:.8rem; font-weight:600; letter-spacing:.08em; text-transform:uppercase; }
.req { color:var(--red); }
.form-input, .form-select { background:var(--bg); border:1px solid var(--border); border-radius:8px; color:var(--white); padding:11px 14px; font-size:.9rem; width:100%; box-sizing:border-box; transition:border-color .2s; }
.form-input:focus, .form-select:focus { outline:none; border-color:var(--red); }
.input-prefix-wrap { position:relative; }
.input-prefix { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--silver); }
.has-prefix { padding-left:28px; }
.form-divider { border:none; border-top:1px solid var(--border); margin:8px 0 24px; }
.current-image-wrap { margin-bottom:12px; }
.current-image { max-height:140px; border-radius:8px; border:1px solid var(--border); object-fit:contain; }
.upload-area { display:flex; align-items:center; justify-content:center; flex-direction:column; border:2px dashed var(--border); border-radius:10px; padding:28px; cursor:pointer; transition:border-color .2s; text-align:center; }
.upload-area:hover { border-color:var(--red); }
.upload-icon { font-size:2rem; color:var(--red); margin-bottom:10px; opacity:.7; }
.upload-text { color:var(--white); font-weight:600; margin:0 0 4px; }
.upload-hint { color:var(--silver); font-size:.8rem; margin:0; }
.form-actions { display:flex; justify-content:flex-end; gap:12px; margin-top:28px; }
.btn-primary { background:var(--red); color:var(--white); border:none; padding:11px 22px; border-radius:8px; font-size:.85rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase; cursor:pointer; display:inline-flex; align-items:center; gap:8px; text-decoration:none; transition:background .2s; }
.btn-primary:hover { background:#c0392b; }
.alert { border-radius:8px; padding:14px 18px; margin-bottom:20px; display:flex; align-items:flex-start; gap:10px; }
.alert-error { background:rgba(231,76,60,.12); border:1px solid rgba(231,76,60,.3); color:#e74c3c; }
.alert-success { background:rgba(39,174,96,.12); border:1px solid rgba(39,174,96,.3); color:#27ae60; }
.alert ul { margin:0; padding-left:16px; }
</style>
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

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin_layout.php';
?>