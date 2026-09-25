<?php
// admin/register.php - Admin Sign Up / Registration Page
require_once '../includes/db.php';

$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($full_name) || empty($username) || empty($email) || empty($password)) {
        $error = 'All fields are required to register.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match. Please try again.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        // Check if username or email already exists
        $chk = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $chk->execute([$username, $email]);
        if ($chk->fetch()) {
            $error = 'Username or Email address is already registered.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, email) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$username, $hashedPassword, $full_name, $email])) {
                // Auto log in after sign up
                $newId = $pdo->lastInsertId();
                $_SESSION['user_id'] = $newId;
                $_SESSION['username'] = $username;
                $_SESSION['full_name'] = $full_name;

                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

require_once '../includes/header.php';
?>

<div class="form-card" style="max-width: 500px; margin: 2rem auto;">
  <div class="form-header" style="text-align: center;">
    <h2 style="font-size: 1.8rem; margin-bottom: 0.5rem;">Create Admin Account</h2>
    <p style="color: var(--text-muted); font-size: 0.9rem;">Sign up to access the IMT-UNN Timetable Control Center</p>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <form action="register.php" method="POST">
    <div class="form-group">
      <label for="full_name">Full Name</label>
      <input type="text" id="full_name" name="full_name" class="form-control" placeholder="e.g. Asogwa Chekwube" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
    </div>

    <div class="form-group">
      <label for="email">Email Address</label>
      <input type="email" id="email" name="email" class="form-control" placeholder="e.g. asogwachekwube26@gmail.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
    </div>

    <div class="form-group">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" class="form-control" placeholder="Choose a username" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
    </div>

    <div class="form-grid-2">
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" class="form-control" placeholder="At least 6 characters" required>
      </div>

      <div class="form-group">
        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Re-type password" required>
      </div>
    </div>

    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem; padding: 0.85rem;">
      ✨ Create Admin Account & Sign In &rarr;
    </button>
  </form>

  <div style="text-align: center; margin-top: 1.5rem; border-top: 1px solid var(--border-color); padding-top: 1rem; font-size: 0.9rem;">
    Already have an account? <a href="login.php" style="font-weight: 600; color: var(--accent-primary);">Sign In Here &rarr;</a>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
