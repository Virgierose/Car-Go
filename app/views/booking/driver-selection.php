<?php
$step = isset($step) ? $step : 2;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $driver_choice = $_POST['driver_choice'] ?? 'self';
    if ($driver_choice === 'self') {
        $_SESSION['booking']['driver_id']   = null;
        $_SESSION['booking']['driver_name'] = 'Self-Drive';
        $_SESSION['booking']['with_driver'] = false;
    } else {
        $chosen_id   = (int) $driver_choice;
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
        $days  = (int)($_SESSION['booking']['days'] ?? 1);
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
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/driver-selection.css?v=<?= time() ?>">

<style>
/* ── Hard resets — prevent _booking_styles.php from collapsing flex children ── */
.selfdriven-opt,
.selfdriven-opt * {
    box-sizing: border-box !important;
}

/* Force the row to always be a horizontal flex */
.selfdriven-opt {
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    flex-wrap: nowrap !important;
}

/* Hide the native radio completely */
.selfdriven-opt input[type="radio"],
.driver-card  input[type="radio"] {
    display: none !important;
}

/* Avatar circle always block-flex */
.selfdriven-icon {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    flex-shrink: 0 !important;
    width: 48px !important;
    height: 48px !important;
    border-radius: 50% !important;
    font-size: 1.4rem !important;
    background: rgba(255,255,255,.06) !important;
    border: 1px solid var(--border) !important;
}

/* Text block grows */
.selfdriven-text {
    flex: 1 1 0 !important;
    min-width: 0 !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 2px !important;
}

.selfdriven-text strong {
    display: block !important;
    font-family: 'Barlow Condensed', sans-serif !important;
    font-size: 1rem !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: .08em !important;
    color: #fff !important;
    margin: 0 !important;
}

.selfdriven-text span {
    display: block !important;
    font-size: .78rem !important;
    color: var(--text-muted, #888) !important;
    margin: 0 !important;
}

/* Price pill */
.selfdriven-price {
    flex-shrink: 0 !important;
    display: inline-flex !important;
    align-items: center !important;
}

/* Check circle */
.selfdriven-check {
    flex-shrink: 0 !important;
    width: 22px !important;
    height: 22px !important;
    border-radius: 50% !important;
    display: none !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: .7rem !important;
    font-weight: 700 !important;
    color: #fff !important;
    background: var(--crimson, #c0392b) !important;
}
.selfdriven-opt.selected .selfdriven-check {
    display: flex !important;
}

/* Driver card — always column flex centered */
.driver-card {
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    text-align: center !important;
}

/* Driver avatar circle */
.driver-card .driver-avatar {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 68px !important;
    height: 68px !important;
    border-radius: 50% !important;
    overflow: hidden !important;
    flex-shrink: 0 !important;
    font-family: 'Barlow Condensed', sans-serif !important;
    font-size: 1.7rem !important;
    font-weight: 700 !important;
    color: var(--crimson-soft, #e05252) !important;
    background: rgba(255,255,255,.06) !important;
    border: 2px solid var(--border) !important;
    margin-bottom: .6rem !important;
}

.driver-card .driver-avatar img {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
}

.driver-check {
    display: none !important;
    position: absolute !important;
    top: 10px !important;
    right: 10px !important;
    width: 22px !important;
    height: 22px !important;
    border-radius: 50% !important;
    background: var(--crimson, #c0392b) !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: .7rem !important;
    font-weight: 700 !important;
    color: #fff !important;
}
.driver-card.selected .driver-check {
    display: flex !important;
}

/* Section divider label */
.ds-section-label {
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
    font-size: .7rem !important;
    font-weight: 700 !important;
    letter-spacing: .14em !important;
    text-transform: uppercase !important;
    color: var(--text-muted, #888) !important;
    margin: 0 0 .9rem !important;
}
.ds-section-label::before,
.ds-section-label::after {
    content: '' !important;
    flex: 1 !important;
    height: 1px !important;
    background: var(--border, #2a2a2a) !important;
}

/* Driver grid */
.driver-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)) !important;
    gap: .9rem !important;
    margin-bottom: 1.8rem !important;
}
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

      <form method="POST" action="<?= BASE_URL ?>?page=driver-selection" id="driver-form">
        <input type="hidden" name="driver_choice" id="h-driver-choice" value="self">

        <!-- ── Self-Drive ──────────────────────────────── -->
        <div class="ds-section-label">Your Option</div>

        <div class="selfdriven-opt selected" id="opt-self" onclick="selectDriver('self')">
          <input type="radio" name="driver_choice" value="self" checked>
          <div class="selfdriven-icon">🚗</div>
          <div class="selfdriven-text">
            <strong>Self-Drive</strong>
            <span>No extra charge — you're in control</span>
          </div>
          <div class="selfdriven-price">Included</div>
          <div class="selfdriven-check">✓</div>
        </div>

        <!-- ── Professional Drivers ───────────────────── -->
        <div class="ds-section-label">Professional Drivers</div>

        <?php if (!empty($drivers)): ?>
          <div class="driver-grid">
            <?php foreach ($drivers as $driver): ?>
            <div class="driver-card" id="opt-<?= $driver['id'] ?>"
                 onclick="selectDriver(<?= $driver['id'] ?>)">
              <input type="radio" name="driver_choice" value="<?= $driver['id'] ?>">

              <div class="driver-avatar">
                <?php if (!empty($driver['photo'])): ?>
                  <img src="<?= htmlspecialchars($driver['photo']) ?>"
                       alt="<?= htmlspecialchars($driver['name']) ?>">
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
          <div class="no-drivers">
            <span class="no-drivers-icon">🚫</span>
            No drivers available right now.<br>
            You may proceed with self-drive.
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
  if (target) {
    target.classList.add('selected');
    var radio = target.querySelector('input[type="radio"]');
    if (radio) radio.checked = true;
  }
  document.getElementById('h-driver-choice').value = val;
}
</script>