<?php /* View: home/index.php | Receives: $featuredCars */ ?>

<link rel="stylesheet" href="<?= ASSET_URL ?>assets/css/home.css">

<!-- ===== HERO ===== -->
<section class="hero">
    <div class="hero-bg"></div>

    <div class="hero-content">
        <p class="hero-eyebrow">Premium Car Rental Service</p>
        <h1 class="hero-title">
            Professional Rental<br>
            <span>Services</span> for Cars,<br>
            Vans, and More.
        </h1>
        
        <div class="hero-cta">
            <a href="<?= BASE_URL ?>?page=price-calculator" class="btn btn-red btn-lg">Get a Free Quote →</a>
            <a href="<?= BASE_URL ?>?page=browse" class="btn btn-ghost btn-lg">View Our Fleet</a>
        </div>
        <br><br><br><br><br>
    </div>

    <!-- Bottom stats bar -->
    <div class="hero-stats">
        <div class="hero-stats-inner">
            <div class="hero-stat"><div class="hero-stat-num">50+</div><div class="hero-stat-label">Vehicles in Fleet</div></div>
            <div class="hero-stat"><div class="hero-stat-num">5K+</div><div class="hero-stat-label">Happy Customers</div></div>
            <div class="hero-stat"><div class="hero-stat-num">7</div><div class="hero-stat-label">Years of Service</div></div>
            <div class="hero-stat"><div class="hero-stat-num">4.9 ⭐</div><div class="hero-stat-label">Average Rating</div></div>
        </div>
    </div>
</section>


<!-- ===== WHY CARGO ===== -->
<section class="section">
    <div class="container">
        <div class="why-cargo-head">
            <span class="section-label">Why Choose Us</span>
            <h2 class="section-title">More Than Just a <span>Car Rental</span></h2>
            <p class="section-sub why-cargo-head">Every ride comes with reliability, safety, and the service you deserve.</p>
        </div>
        <div class="grid-4">
            <?php foreach ([
                ['🛡️','Fully Insured','Every vehicle is covered for your peace of mind on every trip.'],
                ['🧭','GPS Tracking','Real-time tracking on all vehicles for safety and transparency.'],
                ['🤝','Pro Drivers','Licensed, background-checked drivers available on request.'],
                ['⚡','Instant Booking','Book in minutes. No hidden fees, no paperwork hassles.'],
            ] as $f): ?>
            <div class="feature-card">
                <div class="feature-icon"><?= $f[0] ?></div>
                <h3 class="feature-title"><?= $f[1] ?></h3>
                <p class="feature-desc"><?= $f[2] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== FEATURED CARS ===== -->
<section class="section section-dark">
    <div class="container">
        <div class="featured-head">
            <div>
                <span class="section-label">Our Fleet</span>
                <h2 class="section-title">Featured <span>Vehicles</span></h2>
                <p class="section-sub">Top picks from our fleet — ready for your next trip.</p>
            </div>
            <a href="<?= BASE_URL ?>?page=browse" class="btn btn-outline">View All →</a>
        </div>
        <div class="grid-3">
            <?php foreach ($featuredCars as $car): ?>
            <div class="card">
                <div class="card-img card-img-placeholder">
                    <?php if (!empty($car['image'])): ?>
                        <img src="<?= ASSET_URL ?><?= htmlspecialchars($car['image']) ?>"
                             alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model_name']) ?>">
                    <?php else: ?>
                        🚗
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="flex-between">
                        <h3 class="card-title"><?= htmlspecialchars($car['brand'] . ' ' . $car['model_name']) ?></h3>
                    </div>
                    <div class="card-meta">
                        <span>📅 <?= htmlspecialchars($car['year']) ?></span>
                        <span>🎨 <?= htmlspecialchars($car['color']) ?></span>
                        <span>📋 <?= htmlspecialchars($car['plate_number']) ?></span>
                    </div>
                </div>
                <div class="card-foot">
                    <a href="<?= BASE_URL ?>?page=car-details&id=<?= (int)$car['car_id'] ?>" class="btn btn-dark btn-sm" style="flex:1;">Details</a>
                    <a href="<?= BASE_URL ?>?page=price-calculator&car_id=<?= (int)$car['car_id'] ?>" class="btn btn-red btn-sm" style="flex:1;">Book</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== CTA ===== -->
<section class="section">
    <div class="container">
        <div class="cta-card">
            <div class="cta-card-inner">
                <span class="section-label">Ready to Drive?</span>
                <h2 class="section-title">Hit the Road with <span>CarGo</span></h2>
                <p>Get an instant price estimate or create a free account to unlock member rates.</p>
                <div class="cta-card-btns">
                    <a href="<?= BASE_URL ?>?page=price-calculator" class="btn btn-red btn-lg">Get a Free Quote →</a>
                    <a href="<?= BASE_URL ?>?page=login" class="btn btn-ghost btn-lg">Create Account</a>
                </div>
            </div>
        </div>
    </div>
</section>