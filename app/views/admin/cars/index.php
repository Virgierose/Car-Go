<?php
$pageTitle = 'Manage Cars';
$activePage = 'cars';
ob_start();
?>

<div class="red-rule"></div>

<!-- PAGE HEADER -->
<div class="page-header">
    <div>
        <h2 class="page-title-main">Manage Cars</h2>
        <p class="page-subtitle">Fleet vehicle inventory</p>
    </div>
    <div class="header-actions">
        <a href="<?= BASE_URL ?>?page=admin-cars-create" class="btn btn-primary btn-icon"><i class="fas fa-plus"></i> Add New Car</a>
    </div>
</div>

<!-- FILTER BAR -->
<div class="filter-bar">
    <form method="GET" action="<?= BASE_URL ?>?page=admin-cars" class="filter-form">
        <div class="filter-group">
            <i class="fas fa-search filter-icon"></i>
            <input type="text" name="search" class="filter-input" placeholder="Search by model, brand, plate..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </div>
        <div class="filter-group">
            <select name="status" class="filter-select">
                <option value="">All Status</option>
                <option value="Available" <?= ($_GET['status'] ?? '') === 'Available' ? 'selected' : '' ?>>Available</option>
                <option value="Rented" <?= ($_GET['status'] ?? '') === 'Rented' ? 'selected' : '' ?>>Rented</option>
                <option value="Maintenance" <?= ($_GET['status'] ?? '') === 'Maintenance' ? 'selected' : '' ?>>Maintenance</option>
            </select>
        </div>
        <button type="submit" class="btn btn-outline">Filter</button>
        <a href="/admin/cars" class="btn btn-ghost">Reset</a>
    </form>
</div>

<!-- CARS TABLE -->
<div class="card">
    <div class="card-header">
        <span class="card-title">All Vehicles</span>
        <span class="record-count"><?= count($cars ?? []) ?> records</span>
    </div>
    <table>
        <thead>
            <tr>
                <th>Car ID</th>
                <th>Model</th>
                <th>Brand</th>
                <th>Plate No.</th>
                <th>Color</th>
                <th>Year</th>
                <th>Daily Rate</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!empty($cars)): ?>
            <?php foreach ($cars as $c): ?>
            <tr>
                <td><span class="id-badge">#<?= $c['car_id'] ?></span></td>
                <td><?= htmlspecialchars($c['model_name']) ?></td>
                <td><?= htmlspecialchars($c['brand']) ?></td>
                <td><code class="plate-code"><?= htmlspecialchars($c['plate_number']) ?></code></td>
                <td>
                    <span class="color-dot" style="background:<?= strtolower($c['color']) ?>"></span>
                    <?= htmlspecialchars($c['color']) ?>
                </td>
                <td><?= $c['year'] ?></td>
                <td>₱<?= number_format($c['daily_rate'] ?? 0, 2) ?></td>
                <td><span class="badge badge-<?= strtolower($c['status']) ?>"><?= $c['status'] ?></span></td>
                <td>
                    <div class="action-btns">
                        <a href="<?= BASE_URL ?>?page=admin-cars-edit&id=<?= $c['car_id'] ?>" class="btn-action btn-edit" title="Edit"><i class="fas fa-pen"></i></a>
                        <form method="POST" action="<?= BASE_URL ?>?page=admin-cars-delete" onsubmit="return confirm('Delete this car?')">
                            <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn-action btn-delete" title="Delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="9" class="empty-row"><i class="fas fa-car" style="font-size:2rem;margin-bottom:8px;display:block;opacity:.3"></i>No cars in fleet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
.page-header { display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:24px; }
.page-title-main { font-size:1.6rem; font-weight:700; color:var(--white); letter-spacing:.04em; margin:0; }
.page-subtitle { color:var(--silver); font-size:.8rem; margin:4px 0 0; letter-spacing:.12em; text-transform:uppercase; }
.header-actions { display:flex; gap:10px; }
.filter-bar { background:var(--card-bg); border:1px solid var(--border); border-radius:10px; padding:16px 20px; margin-bottom:20px; }
.filter-form { display:flex; gap:12px; align-items:center; flex-wrap:wrap; }
.filter-group { position:relative; display:flex; align-items:center; }
.filter-icon { position:absolute; left:12px; color:var(--silver); font-size:.8rem; }
.filter-input { background:var(--bg); border:1px solid var(--border); border-radius:7px; color:var(--white); padding:9px 14px 9px 34px; font-size:.85rem; min-width:260px; }
.filter-input:focus { outline:none; border-color:var(--red); }
.filter-select { background:var(--bg); border:1px solid var(--border); border-radius:7px; color:var(--white); padding:9px 14px; font-size:.85rem; }
.filter-select:focus { outline:none; border-color:var(--red); }
.record-count { color:var(--silver); font-size:.8rem; letter-spacing:.08em; text-transform:uppercase; }
.id-badge { color:var(--red); font-weight:600; }
.plate-code { background:rgba(255,255,255,.07); padding:3px 8px; border-radius:4px; font-family:monospace; font-size:.85rem; color:var(--white); }
.color-dot { display:inline-block; width:10px; height:10px; border-radius:50%; margin-right:6px; border:1px solid rgba(255,255,255,.2); vertical-align:middle; }
.action-btns { display:flex; gap:6px; }
.btn-action { display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:6px; border:none; cursor:pointer; font-size:.8rem; transition:all .2s; }
.btn-edit { background:rgba(255,255,255,.07); color:var(--silver); }
.btn-edit:hover { background:var(--red); color:var(--white); }
.btn-delete { background:rgba(255,255,255,.07); color:var(--silver); }
.btn-delete:hover { background:#c0392b; color:var(--white); }
.empty-row { text-align:center; color:var(--silver); padding:48px !important; }
.btn-primary { background:var(--red); color:var(--white); border:none; padding:10px 18px; border-radius:8px; font-size:.85rem; font-weight:600; letter-spacing:.06em; text-transform:uppercase; cursor:pointer; display:inline-flex; align-items:center; gap:8px; text-decoration:none; transition:background .2s; }
.btn-primary:hover { background:#c0392b; }
.btn-ghost { background:transparent; color:var(--silver); border:1px solid transparent; padding:9px 14px; border-radius:7px; font-size:.85rem; cursor:pointer; text-decoration:none; transition:all .2s; }
.btn-ghost:hover { color:var(--white); border-color:var(--border); }
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin_layout.php';
?>