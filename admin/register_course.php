<?php
// admin/register_course.php - Register and Manage Courses
require_once '../includes/db.php';
checkAuth();

$msg = '';
$error = '';

// Handle Course Registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course'])) {
    $course_code = strtoupper(trim($_POST['course_code'] ?? ''));
    $course_title = trim($_POST['course_title'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $student_count = intval($_POST['student_count'] ?? 0);

    if (empty($course_code) || empty($course_title) || empty($department) || $student_count <= 0) {
        $error = 'All fields including a valid student enrollment count (> 0) are required.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO courses (course_code, course_title, department, student_count) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$course_code, $course_title, $department, $student_count])) {
                $msg = 'Course registered successfully!';
            }
        } catch (PDOException $e) {
            $error = 'Error registering course. Code may already exist.';
        }
    }
}

// Handle Delete Course
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
    if ($stmt->execute([$delete_id])) {
        $msg = 'Course deleted successfully.';
    }
}

// Fetch all courses
$courses = $pdo->query("SELECT * FROM courses ORDER BY course_code ASC")->fetchAll();

require_once '../includes/header.php';
?>

<div class="page-header">
  <h2>📚 Course Registry & Enrollment</h2>
  <a href="dashboard.php" class="btn btn-secondary">&larr; Back to Dashboard</a>
</div>

<?php if ($msg): ?>
  <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>
<?php if ($error): ?>
  <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="management-grid">
  <!-- Add Course Form -->
  <div class="form-card" style="margin: 0;">
    <div class="form-header">
      <h3>Register Academic Course</h3>
    </div>
    <form action="register_course.php" method="POST">
      <div class="form-group">
        <label for="course_code">Course Code</label>
        <input type="text" id="course_code" name="course_code" class="form-control" placeholder="e.g. CS101" required>
      </div>

      <div class="form-group">
        <label for="course_title">Course Title</label>
        <input type="text" id="course_title" name="course_title" class="form-control" placeholder="e.g. Introduction to Programming" required>
      </div>

      <div class="form-group">
        <label for="department">Department</label>
        <input type="text" id="department" name="department" class="form-control" placeholder="e.g. Computer Science" required>
      </div>

      <div class="form-group">
        <label for="student_count">Enrolled Students Count</label>
        <input type="number" id="student_count" name="student_count" class="form-control" placeholder="e.g. 120" min="1" required>
      </div>

      <button type="submit" name="add_course" class="btn btn-primary" style="width: 100%;">+ Save Course</button>
    </form>
  </div>

  <!-- Course List Table -->
  <div class="table-responsive">
    <table class="custom-table">
      <thead>
        <tr>
          <th>Course Code</th>
          <th>Title</th>
          <th>Department</th>
          <th>Enrolled Students</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($courses)): ?>
          <?php foreach ($courses as $c): ?>
            <tr>
              <td><strong style="color: var(--accent-primary);"><?php echo htmlspecialchars($c['course_code']); ?></strong></td>
              <td><?php echo htmlspecialchars($c['course_title']); ?></td>
              <td><?php echo htmlspecialchars($c['department']); ?></td>
              <td><span style="font-weight: 600;"><?php echo $c['student_count']; ?> students</span></td>
              <td>
                <a href="register_course.php?delete=<?php echo $c['id']; ?>" onclick="return confirmDelete('Delete this course?');" class="btn btn-danger" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="5" style="text-align: center; color: var(--text-muted);">No courses registered yet.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
