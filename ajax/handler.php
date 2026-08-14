<?php
/**
 * CampusGuardian - AJAX Async API Controller
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../mail/mailer.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthenticated session']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$db = Database::getConnection();

if ($action === 'process_approval') {
    $request_id = (int)($_POST['request_id'] ?? 0);
    $request_type = sanitize($_POST['request_type'] ?? ''); // late_entry, leave_request, half_day
    $approval_status = sanitize($_POST['status'] ?? ''); // approved, rejected
    $remarks = sanitize($_POST['remarks'] ?? '');
    $role = $_SESSION['role'] ?? '';

    if ($request_id <= 0 || !in_array($approval_status, ['approved', 'rejected'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
        exit;
    }

    try {
        $student_id = 0;
        $student_name = '';
        $parent_email = '';
        $parent_name = '';

        if ($request_type === 'late_entry') {
            $stmt = $db->prepare("SELECT l.*, s.name as student_name, s.user_id as student_user_id, s.parent_email, s.parent_name FROM late_entries l JOIN students s ON l.student_id = s.id WHERE l.id = ?");
            $stmt->execute([$request_id]);
            $req = $stmt->fetch();

            if (!$req) {
                echo json_encode(['success' => false, 'message' => 'Record not found.']);
                exit;
            }

            if ($role === ROLE_STAFF) {
                // Staff approval level
                $u_stmt = $db->prepare("UPDATE late_entries SET staff_approval = ?, staff_remarks = ?, status = ? WHERE id = ?");
                $u_stmt->execute([$approval_status, $remarks, $approval_status, $request_id]);
            } else {
                // HOD level
                $u_stmt = $db->prepare("UPDATE late_entries SET hod_approval = ?, hod_remarks = ?, status = ? WHERE id = ?");
                $u_stmt->execute([$approval_status, $remarks, $approval_status, $request_id]);
            }

            // Also update attendance table status to Late if approved
            if ($approval_status === 'approved') {
                $att_stmt = $db->prepare("INSERT INTO attendance (student_id, date, check_in_time, status, remarks) VALUES (?, ?, ?, 'Late', ?) ON DUPLICATE KEY UPDATE status = 'Late', remarks = ?");
                $att_stmt->execute([$req['student_id'], $req['date'], $req['arrival_time'], $req['reason'], $req['reason']]);
            }

            // Send notification to student
            add_notification($req['student_user_id'], 'Late Entry ' . ucfirst($approval_status), "Your late entry request for " . format_date($req['date']) . " was " . $approval_status . " by " . strtoupper($role) . ".", $approval_status === 'approved' ? 'success' : 'danger');

            // Trigger Parent Email Notification
            $email_body = EmailTemplates::getLateEntryNotification($req['student_name'], $req['date'], $req['arrival_time'], $req['late_minutes'], $req['reason'], $approval_status, $remarks);
            CampusMailer::send($req['parent_email'], $req['parent_name'], "CampusGuardian: Late Entry Notice - " . ucfirst($approval_status), $email_body);

        } elseif ($request_type === 'leave_request') {
            $stmt = $db->prepare("SELECT l.*, s.name as student_name, s.user_id as student_user_id, s.parent_email, s.parent_name FROM leave_requests l JOIN students s ON l.student_id = s.id WHERE l.id = ?");
            $stmt->execute([$request_id]);
            $req = $stmt->fetch();

            if (!$req) {
                echo json_encode(['success' => false, 'message' => 'Leave record not found.']);
                exit;
            }

            if ($role === ROLE_STAFF) {
                $u_stmt = $db->prepare("UPDATE leave_requests SET staff_approval = ?, staff_remarks = ? WHERE id = ?");
                $u_stmt->execute([$approval_status, $remarks, $request_id]);
            } else {
                $u_stmt = $db->prepare("UPDATE leave_requests SET hod_approval = ?, hod_remarks = ?, status = ? WHERE id = ?");
                $u_stmt->execute([$approval_status, $remarks, $approval_status, $request_id]);
            }

            // If HOD approves, mark Attendance table as Leave
            if ($approval_status === 'approved' && ($role === ROLE_HOD || $req['staff_approval'] === 'approved')) {
                $db->prepare("UPDATE leave_requests SET status = 'approved' WHERE id = ?")->execute([$request_id]);
                $att_stmt = $db->prepare("INSERT INTO attendance (student_id, date, status, remarks) VALUES (?, ?, 'Leave', ?) ON DUPLICATE KEY UPDATE status = 'Leave'");
                $att_stmt->execute([$req['student_id'], $req['start_date'], $req['reason']]);
            }

            add_notification($req['student_user_id'], 'Leave Application ' . ucfirst($approval_status), "Your " . $req['leave_type'] . " application has been " . $approval_status . ".", $approval_status === 'approved' ? 'success' : 'danger');

            $email_body = EmailTemplates::getLeaveNotification($req['student_name'], $req['leave_type'], $req['start_date'], $req['end_date'], $req['reason'], $approval_status, $remarks);
            CampusMailer::send($req['parent_email'], $req['parent_name'], "CampusGuardian: Leave Application " . ucfirst($approval_status), $email_body);

        } elseif ($request_type === 'half_day') {
            $stmt = $db->prepare("SELECT h.*, s.name as student_name, s.user_id as student_user_id, s.parent_email, s.parent_name FROM half_day_permissions h JOIN students s ON h.student_id = s.id WHERE h.id = ?");
            $stmt->execute([$request_id]);
            $req = $stmt->fetch();

            if ($role === ROLE_STAFF) {
                $db->prepare("UPDATE half_day_permissions SET staff_approval = ?, staff_remarks = ?, status = ? WHERE id = ?")->execute([$approval_status, $remarks, $approval_status, $request_id]);
            } else {
                $db->prepare("UPDATE half_day_permissions SET hod_approval = ?, hod_remarks = ?, status = ? WHERE id = ?")->execute([$approval_status, $remarks, $approval_status, $request_id]);
            }

            if ($approval_status === 'approved') {
                $db->prepare("INSERT INTO attendance (student_id, date, status, remarks) VALUES (?, ?, 'Half Day', ?) ON DUPLICATE KEY UPDATE status = 'Half Day'")->execute([$req['student_id'], $req['date'], $req['reason']]);
            }

            add_notification($req['student_user_id'], 'Half-Day Permission ' . ucfirst($approval_status), "Your half-day permission request was " . $approval_status . ".", $approval_status === 'approved' ? 'success' : 'danger');
        }

        echo json_encode(['success' => true, 'message' => 'Request successfully updated to ' . strtoupper($approval_status) . '.']);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
    }

} elseif ($action === 'mark_parent_notification_received') {
    $notification_id = (int)($_POST['notification_id'] ?? 0);

    if ($notification_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid notification ID.']);
        exit;
    }

    try {
        $now = date('Y-m-d H:i:s');
        $stmt = $db->prepare("UPDATE parent_notifications SET status = 'received', received_at = ? WHERE id = ?");
        $stmt->execute([$now, $notification_id]);

        echo json_encode([
            'success' => true, 
            'message' => 'Marked as received successfully.', 
            'received_at' => date('d M Y, h:i A', strtotime($now))
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action requested.']);
}
