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
<title>Leagues — Our League Partners | ATX Beach</title>
<meta name="description" content="Meet the league partners who run coed 4s and 6s beach volleyball leagues on the sand at ATX Beach in Austin — weekly matches, real standings, and season-ending brackets.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<?php wp_head(); ?>
<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo $ATXB; ?>pages.css">
</head>
<body class="page-leagues">

<!-- ticker -->
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
  <!-- court-style hero -->
  <header class="phero" style="background-image:url(<?php echo $ATXB; ?>images/photos/action-43.jpg)">
    <div class="wrap">
      <div class="breadcrumb"><a href="<?php echo $LINK['index']; ?>">Home</a> &rsaquo; Leagues</div>
      <span class="eyebrow">Leagues</span>
      <h1>Our league partners.</h1>
      <p>ATX Beach is home to coed 4s &amp; 6s leagues run by our league partners — weekly matches, real standings, and a season-ending bracket on 8 lit pro courts. Meet the crews below and pick your league.</p>
    </div>
  </header>

  <!-- league partners grid -->
  <section class="panel"><div class="wrap">
    <div class="panel-head">
      <span class="eyebrow">League Partners</span>
      <h2>Who runs our leagues</h2>
      <p>These partners organize and run the leagues on our sand — tap any partner to see their current seasons and sign up.</p>
    </div>
    <div class="cards">

      <!-- Real Austin league partners. Links verified; logo boxes are placeholders until real artwork is supplied. -->
      <div class="sand card" style="--c:var(--teal)">
        <span class="partner__logo" aria-hidden="true">ACES</span>
        <h3>ACES</h3>
        <p>Austin Crew of Everything Social — coed indoor &amp; sand volleyball leagues and social sports around town.</p>
        <a href="https://acesintexas.com/" target="_blank" rel="noopener" class="btn btn-accent">Visit Partner</a>
      </div>

      <div class="sand card" style="--c:var(--coral)">
        <span class="partner__logo" aria-hidden="true">SK</span>
        <h3>SportsKind</h3>
        <p>Austin adult rec sports — coed sand volleyball leagues on our courts, plus grass, indoor &amp; more.</p>
        <a href="https://sportskind.com/austin/" target="_blank" rel="noopener" class="btn btn-accent">Visit Partner</a>
      </div>

      <div class="sand card" style="--c:var(--gold)">
        <span class="partner__logo" aria-hidden="true">PS</span>
        <h3>Pride Sports</h3>
        <p>LGBTQ+ inclusive Austin sports league running coed sand volleyball and a full slate of social sports.</p>
        <a href="https://pridesportsaustin.com" target="_blank" rel="noopener" class="btn btn-accent">Visit Partner</a>
      </div>

      <div class="sand card" style="--c:var(--purple)">
        <span class="partner__logo" aria-hidden="true">SS</span>
        <h3>Stonewall Sports</h3>
        <p>LGBTQ+ &amp; ally community sports nonprofit — volleyball, kickball, pickleball &amp; more here in Austin.</p>
        <a href="https://www.stonewallsportsaustin.org/" target="_blank" rel="noopener" class="btn btn-accent">Visit Partner</a>
      </div>

      <div class="sand card" style="--c:var(--sun)">
        <span class="partner__logo" aria-hidden="true">RR</span>
        <h3>Red Rocks Church</h3>
        <p>Sports ministry running year-round rec adult volleyball leagues here in Austin.</p>
        <a href="https://www.redrockschurch.com/sports/" target="_blank" rel="noopener" class="btn btn-accent">Visit Partner</a>
      </div>

      <div class="sand card" style="--c:var(--teal)">
        <span class="partner__logo" aria-hidden="true">SnS</span>
        <h3>Sip &rsquo;n Serve</h3>
        <p>Austin volleyball community with weekly pickup and monthly tournaments across sand, grass &amp; indoor.</p>
        <a href="https://www.sipnservesociety.com/" target="_blank" rel="noopener" class="btn btn-accent">Visit Partner</a>
      </div>

    </div>
    <p class="note">Logo tiles are placeholders — partner artwork coming soon. Each “Visit Partner” link opens that partner’s official site.</p>
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
      <a href="https://www.facebook.com/profile.php?id=61553879228225" target="_blank" rel="noopener" aria-label="ATX Beach on Facebook"><svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M22 12a10 10 0 1 0-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.5 1.49-3.89 3.77-3.89 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.44 2.89h-2.34v6.99A10 10 0 0 0 22 12z"/></svg></a>
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
