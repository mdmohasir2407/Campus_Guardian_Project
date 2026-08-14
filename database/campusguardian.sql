-- CampusGuardian Database Schema
-- Database Engine: InnoDB | Charset: utf8mb4_unicode_ci
-- Version: 1.0.0

CREATE DATABASE IF NOT EXISTS `campusguardian` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `campusguardian`;

-- --------------------------------------------------------
-- Table 1: `departments`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `departments`;
CREATE TABLE `departments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `dept_code` VARCHAR(20) NOT NULL UNIQUE,
  `dept_name` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `departments` (`id`, `dept_code`, `dept_name`) VALUES
(1, 'CSE', 'Computer Science & Engineering'),
(2, 'MCA', 'Master of Computer Applications'),
(3, 'ECE', 'Electronics & Communication Engineering'),
(4, 'MECH', 'Mechanical Engineering');

-- --------------------------------------------------------
-- Table 2: `users`
-- Default password for all seed accounts is: password123
-- Hash: $2y$10$4y.R9O5X247E/Xw789Z49.B8QW.jFpB6fN6M5C7D8E9F0G1H2I3J4 (or standard password_hash)
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(120) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'hod', 'staff', 'student', 'parent') NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_email` (`email`),
  INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Password hash for 'password123': $2y$10$r9G0V7tA1M.b3bK1xO7H/.k5C/J7Z9Y3N9Y1X1Y1Z1Y1Z1Y1Z1Y1Z
INSERT INTO `users` (`id`, `email`, `password`, `role`, `is_active`) VALUES
(1, 'admin@campusguardian.edu', '$2y$10$r9G0V7tA1M.b3bK1xO7H/.k5C/J7Z9Y3N9Y1X1Y1Z1Y1Z1Y1Z1Y1Z', 'admin', 1),
(2, 'hod.mca@campusguardian.edu', '$2y$10$r9G0V7tA1M.b3bK1xO7H/.k5C/J7Z9Y3N9Y1X1Y1Z1Y1Z1Y1Z1Y1Z', 'hod', 1),
(3, 'staff.sarah@campusguardian.edu', '$2y$10$r9G0V7tA1M.b3bK1xO7H/.k5C/J7Z9Y3N9Y1X1Y1Z1Y1Z1Y1Z1Y1Z', 'staff', 1),
(4, 'rahul.mca24@campusguardian.edu', '$2y$10$r9G0V7tA1M.b3bK1xO7H/.k5C/J7Z9Y3N9Y1X1Y1Z1Y1Z1Y1Z1Y1Z', 'student', 1),
(5, 'hod.cse@campusguardian.edu', '$2y$10$r9G0V7tA1M.b3bK1xO7H/.k5C/J7Z9Y3N9Y1X1Y1Z1Y1Z1Y1Z1Y1Z', 'hod', 1),
(6, 'priya.student@campusguardian.edu', '$2y$10$r9G0V7tA1M.b3bK1xO7H/.k5C/J7Z9Y3N9Y1X1Y1Z1Y1Z1Y1Z1Y1Z', 'student', 1),
(7, 'parent.rahul@example.com', '$2y$10$r9G0V7tA1M.b3bK1xO7H/.k5C/J7Z9Y3N9Y1X1Y1Z1Y1Z1Y1Z1Y1Z', 'parent', 1),
(8, 'parent.priya@example.com', '$2y$10$r9G0V7tA1M.b3bK1xO7H/.k5C/J7Z9Y3N9Y1X1Y1Z1Y1Z1Y1Z1Y1Z', 'parent', 1);

-- --------------------------------------------------------
-- Table 3: `hod`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `hod`;
CREATE TABLE `hod` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `department_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `email` VARCHAR(120) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `hod` (`id`, `user_id`, `department_id`, `name`, `phone`, `email`) VALUES
(1, 2, 2, 'Dr. Aris Thorne', '9876543210', 'hod.mca@campusguardian.edu'),
(2, 5, 1, 'Dr. Eleanor Vance', '9876543211', 'hod.cse@campusguardian.edu');

-- --------------------------------------------------------
-- Table 4: `staff`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `staff`;
CREATE TABLE `staff` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `department_id` INT NOT NULL,
  `staff_code` VARCHAR(30) NOT NULL UNIQUE,
  `name` VARCHAR(100) NOT NULL,
  `designation` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `email` VARCHAR(120) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `staff` (`id`, `user_id`, `department_id`, `staff_code`, `name`, `designation`, `phone`, `email`) VALUES
(1, 3, 2, 'STF-MCA-001', 'Prof. Sarah Jenkins', 'Assistant Professor', '9812345678', 'staff.sarah@campusguardian.edu');

-- --------------------------------------------------------
-- Table 5: `students`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `students`;
CREATE TABLE `students` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `register_number` VARCHAR(30) NOT NULL UNIQUE,
  `student_id_code` VARCHAR(30) NOT NULL UNIQUE,
<<<<<<< HEAD
  `course_type` VARCHAR(10) DEFAULT 'UG',
=======
>>>>>>> 46e8e96fd34928274cd800f4a3cc75a72bc4109b
  `name` VARCHAR(100) NOT NULL,
  `department_id` INT NOT NULL,
  `year` VARCHAR(10) NOT NULL,
  `section` VARCHAR(10) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `parent_name` VARCHAR(100) NOT NULL,
  `parent_phone` VARCHAR(20) NOT NULL,
  `parent_email` VARCHAR(120) NOT NULL,
  `student_email` VARCHAR(120) NOT NULL,
  `photo` VARCHAR(255) DEFAULT 'default_avatar.png',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE CASCADE,
  INDEX `idx_reg_no` (`register_number`),
  INDEX `idx_dept` (`department_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

<<<<<<< HEAD
INSERT INTO `students` (`id`, `user_id`, `register_number`, `student_id_code`, `course_type`, `name`, `department_id`, `year`, `section`, `phone`, `parent_name`, `parent_phone`, `parent_email`, `student_email`, `photo`) VALUES
(1, 4, '2024MCA001', 'STD-MCA-101', 'PG', 'Rahul Sharma', 2, 'II', 'A', '9123456789', 'Suresh Sharma', '9898989898', 'parent.rahul@example.com', 'rahul.mca24@campusguardian.edu', 'default_avatar.png'),
(2, 6, '2024CSE042', 'STD-CSE-205', 'UG', 'Priya Patel', 1, 'III', 'B', '9123456790', 'Ramesh Patel', '9898989899', 'parent.priya@example.com', 'priya.student@campusguardian.edu', 'default_avatar.png');
=======
INSERT INTO `students` (`id`, `user_id`, `register_number`, `student_id_code`, `name`, `department_id`, `year`, `section`, `phone`, `parent_name`, `parent_phone`, `parent_email`, `student_email`, `photo`) VALUES
(1, 4, '2024MCA001', 'STD-MCA-101', 'Rahul Sharma', 2, 'II', 'A', '9123456789', 'Suresh Sharma', '9898989898', 'parent.rahul@example.com', 'rahul.mca24@campusguardian.edu', 'default_avatar.png'),
(2, 6, '2024CSE042', 'STD-CSE-205', 'Priya Patel', 1, 'III', 'B', '9123456790', 'Ramesh Patel', '9898989899', 'parent.priya@example.com', 'priya.student@campusguardian.edu', 'default_avatar.png');
>>>>>>> 46e8e96fd34928274cd800f4a3cc75a72bc4109b

-- --------------------------------------------------------
-- Table 6: `attendance`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `attendance`;
CREATE TABLE `attendance` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `date` DATE NOT NULL,
  `check_in_time` TIME NULL,
  `status` ENUM('Present', 'Late', 'Leave', 'Half Day', 'On Duty', 'Absent', 'Not Informed') NOT NULL DEFAULT 'Not Informed',
  `remarks` VARCHAR(255) DEFAULT NULL,
  `auto_marked` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_student_date` (`student_id`, `date`),
  INDEX `idx_att_date` (`date`),
  INDEX `idx_att_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `attendance` (`id`, `student_id`, `date`, `check_in_time`, `status`, `remarks`, `auto_marked`) VALUES
(1, 1, CURDATE(), '09:18:00', 'Late', 'Traffic delay', 0),
(2, 2, CURDATE(), NULL, 'Not Informed', 'Auto marked missing past 09:00 AM', 1);

-- --------------------------------------------------------
-- Table 7: `late_entries`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `late_entries`;
CREATE TABLE `late_entries` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `date` DATE NOT NULL,
  `arrival_time` TIME NOT NULL,
  `late_minutes` INT NOT NULL,
  `reason` TEXT NOT NULL,
  `photo` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `staff_approval` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `staff_remarks` TEXT DEFAULT NULL,
  `hod_approval` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `hod_remarks` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `late_entries` (`id`, `student_id`, `date`, `arrival_time`, `late_minutes`, `reason`, `photo`, `status`, `staff_approval`, `staff_remarks`, `hod_approval`, `hod_remarks`) VALUES
(1, 1, CURDATE(), '09:18:00', 18, 'Bus broke down on Ring Road route.', NULL, 'approved', 'approved', 'Verified bus breakdown notice', 'approved', 'Approved by HOD');

-- --------------------------------------------------------
-- Table 8: `leave_requests`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `leave_requests`;
CREATE TABLE `leave_requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `leave_type` ENUM('Full Day', 'Half Day', 'Medical Leave', 'Personal Leave') NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `total_days` INT NOT NULL DEFAULT 1,
  `reason` TEXT NOT NULL,
  `attachment` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `staff_approval` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `staff_remarks` TEXT DEFAULT NULL,
  `hod_approval` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `hod_remarks` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `leave_requests` (`id`, `student_id`, `leave_type`, `start_date`, `end_date`, `total_days`, `reason`, `attachment`, `status`, `staff_approval`, `staff_remarks`, `hod_approval`, `hod_remarks`) VALUES
(1, 1, 'Medical Leave', DATE_ADD(CURDATE(), INTERVAL 1 DAY), DATE_ADD(CURDATE(), INTERVAL 2 DAY), 2, 'Severe viral fever and doctor recommended rest.', NULL, 'pending', 'approved', 'Medical certificate required on return', 'pending', NULL);

-- --------------------------------------------------------
-- Table 9: `half_day_permissions`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `half_day_permissions`;
CREATE TABLE `half_day_permissions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `date` DATE NOT NULL,
  `permission_time` TIME NOT NULL,
  `expected_return_time` TIME NOT NULL,
  `reason` TEXT NOT NULL,
  `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `staff_approval` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `staff_remarks` TEXT DEFAULT NULL,
  `hod_approval` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `hod_remarks` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `half_day_permissions` (`id`, `student_id`, `date`, `permission_time`, `expected_return_time`, `reason`, `status`, `staff_approval`, `staff_remarks`, `hod_approval`, `hod_remarks`) VALUES
(1, 1, CURDATE(), '12:30:00', '16:00:00', 'Passport verification appointment at Regional Office.', 'approved', 'approved', 'Allowed with valid token receipt', 'approved', 'HOD Approved');

-- --------------------------------------------------------
-- Table 10: `notifications`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `type` VARCHAR(50) DEFAULT 'info',
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_read` (`user_id`, `is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`) VALUES
(1, 4, 'Late Entry Approved', 'Your late entry request for today has been approved by Staff and HOD.', 'success', 0),
(2, 4, 'Leave Request Update', 'Your medical leave request is pending HOD final review.', 'info', 0);

-- --------------------------------------------------------
-- Table 11: `email_logs`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `email_logs`;
CREATE TABLE `email_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `recipient_email` VARCHAR(120) NOT NULL,
  `recipient_name` VARCHAR(100) DEFAULT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `body` TEXT NOT NULL,
  `status` ENUM('Sent', 'Failed', 'Logged') NOT NULL DEFAULT 'Logged',
  `sent_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `email_logs` (`id`, `recipient_email`, `recipient_name`, `subject`, `body`, `status`) VALUES
(1, 'parent.rahul@example.com', 'Suresh Sharma', 'CampusGuardian Alert: Late Entry Approved', 'Dear Suresh Sharma, your ward Rahul Sharma reported late at 09:18 AM today. Approval status: Approved.', 'Logged');

-- --------------------------------------------------------
-- Table 12: `settings`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `settings` (`setting_key`, `setting_value`, `description`) VALUES
('college_start_time', '09:00:00', 'College official start time for late calculation'),
('auto_absence_enabled', '1', 'Automatically mark un-checked students missing past 09:00 AM as Not Informed'),
('parent_email_notify', '1', 'Send instant email notifications to parents upon approval/absence'),
('smtp_host', 'smtp.gmail.com', 'SMTP Server Host'),
('smtp_port', '587', 'SMTP Server Port'),
('smtp_user', 'alerts@campusguardian.edu', 'SMTP Username'),
('smtp_pass', 'encrypted_password_here', 'SMTP Password / App Password'),
('smtp_encryption', 'tls', 'SMTP Encryption (tls/ssl)'),
('system_institution_name', 'CampusGuardian Institute of Technology', 'Institution Name displayed on headers and reports');

-- --------------------------------------------------------
-- Table 13: `reports`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `reports`;
CREATE TABLE `reports` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `report_type` VARCHAR(100) NOT NULL,
  `generated_by` INT NOT NULL,
  `file_path` VARCHAR(255) DEFAULT NULL,
  `date_range` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`generated_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `reports` (`id`, `report_type`, `generated_by`, `file_path`, `date_range`) VALUES
(1, 'Daily Attendance Summary', 1, 'reports/daily_summary_today.csv', 'Today');

-- --------------------------------------------------------
-- Table 14: `parent_notifications`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `parent_notifications`;
CREATE TABLE `parent_notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `leave_request_id` INT DEFAULT NULL,
  `parent_email` VARCHAR(120) NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('sent', 'received') NOT NULL DEFAULT 'sent',
  `received_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`leave_request_id`) REFERENCES `leave_requests`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `parent_notifications` (`id`, `student_id`, `leave_request_id`, `parent_email`, `title`, `message`, `status`, `received_at`) VALUES
(1, 1, 1, 'parent.rahul@example.com', 'Student Leave Notice Applied', 'Your ward Rahul Sharma submitted a Medical Leave request for 2 day(s) starting from tomorrow.', 'sent', NULL);
