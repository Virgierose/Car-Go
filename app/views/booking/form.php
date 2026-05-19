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

// ── HANDLE POST: save personal + pickup info to session ───────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['booking']['first_name']       = trim($_POST['first_name']       ?? '');
    $_SESSION['booking']['last_name']        = trim($_POST['last_name']        ?? '');
    $_SESSION['booking']['email']            = trim($_POST['email']            ?? '');
    $_SESSION['booking']['phone']            = trim($_POST['phone']            ?? '');
    $_SESSION['booking']['pickup_datetime']  = trim($_POST['pickup_datetime']  ?? '');
    $_SESSION['booking']['return_datetime']  = trim($_POST['return_datetime']  ?? '');
    $_SESSION['booking']['notes']            = trim($_POST['notes']            ?? '');
    // ── Map coordinates and distance ──────────────────────────
    $_SESSION['booking']['pickup_lat']       = trim($_POST['pickup_lat']       ?? '');
    $_SESSION['booking']['pickup_lon']       = trim($_POST['pickup_lon']       ?? '');
    $_SESSION['booking']['delivery_lat']     = trim($_POST['delivery_lat']     ?? '');
    $_SESSION['booking']['delivery_lon']     = trim($_POST['delivery_lon']     ?? '');
    $_SESSION['booking']['distance_km']      = trim($_POST['distance_km']      ?? '');
    header('Location: ' . BASE_URL . '?page=payment');
    exit;
}
?>

<?php include '_booking_styles.php'; ?>

<!-- ── Leaflet CSS (add this if not already in your header.php) ── -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
  #map { height: 380px; border-radius: 10px; border: 1px solid #ddd; margin-bottom: 1rem; }
  .map-controls { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 12px; align-items: center; }
  .map-controls button { padding: 10px 18px; border-radius: 8px; border: none; cursor: pointer; font-size: 15px; font-weight: 600; transition: all 0.2s; }
  #btnSetFrom  { background: #C0392B; color: #fff; }
  #btnSetFrom:hover { background: #A03225; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(192, 57, 43, 0.3); }
  #btnSetTo    { background: #C0392B; color: #fff; }
  #btnSetTo:hover { background: #A03225; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(192, 57, 43, 0.3); }
  #btnRoute    { background: #C0392B; color: #fff; }
  #btnRoute:hover { background: #A03225; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(192, 57, 43, 0.3); }
  #map-status  { font-size: 16px; color: #C0392B; font-weight: 600; margin-bottom: 12px; }
  .map-info    { display: flex; gap: 24px; font-size: 17px; margin-bottom: 12px; font-weight: 600; }
  .map-info span strong { color: #C0392B; }
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

      <form method="POST" action="<?= BASE_URL ?>?page=booking-form">

        <div class="grid-2">
          <div class="form-group">
            <label class="form-label">First Name</label>
            <input type="text" name="first_name" class="form-control"
              value="<?= htmlspecialchars($_SESSION['user_firstname'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Last Name</label>
            <input type="text" name="last_name" class="form-control"
              value="<?= htmlspecialchars($_SESSION['user_lastname'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control"
              value="<?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Phone Number</label>
            <input type="tel" name="phone" class="form-control" placeholder="+63 9XX XXX XXXX" required>
          </div>
        </div>

        <hr class="section-divider">
        <h2 style="margin-bottom:1.4rem;">Pickup & Return Dates</h2>

        <div class="grid-2">
          <div class="form-group">
            <label class="form-label">Pickup Date & Time</label>
            <input type="datetime-local" name="pickup_datetime" class="form-control" required>
          </div>
          <div class="form-group">
            <label class="form-label">Return Date & Time</label>
            <input type="datetime-local" name="return_datetime" class="form-control" required>
          </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════
             MAP SECTION — Users pin their pickup and delivery on the map
             The route + distance/ETA are calculated automatically.
        ════════════════════════════════════════════════════════ -->
        <hr class="section-divider">
        <h2 style="margin-bottom:1rem;">📍 Pin Your Location on Map</h2>
        <p style="font-size:14px; color:#6b7280; margin-bottom:1rem;">
          Use the map to mark your exact pickup and delivery points.
          This helps us calculate the distance and estimated travel time.
        </p>

        <!-- Map action buttons -->
        <div class="map-controls">
          <button type="button" id="btnSetFrom">📦 Set Pickup Pin</button>
          <button type="button" id="btnSetTo">🏠 Set Delivery Pin</button>
          <button type="button" id="btnRoute">🗺️ Calculate Route</button>
        </div>

        <!-- Status message -->
        <p id="map-status">Click "Set Pickup Pin" then click on the map to begin.</p>

        <!-- Distance + ETA display -->
        <div class="map-info">
          <span>📏 Distance: <strong id="distance">--</strong></span>
          <span>⏱️ ETA: <strong id="eta">--</strong></span>
        </div>

        <!-- The Leaflet map renders here -->
        <div id="map"></div>

        <!-- Hidden fields — these get submitted with the form -->
        <input type="hidden" id="pickup_lat"   name="pickup_lat">
        <input type="hidden" id="pickup_lon"   name="pickup_lon">
        <input type="hidden" id="delivery_lat" name="delivery_lat">
        <input type="hidden" id="delivery_lon" name="delivery_lon">
        <input type="hidden" id="distance_km"  name="distance_km">
        <!-- ═══════════════════════════════════════════════════════ -->

        <hr class="section-divider">
        <h2 style="margin-bottom:1.4rem;">Additional Notes</h2>

        <div class="form-group">
          <label class="form-label">Special Requests (Optional)</label>
          <textarea name="notes" class="form-control" rows="3"
            style="resize:vertical;" placeholder="Child seat, airport pickup, accessibility needs…"></textarea>
        </div>

        <div class="bk-actions">
          <a href="<?= BASE_URL ?>?page=driver-selection" class="btn btn-ghost">← Back</a>
          <button type="submit" class="btn btn-red">Next: Payment →</button>
        </div>

      </form>
    </div>
  </div>
</section>

<!-- ── Leaflet JS ─────────────────────────────────────────── -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- ── MODEL: stores data + calls routing calculation ────────── -->
<script>
class DeliveryModel {
  constructor() {
    this.fromCoords  = null;
    this.toCoords    = null;
    this.routeCoords = [];
    this.distanceKm  = 0;
    this.etaMinutes  = 0;
  }

  // Haversine formula to calculate distance between two coordinates
  calculateDistance(from, to) {
    const R = 6371; // Earth's radius in km
    const dLat = (to.lat - from.lat) * Math.PI / 180;
    const dLon = (to.lon - from.lon) * Math.PI / 180;
    const a = 
      Math.sin(dLat/2) * Math.sin(dLat/2) +
      Math.cos(from.lat * Math.PI / 180) * Math.cos(to.lat * Math.PI / 180) *
      Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
  }

  async getRoute(from, to) {
    try {
      // Calculate straight-line distance (faster, no external API)
      const distanceKm = this.calculateDistance(from, to);
      this.distanceKm = distanceKm.toFixed(2);
      
      // Estimate time: average speed 40 km/h in city
      this.etaMinutes = Math.ceil((distanceKm / 40) * 60);
      
      // Create a simple route line between the two points
      this.routeCoords = [[from.lat, from.lon], [to.lat, to.lon]];
      
      console.log('Distance calculated:', this.distanceKm, 'km, ETA:', this.etaMinutes, 'min');
      return this.routeCoords;
    } catch (error) {
      console.error('Route calculation error:', error);
      throw error;
    }
  }
}
</script>

<!-- ── VIEW: manages the Leaflet map display ──────────────── -->
<script>
class DeliveryView {
  constructor() {
    // 🔴 Change these coordinates to your city's center
    // Current default: Bacolod City, Philippines
    this.map = L.map('map').setView([10.6765, 122.9509], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap contributors'
    }).addTo(this.map);

    this.fromMarker = null;
    this.toMarker   = null;
    this.routeLine  = null;
  }

  setFromMarker(coords) {
    if (this.fromMarker) this.map.removeLayer(this.fromMarker);
    this.fromMarker = L.marker([coords.lat, coords.lon])
      .addTo(this.map)
      .bindPopup('📦 Pickup Location')
      .openPopup();
  }

  setToMarker(coords) {
    if (this.toMarker) this.map.removeLayer(this.toMarker);
    this.toMarker = L.marker([coords.lat, coords.lon])
      .addTo(this.map)
      .bindPopup('🏠 Delivery Location')
      .openPopup();
  }

  displayRoute(routeCoords) {
    if (this.routeLine) this.map.removeLayer(this.routeLine);
    this.routeLine = L.polyline(routeCoords, {
      color: '#C0392B',  // red line — matches theme
      weight: 5
    }).addTo(this.map);
    this.map.fitBounds(this.routeLine.getBounds());
  }

  updateInfo(distanceKm, etaMinutes) {
    document.getElementById('distance').textContent = distanceKm + ' km';
    document.getElementById('eta').textContent      = etaMinutes + ' min';
  }

  updateStatus(msg) {
    document.getElementById('map-status').textContent = msg;
  }
}
</script>

<!-- ── CONTROLLER: wires buttons + map clicks together ─────── -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  console.log('=== Booking Form Script Loaded ===');

  // Check if hidden fields exist
  const requiredFields = ['pickup_lat', 'pickup_lon', 'delivery_lat', 'delivery_lon', 'distance_km'];
  requiredFields.forEach(fieldId => {
    const field = document.getElementById(fieldId);
    console.log('Field ' + fieldId + ':', field ? 'FOUND ✓' : 'NOT FOUND ✗');
  });

  const model = new DeliveryModel();
  console.log('DeliveryModel instance created');

  const view  = new DeliveryView();
  console.log('DeliveryView instance created - Map initialized');

  let pinMode = null; // tracks which pin we're placing next

  console.log('Attaching event listeners...');

  // "Set Pickup Pin" button
  const btnSetFrom = document.getElementById('btnSetFrom');
  if (btnSetFrom) {
    btnSetFrom.addEventListener('click', () => {
      console.log('Set Pickup Pin button clicked');
      pinMode = 'from';
      view.updateStatus('Click anywhere on the map to set the PICKUP location.');
    });
    console.log('btnSetFrom listener attached ✓');
  } else {
    console.error('btnSetFrom button NOT FOUND');
  }

  // "Set Delivery Pin" button
  const btnSetTo = document.getElementById('btnSetTo');
  if (btnSetTo) {
    btnSetTo.addEventListener('click', () => {
      console.log('Set Delivery Pin button clicked');
      pinMode = 'to';
      view.updateStatus('Click anywhere on the map to set the DELIVERY location.');
    });
    console.log('btnSetTo listener attached ✓');
  } else {
    console.error('btnSetTo button NOT FOUND');
  }

  // Map click — places the active pin
  view.map.on('click', (e) => {
    console.log('Map clicked at:', e.latlng);
    const coords = { lat: e.latlng.lat, lon: e.latlng.lng };
    console.log('Coordinates object:', coords);

    if (pinMode === 'from') {
      console.log('Setting pickup coordinates');
      model.fromCoords = coords;
      view.setFromMarker(coords);
      // Save to hidden fields for PHP form submission
      document.getElementById('pickup_lat').value = coords.lat;
      document.getElementById('pickup_lon').value = coords.lon;
      console.log('Pickup lat:', coords.lat, 'Pickup lon:', coords.lon);
      view.updateStatus('✅ Pickup pinned! Now click "Set Delivery Pin".');

    } else if (pinMode === 'to') {
      console.log('Setting delivery coordinates');
      model.toCoords = coords;
      view.setToMarker(coords);
      // Save to hidden fields
      document.getElementById('delivery_lat').value = coords.lat;
      document.getElementById('delivery_lon').value = coords.lon;
      console.log('Delivery lat:', coords.lat, 'Delivery lon:', coords.lon);
      view.updateStatus('✅ Delivery pinned! Click "Calculate Route" to see the path.');
    } else {
      console.log('Map clicked but no pin mode active');
    }

    pinMode = null; // reset mode after placing pin
  });

  // "Calculate Route" button
  const btnRoute = document.getElementById('btnRoute');
  if (btnRoute) {
    btnRoute.addEventListener('click', () => {
      console.log('Calculate Route clicked');
      console.log('From Coords:', model.fromCoords);
      console.log('To Coords:', model.toCoords);
      
      if (!model.fromCoords) {
        view.updateStatus('⚠️ Please set the PICKUP pin first!');
        return;
      }
      
      if (!model.toCoords) {
        view.updateStatus('⚠️ Please set the DELIVERY pin first!');
        return;
      }
      
      if (!model.fromCoords.lat || !model.fromCoords.lon || !model.toCoords.lat || !model.toCoords.lon) {
        view.updateStatus('⚠️ Invalid pin locations. Please try again.');
        return;
      }
      
      view.updateStatus('Calculating distance…');
      
      try {
        const distanceKm = model.calculateDistance(model.fromCoords, model.toCoords);
        console.log('Calculated distance:', distanceKm);
        
        model.distanceKm = parseFloat(distanceKm).toFixed(2);
        model.etaMinutes = Math.ceil((model.distanceKm / 40) * 60);
        model.routeCoords = [[model.fromCoords.lat, model.fromCoords.lon], [model.toCoords.lat, model.toCoords.lon]];
        
        console.log('Distance:', model.distanceKm, 'ETA:', model.etaMinutes);
        
        view.displayRoute(model.routeCoords);
        view.updateInfo(model.distanceKm, model.etaMinutes);
        
        const distEl = document.getElementById('distance_km');
        if (distEl) {
          distEl.value = model.distanceKm;
          console.log('Hidden field value set to:', distEl.value);
        } else {
          console.error('distance_km hidden field not found');
        }
        
        view.updateStatus('✅ Route calculated! Distance: ' + model.distanceKm + ' km | ETA: ' + model.etaMinutes + ' min');
      } catch (err) {
        console.error('Error:', err);
        view.updateStatus('❌ Error calculating distance: ' + err.message);
      }
    });
    console.log('btnRoute listener attached ✓');
  } else {
    console.error('btnRoute button NOT FOUND');
  }

  console.log('=== All listeners attached ===');

  // Form validation
  const form = document.querySelector('form');
  if (form) {
    form.addEventListener('submit', function(event) {
      const distanceKm = document.getElementById('distance_km').value;
      console.log('Form validation - distance_km value:', distanceKm);
      
      if (!distanceKm || parseFloat(distanceKm) === 0 || distanceKm === '') {
        event.preventDefault();
        alert('⚠️ Please pin your pickup and delivery locations on the map and calculate the route before continuing.');
        return false;
      }
      return true;
    });
    console.log('Form validation attached ✓');
  }

});
</script>