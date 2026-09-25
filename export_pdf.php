<?php
// export_pdf.php - Printable PDF Document View
require_once 'includes/db.php';

// Fetch all published timetable data
$query = "
  SELECT c.course_code, c.course_title, c.department, c.student_count,
         h.hall_name, h.building, h.capacity,
         p.period_name, p.exam_date, p.start_time, p.end_time
  FROM timetable t
  JOIN courses c ON t.course_id = c.id
  JOIN halls h ON t.hall_id = h.id
  JOIN periods p ON t.period_id = p.id
  WHERE t.status = 'published'
  ORDER BY p.exam_date ASC, p.start_time ASC, c.course_code ASC
";
$stmt = $pdo->prepare($query);
$stmt->execute();
$schedules = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Official Exam Timetable PDF Export</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 20px;
      color: #111;
      background: #fff;
    }
    .header-pdf {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 15px;
      border-bottom: 2px solid #1d4ed8;
      padding-bottom: 15px;
      margin-bottom: 20px;
      text-align: center;
    }
    .logo-pdf {
      height: 60px;
      width: 60px;
      object-fit: contain;
    }
    .header-pdf h1 {
      margin: 0 0 5px 0;
      font-size: 22px;
      color: #1e3a8a;
    }
    .header-pdf p {
      margin: 0;
      font-size: 13px;
      color: #64748b;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
      font-size: 13px;
    }
    th, td {
      border: 1px solid #cbd5e1;
      padding: 10px 12px;
      text-align: left;
    }
    th {
      background-color: #f1f5f9;
      color: #1e293b;
      font-weight: bold;
    }
    tr:nth-child(even) {
      background-color: #f8fafc;
    }
    .btn-print {
      background: #1d4ed8;
      color: white;
      border: none;
      padding: 10px 20px;
      font-size: 14px;
      cursor: pointer;
      border-radius: 4px;
      margin-bottom: 15px;
      font-weight: bold;
    }
    @media print {
      .btn-print { display: none; }
      body { margin: 0; }
    }
  </style>
</head>
<body>

  <button onclick="window.print()" class="btn-print">🖨️ Save as PDF / Print Document</button>

  <div class="header-pdf">
    <img src="images/logo.png" alt="IMT/UNN Logo" class="logo-pdf">
    <div>
      <h1>INSTITUTE OF MANAGEMENT AND TECHNOLOGY (IMT), ENUGU</h1>
      <p style="font-size: 14px; font-weight: bold; color: #1d4ed8; margin-bottom: 3px;">In Affiliation With University of Nigeria, Nsukka (UNN)</p>
      <p>OFFICIAL EXAMINATION TIMETABLE | Generated on: <?php echo date('F d, Y h:i A'); ?></p>
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Date</th>
        <th>Time & Session</th>
        <th>Course Code & Title</th>
        <th>Department</th>
        <th>Hall / Location</th>
        <th>Capacity</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($schedules)): ?>
        <?php foreach ($schedules as $index => $row): ?>
          <tr>
            <td><?php echo $index + 1; ?></td>
            <td><strong><?php echo date('d-M-Y', strtotime($row['exam_date'])); ?></strong></td>
            <td>
              <?php echo htmlspecialchars($row['period_name']); ?><br>
              <small>(<?php echo date('h:i A', strtotime($row['start_time'])) . ' - ' . date('h:i A', strtotime($row['end_time'])); ?>)</small>
            </td>
            <td>
              <strong style="color: #1d4ed8;"><?php echo htmlspecialchars($row['course_code']); ?></strong><br>
              <?php echo htmlspecialchars($row['course_title']); ?>
            </td>
            <td><?php echo htmlspecialchars($row['department']); ?></td>
            <td><?php echo htmlspecialchars($row['hall_name']); ?> (<?php echo htmlspecialchars($row['building']); ?>)</td>
            <td><?php echo $row['student_count']; ?> / <?php echo $row['capacity']; ?> seats</td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="7" style="text-align: center;">No published timetable data available.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <script>
    // Auto prompt print dialog if opened in export mode
    if (window.location.search.includes('autoprint=true')) {
      window.onload = function() { window.print(); }
    }
  </script>

</body>
</html>
