<?php
  $title='Gallery — The Big Draw'; $active='gallery';
  // auto-discover photos: assets/gallery/<year>/*.jpg|png|webp
  $root  = __DIR__.'/assets/gallery';
  $years = [];
  if (is_dir($root)) {
    foreach (glob($root.'/*', GLOB_ONLYDIR) as $dir) {
      $imgs = glob($dir.'/*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE);
      sort($imgs);
      if ($imgs) $years[basename($dir)] = $imgs;
    }
    krsort($years, SORT_NATURAL); // newest year first
  }
  include 'includes/header.php';
?>

<section class="pagehead"><div class="col">
  <div class="eyebrow">Past Years</div>
  <h1>Gallery</h1>
  <p>Moments from past Big Draw tournaments — good volleyball, great people, all for a good cause.</p>
</div></section>

<section class="block"><div class="col">
<?php if (!$years): ?>
  <p class="muted">Photos coming soon.</p>
<?php else: ?>
  <?php if (count($years) > 1): ?>
  <div class="yeartabs">
    <?php $i=0; foreach ($years as $y=>$_): ?>
      <button class="ytab<?= $i===0?' on':'' ?>" data-year="<?= htmlspecialchars($y) ?>"><?= htmlspecialchars($y) ?></button>
    <?php $i++; endforeach; ?>
  </div>
  <?php endif; ?>

  <?php $i=0; foreach ($years as $y=>$imgs): ?>
  <div class="gallery-grid<?= $i===0?' on':'' ?>" data-year="<?= htmlspecialchars($y) ?>">
    <?php foreach ($imgs as $img):
            $src = 'assets/gallery/'.rawurlencode($y).'/'.rawurlencode(basename($img)); ?>
      <a class="shot" href="<?= $src ?>">
        <img loading="lazy" src="<?= $src ?>" alt="The Big Draw <?= htmlspecialchars($y) ?>">
      </a>
    <?php endforeach; ?>
  </div>
  <?php $i++; endforeach; ?>
<?php endif; ?>
</div></section>

<div class="lightbox" id="lightbox" hidden>
  <button class="lb-close" aria-label="Close">&times;</button>
  <img id="lbimg" src="" alt="">
</div>

<script>
  (function(){
    // year tabs
    document.querySelectorAll('.ytab').forEach(function(tab){
      tab.addEventListener('click', function(){
        var y = tab.dataset.year;
        document.querySelectorAll('.ytab').forEach(function(t){ t.classList.toggle('on', t===tab); });
        document.querySelectorAll('.gallery-grid').forEach(function(g){ g.classList.toggle('on', g.dataset.year===y); });
      });
    });
    // lightbox
    var lb = document.getElementById('lightbox'), img = document.getElementById('lbimg');
    document.querySelectorAll('.shot').forEach(function(a){
      a.addEventListener('click', function(e){ e.preventDefault(); img.src = a.getAttribute('href'); lb.hidden = false; });
    });
    function close(){ lb.hidden = true; img.src = ''; }
    lb.addEventListener('click', close);
    document.querySelector('.lb-close').addEventListener('click', function(e){ e.stopPropagation(); close(); });
    addEventListener('keydown', function(e){ if (e.key==='Escape') close(); });
  })();
</script>

<?php include 'includes/footer.php'; ?>
