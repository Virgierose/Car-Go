<?php
// Auth, guard, session unpack and cleanup are all handled by
// BookingController::confirmation() before render() is called.
// $step, $ref, and $summary are injected by the controller.
$step    = $step    ?? 5;
$ref     = $ref     ?? 'CRG-XXXXXX';
$summary = $summary ?? [
    'vehicle' => '—',
    'pickup'  => '—',
    'return'  => '—',
    'driver'  => 'Self-Drive',
    'total'   => '₱0.00',
];
?>

<?php include '_booking_styles.php'; ?>

<style>
.confirm-badge {
  width: 72px; height: 72px; border-radius: 50%;
  background: rgba(192,57,43,0.12); border: 2px solid var(--crimson);
  display: flex; align-items: center; justify-content: center;
  font-size: 2rem; margin: 0 auto 1.5rem;
  animation: popIn .5s cubic-bezier(.17,.67,.35,1.3);
}
@keyframes popIn {
  from { transform: scale(0); opacity:0; }
  to   { transform: scale(1); opacity:1; }
}
.ref-code {
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 2rem; font-weight: 800; letter-spacing: .12em;
  color: var(--crimson-soft); text-align: center; margin-bottom: .4rem;
}
.ref-label { text-align: center; font-size: .7rem; letter-spacing: .2em; text-transform: uppercase; color: var(--text-muted); margin-bottom: 2rem; }

.next-steps { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 1rem; margin: 1.5rem 0; }
.next-step-card {
  border: 1px solid var(--border); background: var(--bg-input);
  padding: 1.2rem 1rem; text-align: center;
}
.next-step-icon { font-size: 1.4rem; margin-bottom: .6rem; display: block; }
.next-step-text { font-size: .75rem; color: var(--text-muted); line-height: 1.5; }
</style>

<div class="bk-hero">
  <span class="bk-label">Booking — Step <?= (int)$step ?> of 5</span>
  <h1 class="bk-title">Booking Confirmed</h1>
  <p class="bk-sub">Your reservation has been successfully placed.</p>
</div>

<div class="steps-wrap">
  <?php $currentStep = $step; include '_steps.php'; ?>
</div>

<section class="bk-section">
  <div class="bk-container">
    <div class="flow-card" style="text-align:left;">

      <div class="confirm-badge">✓</div>
      <div class="ref-code"><?= htmlspecialchars($ref) ?></div>
      <div class="ref-label">Booking Reference</div>

      <h2>Booking Summary</h2>
      <div class="summary-row">
        <span class="summary-key">Vehicle</span>
        <span class="summary-val"><?= htmlspecialchars($summary['vehicle'] ?? '—') ?></span>
      </div>
      <div class="summary-row">
        <span class="summary-key">Pickup</span>
        <span class="summary-val"><?= htmlspecialchars($summary['pickup'] ?? '—') ?></span>
      </div>
      <div class="summary-row">
        <span class="summary-key">Return</span>
        <span class="summary-val"><?= htmlspecialchars($summary['return'] ?? '—') ?></span>
      </div>
      <div class="summary-row">
        <span class="summary-key">Driver</span>
        <span class="summary-val"><?= htmlspecialchars($summary['driver'] ?? 'Self-Drive') ?></span>
      </div>
      <hr class="section-divider">
      <div class="summary-row summary-total">
        <span class="summary-key">Total Paid</span>
        <span class="summary-val"><?= htmlspecialchars($summary['total'] ?? '₱0.00') ?></span>
      </div>

      <hr class="section-divider">
      <h2 style="margin-bottom:1rem;">What Happens Next</h2>
      <div class="next-steps">
        <div class="next-step-card">
          <span class="next-step-icon">📧</span>
          <div class="next-step-text">Confirmation email sent to your inbox</div>
        </div>
        <div class="next-step-card">
          <span class="next-step-icon">📞</span>
          <div class="next-step-text">Our team will call you 24hrs before pickup</div>
        </div>
        <div class="next-step-card">
          <span class="next-step-icon">🪪</span>
          <div class="next-step-text">Bring a valid ID and your booking reference</div>
        </div>
        <div class="next-step-card">
          <span class="next-step-icon">🚗</span>
          <div class="next-step-text">Enjoy your ride with CarGo!</div>
        </div>
      </div>

      <div class="bk-actions" style="justify-content:center; margin-top:2rem;">
        <a href="<?= BASE_URL ?>?page=dashboard" class="btn btn-ghost">View My Bookings</a>
        <a href="<?= BASE_URL ?>?page=browse" class="btn btn-red">Book Another Car</a>
      </div>

    </div>
  </div>
</section>