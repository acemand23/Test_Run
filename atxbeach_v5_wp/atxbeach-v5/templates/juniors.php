<?php
if (!defined('ABSPATH')) exit;
$ATXB = ATXBEACH_ASSETS;
$LINK = [
    'index'   => esc_url(atxb_v5_url('home')),
    'play'    => esc_url(atxb_v5_url('play')),
    'train'   => esc_url(atxb_v5_url('train')),
    'juniors' => esc_url(atxb_v5_url('juniors')),
    'events'  => esc_url(atxb_v5_url('events')),
    'leagues' => esc_url(atxb_v5_url('leagues')),
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ATX Juniors — Youth Beach Volleyball | ATX Beach</title>
<meta name="description" content="ATX Juniors develops young beach volleyball athletes on and off the sand through purposeful training, expert coaching, and recruiting support. Camps, clinics, and competitive pathways in Austin, TX.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<?php wp_head(); ?>
<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo $ATXB; ?>pages.css">
</head>
<body class="page-juniors">

<div class="ticker" role="region" aria-label="Latest news and deals">
  <a class="ticker__brand" href="<?php echo $LINK['index']; ?>" aria-label="ATX Beach home"><img src="<?php echo $ATXB; ?>images/photos/logo-white.png" alt="ATX Beach"></a>
  <span class="ticker__flag" aria-hidden="true">News &amp; Deals</span>
  <div class="ticker__viewport"><div class="ticker__track">
    <div class="ticker__group">
      <span class="ticker__item"><b>NEW</b> Summer memberships are open — train year-round on 8 pro courts</span>
      <span class="ticker__item">🏐 Coed 4s &amp; 6s leagues forming now — grab your team's spot</span>
      <span class="ticker__item">🎉 Book the whole venue — corporate &amp; private events, 8 courts + full bar</span>
      <span class="ticker__item">☀️ Open play daily — check in at the Turtle Shack</span>
      <span class="ticker__item"><b>DEAL</b> New-player clinic — first session on us</span>
      <span class="ticker__item">🧒 ATX Juniors summer camps — spots filling fast</span>
      <span class="ticker__item">🍹 Turtle Shack: full bar, 7 TVs, cold drinks &amp; concessions</span>
    </div>
    <div class="ticker__group" aria-hidden="true">
      <span class="ticker__item"><b>NEW</b> Summer memberships are open — train year-round on 8 pro courts</span>
      <span class="ticker__item">🏐 Coed 4s &amp; 6s leagues forming now — grab your team's spot</span>
      <span class="ticker__item">🎉 Book the whole venue — corporate &amp; private events, 8 courts + full bar</span>
      <span class="ticker__item">☀️ Open play daily — check in at the Turtle Shack</span>
      <span class="ticker__item"><b>DEAL</b> New-player clinic — first session on us</span>
      <span class="ticker__item">🧒 ATX Juniors summer camps — spots filling fast</span>
      <span class="ticker__item">🍹 Turtle Shack: full bar, 7 TVs, cold drinks &amp; concessions</span>
    </div>
  </div></div>
  <a class="ticker__cta" href="https://atxbeach.com/book-a-court/" target="_blank" rel="noopener">Book a Court</a>
</div>

<main>
  <header class="phero" style="background-image:url(<?php echo $ATXB; ?>images/site/juniors-team.jpg)">
    <div class="wrap">
      <div class="breadcrumb"><a href="<?php echo $LINK['index']; ?>">Home</a> &rsaquo; ATX Juniors</div>
      <span class="eyebrow">Youth Beach Volleyball</span>
      <h1>Better players — and better people.</h1>
      <p>ATX Juniors helps young athletes grow on and off the sand through purposeful training, expert coaching, and recruiting support. Camps, clinics, and competitive pathways for every age and level.</p>
      <div class="actions">
        <a href="https://juniors.atxbeach.com/" target="_blank" rel="noopener" class="btn btn-accent">Explore Juniors Programs</a>
        <a href="<?php echo $LINK['index']; ?>" class="btn btn-ghost">Back to Courts</a>
      </div>
    </div>
  </header>

  <section class="panel"><div class="wrap">
    <div class="panel-head">
      <span class="eyebrow">Programs</span>
      <h2>A clear path for every young player</h2>
      <p>Start with the basics or train toward college recruiting — there's a track that meets your athlete where they are.</p>
    </div>
    <div class="cards">
      <div class="sand card"><span class="num">1</span><h3>Camps</h3><p>Seasonal and school-break camps that build fundamentals, confidence, and a love of the game in a fun, supportive setting.</p></div>
      <div class="sand card"><span class="num">2</span><h3>Clinics &amp; Training</h3><p>Ongoing skill development across passing, setting, hitting, and defense, grouped by age and ability.</p></div>
      <div class="sand card"><span class="num">3</span><h3>Competitive Pathway</h3><p>Tournament prep and competitive play for serious athletes, with recruiting support for the next level.</p></div>
    </div>
  </div></section>

  <section class="panel"><div class="wrap">
    <div class="split">
      <div class="sand prose">
        <h2>The ATX Juniors difference</h2>
        <p>ATX Juniors is committed to fostering the growth of athletes both on and off the sand. Our coaches emphasize skill development, game knowledge, fitness, and teamwork — the habits that make great players and great teammates.</p>
        <h3>What players gain</h3>
        <ul>
          <li>Technical skills and beach-specific movement.</li>
          <li>Game IQ: strategy, communication, and reading the court.</li>
          <li>Fitness, discipline, and teamwork that carry beyond volleyball.</li>
          <li>Recruiting guidance for athletes pursuing college play.</li>
        </ul>
        <h3>For parents</h3>
        <ul>
          <li>Expert, vetted coaching in a safe, shaded venue.</li>
          <li>Clear schedules and easy registration.</li>
          <li>A welcoming community of families and players.</li>
        </ul>
      </div>
      <aside class="sand aside">
        <span class="label">ATX Juniors</span>
        <h3>Get your athlete started</h3>
        <p>See current camps, clinics, and competitive programs, and register online.</p>
        <a href="https://juniors.atxbeach.com/" target="_blank" rel="noopener" class="btn btn-accent">View Programs</a>
        <a href="<?php echo $LINK['index']; ?>" class="btn btn-outline">Back to Courts</a>
        <p class="fine">Opens juniors.atxbeach.com in a new tab.</p>
      </aside>
    </div>
  </div></section>

  <section class="panel"><div class="wrap">
    <div class="cta">
      <h2>Give your athlete room to grow.</h2>
      <p>From first touches to college recruiting, ATX Juniors meets every player where they are and helps them get better.</p>
      <div class="actions">
        <a href="https://juniors.atxbeach.com/" target="_blank" rel="noopener" class="btn btn-accent">Explore ATX Juniors</a>
        <a href="<?php echo $LINK['index']; ?>" class="btn btn-ghost">Back to Courts</a>
      </div>
    </div>
  </div></section>
</main>

<footer class="venue">
  <span class="venue__pitch"><b>8 lit pro courts</b> · white sand · full bar · leagues &amp; tournaments</span>
  <span class="venue__meta">
    <span>Open daily 8a–12a</span><span class="dot">·</span>
    <a href="tel:+15128789459">(512) 878-9459</a><span class="dot">·</span>
    <a href="https://maps.google.com/?q=11000+Middle+Fiskville+Road+Bldg+E+Austin+TX+78753" target="_blank" rel="noopener">11000 Middle Fiskville Rd, Austin</a><span class="dot">·</span>
    <span class="venue__social">
      <a href="https://www.instagram.com/atxbeach/" target="_blank" rel="noopener" aria-label="ATX Beach on Instagram"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2.5" y="2.5" width="19" height="19" rx="5.5"/><circle cx="12" cy="12" r="4.3"/><circle cx="17.6" cy="6.4" r="1.1" fill="currentColor" stroke="none"/></svg></a>
      <a href="https://www.facebook.com/profile.php?id=61553887867812" target="_blank" rel="noopener" aria-label="ATX Beach on Facebook"><svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M22 12a10 10 0 1 0-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.5 1.49-3.89 3.77-3.89 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.44 2.89h-2.34v6.99A10 10 0 0 0 22 12z"/></svg></a>
    </span>
  </span>
</footer>

<a class="waiver-fab" href="https://www.yourcourts.com/yourcourts/security/register/2892776" target="_blank" rel="noopener" aria-label="Sign your waiver">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
  Sign Waiver
</a>

<?php wp_footer(); ?>
</body>
</html>
