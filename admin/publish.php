<?php
// admin/publish.php - Timetable Publishing Control
require_once '../includes/db.php';
checkAuth();

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action_publish'])) {
        $pdo->exec("UPDATE timetable SET status = 'published'");
        $msg = 'Timetable has been PUBLISHED! Students and faculty can now view the official schedule.';
    } elseif (isset($_POST['action_unpublish'])) {
        $pdo->exec("UPDATE timetable SET status = 'draft'");
        $msg = 'Timetable has been set to DRAFT mode (hidden from public view).';
    }
}

// Fetch status summary
$publishedCount = $pdo->query("SELECT COUNT(*) FROM timetable WHERE status='published'")->fetchColumn();
$draftCount = $pdo->query("SELECT COUNT(*) FROM timetable WHERE status='draft'")->fetchColumn();
$totalCount = $pdo->query("SELECT COUNT(*) FROM timetable")->fetchColumn();

require_once '../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
  <h2>🚀 Timetable Publishing Center</h2>
  <a href="dashboard.php" class="btn btn-secondary">&larr; Back to Dashboard</a>
</div>

<?php if ($msg): ?>
  <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>

<div class="form-card" style="max-width: 600px; text-align: center;">
  <h3>Current Status Overview</h3>
  
  <div style="display: flex; justify-content: center; gap: 2rem; margin: 1.5rem 0;">
    <div>
      <div style="font-size: 2rem; font-weight: 700; color: var(--accent-success);"><?php echo $publishedCount; ?></div>
      <div style="font-size: 0.85rem; color: var(--text-muted);">Published Entries</div>
    </div>
    <div>
      <div style="font-size: 2rem; font-weight: 700; color: var(--accent-warning);"><?php echo $draftCount; ?></div>
      <div style="font-size: 0.85rem; color: var(--text-muted);">Draft Entries</div>
    </div>
    <div>
      <div style="font-size: 2rem; font-weight: 700; color: #ffffff;"><?php echo $totalCount; ?></div>
      <div style="font-size: 0.85rem; color: var(--text-muted);">Total Allocated</div>
    </div>
  </div>

  <form action="publish.php" method="POST" style="display: flex; justify-content: center; gap: 1rem; margin-top: 2rem;">
    <?php if ($draftCount > 0 || $totalCount > 0): ?>
      <button type="submit" name="action_publish" class="btn btn-success" style="padding: 0.8rem 1.8rem;">
        🌐 Publish Timetable Now
      </button>
    <?php endif; ?>
    
    <?php if ($publishedCount > 0): ?>
      <button type="submit" name="action_unpublish" class="btn btn-warning" style="padding: 0.8rem 1.8rem;">
        🔒 Revert All to Draft
      </button>
    <?php endif; ?>
  </form>
  
  <div style="margin-top: 1.5rem;">
    <a href="../view_timetable.php" target="_blank" style="font-size: 0.9rem; color: var(--accent-primary);">Preview Public View &rarr;</a>
  </div>
</div>

<?php require_once '../includes/footer.php'; ?>
