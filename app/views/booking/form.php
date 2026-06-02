<?php
$step = isset($step) ? $step : 3;

define('FIXED_PICKUP_LAT',   10.6765);
define('FIXED_PICKUP_LON',   122.9509);
define('FIXED_PICKUP_LABEL', 'CarGo Main Branch — Bacolod City');

// Errors come from the controller (if any)
$errors = isset($errors) ? $errors : [];
?>
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/booking.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/booking-forms.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<div class="bk-hero">
  <span class="bk-label">Booking — Step <?= $step ?> of 5</span>
  <h1 class="bk-title">Your Details</h1>
  <p class="bk-sub">Fill in your information and upload your documents to continue.</p>
</div>
<div class="steps-wrap">
  <?php $currentStep = $step; include '_steps.php'; ?>
</div>

<section class="bk-section">
<div class="bk-container">
<div class="flow-card">

  <?php if (!empty($errors)): ?>
  <div class="notice-box notice-danger" style="margin-bottom:1.4rem;">
    <strong>⚠ Please fix these errors:</strong>
    <?php foreach ($errors as $e): ?><div>• <?= htmlspecialchars($e) ?></div><?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if (isset($_SESSION['debug_post'])): ?>
  <div style="margin-bottom:1.4rem; padding:1rem; background:#333; border-left:3px solid #2ecc71; color:#2ecc71; font-size:0.75rem; overflow-x:auto; font-family:monospace;">
    <strong style="color:#fff;">DEBUG - Last POST Received:</strong>
    <div>distance_km: "<?= htmlspecialchars($_SESSION['debug_post']['distance_km'] ?? '') ?>"</div>
    <div>Files: <?= htmlspecialchars(json_encode($_SESSION['debug_files'] ?? [])) ?></div>
    <div>First Name: "<?= htmlspecialchars($_SESSION['debug_post']['first_name'] ?? '') ?>"</div>
    <div>Email: "<?= htmlspecialchars($_SESSION['debug_post']['email'] ?? '') ?>"</div>
  </div>
  <?php unset($_SESSION['debug_post'], $_SESSION['debug_files']); ?>
  <?php endif; ?>

  <form method="POST" action="<?= BASE_URL ?>?page=booking-form"
        id="booking-form" enctype="multipart/form-data">

    <!-- ── Personal Info ──────────────────────────────────── -->
    <h2>Personal Information</h2>
    <div class="grid-2">
      <div class="form-group">
        <label class="form-label">First Name</label>
        <input type="text" name="first_name" class="form-control" required
          value="<?= htmlspecialchars($_SESSION['booking']['first_name'] ?? $_SESSION['client_fname'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Last Name</label>
        <input type="text" name="last_name" class="form-control" required
          value="<?= htmlspecialchars($_SESSION['booking']['last_name'] ?? $_SESSION['client_lname'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" required
          value="<?= htmlspecialchars($_SESSION['booking']['email'] ?? $_SESSION['client_email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Phone Number</label>
        <input type="tel" name="phone" class="form-control" placeholder="+63 9XX XXX XXXX" required
          value="<?= htmlspecialchars($_SESSION['booking']['phone'] ?? '') ?>">
      </div>
    </div>

    <hr class="section-divider">

    <!-- ── Dates ──────────────────────────────────────────── -->
    <h2>Pickup &amp; Return Dates</h2>
    <div class="grid-2">
      <div class="form-group">
        <label class="form-label">Pickup Date &amp; Time</label>
        <input type="datetime-local" name="pickup_datetime" class="form-control" required
          value="<?= htmlspecialchars($_SESSION['booking']['pickup_datetime'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Return Date &amp; Time</label>
        <input type="datetime-local" name="return_datetime" class="form-control" required
          value="<?= htmlspecialchars($_SESSION['booking']['return_datetime'] ?? '') ?>">
      </div>
    </div>

    <hr class="section-divider">

    <!-- ── Map / Destination ──────────────────────────────── -->
    <h2>📍 Set Your Destination</h2>
    <p style="font-size:.78rem;color:rgba(255,255,255,.4);margin-bottom:1rem;">
      Your car is picked up from our branch. Pin where you want to go.
    </p>
    <div class="fixed-pickup-badge">
      <span style="color:var(--crimson-soft);">🏢</span>
      <span>Pickup from: <strong><?= htmlspecialchars(FIXED_PICKUP_LABEL) ?></strong></span>
    </div>
    <div class="map-controls">
      <button type="button" class="map-btn map-btn-red"   id="btnSetDest">📍 Pin Destination</button>
      <button type="button" class="map-btn map-btn-ghost" id="btnCalcRoute" disabled>🗺 Calculate Distance</button>
      <button type="button" class="map-btn map-btn-ghost" id="btnClearDest">✕ Clear</button>
    </div>
    <p id="map-status">Click "Pin Destination" then click the map.</p>
    <div class="map-info">
      <span>📏 Distance: <strong id="distance">—</strong></span>
      <span>⏱ ETA: <strong id="eta">—</strong></span>
    </div>
    <div class="dest-preview" id="dest-preview">
      📍 Destination: <strong id="dest-label-display">—</strong>
    </div>

    <!-- ── FIX: removed overflow:hidden, added position:relative ── -->
    <div id="map-wrapper" style="width:100%;height:340px;margin:1.5rem 0;border-radius:10px;background:#1a1a1c;border:1px solid rgba(192,57,43,.3);position:relative;z-index:0;">
      <div id="map" style="width:100%;height:100%;"></div>
    </div>

    <input type="hidden" id="destination_label" name="destination_label">
    <input type="hidden" id="destination_lat"   name="destination_lat">
    <input type="hidden" id="destination_lon"   name="destination_lon">
    <input type="hidden" id="distance_km"       name="distance_km">

    <hr class="section-divider">

    <!-- ── Document Upload ────────────────────────────────── -->
    <h2>🪪 Required Documents</h2>
    <div class="notice-box notice-warn" style="margin-bottom:1.4rem;">
      <strong>⚠ Required for all rentals:</strong> A valid driver's license and one government-issued ID
      (e.g. passport, SSS, PhilHealth, Voter's ID). Your booking will be reviewed by our team before payment.
      Accepted formats: JPG, PNG, PDF — max 5 MB each.
    </div>

    <div class="grid-2">
      <div class="form-group">
        <label class="form-label">Driver's License <span style="color:var(--crimson-soft);">*</span></label>
        <div class="upload-zone" id="zone-license">
          <input type="file" name="license_img" id="file-license"
                 accept="image/jpeg,image/png,image/gif,image/webp,application/pdf"
                 onchange="previewFile(this,'prev-license','zone-license')">
          <span class="upload-icon">🪪</span>
          <div class="upload-label">Click or drag to upload<br><strong>Driver's License</strong></div>
          <div class="upload-preview" id="prev-license">
            <img id="prev-license-img" src="" alt="">
            <span id="prev-license-name"></span>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Government-Issued ID <span style="color:var(--crimson-soft);">*</span></label>
        <div class="upload-zone" id="zone-govid">
          <input type="file" name="gov_id_img" id="file-govid"
                 accept="image/jpeg,image/png,image/gif,image/webp,application/pdf"
                 onchange="previewFile(this,'prev-govid','zone-govid')">
          <span class="upload-icon">🪪</span>
          <div class="upload-label">Click or drag to upload<br><strong>Government ID</strong></div>
          <div class="upload-preview" id="prev-govid">
            <img id="prev-govid-img" src="" alt="">
            <span id="prev-govid-name"></span>
          </div>
        </div>
      </div>
    </div>

    <hr class="section-divider">

    <!-- ── Notes ──────────────────────────────────────────── -->
    <h2>Additional Notes</h2>
    <div class="form-group">
      <label class="form-label">Special Requests (Optional)</label>
      <textarea name="notes" class="form-control" rows="3"
        placeholder="Child seat, airport pickup, accessibility needs…"><?= htmlspecialchars($_SESSION['booking']['notes'] ?? '') ?></textarea>
    </div>

    <div class="bk-actions">
      <a href="<?= BASE_URL ?>?page=driver-selection" class="btn btn-ghost">← Back</a>
      <button type="submit" class="btn btn-red">Submit for Review →</button>
    </div>

  </form>
</div>
</div>
</section>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// ── File preview ───────────────────────────────────────────────
function previewFile(input, previewId, zoneId) {
  var prev = document.getElementById(previewId);
  var img  = document.getElementById(previewId + '-img');
  var name = document.getElementById(previewId + '-name');
  var file = input.files[0];
  if (!file) return;
  name.textContent = file.name;
  if (file.type.startsWith('image/')) {
    var reader = new FileReader();
    reader.onload = function(e) { img.src = e.target.result; img.style.display = 'block'; };
    reader.readAsDataURL(file);
  } else {
    img.style.display = 'none';
  }
  prev.classList.add('show');
  document.getElementById(zoneId).style.borderColor = 'rgba(192,57,43,.5)';
}

// ── Map ────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
  var PICKUP_LAT = <?= FIXED_PICKUP_LAT ?>;
  var PICKUP_LON = <?= FIXED_PICKUP_LON ?>;

  // ── FIX: preferCanvas + invalidateSize after layout settles ──
  var map = L.map('map', { preferCanvas: true }).setView([PICKUP_LAT, PICKUP_LON], 13);
  setTimeout(function(){ map.invalidateSize(true); }, 100);
  setTimeout(function(){ map.invalidateSize(true); }, 600);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
  }).addTo(map);

  var pickupIcon = L.divIcon({
    html: '<div style="background:#c0392b;width:14px;height:14px;border-radius:50%;border:3px solid #fff;box-shadow:0 0 0 2px #c0392b;"></div>',
    className:'', iconAnchor:[7,7]
  });
  L.marker([PICKUP_LAT, PICKUP_LON], { icon:pickupIcon, interactive:false })
    .addTo(map).bindPopup('🏢 <?= addslashes(FIXED_PICKUP_LABEL) ?>').openPopup();

  var destMarker=null, routeLine=null, pinningMode=false, destCoords=null;

  function setStatus(m){ document.getElementById('map-status').textContent = m; }

  function haversine(la1,lo1,la2,lo2){
    var R=6371, dL=(la2-la1)*Math.PI/180, dO=(lo2-lo1)*Math.PI/180;
    var a=Math.sin(dL/2)*Math.sin(dL/2)+Math.cos(la1*Math.PI/180)*Math.cos(la2*Math.PI/180)*Math.sin(dO/2)*Math.sin(dO/2);
    return R*2*Math.atan2(Math.sqrt(a),Math.sqrt(1-a));
  }

  function placePin(lat,lon){
    if(destMarker) map.removeLayer(destMarker);
    if(routeLine)  map.removeLayer(routeLine);
    var di=L.divIcon({html:'<div style="background:#2ecc71;width:14px;height:14px;border-radius:50%;border:3px solid #fff;box-shadow:0 0 0 2px #2ecc71;"></div>',className:'',iconAnchor:[7,7]});
    destMarker=L.marker([lat,lon],{icon:di}).addTo(map).bindPopup('📍 Your Destination');
    destCoords={lat:lat,lon:lon};
    document.getElementById('destination_lat').value=lat;
    document.getElementById('destination_lon').value=lon;
    setStatus('Getting address…');
    fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat='+lat+'&lon='+lon)
      .then(r=>r.json()).then(d=>{
        var label=d.display_name||(lat.toFixed(5)+', '+lon.toFixed(5));
        document.getElementById('destination_label').value=label;
        document.getElementById('dest-label-display').textContent=label;
        document.getElementById('dest-preview').style.display='block';
        destMarker.bindPopup('📍 '+label).openPopup();
        setStatus('✅ Destination pinned! Click "Calculate Distance".');
      }).catch(()=>{
        document.getElementById('destination_label').value=lat.toFixed(5)+', '+lon.toFixed(5);
        setStatus('✅ Destination pinned! Click "Calculate Distance".');
      });
    document.getElementById('btnCalcRoute').disabled=false;
    document.getElementById('btnCalcRoute').classList.replace('map-btn-ghost','map-btn-red');
  }

  document.getElementById('btnSetDest').addEventListener('click',function(){
    pinningMode=true; map.getContainer().style.cursor='crosshair';
    setStatus('Click anywhere on the map to pin your destination.');
  });

  document.getElementById('btnCalcRoute').addEventListener('click',function(){
    if(!destCoords){setStatus('⚠ Pin your destination first.');return;}
    var dist=haversine(PICKUP_LAT,PICKUP_LON,destCoords.lat,destCoords.lon);
    var eta=Math.ceil((dist/40)*60);
    document.getElementById('distance').textContent=dist.toFixed(2)+' km';
    document.getElementById('eta').textContent=eta+' min';
    document.getElementById('distance_km').value=dist.toFixed(2);
    if(routeLine) map.removeLayer(routeLine);
    routeLine=L.polyline([[PICKUP_LAT,PICKUP_LON],[destCoords.lat,destCoords.lon]],
      {color:'#c0392b',weight:4,dashArray:'8 6'}).addTo(map);
    map.fitBounds(routeLine.getBounds(),{padding:[40,40]});
    setStatus('✅ Distance: '+dist.toFixed(2)+' km — ETA ~'+eta+' min');
  });

  document.getElementById('btnClearDest').addEventListener('click',function(){
    if(destMarker){map.removeLayer(destMarker);destMarker=null;}
    if(routeLine){map.removeLayer(routeLine);routeLine=null;}
    destCoords=null;
    ['destination_lat','destination_lon','destination_label','distance_km'].forEach(id=>document.getElementById(id).value='');
    document.getElementById('distance').textContent='—';
    document.getElementById('eta').textContent='—';
    document.getElementById('dest-preview').style.display='none';
    document.getElementById('btnCalcRoute').disabled=true;
    document.getElementById('btnCalcRoute').classList.replace('map-btn-red','map-btn-ghost');
    setStatus('Click "Pin Destination" then click the map.');
  });

  map.on('click',function(e){
    if(!pinningMode)return;
    pinningMode=false; map.getContainer().style.cursor='';
    placePin(e.latlng.lat,e.latlng.lng);
  });

  document.getElementById('booking-form').addEventListener('submit',function(e){
    var dist=document.getElementById('distance_km').value;
    if(!dist||parseFloat(dist)===0){
      e.preventDefault();
      alert('⚠ Please pin your destination and calculate the distance before continuing.');
    }
  });
});
</script>