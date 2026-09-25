<?php
// admin/register_period.php - Register and Manage Examination Periods/Time Slots
require_once '../includes/db.php';
checkAuth();

$msg = '';
$error = '';

// Handle Add Period
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_period'])) {
    $period_name = trim($_POST['period_name'] ?? '');
    $exam_date = trim($_POST['exam_date'] ?? '');
    $start_time = trim($_POST['start_time'] ?? '');
    $end_time = trim($_POST['end_time'] ?? '');

    if (empty($period_name) || empty($exam_date) || empty($start_time) || empty($end_time)) {
        $error = 'All fields are required to register an examination period.';
    } elseif ($start_time >= $end_time) {
        $error = 'Start time must be earlier than end time.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO periods (period_name, exam_date, start_time, end_time) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$period_name, $exam_date, $start_time, $end_time])) {
            $msg = 'Examination time slot registered successfully!';
        } else {
            $error = 'Failed to register time slot.';
        }
    }
}

// Handle Delete Period
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM periods WHERE id = ?");
    if ($stmt->execute([$delete_id])) {
        $msg = 'Time slot deleted successfully.';
    }
}

// Fetch all periods
$periods = $pdo->query("SELECT * FROM periods ORDER BY exam_date ASC, start_time ASC")->fetchAll();

require_once '../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <h2>⏰ Examination Time Slots & Dates</h2>
  <a href="dashboard.php" class="btn btn-secondary">&larr; Back to Dashboard</a>
</div>

<?php if ($msg): ?>
  <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>
<?php if ($error): ?>
  <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
  <!-- Add Period Form -->
  <div class="form-card" style="margin: 0;">
    <div class="form-header">
      <h3>Add Exam Period Slot</h3>
    </div>
    <form action="register_period.php" method="POST">
      <div class="form-group">
        <label for="period_name">Session Title / Label</label>
        <input type="text" id="period_name" name="period_name" class="form-control" placeholder="e.g. Morning Session 1" required>
      </div>

      <div class="form-group">
        <label for="exam_date">Examination Date</label>
        <input type="date" id="exam_date" name="exam_date" class="form-control" required>
      </div>

      <div class="form-grid-2">
        <div class="form-group">
          <label for="start_time">Start Time</label>
          <input type="time" id="start_time" name="start_time" class="form-control" required>
        </div>

        <div class="form-group">
          <label for="end_time">End Time</label>
          <input type="time" id="end_time" name="end_time" class="form-control" required>
        </div>
      </div>

      <button type="submit" name="add_period" class="btn btn-primary" style="width: 100%;">+ Save Time Slot</button>
    </form>
  </div>

  <!-- Periods List Table -->
  <div class="table-responsive">
    <table class="custom-table">
      <thead>
        <tr>
          <th>Date</th>
          <th>Session Name</th>
          <th>Start Time</th>
          <th>End Time</th>
          <th>Duration</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($periods)): ?>
          <?php foreach ($periods as $p): ?>
            <?php
              $start = new DateTime($p['start_time']);
              $end = new DateTime($p['end_time']);
              $duration = $start->diff($end)->format('%h hrs %i mins');
            ?>
            <tr>
              <td><strong><?php echo date('M d, Y', strtotime($p['exam_date'])); ?></strong></td>
              <td><?php echo htmlspecialchars($p['period_name']); ?></td>
              <td><?php echo date('h:i A', strtotime($p['start_time'])); ?></td>
              <td><?php echo date('h:i A', strtotime($p['end_time'])); ?></td>
              <td><span style="font-size: 0.85rem; color: var(--accent-info);"><?php echo $duration; ?></span></td>
              <td>
                <a href="register_period.php?delete=<?php echo $p['id']; ?>" onclick="return confirmDelete('Delete this time slot?');" class="btn btn-danger" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="6" style="text-align: center; color: var(--text-muted);">No exam time slots created yet.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
