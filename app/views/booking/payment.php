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
        $_SESSION['booking_confirmed'] = [
            'ref'     => $ref,
            'vehicle' => $bk['car_name']    ?? '—',
            'pickup'  => $pickup_dt,
            'return'  => $return_dt,
            'driver'  => $bk['driver_name'] ?? 'Self-Drive',
            'total'   => '₱' . number_format($total, 2),
        ];

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

/* ── QR MODAL ────────────────────────────────────────── */
#qr-modal {
  display:none;
  position:fixed; inset:0; z-index:9999;
  background:rgba(0,0,0,.82);
  backdrop-filter:blur(6px);
  align-items:center; justify-content:center;
}
#qr-modal.open { display:flex; }

.qr-box {
  background:#111113;
  border:1px solid rgba(255,255,255,.08);
  padding:2rem 2.2rem 1.8rem;
  max-width:360px; width:100%;
  text-align:center;
  position:relative;
  animation: qrSlideIn .3s ease;
}
@keyframes qrSlideIn {
  from { transform:translateY(24px); opacity:0; }
  to   { transform:translateY(0);    opacity:1; }
}

.qr-brand {
  font-family:'Barlow Condensed',sans-serif;
  font-size:1.05rem; font-weight:700;
  letter-spacing:.12em; text-transform:uppercase;
  margin-bottom:1.2rem;
}
.qr-brand.gcash { color:#007DFF; }
.qr-brand.maya  { color:#00C27B; }

.qr-frame {
  width:200px; height:200px;
  margin:0 auto 1.2rem;
  border:3px solid rgba(255,255,255,.06);
  padding:10px;
  background:#fff;
  position:relative;
}

/* SVG QR pattern inside white frame */
.qr-frame svg { width:100%; height:100%; }

.qr-amount {
  font-family:'Barlow Condensed',sans-serif;
  font-size:2rem; font-weight:800;
  color:#fff; margin-bottom:.25rem;
}
.qr-ref {
  font-size:.7rem; letter-spacing:.15em; text-transform:uppercase;
  color:rgba(255,255,255,.35); margin-bottom:1.4rem;
}

/* Scan button */
.btn-simulate-scan {
  width:100%; padding:.75rem;
  background:transparent;
  border:1px solid rgba(255,255,255,.12);
  color:rgba(255,255,255,.5);
  font-size:.75rem; letter-spacing:.1em; text-transform:uppercase;
  cursor:pointer; margin-bottom:.8rem;
  transition:all .2s;
}
.btn-simulate-scan:hover { border-color:rgba(255,255,255,.3); color:rgba(255,255,255,.8); }

/* Scanning animation overlay */
.qr-scan-line {
  position:absolute; left:10px; right:10px; height:2px;
  background:linear-gradient(90deg,transparent,rgba(192,57,43,.9),transparent);
  top:10px;
  animation: scanMove 2s linear infinite;
  pointer-events:none;
}
@keyframes scanMove {
  0%   { top:10px;  opacity:1; }
  90%  { top:186px; opacity:1; }
  100% { top:10px;  opacity:0; }
}

/* Success state */
.qr-success {
  display:none;
  flex-direction:column;
  align-items:center;
  padding:1rem 0 .5rem;
}
.qr-success-icon {
  width:64px; height:64px; border-radius:50%;
  display:flex; align-items:center; justify-content:center;
  font-size:2rem; margin-bottom:1rem;
  animation: popIn .4s cubic-bezier(.34,1.56,.64,1);
}
@keyframes popIn {
  from { transform:scale(0); }
  to   { transform:scale(1); }
}
.qr-success-icon.gcash { background:rgba(0,125,255,.15); border:2px solid #007DFF; }
.qr-success-icon.maya  { background:rgba(0,194,123,.15); border:2px solid #00C27B; }
.qr-success h3 { font-family:'Barlow Condensed',sans-serif; font-size:1.3rem; font-weight:700; color:#fff; margin-bottom:.4rem; }
.qr-success p  { font-size:.8rem; color:rgba(255,255,255,.45); margin-bottom:1.2rem; }

.btn-qr-confirm {
  width:100%; padding:.85rem;
  background:var(--crimson,#c0392b); color:#fff;
  border:none; cursor:pointer;
  font-family:'Barlow Condensed',sans-serif;
  font-size:.9rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase;
  transition:opacity .2s;
}
.btn-qr-confirm:hover { opacity:.85; }

.qr-cancel {
  display:block; margin-top:.9rem;
  font-size:.72rem; color:rgba(255,255,255,.3);
  cursor:pointer; text-decoration:underline; text-underline-offset:3px;
  background:none; border:none;
}
.qr-cancel:hover { color:rgba(255,255,255,.6); }
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

        <!-- GCash prompt -->
        <div class="card-fields" id="fields-gcash"
             style="text-align:center;padding:1.2rem 0 .5rem;color:var(--text-muted);font-size:.85rem;line-height:1.8;">
          A QR code will appear for you to scan with your GCash app.
        </div>

        <!-- Maya prompt -->
        <div class="card-fields" id="fields-maya"
             style="text-align:center;padding:1.2rem 0 .5rem;color:var(--text-muted);font-size:.85rem;line-height:1.8;">
          A QR code will appear for you to scan with your Maya app.
        </div>

        <!-- Pay Later -->
        <div class="card-fields" id="fields-cod"
             style="text-align:center;padding:1.5rem 0;color:var(--text-muted);font-size:.85rem;line-height:1.8;">
          Pay upon vehicle pickup.<br>A ₱500 reservation hold may apply.
        </div>

        <form method="POST" action="<?= BASE_URL ?>?page=payment" id="payment-form">
          <input type="hidden" name="payment_method" id="hidden-pm" value="card">
          <div class="bk-actions" style="margin-top:1.5rem;">
            <a href="<?= BASE_URL ?>?page=booking-form" class="btn btn-ghost">← Back</a>
            <button type="button" class="btn btn-red" style="flex:1;justify-content:center;"
                    onclick="handlePay()">
              Confirm & Pay ₱<?= number_format($summary['total'], 2) ?> →
            </button>
          </div>
        </form>
      </div>

    </div>
  </div>
</section>

<!-- ── QR MODAL ──────────────────────────────────────────────── -->
<div id="qr-modal">
  <div class="qr-box">

    <!-- QR Pending State -->
    <div id="qr-pending">
      <div class="qr-brand" id="qr-brand-label">GCash</div>

      <div class="qr-frame">
        <!-- Fake QR SVG pattern -->
        <svg viewBox="0 0 21 21" xmlns="http://www.w3.org/2000/svg" shape-rendering="crispEdges">
          <!-- Top-left finder -->
          <rect x="0" y="0" width="7" height="7" fill="#000"/>
          <rect x="1" y="1" width="5" height="5" fill="#fff"/>
          <rect x="2" y="2" width="3" height="3" fill="#000"/>
          <!-- Top-right finder -->
          <rect x="14" y="0" width="7" height="7" fill="#000"/>
          <rect x="15" y="1" width="5" height="5" fill="#fff"/>
          <rect x="16" y="2" width="3" height="3" fill="#000"/>
          <!-- Bottom-left finder -->
          <rect x="0" y="14" width="7" height="7" fill="#000"/>
          <rect x="1" y="15" width="5" height="5" fill="#fff"/>
          <rect x="2" y="16" width="3" height="3" fill="#000"/>
          <!-- Data dots — random-looking pattern -->
          <rect x="8"  y="0"  width="1" height="1" fill="#000"/>
          <rect x="10" y="0"  width="1" height="1" fill="#000"/>
          <rect x="12" y="0"  width="1" height="1" fill="#000"/>
          <rect x="8"  y="2"  width="2" height="1" fill="#000"/>
          <rect x="11" y="2"  width="1" height="1" fill="#000"/>
          <rect x="9"  y="4"  width="1" height="1" fill="#000"/>
          <rect x="12" y="4"  width="1" height="1" fill="#000"/>
          <rect x="8"  y="6"  width="1" height="2" fill="#000"/>
          <rect x="10" y="6"  width="2" height="1" fill="#000"/>
          <rect x="13" y="6"  width="1" height="1" fill="#000"/>
          <rect x="0"  y="8"  width="1" height="1" fill="#000"/>
          <rect x="2"  y="8"  width="2" height="1" fill="#000"/>
          <rect x="5"  y="8"  width="1" height="1" fill="#000"/>
          <rect x="7"  y="8"  width="2" height="1" fill="#000"/>
          <rect x="11" y="8"  width="1" height="1" fill="#000"/>
          <rect x="13" y="8"  width="1" height="2" fill="#000"/>
          <rect x="15" y="8"  width="2" height="1" fill="#000"/>
          <rect x="19" y="8"  width="2" height="1" fill="#000"/>
          <rect x="0"  y="10" width="2" height="1" fill="#000"/>
          <rect x="4"  y="10" width="1" height="1" fill="#000"/>
          <rect x="6"  y="10" width="2" height="1" fill="#000"/>
          <rect x="9"  y="10" width="3" height="1" fill="#000"/>
          <rect x="14" y="10" width="1" height="1" fill="#000"/>
          <rect x="17" y="10" width="2" height="1" fill="#000"/>
          <rect x="1"  y="12" width="1" height="1" fill="#000"/>
          <rect x="3"  y="12" width="2" height="1" fill="#000"/>
          <rect x="7"  y="12" width="1" height="1" fill="#000"/>
          <rect x="10" y="12" width="2" height="1" fill="#000"/>
          <rect x="14" y="12" width="1" height="2" fill="#000"/>
          <rect x="16" y="12" width="1" height="1" fill="#000"/>
          <rect x="19" y="12" width="2" height="1" fill="#000"/>
          <rect x="8"  y="14" width="1" height="1" fill="#000"/>
          <rect x="10" y="14" width="1" height="1" fill="#000"/>
          <rect x="12" y="14" width="2" height="1" fill="#000"/>
          <rect x="17" y="14" width="1" height="1" fill="#000"/>
          <rect x="9"  y="16" width="2" height="1" fill="#000"/>
          <rect x="12" y="16" width="1" height="2" fill="#000"/>
          <rect x="15" y="16" width="2" height="1" fill="#000"/>
          <rect x="19" y="16" width="2" height="1" fill="#000"/>
          <rect x="8"  y="18" width="1" height="2" fill="#000"/>
          <rect x="11" y="18" width="1" height="1" fill="#000"/>
          <rect x="13" y="18" width="1" height="1" fill="#000"/>
          <rect x="16" y="18" width="1" height="2" fill="#000"/>
          <rect x="18" y="19" width="1" height="1" fill="#000"/>
          <rect x="20" y="18" width="1" height="2" fill="#000"/>
        </svg>
        <div class="qr-scan-line"></div>
      </div>

      <div class="qr-amount">₱<?= number_format($summary['total'], 2) ?></div>
      <div class="qr-ref" id="qr-ref-text">REF: —</div>

      <p style="font-size:.72rem;color:rgba(255,255,255,.3);margin-bottom:1rem;">
        Open your app → Scan QR → Confirm payment
      </p>

      <button class="btn-simulate-scan" onclick="showQRSuccess()">
        ✓ &nbsp;Tap here to simulate scan
      </button>

      <button class="qr-cancel" onclick="closeQR()">Cancel payment</button>
    </div>

    <!-- QR Success State -->
    <div class="qr-success" id="qr-success">
      <div class="qr-success-icon" id="qr-success-icon">✓</div>
      <h3>Payment Received!</h3>
      <p id="qr-success-msg">Your GCash payment has been verified.</p>
      <button class="btn-qr-confirm" onclick="submitBooking()">
        Complete Booking →
      </button>
    </div>

  </div>
</div>

<script>
var currentPM = 'card';

function selectPM(method) {
  document.querySelectorAll('.pay-opt').forEach(function(el){ el.classList.remove('selected'); });
  document.querySelectorAll('.card-fields').forEach(function(el){ el.classList.remove('visible'); });
  document.getElementById('pm-' + method).classList.add('selected');
  document.getElementById('hidden-pm').value = method;
  currentPM = method;

  if (method === 'card')        document.getElementById('fields-card').classList.add('visible');
  else if (method === 'gcash')  document.getElementById('fields-gcash').classList.add('visible');
  else if (method === 'maya')   document.getElementById('fields-maya').classList.add('visible');
  else if (method === 'cod')    document.getElementById('fields-cod').classList.add('visible');
}

// ── QR Modal logic ────────────────────────────────────────────
var qrSimTimer = null;

function handlePay() {
  if (currentPM === 'gcash' || currentPM === 'maya') {
    openQR(currentPM);
  } else {
    document.getElementById('payment-form').submit();
  }
}

function openQR(method) {
  var ref = 'CRG-' + Math.random().toString(36).substring(2,8).toUpperCase();
  document.getElementById('qr-ref-text').textContent = 'REF: ' + ref;

  var brandLabel  = document.getElementById('qr-brand-label');
  var successIcon = document.getElementById('qr-success-icon');
  var successMsg  = document.getElementById('qr-success-msg');
  brandLabel.className  = 'qr-brand ' + method;
  successIcon.className = 'qr-success-icon ' + method;
  brandLabel.textContent = method === 'gcash' ? 'GCash' : 'Maya';
  successMsg.textContent = 'Your ' + (method === 'gcash' ? 'GCash' : 'Maya') + ' payment has been verified.';

  document.getElementById('qr-pending').style.display = 'block';
  document.getElementById('qr-success').style.display = 'none';

  document.getElementById('qr-modal').classList.add('open');
}

function closeQR() {
  document.getElementById('qr-modal').classList.remove('open');
  clearTimeout(qrSimTimer);
}

function showQRSuccess() {
  document.getElementById('qr-pending').style.display = 'none';
  var s = document.getElementById('qr-success');
  s.style.display = 'flex';
}

function submitBooking() {
  closeQR();
  document.getElementById('payment-form').submit();
}

// ── Card formatters ───────────────────────────────────────────
var cardNum = document.getElementById('card-num');
if (cardNum) {
  cardNum.addEventListener('input', function () {
    var v = this.value.replace(/\D/g,'').substring(0,16);
    this.value = v.replace(/(.{4})/g,'$1 ').trim();
  });
}
var cardExp = document.getElementById('card-exp');
if (cardExp) {
  cardExp.addEventListener('input', function () {
    var v = this.value.replace(/\D/g,'').substring(0,4);
    if (v.length >= 3) v = v.substring(0,2) + ' / ' + v.substring(2);
    this.value = v;
  });
}
</script>