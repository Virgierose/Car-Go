<?php
/**
 * View: booking/payment.php — Step 4 of 5
 * Auth-gated. Reads rental from DB using rental_id passed via GET (set by BookingController::form()).
 * On POST: updates tbl_rental with payment method, redirects to confirmation.
 */

$step = 4;

// ── AUTH GATE ─────────────────────────────────────────────────
if (!isset($_SESSION['client_id'])) {
    $_SESSION['redirect_after_login'] = BASE_URL . '?page=payment';
    header('Location: ' . BASE_URL . '?page=login&next=payment');
    exit;
}

// ── GUARD: must have rental_id from BookingController::form() ─
$rental_id = (int)($_GET['rental_id'] ?? 0);

if (!$rental_id) {
    header('Location: ' . BASE_URL . '?page=price-calculator');
    exit;
}

$db  = Database::getInstance();
$uid = (int)$_SESSION['client_id'];

// ── LOAD RENTAL FROM DB ───────────────────────────────────────
$stmt = $db->prepare("
    SELECT r.*, CONCAT(cm.brand,' ',cm.model_name) AS car_name
    FROM tbl_rental r
    JOIN tbl_car ca       ON ca.car_id   = r.car_id
    JOIN tbl_car_model cm ON cm.model_id = ca.model_id
    WHERE r.rental_id = ?
      AND r.client_id = ?
      AND r.rental_status IN ('docs_pending','pending')
    LIMIT 1
");
$stmt->bind_param('ii', $rental_id, $uid);
$stmt->execute();
$rental = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$rental) {
    header('Location: ' . BASE_URL . '?page=dashboard&error=not_ready');
    exit;
}

// ── HANDLE POST: UPDATE PAYMENT METHOD & STATUS ───────────────
$db_error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = trim($_POST['payment_method'] ?? 'cod');
    $allowed_pm     = ['card', 'gcash', 'maya', 'cod'];
    if (!in_array($payment_method, $allowed_pm, true)) $payment_method = 'cod';

    $new_status = 'confirmed';

    $upd = $db->prepare("
        UPDATE tbl_rental
           SET payment_method = ?, rental_status = ?
         WHERE rental_id = ? AND client_id = ?
    ");
    $upd->bind_param('ssii', $payment_method, $new_status, $rental_id, $uid);

    if ($upd->execute()) {
        $upd->close();
        $_SESSION['booking_confirmed'] = [
            'ref'         => $rental['booking_ref'],
            'vehicle'     => $rental['car_name'],
            'pickup'      => $rental['rental_start'],
            'return'      => $rental['rental_end'],
            'driver'      => 'Self-Drive',
            'destination' => $rental['dest_address'] ?? '',
            'distance'    => $rental['distance_km']  ? $rental['distance_km'] . ' km' : '',
            'total'       => '₱' . number_format($rental['total_amount'], 2),
            'paid'        => true,
        ];
        header('Location: ' . BASE_URL . '?page=confirmation');
        exit;
    }

    $db_error = $upd->error;
    $upd->close();
}

// ── BUILD SUMMARY for display ─────────────────────────────────
$total_days = max(1, (int)$rental['total_days']);
$total_amt  = (float)$rental['total_amount'];
$daily_rate = round($total_amt / $total_days, 2);

$summary = [
    'vehicle'    => $rental['car_name'],
    'days'       => $total_days,
    'rate'       => $daily_rate,
    'driver'     => false,
    'driver_fee' => 0,
    'total'      => $total_amt,
    'trip_type'  => 'perday',
];

include '_booking_styles.php';
?>

<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/booking/payment.css?v=2.0">

<div class="bk-hero">
  <span class="bk-label">Booking — Step <?= $step ?> of 5</span>
  <h1 class="bk-title">Payment</h1>
  <p class="bk-sub">Review your booking and complete the payment.</p>
</div>

<div class="steps-wrap">
  <?php $currentStep = $step; include '_steps.php'; ?>
</div>

<?php if (!empty($db_error)): ?>
<div style="max-width:720px;margin:1rem auto;background:rgba(192,57,43,.12);border:1px solid rgba(192,57,43,.35);color:#e05252;padding:.9rem 1rem;font-size:.88rem;">
  ⚠️ Booking could not be saved. Please try again. (<?= htmlspecialchars($db_error) ?>)
</div>
<?php endif; ?>

<section class="bk-section">
  <div class="bk-container" style="max-width:960px;">
    <div class="pay-grid">

      <!-- ── ORDER SUMMARY ── -->
      <div class="flow-card">
        <h2>Order Summary</h2>
        <div class="summary-row">
          <span class="summary-key">Vehicle</span>
          <span class="summary-val"><?= htmlspecialchars($summary['vehicle']) ?></span>
        </div>
        <div class="summary-row">
          <span class="summary-key">Duration</span>
          <span class="summary-val"><?= $summary['days'] ?> day<?= $summary['days'] > 1 ? 's' : '' ?></span>
        </div>
        <div class="summary-row">
          <span class="summary-key">Daily Rate</span>
          <span class="summary-val">₱<?= number_format($summary['rate'], 2) ?></span>
        </div>
        <?php if ($summary['driver'] && $summary['driver_fee'] > 0): ?>
        <div class="summary-row">
          <span class="summary-key">Driver Fee</span>
          <span class="summary-val">₱<?= number_format($summary['driver_fee'], 2) ?></span>
        </div>
        <?php endif; ?>
        <hr class="section-divider">
        <div class="summary-row summary-total">
          <span class="summary-key">Total</span>
          <span class="summary-val">₱<?= number_format($summary['total'], 2) ?></span>
        </div>
        <div style="margin-top:1.2rem;background:rgba(192,57,43,.06);border:1px solid rgba(192,57,43,.15);padding:.9rem 1rem;font-size:.78rem;color:var(--text-muted);line-height:1.6;">
          🛡️ Your payment is secured. You will only be charged once the booking is confirmed.
        </div>
      </div>

      <!-- ── PAYMENT METHOD ── -->
      <div class="flow-card">
        <h2>Payment Method</h2>

        <div class="payment-methods">
          <div class="pay-opt selected" id="pm-card" onclick="selectPM('card')">
            <input type="radio" name="payment_method" value="card" checked>
            <span class="pay-icon">💳</span>
            <span class="pay-label">Credit Card</span>
          </div>
          <div class="pay-opt" id="pm-gcash" onclick="selectPM('gcash')">
            <input type="radio" name="payment_method" value="gcash">
            <span class="pay-icon">📱</span>
            <span class="pay-label">GCash</span>
          </div>
          <div class="pay-opt" id="pm-maya" onclick="selectPM('maya')">
            <input type="radio" name="payment_method" value="maya">
            <span class="pay-icon">🟢</span>
            <span class="pay-label">Maya</span>
          </div>
          <div class="pay-opt" id="pm-cod" onclick="selectPM('cod')">
            <input type="radio" name="payment_method" value="cod">
            <span class="pay-icon">💵</span>
            <span class="pay-label">Pay Later</span>
          </div>
        </div>

        <!-- Credit Card Fields -->
        <div class="card-fields visible" id="fields-card">
          <div class="form-group" style="margin-bottom:1rem;">
            <label class="form-label">Card Number</label>
            <input type="text" class="form-control" placeholder="1234 5678 9012 3456"
                   maxlength="19" id="card-num" autocomplete="cc-number"
                   inputmode="numeric" pattern="[0-9 ]*">
          </div>
          <div class="grid-2">
            <div class="form-group">
              <label class="form-label">Expiry</label>
              <input type="text" class="form-control" placeholder="MM / YY"
                     maxlength="7" id="card-exp" autocomplete="cc-exp"
                     inputmode="numeric">
            </div>
            <div class="form-group">
              <label class="form-label">CVV</label>
              <input type="text" class="form-control" placeholder="•••"
                     maxlength="4" autocomplete="cc-csc"
                     inputmode="numeric">
            </div>
          </div>
          <div class="form-group" style="margin-top:1rem;">
            <label class="form-label">Cardholder Name</label>
            <input type="text" class="form-control" placeholder="As printed on card"
                   autocomplete="cc-name">
          </div>
        </div>

        <!-- GCash hint -->
        <div class="card-fields" id="fields-gcash"
             style="text-align:center;padding:1.2rem 0 .5rem;color:var(--text-muted);font-size:.85rem;line-height:1.8;display:none;">
         Your GCash payment method will be saved when you confirm the booking.
        </div>

        <!-- Maya hint -->
        <div class="card-fields" id="fields-maya"
             style="text-align:center;padding:1.2rem 0 .5rem;color:var(--text-muted);font-size:.85rem;line-height:1.8;display:none;">
         Your Maya payment method will be saved when you confirm the booking.
        </div>

        <!-- Pay Later hint -->
        <div class="card-fields" id="fields-cod"
             style="text-align:center;padding:1.5rem 0;color:var(--text-muted);font-size:.85rem;line-height:1.8;display:none;">
          Pay upon vehicle pickup.<br>A ₱500 reservation hold may apply.
        </div>

        <form method="POST" action="<?= BASE_URL ?>?page=payment&rental_id=<?= $rental_id ?>" id="payment-form">
          <input type="hidden" name="payment_method" id="hidden-pm" value="card">
          <input type="hidden" name="rental_id" value="<?= $rental_id ?>">
          <div class="bk-actions" style="margin-top:1.5rem;">
            <a href="<?= BASE_URL ?>?page=booking-form" class="btn btn-ghost">← Back</a>
            <button type="button" class="btn btn-red" style="flex:1;justify-content:center;" onclick="handlePay()">
              Confirm & Pay ₱<?= number_format($summary['total'], 2) ?> →
            </button>
          </div>
        </form>
      </div>
    <script>
function handlePay() {
    document.getElementById('payment-form').submit();
}
</script>
    </div>
  </div>
</section>


