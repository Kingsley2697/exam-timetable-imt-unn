-- =============================================================
-- IMT-UNN EXAM TIMETABLE SCHEDULING SYSTEM
-- 000WebHost Deployment SQL Script
-- Import this file via phpMyAdmin on 000WebHost
-- =============================================================

-- Step 1: Create & select the database
-- (000WebHost creates the DB for you — skip CREATE DATABASE
--  and just use the database name they give you in phpMyAdmin)

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =============================================================
-- TABLE: users (Admin accounts)
-- =============================================================
CREATE TABLE IF NOT EXISTS `users` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `username`   VARCHAR(50)  NOT NULL UNIQUE,
  `password`   VARCHAR(255) NOT NULL,
  `full_name`  VARCHAR(100) NOT NULL,
  `email`      VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- TABLE: halls (Examination venues)
-- =============================================================
CREATE TABLE IF NOT EXISTS `halls` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `hall_name`  VARCHAR(100) NOT NULL,
  `building`   VARCHAR(100) DEFAULT NULL,
  `capacity`   INT NOT NULL DEFAULT 50,
  `status`     ENUM('active','inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- TABLE: periods (Exam date & time slots)
-- =============================================================
CREATE TABLE IF NOT EXISTS `periods` (
  `id`          INT AUTO_INCREMENT PRIMARY KEY,
  `period_name` VARCHAR(100) NOT NULL,
  `exam_date`   DATE NOT NULL,
  `start_time`  TIME NOT NULL,
  `end_time`    TIME NOT NULL,
  `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- TABLE: courses
-- =============================================================
CREATE TABLE IF NOT EXISTS `courses` (
  `id`            INT AUTO_INCREMENT PRIMARY KEY,
  `course_code`   VARCHAR(20)  NOT NULL UNIQUE,
  `course_title`  VARCHAR(150) NOT NULL,
  `department`    VARCHAR(100) NOT NULL,
  `student_count` INT NOT NULL DEFAULT 0,
  `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- TABLE: timetable (Scheduled exam entries)
-- =============================================================
CREATE TABLE IF NOT EXISTS `timetable` (
  `id`        INT AUTO_INCREMENT PRIMARY KEY,
  `course_id` INT NOT NULL,
  `hall_id`   INT NOT NULL,
  `period_id` INT NOT NULL,
  `status`    ENUM('draft','published') DEFAULT 'draft',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`hall_id`)   REFERENCES `halls`(`id`)   ON DELETE CASCADE,
  FOREIGN KEY (`period_id`) REFERENCES `periods`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_hall_period`     (`hall_id`, `period_id`),
  UNIQUE KEY `unique_course_schedule` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- SEED DATA: Default Admin Account
-- Login: admin / admin123
-- =============================================================
INSERT IGNORE INTO `users` (`username`, `password`, `full_name`, `email`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@imt-unn.edu.ng');

-- =============================================================
-- SEED DATA: 10 Active Examination Halls
-- =============================================================
INSERT IGNORE INTO `halls` (`hall_name`, `building`, `capacity`, `status`) VALUES
('Main Auditorium',              'Block A - Ground Floor',  250, 'active'),
('Hall 101',                     'Science Complex',         120, 'active'),
('Hall 102',                     'Science Complex',         120, 'active'),
('Hall 103',                     'Science Complex',         100, 'active'),
('ICT Lab 1',                    'Technology Wing',          80, 'active'),
('ICT Lab 2',                    'Technology Wing',          80, 'active'),
('E-Library Center',             'Central Library',         150, 'active'),
('Multipurpose Hall A',          'Main Campus',             200, 'active'),
('Multipurpose Hall B',          'Main Campus',             200, 'active'),
('Engineering Lecture Theater',  'Engineering Block',       180, 'active');

-- =============================================================
-- SEED DATA: Exam Periods (3 hrs/paper, 2 sessions/day)
-- Morning: 9:00 AM - 12:00 PM | Night: 5:00 PM - 8:00 PM
-- =============================================================
INSERT IGNORE INTO `periods` (`period_name`, `exam_date`, `start_time`, `end_time`) VALUES
('Morning Session (3 Hours)', '2026-10-12', '09:00:00', '12:00:00'),
('Night Session (3 Hours)',   '2026-10-12', '17:00:00', '20:00:00'),
('Morning Session (3 Hours)', '2026-10-13', '09:00:00', '12:00:00'),
('Night Session (3 Hours)',   '2026-10-13', '17:00:00', '20:00:00'),
('Morning Session (3 Hours)', '2026-10-14', '09:00:00', '12:00:00'),
('Night Session (3 Hours)',   '2026-10-14', '17:00:00', '20:00:00'),
('Morning Session (3 Hours)', '2026-10-15', '09:00:00', '12:00:00'),
('Night Session (3 Hours)',   '2026-10-15', '17:00:00', '20:00:00'),
('Morning Session (3 Hours)', '2026-10-16', '09:00:00', '12:00:00'),
('Night Session (3 Hours)',   '2026-10-16', '17:00:00', '20:00:00');

-- =============================================================
-- SEED DATA: 24 Registered Courses (5 Departments)
-- =============================================================
INSERT IGNORE INTO `courses` (`course_code`, `course_title`, `department`, `student_count`) VALUES
-- Computer Science
('CSC101', 'Introduction to Computer Science & Programming', 'Computer Science', 220),
('CSC102', 'Data Structures & Algorithms',                   'Computer Science', 180),
('CSC201', 'Object-Oriented Programming (C++/Java)',         'Computer Science', 160),
('CSC202', 'Database Management Systems',                    'Computer Science', 150),
('CSC301', 'Operating Systems & Systems Programming',        'Computer Science', 140),
('CSC302', 'Software Engineering & System Analysis',         'Computer Science', 130),
('CSC401', 'Artificial Intelligence & Machine Learning',     'Computer Science', 110),
('CSC402', 'Computer Networks & Cybersecurity',              'Computer Science', 125),
-- Marketing
('MKT101', 'Principles of Marketing',                        'Marketing', 175),
('MKT201', 'Consumer Behavior & Market Analysis',            'Marketing', 140),
('MKT301', 'Digital Marketing & E-Commerce',                 'Marketing', 120),
('MKT401', 'Strategic Brand Management',                     'Marketing',  95),
-- Banking and Finance
('BFN101', 'Introduction to Banking & Financial Systems',    'Banking and Finance', 190),
('BFN201', 'Corporate Finance & Investment Analysis',        'Banking and Finance', 150),
('BFN301', 'Financial Institutions & Markets',               'Banking and Finance', 130),
('BFN401', 'International Finance & Risk Management',        'Banking and Finance', 105),
-- Public Administration
('PAD101', 'Elements of Public Administration',              'Public Administration', 200),
('PAD201', 'Administrative Theory & Practice',               'Public Administration', 165),
('PAD301', 'Public Personnel Management',                    'Public Administration', 145),
('PAD401', 'Public Policy Analysis & Implementation',        'Public Administration', 115),
-- Mass Communication
('MAC101', 'Introduction to Mass Communication',             'Mass Communication', 210),
('MAC201', 'Print Media Production & Journalism',            'Mass Communication', 170),
('MAC301', 'Broadcast Media & Radio Production',             'Mass Communication', 140),
('MAC401', 'Media Law, Ethics & Public Relations',           'Mass Communication', 120);

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================
-- DONE! Your database is ready.
-- Admin login: username = admin | password = admin123
-- =============================================================
