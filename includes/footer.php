<?php
// includes/footer.php
?>
</div> <!-- End main-wrapper -->

<footer class="footer">
  <div class="footer-container">
    <p>IMT-UNN @<?php echo date('Y'); ?> automated exam time table scheduling system. All right reserved</p>
  </div>
</footer>

<script>
  if (window.location.pathname.includes('/admin/')) {
    document.write('<script src="../js/script.js"><\/script>');
  } else {
    document.write('<script src="js/script.js"><\/script>');
  }
</script>
</body>
</html>
