<footer class="foot">
  <?php if (($active ?? '') !== 'sponsor'): ?>
  <div class="tag">Draw <span class="t">Together.</span> Change <span class="t">Together.</span></div>
  <?php endif; ?>
  <div class="sub">Questions? <a href="mailto:questions@tbdvolleyball.com">questions@tbdvolleyball.com</a> · Benefiting Big Brothers Big Sisters of Central Texas</div>
</footer>

<script>
  // mobile hamburger menu
  (function(){
    var btn = document.querySelector('.navtoggle');
    var bar = document.querySelector('.bar');
    if (btn && bar) {
      function setOpen(open){
        bar.classList.toggle('open', open);
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        btn.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
      }
      btn.addEventListener('click', function(e){
        e.stopPropagation();
        setOpen(!bar.classList.contains('open'));
      });
      // close when tapping outside the menu
      document.addEventListener('click', function(e){
        if (bar.classList.contains('open') && !bar.contains(e.target)) setOpen(false);
      });
      // close on Escape
      document.addEventListener('keydown', function(e){
        if (e.key === 'Escape' && bar.classList.contains('open')) setOpen(false);
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
