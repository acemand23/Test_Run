<?php
  $title='Register — The Big Draw'; $active='register';
  $notify = 'mailto:questions@tbdvolleyball.com?subject='.rawurlencode('Notify me when The Big Draw registration opens');
  include 'includes/header.php';
?>

<section class="pagehead signage"><div class="col">
  <div class="eyebrow">Register · Volleyball for Good</div>
  <h1>Registration</h1>
  <p>Team sign-ups for The Big Draw — November 1st, 2026 at Aussie&rsquo;s Grill &amp; Beach Bar, Austin.</p>
</div></section>

<section class="block"><div class="col">
  <div class="soon">
    <div class="soon-badge">Coming Soon</div>
    <h2>Registration is opening September 2026</h2>
    <p>We&rsquo;re putting the finishing touches on sign-ups. Check back shortly &mdash; or drop us a note and we&rsquo;ll let you know the moment it goes live.</p>
    <div class="cta-row">
      <a href="<?= $notify ?>" class="btn grad">Notify me &rarr;</a>
      <a href="tournament.php" class="btn line">How it works</a>
    </div>
  </div>
</div></section>

<?php include 'includes/footer.php'; ?>
