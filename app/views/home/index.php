<?php /* View: home/index.php | Receives: $featuredCars */ ?>

<!-- ===== HERO — Full background image, AWG-style ===== -->
<section class="hero">
    <div class="hero-bg"></div>

    <div class="hero-content">
        <p class="hero-eyebrow">Premium Car Rental Service</p>
        <h1 class="hero-title">
            Professional Rental<br>
            <span>Services</span> for Cars,<br>
            Vans, and More.
        </h1>
        <p class="hero-sub">
            Transform your journey with our <strong>high-quality fleet</strong> —
            sedans, SUVs, vans, and pickups.
            Over <strong>7 years of experience</strong> in delivering reliable rides.
        </p>
        <div class="hero-cta">
            <a href="<?= BASE_URL ?>?page=price-calculator" class="btn btn-red btn-lg">Get a Free Quote →</a>
            <a href="<?= BASE_URL ?>?page=browse" class="btn btn-ghost btn-lg">View Our Fleet</a>
        </div>
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

<!-- ===== QUICK SEARCH BAR ===== -->
<div style="background:var(--black2); border-bottom:1px solid var(--grey);">
    <div class="container">
        <div class="quick-search">
            <p class="quick-search-title"> Quick Search</p>
            <div class="quick-search-grid">
                <div class="form-group" style="margin:0;"><label class="form-label">Pickup Date</label><input type="date" class="form-control"></div>
                <div class="form-group" style="margin:0;"><label class="form-label">Return Date</label><input type="date" class="form-control"></div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Car Type</label>
                    <select class="form-control"><option>Any Type</option><option>Sedan</option><option>SUV</option><option>Van</option><option>Pickup</option></select>
                </div>
                <a href="<?= BASE_URL ?>?page=browse" class="btn btn-red" style="border-radius:var(--radius);">Search</a>
            </div>
        </div>
    </div>
</div>

<!-- ===== WHY CARGO ===== -->
<section class="section">
    <div class="container">
        <div class="text-center" style="margin-bottom:3rem;">
            <span class="section-label">Why Choose Us</span>
            <h2 class="section-title">More Than Just a <span>Car Rental</span></h2>
            <p class="section-sub" style="margin:0 auto; max-width:480px;">Every ride comes with reliability, safety, and the service you deserve.</p>
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
        <div class="flex-between" style="margin-bottom:2.5rem;">
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
                <div class="card-img" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); display: flex; align-items: center; justify-content: center; color: #888; font-size: 48px; min-height: 200px; overflow: hidden;">
                    <?php if (!empty($car['image'])): ?>
                        <img src="<?= BASE_URL ?><?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model_name']) ?>" style="width:100%; height:100%; object-fit:cover;">
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
        <div class="text-center" style="background:var(--black2); border:1px solid var(--grey); border-top:3px solid var(--red); border-radius:12px; padding:4rem 2rem; position:relative; overflow:hidden;">
            <div style="position:absolute;inset:0;background:radial-gradient(ellipse 60% 80% at 50% 100%,rgba(224,27,27,.1),transparent 70%);pointer-events:none;"></div>
            <div style="position:relative;">
                <span class="section-label" style="display:block;">Ready to Drive?</span>
                <h2 class="section-title" style="margin:.4rem 0 1rem;">Hit the Road with <span>CarGo</span></h2>
                <p style="color:var(--white2); margin:0 auto 2rem; max-width:460px;">Get an instant price estimate or create a free account to unlock member rates.</p>
                <div style="display:flex; gap:1rem; justify-content:center; flex-wrap:wrap;">
                    <a href="<?= BASE_URL ?>?page=price-calculator" class="btn btn-red btn-lg">Get a Free Quote →</a>
                    <a href="<?= BASE_URL ?>?page=login" class="btn btn-ghost btn-lg">Create Account</a>
                </div>
            </div>
        </div>
    </div>
</section>