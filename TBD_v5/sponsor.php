<?php
  $title='Sponsor — The Big Draw'; $active='sponsor';

  // Sponsorship packages live in one place; the checkout page reads the same list.
  require 'includes/tiers.php';

  include 'includes/header.php';
?>

<section class="pagehead"><div class="col">
  <div class="eyebrow">Sponsor · Volleyball for Good</div>
  <h1>Sponsor The Big Draw</h1>
  <p>Put your brand in front of Austin&rsquo;s beach-volleyball community while helping Big Brothers Big Sisters of Central Texas create life-changing mentoring relationships for local kids. Every package supports the mission behind The Big Draw — choose the level that fits and be part of a day built around fun, community, and real impact.</p>
</div></section>

<section class="block"><div class="col">
  <div class="tiers">
    <?php foreach ($tiers as $t):
      $tier_href = $t['kind']==='inkind' ? 'sponsor-in-kind.php' : 'become-a-sponsor.php';
      $tier_go   = $t['kind']==='inkind' ? 'Offer an in-kind donation' : 'Choose this level';
    ?>
    <article class="tier">
      <div class="tier-top">
        <span class="tier-n"><?= (int)$t['n'] ?></span>
        <div class="tier-id">
          <h3><?= htmlspecialchars($t['name']) ?></h3>
          <?php if ($t['price'] !== ''): ?><div class="tier-price"><?= htmlspecialchars($t['price']) ?></div><?php endif; ?>
        </div>
        <span class="pillstate <?= $t['state']==='closed' ? 'is-closed' : 'is-open' ?>"><?= htmlspecialchars($t['avail']) ?></span>
      </div>
      <?php if (!empty($t['desc'])): foreach ((array)$t['desc'] as $d): ?>
      <p class="tier-desc"><?= htmlspecialchars($d) ?></p>
      <?php endforeach; endif; ?>
      <?php if (!empty($t['note'])): ?><p class="tier-note"><?= htmlspecialchars($t['note']) ?></p><?php endif; ?>
      <ul class="perks">
        <?php foreach ($t['perks'] as $p): ?><li><?= htmlspecialchars($p) ?></li><?php endforeach; ?>
      </ul>
      <a class="tier-go" href="<?= $tier_href ?>"><?= $tier_go ?> &rarr;</a>
    </article>
    <?php endforeach; ?>
  </div>
</div></section>

<section class="block" style="padding-top:0"><div class="col">
  <div class="sponsor-close">
    <h2>Become a Sponsor</h2>
    <p class="close-tag">The Draw is <span class="t">Random.</span> The Impact is <span class="t">Real.</span></p>
    <p class="close-body">Every sponsorship supports Big Brothers Big Sisters of Central Texas and helps create a better tournament experience for players, mentors, Littles, and the community.</p>
    <p class="close-q">Questions? <a href="mailto:questions@tbdvolleyball.com">questions@tbdvolleyball.com</a></p>
    <div class="cta-row">
      <a href="become-a-sponsor.php" class="btn grad">Become a sponsor &rarr;</a>
      <a href="tournament.php" class="btn line">How it works</a>
    </div>
  </div>
</div></section>

<?php include 'includes/footer.php'; ?>
