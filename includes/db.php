<?php
// includes/db.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$db   = 'exam_timetable_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$pdo = null;

// 1. Try MySQL Connection if extension is available
if (extension_loaded('pdo_mysql')) {
    try {
        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (Exception $e) {
        $pdo = null;
    }
}

// 2. Fallback to SQLite zero-config database
if (!$pdo) {
    try {
        $sqliteFile = __DIR__ . '/../exam_timetable.sqlite';
        $pdo = new PDO("sqlite:" . $sqliteFile, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
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
                course_id INTEGER NOT NULL UNIQUE,
                hall_id INTEGER NOT NULL,
                period_id INTEGER NOT NULL,
                status TEXT DEFAULT 'draft',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE(hall_id, period_id)
            );
        ");

        // Check if admin user exists, insert if not
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        if ($stmt->fetchColumn() == 0) {
            $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $pdo->exec("INSERT INTO users (username, password, full_name, email) VALUES ('admin', '$hashedPassword', 'System Administrator', 'admin@example.com')");
            
            // Seed 10 Active Examination Halls
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
                
            // Seed Exam Periods (3 Hours Per Paper, 2 Papers Per Day: Morning 9:00 AM - 12:00 PM and Night 5:00 PM - 8:00 PM)
            $pdo->exec("INSERT INTO periods (period_name, exam_date, start_time, end_time) VALUES 
                ('Morning Session (3 Hours)', '2026-10-12', '09:00:00', '12:00:00'),
                ('Night Session (3 Hours)', '2026-10-12', '17:00:00', '20:00:00'),
                ('Morning Session (3 Hours)', '2026-10-13', '09:00:00', '12:00:00'),
                ('Night Session (3 Hours)', '2026-10-13', '17:00:00', '20:00:00'),
                ('Morning Session (3 Hours)', '2026-10-14', '09:00:00', '12:00:00'),
                ('Night Session (3 Hours)', '2026-10-14', '17:00:00', '20:00:00'),
                ('Morning Session (3 Hours)', '2026-10-15', '09:00:00', '12:00:00'),
                ('Night Session (3 Hours)', '2026-10-15', '17:00:00', '20:00:00')");
                
            // Seed 8 Registered Academic Courses
            $pdo->exec("INSERT INTO courses (course_code, course_title, department, student_count) VALUES 
                ('CS101', 'Introduction to Computer Science', 'Computer Science', 210),
                ('MTH201', 'Linear Algebra & Calculus', 'Mathematics', 150),
                ('PHY102', 'General Physics II', 'Physics', 75),
                ('ENG101', 'Technical Communication', 'Humanities', 110),
                ('CHM103', 'Organic Chemistry Basics', 'Chemistry', 65),
                ('STA111', 'Statistics for Engineers', 'Mathematics', 130),
                ('ECE204', 'Electrical Circuit Theory', 'Electrical Engineering', 95),
                ('GST102', 'Use of English & Communication', 'General Studies', 240)");
        }
    } catch (Exception $e2) {
        die("Database Connection Error: " . $e2->getMessage());
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
