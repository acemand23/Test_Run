<?php
  $title='Register — The Big Draw'; $active='register';
  include 'includes/header.php';   // provides $reg_is_open, $reg_signup_url, $reg_notify_url
?>

<section class="pagehead"><div class="col">
  <div class="eyebrow">Register · Volleyball for Good</div>
  <h1>Registration</h1>
  <p>Team sign-ups for The Big Draw — November 7th, 2026 at Aussie&rsquo;s Grill &amp; Beach Bar, Austin.</p>
</div></section>

<section class="block"><div class="col">
  <div class="reg-divs">

    <!-- Community Division — open now -->
    <div class="soon">
      <div class="soon-badge"><?= $community_is_open ? 'Now Open' : 'Opening Soon' ?></div>
      <h2>Community Division</h2>
      <p><strong>Co-Ed · Rec · 4v4 · 8:30&nbsp;AM–Noon.</strong> Registration is open now &mdash; sign-ups run through our tournament partner and take just a couple of minutes.</p>
      <div class="cta-row">
        <a href="<?= htmlspecialchars($reg_signup_url) ?>" target="_blank" rel="noopener" class="btn grad">Register now &rarr;</a>
        <a href="tournament.php" class="btn line">How it works</a>
      </div>
    </div>

    <!-- Competitive Division — opens Oct 7 (auto-flips to open) -->
    <div class="soon">
      <div class="soon-badge"><?= $competitive_is_open ? 'Now Open' : 'Opening Soon' ?></div>
      <h2>Competitive Division</h2>
      <?php if ($competitive_is_open): ?>
      <p><strong>Co-Ed · All&nbsp;Levels · 4v4 · 11:30&nbsp;AM–8&nbsp;PM.</strong> Registration is open &mdash; grab your spot in The Big Draw.</p>
      <div class="cta-row">
        <a href="<?= htmlspecialchars($reg_signup_url) ?>" target="_blank" rel="noopener" class="btn grad">Register now &rarr;</a>
        <a href="tournament.php" class="btn line">How it works</a>
      </div>
      <?php else: ?>
      <p><strong>Co-Ed · All&nbsp;Levels · 4v4 · 11:30&nbsp;AM–8&nbsp;PM.</strong> Opens <strong>Wednesday, October&nbsp;7 at 6:00&nbsp;PM CT</strong>. Watch the countdown on our home page, or drop us a note and we&rsquo;ll let you know the moment it opens.</p>
      <div class="cta-row">
        <a href="<?= htmlspecialchars($reg_notify_url) ?>" class="btn grad">Notify me &rarr;</a>
        <a href="tournament.php" class="btn line">How it works</a>
      </div>
      <?php endif; ?>
    </div>

  </div>
</div></section>

<?php include 'includes/footer.php'; ?>
