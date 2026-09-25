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

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // If MySQL connection fails, attempt fallback SQLite database for instant zero-config testing
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
            
            // Insert sample data
            $pdo->exec("INSERT INTO halls (hall_name, building, capacity, status) VALUES 
                ('Main Auditorium', 'Block A - Ground Floor', 250, 'active'),
                ('Hall 101', 'Science Complex', 80, 'active'),
                ('Hall 102', 'Science Complex', 80, 'active'),
                ('ICT Lab 1', 'Technology Wing', 60, 'active'),
                ('E-Library Center', 'Central Library', 120, 'active')");
                
            $pdo->exec("INSERT INTO periods (period_name, exam_date, start_time, end_time) VALUES 
                ('Morning Session 1', '2026-10-12', '09:00:00', '11:00:00'),
                ('Afternoon Session 1', '2026-10-12', '13:00:00', '15:00:00'),
                ('Morning Session 2', '2026-10-13', '09:00:00', '11:00:00'),
                ('Afternoon Session 2', '2026-10-13', '13:00:00', '15:00:00'),
                ('Morning Session 3', '2026-10-14', '09:00:00', '11:00:00')");
                
            $pdo->exec("INSERT INTO courses (course_code, course_title, department, student_count) VALUES 
                ('CS101', 'Introduction to Computer Science', 'Computer Science', 210),
                ('MTH201', 'Linear Algebra & Calculus', 'Mathematics', 150),
                ('PHY102', 'General Physics II', 'Physics', 75),
                ('ENG101', 'Technical Communication', 'Humanities', 110),
                ('CHM103', 'Organic Chemistry Basics', 'Chemistry', 55)");
        }
    } catch (PDOException $e2) {
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
