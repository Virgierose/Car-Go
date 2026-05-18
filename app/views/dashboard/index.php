<?php
/**
 * View: dashboard/index.php
 * Path: C:\xampp\htdocs\CarGo\app\views\dashboard\index.php
 *
 * Uses: MySQLi via Database::getInstance()
 *       Session key: $_SESSION['client_id']  (set by AuthController)
 */

// ── GUARD ────────────────────────────────────────────────────
if (!isset($_SESSION['client_id'])) {
    $_SESSION['redirect_after_login'] = BASE_URL . '?page=dashboard';
    header('Location: ' . BASE_URL . '?page=login');
    exit;
}

$uid = (int) $_SESSION['client_id'];
$db  = Database::getInstance();

// ── HANDLE POST: EDIT ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['action'] ?? '') === 'edit') {
    $id         = (int)   ($_POST['id']           ?? 0);
    $car_id     = (int)   ($_POST['car_id']        ?? 0);
    $pickup     = trim(   $_POST['rental_start']   ?? '');
    $ret        = trim(   $_POST['rental_end']     ?? '');
    $amount     = (float) ($_POST['total_amount']  ?? 0);
    $status     = trim(   $_POST['rental_status']  ?? '');
    $allowed    = ['pending', 'confirmed', 'ongoing', 'completed', 'cancelled'];

    if ($id && $car_id && $pickup && $ret && $amount > 0 && in_array($status, $allowed)) {
        // Recalculate total days
        $days = max(1, (int) ((strtotime($ret) - strtotime($pickup)) / 86400));

        $stmt = $db->prepare("
            UPDATE tbl_booking
               SET car_id        = ?,
                   pickup_date   = ?,
                   return_date   = ?,
                   day_estimated = ?,
                   total_price   = ?,
                   bkng_status   = ?
             WHERE booking_id = ?
               AND client_id  = ?
        ");
        $stmt->bind_param('issiisii', $car_id, $pickup, $ret, $days, $amount, $status, $id, $uid);
        $stmt->execute();
        $stmt->close();
    }

    header('Location: ' . BASE_URL . '?page=dashboard&success=edit');
    exit;
}

// ── HANDLE POST: DELETE ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['action'] ?? '') === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);

    if ($id) {
        $stmt = $db->prepare("DELETE FROM tbl_booking WHERE booking_id = ? AND client_id = ?");
        $stmt->bind_param('ii', $id, $uid);
        $stmt->execute();
        $stmt->close();
    }

    header('Location: ' . BASE_URL . '?page=dashboard&success=delete');
    exit;
}

// ── FETCH BOOKINGS ────────────────────────────────────────────
$stmt = $db->prepare("
    SELECT
        b.booking_id,
        b.car_id,
        CONCAT(cm.brand, ' ', cm.model_name, ' (', c.year, ')') AS vehicle,
        b.pickup_date               AS pickup,
        b.return_date               AS `return`,
        b.day_estimated             AS total_days,
        b.total_price               AS amount,
        b.bkng_status               AS status,
        b.booking_dateAdded         AS date_created
    FROM tbl_booking b
    JOIN tbl_car       c  ON c.car_id    = b.car_id
    JOIN tbl_car_model cm ON cm.model_id = c.model_id
    WHERE b.client_id = ?
    ORDER BY b.booking_dateAdded DESC
");
$stmt->bind_param('i', $uid);
$stmt->execute();
$result   = $stmt->get_result();
$bookings = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ── FETCH CARS FOR EDIT DROPDOWN ─────────────────────────────
$cars = $db->query("
    SELECT c.car_id, cm.brand AS car_brand, cm.model_name AS car_model, c.year AS car_year
    FROM tbl_car c
    JOIN tbl_car_model cm ON cm.model_id = c.model_id
    WHERE c.status != 'maintenance'
    ORDER BY cm.brand
")->fetch_all(MYSQLI_ASSOC);

// ── STATS ────────────────────────────────────────────────────
$stats = [
    'total'     => count($bookings),
    'active'    => count(array_filter($bookings, fn($b) => in_array($b['status'], ['confirmed','ongoing']))),
    'completed' => count(array_filter($bookings, fn($b) => $b['status'] === 'completed')),
    'revenue'   => array_sum(array_column($bookings, 'amount')),
];

// ── ACTIVE BOOKING BANNER ────────────────────────────────────
$active_booking = null;
foreach ($bookings as $b) {
    if (in_array($b['status'], ['confirmed', 'ongoing'])) {
        $active_booking = $b;
        break;
    }
}

$fname = htmlspecialchars($_SESSION['client_fname'] ?? 'Client');
?>

<!-- ══ PAGE HEADER ══════════════════════════════════════════ -->
<div class="page-top">
    <div class="container">
        <span class="section-label">Account</span>
        <h1 class="section-title">My Dashboard</h1>
        <p class="section-sub">Welcome back, <?= $fname ?>. Manage your rentals below.</p>
    </div>
</div>

<!-- ══ TOAST ════════════════════════════════════════════════ -->
<?php if (!empty($_GET['success'])): ?>
<div id="toast" style="position:fixed;bottom:1.5rem;right:1.5rem;background:#27ae60;color:#fff;
     padding:10px 20px;border-radius:8px;font-size:.875rem;font-weight:600;z-index:999;
     box-shadow:0 4px 16px rgba(0,0,0,.2);">
    <?= $_GET['success'] === 'edit' ? '✅ Booking updated!' : '✅ Booking deleted!' ?>
</div>
<script>
    setTimeout(function(){
        var t = document.getElementById('toast');
        if (t) { t.style.transition='opacity .4s'; t.style.opacity='0'; }
    }, 3000);
</script>
<?php endif; ?>

<!-- ══ MAIN LAYOUT ══════════════════════════════════════════ -->
<section class="section">
<div class="container">
<div style="display:grid;grid-template-columns:200px 1fr;gap:2rem;align-items:start;">

    <!-- Side Nav -->
    <div class="side-nav">
        <a href="<?= BASE_URL ?>?page=dashboard" class="active">My Bookings</a>
        <a href="#">History</a>
        <a href="#">Profile</a>
        <hr>
        <a href="<?= BASE_URL ?>?page=price-calculator">New Booking</a>
        <a href="<?= BASE_URL ?>?page=logout" style="color:var(--muted);">Logout</a>
    </div>

    <!-- Main Content -->
    <div>

        <!-- Stat Cards -->
        <div class="grid-4" style="margin-bottom:1.5rem;">
            <div class="stat-card">
                <p class="stat-label">Total Bookings</p>
                <p class="stat-value"><?= $stats['total'] ?></p>
            </div>
            <div class="stat-card">
                <p class="stat-label">Active</p>
                <p class="stat-value red"><?= $stats['active'] ?></p>
            </div>
            <div class="stat-card">
                <p class="stat-label">Completed</p>
                <p class="stat-value"><?= $stats['completed'] ?></p>
            </div>
            <div class="stat-card">
                <p class="stat-label">Total Spent</p>
                <p class="stat-value">₱<?= number_format($stats['revenue']) ?></p>
            </div>
        </div>

        <!-- Active Booking Banner -->
        <?php if ($active_booking): ?>
        <div class="alert alert-info" style="margin-bottom:1.5rem;">
            🚗 Active: <strong><?= htmlspecialchars($active_booking['vehicle']) ?></strong>
            &nbsp;— Pickup <?= htmlspecialchars($active_booking['pickup']) ?>
            &nbsp;| Return <?= htmlspecialchars($active_booking['return']) ?>
            <a href="<?= BASE_URL ?>?page=booking-confirmation"
               style="color:var(--red);margin-left:.75rem;font-size:.82rem;">View →</a>
        </div>
        <?php endif; ?>

        <!-- Table Header -->
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
            <h2 style="font-family:'Barlow Condensed',sans-serif;font-size:1rem;font-weight:700;
                       letter-spacing:.08em;text-transform:uppercase;">My Rentals</h2>
            <a href="<?= BASE_URL ?>?page=price-calculator" class="btn btn-red btn-sm">+ New Booking</a>
        </div>

        <!-- Bookings Table -->
        <div class="table-wrap">
            <?php if (empty($bookings)): ?>
            <p style="padding:2rem;text-align:center;color:var(--muted);">
                No rentals yet.
                <a href="<?= BASE_URL ?>?page=price-calculator">Make your first booking →</a>
            </p>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Vehicle</th>
                        <th>Pickup</th>
                        <th>Return</th>
                        <th>Days</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th style="width:130px;"></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $badge_map = [
                    'confirmed' => 'badge-green',
                    'ongoing'   => 'badge-green',
                    'completed' => 'badge-grey',
                    'pending'   => 'badge-yellow',
                    'cancelled' => 'badge-red',
                ];
                foreach ($bookings as $b):
                    $bg   = $badge_map[$b['status']] ?? 'badge-grey';
                    $json = json_encode($b, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG);
                ?>
                <tr id="row-<?= (int)$b['booking_id'] ?>">
                    <td style="color:var(--muted);font-size:.82rem;"><?= (int)$b['booking_id'] ?></td>
                    <td><strong><?= htmlspecialchars($b['vehicle']) ?></strong></td>
                    <td><?= htmlspecialchars($b['pickup']) ?></td>
                    <td><?= htmlspecialchars($b['return']) ?></td>
                    <td><?= (int)$b['total_days'] ?></td>
                    <td style="font-family:'Barlow Condensed',sans-serif;font-weight:700;">
                        ₱<?= number_format((float)$b['amount']) ?>
                    </td>
                    <td><span class="badge <?= $bg ?>"><?= ucfirst(htmlspecialchars($b['status'])) ?></span></td>
                    <td style="display:flex;gap:.4rem;">
                        <button class="btn btn-dark btn-sm"
                            data-id="<?= (int)$b['booking_id'] ?>"
                            data-booking='<?= $json ?>'
                            onclick="openEditBtn(this)">Edit</button>
                        <button class="btn btn-sm"
                            style="color:#c0392b;border-color:#c0392b;"
                            data-id="<?= (int)$b['booking_id'] ?>"
                            data-vehicle="<?= htmlspecialchars($b['vehicle'], ENT_QUOTES) ?>"
                            onclick="openDelBtn(this)">Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

    </div><!-- /main content -->
</div><!-- /grid -->
</div><!-- /container -->
</section>

<!-- ══ EDIT MODAL ═══════════════════════════════════════════ -->
<div id="edit-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);
     z-index:900;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:10px;padding:2rem;width:460px;max-width:95vw;
              box-shadow:0 8px 32px rgba(0,0,0,.2);">
    <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:1.25rem;">Edit Rental</h3>
    <form method="POST" action="<?= BASE_URL ?>?page=dashboard&action=edit">
      <input type="hidden" name="id"     id="e-id">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">

        <div style="grid-column:1/-1;">
          <label style="font-size:.8rem;font-weight:600;display:block;margin-bottom:4px;">Vehicle</label>
          <select name="car_id" id="e-car-id"
            style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:6px;font-size:.9rem;">
            <?php foreach ($cars as $car): ?>
            <option value="<?= (int)$car['car_id'] ?>">
                <?= htmlspecialchars($car['car_brand'] . ' ' . $car['car_model'] . ' (' . $car['car_year'] . ')') ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label style="font-size:.8rem;font-weight:600;display:block;margin-bottom:4px;">Pickup Date</label>
          <input name="rental_start" id="e-pickup" type="date"
            style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:6px;font-size:.9rem;" required>
        </div>
        <div>
          <label style="font-size:.8rem;font-weight:600;display:block;margin-bottom:4px;">Return Date</label>
          <input name="rental_end" id="e-return" type="date"
            style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:6px;font-size:.9rem;" required>
        </div>
        <div>
          <label style="font-size:.8rem;font-weight:600;display:block;margin-bottom:4px;">Amount (₱)</label>
          <input name="total_amount" id="e-amount" type="number" min="0" step="0.01"
            style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:6px;font-size:.9rem;" required>
        </div>
        <div>
          <label style="font-size:.8rem;font-weight:600;display:block;margin-bottom:4px;">Status</label>
          <select name="rental_status" id="e-status"
            style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:6px;font-size:.9rem;">
            <option value="pending">Pending</option>
            <option value="confirmed">Confirmed</option>
            <option value="ongoing">Ongoing</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>

      </div>
      <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:1.25rem;
                  padding-top:1rem;border-top:1px solid #eee;">
        <button type="button" class="btn btn-sm" onclick="closeEdit()">Cancel</button>
        <button type="submit" class="btn btn-red btn-sm">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<!-- ══ DELETE MODAL ══════════════════════════════════════════ -->
<div id="del-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);
     z-index:900;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:10px;padding:2rem;width:360px;max-width:95vw;
              text-align:center;box-shadow:0 8px 32px rgba(0,0,0,.2);">
    <div style="font-size:2rem;margin-bottom:.75rem;">🗑️</div>
    <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:.5rem;">Delete Rental?</h3>
    <p style="font-size:.9rem;color:#555;margin-bottom:1.5rem;">
        Are you sure you want to delete the rental for
        <strong id="del-vehicle-name"></strong>? This cannot be undone.
    </p>
    <form method="POST" action="<?= BASE_URL ?>?page=dashboard&action=delete">
      <input type="hidden" name="id" id="del-id">
      <div style="display:flex;justify-content:center;gap:10px;">
        <button type="button" class="btn btn-sm" onclick="closeDel()">Cancel</button>
        <button type="submit" class="btn btn-sm"
                style="background:#c0392b;color:#fff;border-color:#c0392b;">Yes, Delete</button>
      </div>
    </form>
  </div>
</div>

<!-- ══ SCRIPTS ═══════════════════════════════════════════════ -->
<script>
function openEditBtn(btn) {
    var data = JSON.parse(btn.getAttribute('data-booking'));
    document.getElementById('e-id').value      = data.booking_id;
    document.getElementById('e-car-id').value  = data.car_id;
    document.getElementById('e-pickup').value  = data.pickup;
    document.getElementById('e-return').value  = data['return'];
    document.getElementById('e-amount').value  = data.amount;
    document.getElementById('e-status').value  = data.status;
    document.getElementById('edit-overlay').style.display = 'flex';
}
function closeEdit() { document.getElementById('edit-overlay').style.display = 'none'; }

function openDelBtn(btn) {
    document.getElementById('del-id').value = btn.getAttribute('data-id');
    document.getElementById('del-vehicle-name').textContent = btn.getAttribute('data-vehicle');
    document.getElementById('del-overlay').style.display = 'flex';
}
function closeDel() { document.getElementById('del-overlay').style.display = 'none'; }

// Close modals on backdrop click
document.getElementById('edit-overlay').addEventListener('click', function(e) {
    if (e.target === this) closeEdit();
});
document.getElementById('del-overlay').addEventListener('click', function(e) {
    if (e.target === this) closeDel();
});
</script>