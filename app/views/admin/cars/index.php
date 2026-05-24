<?php
$pageTitle  = 'Manage Cars';
$activePage = 'admin-cars';
?>

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
                <option value="Rented"    <?= ($_GET['status'] ?? '') === 'Rented'    ? 'selected' : '' ?>>Rented</option>
                <option value="Maintenance" <?= ($_GET['status'] ?? '') === 'Maintenance' ? 'selected' : '' ?>>Maintenance</option>
            </select>
        </div>
        <button type="submit" class="btn btn-outline">Filter</button>
        <a href="<?= BASE_URL ?>?page=admin-cars" class="btn btn-ghost">Reset</a>
    </form>
</div>

<!-- CARS TABLE -->
<div class="card">
    <div class="card-header">
        <span class="card-title">All Vehicles</span>
        <span class="record-count"><?= count($cars ?? []) ?> Records</span>
    </div>
    <table>
        <thead>
            <tr>
                <th>Car ID</th>
                <th>Model</th>
                <th>Brand</th>
                <th>Plate No.</th>
                <th>Year</th>
                <th>Seats</th>
                <th>Transmission</th>
                <th>Fuel Type</th>
                <th>Engine</th>
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
                <td><?= $c['year'] ?></td>
                <td><?= $c['seats'] ?? '-' ?></td>
                <td><?= $c['transmission'] ?? '-' ?></td>
                <td><?= $c['fuel_type'] ?? '-' ?></td>
                <td><?= $c['engine'] ?? '-' ?></td>
                <td>₱<?= number_format($c['daily_rate'] ?? 0, 2) ?></td>
                <td><span class="badge badge-<?= strtolower($c['status']) ?>"><?= $c['status'] ?></span></td>
                <td>
                    <div class="action-btns">
                        <a href="<?= BASE_URL ?>?page=admin-cars-edit&id=<?= $c['car_id'] ?>" class="btn-action btn-edit" title="Edit"><i class="fas fa-pen"></i></a>
                        <form method="POST" action="<?= BASE_URL ?>?page=admin-cars-delete" onsubmit="return confirm('Delete this car?')" style="display:inline">
                            <input type="hidden" name="car_id" value="<?= $c['car_id'] ?>">
                            <button type="submit" class="btn-action btn-delete" title="Delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="12" class="empty-row">
                    <i class="fas fa-car" style="font-size:2rem;margin-bottom:8px;display:block;opacity:.3"></i>
                    No cars in fleet.
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
.filter-bar { background:var(--black-card); border:1px solid var(--border); border-radius:8px; padding:14px 20px; margin-bottom:18px; }
.filter-form { display:flex; gap:12px; align-items:center; flex-wrap:wrap; }
.filter-group { position:relative; display:flex; align-items:center; }
.filter-icon { position:absolute; left:12px; color:var(--silver); font-size:.8rem; }
.filter-input { background:var(--black); border:1px solid var(--border); border-radius:6px; color:var(--white); padding:9px 14px 9px 34px; font-size:.85rem; min-width:260px; font-family:'Barlow',sans-serif; }
.filter-input:focus { outline:none; border-color:var(--red); }
.filter-select { background:var(--black); border:1px solid var(--border); border-radius:6px; color:var(--white); padding:9px 14px; font-size:.85rem; font-family:'Barlow',sans-serif; cursor:pointer; }
.filter-select:focus { outline:none; border-color:var(--red); }
.filter-select option { background:var(--black-card); }
.record-count { color:var(--silver); font-size:.8rem; letter-spacing:.08em; text-transform:uppercase; }
.id-badge { color:var(--red); font-weight:600; }
.plate-code { background:rgba(255,255,255,.07); padding:3px 8px; border-radius:4px; font-family:monospace; font-size:.85rem; color:var(--white); }
.action-btns { display:flex; gap:6px; align-items:center; }
.btn-action { display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:6px; border:none; cursor:pointer; font-size:.8rem; transition:all .2s; }
.btn-edit { background:rgba(255,255,255,.07); color:var(--silver); text-decoration:none; }
.btn-edit:hover { background:var(--red); color:var(--white); }
.btn-delete { background:rgba(255,255,255,.07); color:var(--silver); }
.btn-delete:hover { background:#c0392b; color:var(--white); }
.empty-row { text-align:center; color:var(--silver); padding:48px !important; }
.btn-ghost { background:transparent; color:var(--silver); border:1px solid var(--border); padding:9px 14px; border-radius:6px; font-size:.85rem; cursor:pointer; text-decoration:none; transition:all .2s; font-family:'Barlow',sans-serif; }
.btn-ghost:hover { color:var(--white); border-color:var(--silver); }
</style>