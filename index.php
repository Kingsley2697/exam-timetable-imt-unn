<?php
// index.php - Public Landing Page
require_once 'includes/db.php';
require_once 'includes/header.php';

// Fetch quick statistics
$hallsCount = $pdo->query("SELECT COUNT(*) FROM halls WHERE status='active'")->fetchColumn();
$coursesCount = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$periodsCount = $pdo->query("SELECT COUNT(*) FROM periods")->fetchColumn();
$publishedCount = $pdo->query("SELECT COUNT(*) FROM timetable WHERE status='published'")->fetchColumn();
?>

<div class="hero-section">
  <h1 class="hero-title">Automated Examination Timetable Scheduler</h1>
  <p class="hero-subtitle">Smart collision-free scheduling for academic examinations, hall allocations, and time slot management.</p>
  <div class="button-group" style="justify-content: center; margin-top: 1rem;">
    <a href="view_timetable.php" class="btn btn-primary">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
      View Official Timetable
    </a>
    <a href="admin/login.php" class="btn btn-secondary">Admin Portal</a>
  </div>
</div>

<div class="dashboard-grid">
  <div class="stat-card">
    <div class="stat-icon halls">🏛️</div>
    <div class="stat-details">
      <h4>Active Halls</h4>
      <div class="stat-number"><?php echo $hallsCount; ?></div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon courses">📚</div>
    <div class="stat-details">
      <h4>Registered Courses</h4>
      <div class="stat-number"><?php echo $coursesCount; ?></div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon periods">⏰</div>
    <div class="stat-details">
      <h4>Time Slots</h4>
      <div class="stat-number"><?php echo $periodsCount; ?></div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon">📅</div>
    <div class="stat-details">
      <h4>Published Schedules</h4>
      <div class="stat-number"><?php echo $publishedCount; ?></div>
    </div>
  </div>
</div>

<div class="form-card" style="max-width: 100%; text-align: center;">
  <h2>Recent Exam Schedule Preview</h2>
  <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Below is the latest published examination timetable for all departments.</p>

  <?php
  $stmt = $pdo->prepare("
    SELECT t.id, c.course_code, c.course_title, c.department, h.hall_name, h.building, p.period_name, p.exam_date, p.start_time, p.end_time
    FROM timetable t
    JOIN courses c ON t.course_id = c.id
    JOIN halls h ON t.hall_id = h.id
    JOIN periods p ON t.period_id = p.id
    WHERE t.status = 'published'
    ORDER BY p.exam_date ASC, p.start_time ASC
    LIMIT 5
  ");
  $stmt->execute();
  $recentSchedules = $stmt->fetchAll();
  ?>

  <?php if (!empty($recentSchedules)): ?>
    <div class="table-responsive">
      <table class="custom-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Time Slot</th>
            <th>Course</th>
            <th>Department</th>
            <th>Hall / Venue</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentSchedules as $sched): ?>
            <tr>
              <td><strong><?php echo date('M d, Y', strtotime($sched['exam_date'])); ?></strong></td>
              <td><?php echo date('h:i A', strtotime($sched['start_time'])) . ' - ' . date('h:i A', strtotime($sched['end_time'])); ?></td>
              <td>
                <div style="font-weight: 600; color: var(--accent-primary);"><?php echo htmlspecialchars($sched['course_code']); ?></div>
                <div style="font-size: 0.85rem; color: var(--text-muted);"><?php echo htmlspecialchars($sched['course_title']); ?></div>
              </td>
              <td><?php echo htmlspecialchars($sched['department']); ?></td>
              <td><strong><?php echo htmlspecialchars($sched['hall_name']); ?></strong> (<?php echo htmlspecialchars($sched['building']); ?>)</td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div style="margin-top: 1.5rem;">
      <a href="view_timetable.php" class="btn btn-secondary">See Full Schedule & Export PDF &rarr;</a>
    </div>
  <?php else: ?>
    <div class="alert alert-info">
      No published timetable schedules available at the moment. Please check back later or log in as administrator to generate schedules.
    </div>
  <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
