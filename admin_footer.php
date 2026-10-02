    </div><!-- /.admin-content -->
  </div><!-- /.admin-main -->
</div><!-- /.admin-layout -->

<script src="../assets/js/sims.js"></script>
<script src="../assets/js/search.js"></script>
<script>
/* Admin sidebar toggle — handles both desktop collapse and mobile drawer */
(function(){
  var btn    = document.getElementById('asb-toggle');
  var layout = document.querySelector('.admin-layout');
  if (!btn || !layout) return;

  var isMobile = function(){ return window.innerWidth <= 768; };

  btn.addEventListener('click', function(){
    if (isMobile()) {
      /* Mobile: toggle sidebar-open class to slide in/out */
      var open = layout.classList.toggle('sidebar-open');
      btn.setAttribute('aria-expanded', String(open));
    } else {
      /* Desktop: toggle sidebar-collapsed to collapse/expand */
      var collapsed = layout.classList.toggle('sidebar-collapsed');
      btn.setAttribute('aria-expanded', String(!collapsed));
    }
  });

  /* Close mobile sidebar when clicking outside */
  document.addEventListener('click', function(e){
    if (isMobile() && layout.classList.contains('sidebar-open')) {
      var sidebar = document.getElementById('admin-sidebar');
      if (sidebar && !sidebar.contains(e.target) && !btn.contains(e.target)) {
        layout.classList.remove('sidebar-open');
        btn.setAttribute('aria-expanded', 'false');
      }
    }
  });

  /* Re-check on resize */
  window.addEventListener('resize', function(){
    if (!isMobile()) {
      layout.classList.remove('sidebar-open');
    }
  }, { passive: true });
})();
</script>
</body>
</html>
