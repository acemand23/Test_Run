<?php
  $title  = $title  ?? 'The Big Draw — Volleyball for Good';
  $active = $active ?? '';
  // Registration currently routes to register.php (a "coming soon" holder page).
  // When sign-ups open, point this at the real vballmanager.com event URL
  // (or build the form into register.php).
  $register_url = 'register.php';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($title) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Archivo:wght@500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/site.css?v=10">
</head>
<body>
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
