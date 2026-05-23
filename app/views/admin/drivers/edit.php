<?php
$pageTitle = 'Edit Driver';
$activePage = 'drivers';
ob_start();
?>

<div class="page-header">
    <div>
        <h1 class="page-title-main">Edit Driver</h1>
        <p class="page-subtitle">Driver ID #<?= $driver['driver_id'] ?></p>
    </div>
    <a href="<?= BASE_URL ?>?page=admin-drivers" class="btn-ghost">
        <i class="fas fa-arrow-left"></i> Back to Drivers
    </a>
</div>

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

<?php if (!empty($success)): ?>
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
</div>
<?php endif; ?>

<div class="form-card">
    <!-- Driver identity strip -->
    <div class="driver-identity">
        <div class="d-avatar"><?= strtoupper(substr($driver['drvr_fname'], 0, 1)) ?></div>
        <div>
            <div class="d-name">
                <?= htmlspecialchars(
                    $driver['drvr_fname'] . ' ' .
                    ($driver['drvr_mname'] ? $driver['drvr_mname'] . ' ' : '') .
                    $driver['drvr_lname']
                ) ?>
            </div>
            <div class="d-meta">
                License: <?= htmlspecialchars($driver['drvr_license_no']) ?>
                &nbsp;·&nbsp; Added: <?= date('M d, Y', strtotime($driver['driver_dateAdded'])) ?>
            </div>
        </div>
    </div>

    <form method="POST" action="<?= BASE_URL ?>?page=admin-drivers-update&id=<?= $driver['driver_id'] ?>">
        <input type="hidden" name="driver_id" value="<?= $driver['driver_id'] ?>">

        <div class="form-section-title">
            <i class="fas fa-user"></i> Personal Information
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">First Name <span class="req">*</span></label>
                <input type="text" name="drvr_fname" class="form-input"
                       value="<?= htmlspecialchars($old['drvr_fname'] ?? $driver['drvr_fname']) ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Middle Name</label>
                <input type="text" name="drvr_mname" class="form-input"
                       placeholder="(optional)"
                       value="<?= htmlspecialchars($old['drvr_mname'] ?? $driver['drvr_mname'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Last Name <span class="req">*</span></label>
                <input type="text" name="drvr_lname" class="form-input"
                       value="<?= htmlspecialchars($old['drvr_lname'] ?? $driver['drvr_lname']) ?>" required>
            </div>
        </div>

        <div class="form-row" style="max-width:340px">
            <div class="form-group">
                <label class="form-label">Phone Number <span class="req">*</span></label>
                <div class="icon-wrap">
                    <i class="fas fa-phone fi"></i>
                    <input type="text" name="drvr_phone_number" class="form-input fi-pad"
                           placeholder="09XXXXXXXXX"
                           value="<?= htmlspecialchars($old['drvr_phone_number'] ?? $driver['drvr_phone_number']) ?>" required>
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
                           value="<?= htmlspecialchars($old['drvr_license_no'] ?? $driver['drvr_license_no']) ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Daily Rate (₱) <span class="req">*</span></label>
                <div class="icon-wrap">
                    <i class="fas fa-coins fi"></i>
                    <input type="number" name="rate_per_day" class="form-input fi-pad"
                           min="0" step="0.01"
                           value="<?= htmlspecialchars($old['rate_per_day'] ?? $driver['rate_per_day']) ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="driver_status" class="form-input">
                    <?php
                    $cur  = $old['driver_status'] ?? $driver['driver_status'];
                    $opts = ['available' => 'Available', 'on_trip' => 'On Trip', 'inactive' => 'Inactive'];
                    foreach ($opts as $val => $label):
                    ?>
                    <option value="<?= $val ?>" <?= $cur === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <a href="<?= BASE_URL ?>?page=admin-drivers" class="btn-ghost">Cancel</a>
            <button type="submit" class="btn-primary">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </div>
    </form>
</div>

<style>
.page-header { display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:24px; }
.page-title-main { font-size:1.6rem; font-weight:700; color:var(--white); letter-spacing:.04em; margin:0; }
.page-subtitle { color:var(--silver); font-size:.8rem; margin:4px 0 0; letter-spacing:.12em; text-transform:uppercase; }

.alert { display:flex; align-items:flex-start; gap:10px; padding:14px 18px; border-radius:10px; margin-bottom:20px; font-size:.875rem; }
.alert-error   { background:rgba(192,57,43,.15); border:1px solid rgba(192,57,43,.4); color:#e74c3c; }
.alert-success { background:rgba(39,174,96,.12); border:1px solid rgba(39,174,96,.3); color:#2ecc71; }

.form-card { background:var(--card-bg); border:1px solid var(--border); border-radius:14px; padding:32px; max-width:860px; }

.driver-identity { display:flex; align-items:center; gap:16px; margin-bottom:28px; padding-bottom:24px; border-bottom:1px solid var(--border); }
.d-avatar { width:52px; height:52px; border-radius:50%; background:var(--red); color:var(--white); display:flex; align-items:center; justify-content:center; font-weight:800; font-size:1.3rem; flex-shrink:0; }
.d-name { font-size:1.1rem; font-weight:700; color:var(--white); }
.d-meta { font-size:.8rem; color:var(--silver); margin-top:3px; }

.form-section-title { display:flex; align-items:center; gap:10px; color:var(--red); font-size:.78rem; font-weight:700; letter-spacing:.14em; text-transform:uppercase; margin-bottom:18px; padding-bottom:10px; border-bottom:1px solid var(--border); }

.form-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:18px; margin-bottom:18px; }
.form-group { display:flex; flex-direction:column; gap:8px; }
.form-label { font-size:.82rem; font-weight:600; color:var(--silver); letter-spacing:.06em; text-transform:uppercase; }
.req { color:var(--red); }

.form-input { background:var(--bg); border:1px solid var(--border); border-radius:8px; color:var(--white); padding:10px 14px; font-size:.9rem; transition:border-color .2s,box-shadow .2s; width:100%; box-sizing:border-box; }
.form-input:focus { outline:none; border-color:var(--red); box-shadow:0 0 0 3px rgba(192,57,43,.15); }
.form-input::placeholder { color:rgba(255,255,255,.25); }

.icon-wrap { position:relative; }
.fi { position:absolute; left:13px; top:50%; transform:translateY(-50%); color:var(--silver); font-size:.8rem; pointer-events:none; }
.fi-pad { padding-left:36px !important; }
select.form-input option { background:#1a1a2e; }

.form-actions { display:flex; justify-content:flex-end; gap:12px; margin-top:32px; padding-top:24px; border-top:1px solid var(--border); }
.btn-primary { background:var(--red); color:var(--white); border:none; padding:11px 22px; border-radius:8px; font-size:.875rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase; cursor:pointer; display:inline-flex; align-items:center; gap:8px; text-decoration:none; transition:background .2s; }
.btn-primary:hover { background:#c0392b; }
.btn-ghost { background:transparent; color:var(--silver); border:1px solid var(--border); padding:10px 18px; border-radius:8px; font-size:.875rem; cursor:pointer; text-decoration:none; transition:all .2s; display:inline-flex; align-items:center; gap:8px; }
.btn-ghost:hover { color:var(--white); border-color:rgba(255,255,255,.3); }
</style>

<?php
$content = ob_get_clean();
include BASE_PATH . '/app/views/layouts/admin_layout.php';
?>