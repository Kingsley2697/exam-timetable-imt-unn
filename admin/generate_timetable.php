<?php
// admin/generate_timetable.php - Automated Collision-Free Timetable Generator Engine
require_once '../includes/db.php';
checkAuth();

$msg = '';
$error = '';
$generationReport = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate'])) {
    $clearExisting = isset($_POST['clear_existing']);

    try {
        $pdo->beginTransaction();

        if ($clearExisting) {
            $pdo->exec("DELETE FROM timetable");
        }

        // Fetch courses without schedule
        $courses = $pdo->query("
            SELECT c.* FROM courses c 
            LEFT JOIN timetable t ON c.id = t.course_id 
            WHERE t.id IS NULL 
            ORDER BY c.student_count DESC
        ")->fetchAll();

        // Fetch available halls ordered by capacity
        $halls = $pdo->query("SELECT * FROM halls WHERE status='active' ORDER BY capacity DESC")->fetchAll();

        // Fetch available time slots (periods)
        $periods = $pdo->query("SELECT * FROM periods ORDER BY exam_date ASC, start_time ASC")->fetchAll();

        if (empty($courses)) {
            $msg = 'All courses are already scheduled! Toggle "Clear Existing Schedule" if you wish to re-generate from scratch.';
            $pdo->commit();
        } elseif (empty($halls) || empty($periods)) {
            $error = 'Generation requires at least one active examination hall and period time slot.';
            $pdo->rollBack();
        } else {
            // Fetch occupied (hall_id, period_id) pairs
            $occupied = [];
            $occStmt = $pdo->query("SELECT hall_id, period_id FROM timetable");
            while ($row = $occStmt->fetch()) {
                $occupied[$row['hall_id'] . '_' . $row['period_id']] = true;
            }

            $assignedCount = 0;

            foreach ($courses as $course) {
                $assigned = false;

                // Match with appropriate period and hall
                foreach ($periods as $period) {
                    if ($assigned) break;

                    foreach ($halls as $hall) {
                        // Check hall capacity constraint
                        if ($hall['capacity'] < $course['student_count']) {
                            continue; // Hall too small
                        }

                        $slotKey = $hall['id'] . '_' . $period['id'];

                        // Check double booking constraint
                        if (!isset($occupied[$slotKey])) {
                            // Assign schedule
                            $insStmt = $pdo->prepare("INSERT INTO timetable (course_id, hall_id, period_id, status) VALUES (?, ?, ?, 'draft')");
                            $insStmt->execute([$course['id'], $hall['id'], $period['id']]);

                            $occupied[$slotKey] = true;
                            $assigned = true;
                            $assignedCount++;
                            
                            $generationReport[] = [
                                'success' => true,
                                'course' => $course['course_code'] . ' - ' . $course['course_title'],
                                'details' => "Assigned to {$hall['hall_name']} for {$period['period_name']} on " . date('d-M-Y', strtotime($period['exam_date']))
                            ];
                            break;
                        }
                    }
                }

                if (!$assigned) {
                    $generationReport[] = [
                        'success' => false,
                        'course' => $course['course_code'] . ' - ' . $course['course_title'],
                        'details' => "Unassigned (No available hall with capacity >= {$course['student_count']} seats or all time slots full)."
                    ];
                }
            }

            $pdo->commit();
            $msg = "Timetable Generation Completed! Successfully scheduled $assignedCount course(s).";
        }

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error during generation: " . $e->getMessage();
    }
}

// Fetch current draft/scheduled items
$currentSchedule = $pdo->query("
    SELECT t.id, t.status, c.course_code, c.course_title, c.student_count,
           h.hall_name, h.capacity, p.period_name, p.exam_date, p.start_time, p.end_time
    FROM timetable t
    JOIN courses c ON t.course_id = c.id
    JOIN halls h ON t.hall_id = h.id
    JOIN periods p ON t.period_id = p.id
    ORDER BY p.exam_date ASC, p.start_time ASC
")->fetchAll();

require_once '../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <div>
    <h2>⚡ Automated Timetable Schedule Generator</h2>
    <p style="color: var(--text-muted);">Runs collision detection algorithm matching enrollment size to hall capacities and open period slots.</p>
  </div>
  <a href="dashboard.php" class="btn btn-secondary">&larr; Back to Dashboard</a>
</div>

<?php if ($msg): ?>
  <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>
<?php if ($error): ?>
  <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="form-card" style="max-width: 100%; margin-bottom: 2rem;">
  <form action="generate_timetable.php" method="POST" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
    <div>
      <h3 style="margin-bottom: 0.25rem;">Run Generator Algorithm</h3>
      <p style="color: var(--text-muted); font-size: 0.9rem;">Generate schedule allocations for all unscheduled or all registered courses.</p>
    </div>
    
    <div style="display: flex; align-items: center; gap: 1.5rem;">
      <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
        <input type="checkbox" name="clear_existing" value="1" checked style="width: 18px; height: 18px;">
        <span>Clear Existing Draft/Schedule First</span>
      </label>
      <button type="submit" name="generate" class="btn btn-primary" style="padding: 0.8rem 2rem;">
        ⚙️ Execute Auto-Scheduler
      </button>
    </div>
  </form>
</div>

<?php if (!empty($generationReport)): ?>
  <div style="margin-bottom: 2rem;">
    <h3>Algorithm Log & Allocation Summary</h3>
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1rem; max-height: 250px; overflow-y: auto;">
      <?php foreach ($generationReport as $log): ?>
        <div style="padding: 0.4rem 0; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 0.9rem;">
          <?php if ($log['success']): ?>
            <span style="color: var(--accent-success);">[SUCCESS]</span>
          <?php else: ?>
            <span style="color: var(--accent-danger);">[UNASSIGNED]</span>
          <?php endif; ?>
          <strong><?php echo htmlspecialchars($log['course']); ?></strong> &mdash; <?php echo htmlspecialchars($log['details']); ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>

<h3>Current Timetable Allocations</h3>
<div class="table-responsive" style="margin-top: 1rem;">
  <table class="custom-table">
    <thead>
      <tr>
        <th>Date</th>
        <th>Time Slot</th>
        <th>Course Code & Title</th>
        <th>Venue / Capacity</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($currentSchedule)): ?>
        <?php foreach ($currentSchedule as $row): ?>
          <tr>
            <td><strong><?php echo date('M d, Y', strtotime($row['exam_date'])); ?></strong></td>
            <td><?php echo htmlspecialchars($row['period_name']); ?> (<?php echo date('h:i A', strtotime($row['start_time'])); ?>)</td>
            <td>
              <strong style="color: var(--accent-primary);"><?php echo htmlspecialchars($row['course_code']); ?></strong>
              <div style="font-size: 0.85rem; color: var(--text-muted);"><?php echo htmlspecialchars($row['course_title']); ?> (<?php echo $row['student_count']; ?> students)</div>
            </td>
            <td><strong><?php echo htmlspecialchars($row['hall_name']); ?></strong> (Cap: <?php echo $row['capacity']; ?>)</td>
            <td>
              <span class="badge <?php echo $row['status'] == 'published' ? 'badge-published' : 'badge-draft'; ?>">
                <?php echo ucfirst($row['status']); ?>
              </span>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">
            No timetable entries generated yet. Click "Execute Auto-Scheduler" above to run automatic allocation.
          </td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once '../includes/footer.php'; ?>
