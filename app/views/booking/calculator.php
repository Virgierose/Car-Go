<?php

$step = isset($step) ? $step : 1;

// ── HANDLE POST: save calculator selections to session ────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['booking'] = [
        'car_id'       => (int)   ($_POST['car_id']      ?? 0),
        'car_name'     =>          $_POST['car_name']     ?? '',
        'car_price'    => (float) ($_POST['car_price']   ?? 0),
        'driver_fee'   => (float) ($_POST['driver_fee']  ?? 0),
        'with_driver'  => ($_POST['with_driver'] ?? '0') === '1',
        'trip_type'    =>          $_POST['trip_type']    ?? 'perday',
        'pickup_date'  =>          $_POST['pickup_date']  ?? '',
        'return_date'  =>          $_POST['return_date']  ?? '',
        'days'         => (int)   ($_POST['days']         ?? 1),
        'total'        => (float) ($_POST['total']        ?? 0),
    ];
    header('Location: ' . BASE_URL . '?page=driver-selection');
    exit;
}
?>

<?php include '_booking_styles.php'; ?>

<div class="bk-hero">
  <span class="bk-label">Booking — Step <?= $step ?> of 5</span>
  <h1 class="bk-title">Price Calculator</h1>
  <p class="bk-sub">Enter your trip details to get an instant estimate.</p>
</div>

<div class="steps-wrap">
  <?php $currentStep = $step; include '_steps.php'; ?>
</div>

<section class="bk-section">
  <div class="bk-container">
    <div class="flow-card">
      <h2>Trip Details</h2>

      <div class="grid-2">
        <div class="form-group">
          <label class="form-label">Vehicle</label>
          <select class="form-control" id="car-select">
            <?php if(!empty($cars)): ?>
              <?php foreach ($cars as $car): ?>
                <option value="<?= 0 ?>" data-id="<?= (int)$car['car_id'] ?>" data-name="<?= htmlspecialchars($car['brand'] . ' ' . $car['model_name']) ?>" data-image="<?= htmlspecialchars($car['image'] ?? '') ?>">
                  <?= htmlspecialchars($car['brand'] . ' ' . $car['model_name']) ?> — ₱0/day
                </option>
              <?php endforeach; ?>
            <?php else: ?>
              <option value="0">No vehicles available</option>
            <?php endif; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Trip Type</label>
          <select class="form-control" id="trip-type">
            <option value="perday">Per Day</option>
            <option value="halfday">Half Day (4 hrs) — 60% of daily rate</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Pickup Date</label>
          <input type="date" class="form-control" id="pickup-date">
        </div>
        <div class="form-group">
          <label class="form-label">Return Date</label>
          <input type="date" class="form-control" id="return-date">
        </div>
      </div>

      <div class="form-group" style="margin-top:1.2rem;">
        <label class="form-label">Driver</label>
        <select class="form-control" id="driver-select">
          <option value="0">No — Self-Drive</option>
          <option value="500">Yes — With Driver (+₱500/day)</option>
        </select>
      </div>

      
      <div style="text-align:center; background:linear-gradient(135deg,rgba(192,57,43,0.07),rgba(17,17,19,0.9)); border:1px solid rgba(192,57,43,0.18); padding:2rem; margin-top:1.8rem;">
        <p style="font-family:'Barlow Condensed',sans-serif; font-size:.7rem; letter-spacing:.2em; text-transform:uppercase; color:var(--text-muted); margin-bottom:.4rem;">Estimated Total</p>
        <p style="font-family:'Barlow Condensed',sans-serif; font-size:4rem; font-weight:800; color:var(--crimson-soft); line-height:1;" id="price-display">₱0.00</p>
        <p style="font-size:.75rem; color:var(--text-muted); margin-top:.5rem;" id="price-breakdown">Select dates and a vehicle to calculate.</p>
        <p style="font-size:.7rem; color:var(--text-muted); margin-top:.3rem; opacity:.6;">* Final price confirmed at checkout. Fuel not included.</p>
      </div>

      <form method="POST" action="<?= BASE_URL ?>?page=price-calculator" id="calc-form">
        <input type="hidden" name="car_id"       id="h-car-id">
        <input type="hidden" name="car_name"     id="h-car-name">
        <input type="hidden" name="car_price"    id="h-car-price">
        <input type="hidden" name="driver_fee"   id="h-driver-fee">
        <input type="hidden" name="with_driver"  id="h-with-driver">
        <input type="hidden" name="trip_type"    id="h-trip-type">
        <input type="hidden" name="pickup_date"  id="h-pickup-date">
        <input type="hidden" name="return_date"  id="h-return-date">
        <input type="hidden" name="days"         id="h-days">
        <input type="hidden" name="total"        id="h-total">
        <div class="bk-actions">
          <a href="<?= BASE_URL ?>?page=browse" class="btn btn-ghost">← Back</a>
          <button type="submit" class="btn btn-red" onclick="return submitCalc()">Next: Choose Driver →</button>
        </div>
      </form>
    </div>
  </div>
</section>

<script>
var carSelect    = document.getElementById('car-select');
var driverSelect = document.getElementById('driver-select');
var tripType     = document.getElementById('trip-type');
var pickupDate   = document.getElementById('pickup-date');
var returnDate   = document.getElementById('return-date');
var priceDisplay = document.getElementById('price-display');
var breakdown    = document.getElementById('price-breakdown');


var today    = new Date();
var tomorrow = new Date(); tomorrow.setDate(today.getDate() + 1);
pickupDate.min   = today.toISOString().split('T')[0];
pickupDate.value = today.toISOString().split('T')[0];
returnDate.min   = tomorrow.toISOString().split('T')[0];
returnDate.value = tomorrow.toISOString().split('T')[0];

function getDays() {
  var p = new Date(pickupDate.value);
  var r = new Date(returnDate.value);
  if (!pickupDate.value || !returnDate.value || r <= p) return 0;
  return Math.ceil((r - p) / (1000 * 60 * 60 * 24));
}

function fmt(n) { return '₱' + n.toLocaleString('en-PH', {minimumFractionDigits:2, maximumFractionDigits:2}); }

function updatePrice() {
  var dailyRate = parseFloat(carSelect.value) || 0;
  var driverFee = parseFloat(driverSelect.value) || 0;
  var type      = tripType.value;
  var days      = (type === 'halfday') ? 1 : getDays();

  if (days <= 0 && type !== 'halfday') {
    priceDisplay.textContent = '₱0.00';
    breakdown.textContent    = 'Return date must be after pickup date.';
    return;
  }

  var carTotal, rateLabel;
  if (type === 'halfday') {
    var halfRate = dailyRate * 0.6;
    carTotal  = halfRate;
    rateLabel = fmt(dailyRate) + '/day × 60% (half-day) = ' + fmt(halfRate);
  } else {
    carTotal  = dailyRate * days;
    rateLabel = fmt(dailyRate) + '/day × ' + days + ' day' + (days > 1 ? 's' : '') + ' = ' + fmt(carTotal);
  }

  var driverTotal = driverFee * days;
  var total       = carTotal + driverTotal;

  priceDisplay.textContent = fmt(total);

  var parts = [rateLabel];
  if (driverFee > 0) {
    parts.push('Driver ' + fmt(driverFee) + '/day × ' + days + ' day' + (days > 1 ? 's' : '') + ' = ' + fmt(driverTotal));
  }
  breakdown.textContent = parts.join('   +   ');
}

pickupDate.addEventListener('change', function() {
  var p = new Date(pickupDate.value); p.setDate(p.getDate() + 1);
  returnDate.min = p.toISOString().split('T')[0];
  if (new Date(returnDate.value) <= new Date(pickupDate.value)) {
    returnDate.value = p.toISOString().split('T')[0];
  }
  updatePrice();
});

tripType.addEventListener('change', function() {
  if (tripType.value === 'halfday') {
    returnDate.value    = pickupDate.value;
    returnDate.disabled = true;
  } else {
    returnDate.disabled = false;
    var p = new Date(pickupDate.value); p.setDate(p.getDate() + 1);
    returnDate.value = p.toISOString().split('T')[0];
  }
  updatePrice();
});

returnDate.addEventListener('change', updatePrice);
carSelect.addEventListener('change', updatePrice);
driverSelect.addEventListener('change', updatePrice);
updatePrice();

function submitCalc() {
  var selectedOpt = carSelect.options[carSelect.selectedIndex];
  var days        = (tripType.value === 'halfday') ? 1 : getDays();
  if (days <= 0) { alert('Please select valid pickup and return dates.'); return false; }

  document.getElementById('h-car-id').value      = selectedOpt.getAttribute('data-id') || 0;
  document.getElementById('h-car-name').value    = selectedOpt.getAttribute('data-name') || selectedOpt.text;
  document.getElementById('h-car-price').value   = parseFloat(carSelect.value) || 0;
  document.getElementById('h-driver-fee').value  = parseFloat(driverSelect.value) || 0;
  document.getElementById('h-with-driver').value = (parseFloat(driverSelect.value) > 0) ? '1' : '0';
  document.getElementById('h-trip-type').value   = tripType.value;
  document.getElementById('h-pickup-date').value = pickupDate.value;
  document.getElementById('h-return-date').value = returnDate.value;
  document.getElementById('h-days').value        = days;

  var dailyRate   = parseFloat(carSelect.value) || 0;
  var driverFee   = parseFloat(driverSelect.value) || 0;
  var carTotal    = (tripType.value === 'halfday') ? dailyRate * 0.6 : dailyRate * days;
  document.getElementById('h-total').value = carTotal + driverFee * days;
  return true;
}
</script>