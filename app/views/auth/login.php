<?php
/**
 * View: auth/login.php
 * Variables injected by AuthController::login():
 *   $error   (string|null)  – single flash error
 *   $success (string|null)  – single flash success
 *   $errors  (array)        – field-level validation errors
 *
 * NOTE: All login logic (DB query, session, MFA redirect) lives in
 * AuthController::loginPost(). This file is a pure view — no PHP logic here.
 */

$error   = $error   ?? null;
$success = $success ?? null;
$errors  = $errors  ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>CarGo — Sign In</title>
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800&family=Barlow:wght@300;400;500&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --crimson: #c0392b; --crimson-soft: #e05252;
      --crimson-glow: rgba(192,57,43,0.22);
      --bg-input: #18181c;
      --border: rgba(255,255,255,0.07); --border-focus: rgba(192,57,43,0.5);
      --text-primary: #f0f0f0; --text-muted: #666; --text-label: #999;
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
    .page-content { flex: 1; display: flex; align-items: center; justify-content: center; padding: 2rem 1rem; }
    .wrapper { width: 100%; max-width: 500px; animation: fadeUp 0.6s ease; }
    @keyframes fadeUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }

    .logo { text-align: center; margin-bottom: 28px; }
    .logo-text { font-family: 'Barlow Condensed', sans-serif; font-weight: 800; font-size: 2.8rem; text-transform: uppercase; color: #fff; letter-spacing: .06em; }
    .logo-text span { color: var(--crimson-soft); }
    .logo-sub { font-size: .7rem; letter-spacing: .25em; text-transform: uppercase; color: var(--text-muted); margin-top: 4px; }

    .card {
      background: rgba(17,17,19,0.82); backdrop-filter: blur(14px);
      border: 1px solid var(--border); padding: 36px;
      position: relative; overflow: hidden;
    }
    .card::before {
      content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px;
      background: linear-gradient(90deg, transparent, var(--crimson), transparent);
    }
    .card-heading h1 { font-family: 'Barlow Condensed', sans-serif; font-size: 1.65rem; text-transform: uppercase; letter-spacing: .04em; }
    .card-heading p  { font-size: .82rem; color: var(--text-muted); margin: 6px 0 22px; }
    .card-heading a  { color: var(--crimson-soft); text-decoration: none; }

    .alert { padding: 11px 14px; margin-bottom: 18px; font-size: .82rem; border-left: 3px solid; }
    .alert-error   { background: rgba(192,57,43,0.12); border-color: var(--crimson); color: #f09090; }
    .alert-success { background: rgba(39,174,96,0.12);  border-color: #27ae60;      color: #80d4a0; }

    .field { margin-bottom: 16px; }
    .field label { display: block; font-size: .7rem; letter-spacing: .2em; text-transform: uppercase; color: var(--text-label); margin-bottom: 7px; }
    .field input {
      width: 100%; padding: 12px 14px; background: var(--bg-input);
      border: 1px solid var(--border); color: #fff;
      font-family: 'Barlow', sans-serif; font-size: .9rem; outline: none;
      transition: border-color .2s, box-shadow .2s;
    }
    .field input.is-invalid { border-color: rgba(192,57,43,0.8); }
    .field input:focus { border-color: var(--border-focus); box-shadow: 0 0 0 3px var(--crimson-glow); }
    .field-error { font-size: .75rem; color: #f09090; margin-top: 5px; }

    .row-between { display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; }
    .remember { display: flex; align-items: center; gap: 7px; font-size: .8rem; color: var(--text-muted); cursor: pointer; }
    .remember input[type=checkbox] { accent-color: var(--crimson); width: 14px; height: 14px; }
    .forgot { font-size: .8rem; color: var(--crimson-soft); text-decoration: none; }
    .forgot:hover { opacity: .8; }

    .btn-primary {
      width: 100%; padding: 13px; background: var(--crimson); border: none;
      color: #fff; font-family: 'Barlow Condensed', sans-serif;
      font-size: 1rem; font-weight: 700; letter-spacing: .15em; text-transform: uppercase;
      cursor: pointer; transition: background .2s, box-shadow .2s;
    }
    .btn-primary:hover { background: var(--crimson-soft); box-shadow: 0 4px 20px var(--crimson-glow); }

    .divider { text-align: center; margin: 20px 0; color: #444; font-size: .7rem; letter-spacing: .1em; text-transform: uppercase; }
    .btn-google {
      display: flex; align-items: center; justify-content: center; gap: 10px;
      width: 100%; padding: 11px; border: 1px solid var(--border); color: #fff;
      text-decoration: none; font-size: .86rem;
      transition: border-color .2s, background .2s; background: transparent;
    }
    .btn-google:hover { border-color: rgba(255,255,255,0.18); background: rgba(255,255,255,0.04); }
    .g-icon { width: 18px; height: 18px; flex-shrink: 0; }
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
        <h1>Welcome Back</h1>
        <p>No account? <a href="<?= BASE_URL ?>?page=register">Create one</a></p>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <!-- FIXED: action posts to the MVC route; controller handles DB + MFA redirect -->
      <form method="POST" action="<?= BASE_URL ?>?page=login" novalidate>
        <div class="field">
          <label for="email">Email Address</label>
          <input
            type="email" id="email" name="email"
            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
            autocomplete="email"
            placeholder="your@email.com"
            class="<?= isset($errors['email']) ? 'is-invalid' : '' ?>"
            required
          >
          <?php if (isset($errors['email'])): ?>
            <div class="field-error"><?= htmlspecialchars($errors['email']) ?></div>
          <?php endif; ?>
        </div>

        <div class="field">
          <label for="password">Password</label>
          <input
            type="password" id="password" name="password"
            autocomplete="current-password"
            placeholder="••••••••"
            class="<?= isset($errors['password']) ? 'is-invalid' : '' ?>"
            required
          >
          <?php if (isset($errors['password'])): ?>
            <div class="field-error"><?= htmlspecialchars($errors['password']) ?></div>
          <?php endif; ?>
        </div>

        <div class="row-between">
          <label class="remember">
            <input type="checkbox" name="remember"> Remember me
          </label>
          <a href="<?= BASE_URL ?>?page=forgot-password" class="forgot">Forgot password?</a>
        </div>

        <button type="submit" class="btn-primary">Sign In</button>
      </form>

      <div class="divider">or</div>

      <a href="<?= BASE_URL ?>?page=auth/google" class="btn-google">
        <svg class="g-icon" viewBox="0 0 24 24">
          <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
          <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
          <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
          <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
        </svg>
        Continue with Google
      </a>
    </div>

  </div>
</div>

</body>
</html>