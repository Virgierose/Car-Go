<?php
/**
 * View: auth/mfa-otp.php
 * Variables injected by MfaController::renderOtpForm():
 *   $email  (string)       – email the OTP was sent to
 *   $error  (string|null)  – error flash message
 *   $sent   (bool)         – whether the email was just (re)sent
 */

$error = $error ?? null;
$sent  = $sent  ?? false;

/**
 * Mask the email for display: j***@gmail.com
 */
function maskEmail(string $email): string
{
    if (!str_contains($email, '@')) {
        return $email; // guard against malformed addresses
    }
    [$local, $domain] = explode('@', $email, 2);
    $masked = substr($local, 0, 1) . str_repeat('*', max(1, strlen($local) - 1));
    return $masked . '@' . $domain;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>CarGo — Verify Your Identity</title>
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800&family=Barlow:wght@300;400;500&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --crimson:       #c0392b;
      --crimson-soft:  #e05252;
      --crimson-glow:  rgba(192,57,43,0.22);
      --bg-input:      #18181c;
      --border:        rgba(255,255,255,0.07);
      --border-focus:  rgba(192,57,43,0.5);
      --text-primary:  #f0f0f0;
      --text-muted:    #666;
      --text-label:    #999;
    }

    body {
      font-family: 'Barlow', sans-serif;
      background:
        linear-gradient(rgba(10,10,11,0.85), rgba(10,10,11,0.92)),
        url('https://images.unsplash.com/photo-1503376780353-7e6692767b70?q=80&w=1920&auto=format&fit=crop');
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
      color: var(--text-primary);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      overflow-x: hidden;
    }

    /* ── Layout ─────────────────────────────────────────────────────── */
    .page-content {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem 1rem;
    }
    .wrapper {
      width: 100%;
      max-width: 420px;
      animation: fadeUp .55s ease both;
    }
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(22px); }
      to   { opacity: 1; transform: translateY(0);    }
    }

    /* ── Logo ───────────────────────────────────────────────────────── */
    .logo           { text-align: center; margin-bottom: 28px; }
    .logo-text      {
      font-family: 'Barlow Condensed', sans-serif;
      font-weight: 800; font-size: 2.8rem;
      text-transform: uppercase; color: #fff; letter-spacing: .06em;
    }
    .logo-text span { color: var(--crimson-soft); }
    .logo-sub       {
      font-size: .7rem; letter-spacing: .25em;
      text-transform: uppercase; color: var(--text-muted); margin-top: 4px;
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
      margin: 8px 0 22px; line-height: 1.5;
    }
    .card-heading strong { color: #ccc; }

    /* ── Alerts ─────────────────────────────────────────────────────── */
    .alert {
      padding: 11px 14px; margin-bottom: 18px;
      font-size: .82rem; border-left: 3px solid;
    }
    .alert-error   { background: rgba(192,57,43,0.12); border-color: var(--crimson);  color: #f09090; }
    .alert-success { background: rgba(39,174,96,0.12);  border-color: #27ae60;        color: #80d4a0; }

    /* ── Field ──────────────────────────────────────────────────────── */
    .field              { margin-bottom: 20px; }
    .field label        {
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
      /* Remove number spinners */
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

    /* ── Resend row ─────────────────────────────────────────────────── */
    .resend-row {
      text-align: center; margin-top: 18px;
      font-size: .8rem; color: var(--text-muted);
    }
    .resend-row a {
      color: var(--crimson-soft); text-decoration: none; margin-left: 4px;
    }
    .resend-row a:hover { opacity: .8; }

    /* ── Countdown ──────────────────────────────────────────────────── */
    #countdown {
      color: #aaa; font-size: .78rem;
      display: block; margin-top: 5px; text-align: center;
    }

    /* ── Back link ──────────────────────────────────────────────────── */
    .back-link {
      display: block; text-align: center;
      margin-top: 20px; font-size: .8rem;
      color: var(--text-muted); text-decoration: none;
    }
    .back-link:hover { color: #aaa; }
  </style>
</head>
<body>

<div class="page-content">
  <div class="wrapper">

    <!-- Logo -->
    <div class="logo">
      <div class="logo-text">Car<span>Go</span></div>
      <div class="logo-sub">Premium Car Rental</div>
    </div>

    <!-- Card -->
    <div class="card">

      <div class="card-heading">
        <h1>Check Your Email</h1>
        <p>
          We sent a 6-digit verification code to<br>
          <strong><?= htmlspecialchars(maskEmail($email), ENT_QUOTES, 'UTF-8') ?></strong>.
          Enter it below to continue.
        </p>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-error" role="alert">
          <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
      <?php endif; ?>

      <?php if ($sent && !$error): ?>
        <div class="alert alert-success" role="status">
          A new code has been sent — check your inbox.
        </div>
      <?php endif; ?>

      <form method="POST" action="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>?page=mfa-otp" novalidate>
        <div class="field">
          <label for="otp_code">Verification Code</label>
          <input
            type="number"
            id="otp_code"
            name="otp_code"
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

      <div class="resend-row">
        Didn't receive it?
        <a id="resend-link"
           href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>?page=mfa-otp&resend=1"
           aria-disabled="true">
          Resend code
        </a>
        <span id="countdown"></span>
      </div>

      <a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>?page=login" class="back-link">
        ← Back to sign in
      </a>
    </div>
    <!-- /Card -->

  </div>
</div>

<script>
(function () {
  'use strict';

  const countdownEl = document.getElementById('countdown');
  const resendLink  = document.getElementById('resend-link');
  const WAIT        = 30; // seconds before resend is allowed
  let   remaining   = WAIT;

  // Disable resend immediately
  resendLink.style.pointerEvents = 'none';
  resendLink.style.opacity       = '0.4';
  countdownEl.textContent        = '(' + remaining + 's)';

  const timer = setInterval(function () {
    remaining -= 1;
    if (remaining > 0) {
      countdownEl.textContent = '(' + remaining + 's)';
    } else {
      clearInterval(timer);
      countdownEl.textContent        = '';
      resendLink.style.pointerEvents = '';
      resendLink.style.opacity       = '';
      resendLink.removeAttribute('aria-disabled');
    }
  }, 1000);

  // Auto-submit the form as soon as 6 digits have been entered
  document.getElementById('otp_code').addEventListener('input', function () {
    // Clamp to 6 digits — prevents pasting more
    if (this.value.length > 6) {
      this.value = this.value.slice(0, 6);
    }
    if (this.value.length === 6) {
      this.closest('form').submit();
    }
  });
})();
</script>

</body>
</html>