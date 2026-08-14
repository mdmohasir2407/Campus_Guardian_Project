<?php
/**
 * CampusGuardian - Admin & System Reports
 */
require_once __DIR__ . '/../includes/auth_check.php';
check_auth([ROLE_ADMIN, ROLE_HOD]);

$page_title = "System & Analytics Reports";
$db = Database::getConnection();

$report_type = $_GET['type'] ?? 'attendance';
$date_filter = $_GET['date'] ?? date('Y-m-d');
$dept_filter = (int)($_GET['department_id'] ?? 0);

$departments = $db->query("SELECT * FROM departments ORDER BY dept_name ASC")->fetchAll();

// Query construction based on report_type
$records = [];
if ($report_type === 'attendance') {
    $sql = "SELECT a.*, s.name as student_name, s.register_number, d.dept_code 
            FROM attendance a 
            JOIN students s ON a.student_id = s.id 
            JOIN departments d ON s.department_id = d.id 
            WHERE a.date = ?";
    $params = [$date_filter];
    if ($dept_filter > 0) {
        $sql .= " AND s.department_id = ?";
        $params[] = $dept_filter;
    }
    $sql .= " ORDER BY a.id DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $records = $stmt->fetchAll();

} elseif ($report_type === 'late') {
    $sql = "SELECT l.*, s.name as student_name, s.register_number, d.dept_code 
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
    $sql .= " ORDER BY l.id DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $records = $stmt->fetchAll();

} elseif ($report_type === 'leave') {
    $sql = "SELECT lr.*, s.name as student_name, s.register_number, d.dept_code 
            FROM leave_requests lr 
            JOIN students s ON lr.student_id = s.id 
            JOIN departments d ON s.department_id = d.id 
            WHERE 1=1";
    $params = [];
    if ($dept_filter > 0) {
        $sql .= " AND s.department_id = ?";
        $params[] = $dept_filter;
    }
    $sql .= " ORDER BY lr.id DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $records = $stmt->fetchAll();
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div id="content">
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container-fluid p-4">
        <?php echo get_flash(); ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Reports & Export Center</h3>
                <p class="text-muted mb-0">Generate, view, and export attendance, late entry, and leave reports.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="../reports/export_excel.php?type=<?php echo $report_type; ?>&date=<?php echo $date_filter; ?>&department_id=<?php echo $dept_filter; ?>" class="btn btn-success shadow-sm">
                    <i class="bi bi-file-earmark-excel me-1"></i> Export Excel (CSV)
                </a>
                <a href="../reports/export_pdf.php?type=<?php echo $report_type; ?>&date=<?php echo $date_filter; ?>&department_id=<?php echo $dept_filter; ?>" target="_blank" class="btn btn-danger shadow-sm">
                    <i class="bi bi-printer me-1"></i> Print / PDF View
                </a>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card border-0 shadow-sm p-4 mb-4">
            <form action="reports.php" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Report Category</label>
                    <select name="type" class="form-select">
                        <option value="attendance" <?php echo $report_type === 'attendance' ? 'selected' : ''; ?>>Daily Attendance Report</option>
                        <option value="late" <?php echo $report_type === 'late' ? 'selected' : ''; ?>>Late Entries Report</option>
                        <option value="leave" <?php echo $report_type === 'leave' ? 'selected' : ''; ?>>Leave Applications Report</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Filter Date</label>
                    <input type="date" name="date" class="form-control" value="<?php echo $date_filter; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Department</label>
                    <select name="department_id" class="form-select">
                        <option value="0">All Departments</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?php echo $d['id']; ?>" <?php echo $dept_filter === (int)$d['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($d['dept_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-filter me-1"></i> Apply Filters</button>
                </div>
            </form>
        </div>

        <!-- Records Table -->
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <?php if ($report_type === 'attendance'): ?>
                            <tr>
                                <th>Student Name</th>
                                <th>Reg No</th>
                                <th>Dept</th>
                                <th>Date</th>
                                <th>Check-In Time</th>
                                <th>Attendance Status</th>
                                <th>Remarks</th>
                            </tr>
                        <?php elseif ($report_type === 'late'): ?>
                            <tr>
                                <th>Student Name</th>
                                <th>Reg No</th>
                                <th>Date</th>
                                <th>Arrival Time</th>
                                <th>Delay</th>
                                <th>Reason</th>
                                <th>Status</th>
                            </tr>
                        <?php elseif ($report_type === 'leave'): ?>
                            <tr>
                                <th>Student Name</th>
                                <th>Leave Type</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Total Days</th>
                                <th>Reason</th>
                                <th>Status</th>
                            </tr>
                        <?php endif; ?>
                    </thead>
                    <tbody>
                        <?php if (empty($records)): ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted">No records matching selected criteria.</td></tr>
                        <?php else: ?>
                            <?php foreach ($records as $r): ?>
                                <tr>
                                    <?php if ($report_type === 'attendance'): ?>
                                        <td class="fw-semibold text-dark"><?php echo htmlspecialchars($r['student_name']); ?></td>
                                        <td><?php echo htmlspecialchars($r['register_number']); ?></td>
                                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($r['dept_code']); ?></span></td>
                                        <td><?php echo format_date($r['date']); ?></td>
                                        <td><?php echo format_time($r['check_in_time']); ?></td>
                                        <td><span class="badge badge-status <?php echo get_badge_class($r['status']); ?>"><?php echo strtoupper($r['status']); ?></span></td>
                                        <td class="small text-muted"><?php echo htmlspecialchars($r['remarks'] ?? '-'); ?></td>

                                    <?php elseif ($report_type === 'late'): ?>
                                        <td class="fw-semibold text-dark"><?php echo htmlspecialchars($r['student_name']); ?></td>
                                        <td><?php echo htmlspecialchars($r['register_number']); ?></td>
                                        <td><?php echo format_date($r['date']); ?></td>
                                        <td><?php echo format_time($r['arrival_time']); ?></td>
                                        <td class="text-danger fw-bold"><?php echo $r['late_minutes']; ?> min</td>
                                        <td class="small text-muted"><?php echo htmlspecialchars($r['reason']); ?></td>
                                        <td><span class="badge badge-status <?php echo get_badge_class($r['status']); ?>"><?php echo strtoupper($r['status']); ?></span></td>

                                    <?php elseif ($report_type === 'leave'): ?>
                                        <td class="fw-semibold text-dark"><?php echo htmlspecialchars($r['student_name']); ?></td>
                                        <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($r['leave_type']); ?></span></td>
                                        <td><?php echo format_date($r['start_date']); ?></td>
                                        <td><?php echo format_date($r['end_date']); ?></td>
                                        <td><strong><?php echo $r['total_days']; ?> Day(s)</strong></td>
                                        <td class="small text-muted"><?php echo htmlspecialchars($r['reason']); ?></td>
                                        <td><span class="badge badge-status <?php echo get_badge_class($r['status']); ?>"><?php echo strtoupper($r['status']); ?></span></td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
