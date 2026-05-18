<?php
/**
 * View: auth/mfa-setup.php
 * Variables injected by MfaController::totpSetup():
 *   $qrUri   (string)       – base64 SVG data URI for the QR code
 *   $secret  (string)       – plain Base32 secret (for manual entry)
 *   $error   (string|null)  – error flash message
 *   $client  (array)        – client row (client_id, clnt_fname, email, …)
 */

$error  = $error  ?? null;
$secret = $secret ?? '';
$qrUri  = $qrUri  ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>CarGo — Set Up Two-Factor Authentication</title>
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
    body {
      font-family: 'Barlow', sans-serif;
      background:
        linear-gradient(rgba(10,10,11,0.85), rgba(10,10,11,0.92)),
        url('https://images.unsplash.com/photo-1503376780353-7e6692767b70?q=80&w=1920&auto=format&fit=crop');
      background-size: cover; background-position: center; background-attachment: fixed;
      color: var(--text-primary); min-height: 100vh;
      display: flex; flex-direction: column; overflow-x: hidden;
    }
    .page-content {
      flex: 1; display: flex; align-items: center;
      justify-content: center; padding: 2rem 1rem;
    }
    .wrapper { width: 100%; max-width: 460px; animation: fadeUp 0.6s ease; }
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(20px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    /* Logo */
    .logo { text-align: center; margin-bottom: 28px; }
    .logo-text {
      font-family: 'Barlow Condensed', sans-serif;
      font-weight: 800; font-size: 2.8rem;
      text-transform: uppercase; color: #fff; letter-spacing: .06em;
    }
    .logo-text span { color: var(--crimson-soft); }
    .logo-sub {
      font-size: .7rem; letter-spacing: .25em;
      text-transform: uppercase; color: var(--text-muted); margin-top: 4px;
    }

    /* Card */
    .card {
      background: rgba(17,17,19,0.82); backdrop-filter: blur(14px);
      border: 1px solid var(--border); padding: 36px;
      position: relative; overflow: hidden;
    }
    .card::before {
      content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px;
      background: linear-gradient(90deg, transparent, var(--crimson), transparent);
    }

    .card-heading h1 {
      font-family: 'Barlow Condensed', sans-serif;
      font-size: 1.65rem; text-transform: uppercase; letter-spacing: .04em;
    }
    .card-heading p { font-size: .84rem; color: var(--text-muted); margin: 8px 0 22px; line-height: 1.6; }

    /* Alert */
    .alert { padding: 11px 14px; margin-bottom: 18px; font-size: .82rem; border-left: 3px solid; }
    .alert-error { background: rgba(192,57,43,0.12); border-color: var(--crimson); color: #f09090; }

    /* Steps */
    .steps { list-style: none; margin-bottom: 22px; counter-reset: step; }
    .steps li {
      counter-increment: step;
      display: flex; align-items: flex-start; gap: 12px;
      font-size: .84rem; color: #bbb; margin-bottom: 12px; line-height: 1.5;
    }
    .steps li::before {
      content: counter(step);
      flex-shrink: 0;
      width: 22px; height: 22px;
      background: var(--crimson); color: #fff;
      font-family: 'Barlow Condensed', sans-serif; font-weight: 700; font-size: .8rem;
      display: flex; align-items: center; justify-content: center;
      margin-top: 1px;
    }

    /* QR code */
    .qr-wrapper {
      display: flex; justify-content: center;
      margin: 20px 0;
    }
    .qr-wrapper img {
      width: 180px; height: 180px;
      background: #fff; padding: 10px;
      border: 1px solid var(--border);
    }

    /* Manual secret */
    .secret-block { margin-bottom: 22px; }
    .secret-block label {
      display: block; font-size: .7rem; letter-spacing: .2em;
      text-transform: uppercase; color: var(--text-label); margin-bottom: 7px;
    }
    .secret-box {
      display: flex; gap: 8px; align-items: center;
    }
    .secret-box code {
      flex: 1; padding: 10px 12px;
      background: var(--bg-input); border: 1px solid var(--border);
      color: #ccc; font-size: .82rem; letter-spacing: .12em;
      word-break: break-all; user-select: all;
    }
    .copy-btn {
      flex-shrink: 0; padding: 10px 14px;
      background: transparent; border: 1px solid var(--border);
      color: var(--text-muted); font-size: .75rem; letter-spacing: .1em;
      text-transform: uppercase; cursor: pointer;
      transition: border-color .2s, color .2s;
      font-family: 'Barlow', sans-serif;
    }
    .copy-btn:hover { border-color: rgba(255,255,255,0.2); color: #ccc; }
    .copy-btn.copied { border-color: #27ae60; color: #27ae60; }

    /* OTP input */
    .field { margin-bottom: 20px; }
    .field label {
      display: block; font-size: .7rem; letter-spacing: .2em;
      text-transform: uppercase; color: var(--text-label); margin-bottom: 7px;
    }
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
    }
    .otp-input:focus {
      border-color: var(--border-focus);
      box-shadow: 0 0 0 3px var(--crimson-glow);
    }
    .otp-input::-webkit-outer-spin-button,
    .otp-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    .otp-input[type=number] { -moz-appearance: textfield; }

    /* Buttons */
    .btn-primary {
      width: 100%; padding: 13px;
      background: var(--crimson); border: none;
      color: #fff;
      font-family: 'Barlow Condensed', sans-serif;
      font-size: 1rem; font-weight: 700; letter-spacing: .15em; text-transform: uppercase;
      cursor: pointer;
      transition: background .2s, box-shadow .2s;
    }
    .btn-primary:hover { background: var(--crimson-soft); box-shadow: 0 4px 20px var(--crimson-glow); }

    .back-link {
      display: block; text-align: center;
      margin-top: 20px; font-size: .8rem;
      color: var(--text-muted); text-decoration: none;
    }
    .back-link:hover { color: #aaa; }

    .divider {
      border: none; border-top: 1px solid var(--border);
      margin: 22px 0;
    }
  </style>
</head>
<body>

<div class="page-content">
  <div class="wrapper">

    <div class="logo">
      <div class="logo-text">Car<span>Go</span></div>
      <div class="logo-sub">Premium Car Rental</div>
    </div>

    <div class="card">
      <div class="card-heading">
        <h1>Set Up 2-Factor Auth</h1>
        <p>
          Protect your account with an authenticator app.
          You only need to do this once.
        </p>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <!-- Step instructions -->
      <ol class="steps">
        <li>Install an authenticator app on your phone — <strong>Google Authenticator</strong> or <strong>Authy</strong> work great.</li>
        <li>Open the app and tap <strong>Add account</strong> → <strong>Scan QR code</strong>.</li>
        <li>Scan the QR code below, or enter the secret key manually.</li>
        <li>Type the 6-digit code from the app to confirm setup.</li>
      </ol>

      <!-- QR Code -->
      <div class="qr-wrapper">
        <img src="<?= htmlspecialchars($qrUri) ?>" alt="Scan this QR code with your authenticator app">
      </div>

      <hr class="divider">

      <!-- Manual secret key -->
      <div class="secret-block">
        <label>Or enter this key manually</label>
        <div class="secret-box">
          <code id="secretKey"><?= htmlspecialchars($secret) ?></code>
          <button type="button" class="copy-btn" id="copyBtn" onclick="copySecret()">Copy</button>
        </div>
      </div>

      <hr class="divider">

      <!-- Confirm with TOTP code -->
      <form method="POST" action="<?= BASE_URL ?>?page=mfa-verify" novalidate>
        <div class="field">
          <label for="totp_code">Enter the 6-digit code from your app</label>
          <input
            type="number"
            id="totp_code"
            name="totp_code"
            class="otp-input"
            placeholder="000000"
            maxlength="6"
            autocomplete="one-time-code"
            inputmode="numeric"
            autofocus
            required
          >
        </div>

        <button type="submit" class="btn-primary">Confirm &amp; Activate</button>
      </form>

      <a href="<?= BASE_URL ?>?page=login" class="back-link">← Cancel and go back to sign in</a>
    </div>

  </div>
</div>

<script>
function copySecret() {
  const key = document.getElementById('secretKey').textContent.trim();
  const btn = document.getElementById('copyBtn');

  navigator.clipboard.writeText(key).then(() => {
    btn.textContent = 'Copied!';
    btn.classList.add('copied');
    setTimeout(() => {
      btn.textContent = 'Copy';
      btn.classList.remove('copied');
    }, 2000);
  }).catch(() => {
    // Fallback for older browsers
    const range = document.createRange();
    range.selectNode(document.getElementById('secretKey'));
    window.getSelection().removeAllRanges();
    window.getSelection().addRange(range);
    document.execCommand('copy');
    window.getSelection().removeAllRanges();
    btn.textContent = 'Copied!';
    btn.classList.add('copied');
    setTimeout(() => {
      btn.textContent = 'Copy';
      btn.classList.remove('copied');
    }, 2000);
  });
}

// Auto-submit when 6 digits entered
document.getElementById('totp_code').addEventListener('input', function () {
  if (this.value.length === 6) {
    this.closest('form').submit();
  }
});
</script>

</body>
</html>