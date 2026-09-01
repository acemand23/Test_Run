<?php
  $title  = $title  ?? 'The Big Draw — Volleyball for Good';
  $active = $active ?? '';

  // ── Registration gating ───────────────────────────────────────────────
  // Sign-ups open Sat Oct 7, 2026 at 6:00 PM Central. Oct 7 is still Central
  // DAYLIGHT time (CDT = UTC-5; DST ends Nov 1), so the instant is -05:00.
  // Everything below flips automatically at that moment — no edit needed on the day.
  $reg_open_ts    = strtotime('2026-10-07T18:00:00-05:00');
  $reg_signup_url = 'https://tbdvolley.vballmanager.com/org/tournament.php?slug=tbdvolley&event_id=121';
  $reg_is_open    = time() >= $reg_open_ts;
  $reg_notify_url = 'mailto:questions@tbdvolleyball.com?subject='
                  . rawurlencode('Notify me when The Big Draw registration opens');
  // Primary "Register" destination (home poster hotspot + countdown CTA):
  // the live signup once open, otherwise the on-site holder page.
  $register_url   = $reg_is_open ? $reg_signup_url : 'register.php';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($title) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Archivo:wght@500;600;700;800;900&family=Saira+Condensed:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/site.css?v=26">
</head>
<body class="<?= htmlspecialchars($body_class ?? '') ?>">
<header><div class="bar">
  <a class="brand" href="index.php"><img src="assets/brand/logo.png" alt="The Big Draw"></a>
  <button class="navtoggle" aria-label="Open menu" aria-expanded="false" aria-controls="navlinks">
    <span></span><span></span><span></span>
  </button>
  <nav class="links" id="navlinks">
    <a href="register.php" class="<?= $active==='register'?'on':'' ?>">Register</a>
    <a href="tournament.php" class="<?= $active==='tour'?'on':'' ?>">How It Works</a>
    <a href="volunteer.php" class="<?= $active==='involve'?'on':'' ?>">Get Involved</a>
    <a href="sponsor.php" class="<?= $active==='sponsor'?'on':'' ?>">Sponsor</a>
    <a href="gallery.php" class="<?= $active==='gallery'?'on':'' ?>">Gallery</a>
    <a href="contact.php" class="<?= $active==='contact'?'on':'' ?>">Contact</a>
    <a href="sponsor.php" class="pill">Sponsor Now</a>
  </nav>
</div></header>
