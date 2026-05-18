<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CARGO Admin — <?= $pageTitle ?? 'Dashboard' ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --red:        #C0111F;
            --red-dark:   #8B0000;
            --red-glow:   rgba(192,17,31,0.35);
            --black:      #0A0A0A;
            --black-mid:  #141414;
            --black-card: #1C1C1C;
            --silver:     #A8A8A8;
            --silver-lt:  #D4D4D4;
            --white:      #F2F2F2;
            --border:     rgba(168,168,168,0.15);
            --transition: 0.25s cubic-bezier(0.4,0,0.2,1);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Barlow', sans-serif;
            background: var(--black);
            color: var(--white);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }

        /* ── SIDEBAR ── */
        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: var(--black-mid);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0;
            z-index: 100;
        }

        .sidebar-brand {
            padding: 28px 24px 20px;
            border-bottom: 1px solid var(--border);
        }
        .sidebar-brand .logo-text {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 2rem;
            letter-spacing: 4px;
            color: var(--red);
            text-shadow: 0 0 20px var(--red-glow);
            line-height: 1;
        }
        .sidebar-brand .logo-sub {
            font-size: 0.65rem;
            letter-spacing: 3px;
            color: var(--silver);
            text-transform: uppercase;
            margin-top: 2px;
        }

        .sidebar-nav { flex: 1; padding: 16px 0; }

        .nav-section-label {
            font-size: 0.6rem;
            letter-spacing: 3px;
            color: var(--silver);
            text-transform: uppercase;
            padding: 16px 24px 6px;
            opacity: 0.6;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 24px;
            color: var(--silver);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: var(--transition);
            border-left: 3px solid transparent;
            position: relative;
        }
        .nav-item i { width: 18px; text-align: center; font-size: 0.9rem; }
        .nav-item:hover {
            color: var(--white);
            background: rgba(192,17,31,0.08);
            border-left-color: var(--red);
        }
        .nav-item.active {
            color: var(--white);
            background: rgba(192,17,31,0.12);
            border-left-color: var(--red);
        }
        .nav-item.active i { color: var(--red); }

        .sidebar-footer {
            padding: 16px 24px;
            border-top: 1px solid var(--border);
            font-size: 0.78rem;
            color: var(--silver);
        }
        .sidebar-footer a {
            color: var(--red);
            text-decoration: none;
            font-weight: 600;
        }
        .sidebar-footer a:hover { text-decoration: underline; }

        /* ── MAIN ── */
        .main-wrapper {
            margin-left: 260px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            width: calc(100% - 260px);
        }

        /* ── TOPBAR ── */
        .topbar {
            height: 64px;
            background: var(--black-mid);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .topbar-title {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 1.4rem;
            letter-spacing: 2px;
            color: var(--silver-lt);
        }
        .topbar-actions { display: flex; align-items: center; gap: 16px; }
        .topbar-admin {
            display: flex; align-items: center; gap: 10px;
            font-size: 0.82rem; color: var(--silver);
        }
        .avatar {
            width: 34px; height: 34px;
            background: var(--red-dark);
            border-radius: 50%;
            display: grid; place-items: center;
            font-family: 'Bebas Neue', sans-serif;
            font-size: 0.95rem;
            color: var(--white);
            border: 2px solid var(--red);
        }

        /* ── CONTENT ── */
        .content { flex: 1; padding: 20px 24px; }

        /* ── CARDS / STATS ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }
        .stat-card {
            background: var(--black-card);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 22px 24px;
            position: relative;
            overflow: hidden;
            transition: var(--transition);
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: var(--red);
        }
        .stat-card:hover {
            border-color: rgba(192,17,31,0.4);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.4);
        }
        .stat-icon {
            width: 40px; height: 40px;
            background: rgba(192,17,31,0.12);
            border-radius: 8px;
            display: grid; place-items: center;
            margin-bottom: 14px;
            color: var(--red);
            font-size: 1rem;
        }
        .stat-value {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 2.2rem;
            letter-spacing: 2px;
            color: var(--white);
            line-height: 1;
        }
        .stat-label {
            font-size: 0.72rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--silver);
            margin-top: 4px;
        }

        /* ── TABLE CARD ── */
        .card {
            background: var(--black-card);
            border: 1px solid var(--border);
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 24px;
        }
        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 24px;
            border-bottom: 1px solid var(--border);
            background: rgba(0,0,0,0.2);
        }
        .card-title {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 1.1rem;
            letter-spacing: 2px;
            color: var(--silver-lt);
        }

        /* ── BUTTONS ── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 18px;
            border-radius: 5px;
            font-family: 'Barlow', sans-serif;
            font-size: 0.82rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            cursor: pointer;
            text-decoration: none;
            transition: var(--transition);
            border: none;
        }
        .btn-red {
            background: var(--red);
            color: var(--white);
        }
        .btn-red:hover {
            background: var(--red-dark);
            box-shadow: 0 0 16px var(--red-glow);
        }
        .btn-outline {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--silver);
        }
        .btn-outline:hover { border-color: var(--silver); color: var(--white); }
        .btn-icon {
            padding: 7px 10px;
            font-size: 0.8rem;
        }
        .btn-danger { background: rgba(192,17,31,0.15); color: var(--red); border: 1px solid rgba(192,17,31,0.3); }
        .btn-danger:hover { background: var(--red); color: var(--white); }
        .btn-edit { background: rgba(168,168,168,0.1); color: var(--silver-lt); border: 1px solid rgba(168,168,168,0.2); }
        .btn-edit:hover { background: rgba(168,168,168,0.2); color: var(--white); }

        /* ── TABLE ── */
        table { width: 100%; border-collapse: collapse; }
        thead th {
            background: rgba(0,0,0,0.3);
            padding: 12px 16px;
            text-align: left;
            font-size: 0.65rem;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: var(--silver);
            font-weight: 600;
            border-bottom: 1px solid var(--border);
        }
        tbody tr {
            border-bottom: 1px solid rgba(168,168,168,0.07);
            transition: var(--transition);
        }
        tbody tr:hover { background: rgba(192,17,31,0.04); }
        tbody td {
            padding: 13px 16px;
            font-size: 0.875rem;
            color: var(--silver-lt);
            vertical-align: middle;
        }
        .td-actions { display: flex; gap: 8px; }

        /* ── BADGES ── */
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .badge-available  { background: rgba(34,197,94,0.15);  color: #4ade80; }
        .badge-rented     { background: rgba(192,17,31,0.15);  color: #f87171; }
        .badge-maintenance{ background: rgba(251,191,36,0.15); color: #fbbf24; }

        /* ── FORMS ── */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { display: flex; flex-direction: column; gap: 7px; }
        .form-group.full-width { grid-column: 1 / -1; }
        .form-label {
            font-size: 0.7rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--silver);
            font-weight: 600;
        }
        .form-control {
            background: rgba(0,0,0,0.4);
            border: 1px solid var(--border);
            border-radius: 5px;
            padding: 10px 14px;
            font-family: 'Barlow', sans-serif;
            font-size: 0.875rem;
            color: var(--white);
            transition: var(--transition);
            outline: none;
        }
        .form-control:focus {
            border-color: var(--red);
            box-shadow: 0 0 0 3px rgba(192,17,31,0.15);
        }
        .form-control option { background: var(--black-card); }
        select.form-control { cursor: pointer; }

        /* ── ALERT ── */
        .alert {
            padding: 12px 18px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 0.875rem;
            display: flex; align-items: center; gap: 10px;
        }
        .alert-success { background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.3); color: #4ade80; }
        .alert-error   { background: rgba(192,17,31,0.1); border: 1px solid rgba(192,17,31,0.3); color: #f87171; }

        /* ── SEARCH BAR ── */
        .search-bar {
            display: flex; align-items: center; gap: 10px;
        }
        .search-input {
            background: rgba(0,0,0,0.4);
            border: 1px solid var(--border);
            border-radius: 5px;
            padding: 7px 14px;
            color: var(--white);
            font-family: 'Barlow', sans-serif;
            font-size: 0.85rem;
            outline: none;
            width: 220px;
            transition: var(--transition);
        }
        .search-input:focus { border-color: var(--red); }
        .search-input::placeholder { color: var(--silver); opacity: 0.6; }

        /* ── DIVIDER ── */
        .red-rule {
            height: 2px;
            background: linear-gradient(to right, var(--red), transparent);
            margin-bottom: 20px; /* FIX: reduced from 28px to 20px */
        }

        /* ── SCROLLBAR ── */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: var(--black); }
        ::-webkit-scrollbar-thumb { background: var(--red-dark); border-radius: 3px; }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="logo-text">CARGO</div>
        <div class="logo-sub">Admin Control Panel</div>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section-label">Overview</div>
        <a href="<?= BASE_URL ?>?page=admin" class="nav-item <?= ($activePage ?? '') === 'admin' ? 'active' : '' ?>">
            <i class="fas fa-gauge-high"></i> Dashboard
        </a>

        <div class="nav-section-label">Fleet</div>
        <a href="<?= BASE_URL ?>?page=admin-cars" class="nav-item <?= ($activePage ?? '') === 'admin-cars' ? 'active' : '' ?>">
            <i class="fas fa-car"></i> Manage Cars
        </a>
        <a href="<?= BASE_URL ?>?page=admin-cars-create" class="nav-item <?= ($activePage ?? '') === 'admin-cars-create' ? 'active' : '' ?>">
            <i class="fas fa-circle-plus"></i> Add New Car
        </a>
        <a href="<?= BASE_URL ?>?page=admin-car-models" class="nav-item <?= ($activePage ?? '') === 'admin-car-models' ? 'active' : '' ?>">
            <i class="fas fa-layer-group"></i> Car Models
        </a>

        <div class="nav-section-label">Operations</div>
        <a href="<?= BASE_URL ?>?page=admin-bookings" class="nav-item <?= ($activePage ?? '') === 'admin-bookings' ? 'active' : '' ?>">
            <i class="fas fa-calendar-check"></i> Bookings
        </a>
        <a href="<?= BASE_URL ?>?page=admin-drivers" class="nav-item <?= ($activePage ?? '') === 'admin-drivers' ? 'active' : '' ?>">
            <i class="fas fa-id-card"></i> Drivers
        </a>
        <a href="<?= BASE_URL ?>?page=admin-clients" class="nav-item <?= ($activePage ?? '') === 'admin-clients' ? 'active' : '' ?>">
            <i class="fas fa-users"></i> Clients
        </a>
        <a href="<?= BASE_URL ?>?page=admin-payments" class="nav-item <?= ($activePage ?? '') === 'admin-payments' ? 'active' : '' ?>">
            <i class="fas fa-credit-card"></i> Payments
        </a>
        <a href="<?= BASE_URL ?>?page=admin-messages" class="nav-item <?= ($activePage ?? '') === 'admin-messages' ? 'active' : '' ?>">
            <i class="fas fa-envelope"></i> Messages
        </a>
    </nav>
    <div class="sidebar-footer">
        Logged in as <strong style="color:var(--silver-lt)">Admin</strong><br>
        <a href="<?= BASE_URL ?>?page=logout"><i class="fas fa-right-from-bracket"></i> Sign Out</a>
    </div>
</aside>

<!-- MAIN -->
<div class="main-wrapper">
    <header class="topbar">
        <span class="topbar-title"><?= $pageTitle ?? 'Dashboard' ?></span>
        <div class="topbar-actions">
            <div class="topbar-admin">
                <div class="avatar">A</div>
                <span>Administrator</span>
            </div>
        </div>
    </header>
    <main class="content">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><i class="fas fa-triangle-exclamation"></i> <?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <?= $content ?? '' ?>
    </main>
</div>

</body>
</html>