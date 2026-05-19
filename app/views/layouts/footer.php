</main>

<footer class="site-footer">
    <div class="footer-inner">
        <div class="footer-brand">
            <a href="<?= BASE_URL ?>?page=home" class="logo">
                <span class="logo-text">CAR<span class="red">GO</span></span>
            </a>
            <p>Drive Your Way.</p>
        </div>
        <div class="footer-links">
            <div>
                <h4>Explore</h4>
                <a href="<?= BASE_URL ?>?page=home">Home</a>
                <a href="<?= BASE_URL ?>?page=browse">Browse Cars</a>
                <a href="<?= BASE_URL ?>?page=about">About Us</a>
                <a href="<?= BASE_URL ?>?page=contact">Contact</a>
            </div>

            <?php if (isset($_SESSION['client_role']) && $_SESSION['client_role'] === 'admin'): ?>
            <div>
                <h4>Manage</h4>
                <a href="<?= BASE_URL ?>?page=admin">Admin Dashboard</a>
                <a href="<?= BASE_URL ?>?page=admin-bookings">Manage Bookings</a>
                <a href="<?= BASE_URL ?>?page=admin-cars">Manage Fleet</a>
                <a href="<?= BASE_URL ?>?page=admin-drivers">Manage Drivers</a>
            </div>
            <?php else: ?>
            <div>
                <h4>Book a Car</h4>
                <a href="<?= BASE_URL ?>?page=price-calculator">Price Calculator</a>
                <a href="<?= BASE_URL ?>?page=driver-selection">Driver Options</a>
                <a href="<?= BASE_URL ?>?page=booking-form">Booking Form</a>
            </div>
            <?php endif; ?>

            <div>
                <h4>Account</h4>
                <?php if (isset($_SESSION['client_id'])): ?>
                    <?php if (isset($_SESSION['client_role']) && $_SESSION['client_role'] === 'admin'): ?>
                        <a href="<?= BASE_URL ?>?page=admin-clients">Clients</a>
                        <a href="<?= BASE_URL ?>?page=admin-payments">Payments</a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>?page=dashboard">My Dashboard</a>
                        <a href="<?= BASE_URL ?>?page=dashboard">My Bookings</a>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>?page=logout">Logout</a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>?page=login">Login / Register</a>
                    <a href="<?= BASE_URL ?>?page=dashboard">My Dashboard</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> <?= APP_NAME ?> Car Rentals. All rights reserved.</p>
    </div>
</footer>
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="/CARGO/assets/js/main.js"></script>
</body>
</html>