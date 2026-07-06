<?php
  $title='In-Kind Sponsorship — The Big Draw'; $active='sponsor';
  // In-kind offers are emailed to the sponsorship contact.
  $inkind_email = 'questions@tbdvolleyball.com';
  include 'includes/header.php';
?>

<section class="pagehead"><div class="col">
  <div class="eyebrow">Sponsor · In-Kind</div>
  <h1>Offer an In-Kind Donation</h1>
  <p>Support The Big Draw by donating items or services that improve the player experience or help us celebrate the winning teams. Tell us what you have in mind and we&rsquo;ll follow up.</p>
</div></section>

<section class="block"><div class="col">
  <div class="contact-grid">
    <div class="cinfo">
      <h2 class="kicker">In-kind opportunities</h2>
      <p class="muted">Swag for player bags, prizes for top-finishing teams, food, drinks, services, or event-day supplies — all of it helps. In-kind donations valued at $500 or more receive event-day verbal recognition, and logo recognition on swag or prize-table signage when applicable.</p>
      <p class="muted">Questions first? Email <a href="mailto:<?= htmlspecialchars($inkind_email) ?>"><?= htmlspecialchars($inkind_email) ?></a>.</p>
    </div>

    <form class="lead-form" id="inkindForm" novalidate>
      <div class="field">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" placeholder="Your name" required>
      </div>
      <div class="field">
        <label for="business">Business / organization <span class="opt">(optional)</span></label>
        <input type="text" id="business" name="business" placeholder="Company or group name">
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
        <legend>What would you like to donate?</legend>
        <label class="check"><input type="checkbox" name="items" value="Swag for player bags"><span>Swag for player bags</span></label>
        <label class="check"><input type="checkbox" name="items" value="Prizes for top teams"><span>Prizes for top teams</span></label>
        <label class="check"><input type="checkbox" name="items" value="Food / drinks / services"><span>Food, drinks, or services</span></label>
        <label class="check"><input type="checkbox" name="items" value="Other"><span>Other</span></label>
      </fieldset>

      <div class="field">
        <label for="notes">Tell us more <span class="opt">(what you&rsquo;d like to donate, quantity, value…)</span></label>
        <textarea id="notes" name="notes" rows="3" placeholder="Describe your in-kind donation"></textarea>
      </div>

      <button type="submit" class="btn grad">Send your offer →</button>
      <div class="form-success" id="inkindSuccess" hidden>Thanks! Your email app should open with everything filled in — just hit send and we&rsquo;ll follow up.</div>
    </form>
  </div>
</div></section>

<script>
  (function(){
    var form = document.getElementById('inkindForm');
    var success = document.getElementById('inkindSuccess');
    var to = <?= json_encode($inkind_email) ?>;
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (!form.checkValidity()) { form.reportValidity(); return; }
      var name  = form.name.value.trim();
      var items = Array.prototype.map.call(form.querySelectorAll('input[name="items"]:checked'), function(c){ return c.value; });

      var lines = [
        'New in-kind sponsorship offer for The Big Draw',
        '',
        'Name:     ' + name,
        'Business: ' + (form.business.value.trim() || '—'),
        'Email:    ' + form.email.value.trim(),
        'Phone:    ' + (form.phone.value.trim() || '—'),
        '',
        'Would like to donate:',
        (items.length ? items.map(function(r){ return '  • ' + r; }).join('\n') : '  • (not specified)'),
        '',
        'Details:',
        (form.notes.value.trim() || '  —')
      ];

      var subject = 'In-kind sponsorship — ' + (name || 'The Big Draw');
      var href = 'mailto:' + to +
                 '?subject=' + encodeURIComponent(subject) +
                 '&body='    + encodeURIComponent(lines.join('\n'));
      window.location.href = href;
      success.hidden = false;
    });
  })();
</script>

<?php include 'includes/footer.php'; ?>
