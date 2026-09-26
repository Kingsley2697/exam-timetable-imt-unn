<?php
// view_timetable.php - Public & Full Timetable View
require_once 'includes/db.php';
require_once 'includes/header.php';

// Fetch departments for filter dropdown
$departments = $pdo->query("SELECT DISTINCT department FROM courses ORDER BY department ASC")->fetchAll(PDO::FETCH_COLUMN);

// Query all published or all timetables depending on login state
$isLoggedIn = isset($_SESSION['user_id']);
$statusCondition = $isLoggedIn ? "" : "WHERE t.status = 'published'";

$query = "
  SELECT t.id, t.status, c.course_code, c.course_title, c.department, c.student_count,
         h.hall_name, h.building, h.capacity,
         p.period_name, p.exam_date, p.start_time, p.end_time
  FROM timetable t
  JOIN courses c ON t.course_id = c.id
  JOIN halls h ON t.hall_id = h.id
  JOIN periods p ON t.period_id = p.id
  $statusCondition
  ORDER BY p.exam_date ASC, p.start_time ASC, c.course_code ASC
";
$stmt = $pdo->prepare($query);
$stmt->execute();
$schedules = $stmt->fetchAll();
?>

<div class="page-header">
  <div>
    <h2>Official Examination Timetable</h2>
    <p style="color: var(--text-muted);">View examination dates, allotted venues, and time slots.</p>
  </div>
  <div class="page-header-actions no-print">
    <button onclick="printTimetable()" class="btn btn-secondary">
      🖨️ Print Timetable
    </button>
    <a href="export_pdf.php" target="_blank" class="btn btn-success">
      📄 Export PDF / Document
    </a>
  </div>
</div>

<div class="toolbar-section no-print">
  <div class="search-box">
    <input type="text" id="tableSearch" class="form-control" placeholder="Search by course code, title, venue, date...">
  </div>

  <div class="filter-group">
    <label for="deptFilter" style="font-size: 0.9rem; color: var(--text-muted);">Department:</label>
    <select id="deptFilter" class="form-control" style="width: 200px;">
      <option value="">All Departments</option>
      <?php foreach ($departments as $dept): ?>
        <option value="<?php echo htmlspecialchars($dept); ?>"><?php echo htmlspecialchars($dept); ?></option>
      <?php endforeach; ?>
    </select>
  </div>
</div>

<div class="table-responsive">
  <table class="custom-table">
    <thead>
      <tr>
        <th>Date</th>
        <th>Session & Time</th>
        <th>Course Code & Title</th>
        <th>Department</th>
        <th>Exam Hall / Capacity</th>
        <?php if ($isLoggedIn): ?>
          <th>Status</th>
        <?php endif; ?>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($schedules)): ?>
        <?php foreach ($schedules as $row): ?>
          <tr>
            <td><strong><?php echo date('D, M d, Y', strtotime($row['exam_date'])); ?></strong></td>
            <td>
              <div style="font-weight: 500;"><?php echo htmlspecialchars($row['period_name']); ?></div>
              <div style="font-size: 0.85rem; color: var(--text-muted);"><?php echo date('h:i A', strtotime($row['start_time'])) . ' - ' . date('h:i A', strtotime($row['end_time'])); ?></div>
            </td>
            <td>
              <div style="font-weight: 600; color: var(--accent-primary);"><?php echo htmlspecialchars($row['course_code']); ?></div>
              <div style="font-size: 0.9rem;"><?php echo htmlspecialchars($row['course_title']); ?></div>
              <div style="font-size: 0.8rem; color: var(--text-muted);">Enrolled: <?php echo $row['student_count']; ?> students</div>
            </td>
            <td class="dept-col"><?php echo htmlspecialchars($row['department']); ?></td>
            <td>
              <strong><?php echo htmlspecialchars($row['hall_name']); ?></strong>
              <div style="font-size: 0.85rem; color: var(--text-muted);"><?php echo htmlspecialchars($row['building']); ?> (Cap: <?php echo $row['capacity']; ?>)</div>
            </td>
            <?php if ($isLoggedIn): ?>
              <td>
                <span class="badge <?php echo $row['status'] == 'published' ? 'badge-published' : 'badge-draft'; ?>">
                  <?php echo ucfirst($row['status']); ?>
                </span>
              </td>
            <?php endif; ?>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="<?php echo $isLoggedIn ? '6' : '5'; ?>" style="text-align: center; padding: 2rem; color: var(--text-muted);">
            No scheduled exams found. <?php if(!$isLoggedIn) echo 'Ensure timetable has been published by Administrator.'; ?>
          </td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once 'includes/footer.php'; ?>
