<?php
// admin/generate_timetable.php - Automated & Manual Timetable Generator Engine
require_once '../includes/db.php';
checkAuth();

$msg = '';
$error = '';
$generationReport = [];

// Handle Single Manual Course Assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_single'])) {
    $course_id = intval($_POST['course_id'] ?? 0);
    $hall_id = intval($_POST['hall_id'] ?? 0);
    $period_id = intval($_POST['period_id'] ?? 0);

    if (!$course_id || !$hall_id || !$period_id) {
        $error = 'Please select a course, examination hall, and time slot session.';
    } else {
        // Check if course is already scheduled
        $chkCourse = $pdo->prepare("SELECT id FROM timetable WHERE course_id = ?");
        $chkCourse->execute([$course_id]);

        // Check if hall is occupied in that period
        $chkHall = $pdo->prepare("SELECT id FROM timetable WHERE hall_id = ? AND period_id = ?");
        $chkHall->execute([$hall_id, $period_id]);

        if ($chkCourse->fetch()) {
            $error = 'This course is already scheduled! You can edit or remove it from the Edit Schedule menu.';
        } elseif ($chkHall->fetch()) {
            $error = 'Conflict detected! The selected hall is already occupied during this time slot session.';
        } else {
            $ins = $pdo->prepare("INSERT INTO timetable (course_id, hall_id, period_id, status) VALUES (?, ?, ?, 'draft')");
            if ($ins->execute([$course_id, $hall_id, $period_id])) {
                $msg = 'Course scheduled successfully!';
            } else {
                $error = 'Failed to schedule course.';
            }
        }
    }
}

// Handle Auto-Scheduler Generation
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
            $msg = 'All courses are already scheduled! Uncheck "Clear Existing Schedule" or click below to manage entries.';
            $pdo->commit();
        } elseif (empty($halls) || empty($periods)) {
            $error = 'Generation requires active examination halls and period time slots.';
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

// Fetch all unscheduled courses for single assignment dropdown
$unscheduledCourses = $pdo->query("
    SELECT c.* FROM courses c 
    LEFT JOIN timetable t ON c.id = t.course_id 
    WHERE t.id IS NULL 
    ORDER BY c.department ASC, c.course_code ASC
")->fetchAll();

$allHalls = $pdo->query("SELECT * FROM halls WHERE status='active' ORDER BY hall_name ASC")->fetchAll();
$allPeriods = $pdo->query("SELECT * FROM periods ORDER BY exam_date ASC, start_time ASC")->fetchAll();

// Fetch current draft/scheduled items
$currentSchedule = $pdo->query("
    SELECT t.id, t.status, c.course_code, c.course_title, c.department, c.student_count,
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
    <h2>⚡ Timetable Scheduling & Allocation Generator</h2>
    <p style="color: var(--text-muted);">Schedule individual courses one-by-one by choice, or run auto-scheduler for all courses.</p>
  </div>
  <a href="dashboard.php" class="btn btn-secondary">&larr; Back to Dashboard</a>
</div>

<?php if ($msg): ?>
  <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>
<?php if ($error): ?>
  <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
  <!-- Section 1: Single Course Choice Assignment -->
  <div class="form-card" style="margin: 0; max-width: 100%;">
    <div class="form-header">
      <h3>🎯 Schedule Course One-by-One</h3>
      <p style="color: var(--text-muted); font-size: 0.85rem;">Select a course from any department and assign it to a hall & time slot by choice.</p>
    </div>

    <form action="generate_timetable.php" method="POST">
      <div class="form-group">
        <label for="course_id">Select Course to Schedule</label>
        <select name="course_id" id="course_id" class="form-control" required>
          <option value="">-- Choose Course (Department & Code) --</option>
          <?php 
            $currDept = '';
            foreach ($unscheduledCourses as $uc): 
              if ($currDept !== $uc['department']) {
                if ($currDept !== '') echo '</optgroup>';
                $currDept = $uc['department'];
                echo '<optgroup label="' . htmlspecialchars($currDept) . '">';
              }
          ?>
            <option value="<?php echo $uc['id']; ?>">
              <?php echo htmlspecialchars($uc['course_code'] . ' - ' . $uc['course_title'] . ' (' . $uc['student_count'] . ' students)'); ?>
            </option>
          <?php endforeach; if ($currDept !== '') echo '</optgroup>'; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="hall_id">Select Examination Hall</label>
        <select name="hall_id" id="hall_id" class="form-control" required>
          <option value="">-- Choose Hall & Venue --</option>
          <?php foreach ($allHalls as $h): ?>
            <option value="<?php echo $h['id']; ?>">
              <?php echo htmlspecialchars($h['hall_name'] . ' (Capacity: ' . $h['capacity'] . ' seats)'); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="period_id">Select Exam Session Slot (Date & Session)</label>
        <select name="period_id" id="period_id" class="form-control" required>
          <option value="">-- Choose Exam Date & Time Session --</option>
          <?php foreach ($allPeriods as $p): ?>
            <option value="<?php echo $p['id']; ?>">
              <?php echo date('D, M d, Y', strtotime($p['exam_date'])) . ' - ' . htmlspecialchars($p['period_name']) . ' (' . date('h:i A', strtotime($p['start_time'])) . ' - ' . date('h:i A', strtotime($p['end_time'])) . ')'; ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <button type="submit" name="assign_single" class="btn btn-primary" style="width: 100%;">
        ➕ Assign Selected Course Schedule
      </button>
    </form>
  </div>

  <!-- Section 2: Bulk Auto-Scheduler -->
  <div class="form-card" style="margin: 0; max-width: 100%;">
    <div class="form-header">
      <h3>⚙️ Bulk Auto-Scheduler Algorithm</h3>
      <p style="color: var(--text-muted); font-size: 0.85rem;">Automatically matches all remaining unassigned courses with capacity & time slots.</p>
    </div>

    <form action="generate_timetable.php" method="POST" style="height: 80%; display: flex; flex-direction: column; justify-content: space-between;">
      <div class="form-group">
        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; margin-top: 1rem;">
          <input type="checkbox" name="clear_existing" value="1" style="width: 18px; height: 18px;">
          <span>Clear Existing Schedule Before Auto-Generating</span>
        </label>
        <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0.5rem;">
          If unchecked, auto-scheduler will only schedule remaining unassigned courses.
        </p>
      </div>

      <button type="submit" name="generate" class="btn btn-success" style="width: 100%; padding: 0.85rem;">
        ⚡ Execute Auto-Scheduler for All Courses
      </button>
    </form>
  </div>
</div>

<?php if (!empty($generationReport)): ?>
  <div style="margin-bottom: 2rem;">
    <h3>Algorithm Log & Allocation Summary</h3>
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1rem; max-height: 250px; overflow-y: auto;">
      <?php foreach ($generationReport as $log): ?>
        <div style="padding: 0.4rem 0; border-bottom: 1px solid rgba(0,0,0,0.05); font-size: 0.9rem;">
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

<h3>Current Scheduled Timetable (Total: <?php echo count($currentSchedule); ?>)</h3>
<div class="table-responsive" style="margin-top: 1rem;">
  <table class="custom-table">
    <thead>
      <tr>
        <th>Date</th>
        <th>Time Session</th>
        <th>Course Code & Title</th>
        <th>Department</th>
        <th>Venue / Capacity</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($currentSchedule)): ?>
        <?php foreach ($currentSchedule as $row): ?>
          <tr>
            <td><strong><?php echo date('M d, Y', strtotime($row['exam_date'])); ?></strong></td>
            <td><?php echo htmlspecialchars($row['period_name']); ?> <br><small>(<?php echo date('h:i A', strtotime($row['start_time'])) . ' - ' . date('h:i A', strtotime($row['end_time'])); ?>)</small></td>
            <td>
              <strong style="color: var(--accent-primary);"><?php echo htmlspecialchars($row['course_code']); ?></strong>
              <div style="font-size: 0.85rem; color: var(--text-muted);"><?php echo htmlspecialchars($row['course_title']); ?> (<?php echo $row['student_count']; ?> students)</div>
            </td>
            <td><?php echo htmlspecialchars($row['department']); ?></td>
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
          <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">
            No timetable entries generated yet. Select a course above to schedule manually or run the Auto-Scheduler.
          </td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once '../includes/footer.php'; ?>
