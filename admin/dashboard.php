<?php
// admin/dashboard.php - Administrative Control Hub
require_once '../includes/db.php';
checkAuth();

require_once '../includes/header.php';

// Metrics
$hallsCount = $pdo->query("SELECT COUNT(*) FROM halls")->fetchColumn();
$coursesCount = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$periodsCount = $pdo->query("SELECT COUNT(*) FROM periods")->fetchColumn();
$timetableCount = $pdo->query("SELECT COUNT(*) FROM timetable")->fetchColumn();
$publishedCount = $pdo->query("SELECT COUNT(*) FROM timetable WHERE status='published'")->fetchColumn();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
  <div>
    <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?> 👋</h2>
    <p style="color: var(--text-muted);">Exam Timetable Management & Scheduling Dashboard</p>
  </div>
  <div>
    <a href="generate_timetable.php" class="btn btn-primary">⚡ Generate Timetable</a>
    <a href="publish.php" class="btn btn-success">🚀 Publish Control</a>
  </div>
</div>

<!-- Quick Stats Cards -->
<div class="dashboard-grid">
  <div class="stat-card">
    <div class="stat-icon halls">🏛️</div>
    <div class="stat-details">
      <h4>Exam Halls</h4>
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
    <div class="stat-icon">📊</div>
    <div class="stat-details">
      <h4>Status</h4>
      <div class="stat-number" style="font-size: 1.2rem; color: var(--accent-success);">
        <?php echo $publishedCount > 0 ? "$publishedCount Published" : "$timetableCount Drafts"; ?>
      </div>
    </div>
  </div>
</div>

<!-- Admin Quick Navigation Links -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">
  <div class="stat-card" style="flex-direction: column; align-items: flex-start;">
    <h3>🏛️ Hall Management</h3>
    <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0.5rem 0 1rem;">Add new exam halls, set seating capacity, building locations, and activate venues.</p>
    <a href="register_hall.php" class="btn btn-secondary" style="width: 100%;">Manage Halls &rarr;</a>
  </div>

  <div class="stat-card" style="flex-direction: column; align-items: flex-start;">
    <h3>⏰ Exam Time Slots</h3>
    <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0.5rem 0 1rem;">Set up exam session slots, dates, start times, and end times for timetabling.</p>
    <a href="register_period.php" class="btn btn-secondary" style="width: 100%;">Manage Periods &rarr;</a>
  </div>

  <div class="stat-card" style="flex-direction: column; align-items: flex-start;">
    <h3>📚 Course Registry</h3>
    <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0.5rem 0 1rem;">Register academic courses, department categories, and student enrollment totals.</p>
    <a href="register_course.php" class="btn btn-secondary" style="width: 100%;">Manage Courses &rarr;</a>
  </div>

  <div class="stat-card" style="flex-direction: column; align-items: flex-start;">
    <h3>⚙️ Schedule Management</h3>
    <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0.5rem 0 1rem;">Manually adjust generated schedules, resolve hall conflicts, or clear timetable drafts.</p>
    <a href="edit_schedule.php" class="btn btn-secondary" style="width: 100%;">Edit Schedule &rarr;</a>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
