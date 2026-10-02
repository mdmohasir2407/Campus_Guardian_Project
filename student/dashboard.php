<?php
/**
 * CampusGuardian - Student Overview Dashboard
 */
require_once __DIR__ . '/../includes/auth_check.php';
check_auth([ROLE_STUDENT]);

$page_title = "Student Dashboard";
$db = Database::getConnection();
$student_id = $_SESSION['student_id'] ?? 0;

// Fetch student metrics (including late entries, leaves, and half-day permissions)
$pending_count = $db->query("SELECT COUNT(*) FROM leave_requests WHERE student_id = $student_id AND status = 'pending'")->fetchColumn() +
                 $db->query("SELECT COUNT(*) FROM late_entries WHERE student_id = $student_id AND status = 'pending'")->fetchColumn() +
                 $db->query("SELECT COUNT(*) FROM half_day_permissions WHERE student_id = $student_id AND status = 'pending'")->fetchColumn();

$approved_count = $db->query("SELECT COUNT(*) FROM leave_requests WHERE student_id = $student_id AND status = 'approved'")->fetchColumn() +
                  $db->query("SELECT COUNT(*) FROM late_entries WHERE student_id = $student_id AND status = 'approved'")->fetchColumn() +
                  $db->query("SELECT COUNT(*) FROM half_day_permissions WHERE student_id = $student_id AND status = 'approved'")->fetchColumn();

$total_count = $db->query("SELECT COUNT(*) FROM leave_requests WHERE student_id = $student_id")->fetchColumn() +
               $db->query("SELECT COUNT(*) FROM late_entries WHERE student_id = $student_id")->fetchColumn() +
               $db->query("SELECT COUNT(*) FROM half_day_permissions WHERE student_id = $student_id")->fetchColumn();

// Fetch recent requests (combining all three types)
$stmt_lates = $db->query("SELECT 'Late Entry' as req_type, date, arrival_time as detail, reason, status, created_at FROM late_entries WHERE student_id = $student_id ORDER BY id DESC LIMIT 5")->fetchAll();
$stmt_leaves = $db->query("SELECT leave_type as req_type, start_date as date, CONCAT(total_days, ' Day(s)') as detail, reason, status, created_at FROM leave_requests WHERE student_id = $student_id ORDER BY id DESC LIMIT 5")->fetchAll();
$stmt_half = $db->query("SELECT 'Half-Day Outpass' as req_type, date, CONCAT(permission_time, ' to ', expected_return_time) as detail, reason, status, created_at FROM half_day_permissions WHERE student_id = $student_id ORDER BY id DESC LIMIT 5")->fetchAll();

$recent_activity = array_merge($stmt_lates, $stmt_leaves, $stmt_half);

// Sort chronologically (newest first) and slice to top 5
usort($recent_activity, function($a, $b) {
    return strcmp($b['created_at'], $a['created_at']);
});
$recent_activity = array_slice($recent_activity, 0, 5);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div id="content">
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container-fluid p-4">
        <?php echo get_flash(); ?>

        <!-- Welcome Banner -->
        <div class="card border-0 shadow-sm p-4 mb-4 bg-primary text-white" style="border-radius:16px;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="fw-bold mb-1">Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>! 👋</h3>
                    <p class="mb-0 text-white-50">Department of <?php echo htmlspecialchars($_SESSION['dept_name']); ?> | Quick Access Portal</p>
                </div>
                <div class="d-none d-md-block">
                    <a href="late_entry.php" class="btn btn-light fw-semibold text-primary me-2"><i class="bi bi-clock me-1"></i> Submit Late Entry</a>
                    <a href="leave_request.php" class="btn btn-outline-light fw-semibold"><i class="bi bi-calendar-plus me-1"></i> Apply Leave</a>
                </div>
            </div>
        </div>

        <!-- Metric Stat Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card card-stat p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fw-medium small text-uppercase">Pending Approvals</span>
                            <h2 class="fw-bold text-warning mb-0 mt-1"><?php echo $pending_count; ?></h2>
                        </div>
                        <div class="stat-icon bg-warning-soft text-warning"><i class="bi bi-hourglass-split"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stat p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fw-medium small text-uppercase">Completed / Approved</span>
                            <h2 class="fw-bold text-success mb-0 mt-1"><?php echo $approved_count; ?></h2>
                        </div>
                        <div class="stat-icon bg-success-soft text-success"><i class="bi bi-check-circle-fill"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stat p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fw-medium small text-uppercase">Total Requests Logged</span>
                            <h2 class="fw-bold text-primary mb-0 mt-1"><?php echo $total_count; ?></h2>
                        </div>
                        <div class="stat-icon bg-primary-soft text-primary"><i class="bi bi-folder-fill"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity Table -->
        <div class="card border-0 shadow-sm p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0"><i class="bi bi-journal-text me-2 text-primary"></i> Recent Requests Activity</h5>
                <a href="status.php" class="btn btn-sm btn-link text-decoration-none">View Detailed Status</a>
            </div>
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Date / Details</th>
                            <th>Reason</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_activity)): ?>
                            <tr><td colspan="4" class="text-center py-4 text-muted">No request history found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($recent_activity as $act): ?>
                                <tr>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($act['req_type']); ?></span></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?php echo format_date($act['date']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($act['detail']); ?></small>
                                    </td>
                                    <td class="small text-muted"><?php echo htmlspecialchars($act['reason']); ?></td>
                                    <td><span class="badge badge-status <?php echo get_badge_class($act['status']); ?>"><?php echo strtoupper($act['status']); ?></span></td>
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
