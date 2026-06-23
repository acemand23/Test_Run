<?php
  $title='Gallery — The Big Draw'; $active='gallery';
  // auto-discover media: assets/gallery/<year>/  (images + videos)
  $root  = __DIR__.'/assets/gallery';
  $years = [];
  if (is_dir($root)) {
    foreach (glob($root.'/*', GLOB_ONLYDIR) as $dir) {
      $files = glob($dir.'/*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP,mp4,MP4,mov,MOV,webm,WEBM}', GLOB_BRACE);
      sort($files);
      $items = [];
      foreach ($files as $f) {
        $ext  = strtolower(pathinfo($f, PATHINFO_EXTENSION));
        $type = in_array($ext, ['mp4','mov','webm'], true) ? 'video' : 'image';
        $items[] = ['file'=>basename($f), 'type'=>$type];
      }
      if ($items) $years[basename($dir)] = $items;
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

  <?php $i=0; foreach ($years as $y=>$items): ?>
  <div class="gallery-grid<?= $i===0?' on':'' ?>" data-year="<?= htmlspecialchars($y) ?>">
    <?php foreach ($items as $it):
            $src = 'assets/gallery/'.rawurlencode($y).'/'.rawurlencode($it['file']); ?>
      <?php if ($it['type']==='video'):
              $base     = pathinfo($it['file'], PATHINFO_FILENAME);
              $posterFs = $root.'/'.$y.'/thumbs/'.$base.'.jpg';
              $poster   = 'assets/gallery/'.rawurlencode($y).'/thumbs/'.rawurlencode($base).'.jpg'; ?>
      <a class="shot is-video" href="<?= $src ?>" data-type="video">
        <?php if (is_file($posterFs)): ?>
        <img loading="lazy" src="<?= $poster ?>" alt="The Big Draw <?= htmlspecialchars($y) ?> video">
        <?php else: ?>
        <video src="<?= $src ?>#t=0.1" preload="metadata" muted playsinline></video>
        <?php endif; ?>
      </a>
      <?php else: ?>
      <a class="shot" href="<?= $src ?>" data-type="image">
        <img loading="lazy" src="<?= $src ?>" alt="The Big Draw <?= htmlspecialchars($y) ?>">
      </a>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
  <?php $i++; endforeach; ?>
<?php endif; ?>
</div></section>

<div class="lightbox" id="lightbox" hidden>
  <button class="lb-close" aria-label="Close">&times;</button>
  <img id="lbimg" src="" alt="" hidden>
  <video id="lbvid" controls playsinline hidden></video>
</div>

<script>
  (function(){
    // year tabs — pause any playing video when switching
    var lbvid = document.getElementById('lbvid');
    document.querySelectorAll('.ytab').forEach(function(tab){
      tab.addEventListener('click', function(){
        var y = tab.dataset.year;
        document.querySelectorAll('.ytab').forEach(function(t){ t.classList.toggle('on', t===tab); });
        document.querySelectorAll('.gallery-grid').forEach(function(g){ g.classList.toggle('on', g.dataset.year===y); });
      });
    });
    // lightbox (images + videos)
    var lb = document.getElementById('lightbox'), lbimg = document.getElementById('lbimg');
    document.querySelectorAll('.shot').forEach(function(a){
      a.addEventListener('click', function(e){
        e.preventDefault();
        var href = a.getAttribute('href');
        if (a.dataset.type === 'video') {
          lbimg.hidden = true; lbimg.src = '';
          lbvid.src = href; lbvid.hidden = false; lbvid.play();
        } else {
          lbvid.pause(); lbvid.src = ''; lbvid.hidden = true;
          lbimg.src = href; lbimg.hidden = false;
        }
        lb.hidden = false;
      });
    });
    function close(){ lb.hidden = true; lbimg.src=''; lbimg.hidden=true; lbvid.pause(); lbvid.src=''; lbvid.hidden=true; }
    lb.addEventListener('click', close);
    lbvid.addEventListener('click', function(e){ e.stopPropagation(); }); // don't close when using video controls
    document.querySelector('.lb-close').addEventListener('click', function(e){ e.stopPropagation(); close(); });
    addEventListener('keydown', function(e){ if (e.key==='Escape') close(); });
  })();
</script>

<?php include 'includes/footer.php'; ?>
