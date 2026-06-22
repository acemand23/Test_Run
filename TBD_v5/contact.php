<?php $title='Contact — The Big Draw'; $active='contact'; include 'includes/header.php'; ?>

<section class="pagehead"><div class="col">
  <div class="eyebrow">Questions?</div>
  <h1>Contact Us</h1>
  <p>Reach out about playing, sponsoring, or volunteering at The Big Draw. We'll get back to you.</p>
</div></section>

<section class="block"><div class="col">
  <div class="contact-grid">
    <div class="cinfo">
      <h2 class="kicker">Get in touch</h2>
      <p class="lead"><a href="mailto:questions@tbdvolleyball.com">questions@tbdvolleyball.com</a></p>
      <p class="muted">Aussie's Grill &amp; Beach Bar<br>306 Barton Springs Rd, Austin, TX 78704</p>
      <p class="muted">Benefiting Big Brothers Big Sisters of Central Texas.</p>
    </div>

    <form class="lead-form" id="leadForm" novalidate>
      <div class="field">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" placeholder="Your name" required>
      </div>
      <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="you@example.com" required>
      </div>
      <div class="field">
        <label for="topic">Topic</label>
        <select id="topic" name="topic">
          <option>Playing / registration</option>
          <option>Sponsorship</option>
          <option>Volunteering</option>
          <option>Something else</option>
        </select>
      </div>
      <div class="field">
        <label for="message">Message</label>
        <textarea id="message" name="message" rows="4" placeholder="How can we help?"></textarea>
      </div>
      <button type="submit" class="btn grad">Send Message →</button>
      <div class="form-success" id="formSuccess" hidden>Thanks! This is a sample form — connect it to your email/CRM to capture real messages.</div>
    </form>
  </div>
</div></section>

<script>
  (function(){
    var form = document.getElementById('leadForm');
    var success = document.getElementById('formSuccess');
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (!form.checkValidity()) { form.reportValidity(); return; }
      success.hidden = false;
      form.querySelector('button[type="submit"]').textContent = 'Message Sent';
    });
  })();
</script>

<?php include 'includes/footer.php'; ?>
