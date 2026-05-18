<?php ?>

<style>
  @import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@300;400;500;600&family=Barlow+Condensed:wght@500;600;700&display=swap');

  .contact-hero,
  .contact-section,
  .map-section,
  .faq-section { font-family: 'Barlow', sans-serif; }

  /* ────── HERO ────── */
  .contact-hero {
    position: relative; min-height: 360px;
    display: flex; align-items: flex-end; overflow: hidden;
    background: linear-gradient(to right, rgba(0,0,0,.82) 0%, rgba(0,0,0,.60) 100%),
      url('https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?q=80&w=1920&auto=format&fit=crop') center/cover no-repeat;
    margin-top: 72px;
  }
  .contact-hero::before {
    content: ''; position: absolute; inset: 0;
    background:
      radial-gradient(ellipse 55% 55% at 85% 30%, rgba(224,27,27,.10) 0%, transparent 60%),
      radial-gradient(ellipse 35% 45% at 15% 75%, rgba(224,27,27,.05) 0%, transparent 50%);
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
    padding: 56px 48px; max-width: 1240px; margin: 0 auto; width: 100%;
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

  .hero-sub { margin-top: 18px; font-size: 14px; color: var(--muted); }
  .hero-sub a { color: var(--muted); text-decoration: none; }
  .hero-sub a:hover { color: var(--red); }
  .hero-sub .sep { margin: 0 6px; color: var(--grey); }

  /* ────── CONTACT GRID ────── */
  .contact-section {
    background: var(--black2);
    border-top: 1px solid var(--grey);
    border-bottom: 1px solid var(--grey);
    padding: 88px 48px;
  }
  .contact-section .wrap {
    max-width: 1240px; margin: 0 auto;
    display: grid; grid-template-columns: 1fr 1.4fr; gap: 60px; align-items: start;
  }

  .section-tag {
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 11px; letter-spacing: 3.5px; text-transform: uppercase;
    color: var(--red); font-weight: 600; margin-bottom: 16px;
    display: flex; align-items: center; gap: 10px;
  }
  .section-tag::after { content: ''; height: 1px; width: 60px; background: var(--grey); }

  .info-panel h2 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: clamp(34px, 4vw, 48px); line-height: .95;
    letter-spacing: 1px; color: var(--white); margin-bottom: 10px;
  }
  .info-panel > p { color: var(--muted); font-size: 14px; margin-bottom: 32px; line-height: 1.7; }

  .info-items { display: flex; flex-direction: column; gap: 12px; margin-bottom: 28px; }
  .info-item {
    display: flex; align-items: flex-start; gap: 14px;
    padding: 15px 16px; background: var(--black3);
    border: 1px solid var(--grey); border-radius: 10px; transition: border-color .22s;
  }
  .info-item:hover { border-color: var(--red); }

  .info-icon {
    width: 36px; height: 36px; background: var(--redglow);
    border-radius: 8px; display: flex; align-items: center; justify-content: center;
    font-size: 16px; flex-shrink: 0;
  }
  .info-text strong { display: block; font-family: 'Barlow Condensed', sans-serif; font-size: 12px; font-weight: 700; color: var(--white); letter-spacing: .5px; margin-bottom: 2px; }
  .info-text span { font-size: 13px; color: var(--muted); }

  .hours-block {
    background: var(--black3); border: 1px solid var(--grey);
    border-radius: 10px; padding: 18px 18px 16px; margin-bottom: 24px;
  }
  .hours-title {
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 11px; letter-spacing: 2px; text-transform: uppercase;
    color: var(--red); font-weight: 600; margin-bottom: 14px;
  }
  .hours-row {
    display: flex; justify-content: space-between;
    font-size: 13px; padding: 6px 0;
    border-bottom: 1px solid var(--grey); color: var(--muted);
  }
  .hours-row:last-child { border-bottom: none; }
  .hours-row span:first-child { color: var(--white); }

  .social-row { display: flex; gap: 10px; }
  .social-btn {
    padding: 8px 18px; background: var(--black3);
    border: 1px solid var(--grey); border-radius: 6px;
    color: var(--muted); font-family: 'Barlow Condensed', sans-serif;
    font-size: 13px; font-weight: 600; text-decoration: none;
    transition: border-color .2s, color .2s;
  }
  .social-btn:hover { border-color: var(--red); color: var(--red); }

  /* ── Form panel ── */
  .form-panel {
    background: var(--black3); border: 1px solid var(--grey);
    border-radius: 16px; padding: 36px 32px;
    position: relative; overflow: hidden;
  }
  .form-panel::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px;
    background: linear-gradient(90deg, var(--red), transparent 70%);
  }
  .form-panel h2 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: clamp(28px, 3vw, 38px); letter-spacing: 1px;
    color: var(--white); margin-bottom: 6px; line-height: 1;
  }
  .form-panel > p { color: var(--muted); font-size: 14px; margin-bottom: 28px; }

  .c-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

  .c-field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px; }
  .c-field label {
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 11px; letter-spacing: 2px; text-transform: uppercase;
    color: var(--muted); font-weight: 600;
  }
  .c-field input,
  .c-field select,
  .c-field textarea {
    background: var(--black2); border: 1px solid var(--grey);
    border-radius: 8px; padding: 12px 14px;
    color: var(--white); font-family: 'Barlow', sans-serif;
    font-size: 14px; outline: none; transition: border-color .2s; width: 100%;
  }
  .c-field select option { background: var(--black2); }
  .c-field input:focus,
  .c-field select:focus,
  .c-field textarea:focus { border-color: var(--red); box-shadow: 0 0 0 3px var(--redglow); }
  .c-field textarea { resize: vertical; min-height: 110px; }

  .success-msg {
    margin-top: 14px; padding: 12px 16px;
    background: rgba(46,160,67,.1); border: 1px solid rgba(46,160,67,.25);
    border-radius: 8px; color: #6fcf97; font-size: 14px;
  }

  /* ────── MAP ────── */
  .map-section { padding: 0 48px 88px; background: var(--black2); }
  .map-section .wrap { max-width: 1240px; margin: 0 auto; }
  .map-wrap {
    border-radius: 16px; overflow: hidden;
    border: 1px solid var(--grey); position: relative;
    box-shadow: 0 24px 60px rgba(0,0,0,.5);
  }
  .map-wrap iframe { display: block; width: 100%; height: 340px; border: 0; filter: grayscale(20%) contrast(1.05) brightness(.9); }
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
    0%,100% { box-shadow: 0 0 0 0 rgba(224,27,27,.55); }
    60%      { box-shadow: 0 0 0 7px rgba(224,27,27,0); }
  }

  /* ────── FAQ ────── */
  .faq-section {
    padding: 88px 48px; background: var(--black);
    border-top: 1px solid var(--grey);
  }
  .faq-section .wrap { max-width: 1240px; margin: 0 auto; }
  .faq-head { margin-bottom: 44px; }
  .faq-head h2 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: clamp(34px, 4vw, 50px); letter-spacing: 1px; color: var(--white); line-height: 1;
  }
  .faq-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

  .faq-card {
    background: var(--black2); border: 1px solid var(--grey);
    border-radius: 12px; padding: 24px 22px;
    position: relative; overflow: hidden; transition: border-color .22s;
  }
  .faq-card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px;
    background: var(--red); transform: scaleX(0); transform-origin: left; transition: transform .3s;
  }
  .faq-card:hover { border-color: var(--red); }
  .faq-card:hover::before { transform: scaleX(1); }

  .faq-q {
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 15px; font-weight: 700; color: var(--white); margin-bottom: 10px;
    display: flex; align-items: flex-start; gap: 10px; letter-spacing: .04em;
  }
  .faq-q::before { content: 'Q'; font-family: 'Bebas Neue', sans-serif; font-size: 18px; color: var(--red); flex-shrink: 0; line-height: 1.2; }
  .faq-a { color: var(--muted); font-size: 14px; line-height: 1.75; padding-left: 24px; }

  /* ────── RESPONSIVE ────── */
  @media (max-width: 900px) {
    .contact-section .wrap { grid-template-columns: 1fr; gap: 44px; }
    .faq-grid { grid-template-columns: 1fr; }
    .c-form-row { grid-template-columns: 1fr; }
    .contact-section, .faq-section { padding: 60px 24px; }
    .map-section { padding: 0 24px 60px; }
    .hero-inner { padding: 48px 24px; }
    .map-wrap iframe { height: 260px; }
  }
</style>

<?php $solidHeader = true; ?>

<!-- ── HERO ─────────────────────────────────────── -->
<div class="contact-hero">
  <div class="hero-ghost">CONTACT</div>
  <div class="hero-inner">
    <div class="label-tag">Get In Touch</div>
    <h1 class="hero-h1">Contact <span class="red">Us</span></h1>
    <p class="hero-sub">
      <a href="<?= BASE_URL ?>?page=home">Home</a>
      <span class="sep">/</span>
      Contact Us
    </p>
  </div>
</div>

<!-- ── CONTACT + FORM ────────────────────────────── -->
<section class="contact-section">
  <div class="wrap">

    <div class="info-panel">
      <div class="section-tag">Reach Us</div>
      <h2>Contact Information</h2>
      <p>We'd love to hear from you. Reach out and we'll get back to you as soon as possible.</p>

      <div class="info-items">
        <div class="info-item">
          <div class="info-icon">📍</div>
          <div class="info-text"><strong>Address</strong><span>Bacolod City, Philippines</span></div>
        </div>
        <div class="info-item">
          <div class="info-icon">📞</div>
          <div class="info-text"><strong>Phone</strong><span>+63 945 756 6561</span></div>
        </div>
        <div class="info-item">
          <div class="info-icon">✉️</div>
          <div class="info-text"><strong>Email</strong><span>cargo@carrental.com</span></div>
        </div>
      </div>

      <div class="hours-block">
        <div class="hours-title">Business Hours</div>
        <div class="hours-row"><span>Mon – Fri</span><span>8:00 AM – 6:00 PM</span></div>
        <div class="hours-row"><span>Saturday</span><span>9:00 AM – 5:00 PM</span></div>
        <div class="hours-row"><span>Sunday</span><span>Closed</span></div>
      </div>

      <div class="social-row">
        <a href="#" class="social-btn">Facebook</a>
        <a href="#" class="social-btn">Instagram</a>
        <a href="#" class="social-btn">Twitter</a>
      </div>
    </div>

    <div class="form-panel">
      <h2>Send A Message</h2>
      <p>Fill out the form and we'll reply within 24 hours.</p>

      <form method="POST">
        <div class="c-form-row">
          <div class="c-field">
            <label>Your Name</label>
            <input type="text" name="name" placeholder="Juan Dela Cruz" required>
          </div>
          <div class="c-field">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="you@example.com" required>
          </div>
        </div>
        <div class="c-field">
          <label>Subject</label>
          <select name="subject">
            <option value="">Select a subject...</option>
            <option>Booking Inquiry</option>
            <option>Support</option>
            <option>Feedback</option>
          </select>
        </div>
        <div class="c-field">
          <label>Message</label>
          <textarea name="message" placeholder="Tell us how we can help..." required></textarea>
        </div>
        <button type="submit" class="btn btn-red btn-full" style="border-radius:6px;">Send Message →</button>
      </form>

      <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <div class="success-msg">✓ Message sent! We'll get back to you shortly.</div>
      <?php endif; ?>
    </div>

  </div>
</section>


<section class="map-section">
  <div class="wrap">
    <div class="map-wrap">
      <iframe
        src="https://maps.google.com/maps?q=Bacolod%20City,%20Philippines&t=&z=13&ie=UTF8&iwloc=&output=embed"
        allowfullscreen loading="lazy">
      </iframe>
      <div class="map-badge"><div class="map-dot"></div>Bacolod City, Philippines</div>
    </div>
  </div>
</section>

<section class="faq-section">
  <div class="wrap">
    <div class="faq-head">
      <div class="section-tag">FAQ</div>
      <h2>Frequently Asked Questions</h2>
    </div>
    <div class="faq-grid">
      <div class="faq-card">
        <div class="faq-q">How do I book a car?</div>
        <div class="faq-a">You can book directly through our website by selecting your preferred vehicle and schedule.</div>
      </div>
      <div class="faq-card">
        <div class="faq-q">What documents are required?</div>
        <div class="faq-a">A valid driver's license and one government-issued ID are required for all rentals.</div>
      </div>
      <div class="faq-card">
        <div class="faq-q">Do you offer airport pickup?</div>
        <div class="faq-a">Yes! We offer convenient pickup and drop-off services within Cebu at no extra charge.</div>
      </div>
      <div class="faq-card">
        <div class="faq-q">Can I cancel my booking?</div>
        <div class="faq-a">Cancellations made 24 hours before the rental start time are eligible for a full refund.</div>
      </div>
    </div>
  </div>
</section>