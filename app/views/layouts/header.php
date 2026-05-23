<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> — CarGo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@400;500;600&family=Barlow+Condensed:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/CARGO/assets/css/style.css">
    <link rel="stylesheet" href="/CARGO/assets/css/header.css">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>
<body>

<?php $currentPage = $_GET['page'] ?? 'home'; ?>
<?php $isAdmin    = isset($_SESSION['client_role']) && $_SESSION['client_role'] === 'admin'; ?>
<?php $isLoggedIn = isset($_SESSION['client_id']); ?>

<header class="site-header">
    <div class="header-inner">

        <a href="<?= BASE_URL ?>?page=home" class="logo">
            <span class="logo-text">CAR<span class="red">GO</span></span>
        </a>

        <nav class="nav" id="main-nav">
            <a href="<?= BASE_URL ?>?page=home"   class="<?= $currentPage==='home'   ? 'active':'' ?>">Home</a>
            <a href="<?= BASE_URL ?>?page=browse" class="<?= $currentPage==='browse' ? 'active':'' ?>">Cars</a>
            <?php if (!$isAdmin): ?>
                <a href="<?= BASE_URL ?>?page=about"   class="<?= $currentPage==='about'   ? 'active':'' ?>">About</a>
                <a href="<?= BASE_URL ?>?page=contact" class="<?= $currentPage==='contact' ? 'active':'' ?>">Contact</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>?page=admin" class="<?= $currentPage==='admin' ? 'active':'' ?>">Admin Panel</a>
            <?php endif; ?>
        </nav>

        <div class="header-actions">
            <?php if ($isLoggedIn): ?>
                <?php if ($isAdmin): ?>
                    <div class="admin-profile" tabindex="0">
                        <div class="admin-avatar">A</div>
                        <div class="admin-dropdown">
                            <a href="<?= BASE_URL ?>?page=admin-cars">&#9741; Fleet</a>
                            <a href="<?= BASE_URL ?>?page=admin-bookings">&#9741; Bookings</a>
                            <a href="<?= BASE_URL ?>?page=admin-drivers">&#9741; Drivers</a>
                            <a href="<?= BASE_URL ?>?page=admin-clients">&#9741; Clients</a>
                            <a href="<?= BASE_URL ?>?page=admin-payments">&#9741; Payments</a>
                            <a href="<?= BASE_URL ?>?page=admin-messages">&#9741; Messages</a>
                            <hr>
                            <a href="<?= BASE_URL ?>?page=logout" class="danger">&#10148; Sign Out</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>?page=dashboard" class="btn btn-ghost">My Dashboard</a>
                    <a href="<?= BASE_URL ?>?page=price-calculator" class="btn btn-red">Book Now</a>
                    <a href="<?= BASE_URL ?>?page=logout" class="btn btn-ghost">Logout</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="<?= BASE_URL ?>?page=login" class="btn btn-ghost">Login</a>
                <a href="<?= BASE_URL ?>?page=price-calculator" class="btn btn-red">Book Now</a>
            <?php endif; ?>
        </div>

        <button class="hamburger" id="hamburger">
            <span></span><span></span><span></span>
        </button>

    </div>
</header>

<main>