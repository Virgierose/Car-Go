<?php

$step = isset($step) ? $step : 3;

// ── AUTH GATE ─────────────────────────────────────────────────
if (!isset($_SESSION['client_id'])) {
    $_SESSION['redirect_after_login'] = BASE_URL . '?page=booking-form';
    header('Location: ' . BASE_URL . '?page=login&next=booking-form');
    exit;
}

// ── GUARD: must have calculator data from step 1 ──────────────
if (empty($_SESSION['booking']['car_id'])) {
    header('Location: ' . BASE_URL . '?page=price-calculator');
    exit;
}

// ── FIXED PICKUP LOCATION (your business address) ─────────────
// 🔴 Change these to your actual business coordinates and label
define('FIXED_PICKUP_LAT',  10.6765);
define('FIXED_PICKUP_LON',  122.9509);
define('FIXED_PICKUP_LABEL', 'CarGo Main Branch — Bacolod City');

// ── HANDLE POST ───────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['booking']['first_name']        = trim($_POST['first_name']        ?? '');
    $_SESSION['booking']['last_name']         = trim($_POST['last_name']         ?? '');
    $_SESSION['booking']['email']             = trim($_POST['email']             ?? '');
    $_SESSION['booking']['phone']             = trim($_POST['phone']             ?? '');
    $_SESSION['booking']['pickup_datetime']   = trim($_POST['pickup_datetime']   ?? '');
    $_SESSION['booking']['return_datetime']   = trim($_POST['return_datetime']   ?? '');
    $_SESSION['booking']['notes']             = trim($_POST['notes']             ?? '');
    // Fixed pickup is always the branch
    $_SESSION['booking']['pickup_location']   = FIXED_PICKUP_LABEL;
    // Destination pinned by user
    $_SESSION['booking']['destination_label'] = trim($_POST['destination_label'] ?? '');
    $_SESSION['booking']['destination_lat']   = trim($_POST['destination_lat']   ?? '');
    $_SESSION['booking']['destination_lon']   = trim($_POST['destination_lon']   ?? '');
    $_SESSION['booking']['distance_km']       = trim($_POST['distance_km']       ?? '');
    header('Location: ' . BASE_URL . '?page=payment');
    exit;
}
?>

<?php include '_booking_styles.php'; ?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
#map { height:380px; border:1px solid rgba(255,255,255,.1); margin-bottom:1rem; }
.map-controls { display:flex; gap:.8rem; flex-wrap:wrap; margin-bottom:.9rem; align-items:center; }
.map-btn {
  padding:.6rem 1.1rem; border:none; cursor:pointer; font-size:.8rem; font-weight:700;
  letter-spacing:.08em; text-transform:uppercase; transition:all .2s;
  font-family:'Barlow Condensed',sans-serif;
}
.map-btn-red   { background:var(--crimson,#c0392b); color:#fff; }
.map-btn-red:hover { background:#a03225; transform:translateY(-1px); box-shadow:0 4px 12px rgba(192,57,43,.4); }
.map-btn-ghost { background:transparent; color:rgba(255,255,255,.5); border:1px solid rgba(255,255,255,.15); }
.map-btn-ghost:hover { color:#fff; border-color:rgba(255,255,255,.35); }
#map-status { font-size:.78rem; color:var(--crimson-soft,#e05252); font-weight:600; margin-bottom:.8rem;
              min-height:1.1rem; }
.map-info   { display:flex; gap:1.5rem; font-size:.8rem; margin-bottom:.9rem; color:rgba(255,255,255,.5); }
.map-info span strong { color:#fff; font-family:'Barlow Condensed',sans-serif; font-size:1rem; }
.dest-preview {
  background:rgba(192,57,43,.07); border:1px solid rgba(192,57,43,.2);
  padding:.7rem 1rem; font-size:.8rem; color:rgba(255,255,255,.7);
  margin-bottom:.9rem; display:none;
}
.dest-preview strong { color:var(--crimson-soft,#e05252); }
.fixed-pickup-badge {
  background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08);
  padding:.6rem 1rem; font-size:.78rem; color:rgba(255,255,255,.5); margin-bottom:1rem;
  display:flex; align-items:center; gap:.5rem;
}
.fixed-pickup-badge strong { color:rgba(255,255,255,.85); }
</style>

<div class="bk-hero">
  <span class="bk-label">Booking — Step <?= $step ?> of 5</span>
  <h1 class="bk-title">Your Details</h1>
  <p class="bk-sub">Fill in your information to continue with the booking.</p>
</div>

<div class="steps-wrap">
  <?php $currentStep = $step; include '_steps.php'; ?>
</div>

<section class="bk-section">
  <div class="bk-container">
    <div class="flow-card">
      <h2>Personal Information</h2>

      <form method="POST" action="<?= BASE_URL ?>?page=booking-form" id="booking-form">

        <div class="grid-2">
          <div class="form-group">
            <label class="form-label">First Name</label>
            <input type="text" name="first_name" class="form-control"
              value="<?= htmlspecialchars($_SESSION['booking']['first_name'] ?? $_SESSION['client_fname'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Last Name</label>
            <input type="text" name="last_name" class="form-control"
              value="<?= htmlspecialchars($_SESSION['booking']['last_name'] ?? $_SESSION['client_lname'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control"
              value="<?= htmlspecialchars($_SESSION['booking']['email'] ?? $_SESSION['client_email'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Phone Number</label>
            <input type="tel" name="phone" class="form-control" placeholder="+63 9XX XXX XXXX"
              value="<?= htmlspecialchars($_SESSION['booking']['phone'] ?? '') ?>" required>
          </div>
        </div>

        <hr class="section-divider">
        <h2 style="margin-bottom:1.4rem;">Pickup &amp; Return Dates</h2>

        <div class="grid-2">
          <div class="form-group">
            <label class="form-label">Pickup Date &amp; Time</label>
            <input type="datetime-local" name="pickup_datetime" class="form-control"
              value="<?= htmlspecialchars($_SESSION['booking']['pickup_datetime'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Return Date &amp; Time</label>
            <input type="datetime-local" name="return_datetime" class="form-control"
              value="<?= htmlspecialchars($_SESSION['booking']['return_datetime'] ?? '') ?>" required>
          </div>
        </div>

        <hr class="section-divider">
        <h2 style="margin-bottom:.6rem;">📍 Set Your Destination</h2>
        <p style="font-size:.78rem;color:rgba(255,255,255,.4);margin-bottom:1rem;">
          Your car will be picked up from our branch. Pin your destination on the map so we can calculate the distance.
        </p>

        <!-- Fixed pickup badge -->
        <div class="fixed-pickup-badge">
          <span style="color:var(--crimson-soft,#e05252);">🏢</span>
          <span>Pickup from: <strong><?= htmlspecialchars(FIXED_PICKUP_LABEL) ?></strong></span>
        </div>

        <!-- Map controls -->
        <div class="map-controls">
          <button type="button" class="map-btn map-btn-red" id="btnSetDest">
            📍 Click to Pin Destination
          </button>
          <button type="button" class="map-btn map-btn-ghost" id="btnCalcRoute" disabled>
            🗺 Calculate Distance
          </button>
          <button type="button" class="map-btn map-btn-ghost" id="btnClearDest">
            ✕ Clear Pin
          </button>
        </div>

        <p id="map-status">Click "Pin Destination" then click on the map.</p>

        <div class="map-info">
          <span>📏 Distance: <strong id="distance">—</strong></span>
          <span>⏱ ETA: <strong id="eta">—</strong></span>
        </div>

        <!-- Destination preview -->
        <div class="dest-preview" id="dest-preview">
          📍 Destination: <strong id="dest-label-display">—</strong>
        </div>

        <div id="map"></div>

        <!-- Hidden fields -->
        <input type="hidden" id="destination_label" name="destination_label">
        <input type="hidden" id="destination_lat"   name="destination_lat">
        <input type="hidden" id="destination_lon"   name="destination_lon">
        <input type="hidden" id="distance_km"       name="distance_km">

        <hr class="section-divider">
        <h2 style="margin-bottom:1.4rem;">Additional Notes</h2>

        <div class="form-group">
          <label class="form-label">Special Requests (Optional)</label>
          <textarea name="notes" class="form-control" rows="3"
            style="resize:vertical;" placeholder="Child seat, airport pickup, accessibility needs…"><?= htmlspecialchars($_SESSION['booking']['notes'] ?? '') ?></textarea>
        </div>

        <div class="bk-actions">
          <a href="<?= BASE_URL ?>?page=driver-selection" class="btn btn-ghost">← Back</a>
          <button type="submit" class="btn btn-red">Next: Payment →</button>
        </div>

      </form>
    </div>
  </div>
</section>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

  // ── Fixed pickup coords from PHP ─────────────────────────────
  var PICKUP_LAT = <?= FIXED_PICKUP_LAT ?>;
  var PICKUP_LON = <?= FIXED_PICKUP_LON ?>;

  // ── Init map centered on pickup ───────────────────────────────
  var map = L.map('map').setView([PICKUP_LAT, PICKUP_LON], 13);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
  }).addTo(map);

  // ── Fixed pickup marker (cannot be moved) ─────────────────────
  var pickupIcon = L.divIcon({
    html: '<div style="background:#c0392b;width:14px;height:14px;border-radius:50%;border:3px solid #fff;box-shadow:0 0 0 2px #c0392b;"></div>',
    className: '', iconAnchor: [7, 7]
  });
  L.marker([PICKUP_LAT, PICKUP_LON], { icon: pickupIcon, interactive: false })
    .addTo(map)
    .bindPopup('🏢 <?= addslashes(FIXED_PICKUP_LABEL) ?>')
    .openPopup();

  // ── State ──────────────────────────────────────────────────────
  var destMarker  = null;
  var routeLine   = null;
  var pinningMode = false;
  var destCoords  = null;

  // ── Helpers ───────────────────────────────────────────────────
  function setStatus(msg) { document.getElementById('map-status').textContent = msg; }

  function haversine(lat1, lon1, lat2, lon2) {
    var R = 6371;
    var dLat = (lat2 - lat1) * Math.PI / 180;
    var dLon = (lon2 - lon1) * Math.PI / 180;
    var a = Math.sin(dLat/2) * Math.sin(dLat/2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
            Math.sin(dLon/2) * Math.sin(dLon/2);
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  }

  function reverseGeocode(lat, lon, callback) {
    fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lon)
      .then(function(r){ return r.json(); })
      .then(function(d){ callback(d.display_name || (lat.toFixed(5) + ', ' + lon.toFixed(5))); })
      .catch(function(){ callback(lat.toFixed(5) + ', ' + lon.toFixed(5)); });
  }

  function placeDestPin(lat, lon) {
    // Remove old marker and route
    if (destMarker) map.removeLayer(destMarker);
    if (routeLine)  map.removeLayer(routeLine);

    var destIcon = L.divIcon({
      html: '<div style="background:#2ecc71;width:14px;height:14px;border-radius:50%;border:3px solid #fff;box-shadow:0 0 0 2px #2ecc71;"></div>',
      className: '', iconAnchor: [7, 7]
    });
    destMarker = L.marker([lat, lon], { icon: destIcon }).addTo(map)
      .bindPopup('📍 Your Destination');

    destCoords = { lat: lat, lon: lon };

    // Update hidden fields
    document.getElementById('destination_lat').value = lat;
    document.getElementById('destination_lon').value = lon;

    // Reverse geocode to get readable address
    setStatus('Getting address…');
    reverseGeocode(lat, lon, function(label) {
      document.getElementById('destination_label').value = label;
      document.getElementById('dest-label-display').textContent = label;
      document.getElementById('dest-preview').style.display = 'block';
      destMarker.bindPopup('📍 ' + label).openPopup();
      setStatus('✅ Destination pinned! Click "Calculate Distance" to continue.');
    });

    // Enable calculate button
    document.getElementById('btnCalcRoute').disabled = false;
    document.getElementById('btnCalcRoute').classList.remove('map-btn-ghost');
    document.getElementById('btnCalcRoute').classList.add('map-btn-red');
  }

  function calcRoute() {
    if (!destCoords) { setStatus('⚠ Pin your destination first.'); return; }
    var dist = haversine(PICKUP_LAT, PICKUP_LON, destCoords.lat, destCoords.lon);
    var eta  = Math.ceil((dist / 40) * 60);

    document.getElementById('distance').textContent = dist.toFixed(2) + ' km';
    document.getElementById('eta').textContent      = eta + ' min';
    document.getElementById('distance_km').value    = dist.toFixed(2);

    // Draw route line
    if (routeLine) map.removeLayer(routeLine);
    routeLine = L.polyline(
      [[PICKUP_LAT, PICKUP_LON], [destCoords.lat, destCoords.lon]],
      { color: '#c0392b', weight: 4, dashArray: '8 6' }
    ).addTo(map);
    map.fitBounds(routeLine.getBounds(), { padding: [40, 40] });

    setStatus('✅ Distance: ' + dist.toFixed(2) + ' km — ETA ~' + eta + ' min');
  }

  // ── Button: Pin Destination ───────────────────────────────────
  document.getElementById('btnSetDest').addEventListener('click', function () {
    pinningMode = true;
    map.getContainer().style.cursor = 'crosshair';
    setStatus('Click anywhere on the map to pin your destination.');
  });

  // ── Button: Calculate ─────────────────────────────────────────
  document.getElementById('btnCalcRoute').addEventListener('click', calcRoute);

  // ── Button: Clear ─────────────────────────────────────────────
  document.getElementById('btnClearDest').addEventListener('click', function () {
    if (destMarker) { map.removeLayer(destMarker); destMarker = null; }
    if (routeLine)  { map.removeLayer(routeLine);  routeLine  = null; }
    destCoords = null;
    document.getElementById('destination_lat').value   = '';
    document.getElementById('destination_lon').value   = '';
    document.getElementById('destination_label').value = '';
    document.getElementById('distance_km').value       = '';
    document.getElementById('distance').textContent    = '—';
    document.getElementById('eta').textContent         = '—';
    document.getElementById('dest-preview').style.display = 'none';
    document.getElementById('btnCalcRoute').disabled   = true;
    document.getElementById('btnCalcRoute').classList.add('map-btn-ghost');
    document.getElementById('btnCalcRoute').classList.remove('map-btn-red');
    setStatus('Click "Pin Destination" then click on the map.');
  });

  // ── Map click ─────────────────────────────────────────────────
  map.on('click', function (e) {
    if (!pinningMode) return;
    pinningMode = false;
    map.getContainer().style.cursor = '';
    placeDestPin(e.latlng.lat, e.latlng.lng);
  });

  // ── Form submit validation ────────────────────────────────────
  document.getElementById('booking-form').addEventListener('submit', function (e) {
    var dist = document.getElementById('distance_km').value;
    if (!dist || parseFloat(dist) === 0) {
      e.preventDefault();
      alert('⚠ Please pin your destination on the map and calculate the distance before continuing.');
    }
  });

});
</script>