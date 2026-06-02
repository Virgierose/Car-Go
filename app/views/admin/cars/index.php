<?php
$pageTitle  = 'Manage Cars';
$activePage = 'admin-cars';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/cars.css">

<!-- FILTER BAR -->
<div class="filter-bar">
    <form method="GET" action="<?= BASE_URL ?>?page=admin-cars" class="filter-form">
        <div class="filter-group">
            <i class="fas fa-search filter-icon"></i>
            <input type="text" name="search" class="filter-input"
                   placeholder="Search by model, brand, plate..."
                   value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        </div>
        <div class="filter-group">
            <select name="status" class="filter-select">
                <option value="">All Status</option>
                <option value="Available"    <?= ($_GET['status'] ?? '') === 'Available'    ? 'selected' : '' ?>>Available</option>
                <option value="Rented"       <?= ($_GET['status'] ?? '') === 'Rented'       ? 'selected' : '' ?>>Rented</option>
                <option value="Maintenance"  <?= ($_GET['status'] ?? '') === 'Maintenance'  ? 'selected' : '' ?>>Maintenance</option>
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
</div>