<?php
$pageTitle  = 'Add Driver';
$activePage = 'drivers';
?>

<!-- PAGE HEADER -->
<div class="page-header">
    <div>
        <h1 class="page-title-main">Add Driver</h1>
        <p class="page-subtitle">Register a new driver to the system</p>
    </div>
    <a href="<?= BASE_URL ?>/admin/drivers" class="btn-ghost">
        <i class="fas fa-arrow-left"></i> Back to Drivers
    </a>
</div>

<!-- VALIDATION ERRORS -->
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
    <form method="POST" action="<?= BASE_URL ?>/admin/drivers/store">

        <div class="form-section-title">
            <i class="fas fa-user"></i> Personal Information
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">First Name <span class="req">*</span></label>
                <input type="text" name="drvr_fname" class="form-input"
                       placeholder="e.g. Juan"
                       value="<?= htmlspecialchars($old['drvr_fname'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Middle Name</label>
                <input type="text" name="drvr_mname" class="form-input"
                       placeholder="(optional)"
                       value="<?= htmlspecialchars($old['drvr_mname'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Last Name <span class="req">*</span></label>
                <input type="text" name="drvr_lname" class="form-input"
                       placeholder="e.g. Dela Cruz"
                       value="<?= htmlspecialchars($old['drvr_lname'] ?? '') ?>" required>
            </div>
        </div>

        <div class="form-row" style="max-width:340px">
            <div class="form-group">
                <label class="form-label">Phone Number <span class="req">*</span></label>
                <div class="icon-wrap">
                    <i class="fas fa-phone fi"></i>
                    <input type="text" name="drvr_phone_number" class="form-input fi-pad"
                           placeholder="09XXXXXXXXX"
                           value="<?= htmlspecialchars($old['drvr_phone_number'] ?? '') ?>" required>
                </div>
            </div>
        </div>

        <div class="form-section-title" style="margin-top:28px">
            <i class="fas fa-id-card"></i> License &amp; Rate
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">License No. <span class="req">*</span></label>
                <div class="icon-wrap">
                    <i class="fas fa-fingerprint fi"></i>
                    <input type="text" name="drvr_license_no" class="form-input fi-pad"
                           placeholder="e.g. N01-23-456789"
                           value="<?= htmlspecialchars($old['drvr_license_no'] ?? '') ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Daily Rate (₱) <span class="req">*</span></label>
                <div class="icon-wrap">
                    <i class="fas fa-coins fi"></i>
                    <input type="number" name="rate_per_day" class="form-input fi-pad"
                           placeholder="e.g. 500" min="0" step="0.01"
                           value="<?= htmlspecialchars($old['rate_per_day'] ?? '') ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="driver_status" class="form-input">
                    <?php
                    $cur  = $old['driver_status'] ?? 'available';
                    $opts = ['available' => 'Available', 'on_trip' => 'On Trip', 'inactive' => 'Inactive'];
                    foreach ($opts as $val => $label):
                    ?>
                    <option value="<?= $val ?>" <?= $cur === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <a href="<?= BASE_URL ?>/admin/drivers" class="btn-ghost">Cancel</a>
            <button type="submit" class="btn-primary">
                <i class="fas fa-user-plus"></i> Register Driver
            </button>
        </div>

    </form>
</div>