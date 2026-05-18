<?php
/**
 * View: auth/register.php
 * Variables injected by AuthController::register():
 *   $error   (string|null)  – single error flash
 *   $success (string|null)  – single success flash
 *   $errors  (array)        – field-level validation errors
 */

if (isset($_SESSION['client_id'])) {
    header('Location: ' . BASE_URL . '?page=dashboard');
    exit;
}

$error   = $error   ?? null;
$success = $success ?? null;
$errors  = $errors  ?? [];

// Re-populate fields on validation failure
function old(string $key): string {
    return htmlspecialchars($_POST[$key] ?? '');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>CarGo — Create Account</title>
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
      --border-err:   rgba(192,57,43,0.8);
      --text-primary: #f0f0f0;
      --text-muted:   #666;
      --text-label:   #999;
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
    .page-content {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem 1rem;
    }
    .wrapper { width: 100%; max-width: 500px; animation: fadeUp .6s ease; }
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
      background: rgba(17,17,19,0.82);
      backdrop-filter: blur(14px);
      border: 1px solid var(--border);
      padding: 36px;
      position: relative;
      overflow: hidden;
    }
    .card::before {
      content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px;
      background: linear-gradient(90deg, transparent, var(--crimson), transparent);
    }
    .card-heading h1 {
      font-family: 'Barlow Condensed', sans-serif;
      font-size: 1.65rem; text-transform: uppercase; letter-spacing: .04em;
    }
    .card-heading p  { font-size: .82rem; color: var(--text-muted); margin: 6px 0 22px; }
    .card-heading a  { color: var(--crimson-soft); text-decoration: none; }
    .card-heading a:hover { opacity: .8; }

    /* Alerts */
    .alert { padding: 11px 14px; margin-bottom: 18px; font-size: .82rem; border-left: 3px solid; }
    .alert-error   { background: rgba(192,57,43,0.12); border-color: var(--crimson);  color: #f09090; }
    .alert-success { background: rgba(39,174,96,0.12);  border-color: #27ae60;         color: #80d4a0; }

    /* Two-column grid for name fields */
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 0 16px; }

    /* Fields */
    .field { margin-bottom: 16px; }
    .field label {
      display: block; font-size: .7rem; letter-spacing: .2em;
      text-transform: uppercase; color: var(--text-label); margin-bottom: 7px;
    }
    .field input {
      width: 100%; padding: 12px 14px;
      background: var(--bg-input);
      border: 1px solid var(--border);
      color: #fff;
      font-family: 'Barlow', sans-serif; font-size: .9rem;
      outline: none;
      transition: border-color .2s, box-shadow .2s;
    }
    .field input:focus        { border-color: var(--border-focus); box-shadow: 0 0 0 3px var(--crimson-glow); }
    .field input.is-invalid   { border-color: var(--border-err); }
    .field-error { font-size: .75rem; color: #f09090; margin-top: 5px; }

    /* Password strength bar */
    .strength-bar { height: 3px; margin-top: 8px; background: #2a2a2e; overflow: hidden; }
    .strength-fill { height: 100%; width: 0; transition: width .3s, background .3s; }

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

    .terms {
      font-size: .75rem; color: var(--text-muted);
      text-align: center; margin-top: 14px;
    }
    .terms a { color: var(--crimson-soft); text-decoration: none; }
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
        <h1>Create Account</h1>
        <p>Already have one? <a href="<?= BASE_URL ?>?page=login">Sign in</a></p>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <form method="POST" action="<?= BASE_URL ?>?page=register" novalidate>

        <!-- Name row -->
        <div class="grid-2">
          <div class="field">
            <label for="clnt_fname">First Name</label>
            <input
              type="text" id="clnt_fname" name="clnt_fname"
              value="<?= old('clnt_fname') ?>"
              autocomplete="given-name"
              placeholder="Juan"
              class="<?= isset($errors['clnt_fname']) ? 'is-invalid' : '' ?>"
              required
            >
            <?php if (isset($errors['clnt_fname'])): ?>
              <div class="field-error"><?= htmlspecialchars($errors['clnt_fname']) ?></div>
            <?php endif; ?>
          </div>

          <div class="field">
            <label for="clnt_lname">Last Name</label>
            <input
              type="text" id="clnt_lname" name="clnt_lname"
              value="<?= old('clnt_lname') ?>"
              autocomplete="family-name"
              placeholder="dela Cruz"
              class="<?= isset($errors['clnt_lname']) ? 'is-invalid' : '' ?>"
              required
            >
            <?php if (isset($errors['clnt_lname'])): ?>
              <div class="field-error"><?= htmlspecialchars($errors['clnt_lname']) ?></div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Middle name (optional) -->
        <div class="field">
          <label for="clnt_mname">Middle Name <span style="color:#555">(optional)</span></label>
          <input
            type="text" id="clnt_mname" name="clnt_mname"
            value="<?= old('clnt_mname') ?>"
            autocomplete="additional-name"
            placeholder="Santos"
          >
        </div>

        <!-- Email -->
        <div class="field">
          <label for="email">Email Address</label>
          <input
            type="email" id="email" name="email"
            value="<?= old('email') ?>"
            autocomplete="email"
            placeholder="your@email.com"
            class="<?= isset($errors['email']) ? 'is-invalid' : '' ?>"
            required
          >
          <?php if (isset($errors['email'])): ?>
            <div class="field-error"><?= htmlspecialchars($errors['email']) ?></div>
          <?php endif; ?>
        </div>

        <!-- Phone -->
        <div class="field">
          <label for="clnt_phone_number">Phone Number</label>
          <input
            type="tel" id="clnt_phone_number" name="clnt_phone_number"
            value="<?= old('clnt_phone_number') ?>"
            autocomplete="tel"
            placeholder="09XX XXX XXXX"
            class="<?= isset($errors['clnt_phone_number']) ? 'is-invalid' : '' ?>"
            required
          >
          <?php if (isset($errors['clnt_phone_number'])): ?>
            <div class="field-error"><?= htmlspecialchars($errors['clnt_phone_number']) ?></div>
          <?php endif; ?>
        </div>

        <!-- Address -->
        <div class="field">
          <label for="adress">Address</label>
          <input
            type="text" id="adress" name="adress"
            value="<?= old('adress') ?>"
            autocomplete="street-address"
            placeholder="123 Rizal St, Iloilo City"
            class="<?= isset($errors['adress']) ? 'is-invalid' : '' ?>"
            required
          >
          <?php if (isset($errors['adress'])): ?>
            <div class="field-error"><?= htmlspecialchars($errors['adress']) ?></div>
          <?php endif; ?>
        </div>

        <!-- Password -->
        <div class="field">
          <label for="password">Password</label>
          <input
            type="password" id="password" name="password"
            autocomplete="new-password"
            placeholder="Min. 8 characters"
            class="<?= isset($errors['password']) ? 'is-invalid' : '' ?>"
            oninput="updateStrength(this.value)"
            required
          >
          <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
          <?php if (isset($errors['password'])): ?>
            <div class="field-error"><?= htmlspecialchars($errors['password']) ?></div>
          <?php endif; ?>
        </div>

        <!-- Confirm Password -->
        <div class="field">
          <label for="confirm_password">Confirm Password</label>
          <input
            type="password" id="confirm_password" name="confirm_password"
            autocomplete="new-password"
            placeholder="Re-enter password"
            class="<?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>"
            required
          >
          <?php if (isset($errors['confirm_password'])): ?>
            <div class="field-error"><?= htmlspecialchars($errors['confirm_password']) ?></div>
          <?php endif; ?>
        </div>

        <button type="submit" class="btn-primary">Create Account</button>

        <p class="terms">
          By signing up you agree to our
          <a href="<?= BASE_URL ?>?page=terms">Terms of Service</a> &amp;
          <a href="<?= BASE_URL ?>?page=privacy">Privacy Policy</a>.
        </p>

      </form>
    </div>

  </div>
</div>

<script>
function updateStrength(val) {
  const fill = document.getElementById('strengthFill');
  let score = 0;
  if (val.length >= 8)                         score++;
  if (/[A-Z]/.test(val))                       score++;
  if (/[0-9]/.test(val))                       score++;
  if (/[^A-Za-z0-9]/.test(val))               score++;

  const widths = ['0%', '25%', '50%', '75%', '100%'];
  const colors = ['transparent', '#e05252', '#e8a838', '#4caf50', '#27ae60'];
  fill.style.width      = widths[score];
  fill.style.background = colors[score];
}
</script>
</body>
</html>