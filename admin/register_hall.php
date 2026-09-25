<?php
// admin/register_hall.php - Register and Manage Examination Halls
require_once '../includes/db.php';
checkAuth();

$msg = '';
$error = '';

// Handle Hall Registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_hall'])) {
    $hall_name = trim($_POST['hall_name'] ?? '');
    $building = trim($_POST['building'] ?? '');
    $capacity = intval($_POST['capacity'] ?? 0);

    if (empty($hall_name) || $capacity <= 0) {
        $error = 'Hall name and a valid capacity (> 0) are required.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO halls (hall_name, building, capacity) VALUES (?, ?, ?)");
        if ($stmt->execute([$hall_name, $building, $capacity])) {
            $msg = 'Examination hall registered successfully!';
        } else {
            $error = 'Failed to register examination hall.';
        }
    }
}

// Handle Delete Hall
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM halls WHERE id = ?");
    if ($stmt->execute([$delete_id])) {
        $msg = 'Examination hall deleted successfully.';
    }
}

// Fetch all halls
$halls = $pdo->query("SELECT * FROM halls ORDER BY id DESC")->fetchAll();

require_once '../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <h2>🏛️ Examination Halls Registry</h2>
  <a href="dashboard.php" class="btn btn-secondary">&larr; Back to Dashboard</a>
</div>

<?php if ($msg): ?>
  <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>
<?php if ($error): ?>
  <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
  <!-- Add Hall Form -->
  <div class="form-card" style="margin: 0;">
    <div class="form-header">
      <h3>Register New Hall</h3>
    </div>
    <form action="register_hall.php" method="POST">
      <div class="form-group">
        <label for="hall_name">Hall Name / Number</label>
        <input type="text" id="hall_name" name="hall_name" class="form-control" placeholder="e.g. Main Auditorium" required>
      </div>

      <div class="form-group">
        <label for="building">Building / Location</label>
        <input type="text" id="building" name="building" class="form-control" placeholder="e.g. Science Block A">
      </div>

      <div class="form-group">
        <label for="capacity">Seating Capacity</label>
        <input type="number" id="capacity" name="capacity" class="form-control" placeholder="e.g. 150" min="1" required>
      </div>

      <button type="submit" name="add_hall" class="btn btn-primary" style="width: 100%;">+ Save Examination Hall</button>
    </form>
  </div>

  <!-- Halls List Table -->
  <div class="table-responsive">
    <table class="custom-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Hall Name</th>
          <th>Building</th>
          <th>Capacity</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($halls)): ?>
          <?php foreach ($halls as $hall): ?>
            <tr>
              <td>#<?php echo $hall['id']; ?></td>
              <td><strong><?php echo htmlspecialchars($hall['hall_name']); ?></strong></td>
              <td><?php echo htmlspecialchars($hall['building'] ?: 'N/A'); ?></td>
              <td><span style="font-weight: 600; color: var(--accent-success);"><?php echo $hall['capacity']; ?> seats</span></td>
              <td><span class="badge badge-published"><?php echo ucfirst($hall['status']); ?></span></td>
              <td>
                <a href="register_hall.php?delete=<?php echo $hall['id']; ?>" onclick="return confirmDelete('Are you sure you want to delete this hall?');" class="btn btn-danger" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="6" style="text-align: center; color: var(--text-muted);">No examination halls registered yet.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
