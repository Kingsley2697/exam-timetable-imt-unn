<?php
// includes/db.php
// ============================================================
// DATABASE CONFIGURATION — MySQL (000WebHost / Shared Hosting)
// ============================================================
// After uploading to 000WebHost, update the 4 values below
// with the credentials from your 000WebHost MySQL panel.
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---- UPDATE THESE VALUES ON YOUR HOSTING ----
$host    = 'localhost';           // Usually 'localhost' on 000WebHost
$db      = 'exam_timetable_db';  // Your database name from 000WebHost panel
$user    = 'root';               // Your database username from 000WebHost panel
$pass    = '';                   // Your database password from 000WebHost panel
$charset = 'utf8mb4';
// ---------------------------------------------

$pdo = null;

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, $user, $pass, $options);

} catch (PDOException $e) {
    // ---- Try SQLite fallback (for local XAMPP/WAMP testing) ----
    try {
        $sqliteFile = __DIR__ . '/../exam_timetable.sqlite';
        $pdo = new PDO("sqlite:" . $sqliteFile, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        // Auto-create tables in SQLite if brand new
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL UNIQUE,
                password TEXT NOT NULL,
                full_name TEXT NOT NULL,
                email TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE IF NOT EXISTS halls (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                hall_name TEXT NOT NULL,
                building TEXT DEFAULT NULL,
                capacity INTEGER NOT NULL DEFAULT 50,
                status TEXT DEFAULT 'active',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE IF NOT EXISTS periods (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                period_name TEXT NOT NULL,
                exam_date TEXT NOT NULL,
                start_time TEXT NOT NULL,
                end_time TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE IF NOT EXISTS courses (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                course_code TEXT NOT NULL UNIQUE,
                course_title TEXT NOT NULL,
                department TEXT NOT NULL,
                student_count INTEGER NOT NULL DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
            CREATE TABLE IF NOT EXISTS timetable (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                course_id INTEGER NOT NULL,
                hall_id INTEGER NOT NULL,
                period_id INTEGER NOT NULL,
                status TEXT DEFAULT 'draft',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE(hall_id, period_id),
                UNIQUE(course_id)
            );
        ");

        // Seed default data if empty
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        if ($stmt->fetchColumn() == 0) {
            $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $pdo->exec("INSERT INTO users (username, password, full_name, email)
                        VALUES ('admin', '$hashedPassword', 'System Administrator', 'admin@imt-unn.edu.ng')");

            $pdo->exec("INSERT INTO halls (hall_name, building, capacity, status) VALUES
                ('Main Auditorium', 'Block A - Ground Floor', 250, 'active'),
                ('Hall 101', 'Science Complex', 120, 'active'),
                ('Hall 102', 'Science Complex', 120, 'active'),
                ('Hall 103', 'Science Complex', 100, 'active'),
                ('ICT Lab 1', 'Technology Wing', 80, 'active'),
                ('ICT Lab 2', 'Technology Wing', 80, 'active'),
                ('E-Library Center', 'Central Library', 150, 'active'),
                ('Multipurpose Hall A', 'Main Campus', 200, 'active'),
                ('Multipurpose Hall B', 'Main Campus', 200, 'active'),
                ('Engineering Lecture Theater', 'Engineering Block', 180, 'active')");

            $pdo->exec("INSERT INTO periods (period_name, exam_date, start_time, end_time) VALUES
                ('Morning Session (3 Hours)', '2026-10-12', '09:00:00', '12:00:00'),
                ('Night Session (3 Hours)',   '2026-10-12', '17:00:00', '20:00:00'),
                ('Morning Session (3 Hours)', '2026-10-13', '09:00:00', '12:00:00'),
                ('Night Session (3 Hours)',   '2026-10-13', '17:00:00', '20:00:00'),
                ('Morning Session (3 Hours)', '2026-10-14', '09:00:00', '12:00:00'),
                ('Night Session (3 Hours)',   '2026-10-14', '17:00:00', '20:00:00'),
                ('Morning Session (3 Hours)', '2026-10-15', '09:00:00', '12:00:00'),
                ('Night Session (3 Hours)',   '2026-10-15', '17:00:00', '20:00:00'),
                ('Morning Session (3 Hours)', '2026-10-16', '09:00:00', '12:00:00'),
                ('Night Session (3 Hours)',   '2026-10-16', '17:00:00', '20:00:00')");

            $pdo->exec("INSERT INTO courses (course_code, course_title, department, student_count) VALUES
                ('CSC101', 'Introduction to Computer Science & Programming', 'Computer Science', 220),
                ('CSC102', 'Data Structures & Algorithms', 'Computer Science', 180),
                ('CSC201', 'Object-Oriented Programming (C++/Java)', 'Computer Science', 160),
                ('CSC202', 'Database Management Systems', 'Computer Science', 150),
                ('CSC301', 'Operating Systems & Systems Programming', 'Computer Science', 140),
                ('CSC302', 'Software Engineering & System Analysis', 'Computer Science', 130),
                ('CSC401', 'Artificial Intelligence & Machine Learning', 'Computer Science', 110),
                ('CSC402', 'Computer Networks & Cybersecurity', 'Computer Science', 125),
                ('MKT101', 'Principles of Marketing', 'Marketing', 175),
                ('MKT201', 'Consumer Behavior & Market Analysis', 'Marketing', 140),
                ('MKT301', 'Digital Marketing & E-Commerce', 'Marketing', 120),
                ('MKT401', 'Strategic Brand Management', 'Marketing', 95),
                ('BFN101', 'Introduction to Banking & Financial Systems', 'Banking and Finance', 190),
                ('BFN201', 'Corporate Finance & Investment Analysis', 'Banking and Finance', 150),
                ('BFN301', 'Financial Institutions & Markets', 'Banking and Finance', 130),
                ('BFN401', 'International Finance & Risk Management', 'Banking and Finance', 105),
                ('PAD101', 'Elements of Public Administration', 'Public Administration', 200),
                ('PAD201', 'Administrative Theory & Practice', 'Public Administration', 165),
                ('PAD301', 'Public Personnel Management', 'Public Administration', 145),
                ('PAD401', 'Public Policy Analysis & Implementation', 'Public Administration', 115),
                ('MAC101', 'Introduction to Mass Communication', 'Mass Communication', 210),
                ('MAC201', 'Print Media Production & Journalism', 'Mass Communication', 170),
                ('MAC301', 'Broadcast Media & Radio Production', 'Mass Communication', 140),
                ('MAC401', 'Media Law, Ethics & Public Relations', 'Mass Communication', 120)");
        }

    } catch (Exception $e2) {
        die("<h2 style='font-family:sans-serif;color:red;padding:2rem;'>
            ⚠️ Database Connection Failed<br>
            <small style='font-size:0.9rem;color:#555;'>
            Please update the database credentials in <code>includes/db.php</code>.<br>
            Error: " . htmlspecialchars($e2->getMessage()) . "
            </small></h2>");
    }
}

// Helper authentication check
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}
?>
