<?php
$pageTitle  = 'Manage Cars';
$activePage = 'admin-cars';
?>

<link rel="stylesheet" href="<?= ASSET_URL ?>assets/css/admin-drivers.css">
<style>
/* ── Manage Cars — scoped additions ──────────────────────── */

/* Filter bar matches driver list style */
.filter-bar {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 14px 20px;
    margin-bottom: 18px;
}
.filter-form {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}
.filter-group {
    position: relative;
    display: flex;
    align-items: center;
    flex: 1;
    min-width: 200px;
}
.filter-group.filter-group--select {
    flex: 0 0 auto;
    min-width: 0;
}
.filter-icon {
    position: absolute;
    left: 12px;
    color: var(--silver);
    font-size: .8rem;
    pointer-events: none;
}
.filter-input {
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 7px;
    color: var(--white);
    padding: 9px 14px 9px 34px;
    font-size: .85rem;
    width: 100%;
    box-sizing: border-box;
    font-family: 'Barlow', sans-serif;
    transition: border-color .2s;
}
.filter-input:focus {
    outline: none;
    border-color: var(--red);
}
.filter-input::placeholder { color: var(--silver); opacity: .6; }
.filter-select {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 7px;
    color: var(--white);
    padding: 9px 14px;
    font-size: .85rem;
    font-family: 'Barlow', sans-serif;
    cursor: pointer;
    -webkit-appearance: none;
    appearance: none;
    transition: border-color .2s;
}
.filter-select:focus {
    outline: none;
    border-color: var(--red);
}
.filter-select option { background: var(--card-bg); }
.filter-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}
.btn-reset {
    background: none;
    border: none;
    color: var(--silver);
    font-size: .82rem;
    font-weight: 600;
    cursor: pointer;
    font-family: 'Barlow', sans-serif;
    text-decoration: none;
    padding: 9px 4px;
    transition: color .2s;
}
.btn-reset:hover { color: var(--white); }

/* Table card */
.table-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    overflow: hidden;
}
.table-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 24px;
    border-bottom: 1px solid var(--border);
    flex-wrap: wrap;
    gap: 8px;
}
.table-card-title {
    font-size: .8rem;
    font-weight: 700;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: var(--white);
}
.table-wrap {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
table {
    width: 100%;
    border-collapse: collapse;
    min-width: 700px;
}
thead th {
    padding: 11px 16px;
    text-align: left;
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: var(--silver);
    border-bottom: 1px solid var(--border);
    white-space: nowrap;
    background: rgba(255,255,255,.02);
}
tbody tr {
    border-bottom: 1px solid var(--border);
    transition: background .15s;
}
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: rgba(255,255,255,.03); }
tbody td {
    padding: 13px 16px;
    font-size: .875rem;
    color: var(--white);
    vertical-align: middle;
}
.empty-row {
    text-align: center;
    color: var(--silver);
    padding: 52px 24px !important;
}

/* Badges */
.badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 4px;
    font-size: .68rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
    white-space: nowrap;
}
.badge-available   { background: rgba(39,174,96,.15);  border: 1px solid rgba(39,174,96,.3);  color: #2ecc71; }
.badge-rented      { background: rgba(241,196,15,.15); border: 1px solid rgba(241,196,15,.3); color: #f1c40f; }
.badge-maintenance { background: rgba(192,57,43,.15);  border: 1px solid rgba(192,57,43,.3);  color: #e74c3c; }

/* Plate code */
.plate-code {
    background: rgba(255,255,255,.07);
    padding: 3px 8px;
    border-radius: 4px;
    font-family: monospace;
    font-size: .82rem;
    border: 1px solid var(--border);
    white-space: nowrap;
}

/* ── Mobile: card-list replaces table on small screens ───── */
.car-cards { display: none; }

@media (max-width: 768px) {
    /* Header stacks */
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    .page-header .btn-primary { width: 100%; justify-content: center; }

    /* Filter stacks */
    .filter-form { flex-direction: column; align-items: stretch; }
    .filter-group,
    .filter-group.filter-group--select { flex: unset; width: 100%; min-width: 0; }
    .filter-select { width: 100%; }
    .filter-actions { justify-content: flex-end; }
    .filter-actions .btn-primary { flex: 1; justify-content: center; }

    /* Hide table, show cards */
    .table-wrap table { display: none; }
    .car-cards { display: block; }

    .car-card {
        padding: 14px 16px;
        border-bottom: 1px solid var(--border);
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 6px 12px;
        align-items: start;
    }
    .car-card:last-child { border-bottom: none; }
    .car-card-main { grid-column: 1; }
    .car-card-actions { grid-column: 2; grid-row: 1 / 3; align-self: center; }

    .car-card-name {
        font-weight: 700;
        color: var(--white);
        font-size: .95rem;
        margin-bottom: 2px;
    }
    .car-card-meta {
        font-size: .78rem;
        color: var(--silver);
        display: flex;
        flex-wrap: wrap;
        gap: 4px 10px;
        margin-top: 4px;
    }
    .car-card-meta span { white-space: nowrap; }
    .car-card-bottom {
        grid-column: 1;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 4px;
    }
    .car-card-rate {
        font-weight: 700;
        color: var(--white);
        font-size: .88rem;
    }
    .table-card-header { padding: 14px 16px; }
}

@media (max-width: 420px) {
    .filter-bar { padding: 12px; }
    .car-card { padding: 12px 14px; }
}
</style>

<!-- PAGE HEADER -->
<div class="page-header">
    <div>
        <h1 class="page-title-main">Manage Cars</h1>
        <p class="page-subtitle">Fleet vehicle management</p>
    </div>
    <a href="<?= BASE_URL ?>?page=admin-cars-create" class="btn-primary">
        <i class="fas fa-plus"></i> Add New Car
    </a>
</div>

<!-- FILTER BAR -->
<div class="filter-bar">
    <form method="GET" action="<?= BASE_URL ?>?page=admin-cars" class="filter-form">
        <div class="filter-group">
            <i class="fas fa-search filter-icon"></i>
            <input type="text" name="search" class="filter-input"
                   placeholder="Search by model, brand, plate..."
                   value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </div>
        <div class="filter-group filter-group--select">
            <select name="status" class="filter-select">
                <option value="">All Status</option>
                <option value="available"   <?= ($_GET['status'] ?? '') === 'available'   ? 'selected' : '' ?>>Available</option>
                <option value="rented"      <?= ($_GET['status'] ?? '') === 'rented'      ? 'selected' : '' ?>>Rented</option>
                <option value="maintenance" <?= ($_GET['status'] ?? '') === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
            </select>
        </div>
        <div class="filter-actions">
            <button type="submit" class="btn-primary">
                <i class="fas fa-filter"></i> Filter
            </button>
            <a href="<?= BASE_URL ?>?page=admin-cars" class="btn-reset">Reset</a>
        </div>
    </form>
</div>

<!-- CARS TABLE -->
<div class="table-card">
    <div class="table-card-header">
        <span class="table-card-title">All Vehicles</span>
        <span class="record-count"><?= count($cars ?? []) ?> Records</span>
    </div>

    <div class="table-wrap">

        <!-- Desktop table -->
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
                    <td><?= $c['seats'] ?? '—' ?></td>
                    <td><?= $c['transmission'] ?? '—' ?></td>
                    <td><?= $c['fuel_type'] ?? '—' ?></td>
                    <td><?= $c['engine'] ?? '—' ?></td>
                    <td>₱<?= number_format($c['daily_rate'] ?? 0, 2) ?></td>
                    <td><span class="badge badge-<?= strtolower($c['status']) ?>"><?= ucfirst($c['status']) ?></span></td>
                    <td>
                        <div class="action-btns">
                            <a href="<?= BASE_URL ?>?page=admin-cars-edit&id=<?= $c['car_id'] ?>"
                               class="btn-action btn-edit" title="Edit">
                                <i class="fas fa-pen"></i>
                            </a>
                            <form method="POST" action="<?= BASE_URL ?>?page=admin-cars-delete"
                                  onsubmit="return confirm('Delete this car?')" style="display:inline">
                                <input type="hidden" name="car_id" value="<?= $c['car_id'] ?>">
                                <button type="submit" class="btn-action btn-delete" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
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

        <!-- Mobile card list (shown instead of table on ≤768px) -->
        <div class="car-cards">
            <?php if (!empty($cars)): ?>
                <?php foreach ($cars as $c): ?>
                <div class="car-card">
                    <div class="car-card-main">
                        <div class="car-card-name">
                            <?= htmlspecialchars($c['brand']) ?> <?= htmlspecialchars($c['model_name']) ?>
                        </div>
                        <div class="car-card-meta">
                            <span><code class="plate-code"><?= htmlspecialchars($c['plate_number']) ?></code></span>
                            <span><?= $c['year'] ?></span>
                            <span><?= $c['transmission'] ?? '—' ?></span>
                            <span><?= $c['fuel_type'] ?? '—' ?></span>
                            <?php if (!empty($c['seats'])): ?>
                                <span><?= $c['seats'] ?> seats</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="car-card-actions">
                        <div class="action-btns">
                            <a href="<?= BASE_URL ?>?page=admin-cars-edit&id=<?= $c['car_id'] ?>"
                               class="btn-action btn-edit" title="Edit">
                                <i class="fas fa-pen"></i>
                            </a>
                            <form method="POST" action="<?= BASE_URL ?>?page=admin-cars-delete"
                                  onsubmit="return confirm('Delete this car?')" style="display:inline">
                                <input type="hidden" name="car_id" value="<?= $c['car_id'] ?>">
                                <button type="submit" class="btn-action btn-delete" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="car-card-bottom">
                        <span class="car-card-rate">₱<?= number_format($c['daily_rate'] ?? 0, 2) ?>/day</span>
                        <span class="badge badge-<?= strtolower($c['status']) ?>"><?= ucfirst($c['status']) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-row">
                    <i class="fas fa-car" style="font-size:2rem;margin-bottom:8px;display:block;opacity:.3"></i>
                    No cars in fleet.
                </div>
            <?php endif; ?>
        </div>

    </div><!-- /.table-wrap -->
</div><!-- /.table-card -->