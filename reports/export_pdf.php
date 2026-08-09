<?php
/**
 * CampusGuardian - Printable PDF Report Template Generator
 */
require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION['user_id'])) {
    die("Access denied.");
}

$report_type = $_GET['type'] ?? 'attendance';
$date_filter = $_GET['date'] ?? date('Y-m-d');
$dept_filter = (int)($_GET['department_id'] ?? 0);
$institution = get_db_setting('system_institution_name', 'CampusGuardian Institute');

$db = Database::getConnection();

$records = [];
if ($report_type === 'attendance') {
    $sql = "SELECT a.*, s.name as student_name, s.register_number, d.dept_code 
            FROM attendance a JOIN students s ON a.student_id = s.id 
            JOIN departments d ON s.department_id = d.id WHERE a.date = ?";
    $params = [$date_filter];
    if ($dept_filter > 0) { $sql .= " AND s.department_id = ?"; $params[] = $dept_filter; }
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $records = $stmt->fetchAll();

} elseif ($report_type === 'late') {
    $sql = "SELECT l.*, s.name as student_name, s.register_number, d.dept_code 
            FROM late_entries l JOIN students s ON l.student_id = s.id 
            JOIN departments d ON s.department_id = d.id WHERE 1=1";
    $params = [];
    if (!empty($date_filter)) { $sql .= " AND l.date = ?"; $params[] = $date_filter; }
    if ($dept_filter > 0) { $sql .= " AND s.department_id = ?"; $params[] = $dept_filter; }
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $records = $stmt->fetchAll();

} elseif ($report_type === 'leave') {
    $sql = "SELECT lr.*, s.name as student_name, s.register_number, d.dept_code 
            FROM leave_requests lr JOIN students s ON lr.student_id = s.id 
            JOIN departments d ON s.department_id = d.id WHERE 1=1";
    $params = [];
    if ($dept_filter > 0) { $sql .= " AND s.department_id = ?"; $params[] = $dept_filter; }
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $records = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>CampusGuardian Official Report - <?php echo ucfirst($report_type); ?></title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; margin: 30px; color: #1e293b; background: #fff; }
        .header { text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 24px; text-transform: uppercase; letter-spacing: 1px; color: #0f172a; }
        .header p { margin: 4px 0 0; color: #64748b; font-size: 14px; }
        .meta-bar { display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 13px; color: #475569; background: #f8fafc; padding: 10px 15px; border-radius: 6px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 13px; }
        th, td { border: 1px solid #cbd5e1; padding: 8px 12px; text-align: left; }
        th { background: #0f172a; color: #ffffff; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; }
        tr:nth-child(even) { background: #f8fafc; }
        .footer { margin-top: 40px; font-size: 11px; text-align: center; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 10px; }
        @media print {
            .no-print { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body>

<div class="no-print" style="margin-bottom: 20px; text-align: right;">
    <button onclick="window.print();" style="padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
        🖨️ Print / Save as PDF
    </button>
</div>

<div class="header">
    <h1><?php echo htmlspecialchars($institution); ?></h1>
    <p>CampusGuardian Smart Student Monitoring & Approval System</p>
    <h3 style="margin-top: 10px; color: #3b82f6; font-size: 18px;">Official <?php echo ucfirst($report_type); ?> Summary Report</h3>
</div>

<div class="meta-bar">
    <div><strong>Generated On:</strong> <?php echo date('d M Y, h:i A'); ?></div>
    <div><strong>Filter Date:</strong> <?php echo format_date($date_filter); ?></div>
    <div><strong>Total Records:</strong> <?php echo count($records); ?></div>
</div>

<table>
    <thead>
        <?php if ($report_type === 'attendance'): ?>
            <tr>
                <th>#</th>
                <th>Student Name</th>
                <th>Reg No</th>
                <th>Dept</th>
                <th>Check-In Time</th>
                <th>Status</th>
                <th>Remarks</th>
            </tr>
        <?php elseif ($report_type === 'late'): ?>
            <tr>
                <th>#</th>
                <th>Student Name</th>
                <th>Reg No</th>
                <th>Date</th>
                <th>Arrival Time</th>
                <th>Delay</th>
                <th>Status</th>
            </tr>
        <?php elseif ($report_type === 'leave'): ?>
            <tr>
                <th>#</th>
                <th>Student Name</th>
                <th>Leave Type</th>
                <th>From Date</th>
                <th>To Date</th>
                <th>Days</th>
                <th>Status</th>
            </tr>
        <?php endif; ?>
    </thead>
    <tbody>
        <?php if (empty($records)): ?>
            <tr><td colspan="7" style="text-align: center;">No records found.</td></tr>
        <?php else: ?>
            <?php foreach ($records as $index => $r): ?>
                <tr>
                    <td><?php echo $index + 1; ?></td>
                    <?php if ($report_type === 'attendance'): ?>
                        <td><strong><?php echo htmlspecialchars($r['student_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($r['register_number']); ?></td>
                        <td><?php echo htmlspecialchars($r['dept_code']); ?></td>
                        <td><?php echo format_time($r['check_in_time']); ?></td>
                        <td><strong><?php echo strtoupper($r['status']); ?></strong></td>
                        <td><?php echo htmlspecialchars($r['remarks'] ?? '-'); ?></td>

                    <?php elseif ($report_type === 'late'): ?>
                        <td><strong><?php echo htmlspecialchars($r['student_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($r['register_number']); ?></td>
                        <td><?php echo format_date($r['date']); ?></td>
                        <td><?php echo format_time($r['arrival_time']); ?></td>
                        <td><?php echo $r['late_minutes']; ?> mins</td>
                        <td><strong><?php echo strtoupper($r['status']); ?></strong></td>

                    <?php elseif ($report_type === 'leave'): ?>
                        <td><strong><?php echo htmlspecialchars($r['student_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($r['leave_type']); ?></td>
                        <td><?php echo format_date($r['start_date']); ?></td>
                        <td><?php echo format_date($r['end_date']); ?></td>
                        <td><?php echo $r['total_days']; ?></td>
                        <td><strong><?php echo strtoupper($r['status']); ?></strong></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<div class="footer">
    <p>This document is electronically generated by CampusGuardian. Principal / HOD Signature Verification Required for Physical Records.</p>
</div>

</body>
</html>
