<?php $pageTitle = 'Dashboard'; $activePage = 'dashboard'; ob_start(); ?>

<!-- STATS -->
<div class="stats-grid" style="align-items:center; margin-bottom:24px;">
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-car"></i></div>
        <div class="stat-value"><?= $totalCars ?? 0 ?></div>
        <div class="stat-label">Total Cars</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
        <div class="stat-value"><?= $totalBookings ?? 0 ?></div>
        <div class="stat-label">Total Bookings</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-id-card"></i></div>
        <div class="stat-value"><?= $totalDrivers ?? 0 ?></div>
        <div class="stat-label">Active Drivers</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-peso-sign"></i></div>
        <div class="stat-value">₱<?= number_format($totalRevenue ?? 0) ?></div>
        <div class="stat-label">Total Revenue</div>
    </div>
</div>

<!-- RECENT BOOKINGS -->
<div class="card">
    <div class="card-header">
        <span class="card-title">Recent Bookings</span>
        <a href="<?= BASE_URL ?>?page=admin-bookings" class="btn btn-outline btn-icon"><i class="fas fa-arrow-right"></i> View All</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Client</th>
                <th>Car</th>
                <th>Driver</th>
                <th>Pickup</th>
                <th>Return</th>
                <th>Days</th>
                <th>Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!empty($recentBookings)): ?>
            <?php foreach ($recentBookings as $b): ?>
            <tr>
                <td>#<?= $b['booking_id'] ?></td>
                <td><?= htmlspecialchars($b['clnt_fname'] . ' ' . $b['clnt_lname']) ?></td>
                <td><?= htmlspecialchars($b['model_name'] ?? $b['plate_number']) ?></td>
                <td><?= htmlspecialchars($b['drvr_fname'] . ' ' . $b['drvr_lname']) ?></td>
                <td><?= $b['pickup_date'] ?></td>
                <td><?= $b['return_date'] ?></td>
                <td><?= $b['num_days'] ?></td>
                <td>₱<?= number_format($b['total_price'], 2) ?></td>
                <td><span class="badge badge-<?= strtolower($b['bkng_status']) ?>"><?= $b['bkng_status'] ?></span></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="9" style="text-align:center;color:var(--silver);padding:32px">No bookings found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- AVAILABLE CARS -->
<div class="card">
    <div class="card-header">
        <span class="card-title">Fleet Overview</span>
        <a href="<?= BASE_URL ?>?page=admin-cars" class="btn btn-outline btn-icon"><i class="fas fa-arrow-right"></i> Manage Fleet</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>Car ID</th><th>Model</th><th>Brand</th><th>Plate No.</th><th>Color</th><th>Year</th><th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!empty($cars)): ?>
            <?php foreach ($cars as $c): ?>
            <tr>
                <td>#<?= $c['car_id'] ?></td>
                <td><?= htmlspecialchars($c['model_name']) ?></td>
                <td><?= htmlspecialchars($c['brand']) ?></td>
                <td><?= htmlspecialchars($c['plate_number']) ?></td>
                <td><?= htmlspecialchars($c['color']) ?></td>
                <td><?= $c['year'] ?></td>
                <td><span class="badge badge-<?= strtolower($c['status']) ?>"><?= $c['status'] ?></span></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="7" style="text-align:center;color:var(--silver);padding:32px">No cars in fleet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin_layout.php';
?>