<?php
  $title='Get Involved — The Big Draw'; $active='involve';
  // TODO: set the real tournament director address — all volunteer signups are emailed here
  $director_email = 'director@tbdvolleyball.com';
  include 'includes/header.php';
?>

<section class="pagehead"><div class="col">
  <div class="eyebrow">Get Involved</div>
  <h1>Volunteer With Us</h1>
  <p>The Big Draw runs on volunteers. Tell us how you'd like to help and we'll be in touch with the details.</p>
</div></section>

<section class="block"><div class="col">
  <div class="contact-grid">
    <div class="cinfo">
      <h2 class="kicker">Why volunteer</h2>
      <p class="muted">Every hour you give supports Big Brothers Big Sisters of Central Texas — and it's a blast on the sand. No experience needed; we'll match you to a role that fits.</p>
      <p class="muted">Questions first? Email <a href="mailto:<?= htmlspecialchars($director_email) ?>"><?= htmlspecialchars($director_email) ?></a>.</p>
    </div>

    <form class="lead-form" id="volForm" novalidate>
      <div class="field">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" placeholder="Your name" required>
      </div>
      <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="you@example.com" required>
      </div>
      <div class="field">
        <label for="phone">Phone <span class="opt">(optional)</span></label>
        <input type="tel" id="phone" name="phone" placeholder="(512) 555-0123">
      </div>

      <fieldset class="field checks">
        <legend>What would you like to help with?</legend>
        <label class="check"><input type="checkbox" name="roles" value="Pre-event work"><span>Pre-event work</span></label>
        <label class="check"><input type="checkbox" name="roles" value="Morning-of setup"><span>Morning-of setup</span></label>
        <label class="check"><input type="checkbox" name="roles" value="Run of show"><span>Help with run of show</span></label>
        <label class="check"><input type="checkbox" name="roles" value="Tear down"><span>Tear down</span></label>
        <label class="check"><input type="checkbox" name="roles" value="Wherever you need me"><span>Wherever you need me</span></label>
      </fieldset>

      <div class="field">
        <label for="notes">Anything else? <span class="opt">(availability, group size, t-shirt size…)</span></label>
        <textarea id="notes" name="notes" rows="3" placeholder="Tell us anything that helps us plan"></textarea>
      </div>

      <button type="submit" class="btn grad">Send to the Director →</button>
      <div class="form-success" id="volSuccess" hidden>Thanks! Your email app should open with everything filled in — just hit send and we'll follow up.</div>
    </form>
  </div>
</div></section>

<script>
  (function(){
    var form = document.getElementById('volForm');
    var success = document.getElementById('volSuccess');
    var director = <?= json_encode($director_email) ?>;
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (!form.checkValidity()) { form.reportValidity(); return; }
      var name  = form.name.value.trim();
      var roles = Array.prototype.map.call(form.querySelectorAll('input[name="roles"]:checked'), function(c){ return c.value; });

      var lines = [
        'New volunteer signup for The Big Draw',
        '',
        'Name:  ' + name,
        'Email: ' + form.email.value.trim(),
        'Phone: ' + (form.phone.value.trim() || '—'),
        '',
        'Would like to help with:',
        (roles.length ? roles.map(function(r){ return '  • ' + r; }).join('\n') : '  • (not specified)'),
        '',
        'Notes:',
        (form.notes.value.trim() || '  —')
      ];

      var subject = 'Volunteer signup — ' + (name || 'The Big Draw');
      var href = 'mailto:' + director +
                 '?subject=' + encodeURIComponent(subject) +
                 '&body='    + encodeURIComponent(lines.join('\n'));
      window.location.href = href;
      success.hidden = false;
    });
  })();
</script>

<?php include 'includes/footer.php'; ?>
