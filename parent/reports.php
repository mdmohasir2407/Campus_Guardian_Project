<?php
/**
 * CampusGuardian - Parent Monthly Leave Report Page
 */
require_once __DIR__ . '/../includes/auth_check.php';
check_auth([ROLE_PARENT]);

$page_title = "Monthly Leave Report";
$db = Database::getConnection();

$parent_email = $_SESSION['parent_email'] ?? '';
$student_id = $_SESSION['student_id'] ?? 0;

// Fetch Student / Ward details
$stmt_st = $db->prepare("SELECT s.*, d.dept_name, d.dept_code FROM students s JOIN departments d ON s.department_id = d.id WHERE s.id = ?");
$stmt_st->execute([$student_id]);
$ward = $stmt_st->fetch();

$selected_month = sanitize($_GET['month'] ?? date('Y-m'));

// Fetch Monthly Leaves
$stmt = $db->prepare("SELECT * FROM leave_requests WHERE student_id = ? AND DATE_FORMAT(start_date, '%Y-%m') = ? ORDER BY start_date DESC");
$stmt->execute([$student_id, $selected_month]);
$leaves = $stmt->fetchAll();

// Calculate Monthly Statistics
$total_leave_days = 0;
$leave_type_counts = [];

foreach ($leaves as $l) {
    $total_leave_days += (int)$l['total_days'];
    $type = $l['leave_type'];
    if (!isset($leave_type_counts[$type])) {
        $leave_type_counts[$type] = 0;
    }
    $leave_type_counts[$type] += (int)$l['total_days'];
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div id="content">
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container-fluid p-4">
        <?php echo get_flash(); ?>

        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 border-bottom pb-3">
            <div>
                <h3 class="fw-bold mb-1"><i class="bi bi-calendar-range text-primary me-2"></i> Monthly Student Leave Report</h3>
                <p class="text-muted mb-0">Detailed breakdown of leave days taken by <strong><?php echo htmlspecialchars($ward['name'] ?? 'Ward'); ?></strong> (Reg. No: <?php echo htmlspecialchars($ward['register_number'] ?? 'N/A'); ?>)</p>
            </div>
            <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
                <form method="GET" class="d-flex align-items-center gap-2">
                    <label class="form-label mb-0 small fw-semibold text-muted">Select Month:</label>
                    <input type="month" name="month" class="form-control form-control-sm" value="<?php echo htmlspecialchars($selected_month); ?>" onchange="this.form.submit()">
                </form>
                <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="bi bi-printer me-1"></i> Print Report</button>
            </div>
        </div>

        <!-- Monthly Overview Banner -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-3 bg-light">
                    <div class="text-muted small fw-semibold">Target Month</div>
                    <h4 class="fw-bold text-dark mb-0"><?php echo date('F Y', strtotime($selected_month . '-01')); ?></h4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-3 bg-warning-soft">
                    <div class="text-muted small fw-semibold">Total Days Off</div>
                    <h4 class="fw-bold text-warning mb-0"><?php echo $total_leave_days; ?> Day(s)</h4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-3 bg-info-soft">
                    <div class="text-muted small fw-semibold">Leave Applications</div>
                    <h4 class="fw-bold text-info mb-0"><?php echo count($leaves); ?> Application(s)</h4>
                </div>
            </div>
        </div>

        <!-- Type Breakdown -->
        <?php if (!empty($leave_type_counts)): ?>
            <div class="card border-0 shadow-sm p-3 mb-4">
                <h6 class="fw-bold text-secondary mb-2">Leave Category Breakdown:</h6>
                <div class="d-flex flex-wrap gap-3">
                    <?php foreach ($leave_type_counts as $t => $cnt): ?>
                        <span class="badge bg-secondary p-2 fs-6">
                            <?php echo htmlspecialchars($t); ?>: <strong><?php echo $cnt; ?> Day(s)</strong>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Detailed Leave Dates Table -->
        <div class="card border-0 shadow-sm p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-list-stars text-primary me-2"></i> Applied Leave Dates Record</h5>
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>S.No</th>
                            <th>Leave Category</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Total Days</th>
                            <th>Reason / Explanation</th>
                            <th>Faculty Approval Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($leaves)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    No leave requests recorded for <?php echo date('F Y', strtotime($selected_month . '-01')); ?>.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $sn = 1; foreach ($leaves as $l): ?>
                                <tr>
                                    <td><?php echo $sn++; ?></td>
                                    <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($l['leave_type']); ?></span></td>
                                    <td class="fw-bold text-dark"><?php echo format_date($l['start_date']); ?></td>
                                    <td class="fw-bold text-dark"><?php echo format_date($l['end_date']); ?></td>
                                    <td><span class="badge bg-dark"><?php echo $l['total_days']; ?> Day(s)</span></td>
                                    <td class="text-secondary"><?php echo htmlspecialchars($l['reason']); ?></td>
                                    <td>
                                        <span class="badge badge-status <?php echo get_badge_class($l['status']); ?>">
                                            <?php echo strtoupper($l['status']); ?>
                                        </span>
                                    </td>
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
