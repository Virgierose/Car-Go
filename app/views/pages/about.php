<?php ?>

 <link rel="stylesheet" href="<?= ASSET_URL ?>assets/css/about.css">

<?php
// Tell the header to render as solid (not transparent)
$solidHeader = true;
?>

<!-- ── HERO ─────────────────────────────────────── -->
<div class="about-hero">
  <div class="hero-ghost">ABOUT US</div>
  <div class="hero-inner">
    <div class="label-tag">Who We Are</div>
    <h1 class="hero-h1">About <span class="red">Us</span></h1>
    <p class="hero-sub">
     <br>
    </p>
  </div>
</div>

<!-- ── INTRO ─────────────────────────────────────── -->
<section class="about-intro">
  <div class="wrap">
    <div class="intro-left">
      <div class="section-tag">Our Story</div>
      <h2>Your Trusted <span class="red">Car Rental</span> Partner</h2>
      <p>We provide high-quality vehicles for every type of journey—whether it's a quick city trip, a family vacation, or a business ride.</p>
      <p>Our fleet includes Sedans, SUVs, Vans, and Pickups. We ensure comfort, reliability, and affordability in every ride.</p>
    </div>
    <div class="stats-grid">
      <div class="stat-box"><div class="stat-num">500+</div><div class="stat-label">Happy Clients</div></div>
      <div class="stat-box"><div class="stat-num">50+</div><div class="stat-label">Fleet Vehicles</div></div>
      <div class="stat-box"><div class="stat-num">5★</div><div class="stat-label">Average Rating</div></div>
      <div class="stat-box"><div class="stat-num">24/7</div><div class="stat-label">Support</div></div>
    </div>
  </div>
</section>

<!-- ── FEATURES ───────────────────────────────────── -->
<section class="features-section">
  <div class="wrap">
    <div class="section-head">
      <h2>What Sets Us Apart</h2>
      <p>Three pillars that define every rental experience we deliver.</p>
    </div>
    <div class="features-grid">
      <div class="feat-card">
        <div class="feat-num">01</div>
        <div class="feat-icon">🚗</div>
        <h3>Wide Selection</h3>
        <p>From compact sedans to spacious SUVs and utility pickups—many vehicles to choose from for any need.</p>
      </div>
      <div class="feat-card">
        <div class="feat-num">02</div>
        <div class="feat-icon">💰</div>
        <h3>Affordable Pricing</h3>
        <p>Best prices guaranteed. Transparent rates with no hidden fees so you always know what you're paying.</p>
      </div>
      <div class="feat-card">
        <div class="feat-num">03</div>
        <div class="feat-icon">⭐</div>
        <h3>Reliable & Smooth</h3>
        <p>Easy online booking, well-maintained vehicles, and friendly support for a stress-free experience.</p>
      </div>
    </div>
  </div>
</section>

<!-- ── LOCATION ───────────────────────────────────── -->
<section class="location-section">
  <div class="wrap">
    <div class="loc-left">
      <div class="loc-tag">Find Us</div>
      <h2>Our Location</h2>
      <p>Visit our rental office to browse our fleet in person, talk to our team, and drive away with confidence.</p>
      <div class="loc-items">
        <div class="loc-item">
          <div class="loc-icon">📍</div>
          <div class="loc-info"><strong>Address</strong><span>Bacolod City, Central Visayas, Philippines</span></div>
        </div>
        <div class="loc-item">
          <div class="loc-icon">🕐</div>
          <div class="loc-info"><strong>Operating Hours</strong><span>Mon – Sat: 8:00 AM – 7:00 PM</span></div>
        </div>
        <div class="loc-item">
          <div class="loc-icon">📞</div>
          <div class="loc-info"><strong>Phone / WhatsApp</strong><span>+63 912 345 6789</span></div>
        </div>
      </div>
    </div>
    <div class="loc-map">
      <iframe
        src="https://maps.google.com/maps?q=Bacolod%20City,%20Philippines&t=&z=13&ie=UTF8&iwloc=&output=embed"
        allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade">
      </iframe>
      <div class="map-badge"><div class="map-dot"></div>Bacolod City, Philippines</div>
    </div>
  </div>
</section>

<!-- ── CTA ────────────────────────────────────────── -->
<section class="cta-strip">
  <div class="cta-inner">
    <div class="cta-label">Book a Ride</div>
    <h2>Ready To Hit<br>The Road?</h2>
    <p>Browse our fleet and make a reservation in minutes.<br>Affordable, reliable, and ready when you are.</p>
    <a href="<?= BASE_URL ?>?page=browse" class="btn btn-red btn-lg">View Our Fleet →</a>
  </div>
</section>