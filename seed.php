<?php
require_once __DIR__ . '/config/config.php';

try {
    $db = Database::getConnection();

    // Insert Dummy Departments if not exists
    $db->query("INSERT IGNORE INTO departments (dept_name, dept_code) VALUES ('Computer Science', 'CSE'), ('Information Technology', 'IT'), ('Electronics', 'ECE')");

    $dept_id = $db->query("SELECT id FROM departments LIMIT 1")->fetchColumn();
    if (!$dept_id) $dept_id = 1;

    // Create a dummy user for student
    $db->query("INSERT IGNORE INTO users (email, password, role, is_active) VALUES ('rahul.mca24@campusguardian.edu', '" . password_hash('password123', PASSWORD_BCRYPT) . "', 'student', 1)");
    $student_user_id = $db->query("SELECT id FROM users WHERE email = 'rahul.mca24@campusguardian.edu'")->fetchColumn();

    // Insert Dummy Student
    $db->query("INSERT IGNORE INTO students (user_id, student_id_code, register_number, name, student_email, department_id, year, section, phone, parent_name, parent_phone, parent_email) VALUES ($student_user_id, 'STD-1001', 'REG1001', 'Rahul Sharma', 'rahul.mca24@campusguardian.edu', $dept_id, 'II', 'A', '9876543210', 'Suresh Sharma', '9876543211', 'parent.rahul@example.com')");
    
    $student_id = $db->query("SELECT id FROM students WHERE student_email = 'rahul.mca24@campusguardian.edu'")->fetchColumn();

    // Insert Dummy Leave Requests
    $db->query("INSERT IGNORE INTO leave_requests (student_id, leave_type, start_date, end_date, total_days, reason, staff_approval, hod_approval, status) VALUES 
    ($student_id, 'Sick Leave', DATE_SUB(CURDATE(), INTERVAL 2 DAY), DATE_SUB(CURDATE(), INTERVAL 1 DAY), 2, 'Fever and cold', 'approved', 'approved', 'approved'),
    ($student_id, 'Casual Leave', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 DAY), 2, 'Attending family function', 'pending', 'pending', 'pending'),
    ($student_id, 'On Duty', DATE_ADD(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 6 DAY), 2, 'Attending Hackathon in Chennai', 'pending', 'pending', 'pending')
    ");

    // Fetch leave requests to map to parent notifications
    $leaves = $db->query("SELECT id, status FROM leave_requests WHERE student_id = $student_id")->fetchAll();
    
    foreach ($leaves as $leave) {
        $lr_id = $leave['id'];
        $status = ($leave['status'] == 'approved') ? 'received' : 'sent';
        $db->query("INSERT IGNORE INTO parent_notifications (student_id, leave_request_id, parent_email, title, message, status) VALUES ($student_id, $lr_id, 'parent.rahul@example.com', 'Leave Application', 'Ward has applied for leave', '$status')");
    }

    // Dummy Late Entries
    $db->query("INSERT IGNORE INTO late_entries (student_id, date, arrival_time, late_minutes, reason, staff_approval, hod_approval) VALUES 
    ($student_id, DATE_SUB(CURDATE(), INTERVAL 3 DAY), '09:45:00', 45, 'Bus delayed due to traffic', 'approved', 'approved'),
    ($student_id, CURDATE(), '10:00:00', 60, 'Bike puncture', 'pending', 'pending')
    ");

    // Dummy Half Day
    $db->query("INSERT IGNORE INTO half_day_permissions (student_id, date, permission_time, expected_return_time, reason, staff_approval, hod_approval) VALUES 
    ($student_id, CURDATE(), '13:00:00', '16:00:00', 'Going to hospital', 'pending', 'pending')
    ");

    echo "Dummy data populated successfully! You can now test the views for Parent, Staff, and HOD.";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
