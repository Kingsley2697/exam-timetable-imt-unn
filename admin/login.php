<?php
// admin/login.php - Administrator Login Page
require_once '../includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_input = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($login_input) || empty($password)) {
        $error = 'Please enter your username/email and password.';
    } else {
        // Support logging in with Username OR Email address
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$login_input, $login_input]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid username/email or password.';
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
      <label for="username">Username or Email Address</label>
      <input type="text" id="username" name="username" class="form-control" placeholder="Enter username or email" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
    </div>

    <div class="form-group">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required>
    </div>

    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem; padding: 0.85rem;">
      Login to Admin Portal &rarr;
    </button>
  </form>

</div>

<?php require_once '../includes/footer.php'; ?>
