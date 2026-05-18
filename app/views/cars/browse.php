<?php
// browse.php — $cars is injected by the controller (Car::all() or Car::filtered())
// Filter params come in via GET so the sidebar "Apply" button can POST/GET correctly.

// ── Read active filter values (passed from controller or from GET) ──────────
$active_brand  = $_GET['brand'] ?? '';   // Filter by brand
$min_year      = isset($_GET['min_year']) ? (int)$_GET['min_year'] : 1990;
$max_year      = isset($_GET['max_year']) ? (int)$_GET['max_year'] : 2030;
$sort          = $_GET['sort'] ?? 'year_desc';

// ── Client-side filter + sort on the $cars array injected by the controller ──
$filtered = array_filter($cars ?? [], function($car) use ($active_brand, $min_year, $max_year) {
    // Brand filter (if selected)
    if (!empty($active_brand) && stripos($car['brand'], $active_brand) === false) {
        return false;
    }
    // Year filter
    if ((int)$car['year'] < $min_year || (int)$car['year'] > $max_year) {
        return false;
    }
    return true;
});

// Sort
usort($filtered, function($a, $b) use ($sort) {
    return match($sort) {
        'year_asc'   => (int)$a['year'] <=> (int)$b['year'],
        'brand_asc'  => strcasecmp($a['brand'], $b['brand']),
        default      => (int)$b['year'] <=> (int)$a['year'],   // year_desc
    };
});
$filtered = array_values($filtered);
?>

<style>
  @import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500&display=swap');

  /* ── SHARED HERO STYLE (consistent across all pages) ── */
  .page-hero {
    position: relative;
    min-height: 360px;
    display: flex;
    align-items: flex-end;
    overflow: hidden;
    background:
      linear-gradient(to right, rgba(0,0,0,.82) 0%, rgba(0,0,0,.60) 100%),
      url('https://images.unsplash.com/photo-1568605117036-5fe5e7bab0b7?w=1600&q=80')
      center/cover no-repeat;
  }

  .page-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
      radial-gradient(ellipse 55% 55% at 85% 30%, rgba(192,57,43,.10) 0%, transparent 60%),
      radial-gradient(ellipse 35% 45% at 15% 75%, rgba(192,57,43,.05) 0%, transparent 50%);
    pointer-events: none;
  }

  .hero-grid {
    position: absolute;
    inset: 0;
    background-image:
      linear-gradient(rgba(192,57,43,.05) 1px, transparent 1px),
      linear-gradient(90deg, rgba(192,57,43,.05) 1px, transparent 1px);
    background-size: 56px 56px;
    mask-image: linear-gradient(to bottom, transparent 0%, black 30%, black 70%, transparent 100%);
    -webkit-mask-image: linear-gradient(to bottom, transparent 0%, black 30%, black 70%, transparent 100%);
  }

  .hero-slash {
    position: absolute;
    top: -40px; right: -80px;
    width: 520px; height: 520px;
    border: 1px solid rgba(192,57,43,.07);
    border-radius: 50%;
    pointer-events: none;
  }

  .hero-slash::after {
    content: '';
    position: absolute;
    inset: 40px;
    border: 1px solid rgba(192,57,43,.04);
    border-radius: 50%;
  }

  .hero-ghost {
    position: absolute;
    bottom: -10px; left: 0;
    font-family: 'Bebas Neue', sans-serif;
    font-size: clamp(90px, 16vw, 220px);
    color: rgba(255,255,255,.028);
    letter-spacing: 6px;
    line-height: 1;
    user-select: none;
    pointer-events: none;
    white-space: nowrap;
  }

  .hero-inner {
    position: relative;
    z-index: 2;
    padding: 56px 48px;
    max-width: 1200px;
    margin: 0 auto;
    width: 100%;
  }

  .label-tag {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-size: 11px;
    letter-spacing: 3.5px;
    text-transform: uppercase;
    color: #c0392b;
    margin-bottom: 14px;
    font-weight: 500;
    font-family: 'DM Sans', sans-serif;
  }

  .label-tag::before {
    content: '';
    width: 28px; height: 2px;
    background: #c0392b;
    border-radius: 2px;
  }

  .hero-h1 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: clamp(56px, 9vw, 108px);
    line-height: .92;
    letter-spacing: 2px;
    color: #ffffff;
  }

  .hero-h1 .red { color: #c0392b; }

  .hero-sub {
    margin-top: 18px;
    font-size: 14px;
    color: #888888;
    font-weight: 300;
    font-family: 'DM Sans', sans-serif;
  }

  .hero-sub a { color: #888888; text-decoration: none; }
  .hero-sub a:hover { color: #c0392b; }
  .hero-sub .sep { margin: 0 6px; color: #222222; }

  @media (max-width: 900px) {
    .hero-inner { padding: 48px 24px; }
  }
</style>


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


<section class="section">
    <div class="container">
        <?php
        // Build base URL for filter form (preserves any other GET params cleanly)
        $filterBase = BASE_URL . '?page=browse';
        ?>

        <form method="GET" action="<?= BASE_URL ?>">
        <input type="hidden" name="page" value="browse">

        <div style="display:grid; grid-template-columns:240px 1fr; gap:2rem; align-items:start;">

            <!-- Filter Sidebar -->
            <aside style="background:var(--black2); border:1px solid var(--grey); border-radius:8px; padding:1.25rem;">
                <h3 style="font-family:'Barlow Condensed',sans-serif; font-size:.85rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; margin-bottom:1.25rem; padding-bottom:.75rem; border-bottom:1px solid var(--grey);">Filters</h3>

                <p style="font-family:'Barlow Condensed',sans-serif; font-size:.78rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:var(--muted); margin-bottom:.6rem;">Brand</p>
                <input type="text" name="brand" value="<?= htmlspecialchars($active_brand) ?>" placeholder="Search brand..." style="width:100%; padding:.5rem; background:var(--bg); border:1px solid var(--grey); border-radius:4px; color:var(--white); font-size:.9rem; margin-bottom:1.25rem;">

                <p style="font-family:'Barlow Condensed',sans-serif; font-size:.78rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:var(--muted); margin-bottom:.6rem;">Min Year</p>
                <input type="number" name="min_year" value="<?= (int)$min_year ?>" min="1990" max="2030" style="width:100%; padding:.5rem; background:var(--bg); border:1px solid var(--grey); border-radius:4px; color:var(--white); font-size:.9rem; margin-bottom:1.25rem;">

                <p style="font-family:'Barlow Condensed',sans-serif; font-size:.78rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:var(--muted); margin-bottom:.6rem;">Max Year</p>
                <input type="number" name="max_year" value="<?= (int)$max_year ?>" min="1990" max="2030" style="width:100%; padding:.5rem; background:var(--bg); border:1px solid var(--grey); border-radius:4px; color:var(--white); font-size:.9rem; margin-bottom:1.25rem;">

                <!-- Hidden sort to carry it through filter submit -->
                <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">

                <button type="submit" class="btn btn-red btn-sm btn-full">Apply</button>
                <a href="<?= BASE_URL ?>?page=browse" class="btn btn-ghost btn-sm btn-full" style="margin-top:.5rem;">Reset</a>
            </aside>

            <!-- Car Grid -->
            <div>
                <div class="flex-between" style="margin-bottom:1.5rem;">
                    <p style="color:var(--muted); font-size:.88rem;">
                        Showing <strong style="color:var(--white);"><?= count($filtered) ?> vehicle<?= count($filtered) !== 1 ? 's' : '' ?></strong>
                        <?php if (count($filtered) !== count($cars ?? [])): ?>
                            <span style="color:var(--muted);"> of <?= count($cars ?? []) ?> total</span>
                        <?php endif; ?>
                    </p>
                    <select name="sort" class="form-control"
                            style="width:auto; font-size:.85rem; padding:.4rem .8rem;"
                            onchange="this.form.submit()">
                        <option value="year_desc" <?= $sort === 'year_desc' ? 'selected' : '' ?>>Year: Newest First</option>
                        <option value="year_asc"  <?= $sort === 'year_asc'  ? 'selected' : '' ?>>Year: Oldest First</option>
                        <option value="brand_asc" <?= $sort === 'brand_asc' ? 'selected' : '' ?>>Brand: A to Z</option>
                    </select>
                </div>

                <?php if (empty($filtered)): ?>
                <div style="text-align:center; padding:4rem 0; color:var(--muted);">
                    <div style="font-size:3rem; margin-bottom:1rem;">🚗</div>
                    <p>No vehicles match your current filters.</p>
                    <a href="<?= BASE_URL ?>?page=browse" class="btn btn-ghost" style="margin-top:1rem;">Clear Filters</a>
                </div>
                <?php else: ?>
                <div class="grid-3">
                    <?php foreach ($filtered as $car): ?>
                    <div class="card">
                        <div class="card-img" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); display: flex; align-items: center; justify-content: center; color: #888; font-size: 48px; min-height: 200px; overflow: hidden;">
                            <?php if (!empty($car['image'])): ?>
                                <img src="<?= ASSET_URL ?><?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model_name']) ?>" style="width:100%; height:100%; object-fit:cover;">
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
                <?php endif; ?>
            </div>

        </div>
        </form>
    </div>
</section>