<?php
  $title='The Big Draw — Volleyball for Good'; $active='home';

  /* Sponsors featured on the home page. Add one entry per sponsor as they sign on:
   *   'name'  — shown as the logo's alt text (and as a text placeholder until a logo is added)
   *   'logo'  — OPTIONAL image path, e.g. 'assets/sponsors/acme.png'. If omitted, the
   *             sponsor's NAME is shown in the logo slot as a placeholder.
   *   'url'   — optional; wraps the logo in a link to the sponsor's site
   *   'level' — 'presenting' | 'court' | 'team' | 'match' | 'inkind'
   *             controls ORDER (top to bottom) and logo SIZE (presenting largest;
   *             match + in-kind are the smallest / same size).
   * While empty, the section shows an invitation + "Become a sponsor" CTA. */
  $sponsors = [
    // 2026 sponsors. Logos live in assets/sponsors/. Peach Pollen logo is TBD, so
    // its NAME shows as a placeholder until a 'logo' path is added.
    ['name' => "O'Connell Robertson", 'logo' => 'assets/sponsors/oconnell-robertson.png', 'level' => 'court'],
    ['name' => 'Interface',           'logo' => 'assets/sponsors/interface.jpg',          'level' => 'team'],
    ['name' => 'Steelcase',           'logo' => 'assets/sponsors/steelcase.png',          'level' => 'team'],
    ['name' => 'McCoy Rockford',      'logo' => 'assets/sponsors/mccoy-rockford.png',     'level' => 'team'],
    ['name' => 'Teri Schock',         'logo' => 'assets/sponsors/teresa-schock.jpg',      'level' => 'team'],
    // Pump Studios logo TBD — the Drive copy is a bad crop; name placeholder for now.
    ['name' => 'Pump Studios',        'level' => 'team'],
    ['name' => 'Peach Pollen',        'logo' => 'assets/sponsors/peach-pollen.svg',       'level' => 'match'],
    ['name' => 'Digaball',            'logo' => 'assets/sponsors/digaball.png',           'level' => 'inkind'],
    ['name' => 'Aussies',             'logo' => 'assets/sponsors/aussies.png',            'level' => 'inkind'],
  ];

  // Level display order (top→bottom) + section label. Logo size per level is in css/site.css (.lvl-*).
  // 'match' is a new package, shown at the same size as in-kind.
  $sponsor_levels = [
    'presenting' => 'Presenting Sponsor',
    'court'      => 'Court Sponsors',
    'team'       => 'Team Sponsors',
    'match'      => 'Match Sponsors',
    'inkind'     => 'In-Kind Sponsors',
  ];

  include 'includes/header.php';
?>

<!-- home hero = the cropped poster image. The date/time/location are NOT baked into
     the image anymore — they're HTML text overlaid on the icons below, so updating the
     event details is a one-line edit here (no image editing). -->
<div class="slice home">
  <img src="assets/poster.png?v=7" alt="The Big Draw — Blind Draw 4s beach volleyball tournament, November 7th, 2026 at Aussies Grill & Beach Bar. Volleyball for Good, benefiting Big Brothers Big Sisters.">

  <!-- ►► EVENT DETAILS — edit this text to update the poster (no image editing needed) -->
  <span class="poster-ov ov-date">November 7th, 2026</span>
  <span class="poster-ov ov-loc">Aussies Grill &amp; Beach Bar</span>

  <!-- Register Now button hotspot — goes straight to the signup (Community is open) -->
  <a class="hot" data-label="Register Now" href="<?= htmlspecialchars($register_url) ?>" target="_blank" rel="noopener"
     style="left:5.3%;top:69.3%;width:29.4%;height:11%"></a>
</div>

<!-- registration — Community Division is open now; Competitive counts down to
     6pm CT Oct 7, 2026 then auto-flips to "open". Targets + signup URL live in
     includes/header.php ($community_is_open / $competitive_open_ts / $reg_signup_url). -->
<?php
  $cd_left = max(0, $competitive_open_ts - time());
  $cd_d = intdiv($cd_left, 86400);
  $cd_h = intdiv($cd_left % 86400, 3600);
  $cd_m = intdiv($cd_left % 3600, 60);
  $cd_s = $cd_left % 60;
?>
<section class="countdown-wrap"><div class="col">

  <?php if ($community_is_open): ?>
  <!-- Community Division — registration open now -->
  <div class="reg-live">
    <div class="soon-badge">Now Open</div>
    <p class="cd-kicker">Community Division Registration</p>
    <p class="cd-sub">Co-Ed · Rec · 4v4 · 8:30&nbsp;AM–Noon · Sat, Nov&nbsp;7</p>
    <div class="cta-row">
      <a class="btn grad" href="<?= htmlspecialchars($reg_signup_url) ?>" target="_blank" rel="noopener">Register now →</a>
    </div>
  </div>
  <?php endif; ?>

  <!-- Competitive Division — countdown to Oct 7, then auto-flips to "open" -->
  <div class="countdown<?= $competitive_is_open ? ' is-open' : '' ?>"
       data-reg-open="<?= date('c', $competitive_open_ts) ?>"
       data-signup="<?= htmlspecialchars($reg_signup_url) ?>">
    <p class="cd-kicker">Competitive Division Opens</p>
    <div class="cd-clock" role="timer" aria-label="Time until Competitive Division registration opens">
      <div class="cd-cell"><span class="cd-num" data-cd="days"><?= sprintf('%02d', $cd_d) ?></span><span class="cd-lab">Days</span></div>
      <div class="cd-cell"><span class="cd-num" data-cd="hours"><?= sprintf('%02d', $cd_h) ?></span><span class="cd-lab">Hrs</span></div>
      <div class="cd-cell"><span class="cd-num" data-cd="mins"><?= sprintf('%02d', $cd_m) ?></span><span class="cd-lab">Min</span></div>
      <div class="cd-cell"><span class="cd-num" data-cd="secs"><?= sprintf('%02d', $cd_s) ?></span><span class="cd-lab">Sec</span></div>
    </div>
    <p class="cd-open-msg">🏐 Competitive registration is open!</p>
    <p class="cd-sub">Wednesday, October 7, 2026 · 6:00&nbsp;PM CT · Co-Ed · All&nbsp;Levels · 4v4 · 11:30&nbsp;AM–8&nbsp;PM</p>
    <div class="cta-row">
      <a class="btn grad cd-cta"
         href="<?= $competitive_is_open ? htmlspecialchars($reg_signup_url) : htmlspecialchars($reg_notify_url) ?>"
         <?= $competitive_is_open ? 'target="_blank" rel="noopener"' : '' ?>><?= $competitive_is_open ? 'Register now →' : 'Get notified →' ?></a>
    </div>
  </div>

</div></section>
<script>
(function(){
  var el = document.querySelector('.countdown');
  if (!el || el.classList.contains('is-open')) return; // already open (server-rendered)
  var target = new Date(el.getAttribute('data-reg-open')).getTime();
  if (isNaN(target)) return;
  var signup = el.getAttribute('data-signup');
  var cta = el.querySelector('.cd-cta');
  var out = {};
  ['days','hours','mins','secs'].forEach(function(k){ out[k] = el.querySelector('[data-cd="'+k+'"]'); });
  var timer;
  function pad(n){ return (n < 10 ? '0' : '') + n; }
  function open(){
    el.classList.add('is-open');
    if (cta && signup){
      cta.setAttribute('href', signup);
      cta.setAttribute('target', '_blank');
      cta.setAttribute('rel', 'noopener');
      cta.textContent = 'Register now →';
    }
    if (timer) clearInterval(timer);
  }
  function tick(){
    var diff = target - Date.now();
    if (diff <= 0){ open(); return; }
    var s = Math.floor(diff / 1000);
    var d = Math.floor(s / 86400); s -= d * 86400;
    var h = Math.floor(s / 3600);  s -= h * 3600;
    var m = Math.floor(s / 60);    s -= m * 60;
    if (out.days)  out.days.textContent  = pad(d);
    if (out.hours) out.hours.textContent = pad(h);
    if (out.mins)  out.mins.textContent  = pad(m);
    if (out.secs)  out.secs.textContent  = pad(s);
  }
  tick();
  timer = setInterval(tick, 1000);
})();
</script>

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
                $has_logo = !empty($s['logo']);
                $mark = $has_logo
                  ? '<img src="'.htmlspecialchars($s['logo']).'" alt="'.htmlspecialchars($s['name']).'" loading="lazy">'
                  : '<span class="sponsor-ph">'.htmlspecialchars($s['name']).'</span>'; ?>
        <div class="sponsor-logo<?= $has_logo ? '' : ' is-ph' ?>"><?php if (!empty($s['url'])): ?><a href="<?= htmlspecialchars($s['url']) ?>" target="_blank" rel="noopener"><?= $mark ?></a><?php else: ?><?= $mark ?><?php endif; ?></div>
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
