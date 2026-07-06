<?php
  $title='Become a Sponsor — The Big Draw'; $active='sponsor';

  // Shared package list; the summary below shows the paid levels.
  require 'includes/tiers.php';
  $pay_tiers = array_filter($tiers, function ($t) { return $t['kind'] === 'pay'; });

  include 'includes/header.php';
?>

<section class="pagehead"><div class="col">
  <div class="eyebrow">Sponsor · Volleyball for Good</div>
  <h1>Become a Sponsor</h1>
  <p>Pick your level below, then enter that amount in the secure donation form. Every gift supports Big Brothers Big Sisters of Central Texas and the mentoring mission behind The Big Draw.</p>
</div></section>

<section class="block"><div class="col">
  <h2 class="kicker">Choose your level</h2>
  <ul class="sponsor-summary">
    <?php foreach ($pay_tiers as $t): ?>
    <li>
      <span class="ss-name"><?= htmlspecialchars($t['name']) ?></span>
      <span class="ss-avail"><?= htmlspecialchars($t['avail']) ?></span>
      <span class="ss-price"><?= htmlspecialchars($t['price']) ?></span>
    </li>
    <?php endforeach; ?>
  </ul>
  <p class="summary-links">
    <a href="sponsor.php">See full sponsorship details &rarr;</a>
  </p>

  <p class="bb-instruct">Enter the amount for your chosen level as your gift below.</p>
  <div class="bb-frame">
    <iframe title="The Big Draw donation form"
            src="https://host.nxt.blackbaud.com/donor-form?svcid=renxt&amp;formId=edce5290-bfdc-4ab4-8ac5-d089a7d1bfa5&amp;envid=p--K_lwkjaikKw84Z7Hr2ixg&amp;zone=usa"
            loading="lazy" frameborder="0"></iframe>
  </div>

  <p class="bb-inkind">Donating goods or services instead? <a href="sponsor-in-kind.php">Offer an in-kind donation &rarr;</a></p>
</div></section>

<?php include 'includes/footer.php'; ?>
