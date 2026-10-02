<?php
/**
 * CampusGuardian - Automatic Attendance Engine (09:00 AM Check)
 * Cron / Background Script
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../mail/mailer.php';

header('Content-Type: application/json');

$today = date('Y-m-d');
$now_time = date('H:i:s');
$cutoff_time = get_db_setting('college_start_time', '09:00:00');

$auto_enabled = get_db_setting('auto_absence_enabled', '1');
if ($auto_enabled != '1') {
    echo json_encode(['status' => 'disabled', 'message' => 'Auto attendance check is disabled in system settings.']);
    exit;
}

// Only proceed if current time is past cutoff time (e.g. 09:00 AM)
if ($now_time < $cutoff_time && (!isset($_GET['force']))) {
    echo json_encode(['status' => 'pending', 'message' => 'Current time (' . $now_time . ') is before official cutoff time (' . $cutoff_time . ').']);
    exit;
}

try {
    $db = Database::getConnection();

    // Find all active students who do NOT have an attendance entry for today
    $sql = "SELECT s.id, s.name, s.register_number, s.parent_email, s.parent_name, s.user_id 
            FROM students s 
            WHERE s.id NOT IN (SELECT student_id FROM attendance WHERE date = ?)
            AND s.id NOT IN (SELECT student_id FROM leave_requests WHERE start_date <= ? AND end_date >= ? AND status = 'approved')";

    $stmt = $db->prepare($sql);
    $stmt->execute([$today, $today, $today]);
    $missing_students = $stmt->fetchAll();

    $marked_count = 0;
    foreach ($missing_students as $student) {
        // Mark attendance as NOT INFORMED automatically
        $ins = $db->prepare("INSERT INTO attendance (student_id, date, status, remarks, auto_marked) VALUES (?, ?, 'Not Informed', 'Auto-flagged missing past 09:00 AM cutoff', 1)");
        $ins->execute([$student['id'], $today]);
        $marked_count++;

        // Add Notification
        add_notification($student['user_id'], 'Absence Flagged', 'You have been automatically marked Not Informed for today (' . format_date($today) . ') as no check-in was recorded by 09:00 AM.', 'danger');

        // Dispatch Urgent Parent Email
        $email_html = EmailTemplates::getAbsenceAlertNotification($student['name'], $student['register_number'], $today, format_time($cutoff_time));
        try {
            CampusMailer::send($student['parent_email'], $student['parent_name'], "URGENT: CampusGuardian Student Absence Alert - " . $student['name'], $email_html);
        } catch (Exception $e) {
            error_log("Mail Error: " . $e->getMessage());
        }
    }

    echo json_encode([
        'status' => 'success',
        'marked_count' => $marked_count,
        'date' => $today,
        'cutoff' => $cutoff_time,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
