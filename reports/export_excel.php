<?php
/**
 * CampusGuardian - Excel (CSV) Export Generator
 */
require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION['user_id'])) {
    die("Access denied.");
}

$report_type = $_GET['type'] ?? 'attendance';
$date_filter = $_GET['date'] ?? date('Y-m-d');
$dept_filter = (int)($_GET['department_id'] ?? 0);

$db = Database::getConnection();

$filename = "CampusGuardian_" . ucfirst($report_type) . "_Report_" . date('Y-m-d_His') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

if ($report_type === 'attendance') {
    fputcsv($output, ['ID', 'Student Name', 'Register Number', 'Department', 'Date', 'Check-In Time', 'Status', 'Remarks']);
    
    $sql = "SELECT a.id, s.name, s.register_number, d.dept_code, a.date, a.check_in_time, a.status, a.remarks 
            FROM attendance a 
            JOIN students s ON a.student_id = s.id 
            JOIN departments d ON s.department_id = d.id 
            WHERE a.date = ?";
    $params = [$date_filter];
    if ($dept_filter > 0) {
        $sql .= " AND s.department_id = ?";
        $params[] = $dept_filter;
    }
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }

} elseif ($report_type === 'late') {
    fputcsv($output, ['ID', 'Student Name', 'Register Number', 'Department', 'Date', 'Arrival Time', 'Late Minutes', 'Reason', 'Approval Status']);
    
    $sql = "SELECT l.id, s.name, s.register_number, d.dept_code, l.date, l.arrival_time, l.late_minutes, l.reason, l.status 
            FROM late_entries l 
            JOIN students s ON l.student_id = s.id 
            JOIN departments d ON s.department_id = d.id 
            WHERE 1=1";
    $params = [];
    if (!empty($date_filter)) {
        $sql .= " AND l.date = ?";
        $params[] = $date_filter;
    }
    if ($dept_filter > 0) {
        $sql .= " AND s.department_id = ?";
        $params[] = $dept_filter;
    }
    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }

} elseif ($report_type === 'leave') {
    fputcsv($output, ['ID', 'Student Name', 'Register Number', 'Department', 'Leave Type', 'Start Date', 'End Date', 'Total Days', 'Reason', 'Approval Status']);
    
    $sql = "SELECT lr.id, s.name, s.register_number, d.dept_code, lr.leave_type, lr.start_date, lr.end_date, lr.total_days, lr.reason, lr.status 
            FROM leave_requests lr 
            JOIN students s ON lr.student_id = s.id 
            JOIN departments d ON s.department_id = d.id 
            WHERE 1=1";
    $params = [];
    if ($dept_filter > 0) {
        $sql .= " AND s.department_id = ?";
        $params[] = $dept_filter;
    }
    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
}

fclose($output);
exit;
