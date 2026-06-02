<?php
$step    = $step    ?? 5;
$ref     = $ref     ?? 'CRG-XXXXXX';
$summary = $summary ?? ['vehicle'=>'—','pickup'=>'—','return'=>'—','driver'=>'Self-Drive','total'=>'₱0.00'];
?>
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/booking.css">

<div class="bk-hero">
  <span class="bk-label">Booking — Step <?= (int)$step ?> of 5</span>
  <h1 class="bk-title">Booking Submitted!</h1>
  <p class="bk-sub">Your documents are under review. We'll notify you once approved.</p>
</div>
<div class="steps-wrap">
  <?php $currentStep = $step; include '_steps.php'; ?>
</div>

<section class="bk-section">
<div class="bk-container">
<div class="flow-card" style="text-align:left;">

  <div class="confirm-badge">📋</div>
  <div class="ref-code"><?= htmlspecialchars($ref) ?></div>
  <div class="ref-label">Booking Reference</div>

  <div class="notice-box notice-warn" style="margin-bottom:1.6rem;">
    <strong>⏳ Pending Document Review</strong><br>
    Our team is reviewing your driver's license and government ID.
    Once approved, you will be able to proceed to payment. This usually takes <strong>1–2 hours</strong>.
  </div>

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
  <?php if (!empty($summary['destination'])): ?>
  <div class="summary-row">
    <span class="summary-key">Destination</span>
    <span class="summary-val" style="max-width:300px;text-align:right;font-size:.8rem;"><?= htmlspecialchars($summary['destination']) ?></span>
  </div>
  <?php endif; ?>
  <?php if (!empty($summary['distance'])): ?>
  <div class="summary-row">
    <span class="summary-key">Distance</span>
    <span class="summary-val"><?= htmlspecialchars($summary['distance']) ?></span>
  </div>
  <?php endif; ?>
  <hr class="section-divider">
  <div class="summary-row summary-total">
    <span class="summary-key">Total</span>
    <span class="summary-val"><?= htmlspecialchars($summary['total'] ?? '₱0.00') ?></span>
  </div>

  <hr class="section-divider">
  <h2 style="margin-bottom:1rem;">What Happens Next</h2>
  <div class="next-steps">
    <div class="next-step-card">
      <span class="next-step-icon">🪪</span>
      <div class="next-step-text">Admin reviews your license &amp; government ID</div>
    </div>
    <div class="next-step-card">
      <span class="next-step-icon">✅</span>
      <div class="next-step-text">You'll be notified once documents are approved</div>
    </div>
    <div class="next-step-card">
      <span class="next-step-icon">💳</span>
      <div class="next-step-text">Log in to your dashboard to complete payment</div>
    </div>
    <div class="next-step-card">
      <span class="next-step-icon">🚗</span>
      <div class="next-step-text">Pick up your car and enjoy!</div>
    </div>
  </div>

  <div class="bk-actions" style="justify-content:center;margin-top:2rem;">
    <a href="<?= BASE_URL ?>?page=dashboard" class="btn btn-red">Go to My Dashboard →</a>
    <a href="<?= BASE_URL ?>?page=browse"    class="btn btn-ghost">Browse More Cars</a>
  </div>

</div>
</div>
</section>