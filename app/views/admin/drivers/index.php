<?php
$pageTitle  = 'Drivers';
$activePage = 'admin-drivers';
?>

<link rel="stylesheet" href="<?= ASSET_URL ?>assets/css/admin-drivers.css">

<div class="page-header">
    <div>
        <h1 class="page-title-main">Drivers</h1>
        <p class="page-subtitle">Manage your driver registry</p>
    </div>
    <a href="<?= BASE_URL ?>?page=admin-drivers-create" class="btn-primary">
        <i class="fas fa-user-plus"></i> Add Driver
    </a>
</div>

<div class="filter-bar">
    <form method="GET" action="<?= BASE_URL ?>" class="filter-form">
        <input type="hidden" name="page" value="admin-drivers">
        <div class="filter-group">
            <i class="fas fa-search filter-icon"></i>
            <input type="text" name="search" class="filter-input"
                   placeholder="Search name, license, contact..."
                   value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </div>
        <div class="filter-group">
            <select name="status" class="filter-select">
                <option value="">All Status</option>
                <option value="available" <?= ($_GET['status'] ?? '') === 'available' ? 'selected' : '' ?>>Available</option>
                <option value="on_trip"   <?= ($_GET['status'] ?? '') === 'on_trip'   ? 'selected' : '' ?>>On Trip</option>
                <option value="inactive"  <?= ($_GET['status'] ?? '') === 'inactive'  ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>
        <button type="submit" class="btn btn-outline">Filter</button>
        <a href="<?= BASE_URL ?>?page=admin-drivers" class="btn btn-ghost">Reset</a>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">Driver Registry</span>
        <span class="record-count"><?= count($drivers ?? []) ?> Records</span>
    </div>
    <table>
        <thead>
            <tr>
                <th>ID</th><th>Name</th><th>Contact</th><th>License No.</th><th>Daily Rate</th><th>Status</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!empty($drivers)): ?>
            <?php foreach ($drivers as $d): ?>
            <?php
                $statusMap  = ['available' => 'available', 'on_trip' => 'rented', 'inactive' => 'maintenance'];
                $labelMap   = ['available' => 'Available', 'on_trip' => 'On Trip', 'inactive' => 'Inactive'];
                $rawStatus  = $d['driver_status'] ?? 'available';
                $badgeClass = $statusMap[$rawStatus] ?? 'available';
                $badgeLabel = $labelMap[$rawStatus]  ?? ucfirst($rawStatus);
            ?>
            <tr>
                <td><span class="id-badge">#<?= $d['driver_id'] ?></span></td>
                <td>
                    <div class="name-cell">
                        <div class="avatar"><?= strtoupper(substr($d['drvr_fname'], 0, 1)) ?></div>
                        <div>
                            <div class="name-main"><?= htmlspecialchars($d['drvr_fname'] . ' ' . $d['drvr_lname']) ?></div>
                            <?php if (!empty($d['drvr_mname'])): ?>
                            <div class="name-sub"><?= htmlspecialchars($d['drvr_mname']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </td>
                <td><?= htmlspecialchars($d['drvr_phone_number'] ?? '—') ?></td>
                <td><code class="plate-code"><?= htmlspecialchars($d['drvr_license_no'] ?? '—') ?></code></td>
                <td>₱<?= number_format($d['rate_per_day'] ?? 0, 2) ?></td>
                <td><span class="badge badge-<?= $badgeClass ?>"><?= $badgeLabel ?></span></td>
                <td>
                    <div class="action-btns">
                        <a href="<?= BASE_URL ?>?page=admin-drivers-edit&id=<?= $d['driver_id'] ?>" class="btn-action btn-edit" title="Edit"><i class="fas fa-pen"></i></a>
                        <form method="POST" action="<?= BASE_URL ?>?page=admin-drivers-delete" onsubmit="return confirm('Remove this driver?')" style="display:inline">
                            <input type="hidden" name="id" value="<?= $d['driver_id'] ?>">
                            <button type="submit" class="btn-action btn-delete" title="Delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="empty-row">
                    <i class="fas fa-id-card" style="font-size:2rem;margin-bottom:8px;display:block;opacity:.3"></i>
                    No drivers registered yet.
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
.page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
.page-title-main { font-size:1.4rem; font-weight:700; color:var(--white); margin:0; }
.page-subtitle { color:var(--silver); font-size:.8rem; margin:4px 0 0; letter-spacing:.1em; text-transform:uppercase; }
.filter-bar { background:var(--black-card); border:1px solid var(--border); border-radius:8px; padding:14px 20px; margin-bottom:18px; }
.filter-form { display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
.filter-group { position:relative; display:flex; align-items:center; }
.filter-icon { position:absolute; left:12px; color:var(--silver); font-size:.8rem; }
.filter-input { background:var(--black); border:1px solid var(--border); border-radius:6px; color:var(--white); padding:9px 14px 9px 34px; font-size:.85rem; min-width:240px; font-family:'Barlow',sans-serif; }
.filter-input:focus { outline:none; border-color:var(--red); }
.filter-select { background:var(--black); border:1px solid var(--border); border-radius:6px; color:var(--white); padding:9px 14px; font-size:.85rem; font-family:'Barlow',sans-serif; cursor:pointer; }
.filter-select:focus { outline:none; border-color:var(--red); }
.filter-select option { background:var(--black-card); }
.record-count { color:var(--silver); font-size:.8rem; letter-spacing:.08em; text-transform:uppercase; }
.id-badge { color:var(--red); font-weight:600; }
.name-cell { display:flex; align-items:center; gap:10px; }
.avatar { width:32px; height:32px; border-radius:50%; background:var(--red-dark); color:var(--white); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:.85rem; flex-shrink:0; }
.name-main { color:var(--white); font-weight:600; font-size:.875rem; }
.name-sub { color:var(--silver); font-size:.75rem; }
.plate-code { background:rgba(255,255,255,.07); padding:3px 8px; border-radius:4px; font-family:monospace; font-size:.85rem; color:var(--white); }
.action-btns { display:flex; gap:6px; align-items:center; }
.btn-action { display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:6px; border:none; cursor:pointer; font-size:.8rem; transition:all .2s; text-decoration:none; }
.btn-edit { background:rgba(255,255,255,.07); color:var(--silver); }
.btn-edit:hover { background:var(--red); color:var(--white); }
.btn-delete { background:rgba(255,255,255,.07); color:var(--silver); }
.btn-delete:hover { background:#c0392b; color:var(--white); }
.empty-row { text-align:center; color:var(--silver); padding:48px !important; }
.btn-primary { background:var(--red); color:var(--white); border:none; padding:10px 18px; border-radius:6px; font-size:.85rem; font-weight:600; letter-spacing:.06em; text-transform:uppercase; cursor:pointer; display:inline-flex; align-items:center; gap:8px; text-decoration:none; transition:background .2s; font-family:'Barlow',sans-serif; }
.btn-primary:hover { background:#a00816; }
.btn-ghost { background:transparent; color:var(--silver); border:1px solid var(--border); padding:9px 14px; border-radius:6px; font-size:.85rem; cursor:pointer; text-decoration:none; transition:all .2s; font-family:'Barlow',sans-serif; }
.btn-ghost:hover { color:var(--white); border-color:var(--silver); }
</style>