<?php
// browse.php — $cars is injected by the controller (Car::all() or Car::filtered())
// Filter params come in via GET so the sidebar "Apply" button can POST/GET correctly.

// ── Read active filter values (passed from controller or from GET) ──────────
$active_brand  = $_GET['brand'] ?? '';
$min_year      = isset($_GET['min_year']) ? (int)$_GET['min_year'] : 1990;
$max_year      = isset($_GET['max_year']) ? (int)$_GET['max_year'] : 2030;
$sort          = $_GET['sort'] ?? 'year_desc';

// ── Client-side filter + sort on the $cars array injected by the controller ──
$filtered = array_filter($cars ?? [], function($car) use ($active_brand, $min_year, $max_year) {
    if (!empty($active_brand) && stripos($car['brand'], $active_brand) === false) return false;
    if ((int)$car['year'] < $min_year || (int)$car['year'] > $max_year) return false;
    return true;
});

usort($filtered, function($a, $b) use ($sort) {
    return match($sort) {
        'year_asc'   => (int)$a['year'] <=> (int)$b['year'],
        'brand_asc'  => strcasecmp($a['brand'], $b['brand']),
        default      => (int)$b['year'] <=> (int)$a['year'],
    };
});
$filtered = array_values($filtered);
?>

<link rel="stylesheet" href="/CarGo/assets/css/browse.css">

<!-- ── HERO ─────────────────────────────────────── -->
<div class="page-hero">
  <div class="hero-grid"></div>
  <div class="hero-slash"></div>
  <div class="hero-ghost">OUR FLEET</div>
  <div class="hero-inner">
    <div class="label-tag">Our Fleet</div>
    <h1 class="hero-h1">Browse <span class="red">Cars</span></h1>
    <p class="hero-sub"></p>
  </div>
</div>

<!-- ── BROWSE SECTION ────────────────────────────── -->
<section class="section">
    <div class="container">

        <form method="GET" action="<?= BASE_URL ?>">
        <input type="hidden" name="page" value="browse">

        <div class="browse-grid">

            <!-- ── FILTER SIDEBAR ── -->
            <aside class="filter-sidebar">
                <h3 class="filter-title">Filters</h3>

                <p class="filter-label">Brand</p>
                <input type="text" name="brand"
                       value="<?= htmlspecialchars($active_brand) ?>"
                       placeholder="Search brand..."
                       class="filter-input">

                <p class="filter-label">Min Year</p>
                <input type="number" name="min_year"
                       value="<?= (int)$min_year ?>"
                       min="1990" max="2030"
                       class="filter-input">

                <p class="filter-label">Max Year</p>
                <input type="number" name="max_year"
                       value="<?= (int)$max_year ?>"
                       min="1990" max="2030"
                       class="filter-input">

                <!-- Carry sort value through filter submit -->
                <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">

                <button type="submit" class="btn btn-red btn-sm btn-full">Apply</button>
                <a href="<?= BASE_URL ?>?page=browse"
                   class="btn btn-ghost btn-sm btn-full filter-reset">Reset</a>
            </aside>

            <!-- ── CAR GRID ── -->
            <div>

                <!-- Results toolbar -->
                <div class="results-toolbar">
                    <p class="results-count">
                        Showing <strong><?= count($filtered) ?> vehicle<?= count($filtered) !== 1 ? 's' : '' ?></strong>
                        <?php if (count($filtered) !== count($cars ?? [])): ?>
                            <span class="total"> of <?= count($cars ?? []) ?> total</span>
                        <?php endif; ?>
                    </p>
                    <select name="sort" class="form-control sort-select"
                            onchange="this.form.submit()">
                        <option value="year_desc" <?= $sort === 'year_desc' ? 'selected' : '' ?>>Year: Newest First</option>
                        <option value="year_asc"  <?= $sort === 'year_asc'  ? 'selected' : '' ?>>Year: Oldest First</option>
                        <option value="brand_asc" <?= $sort === 'brand_asc' ? 'selected' : '' ?>>Brand: A to Z</option>
                    </select>
                </div>

                <?php if (empty($filtered)): ?>
                <!-- Empty state -->
                <div class="empty-state">
                    <div class="empty-state-icon">🚗</div>
                    <p>No vehicles match your current filters.</p>
                    <a href="<?= BASE_URL ?>?page=browse" class="btn btn-ghost">Clear Filters</a>
                </div>

                <?php else: ?>
                <!-- Car cards -->
                <div class="grid-3">
                    <?php foreach ($filtered as $car): ?>
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
                            <a href="<?= BASE_URL ?>?page=car-details&id=<?= (int)$car['car_id'] ?>"
                               class="btn btn-dark btn-sm">Details</a>
                            <a href="<?= BASE_URL ?>?page=price-calculator&car_id=<?= (int)$car['car_id'] ?>"
                               class="btn btn-red btn-sm">Book</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

            </div>

        </div>
        </form>

    </div>
</section>