<?php
$pageTitle = 'Drivers';
$activePage = 'drivers';
ob_start();
?>

<!-- FILTER BAR -->
<div class="filter-bar">
    <form method="GET" action="<?= BASE_URL ?>?page=admin-drivers" class="filter-form">
        <div class="filter-group">
            <i class="fas fa-search filter-icon"></i>
            <input type="text" name="search" class="filter-input" placeholder="Search name, license, contact..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </div>
        <div class="filter-group">
            <select name="status" class="filter-select">
                <option value="">All Status</option>
                <option value="Active" <?= ($_GET['status'] ?? '') === 'Active' ? 'selected' : '' ?>>Active</option>
                <option value="Inactive" <?= ($_GET['status'] ?? '') === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>
        <button type="submit" class="btn btn-outline">Filter</button>
        <a href="/admin/drivers" class="btn btn-ghost">Reset</a>
    </form>
</div>

<!-- DRIVERS TABLE -->
<div class="card">
    <div class="card-header">
        <span class="card-title">Driver Registry</span>
        <span class="record-count"><?= count($drivers ?? []) ?> records</span>
    </div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Contact</th>
                <th>License No.</th>
                <th>License Expiry</th>
                <th>Daily Rate</th>
                <th>Assigned Car</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!empty($drivers)): ?>
            <?php foreach ($drivers as $d): ?>
            <tr>
                <td><span class="id-badge">#<?= $d['driver_id'] ?></span></td>
                <td>
                    <div class="name-cell">
                        <div class="avatar"><?= strtoupper(substr($d['drvr_fname'], 0, 1)) ?></div>
                        <div>
                            <div class="name-main"><?= htmlspecialchars($d['drvr_fname'] . ' ' . $d['drvr_lname']) ?></div>
                            <div class="name-sub"><?= htmlspecialchars($d['email'] ?? '') ?></div>
                        </div>
                    </div>
                </td>
                <td><?= htmlspecialchars($d['contact'] ?? '—') ?></td>
                <td><code class="plate-code"><?= htmlspecialchars($d['license_no'] ?? '—') ?></code></td>
                <td><?= $d['license_expiry'] ?? '—' ?></td>
                <td>₱<?= number_format($d['daily_rate'] ?? 0, 2) ?></td>
                <td><?= htmlspecialchars($d['assigned_car'] ?? 'Unassigned') ?></td>
                <td><span class="badge badge-<?= strtolower($d['status'] ?? 'active') ?>"><?= $d['status'] ?? 'Active' ?></span></td>
                <td>
                    <div class="action-btns">
                        <a href="<?= BASE_URL ?>?page=admin-drivers-edit&id=<?= $d['driver_id'] ?>" class="btn-action btn-edit" title="Edit"><i class="fas fa-pen"></i></a>
                        <form method="POST" action="<?= BASE_URL ?>?page=admin-drivers-delete" onsubmit="return confirm('Remove this driver?')">
                            <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn-action btn-delete" title="Delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="9" class="empty-row"><i class="fas fa-id-card" style="font-size:2rem;margin-bottom:8px;display:block;opacity:.3"></i>No drivers registered.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- ADD/EDIT DRIVER MODAL (inline form card) -->
<!-- Alternatively link to a create page; this uses the same pattern -->

<style>
.page-header { display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:24px; }
.page-title-main { font-size:1.6rem; font-weight:700; color:var(--white); letter-spacing:.04em; margin:0; }
.page-subtitle { color:var(--silver); font-size:.8rem; margin:4px 0 0; letter-spacing:.12em; text-transform:uppercase; }
.filter-bar { background:var(--card-bg); border:1px solid var(--border); border-radius:10px; padding:14px 20px; margin-bottom:18px; }
.filter-form { display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
.filter-group { position:relative; display:flex; align-items:center; }
.filter-icon { position:absolute; left:12px; color:var(--silver); font-size:.8rem; }
.filter-input { background:var(--bg); border:1px solid var(--border); border-radius:7px; color:var(--white); padding:9px 14px 9px 34px; font-size:.85rem; min-width:280px; }
.filter-input:focus { outline:none; border-color:var(--red); }
.filter-select { background:var(--black-card); border:1px solid var(--border); border-radius:7px; color:var(--white); padding:9px 14px; font-size:.85rem; }
.record-count { color:var(--silver); font-size:.8rem; letter-spacing:.08em; text-transform:uppercase; }
.id-badge { color:var(--red); font-weight:600; }
.name-cell { display:flex; align-items:center; gap:12px; }
.avatar { width:34px; height:34px; border-radius:50%; background:var(--red); color:var(--white); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:.85rem; flex-shrink:0; }
.name-main { font-weight:600; color:var(--white); }
.name-sub { font-size:.78rem; color:var(--silver); }
.plate-code { background:rgba(255,255,255,.07); padding:3px 8px; border-radius:4px; font-family:monospace; font-size:.85rem; color:var(--white); }
.action-btns { display:flex; gap:6px; }
.btn-action { display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:6px; border:none; cursor:pointer; font-size:.8rem; transition:all .2s; text-decoration:none; }
.btn-edit { background:rgba(255,255,255,.07); color:var(--silver); }
.btn-edit:hover { background:var(--red); color:var(--white); }
.btn-delete { background:rgba(255,255,255,.07); color:var(--silver); }
.btn-delete:hover { background:#c0392b; color:var(--white); }
.empty-row { text-align:center; color:var(--silver); padding:48px !important; }
.btn-primary { background:var(--red); color:var(--white); border:none; padding:10px 18px; border-radius:8px; font-size:.85rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase; cursor:pointer; display:inline-flex; align-items:center; gap:8px; text-decoration:none; transition:background .2s; }
.btn-primary:hover { background:#c0392b; }
.btn-ghost { background:transparent; color:var(--silver); border:1px solid transparent; padding:9px 14px; border-radius:7px; font-size:.85rem; cursor:pointer; text-decoration:none; transition:all .2s; }
.btn-ghost:hover { color:var(--white); border-color:var(--border); }
</style>