<?php
  $title='Sponsor — The Big Draw'; $active='sponsor';

  /* ------------------------------------------------------------------ *
   *  SPONSORSHIP PACKAGES — edit here. The page renders from this array.
   *  To change a price, perk, or availability, just edit the text below.
   *    'price' : leave '' for no fixed price (e.g. In-Kind)
   *    'state' : 'open'   -> orange availability pill
   *              'closed' -> teal pill (e.g. "Goal achieved")
   *    'note'  : optional line shown above the perks
   * ------------------------------------------------------------------ */
  $tiers = [
    [
      'n'=>1, 'name'=>'Presenting Sponsor', 'price'=>'$5,000',
      'avail'=>'1 spot available', 'state'=>'open',
      'perks'=>[
        'Logo and appreciation on the tournament web site (the landing page for all promotion and registration)',
        'Speaking opportunity during the tournament\'s "Minute for Mission"',
        'Logo on players\' arm bands (worn throughout the tournament)',
        'Social media recognition',
      ],
    ],
    [
      'n'=>2, 'name'=>'Court Sponsor', 'price'=>'$2,000',
      'avail'=>'2 spots available', 'state'=>'open',
      'perks'=>[
        'Highly visible logo and appreciation on a banner on the court',
        'Verbal appreciation at the event',
        'Logo on the tournament web site',
        'Social media recognition',
      ],
    ],
    [
      'n'=>3, 'name'=>'Team Sponsor', 'price'=>'$500',
      'avail'=>'12 spots available', 'state'=>'open',
      'perks'=>[
        'Logo on a team\'s arm bands (worn by 4 players throughout the tournament)',
        'Verbal appreciation at the event',
      ],
    ],
    [
      'n'=>4, 'name'=>'Match Sponsor', 'price'=>'$125',
      'avail'=>'Spots available', 'state'=>'open',
      'perks'=>[
        'Sponsors a Big Brothers Big Sisters match — a Big and their Little — to play in the tournament together',
        'Provides lunch for the Big and their Little',
      ],
    ],
    [
      'n'=>5, 'name'=>'In-Kind Sponsor', 'price'=>'',
      'avail'=>'Spots available', 'state'=>'open',
      'note'=>'Options include either or both:',
      'perks'=>[
        'Donate swag for all players',
        'Donate prizes for the players on the top two winning teams',
        'Verbal appreciation at the event for in-kind donations of at least $500 in value',
      ],
    ],
  ];

  $sponsor_mailto = 'mailto:questions@tbdvolleyball.com?subject='.rawurlencode('The Big Draw Sponsorship');

  include 'includes/header.php';
?>

<section class="pagehead"><div class="col">
  <div class="eyebrow">Sponsor · Volleyball for Good</div>
  <h1>Sponsorship Packages</h1>
  <p>Every sponsorship package helps Big Brothers Big Sisters of Central Texas create life-changing mentoring relationships for local kids while putting your brand in front of an engaged community event. Pick the level that fits.</p>
</div></section>

<section class="block"><div class="col">
  <div class="tiers">
    <?php foreach ($tiers as $t): ?>
    <article class="tier">
      <div class="tier-top">
        <span class="tier-n"><?= (int)$t['n'] ?></span>
        <div class="tier-id">
          <h3><?= htmlspecialchars($t['name']) ?></h3>
          <?php if ($t['price'] !== ''): ?><div class="tier-price"><?= htmlspecialchars($t['price']) ?></div><?php endif; ?>
        </div>
        <span class="pillstate <?= $t['state']==='closed' ? 'is-closed' : 'is-open' ?>"><?= htmlspecialchars($t['avail']) ?></span>
      </div>
      <?php if (!empty($t['note'])): ?><p class="tier-note"><?= htmlspecialchars($t['note']) ?></p><?php endif; ?>
      <ul class="perks">
        <?php foreach ($t['perks'] as $p): ?><li><?= htmlspecialchars($p) ?></li><?php endforeach; ?>
      </ul>
    </article>
    <?php endforeach; ?>
  </div>

  <div class="cta-row">
    <a href="<?= $sponsor_mailto ?>" class="btn grad">Become a sponsor →</a>
    <a href="tournament.php" class="btn line">How it works</a>
  </div>
</div></section>

<?php include 'includes/footer.php'; ?>
