<footer class="foot">
  <div class="tag">Draw <span class="t">Together.</span> Change <span class="t">Together.</span></div>
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
</body>
</html>
