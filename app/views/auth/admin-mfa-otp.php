<?php
/**
 * View: auth/admin-mfa-otp.php
 *
 * IMPORTANT: This view must be rendered WITHOUT the site's shared
 * header/footer layout. In your controller/router, bypass the layout
 * wrapper entirely for this route — e.g. render this file directly
 * instead of passing it through a main layout template.
 *
 * Variables:
 *   $error  (string|null) – error flash message
 *   $qrUri  (string|null) – QR code data URI (optional, for setup flow)
 */
$error = $error ?? null;
$qrUri = $qrUri ?? null;

// Prevent any output buffering from a parent layout leaking in
if (ob_get_level() > 0) {
    ob_end_clean();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>CarGo — Admin Two-Factor Auth</title>
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800&family=Barlow:wght@300;400;500&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --crimson:      #c0392b;
      --crimson-soft: #e05252;
      --crimson-glow: rgba(192,57,43,0.22);
      --bg-input:     #18181c;
      --border:       rgba(255,255,255,0.07);
      --border-focus: rgba(192,57,43,0.5);
      --text-primary: #f0f0f0;
      --text-muted:   #666;
      --text-label:   #999;
    }

    /*
     * Hard reset — ensures no parent layout styles bleed in even if this
     * view is accidentally nested inside a shared wrapper.
     */
    html {
      height: 100%;
    }

    body {
      font-family: 'Barlow', sans-serif;
      background:
        linear-gradient(rgba(10,10,11,0.88), rgba(10,10,11,0.94)),
        url('https://images.unsplash.com/photo-1503376780353-7e6692767b70?q=80&w=1920&auto=format&fit=crop');
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
      color: var(--text-primary);

      /* Full-viewport centering — this page has NO header or footer */
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem 1rem;

      /* Kill any inherited layout from a parent wrapper */
      flex-direction: column;
    }

    /* ── Wrapper ────────────────────────────────────────────────────── */
    .mfa-wrapper {
      width: 100%;
      max-width: 440px;
      animation: fadeUp .55s ease both;
    }
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(22px); }
      to   { opacity: 1; transform: translateY(0);    }
    }

    /* ── Logo ───────────────────────────────────────────────────────── */
    .logo         { text-align: center; margin-bottom: 28px; }
    .logo-text    {
      font-family: 'Barlow Condensed', sans-serif;
      font-weight: 800; font-size: 2.8rem;
      text-transform: uppercase; color: #fff; letter-spacing: .06em;
    }
    .logo-text span { color: var(--crimson-soft); }
    .logo-sub     {
      font-size: .7rem; letter-spacing: .25em;
      text-transform: uppercase; color: var(--text-muted); margin-top: 4px;
    }

    /* ── Admin badge ────────────────────────────────────────────────── */
    .admin-badge {
      display: inline-flex; align-items: center; gap: 6px;
      background: rgba(192,57,43,0.15);
      border: 1px solid rgba(192,57,43,0.35);
      color: var(--crimson-soft);
      font-size: .68rem; letter-spacing: .2em;
      text-transform: uppercase;
      padding: 4px 10px;
      margin-bottom: 18px;
    }
    .admin-badge::before {
      content: '';
      display: inline-block;
      width: 6px; height: 6px;
      border-radius: 50%;
      background: var(--crimson-soft);
    }

    /* ── Card ───────────────────────────────────────────────────────── */
    .card {
      background: rgba(17,17,19,0.82);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      border: 1px solid var(--border);
      padding: 36px;
      position: relative;
      overflow: hidden;
    }
    .card::before {
      content: '';
      position: absolute; top: 0; left: 0; right: 0; height: 2px;
      background: linear-gradient(90deg, transparent, var(--crimson), transparent);
    }

    /* ── Card heading ───────────────────────────────────────────────── */
    .card-heading h1 {
      font-family: 'Barlow Condensed', sans-serif;
      font-size: 1.65rem; text-transform: uppercase; letter-spacing: .04em;
    }
    .card-heading p {
      font-size: .84rem; color: var(--text-muted);
      margin: 8px 0 0; line-height: 1.5;
    }

    /* ── QR Code block ──────────────────────────────────────────────── */
    .qr-block {
      display: flex;
      flex-direction: column;
      align-items: center;
      margin: 22px 0;
      padding: 20px;
      background: rgba(255,255,255,0.03);
      border: 1px solid var(--border);
    }
    .qr-block img {
      width: 180px; height: 180px;
      display: block;
      filter: invert(1) hue-rotate(180deg);
      image-rendering: pixelated;
    }
    .qr-block p {
      font-size: .75rem; color: var(--text-muted);
      text-align: center; margin-top: 12px; line-height: 1.5;
    }
    .qr-block strong {
      color: #ccc;
      font-family: 'Barlow Condensed', sans-serif;
      letter-spacing: .1em; font-size: .85rem;
    }

    /* ── Divider ────────────────────────────────────────────────────── */
    .divider {
      display: flex; align-items: center; gap: 10px;
      margin: 4px 0 20px;
    }
    .divider::before, .divider::after {
      content: ''; flex: 1; height: 1px;
      background: var(--border);
    }
    .divider span {
      font-size: .7rem; color: var(--text-muted);
      letter-spacing: .15em; text-transform: uppercase;
    }

    /* ── Alert ──────────────────────────────────────────────────────── */
    .alert {
      padding: 11px 14px; margin-bottom: 18px;
      font-size: .82rem; border-left: 3px solid;
    }
    .alert-error {
      background: rgba(192,57,43,0.12);
      border-color: var(--crimson);
      color: #f09090;
    }

    /* ── Field ──────────────────────────────────────────────────────── */
    .field             { margin-bottom: 20px; }
    .field label       {
      display: block; font-size: .7rem; letter-spacing: .2em;
      text-transform: uppercase; color: var(--text-label); margin-bottom: 7px;
    }

    /* ── OTP input ──────────────────────────────────────────────────── */
    .otp-input {
      width: 100%; padding: 16px 14px;
      background: var(--bg-input);
      border: 1px solid var(--border);
      color: #fff;
      font-family: 'Barlow Condensed', sans-serif;
      font-size: 2rem; font-weight: 700;
      letter-spacing: .5em; text-align: center;
      outline: none;
      transition: border-color .2s, box-shadow .2s;
      -moz-appearance: textfield;
    }
    .otp-input::-webkit-outer-spin-button,
    .otp-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    .otp-input:focus {
      border-color: var(--border-focus);
      box-shadow: 0 0 0 3px var(--crimson-glow);
    }

    /* ── Submit button ──────────────────────────────────────────────── */
    .btn-primary {
      width: 100%; padding: 13px;
      background: var(--crimson); border: none;
      color: #fff;
      font-family: 'Barlow Condensed', sans-serif;
      font-size: 1rem; font-weight: 700;
      letter-spacing: .15em; text-transform: uppercase;
      cursor: pointer;
      transition: background .2s, box-shadow .2s;
    }
    .btn-primary:hover {
      background: var(--crimson-soft);
      box-shadow: 0 4px 20px var(--crimson-glow);
    }
    .btn-primary:active { transform: scale(.98); }

    /* ── Help text & back link ──────────────────────────────────────── */
    .help-text {
      text-align: center; margin-top: 16px;
      font-size: .78rem; color: var(--text-muted); line-height: 1.6;
    }
    .back-link {
      display: block; text-align: center;
      margin-top: 16px; font-size: .8rem;
      color: var(--text-muted); text-decoration: none;
    }
    .back-link:hover { color: #aaa; }
  </style>
</head>
<body>

<div class="mfa-wrapper">

  <!-- Logo -->
  <div class="logo">
    <div class="logo-text">Car<span>Go</span></div>
    <div class="logo-sub">Admin Panel</div>
  </div>

  <!-- Card -->
  <div class="card">

    <div class="admin-badge">Admin Access</div>

    <div class="card-heading">
      <h1>Two-Factor Auth</h1>
      <p>Open your authenticator app and enter the 6-digit code for your CarGo admin account.</p>
    </div>

    <?php if ($qrUri): ?>
      <div class="qr-block">
        <img src="<?= htmlspecialchars($qrUri, ENT_QUOTES, 'UTF-8') ?>" alt="Scan with your authenticator app"/>
        <p>Scan this QR code with<br><strong>Google Authenticator</strong> or <strong>Authy</strong>, then enter the code below.</p>
      </div>
      <div class="divider"><span>then enter code</span></div>
    <?php else: ?>
      <div style="margin-top:22px;"></div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="alert alert-error" role="alert">
        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>?page=mfa-otp" novalidate>
      <div class="field">
        <label for="totp_code">Authenticator Code</label>
        <input
          type="number"
          id="totp_code"
          name="totp_code"
          class="otp-input"
          placeholder="000000"
          autocomplete="one-time-code"
          inputmode="numeric"
          pattern="\d{6}"
          maxlength="6"
          autofocus
          required
        >
      </div>

      <button type="submit" class="btn-primary">Verify &amp; Sign In</button>
    </form>

    <p class="help-text">
      Use Google Authenticator or Authy.<br>
      Having trouble? Contact your system administrator.
    </p>

    <a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>?page=login" class="back-link">
      ← Back to sign in
    </a>

  </div>
  <!-- /Card -->

</div>

<script>
(function () {
  'use strict';
  document.getElementById('totp_code').addEventListener('input', function () {
    if (this.value.length > 6) this.value = this.value.slice(0, 6);
    if (this.value.length === 6) this.closest('form').submit();
  });
})();
</script>

</body>
</html>