<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CARGO Admin — <?= $pageTitle ?? 'Dashboard' ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
   <link rel="stylesheet" href="<?= ASSET_URL ?>assets/css/admin-layout.css">
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