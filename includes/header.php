<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['user_id']);
$currentPage = basename($_SERVER['PHP_SELF']);
$inAdmin = strpos($_SERVER['PHP_SELF'], '/admin/') !== false;
$imgPath = $inAdmin ? '../images/logo.png' : 'images/logo.png';
$homePath = $inAdmin ? '../index.php' : 'index.php';
$viewPath = $inAdmin ? '../view_timetable.php' : 'view_timetable.php';
$loginPath = $inAdmin ? 'login.php' : 'admin/login.php';
$dashPath = $inAdmin ? 'dashboard.php' : 'admin/dashboard.php';
$genPath = $inAdmin ? 'generate_timetable.php' : 'admin/generate_timetable.php';
$logoutPath = $inAdmin ? 'logout.php' : 'admin/logout.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>IMT-UNN Exam Timetable Scheduling System</title>
  <script>
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
    <a href="<?php echo $homePath; ?>" class="brand-logo">
      <img src="<?php echo $imgPath; ?>" alt="IMT/UNN Logo" class="school-logo-img">
      <span>IMT-UNN Schedule</span>
    </a>

    <ul class="nav-links">
      <li><a href="<?php echo $homePath; ?>" class="<?php echo $currentPage == 'index.php' ? 'active' : ''; ?>">Home</a></li>
      <li><a href="<?php echo $viewPath; ?>" class="<?php echo $currentPage == 'view_timetable.php' ? 'active' : ''; ?>">View Timetable</a></li>
      
      <?php if ($isLoggedIn): ?>
        <li><a href="<?php echo $dashPath; ?>" class="<?php echo $currentPage == 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a></li>
        <li><a href="<?php echo $genPath; ?>" class="<?php echo $currentPage == 'generate_timetable.php' ? 'active' : ''; ?>">Generator</a></li>
        <li><a href="<?php echo $logoutPath; ?>" class="btn-danger btn" style="padding: 0.4rem 1rem;">Logout</a></li>
      <?php else: ?>
        <li><a href="<?php echo $loginPath; ?>" class="btn-admin-nav">Admin Login</a></li>
      <?php endif; ?>
    </ul>
  </div>
</nav>

<div class="main-wrapper">
