<?php ?>

 <link rel="stylesheet" href="<?= ASSET_URL ?>assets/css/contact.css">

<?php $solidHeader = true; ?>

<!-- ── HERO ─────────────────────────────────────── -->
<div class="contact-hero">
  <div class="hero-ghost">CONTACT</div>
  <div class="hero-inner">
    <div class="label-tag">Get In Touch</div>
    <h1 class="hero-h1">Contact <span class="red">Us</span></h1>
    <p class="hero-sub">
     <br>
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