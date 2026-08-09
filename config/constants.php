<?php
/**
 * CampusGuardian - Global Constants
 * Core PHP 8 Architecture
 */

// Application Info
define('APP_NAME', 'CampusGuardian');
define('APP_TAGLINE', 'Smart Student Monitoring, Approval & Alert System');
define('APP_VERSION', '1.0.0');

// User Roles
define('ROLE_ADMIN', 'admin');
define('ROLE_HOD', 'hod');
define('ROLE_STAFF', 'staff');
define('ROLE_STUDENT', 'student');
define('ROLE_PARENT', 'parent');

// Statuses
define('STATUS_PENDING', 'pending');
define('STATUS_APPROVED', 'approved');
define('STATUS_REJECTED', 'rejected');

// Attendance Statuses
define('ATTENDANCE_PRESENT', 'Present');
define('ATTENDANCE_LATE', 'Late');
define('ATTENDANCE_LEAVE', 'Leave');
define('ATTENDANCE_HALF_DAY', 'Half Day');
define('ATTENDANCE_ON_DUTY', 'On Duty');
define('ATTENDANCE_ABSENT', 'Absent');
define('ATTENDANCE_NOT_INFORMED', 'Not Informed');

// Leave Types
define('LEAVE_FULL_DAY', 'Full Day');
define('LEAVE_HALF_DAY', 'Half Day');
define('LEAVE_MEDICAL', 'Medical Leave');
define('LEAVE_PERSONAL', 'Personal Leave');

// System Defaults
define('COLLEGE_START_TIME', '09:00:00');
define('UPLOAD_DIR_PHOTOS', __DIR__ . '/../uploads/profile_photos/');
define('UPLOAD_DIR_ATTACHMENTS', __DIR__ . '/../uploads/attachments/');
