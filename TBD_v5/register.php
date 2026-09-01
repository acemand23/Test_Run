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
  <?php if ($reg_is_open): ?>
  <div class="soon">
    <div class="soon-badge">Now Open</div>
    <h2>Registration is open!</h2>
    <p>Grab your spot in The Big Draw. Sign-ups run through our tournament partner &mdash; it only takes a couple of minutes.</p>
    <div class="cta-row">
      <a href="<?= htmlspecialchars($reg_signup_url) ?>" target="_blank" rel="noopener" class="btn grad">Register now &rarr;</a>
      <a href="tournament.php" class="btn line">How it works</a>
    </div>
  </div>
  <?php else: ?>
  <div class="soon">
    <div class="soon-badge">Opening Soon</div>
    <h2>Registration opens October&nbsp;7, 2026</h2>
    <p>Sign-ups go live <strong>Saturday, October&nbsp;7 at 6:00&nbsp;PM CT</strong>. Drop us a note and we&rsquo;ll let you know the moment they open &mdash; or watch the countdown on our home page.</p>
    <div class="cta-row">
      <a href="<?= htmlspecialchars($reg_notify_url) ?>" class="btn grad">Notify me &rarr;</a>
      <a href="tournament.php" class="btn line">How it works</a>
    </div>
  </div>
  <?php endif; ?>
</div></section>

<?php include 'includes/footer.php'; ?>
