<?php
$pageTitle = 'Add New Car';
$activePage = 'cars';
ob_start();

$error = $error ?? null;
$errors = $errors ?? [];
$old = $old ?? [];
?>

<div class="red-rule"></div>

<div class="page-header">
    <div>
        <h2 class="page-title-main">Add New Car</h2>
        <p class="page-subtitle">Register a vehicle to the fleet</p>
    </div>
    <a href="/admin/cars" class="btn btn-outline btn-icon"><i class="fas fa-arrow-left"></i> Back to Fleet</a>
</div>

<div class="form-card">
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

    <form method="POST" action="<?= BASE_URL ?>?page=admin-cars-store" enctype="multipart/form-data">
        <?php if (function_exists('csrf_field')) echo csrf_field(); ?>

        <div class="form-section-label">Vehicle Information</div>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Brand <span class="req">*</span></label>
                <input type="text" name="brand" class="form-input" placeholder="e.g. Toyota" value="<?= htmlspecialchars($old['brand'] ?? $_POST['brand'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Model <span class="req">*</span></label>
                <input type="text" name="model_name" class="form-input" placeholder="e.g. Vios" value="<?= htmlspecialchars($old['model_name'] ?? $_POST['model_name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Plate Number <span class="req">*</span></label>
                <input type="text" name="plate_number" class="form-input" placeholder="e.g. ABC 1234" value="<?= htmlspecialchars($old['plate_number'] ?? $_POST['plate_number'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Year <span class="req">*</span></label>
                <input type="number" name="year" class="form-input" placeholder="e.g. 2022" min="1990" max="2030" value="<?= htmlspecialchars($old['year'] ?? $_POST['year'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Color <span class="req">*</span></label>
                <input type="text" name="color" class="form-input" placeholder="e.g. Black" value="<?= htmlspecialchars($old['color'] ?? $_POST['color'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="available" <?= ($old['status'] ?? $_POST['status'] ?? 'available') === 'available' ? 'selected' : '' ?>>Available</option>
                    <option value="maintenance" <?= ($old['status'] ?? $_POST['status'] ?? '') === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                    <option value="rented" <?= ($old['status'] ?? $_POST['status'] ?? '') === 'rented' ? 'selected' : '' ?>>Rented</option>
                </select>
            </div>
        </div>
        </div>
        <div class="form-divider"></div>
        <div class="form-section-label">Car Image</div>
        <div class="form-group">
            <label class="upload-area" id="uploadArea">
                <input type="file" name="car_image" id="carImage" accept="image/*" style="display:none" onchange="previewImage(this)">
                <div id="uploadPlaceholder">
                    <i class="fas fa-cloud-upload-alt upload-icon"></i>
                    <p class="upload-text">Click to upload car image</p>
                    <p class="upload-hint">PNG, JPG, GIF, WEBP up to 5MB</p>
                </div>
                <img id="imagePreview" src="" alt="" style="display:none;max-height:180px;border-radius:8px;object-fit:contain;">
            </label>
        </div>
        <div class="form-actions">
            <a href="/admin/cars" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Add Car</button>
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
.form-divider { border:none; border-top:1px solid var(--border); margin:8px 0 24px; }
.form-actions { display:flex; justify-content:flex-end; gap:12px; margin-top:28px; }
.btn-primary { background:var(--red); color:var(--white); border:none; padding:11px 22px; border-radius:8px; font-size:.85rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase; cursor:pointer; display:inline-flex; align-items:center; gap:8px; text-decoration:none; transition:background .2s; }
.btn-primary:hover { background:#c0392b; }
.alert { border-radius:8px; padding:14px 18px; margin-bottom:20px; display:flex; align-items:flex-start; gap:10px; }
.alert-error { background:rgba(231,76,60,.12); border:1px solid rgba(231,76,60,.3); color:#e74c3c; }
.alert ul { margin:0; padding-left:16px; }
.upload-area { display:flex; align-items:center; justify-content:center; flex-direction:column; border:2px dashed var(--border); border-radius:10px; padding:40px; cursor:pointer; transition:border-color .2s; text-align:center; }
.upload-area:hover { border-color:var(--red); }
.upload-icon { font-size:2.5rem; color:var(--red); margin-bottom:12px; opacity:.7; }
.upload-text { color:var(--white); font-weight:600; margin:0 0 4px; }
.upload-hint { color:var(--silver); font-size:.8rem; margin:0; }
</style>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('imagePreview').src = e.target.result;
            document.getElementById('imagePreview').style.display = 'block';
            document.getElementById('uploadPlaceholder').style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin_layout.php';
?>