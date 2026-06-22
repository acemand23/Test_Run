<footer class="foot">
  <div class="tag">The Draw is <span class="t">Random.</span> The Impact is <span class="t">Real.</span></div>
  <div class="sub">Questions? <a href="mailto:questions@tbdvolleyball.com">questions@tbdvolleyball.com</a> · Benefiting Big Brothers Big Sisters of Central Texas</div>
</footer>

<script>
  // mobile hamburger menu
  (function(){
    var btn = document.querySelector('.navtoggle');
    var bar = document.querySelector('.bar');
    if (btn && bar) {
      btn.addEventListener('click', function(){
        var open = bar.classList.toggle('open');
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        btn.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
      });
    }
  })();
  // add #edit to any URL to reveal hotspot boxes for fine-tuning their positions
  (function(){
    function sync(){ document.querySelectorAll('.slice').forEach(function(s){ s.classList.toggle('edit', location.hash==='#edit'); }); }
    addEventListener('hashchange', sync); sync();
  })();
</script>
<?php
// Build stamp: the live commit of this checkout — verify a deploy from "View Source".
// Reads .git at render time (filesystem read, unaffected by the .htaccess web block);
// shows "dev" when this isn't a git checkout.
$tbd_git = dirname(__DIR__) . '/.git';
$tbd_rev = 'dev';
if (is_dir($tbd_git)) {
    $head = @trim((string) @file_get_contents("$tbd_git/HEAD"));
    if (strncmp($head, 'ref:', 4) === 0) {
        $ref = trim(substr($head, 4));
        if (is_file("$tbd_git/$ref")) {
            $tbd_rev = trim((string) file_get_contents("$tbd_git/$ref"));
        } elseif (preg_match('/^([0-9a-f]{40})\s+' . preg_quote($ref, '/') . '$/m', (string) @file_get_contents("$tbd_git/packed-refs"), $m)) {
            $tbd_rev = $m[1];
        }
    } elseif ($head !== '') {
        $tbd_rev = $head; // detached HEAD
    }
}
echo '<!-- build: ' . substr($tbd_rev, 0, 7) . ' @ ' . gmdate('c') . " -->\n";
?>
</body>
</html>
