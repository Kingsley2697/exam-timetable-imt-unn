<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['user_id']);
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Exam Timetable Scheduling System</title>
  <link rel="stylesheet" href="../css/style.css">
  <!-- Fallback root relative CSS path if accessed from root -->
  <script>
    // Adjust CSS path based on directory depth
    if (window.location.pathname.includes('/admin/')) {
      document.write('<link rel="stylesheet" href="../css/style.css">');
    } else {
      document.write('<link rel="stylesheet" href="css/style.css">');
    }
  </script>
</head>
<body>

<nav class="navbar">
  <div class="navbar-container">
    <a href="<?php echo $isLoggedIn ? '../index.php' : 'index.php'; ?>" class="brand-logo">
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
        <line x1="16" y1="2" x2="16" y2="6"></line>
        <line x1="8" y1="2" x2="8" y2="6"></line>
        <line x1="3" y1="10" x2="21" y2="10"></line>
      </svg>
      ExamSchedule
    </a>

    <ul class="nav-links">
      <li><a href="<?php echo ($currentPage == 'index.php' || !strpos($_SERVER['PHP_SELF'], '/admin/')) ? 'index.php' : '../index.php'; ?>" class="<?php echo $currentPage == 'index.php' ? 'active' : ''; ?>">Home</a></li>
      <li><a href="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') ? '../view_timetable.php' : 'view_timetable.php'; ?>" class="<?php echo $currentPage == 'view_timetable.php' ? 'active' : ''; ?>">View Timetable</a></li>
      
      <?php if ($isLoggedIn): ?>
        <li><a href="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') ? 'dashboard.php' : 'admin/dashboard.php'; ?>" class="<?php echo $currentPage == 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a></li>
        <li><a href="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') ? 'generate_timetable.php' : 'admin/generate_timetable.php'; ?>">Generator</a></li>
        <li><a href="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') ? 'logout.php' : 'admin/logout.php'; ?>" class="btn-danger btn" style="padding: 0.4rem 1rem;">Logout</a></li>
      <?php else: ?>
        <li><a href="<?php echo strpos($_SERVER['PHP_SELF'], '/admin/') ? 'login.php' : 'admin/login.php'; ?>" class="btn-admin-nav">Admin Login</a></li>
      <?php endif; ?>
    </ul>
  </div>
</nav>

<div class="main-wrapper">
