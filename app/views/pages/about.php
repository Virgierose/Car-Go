<?php ?>

<style>
  
  @import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@300;400;500;600&family=Barlow+Condensed:wght@500;600;700&display=swap');

  
  .about-hero,
  .about-intro,
  .features-section,
  .location-section,
  .cta-strip { font-family: 'Barlow', sans-serif; }

 
  .about-hero {
    position: relative;
    min-height: 360px;
    display: flex;
    align-items: flex-end;
    overflow: hidden;
    background: linear-gradient(to right, rgba(0,0,0,.82) 0%, rgba(0,0,0,.60) 100%),
      url('assets/img/aboutB.png') center/cover no-repeat;
    margin-top: 72px; /* offset fixed header */
  }

  .about-hero::before {
    content: '';
    position: absolute; inset: 0;
    background:
      radial-gradient(ellipse 55% 55% at 85% 30%, rgba(192,57,43,.10) 0%, transparent 60%),
      radial-gradient(ellipse 35% 45% at 15% 75%, rgba(192,57,43,.05) 0%, transparent 50%);
    pointer-events: none;
  }

  .hero-ghost {
    position: absolute; bottom: -10px; left: 0;
    font-family: 'Bebas Neue', sans-serif;
    font-size: clamp(90px, 16vw, 220px);
    color: rgba(255,255,255,.025);
    letter-spacing: 6px; line-height: 1;
    user-select: none; pointer-events: none; white-space: nowrap;
  }

  .hero-inner {
    position: relative; z-index: 2;
    padding: 56px 48px;
    max-width: 1240px; margin: 0 auto; width: 100%;
  }

  .label-tag {
    display: inline-flex; align-items: center; gap: 10px;
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 11px; letter-spacing: 3.5px; text-transform: uppercase;
    color: var(--red); margin-bottom: 14px; font-weight: 600;
  }
  .label-tag::before { content: ''; width: 28px; height: 2px; background: var(--red); border-radius: 2px; }

  .hero-h1 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: clamp(56px, 9vw, 108px);
    line-height: .92; letter-spacing: 2px; color: var(--white);
  }
  .hero-h1 .red { color: var(--red); }

  .hero-sub { margin-top: 18px; font-size: 14px; color: var(--muted); font-weight: 300; }
  .hero-sub a { color: var(--muted); text-decoration: none; }
  .hero-sub a:hover { color: var(--red); }
  .hero-sub .sep { margin: 0 6px; color: var(--grey); }

 
  .about-intro {
    background: var(--black2);
    border-top: 1px solid var(--grey);
    border-bottom: 1px solid var(--grey);
    padding: 88px 48px;
  }
  .about-intro .wrap {
    max-width: 1240px; margin: 0 auto;
    display: grid; grid-template-columns: 1fr 1fr; gap: 72px; align-items: center;
  }

  .section-tag {
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 11px; letter-spacing: 3.5px; text-transform: uppercase;
    color: var(--red); font-weight: 600; margin-bottom: 18px;
    display: flex; align-items: center; gap: 10px;
  }
  .section-tag::after { content: ''; height: 1px; width: 60px; background: var(--grey); }

  .intro-left h2 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: clamp(38px, 4.5vw, 56px);
    line-height: .95; letter-spacing: 1px; margin-bottom: 22px; color: var(--white);
  }
  .intro-left h2 .red { color: var(--red); }
  .intro-left p { color: var(--muted); line-height: 1.85; font-size: 15px; margin-bottom: 14px; }

  .stats-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

  .stat-box {
    background: var(--black3); border: 1px solid var(--grey);
    border-radius: 12px; padding: 26px 22px;
    position: relative; overflow: hidden;
    transition: border-color .22s, transform .22s; cursor: default;
  }
  .stat-box::after {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px;
    background: linear-gradient(90deg, var(--red), transparent);
    transform: scaleX(0); transform-origin: left; transition: transform .3s;
  }
  .stat-box:hover { border-color: var(--red); transform: translateY(-3px); }
  .stat-box:hover::after { transform: scaleX(1); }

  .stat-num { font-family: 'Bebas Neue', sans-serif; font-size: 44px; color: var(--red); line-height: 1; }
  .stat-label {
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 11px; color: var(--muted); letter-spacing: 2px;
    text-transform: uppercase; margin-top: 6px; font-weight: 600;
  }

  
  .features-section { padding: 88px 48px; background: var(--black); }
  .features-section .wrap { max-width: 1240px; margin: 0 auto; }

  .section-head {
    display: flex; align-items: flex-end; justify-content: space-between;
    margin-bottom: 44px; gap: 24px; flex-wrap: wrap;
  }
  .section-head h2 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: clamp(34px, 4vw, 50px); letter-spacing: 1px; color: var(--white); line-height: 1;
  }
  .section-head p { color: var(--muted); font-size: 14px; max-width: 280px; text-align: right; line-height: 1.65; }

  .features-grid {
    display: grid; grid-template-columns: repeat(3, 1fr);
    gap: 1px; background: var(--grey);
    border: 1px solid var(--grey); border-radius: 16px; overflow: hidden;
  }

  .feat-card {
    background: var(--black2); padding: 40px 32px 36px;
    position: relative; transition: background .22s;
  }
  .feat-card:hover { background: var(--black3); }
  .feat-card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px;
    background: var(--red); transform: scaleX(0); transform-origin: left; transition: transform .3s;
  }
  .feat-card:hover::before { transform: scaleX(1); }

  .feat-num {
    position: absolute; top: 18px; right: 22px;
    font-family: 'Bebas Neue', sans-serif; font-size: 52px;
    color: rgba(255,255,255,.04); line-height: 1; transition: color .22s;
  }
  .feat-card:hover .feat-num { color: rgba(224,27,27,.08); }

  .feat-icon {
    width: 52px; height: 52px;
    background: var(--redglow); border: 1px solid rgba(224,27,27,.25);
    border-radius: 14px; display: flex; align-items: center; justify-content: center;
    font-size: 22px; margin-bottom: 22px; transition: background .22s, border-color .22s;
  }
  .feat-card:hover .feat-icon { background: rgba(224,27,27,.18); border-color: var(--red); }

  .feat-card h3 { font-family: 'Barlow Condensed', sans-serif; font-size: 17px; font-weight: 700; color: var(--white); margin-bottom: 10px; letter-spacing: .04em; }
  .feat-card p { color: var(--muted); font-size: 14px; line-height: 1.75; }

  /* ────────────────── LOCATION ────────────────── */
  .location-section {
    padding: 88px 48px; background: var(--black2);
    border-top: 1px solid var(--grey);
  }
  .location-section .wrap {
    max-width: 1240px; margin: 0 auto;
    display: grid; grid-template-columns: 1fr 1.45fr; gap: 60px; align-items: center;
  }

  .loc-tag {
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 11px; letter-spacing: 3.5px; text-transform: uppercase;
    color: var(--red); font-weight: 600; margin-bottom: 16px;
    display: flex; align-items: center; gap: 10px;
  }
  .loc-tag::before { content: ''; width: 28px; height: 2px; background: var(--red); border-radius: 2px; }

  .loc-left h2 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: clamp(38px, 4.5vw, 54px); color: var(--white);
    line-height: .95; margin-bottom: 18px; letter-spacing: 1px;
  }
  .loc-left > p { color: var(--muted); font-size: 15px; line-height: 1.85; margin-bottom: 34px; }

  .loc-items { display: flex; flex-direction: column; gap: 12px; }
  .loc-item {
    display: flex; align-items: flex-start; gap: 14px;
    padding: 15px 16px; background: var(--black3);
    border: 1px solid var(--grey); border-radius: 10px; transition: border-color .22s;
  }
  .loc-item:hover { border-color: var(--red); }

  .loc-icon {
    width: 36px; height: 36px; background: var(--redglow); border-radius: 8px;
    display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0;
  }
  .loc-info strong { display: block; font-family: 'Barlow Condensed', sans-serif; font-size: 12px; font-weight: 700; color: var(--white); margin-bottom: 2px; letter-spacing: .5px; }
  .loc-info span { font-size: 13px; color: var(--muted); }

  .loc-map { border-radius: 16px; overflow: hidden; border: 1px solid var(--grey); position: relative; box-shadow: 0 28px 64px rgba(0,0,0,.6); }
  .loc-map iframe { display: block; width: 100%; height: 430px; border: 0; filter: grayscale(20%) contrast(1.05) brightness(.9); }

  .map-badge {
    position: absolute; bottom: 14px; left: 14px;
    background: rgba(10,10,10,.92); border: 1px solid rgba(224,27,27,.35);
    border-radius: 8px; padding: 10px 14px;
    display: flex; align-items: center; gap: 9px;
    font-family: 'Barlow Condensed', sans-serif; font-size: 13px; font-weight: 700;
    color: var(--white); backdrop-filter: blur(10px);
  }
  .map-dot {
    width: 9px; height: 9px; background: var(--red);
    border-radius: 50%; flex-shrink: 0; animation: pulse-dot 2s infinite;
  }
  @keyframes pulse-dot {
    0%, 100% { box-shadow: 0 0 0 0 rgba(224,27,27,.55); }
    60%       { box-shadow: 0 0 0 7px rgba(224,27,27,0); }
  }

  /* ────────────────── CTA ────────────────── */
  .cta-strip {
    padding: 72px 48px; text-align: center; position: relative; overflow: hidden;
    background: linear-gradient(to right, rgba(0,0,0,.82) 0%, rgba(0,0,0,.60) 100%),
      url('https://images.unsplash.com/photo-1485291571150-772bcfc10da5?w=1600&q=80') center/cover no-repeat;
    border-top: 1px solid var(--grey);
  }
  .cta-strip::before {
    content: ''; position: absolute; inset: 0;
    background: radial-gradient(ellipse 70% 120% at 50% 50%, rgba(224,27,27,.07) 0%, transparent 70%);
    pointer-events: none;
  }

  .cta-inner { position: relative; z-index: 1; max-width: 660px; margin: 0 auto; }

  .cta-label {
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 11px; letter-spacing: 3.5px; text-transform: uppercase;
    color: var(--red); font-weight: 600; margin-bottom: 16px;
    display: flex; align-items: center; justify-content: center; gap: 10px;
  }
  .cta-label::before, .cta-label::after { content: ''; width: 28px; height: 1px; background: var(--red); opacity: .5; }

  .cta-strip h2 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: clamp(40px, 5.5vw, 68px);
    line-height: .92; letter-spacing: 2px; color: var(--white); margin-bottom: 18px;
  }
  .cta-strip p { color: var(--muted); font-size: 15px; margin-bottom: 34px; line-height: 1.75; }

  /* ────────────────── RESPONSIVE ────────────────── */
  @media (max-width: 900px) {
    .about-intro .wrap, .location-section .wrap { grid-template-columns: 1fr; gap: 44px; }
    .features-grid { grid-template-columns: 1fr; }
    .section-head p { text-align: left; max-width: 100%; }
    .loc-map iframe { height: 300px; }
    .about-intro, .features-section, .location-section, .cta-strip { padding: 60px 24px; }
    .hero-inner { padding: 48px 24px; }
  }
</style>

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
      <a href="<?= BASE_URL ?>?page=home">Home</a>
      <span class="sep">/</span>
      About Us
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


<section class="cta-strip">
  <div class="cta-inner">
    <div class="cta-label">Book a Ride</div>
    <h2>Ready To Hit<br>The Road?</h2>
    <p>Browse our fleet and make a reservation in minutes.<br>Affordable, reliable, and ready when you are.</p>
    <a href="<?= BASE_URL ?>?page=browse" class="btn btn-red btn-lg">View Our Fleet →</a>
  </div>
</section>