<?php
/**
 * View: booking/payment.php — Step 4 of 5
 * Auth-gated. On POST: inserts tbl_rental, clears booking session, redirects to confirmation.
 */

$step = 4;

// ── AUTH GATE ─────────────────────────────────────────────────
if (!isset($_SESSION['client_id'])) {
    $_SESSION['redirect_after_login'] = BASE_URL . '?page=payment';
    header('Location: ' . BASE_URL . '?page=login&next=payment');
    exit;
}

// ── GUARD: must have personal info from step 3 ────────────────
if (empty($_SESSION['booking']['first_name'])) {
    header('Location: ' . BASE_URL . '?page=booking-form');
    exit;
}

$bk = $_SESSION['booking'];
$db = Database::getInstance();

// ── HANDLE POST: INSERT BOOKING ───────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = trim($_POST['payment_method'] ?? 'cod');
    $allowed_pm     = ['card','gcash','maya','cod'];
    if (!in_array($payment_method, $allowed_pm)) $payment_method = 'cod';

    $uid          = (int) $_SESSION['client_id'];
    $car_id       = (int) $bk['car_id'];
    $driver_id    = !empty($bk['driver_id']) ? (int) $bk['driver_id'] : null;
    $pickup_dt    = $bk['pickup_datetime'];
    $return_dt    = $bk['return_datetime'];
    $pickup_loc   = $bk['pickup_location'];
    $return_loc   = $bk['return_location'] ?? $pickup_loc;
    $days         = (int) ($bk['days'] ?? 1);
    $total        = (float) ($bk['total'] ?? 0);
    $notes        = $bk['notes'] ?? '';
    $status       = ($payment_method === 'cod') ? 'pending' : 'confirmed';

    // Generate reference code
    $ref = 'CRG-' . strtoupper(substr(md5(uniqid('', true)), 0, 6));

    $stmt = $db->prepare("
        INSERT INTO tbl_rental
            (client_id, car_id, driver_id,
             rental_start, rental_end, total_days, total_amount,
             pickup_location, return_location,
             payment_method, rental_status, booking_ref, notes, date_created)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())
    ");
    $stmt->bind_param(
        'iiissidssssss',
        $uid, $car_id, $driver_id,
        $pickup_dt, $return_dt, $days, $total,
        $pickup_loc, $return_loc,
        $payment_method, $status, $ref, $notes
    );

    if ($stmt->execute()) {
        // Build confirmation summary
        $_SESSION['booking_confirmed'] = [
            'ref'     => $ref,
            'vehicle' => $bk['car_name']    ?? '—',
            'pickup'  => $pickup_dt,
            'return'  => $return_dt,
            'driver'  => $bk['driver_name'] ?? 'Self-Drive',
            'total'   => '₱' . number_format($total, 2),
        ];

        // Clear booking state
        unset($_SESSION['booking']);

        $stmt->close();
        header('Location: ' . BASE_URL . '?page=confirmation');
        exit;
    }

    $db_error = $db->error;
    $stmt->close();
}

// ── BUILD SUMMARY for display ─────────────────────────────────
$summary = [
    'vehicle'    => $bk['car_name']    ?? '—',
    'days'       => (int) ($bk['days']       ?? 1),
    'rate'       => (float) ($bk['car_price']  ?? 0),
    'driver'     => $bk['with_driver'] ?? false,
    'driver_fee' => (float) ($bk['driver_fee']  ?? 0),
    'total'      => (float) ($bk['total']       ?? 0),
];

include '_booking_styles.php';
?>

<style>
.payment-methods { display:grid; grid-template-columns:repeat(auto-fill,minmax(150px,1fr)); gap:.8rem; margin:1rem 0 1.5rem; }
.pay-opt {
  border:1px solid var(--border); background:var(--bg-input);
  padding:1rem; text-align:center; cursor:pointer; transition:all .2s; position:relative;
}
.pay-opt:hover { border-color:rgba(192,57,43,.3); }
.pay-opt.selected { border-color:var(--crimson); background:rgba(192,57,43,.07); }
.pay-opt input { position:absolute; opacity:0; pointer-events:none; }
.pay-icon { font-size:1.6rem; margin-bottom:.4rem; display:block; }
.pay-label { font-size:.75rem; letter-spacing:.08em; text-transform:uppercase; color:var(--text-label); }
.pay-opt.selected .pay-label { color:var(--crimson-soft); }
.card-fields { display:none; }
.card-fields.visible { display:block; }
@media(max-width:720px){
  .pay-grid { grid-template-columns:1fr !important; }
}
</style>

<div class="bk-hero">
  <span class="bk-label">Booking — Step <?= $step ?> of 5</span>
  <h1 class="bk-title">Payment</h1>
  <p class="bk-sub">Review your booking and complete the payment.</p>
</div>

<div class="steps-wrap">
  <?php $currentStep = $step; include '_steps.php'; ?>
</div>

<?php if (!empty($db_error)): ?>
<div style="max-width:720px;margin:1rem auto;background:rgba(192,57,43,.12);border:1px solid rgba(192,57,43,.35);
            color:#e05252;padding:.9rem 1rem;font-size:.88rem;">
  ⚠️ Booking could not be saved. Please try again. (<?= htmlspecialchars($db_error) ?>)
</div>
<?php endif; ?>

<section class="bk-section">
  <div class="bk-container" style="max-width:960px;">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;align-items:start;" class="pay-grid">

      <!-- ORDER SUMMARY -->
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
        <div style="margin-top:1.2rem;background:rgba(192,57,43,.06);border:1px solid rgba(192,57,43,.15);
                    padding:.9rem 1rem;font-size:.78rem;color:var(--text-muted);line-height:1.6;">
          🛡️ Your payment is secured. You will only be charged once the booking is confirmed.
        </div>
      </div>

      <!-- PAYMENT FORM -->
      <div class="flow-card">
        <h2>Payment Method</h2>

        <div class="payment-methods">
          <div class="pay-opt selected" id="pm-card" onclick="selectPM('card')">
            <input type="radio" name="payment_method" value="card" checked>
            <span class="pay-icon">💳</span><span class="pay-label">Credit Card</span>
          </div>
          <div class="pay-opt" id="pm-gcash" onclick="selectPM('gcash')">
            <input type="radio" name="payment_method" value="gcash">
            <span class="pay-icon">📱</span><span class="pay-label">GCash</span>
          </div>
          <div class="pay-opt" id="pm-maya" onclick="selectPM('maya')">
            <input type="radio" name="payment_method" value="maya">
            <span class="pay-icon">🟢</span><span class="pay-label">Maya</span>
          </div>
          <div class="pay-opt" id="pm-cod" onclick="selectPM('cod')">
            <input type="radio" name="payment_method" value="cod">
            <span class="pay-icon">💵</span><span class="pay-label">Pay Later</span>
          </div>
        </div>

        <!-- Credit Card Fields -->
        <div class="card-fields visible" id="fields-card">
          <div class="form-group" style="margin-bottom:1rem;">
            <label class="form-label">Card Number</label>
            <input type="text" class="form-control" placeholder="1234 5678 9012 3456"
                   maxlength="19" id="card-num" autocomplete="cc-number">
          </div>
          <div class="grid-2">
            <div class="form-group">
              <label class="form-label">Expiry</label>
              <input type="text" class="form-control" placeholder="MM / YY" maxlength="7"
                     id="card-exp" autocomplete="cc-exp">
            </div>
            <div class="form-group">
              <label class="form-label">CVV</label>
              <input type="text" class="form-control" placeholder="•••" maxlength="4"
                     autocomplete="cc-csc">
            </div>
          </div>
          <div class="form-group" style="margin-top:1rem;">
            <label class="form-label">Cardholder Name</label>
            <input type="text" class="form-control" placeholder="As printed on card"
                   autocomplete="cc-name">
          </div>
        </div>

        <!-- GCash / Maya -->
        <div class="card-fields" id="fields-ewallet"
             style="text-align:center;padding:1.5rem 0;color:var(--text-muted);font-size:.85rem;line-height:1.8;">
          You will be redirected to complete payment<br>after confirming your booking.
        </div>

        <!-- Pay Later -->
        <div class="card-fields" id="fields-cod"
             style="text-align:center;padding:1.5rem 0;color:var(--text-muted);font-size:.85rem;line-height:1.8;">
          Pay upon vehicle pickup.<br>A ₱500 reservation hold may apply.
        </div>

        <form method="POST" action="<?= BASE_URL ?>?page=payment">
          <input type="hidden" name="payment_method" id="hidden-pm" value="card">
          <div class="bk-actions" style="margin-top:1.5rem;">
            <a href="<?= BASE_URL ?>?page=booking-form" class="btn btn-ghost">← Back</a>
            <button type="submit" class="btn btn-red" style="flex:1;justify-content:center;">
              Confirm & Pay ₱<?= number_format($summary['total'], 2) ?> →
            </button>
          </div>
        </form>
      </div>

    </div>
  </div>
</section>

<script>
function selectPM(method) {
  document.querySelectorAll('.pay-opt').forEach(function(el){ el.classList.remove('selected'); });
  document.querySelectorAll('.card-fields').forEach(function(el){ el.classList.remove('visible'); });
  document.getElementById('pm-' + method).classList.add('selected');
  document.getElementById('hidden-pm').value = method;
  if (method === 'card')                      document.getElementById('fields-card').classList.add('visible');
  else if (method === 'gcash' || method === 'maya') document.getElementById('fields-ewallet').classList.add('visible');
  else if (method === 'cod')                  document.getElementById('fields-cod').classList.add('visible');
}

// Card number formatter
var cardNum = document.getElementById('card-num');
if (cardNum) {
  cardNum.addEventListener('input', function () {
    var v = this.value.replace(/\D/g,'').substring(0,16);
    this.value = v.replace(/(.{4})/g,'$1 ').trim();
  });
}

// Expiry formatter
var cardExp = document.getElementById('card-exp');
if (cardExp) {
  cardExp.addEventListener('input', function () {
    var v = this.value.replace(/\D/g,'').substring(0,4);
    if (v.length >= 3) v = v.substring(0,2) + ' / ' + v.substring(2);
    this.value = v;
  });
}
</script>