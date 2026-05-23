<?php
// View: cars/details.php
// Data received: $car (array)

// ✅ SAFE IMAGE
$image = !empty($car['image'])
    ? ASSET_URL . $car['image']
    : ASSET_URL . 'assets/images/default-car.jpg';
?>

<link rel="stylesheet" href="/CarGo/assets/css/car-details.css">

<!-- ── HERO ─────────────────────────────────────── -->
<div class="page-hero">
  <div class="hero-grid"></div>
  <div class="hero-ghost">DETAILS</div>

  <div class="hero-inner">
    <div class="label-tag">Vehicle Details</div>
    <h1 class="hero-h1"><?= htmlspecialchars($car['brand'] . ' ' . $car['model_name']) ?></h1>
    <div class="hero-badge"><?= htmlspecialchars($car['color']) ?></div>
  </div>
</div>

<!-- ── MAIN CONTENT ───────────────────────────────── -->
<section class="section">
    <div class="container">
        <div class="details-grid">

            <!-- LEFT COLUMN -->
            <div>

                <!-- Main image -->
                <div class="car-img-wrap">
                    <img src="<?= $image ?>"
                         class="car-main-img"
                         alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model_name']) ?>">
                </div>

                <!-- Specifications -->
                <h2 class="section-title details-section-title">Specifications</h2>
                <span class="red-bar"></span>

                <div class="specs-grid">
                    <?php
                    $specs = [
                        'Seats'        => $car['seats'] ?? '-',
                        'Transmission' => $car['transmission'] ?? '-',
                        'Fuel'         => $car['fuel_type'] ?? '-',
                        'Engine'       => $car['engine'] ?? '-',
                        'Year'         => $car['year'] ?? '-',
                        'Status'       => '<span class="badge badge-green">Available</span>',
                    ];
                    foreach ($specs as $label => $value):
                    ?>
                    <div class="spec-tile">
                        <p class="spec-tile-label"><?= htmlspecialchars($label) ?></p>
                        <p class="spec-tile-value"><?= $value ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Description -->
                <h2 class="section-title details-section-title">About This Vehicle</h2>
                <span class="red-bar"></span>

                <p class="about-text">
                    The <?= htmlspecialchars($car['brand'] . ' ' . $car['model_name']) ?> is one of our most popular rentals — perfect for city drives and long-distance trips.
                    All vehicles are regularly serviced, fully insured, and thoroughly cleaned before each rental.
                </p>

            </div>

            <!-- RIGHT COLUMN — Booking Sidebar -->
            <div class="booking-sidebar">

                <p class="booking-rate">
                    ₱<?= number_format($car['daily_rate'] ?? 0) ?>
                    <span class="booking-rate-unit"> / day</span>
                </p>

                <div class="form-group">
                    <label>Pickup Date</label>
                    <input type="date" class="form-control">
                </div>

                <div class="form-group">
                    <label>Return Date</label>
                    <input type="date" class="form-control">
                </div>

                <a href="<?= BASE_URL ?>?page=price-calculator&car_id=<?= $car['car_id'] ?>"
                   class="btn btn-red btn-full">
                    Book This Car →
                </a>

                <a href="<?= BASE_URL ?>?page=browse"
                   class="btn btn-ghost btn-full booking-back">
                    ← Back
                </a>

            </div>

        </div>
    </div>
</section>