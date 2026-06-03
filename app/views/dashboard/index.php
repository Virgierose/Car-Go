<?php
/**
 * View: dashboard/index.php
 * Tabs: My Bookings | History | Profile
 *
 * FIX: Cleaned up rental-card action block — no more duplicate
 *      Details/Cancel buttons. Payment button now correctly shows
 *      when rental_status = 'pending' AND docs_status = 'approved'.
 * FIX: Car image onerror fallback to placeholder.
 */

// ── GUARD ────────────────────────────────────────────────────
if (!isset($_SESSION['client_id'])) {
    $_SESSION['redirect_after_login'] = BASE_URL . '?page=dashboard';
    header('Location: ' . BASE_URL . '?page=login');
    exit;
}

$uid = (int) $_SESSION['client_id'];
$db  = Database::getInstance();

// ── ACTIVE TAB ───────────────────────────────────────────────
$tab = $_GET['tab'] ?? 'bookings';

// ════════════════════════════════════════════════════════════
// HANDLE POST: PROFILE UPDATE
// ════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['action'] ?? '') === 'update-profile') {
    $fname   = trim($_POST['fname']   ?? '');
    $lname   = trim($_POST['lname']   ?? '');
    $mname   = trim($_POST['mname']   ?? '');
    $phone   = trim($_POST['phone']   ?? '');
    $address = trim($_POST['address'] ?? '');
    $email   = trim($_POST['email']   ?? '');
    $errors  = [];

    if ($fname === '')  $errors[] = 'First name is required.';
    if ($lname === '')  $errors[] = 'Last name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';

    if (empty($errors)) {
        $chk = $db->prepare("SELECT client_id FROM tbl_client WHERE email = ? AND client_id != ?");
        $chk->bind_param('si', $email, $uid);
        $chk->execute();
        $chk->store_result();
        if ($chk->num_rows > 0) $errors[] = 'That email is already used by another account.';
        $chk->close();
    }

    if (empty($errors)) {
        $stmt = $db->prepare("
            UPDATE tbl_client
               SET clnt_fname = ?, clnt_lname = ?, clnt_mname = ?,
                   clnt_phone_number = ?, adress = ?, email = ?
             WHERE client_id = ?
        ");
        $stmt->bind_param('ssssssi', $fname, $lname, $mname, $phone, $address, $email, $uid);
        $stmt->execute();
        $stmt->close();

        $_SESSION['client_fname'] = $fname;
        header('Location: ' . BASE_URL . '?page=dashboard&tab=profile&success=profile');
        exit;
    }
    $tab = 'profile';
}

// ── HANDLE POST: CHANGE PASSWORD ─────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['action'] ?? '') === 'change-password') {
    $current  = $_POST['current_password']  ?? '';
    $new      = $_POST['new_password']      ?? '';
    $confirm  = $_POST['confirm_password']  ?? '';
    $pw_errors = [];

    $chk = $db->prepare("SELECT password FROM tbl_client WHERE client_id = ?");
    $chk->bind_param('i', $uid);
    $chk->execute();
    $chk->bind_result($hash);
    $chk->fetch();
    $chk->close();

    if (!password_verify($current, $hash))   $pw_errors[] = 'Current password is incorrect.';
    if (strlen($new) < 8)                    $pw_errors[] = 'New password must be at least 8 characters.';
    if ($new !== $confirm)                   $pw_errors[] = 'New passwords do not match.';

    if (empty($pw_errors)) {
        $new_hash = password_hash($new, PASSWORD_BCRYPT);
        $stmt = $db->prepare("UPDATE tbl_client SET password = ? WHERE client_id = ?");
        $stmt->bind_param('si', $new_hash, $uid);
        $stmt->execute();
        $stmt->close();
        header('Location: ' . BASE_URL . '?page=dashboard&tab=profile&success=password');
        exit;
    }
    $tab = 'profile';
}

// ── HANDLE POST: CANCEL RENTAL ────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['action'] ?? '') === 'cancel-rental') {
    $rid = (int) ($_POST['rental_id'] ?? 0);
    if ($rid) {
        $stmt = $db->prepare("
            UPDATE tbl_rental SET rental_status = 'cancelled'
            WHERE rental_id = ? AND client_id = ?
              AND rental_status IN ('docs_pending','pending','confirmed')
        ");
        $stmt->bind_param('ii', $rid, $uid);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: ' . BASE_URL . '?page=dashboard&tab=bookings&success=cancelled');
    exit;
}

// ════════════════════════════════════════════════════════════
// FETCH DATA
// ════════════════════════════════════════════════════════════

// ── Client profile ───────────────────────────────────────────
$pstmt = $db->prepare("SELECT * FROM tbl_client WHERE client_id = ?");
$pstmt->bind_param('i', $uid);
$pstmt->execute();
$client = $pstmt->get_result()->fetch_assoc();
$pstmt->close();

// ── Active / Upcoming rentals ────────────────────────────────
$rstmt = $db->prepare("
    SELECT
        r.rental_id,
        r.booking_ref,
        r.car_id,
        CONCAT(cm.brand, ' ', cm.model_name) AS vehicle,
        c.year          AS car_year,
        c.image         AS car_image,
        c.plate_number,
        r.rental_start  AS pickup,
        r.rental_end    AS `return`,
        r.total_days,
        r.total_amount  AS amount,
        r.pickup_address,
        r.payment_method,
        r.rental_status AS status,
        r.docs_status,
        r.docs_note,
        r.notes,
        r.date_created,
        CONCAT(d.drvr_fname, ' ', d.drvr_lname) AS driver_name,
        r.dest_address,
        r.dest_lat,
        r.dest_lon,
        r.distance_km
    FROM tbl_rental r
    JOIN tbl_car       c  ON c.car_id    = r.car_id
    JOIN tbl_car_model cm ON cm.model_id = c.model_id
    LEFT JOIN tbl_driver d ON d.driver_id = r.driver_id
    WHERE r.client_id = ?
      AND r.rental_status IN ('docs_pending','pending','confirmed')
    ORDER BY r.rental_start ASC
");
$rstmt->bind_param('i', $uid);
$rstmt->execute();
$active_rentals = $rstmt->get_result()->fetch_all(MYSQLI_ASSOC);
$rstmt->close();

// ── History ───────────────────────────────────────────────────
$hstmt = $db->prepare("
    SELECT
        r.rental_id,
        r.booking_ref,
        CONCAT(cm.brand, ' ', cm.model_name) AS vehicle,
        c.year          AS car_year,
        c.image         AS car_image,
        r.rental_start  AS pickup,
        r.rental_end    AS `return`,
        r.total_days,
        r.total_amount  AS amount,
        r.payment_method,
        r.rental_status AS status,
        r.date_created,
        CONCAT(d.drvr_fname, ' ', d.drvr_lname) AS driver_name,
        r.dest_address,
        r.dest_lat,
        r.dest_lon,
        r.distance_km
    FROM tbl_rental r
    JOIN tbl_car       c  ON c.car_id    = r.car_id
    JOIN tbl_car_model cm ON cm.model_id = c.model_id
    LEFT JOIN tbl_driver d ON d.driver_id = r.driver_id
    WHERE r.client_id = ?
      AND r.rental_status IN ('completed','cancelled')
    ORDER BY r.rental_end DESC
");
$hstmt->bind_param('i', $uid);
$hstmt->execute();
$history = $hstmt->get_result()->fetch_all(MYSQLI_ASSOC);
$hstmt->close();

// ── Stats ─────────────────────────────────────────────────────
$all_rentals = array_merge($active_rentals, $history);
$stats = [
    'total'     => count($all_rentals),
    'active'    => count($active_rentals),
    'completed' => count(array_filter($history, fn($r) => $r['status'] === 'completed')),
    'spent'     => array_sum(array_column(
                     array_filter($all_rentals, fn($r) => $r['status'] !== 'cancelled'),
                     'amount')),
];

$fname = htmlspecialchars($client['clnt_fname'] ?? 'Client');
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="<?= ASSET_URL ?>assets/css/dashboard.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

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
<?php
$toastMap = [
    'profile'   => ['cls' => 'toast-success', 'msg' => '✅ Profile updated!'],
    'password'  => ['cls' => 'toast-success', 'msg' => '✅ Password changed!'],
    'cancelled' => ['cls' => 'toast-cancel',  'msg' => '🚫 Booking cancelled.'],
];
$t = $toastMap[$_GET['success']] ?? ['cls' => 'toast-success', 'msg' => '✅ Done!'];
?>
<div id="toast" class="toast <?= $t['cls'] ?>"><?= $t['msg'] ?></div>
<script>setTimeout(function(){var el=document.getElementById('toast');if(el){el.style.opacity='0';}},3000);</script>
<?php endif; ?>

<!-- ══ MAIN LAYOUT ══════════════════════════════════════════ -->
<section class="section">
<div class="container">
<div class="dash-wrap">

    <!-- ── Side Nav ───────────────────────────────────────── -->
    <nav class="side-nav">
        <a href="<?= BASE_URL ?>?page=dashboard&tab=bookings"
           class="<?= $tab === 'bookings' ? 'active' : '' ?>">My Bookings</a>
        <a href="<?= BASE_URL ?>?page=dashboard&tab=history"
           class="<?= $tab === 'history'  ? 'active' : '' ?>">History</a>
        <a href="<?= BASE_URL ?>?page=dashboard&tab=profile"
           class="<?= $tab === 'profile'  ? 'active' : '' ?>">Profile</a>
        <hr>
        <a href="<?= BASE_URL ?>?page=price-calculator">+ New Booking</a>
        <a href="<?= BASE_URL ?>?page=logout" style="color:var(--text-muted);">Logout</a>
    </nav>

    <!-- ── Main Panel ─────────────────────────────────────── -->
    <div>

        <!-- Stat Cards -->
        <div class="stat-grid">
            <div class="stat-card">
                <p class="stat-label">Total Rentals</p>
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
                <p class="stat-value">₱<?= number_format($stats['spent']) ?></p>
            </div>
        </div>

        <!-- ════ TAB: MY BOOKINGS ════════════════════════════ -->
        <div class="tab-panel <?= $tab === 'bookings' ? 'active' : '' ?>">

            <div class="section-header-row">
                <h2>Active &amp; Upcoming Rentals</h2>
                <a href="<?= BASE_URL ?>?page=price-calculator" class="btn btn-red btn-sm">+ New Booking</a>
            </div>

            <?php if (empty($active_rentals)): ?>
            <div class="empty-state">
                <div class="empty-icon">🚗</div>
                <p>You have no active or upcoming rentals.</p>
                <a href="<?= BASE_URL ?>?page=price-calculator" class="btn btn-red">Book a Car Now</a>
            </div>
            <?php else: ?>

                <?php foreach ($active_rentals as $r):
                    $status      = $r['status']     ?? '';
                    $docs_status = $r['docs_status'] ?? '';
                ?>
                <div class="rental-card">

                    <!-- Car Image -->
                    <div>
                        <?php if (!empty($r['car_image'])): ?>
                            <img
                                src="<?= BASE_URL . htmlspecialchars($r['car_image']) ?>"
                                alt="<?= htmlspecialchars($r['vehicle']) ?>"
                                class="rental-car-img"
                                onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                            <div class="rental-car-img-placeholder" style="display:none;">🚗</div>
                        <?php else: ?>
                            <div class="rental-car-img-placeholder">🚗</div>
                        <?php endif; ?>
                    </div>

                    <!-- Info -->
                    <div class="rental-info">
                        <div class="rental-ref"><?= htmlspecialchars($r['booking_ref']) ?></div>
                        <div class="rental-veh">
                            <?= htmlspecialchars($r['vehicle']) ?>
                            <span style="font-size:.85rem;font-weight:400;color:var(--text-muted);">
                                (<?= htmlspecialchars($r['car_year']) ?>)
                            </span>
                        </div>
                        <div class="rental-meta">
                            <span>📅 <strong><?= date('M d, Y g:i A', strtotime($r['pickup'])) ?></strong></span>
                            <span>🔁 Return: <strong><?= date('M d, Y g:i A', strtotime($r['return'])) ?></strong></span>
                            <span>📍 <strong><?= htmlspecialchars($r['pickup_address'] ?: 'TBD') ?></strong></span>
                            <span>🧑‍✈️ <strong><?= htmlspecialchars($r['driver_name'] ?? 'Self-Drive') ?></strong></span>
                            <span>💳 <strong><?= strtoupper($r['payment_method']) ?></strong></span>
                            <span><?= (int)$r['total_days'] ?> day<?= $r['total_days'] > 1 ? 's' : '' ?></span>
                        </div>
                    </div>

                    <!-- ── RIGHT COLUMN: badge + amount + actions ── -->
                    <!--
                        STATUS LOGIC (mutually exclusive, clean):
                        1. docs_pending + rejected  → show rejection note + Cancel
                        2. docs_pending + pending   → show "under review" + Cancel
                        3. pending + approved       → ✅ PAYMENT BUTTON
                        4. confirmed                → Details + Cancel
                        5. anything else            → Details only
                    -->
                    <div class="rental-actions">

                        <span class="badge badge-<?= $status ?>">
                            <?= ucfirst(str_replace('_', ' ', $status)) ?>
                        </span>

                        <div class="rental-amount">₱<?= number_format((float)$r['amount'], 2) ?></div>

                        <?php if ($status === 'docs_pending'): ?>
                            <?php if ($docs_status === 'rejected'): ?>
                                <div class="docs-notice rejected">
                                    ✕ Docs rejected<?= !empty($r['docs_note']) ? ': ' . htmlspecialchars($r['docs_note']) : '' ?>
                                </div>
                            <?php else: ?>
                                <div class="docs-notice review">
                                    ⏳ Documents under review…
                                </div>
                            <?php endif; ?>
                            <div class="action-group">
                                <button class="btn btn-dark btn-sm"
                                    onclick="openDetail(<?= htmlspecialchars(json_encode($r), ENT_QUOTES) ?>)">
                                    Details
                                </button>
                                <button class="btn btn-cancel btn-sm"
                                    onclick="openCancel(<?= (int)$r['rental_id'] ?>, '<?= htmlspecialchars($r['vehicle'], ENT_QUOTES) ?>')">
                                    Cancel
                                </button>
                            </div>

                        <?php elseif ($status === 'pending' && $docs_status === 'approved'): ?>
                            <!-- docs approved, awaiting payment -->
                            <a href="<?= BASE_URL ?>?page=payment&rental_id=<?= (int)$r['rental_id'] ?>"
                               class="btn btn-red btn-sm">
                                💳 Proceed to Payment
                            </a>
                            <div class="action-group">
                                <button class="btn btn-dark btn-sm"
                                    onclick="openDetail(<?= htmlspecialchars(json_encode($r), ENT_QUOTES) ?>)">
                                    Details
                                </button>
                            </div>

                        <?php elseif ($status === 'confirmed'): ?>
                            <div class="action-group">
                                <button class="btn btn-dark btn-sm"
                                    onclick="openDetail(<?= htmlspecialchars(json_encode($r), ENT_QUOTES) ?>)">
                                    Details
                                </button>
                                <button class="btn btn-cancel btn-sm"
                                    onclick="openCancel(<?= (int)$r['rental_id'] ?>, '<?= htmlspecialchars($r['vehicle'], ENT_QUOTES) ?>')">
                                    Cancel
                                </button>
                            </div>

                        <?php else: ?>
                            <div class="action-group">
                                <button class="btn btn-dark btn-sm"
                                    onclick="openDetail(<?= htmlspecialchars(json_encode($r), ENT_QUOTES) ?>)">
                                    Details
                                </button>
                            </div>
                        <?php endif; ?>

                    </div><!-- /rental-actions -->
                </div><!-- /rental-card -->
                <?php endforeach; ?>

            <?php endif; ?>
        </div><!-- /tab bookings -->

        <!-- ════ TAB: HISTORY ════════════════════════════════ -->
        <div class="tab-panel <?= $tab === 'history' ? 'active' : '' ?>">

            <div class="section-header-row">
                <h2>Rental History</h2>
            </div>

            <?php if (empty($history)): ?>
            <div class="empty-state">
                <div class="empty-icon">📋</div>
                <p>No completed or cancelled rentals yet.</p>
            </div>
            <?php else: ?>
            <div class="hist-table-wrap">
            <table class="hist-table">
                <thead>
                    <tr>
                        <th>Ref #</th>
                        <th>Vehicle</th>
                        <th>Pickup</th>
                        <th>Return</th>
                        <th>Days</th>
                        <th>Driver</th>
                        <th>Payment</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($history as $r): ?>
                <tr>
                    <td class="mono" style="color:var(--text-muted);font-size:.73rem;">
                        <?= htmlspecialchars($r['booking_ref']) ?>
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($r['vehicle']) ?></strong>
                        <span style="color:var(--text-muted);font-size:.73rem;"> (<?= $r['car_year'] ?>)</span>
                    </td>
                    <td><?= date('M d, Y', strtotime($r['pickup'])) ?></td>
                    <td><?= date('M d, Y', strtotime($r['return'])) ?></td>
                    <td><?= (int)$r['total_days'] ?></td>
                    <td><?= htmlspecialchars($r['driver_name'] ?? 'Self-Drive') ?></td>
                    <td style="text-transform:uppercase;"><?= htmlspecialchars($r['payment_method']) ?></td>
                    <td class="mono">₱<?= number_format((float)$r['amount'], 2) ?></td>
                    <td><span class="badge badge-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
                    <td>
                        <button class="btn btn-dark btn-sm"
                            onclick="openDetail(<?= htmlspecialchars(json_encode($r), ENT_QUOTES) ?>)">
                            View
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php endif; ?>
        </div><!-- /tab history -->

        <!-- ════ TAB: PROFILE ════════════════════════════════ -->
        <div class="tab-panel <?= $tab === 'profile' ? 'active' : '' ?>">

            <?php if (!empty($errors)): ?>
            <div class="alert-box alert-error">
                <?php foreach ($errors as $e): ?><div>⚠ <?= htmlspecialchars($e) ?></div><?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($pw_errors)): ?>
            <div class="alert-box alert-error">
                <?php foreach ($pw_errors as $e): ?><div>⚠ <?= htmlspecialchars($e) ?></div><?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Personal Info -->
            <div class="form-section">
                <h3>Personal Information</h3>
                <form method="POST" action="<?= BASE_URL ?>?page=dashboard&action=update-profile&tab=profile">
                    <div class="profile-grid">
                        <div class="form-group-d">
                            <label class="form-label-d">First Name</label>
                            <input class="form-input-d" name="fname" required
                                   value="<?= htmlspecialchars($client['clnt_fname'] ?? '') ?>">
                        </div>
                        <div class="form-group-d">
                            <label class="form-label-d">Last Name</label>
                            <input class="form-input-d" name="lname" required
                                   value="<?= htmlspecialchars($client['clnt_lname'] ?? '') ?>">
                        </div>
                        <div class="form-group-d">
                            <label class="form-label-d">Middle Name</label>
                            <input class="form-input-d" name="mname"
                                   value="<?= htmlspecialchars($client['clnt_mname'] ?? '') ?>">
                        </div>
                        <div class="form-group-d">
                            <label class="form-label-d">Phone Number</label>
                            <input class="form-input-d" name="phone"
                                   value="<?= htmlspecialchars($client['clnt_phone_number'] ?? '') ?>">
                        </div>
                        <div class="form-group-d" style="grid-column:1/-1;">
                            <label class="form-label-d">Email Address</label>
                            <input class="form-input-d" name="email" type="email" required
                                   value="<?= htmlspecialchars($client['email'] ?? '') ?>">
                        </div>
                        <div class="form-group-d" style="grid-column:1/-1;">
                            <label class="form-label-d">Address</label>
                            <input class="form-input-d" name="address"
                                   value="<?= htmlspecialchars($client['adress'] ?? '') ?>">
                        </div>
                    </div>
                    <div style="margin-top:1.2rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem;">
                        <span style="font-size:.72rem;color:var(--text-muted);">
                            Member since <?= date('F j, Y', strtotime($client['client_dateAdded'])) ?>
                        </span>
                        <button type="submit" class="btn btn-red btn-sm">Save Changes</button>
                    </div>
                </form>
            </div>

            <!-- Change Password -->
            <div class="form-section">
                <h3>Change Password</h3>
                <form method="POST" action="<?= BASE_URL ?>?page=dashboard&action=change-password&tab=profile">
                    <div class="profile-grid">
                        <div class="form-group-d" style="grid-column:1/-1;">
                            <label class="form-label-d">Current Password</label>
                            <input class="form-input-d" type="password" name="current_password"
                                   required placeholder="Enter current password">
                        </div>
                        <div class="form-group-d">
                            <label class="form-label-d">New Password</label>
                            <input class="form-input-d" type="password" name="new_password"
                                   required placeholder="Min. 8 characters">
                        </div>
                        <div class="form-group-d">
                            <label class="form-label-d">Confirm New Password</label>
                            <input class="form-input-d" type="password" name="confirm_password"
                                   required placeholder="Repeat new password">
                        </div>
                    </div>
                    <div style="margin-top:1.2rem;text-align:right;">
                        <button type="submit" class="btn btn-red btn-sm">Update Password</button>
                    </div>
                </form>
            </div>

            <!-- Account Info (read-only) -->
            <div class="form-section">
                <h3>Account Details</h3>
                <div class="detail-row">
                    <span class="detail-key">Account ID</span>
                    <span class="detail-val" style="color:var(--text-muted);">#<?= $uid ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-key">Role</span>
                    <span class="detail-val">
                        <span class="badge badge-confirmed"><?= ucfirst($client['role']) ?></span>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-key">Member Since</span>
                    <span class="detail-val"><?= date('F j, Y', strtotime($client['client_dateAdded'])) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-key">Total Rentals</span>
                    <span class="detail-val"><?= $stats['total'] ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-key">Total Spent</span>
                    <span class="detail-val">₱<?= number_format($stats['spent'], 2) ?></span>
                </div>
            </div>

        </div><!-- /tab profile -->

    </div><!-- /main panel -->
</div><!-- /dash-wrap -->
</div><!-- /container -->
</section>

<!-- ══ DETAIL MODAL ═══════════════════════════════════════════ -->
<div class="modal-bg" id="detail-modal">
  <div class="modal-box">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.2rem;">
      <div class="modal-title">Rental Details</div>
      <button class="modal-close" onclick="closeDetail()">✕</button>
    </div>
    <div id="dm-body"></div>
    <div style="margin-top:1.5rem;text-align:right;">
      <button class="btn btn-ghost btn-sm" onclick="closeDetail()">Close</button>
    </div>
  </div>
</div>

<!-- ══ CANCEL MODAL ══════════════════════════════════════════ -->
<div class="modal-bg" id="cancel-modal">
  <div class="modal-box" style="max-width:380px;text-align:center;padding:2rem;">
    <div style="font-size:2.5rem;margin-bottom:.8rem;">🚫</div>
    <div class="modal-title">Cancel Booking?</div>
    <p style="font-size:.83rem;color:var(--text-muted);margin-bottom:1.5rem;">
      Are you sure you want to cancel the rental for
      <strong id="cancel-veh"></strong>? This cannot be undone.
    </p>
    <form method="POST" action="<?= BASE_URL ?>?page=dashboard&action=cancel-rental">
      <input type="hidden" name="rental_id" id="cancel-rid">
      <div style="display:flex;justify-content:center;gap:.6rem;">
        <button type="button" class="btn btn-ghost btn-sm" onclick="closeCancel()">Keep It</button>
        <button type="submit" class="btn btn-red btn-sm">Yes, Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
// ── Detail Modal ─────────────────────────────────────────────
function openDetail(r) {
    var driver  = r.driver_name || 'Self-Drive';
    var pickup  = r.pickup       ? formatDT(r.pickup)       : '—';
    var ret     = r['return']    ? formatDT(r['return'])    : '—';
    var created = r.date_created ? formatDT(r.date_created) : '—';

    var rows = [
        ['Booking Ref',    r.booking_ref   || '—'],
        ['Vehicle',        (r.vehicle||'—') + ' (' + (r.car_year||'') + ')'],
        ['Plate Number',   r.plate_number  || '—'],
        ['Pickup Date',    pickup],
        ['Return Date',    ret],
        ['Pickup Branch',  r.pickup_address || '—'],
        ['Destination',    r.dest_address   || '—'],
        ['Distance',       r.distance_km ? r.distance_km + ' km' : '—'],
        ['Duration',       (r.total_days||1) + ' day' + (r.total_days > 1 ? 's' : '')],
        ['Driver',         driver],
        ['Payment',        (r.payment_method||'').toUpperCase()],
        ['Status',         '<span class="badge badge-'+(r.status||'')+'">'+ucf(r.status)+'</span>'],
        ['Total',          '₱' + fmt(r.amount)],
        ['Booked On',      created],
    ];
    if (r.notes) rows.push(['Notes', r.notes]);

    var miniMap = '';
    if (r.dest_lat && r.dest_lon) {
        miniMap = '<div id="mini-map" style="height:160px;margin-top:1rem;border:1px solid var(--border);border-radius:4px;overflow:hidden;"></div>';
    }

    document.getElementById('dm-body').innerHTML =
        rows.map(function(row) {
            return '<div class="detail-row"><span class="detail-key">' + row[0] +
                   '</span><span class="detail-val">' + row[1] + '</span></div>';
        }).join('') + miniMap;

    document.getElementById('detail-modal').classList.add('open');

    if (r.dest_lat && r.dest_lon && typeof L !== 'undefined') {
        setTimeout(function() {
            var mm = L.map('mini-map', { zoomControl: false, attributionControl: false });
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(mm);
            var ll = [parseFloat(r.dest_lat), parseFloat(r.dest_lon)];
            L.marker(ll).addTo(mm).bindPopup('📍 Destination').openPopup();
            mm.setView(ll, 13);
        }, 100);
    }
}
function closeDetail() { document.getElementById('detail-modal').classList.remove('open'); }

// ── Cancel Modal ──────────────────────────────────────────────
function openCancel(rid, veh) {
    document.getElementById('cancel-rid').value       = rid;
    document.getElementById('cancel-veh').textContent = veh;
    document.getElementById('cancel-modal').classList.add('open');
}
function closeCancel() { document.getElementById('cancel-modal').classList.remove('open'); }

// Close on backdrop click
['detail-modal','cancel-modal'].forEach(function(id) {
    document.getElementById(id).addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('open');
    });
});

// ── Helpers ───────────────────────────────────────────────────
function fmt(n) {
    return parseFloat(n || 0).toLocaleString('en-PH', {minimumFractionDigits:2, maximumFractionDigits:2});
}
function ucf(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; }
function formatDT(s) {
    if (!s) return '—';
    var d = new Date(s.replace(' ', 'T'));
    if (isNaN(d)) return s;
    return d.toLocaleDateString('en-PH', {month:'short', day:'numeric', year:'numeric'}) +
           ' ' + d.toLocaleTimeString('en-PH', {hour:'numeric', minute:'2-digit'});
}
</script>