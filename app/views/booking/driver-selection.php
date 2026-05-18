<?php

$step = isset($step) ? $step : 2;

// ── HANDLE POST: save driver selection to session ─────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $driver_choice = $_POST['driver_choice'] ?? 'self';
    if ($driver_choice === 'self') {
        $_SESSION['booking']['driver_id']   = null;
        $_SESSION['booking']['driver_name'] = 'Self-Drive';
        $_SESSION['booking']['with_driver'] = false;
    } else {
        $chosen_id = (int) $driver_choice;
        // Find driver name from the $drivers array passed by the controller
        $driver_name = 'Driver #' . $chosen_id;
        if (!empty($drivers)) {
            foreach ($drivers as $d) {
                if ((int)$d['id'] === $chosen_id) { $driver_name = $d['name']; break; }
            }
        }
        $_SESSION['booking']['driver_id']   = $chosen_id;
        $_SESSION['booking']['driver_name'] = $driver_name;
        $_SESSION['booking']['with_driver'] = true;
        $_SESSION['booking']['driver_fee']  = 500;
        // Recalculate total with driver fee
        $days  = (int) ($_SESSION['booking']['days'] ?? 1);
        $total = (float)($_SESSION['booking']['car_price'] ?? 0);
        if ($_SESSION['booking']['trip_type'] === 'halfday') $total *= 0.6;
        else $total *= $days;
        $total += 500 * $days;
        $_SESSION['booking']['total'] = $total;
    }
    header('Location: ' . BASE_URL . '?page=booking-form');
    exit;
}
?>

<?php include '_booking_styles.php'; ?>

<style>
.driver-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
.driver-card {
  border: 1px solid var(--border);
  background: var(--bg-input);
  padding: 1.5rem 1rem;
  text-align: center;
  cursor: pointer;
  transition: all .25s;
  position: relative;
}
.driver-card:hover { border-color: rgba(192,57,43,0.3); background: rgba(192,57,43,0.04); }
.driver-card.selected { border-color: var(--crimson); background: rgba(192,57,43,0.07); box-shadow: 0 0 16px var(--crimson-glow); }
.driver-card input[type="radio"] { position: absolute; opacity: 0; pointer-events: none; }
.driver-avatar {
  width: 64px; height: 64px; border-radius: 50%;
  background: var(--bg-card); border: 2px solid var(--border);
  margin: 0 auto .8rem;
  display: flex; align-items: center; justify-content: center;
  font-family: 'Barlow Condensed', sans-serif; font-size: 1.4rem; font-weight: 700;
  color: var(--crimson-soft);
  overflow: hidden;
}
.driver-avatar img { width: 100%; height: 100%; object-fit: cover; }
.driver-name { font-family: 'Barlow Condensed', sans-serif; font-size: 1rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--text-primary); }
.driver-badge { font-size: .68rem; color: var(--text-muted); letter-spacing: .08em; margin-top: .2rem; }
.driver-check {
  position: absolute; top: 10px; right: 10px;
  width: 18px; height: 18px; border-radius: 50%;
  background: var(--crimson); display: none; align-items: center; justify-content: center;
  font-size: .6rem; color: #fff;
}
.driver-card.selected .driver-check { display: flex; }

.selfdriven-opt {
  border: 1px dashed var(--border);
  background: transparent; padding: 1.2rem 1.5rem;
  display: flex; align-items: center; gap: 1rem;
  cursor: pointer; transition: all .25s; margin-bottom: 1.5rem;
}
.selfdriven-opt:hover { border-color: rgba(192,57,43,0.3); }
.selfdriven-opt.selected { border-color: var(--crimson); background: rgba(192,57,43,0.05); }
.selfdriven-opt input[type="radio"] { position: absolute; opacity: 0; pointer-events: none; }
.selfdriven-icon { font-size: 1.5rem; }
.selfdriven-text { flex: 1; }
.selfdriven-text strong { display: block; font-family: 'Barlow Condensed', sans-serif; text-transform: uppercase; letter-spacing: .08em; font-size: .95rem; }
.selfdriven-text span { font-size: .75rem; color: var(--text-muted); }
</style>

<div class="bk-hero">
  <span class="bk-label">Booking — Step <?= $step ?> of 5</span>
  <h1 class="bk-title">Choose Your Driver</h1>
  <p class="bk-sub">Pick a professional driver or take the wheel yourself.</p>
</div>

<div class="steps-wrap">
  <?php $currentStep = $step; include '_steps.php'; ?>
</div>

<section class="bk-section">
  <div class="bk-container">
    <div class="flow-card">
      <h2>Driver Preference</h2>

      <form method="POST" action="<?= BASE_URL ?>?page=driver-selection" id="driver-form">
        <input type="hidden" name="driver_choice" id="h-driver-choice" value="self">

      <!-- Self Drive Option -->
      <div class="selfdriven-opt selected" id="opt-self" onclick="selectDriver('self')">
        <input type="radio" name="driver_choice" value="self" checked>
        <div class="selfdriven-icon">🚗</div>
        <div class="selfdriven-text">
          <strong>Self-Drive</strong>
          <span>No extra charge — you're in control</span>
        </div>
        <div style="font-family:'Barlow Condensed',sans-serif; font-size:1.1rem; color:var(--text-muted);">Included</div>
      </div>

      <!-- Available Drivers -->
      <?php if(!empty($drivers)): ?>
        <p class="form-label" style="margin-bottom:1rem;">Or choose a professional driver</p>
        <div class="driver-grid">
          <?php foreach($drivers as $driver): ?>
          <div class="driver-card" id="opt-<?= $driver['id'] ?>" onclick="selectDriver(<?= $driver['id'] ?>)">
            <input type="radio" name="driver_choice" value="<?= $driver['id'] ?>">
            <div class="driver-avatar">
              <?php if(!empty($driver['photo'])): ?>
                <img src="<?= htmlspecialchars($driver['photo']) ?>" alt="">
              <?php else: ?>
                <?= strtoupper(substr($driver['name'], 0, 1)) ?>
              <?php endif; ?>
            </div>
            <div class="driver-check">✓</div>
            <div class="driver-name"><?= htmlspecialchars($driver['name']) ?></div>
            <div class="driver-badge">+₱500/day</div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div style="text-align:center; padding:2rem; border:1px dashed var(--border); color:var(--text-muted); font-size:.85rem; margin-bottom:1.5rem;">
          No drivers available right now. You may proceed with self-drive.
        </div>
      <?php endif; ?>

      <div class="bk-actions">
        <a href="<?= BASE_URL ?>?page=price-calculator" class="btn btn-ghost">← Back</a>
        <button type="submit" class="btn btn-red">Next: Your Details →</button>
      </div>

      </form>
    </div>
  </div>
</section>

<script>
var selected = 'self';
function selectDriver(val) {
  selected = val;
  document.querySelectorAll('.driver-card, .selfdriven-opt').forEach(function(el) {
    el.classList.remove('selected');
  });
  var target = document.getElementById('opt-' + val);
  if (target) target.classList.add('selected');
  var radio = target ? target.querySelector('input[type="radio"]') : null;
  if (radio) radio.checked = true;
  document.getElementById('h-driver-choice').value = val;
}
</script>