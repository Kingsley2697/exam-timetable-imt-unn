-- Exam Timetable Scheduling Database Schema

CREATE DATABASE IF NOT EXISTS `exam_timetable_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `exam_timetable_db`;

-- Table structure for users (Admin)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for examination halls
CREATE TABLE IF NOT EXISTS `halls` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `hall_name` VARCHAR(100) NOT NULL,
  `building` VARCHAR(100) DEFAULT NULL,
  `capacity` INT NOT NULL DEFAULT 50,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for exam periods (Date and Time slots)
CREATE TABLE IF NOT EXISTS `periods` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `period_name` VARCHAR(100) NOT NULL,
  `exam_date` DATE NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for courses
CREATE TABLE IF NOT EXISTS `courses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `course_code` VARCHAR(20) NOT NULL UNIQUE,
  `course_title` VARCHAR(150) NOT NULL,
  `department` VARCHAR(100) NOT NULL,
  `student_count` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for timetable schedule
CREATE TABLE IF NOT EXISTS `timetable` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `course_id` INT NOT NULL,
  `hall_id` INT NOT NULL,
  `period_id` INT NOT NULL,
  `status` ENUM('draft', 'published') DEFAULT 'draft',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`hall_id`) REFERENCES `halls`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`period_id`) REFERENCES `periods`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_hall_period` (`hall_id`, `period_id`),
  UNIQUE KEY `unique_course_schedule` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Default Admin Account (Username: admin, Password: admin123)
INSERT INTO `users` (`username`, `password`, `full_name`, `email`)
VALUES ('admin', '$2y$10$wT0vR3j0x4B3XQvW0Z8q7uWdYvX/Z6qQ5j4f8K9L0M1N2O3P4Q5R6', 'System Administrator', 'admin@example.com')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Seed 10 Active Examination Halls
INSERT INTO `halls` (`hall_name`, `building`, `capacity`, `status`) VALUES
('Main Auditorium', 'Block A - Ground Floor', 250, 'active'),
('Hall 101', 'Science Complex', 120, 'active'),
('Hall 102', 'Science Complex', 120, 'active'),
('Hall 103', 'Science Complex', 100, 'active'),
('ICT Lab 1', 'Technology Wing', 80, 'active'),
('ICT Lab 2', 'Technology Wing', 80, 'active'),
('E-Library Center', 'Central Library', 150, 'active'),
('Multipurpose Hall A', 'Main Campus', 200, 'active'),
('Multipurpose Hall B', 'Main Campus', 200, 'active'),
('Engineering Lecture Theater', 'Engineering Block', 180, 'active')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Seed Exam Periods (3 Hours Per Paper, 2 Papers Per Day: Morning 9:00 AM - 12:00 PM and Night 5:00 PM - 8:00 PM)
INSERT INTO `periods` (`period_name`, `exam_date`, `start_time`, `end_time`) VALUES
('Morning Session (3 Hours)', '2026-10-12', '09:00:00', '12:00:00'),
('Night Session (3 Hours)', '2026-10-12', '17:00:00', '20:00:00'),
('Morning Session (3 Hours)', '2026-10-13', '09:00:00', '12:00:00'),
('Night Session (3 Hours)', '2026-10-13', '17:00:00', '20:00:00'),
('Morning Session (3 Hours)', '2026-10-14', '09:00:00', '12:00:00'),
('Night Session (3 Hours)', '2026-10-14', '17:00:00', '20:00:00'),
('Morning Session (3 Hours)', '2026-10-15', '09:00:00', '12:00:00'),
('Night Session (3 Hours)', '2026-10-15', '17:00:00', '20:00:00')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Seed 8 Registered Academic Courses
INSERT INTO `courses` (`course_code`, `course_title`, `department`, `student_count`) VALUES
('CS101', 'Introduction to Computer Science', 'Computer Science', 210),
('MTH201', 'Linear Algebra & Calculus', 'Mathematics', 150),
('PHY102', 'General Physics II', 'Physics', 75),
('ENG101', 'Technical Communication', 'Humanities', 110),
('CHM103', 'Organic Chemistry Basics', 'Chemistry', 65),
('STA111', 'Statistics for Engineers', 'Mathematics', 130),
('ECE204', 'Electrical Circuit Theory', 'Electrical Engineering', 95),
('GST102', 'Use of English & Communication', 'General Studies', 240)
ON DUPLICATE KEY UPDATE `id`=`id`;
