<?php
  $title  = $title  ?? 'The Big Draw — Volleyball for Good';
  $active = $active ?? '';
  // TODO: replace with the specific vballmanager.com event registration link
  $register_url = 'https://vballmanager.com/';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($title) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Archivo:wght@500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/festival.css?v=<?= @filemtime(__DIR__ . '/../css/festival.css') ?: time() ?>">
</head>
<body>
<header><div class="wrap nav">
  <a href="index.php"><img src="assets/brand/logo.png" alt="The Big Draw"></a>
  <nav class="links">
    <a href="index.php"      class="<?= $active==='home'?'on':'' ?>">Home</a>
    <a href="tournament.php" class="<?= $active==='tour'?'on':'' ?>">Tournament</a>
    <a href="sponsor.php"    class="<?= $active==='sponsor'?'on':'' ?>">Sponsor</a>
    <a href="<?= $register_url ?>" class="pill">Register</a>
  </nav>
</div></header>
