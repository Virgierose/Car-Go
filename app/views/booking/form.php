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
    $_SESSION['booking']['pickup_location']  = trim($_POST['pickup_location']  ?? '');
    $_SESSION['booking']['return_location']  = trim($_POST['return_location']  ?? '');
    $_SESSION['booking']['pickup_datetime']  = trim($_POST['pickup_datetime']  ?? '');
    $_SESSION['booking']['return_datetime']  = trim($_POST['return_datetime']  ?? '');
    $_SESSION['booking']['notes']            = trim($_POST['notes']            ?? '');
    header('Location: ' . BASE_URL . '?page=payment');
    exit;
}
?>

<?php include '_booking_styles.php'; ?>
 
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
        <h2 style="margin-bottom:1.4rem;">Pickup & Return</h2>

        <div class="grid-2">
          <div class="form-group">
            <label class="form-label">Pickup Location</label>
            <input type="text" name="pickup_location" class="form-control" placeholder="Address or landmark" required>
          </div>
          <div class="form-group">
            <label class="form-label">Return Location</label>
            <input type="text" name="return_location" class="form-control" placeholder="Same as pickup or other">
          </div>
          <div class="form-group">
            <label class="form-label">Pickup Date & Time</label>
            <input type="datetime-local" name="pickup_datetime" class="form-control" required>
          </div>
          <div class="form-group">
            <label class="form-label">Return Date & Time</label>
            <input type="datetime-local" name="return_datetime" class="form-control" required>
          </div>
        </div>

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