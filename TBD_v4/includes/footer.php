<footer class="foot">
  <?php if (($active ?? '') !== 'sponsor'): ?>
  <div class="tag">Draw <span class="t">Together.</span> Change <span class="t">Together.</span></div>
  <?php endif; ?>
  <div class="sub">Questions? <a href="mailto:questions@tbdvolleyball.com">questions@tbdvolleyball.com</a> · Benefiting Big Brothers Big Sisters of Central Texas</div>
</footer>

<script>
  // add #edit to any URL to reveal hotspot boxes for fine-tuning their positions
  (function(){
    function sync(){ document.querySelectorAll('.slice').forEach(function(s){ s.classList.toggle('edit', location.hash==='#edit'); }); }
    addEventListener('hashchange', sync); sync();
  })();
</script>
</body>
</html>
