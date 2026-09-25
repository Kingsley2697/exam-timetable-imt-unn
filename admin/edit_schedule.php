<?php
// admin/edit_schedule.php - Manual Timetable Schedule Adjuster & Overrides
require_once '../includes/db.php';
checkAuth();

$msg = '';
$error = '';

// Handle Schedule Entry Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_schedule'])) {
    $schedule_id = intval($_POST['schedule_id']);
    $hall_id = intval($_POST['hall_id']);
    $period_id = intval($_POST['period_id']);

    // Check collision (hall occupied at period for another course)
    $stmt = $pdo->prepare("SELECT id FROM timetable WHERE hall_id = ? AND period_id = ? AND id != ?");
    $stmt->execute([$hall_id, $period_id, $schedule_id]);
    if ($stmt->fetch()) {
        $error = 'Conflict detected! The selected hall is already occupied during this time slot.';
    } else {
        $upd = $pdo->prepare("UPDATE timetable SET hall_id = ?, period_id = ? WHERE id = ?");
        if ($upd->execute([$hall_id, $period_id, $schedule_id])) {
            $msg = 'Schedule updated successfully!';
        } else {
            $error = 'Failed to update schedule entry.';
        }
    }
}

// Handle Delete Entry
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM timetable WHERE id = ?");
    if ($stmt->execute([$del_id])) {
        $msg = 'Schedule entry removed successfully.';
    }
}

// Fetch all options for dropdowns
$halls = $pdo->query("SELECT * FROM halls WHERE status='active' ORDER BY hall_name ASC")->fetchAll();
$periods = $pdo->query("SELECT * FROM periods ORDER BY exam_date ASC, start_time ASC")->fetchAll();

// Fetch schedule list
$schedules = $pdo->query("
    SELECT t.id, t.course_id, t.hall_id, t.period_id, t.status,
           c.course_code, c.course_title, c.student_count,
           h.hall_name, p.period_name, p.exam_date, p.start_time, p.end_time
    FROM timetable t
    JOIN courses c ON t.course_id = c.id
    JOIN halls h ON t.hall_id = h.id
    JOIN periods p ON t.period_id = p.id
    ORDER BY p.exam_date ASC, p.start_time ASC
")->fetchAll();

require_once '../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <h2>⚙️ Edit & Adjust Examination Schedules</h2>
  <a href="dashboard.php" class="btn btn-secondary">&larr; Back to Dashboard</a>
</div>

<?php if ($msg): ?>
  <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>
<?php if ($error): ?>
  <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="table-responsive">
  <table class="custom-table">
    <thead>
      <tr>
        <th>Course</th>
        <th>Assigned Hall</th>
        <th>Assigned Time Slot</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($schedules)): ?>
        <?php foreach ($schedules as $sched): ?>
          <tr>
            <td>
              <strong style="color: var(--accent-primary);"><?php echo htmlspecialchars($sched['course_code']); ?></strong>
              <div style="font-size: 0.85rem; color: var(--text-muted);"><?php echo htmlspecialchars($sched['course_title']); ?> (Enrolled: <?php echo $sched['student_count']; ?>)</div>
            </td>

            <form action="edit_schedule.php" method="POST">
              <input type="hidden" name="schedule_id" value="<?php echo $sched['id']; ?>">
              <td>
                <select name="hall_id" class="form-control" style="width: 180px; padding: 0.4rem;">
                  <?php foreach ($halls as $hall): ?>
                    <option value="<?php echo $hall['id']; ?>" <?php echo $hall['id'] == $sched['hall_id'] ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($hall['hall_name']); ?> (Cap: <?php echo $hall['capacity']; ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
              </td>

              <td>
                <select name="period_id" class="form-control" style="width: 220px; padding: 0.4rem;">
                  <?php foreach ($periods as $period): ?>
                    <option value="<?php echo $period['id']; ?>" <?php echo $period['id'] == $sched['period_id'] ? 'selected' : ''; ?>>
                      <?php echo date('M d', strtotime($period['exam_date'])) . ' - ' . htmlspecialchars($period['period_name']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </td>

              <td>
                <span class="badge <?php echo $sched['status'] == 'published' ? 'badge-published' : 'badge-draft'; ?>">
                  <?php echo ucfirst($sched['status']); ?>
                </span>
              </td>

              <td>
                <button type="submit" name="update_schedule" class="btn btn-primary" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;">Save</button>
                <a href="edit_schedule.php?delete=<?php echo $sched['id']; ?>" onclick="return confirmDelete('Remove this schedule entry?');" class="btn btn-danger" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;">Remove</a>
              </td>
            </form>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">No schedules to edit. Please run the timetable generator first.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once '../includes/footer.php'; ?>
