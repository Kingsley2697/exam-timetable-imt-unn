<?php
// includes/footer.php
?>
</div> <!-- End main-wrapper -->

<footer class="footer">
  <div class="footer-container">
    <p>&copy; <?php echo date('Y'); ?> Automated Exam Timetable Scheduling System. All rights reserved.</p>
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
