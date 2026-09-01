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

<!-- home hero = the cropped poster image. The date/time/location are NOT baked into
     the image anymore — they're HTML text overlaid on the icons below, so updating the
     event details is a one-line edit here (no image editing). -->
<div class="slice home">
  <img src="assets/poster.png?v=7" alt="The Big Draw — Blind Draw 4s beach volleyball tournament, November 7th, 2026 at Aussies Grill & Beach Bar. Volleyball for Good, benefiting Big Brothers Big Sisters.">

  <!-- ►► EVENT DETAILS — edit this text to update the poster (no image editing needed) -->
  <span class="poster-ov ov-date">November 7th, 2026</span>
  <span class="poster-ov ov-loc">Aussies Grill &amp; Beach Bar</span>

  <!-- Register Now button hotspot -->
  <a class="hot" data-label="Register Now" href="<?= $register_url ?>"
     style="left:5.3%;top:69.3%;width:29.4%;height:11%"></a>
</div>

<!-- registration countdown — ticks to 6pm CT Oct 7, 2026, then auto-flips to "open".
     The target instant + signup URL live in includes/header.php ($reg_open_ts / $reg_signup_url). -->
<?php
  $cd_left = max(0, $reg_open_ts - time());
  $cd_d = intdiv($cd_left, 86400);
  $cd_h = intdiv($cd_left % 86400, 3600);
  $cd_m = intdiv($cd_left % 3600, 60);
  $cd_s = $cd_left % 60;
?>
<section class="countdown-wrap"><div class="col">
  <div class="countdown<?= $reg_is_open ? ' is-open' : '' ?>"
       data-reg-open="<?= date('c', $reg_open_ts) ?>"
       data-signup="<?= htmlspecialchars($reg_signup_url) ?>">
    <p class="cd-kicker">Registration Opens</p>
    <div class="cd-clock" role="timer" aria-label="Time until registration opens">
      <div class="cd-cell"><span class="cd-num" data-cd="days"><?= sprintf('%02d', $cd_d) ?></span><span class="cd-lab">Days</span></div>
      <div class="cd-cell"><span class="cd-num" data-cd="hours"><?= sprintf('%02d', $cd_h) ?></span><span class="cd-lab">Hrs</span></div>
      <div class="cd-cell"><span class="cd-num" data-cd="mins"><?= sprintf('%02d', $cd_m) ?></span><span class="cd-lab">Min</span></div>
      <div class="cd-cell"><span class="cd-num" data-cd="secs"><?= sprintf('%02d', $cd_s) ?></span><span class="cd-lab">Sec</span></div>
    </div>
    <p class="cd-open-msg">🏐 Registration is open!</p>
    <p class="cd-sub">Saturday, October 7, 2026 · 6:00&nbsp;PM CT</p>
    <div class="cta-row">
      <a class="btn grad cd-cta"
         href="<?= $reg_is_open ? htmlspecialchars($reg_signup_url) : htmlspecialchars($reg_notify_url) ?>"
         <?= $reg_is_open ? 'target="_blank" rel="noopener"' : '' ?>><?= $reg_is_open ? 'Register now →' : 'Get notified →' ?></a>
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
