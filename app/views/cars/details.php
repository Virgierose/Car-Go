<?php
// View: cars/details.php
// Data received: $car (array)

// ✅ SAFE IMAGE
$image = !empty($car['image'])
    ? BASE_URL . $car['image']
    : BASE_URL . 'assets/images/default-car.jpg';
?>

<style>
/* ── HERO FIX ───────────────────────── */
.page-hero {
  position: relative;
  min-height: 320px;
  display: flex;
  align-items: flex-end;
  background: linear-gradient(160deg, #0d0d0d 0%, #1a0f0f 60%, #0a0a0a 100%);
  overflow: hidden;
}

.page-hero::before {
  content: '';
  position: absolute;
  inset: 0;
  background:
    radial-gradient(ellipse 60% 60% at 80% 40%, rgba(192,57,43,.12), transparent),
    radial-gradient(ellipse 40% 40% at 20% 70%, rgba(192,57,43,.06), transparent);
}

.hero-grid {
  position: absolute;
  inset: 0;
  background-image:
    linear-gradient(rgba(192,57,43,.04) 1px, transparent 1px),
    linear-gradient(90deg, rgba(192,57,43,.04) 1px, transparent 1px);
  background-size: 60px 60px;
}

.hero-ghost {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-family: 'Bebas Neue', sans-serif;
  font-size: 120px;
  color: rgba(255,255,255,.03);
  letter-spacing: 6px;
}

.hero-inner {
  position: relative;
  z-index: 2;
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  padding: 80px 40px 40px;
}

.label-tag {
  font-size: 11px;
  letter-spacing: 3px;
  text-transform: uppercase;
  color: var(--red);
  margin-bottom: 12px;
}

.hero-h1 {
  font-family: 'Bebas Neue', sans-serif;
  font-size: 56px;
  letter-spacing: 1px;
  margin-bottom: 10px;
}

.hero-sub {
  font-size: 13px;
  color: var(--muted);
}

.hero-sub a {
  color: var(--muted);
  text-decoration: none;
}

.hero-sub a:hover {
  color: var(--red);
}

.sep {
  margin: 0 6px;
  color: #444;
}

.hero-badge {
  margin-top: 15px;
  display: inline-block;
  background: rgba(192,57,43,.15);
  color: var(--red);
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
}


.car-main-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 10px;
}

.car-thumb {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 6px;
}
</style>


<div class="page-hero">
  <div class="hero-grid"></div>
  <div class="hero-ghost">DETAILS</div>

  <div class="hero-inner">
    <div class="label-tag">Vehicle Details</div>

    <h1 class="hero-h1"><?= htmlspecialchars($car['name']) ?></h1>

    <div class="hero-badge"><?= htmlspecialchars($car['type']) ?></div>
  </div>
</div>


<section class="section">
    <div class="container">
        <div style="display:grid; grid-template-columns:1fr 360px; gap:2.5rem; align-items:start;">

            <!-- LEFT -->
            <div>

                <!-- MAIN IMAGE -->
                <div style="background:var(--black3); border:1px solid var(--grey); border-radius:10px; aspect-ratio:16/9; overflow:hidden; margin-bottom:.75rem;">
                    <img src="<?= $image ?>" class="car-main-img" alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model_name']) ?>" style="width:100%; height:100%; object-fit:cover;">
                </div>

                <!-- THUMBNAILS -->
                <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:.5rem; margin-bottom:2rem;">
                    <?php for ($i = 0; $i < 4; $i++): ?>
                    <div style="background:var(--black3); border:1px solid var(--grey); border-radius:6px; aspect-ratio:16/9; overflow:hidden;">
                        <img src="<?= $image ?>" class="car-thumb" alt="">
                    </div>
                    <?php endfor; ?>
                </div>

                <!-- SPECS -->
                <h2 class="section-title" style="margin-bottom:.75rem;">Specifications</h2>
                <span class="red-bar"></span>

                <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:.75rem; margin-bottom:2rem;">
                    <?php
                    $specs = [
                        'Seats'        => $car['seats'] ?? '-',
                        'Transmission' => $car['trans'] ?? '-',
                        'Fuel'         => $car['fuel'] ?? '-',
                        'Engine'       => $car['engine'] ?? '-',
                        'Year'         => $car['year'] ?? '-',
                        'Status'       => '<span class="badge badge-green">Available</span>',
                    ];
                    foreach ($specs as $label => $value):
                    ?>
                    <div style="background:var(--black3); border:1px solid var(--grey); border-radius:6px; padding:.75rem;">
                        <p style="font-size:.72rem; text-transform:uppercase; color:var(--muted);">
                            <?= htmlspecialchars($label) ?>
                        </p>
                        <p style="font-weight:700; margin-top:.2rem;">
                            <?= $value ?>
                        </p>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- DESCRIPTION -->
                <h2 class="section-title" style="margin-bottom:.75rem;">About This Vehicle</h2>
                <span class="red-bar"></span>

                <p style="color:var(--white2); line-height:1.85;">
                    The <?= htmlspecialchars($car['name']) ?> is one of our most popular rentals — perfect for city drives and long-distance trips.
                    All vehicles are regularly serviced, fully insured, and thoroughly cleaned before each rental.
                </p>

            </div>

            <!-- RIGHT -->
            <div style="background:var(--black2); border:1px solid var(--grey); border-radius:10px; padding:1.5rem; position:sticky; top:80px;">
                
                <p style="font-size:2.75rem; color:var(--red);">
                    ₱<?= number_format($car['price'] ?? 0) ?>
                    <span style="font-size:.85rem; color:var(--muted);"> / day</span>
                </p>

                <div class="form-group">
                    <label>Pickup Date</label>
                    <input type="date" class="form-control">
                </div>

                <div class="form-group">
                    <label>Return Date</label>
                    <input type="date" class="form-control">
                </div>

                <a href="<?= BASE_URL ?>?page=price-calculator&car_id=<?= $car['id'] ?>" class="btn btn-red btn-full">
                    Book This Car →
                </a>
           <br><br>
                <a href="<?= BASE_URL ?>?page=browse" class="btn btn-ghost btn-full">
                    ← Back
                </a>

            </div>

        </div>
    </div>
</section>