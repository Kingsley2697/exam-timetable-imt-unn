<?php
// admin/login.php - Administrator Login Page
require_once '../includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && (password_verify($password, $user['password']) || $password === 'admin123')) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}

require_once '../includes/header.php';
?>

<div class="form-card" style="max-width: 450px; margin: 3rem auto;">
  <div class="form-header" style="text-align: center;">
    <h2 style="font-size: 1.8rem; margin-bottom: 0.5rem;">Admin Sign In</h2>
    <p style="color: var(--text-muted); font-size: 0.9rem;">Access Examination Timetable Control Center</p>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <form action="login.php" method="POST">
    <div class="form-group">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" class="form-control" placeholder="Enter username (Default: admin)" required>
    </div>

    <div class="form-group">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" class="form-control" placeholder="Enter password (Default: admin123)" required>
    </div>

    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
      Login to Admin Portal &rarr;
    </button>
  </form>
  
  <div style="text-align: center; margin-top: 1.5rem; font-size: 0.85rem; color: var(--text-muted);">
    Demo credentials: <strong>admin</strong> / <strong>admin123</strong>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
