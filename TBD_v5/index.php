<?php
  $title='The Big Draw — Volleyball for Good'; $active='home';

  /* Sponsors featured on the home page. Add one entry per sponsor as they sign on:
   *   'name'  — shown as the logo's alt text
   *   'logo'  — image path, e.g. 'assets/sponsors/acme.png'
   *   'url'   — optional; wraps the logo in a link to the sponsor's site
   *   'level' — 'presenting' | 'court' | 'team' | 'inkind'
   *             controls ORDER (top to bottom) and logo SIZE (presenting largest).
   * While empty, the section shows an invitation + "Become a sponsor" CTA. */
  $sponsors = [
    // ['name'=>'Acme Co.', 'logo'=>'assets/sponsors/acme.png', 'url'=>'https://acme.example', 'level'=>'presenting'],
  ];

  // Level display order (top→bottom) + section label. Logo size per level is in css/site.css (.lvl-*).
  $sponsor_levels = [
    'presenting' => 'Presenting Sponsor',
    'court'      => 'Court Sponsors',
    'team'       => 'Team Sponsors',
    'inkind'     => 'In-Kind Sponsors',
  ];

  include 'includes/header.php';
?>

<!-- home = top of the poster (hero + mission), header chopped off and replaced by the shared nav -->
<div class="slice home">
  <img src="assets/poster.png?v=4" alt="The Big Draw — Blind Draw beach volleyball tournament, November 7th, 2026 at Aussies Grill & Beach Bar. Volleyball for Good, benefiting Big Brothers Big Sisters.">
  <!-- Register Now button hotspot -->
  <a class="hot" data-label="Register Now" href="<?= $register_url ?>"
     style="left:5.3%;top:72%;width:33%;height:8.5%"></a>
</div>

<!-- sponsors — shown after the poster, before the footer tagline; grouped & sized by level -->
<section class="block sponsors-home"><div class="col">
  <h2 class="kicker">Our Sponsors</h2>
  <?php if ($sponsors): ?>
    <?php foreach ($sponsor_levels as $key => $label):
            $group = [];
            foreach ($sponsors as $s) { if (($s['level'] ?? 'team') === $key) $group[] = $s; }
            if (!$group) continue; ?>
    <div class="sponsor-tier lvl-<?= htmlspecialchars($key) ?>">
      <h3 class="sponsor-tier-label"><?= htmlspecialchars($label) ?></h3>
      <div class="sponsor-wall">
        <?php foreach ($group as $s):
                $img = '<img src="'.htmlspecialchars($s['logo']).'" alt="'.htmlspecialchars($s['name']).'" loading="lazy">'; ?>
        <div class="sponsor-logo"><?php if (!empty($s['url'])): ?><a href="<?= htmlspecialchars($s['url']) ?>" target="_blank" rel="noopener"><?= $img ?></a><?php else: ?><?= $img ?><?php endif; ?></div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="sponsors-past">
      <img src="assets/sponsors/sponsors-2025.png" alt="The Big Draw 2025 sponsors — Court, Gold, Team, and Support sponsors" loading="lazy">
    </div>
    <p class="muted">A huge thank-you to our 2025 sponsors. Want your brand featured here in 2026?</p>
  <?php endif; ?>
  <div class="cta-row">
    <a href="sponsor.php" class="btn grad">Become a sponsor →</a>
  </div>
</div></section>

<?php include 'includes/footer.php'; ?>
