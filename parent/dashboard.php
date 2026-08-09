<?php
/**
 * CampusGuardian - Parent Dashboard
 * Ward Leave Tracking, Notifications, Received Acknowledgements & Monthly Reports
 */
require_once __DIR__ . '/../includes/auth_check.php';
check_auth([ROLE_PARENT]);

$page_title = "Parent Dashboard";
$db = Database::getConnection();

$parent_email = $_SESSION['parent_email'] ?? '';
$student_id = $_SESSION['student_id'] ?? 0;

// Fetch Student / Ward details
$stmt_st = $db->prepare("SELECT s.*, d.dept_name, d.dept_code FROM students s JOIN departments d ON s.department_id = d.id WHERE s.id = ?");
$stmt_st->execute([$student_id]);
$ward = $stmt_st->fetch();

if (!$ward && !empty($parent_email)) {
    $stmt_st2 = $db->prepare("SELECT s.*, d.dept_name, d.dept_code FROM students s JOIN departments d ON s.department_id = d.id WHERE s.parent_email = ? LIMIT 1");
    $stmt_st2->execute([$parent_email]);
    $ward = $stmt_st2->fetch();
    if ($ward) {
        $_SESSION['student_id'] = $ward['id'];
        $student_id = $ward['id'];
    }
}

$current_month = date('Y-m');
$selected_month = sanitize($_GET['month'] ?? $current_month);

// Leave Statistics for Ward
$stmt_month_leaves = $db->prepare("SELECT COUNT(*) as leave_count, COALESCE(SUM(total_days), 0) as total_days FROM leave_requests WHERE student_id = ? AND DATE_FORMAT(start_date, '%Y-%m') = ?");
$stmt_month_leaves->execute([$student_id, $selected_month]);
$month_stats = $stmt_month_leaves->fetch();

$stmt_total_leaves = $db->prepare("SELECT COUNT(*) as leave_count, COALESCE(SUM(total_days), 0) as total_days FROM leave_requests WHERE student_id = ?");
$stmt_total_leaves->execute([$student_id]);
$overall_stats = $stmt_total_leaves->fetch();

// Unacknowledged notifications count
$stmt_unack = $db->prepare("SELECT COUNT(*) as unack_count FROM parent_notifications WHERE student_id = ? AND status = 'sent'");
$stmt_unack->execute([$student_id]);
$unack_res = $stmt_unack->fetch();
$unack_count = $unack_res['unack_count'] ?? 0;

// Fetch Parent Notifications for Ward
$stmt_pn = $db->prepare("SELECT pn.*, lr.leave_type, lr.start_date, lr.end_date, lr.total_days, lr.status as leave_status FROM parent_notifications pn LEFT JOIN leave_requests lr ON pn.leave_request_id = lr.id WHERE pn.student_id = ? ORDER BY pn.id DESC");
$stmt_pn->execute([$student_id]);
$notifications = $stmt_pn->fetchAll();

// Fetch Monthly Leaves Detailed List
$stmt_m_leaves = $db->prepare("SELECT * FROM leave_requests WHERE student_id = ? AND DATE_FORMAT(start_date, '%Y-%m') = ? ORDER BY start_date DESC");
$stmt_m_leaves->execute([$student_id, $selected_month]);
$monthly_leaves = $stmt_m_leaves->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div id="content">
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container-fluid p-4">
        <?php echo get_flash(); ?>

        <!-- Welcome Banner & Ward Info -->
        <div class="card border-0 shadow-sm p-4 mb-4 bg-gradient text-white" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border-radius: 14px;">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <span class="badge bg-primary-soft text-primary border border-primary px-3 py-2 rounded-pill mb-2 fw-semibold">👨‍👩‍👦 PARENT PORTAL</span>
                    <h3 class="fw-bold mb-1">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h3>
                    <p class="text-white-50 mb-0">
                        Monitoring Ward: <strong class="text-white"><?php echo htmlspecialchars($ward['name'] ?? 'N/A'); ?></strong> 
                        | Reg. No: <span class="badge bg-secondary"><?php echo htmlspecialchars($ward['register_number'] ?? 'N/A'); ?></span>
                        | Dept: <span class="badge bg-info text-dark"><?php echo htmlspecialchars($ward['dept_code'] ?? 'N/A'); ?></span>
                        | Year: <?php echo htmlspecialchars($ward['year'] ?? 'N/A'); ?> - Section <?php echo htmlspecialchars($ward['section'] ?? 'N/A'); ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="reports.php" class="btn btn-primary fw-semibold shadow-sm">
                        <i class="bi bi-file-earmark-bar-graph me-1"></i> Full Monthly Report
                    </a>
                </div>
            </div>
        </div>

        <!-- Summary Stat Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3 h-100">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-warning-soft text-warning rounded-circle p-3 fs-3"><i class="bi bi-calendar2-range"></i></div>
                        <div>
                            <div class="text-muted small fw-semibold">Leave Days (This Month)</div>
                            <h3 class="fw-bold mb-0 text-dark"><?php echo $month_stats['total_days']; ?> <span class="fs-6 text-muted font-normal">day(s)</span></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3 h-100">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary-soft text-primary rounded-circle p-3 fs-3"><i class="bi bi-clock-history"></i></div>
                        <div>
                            <div class="text-muted small fw-semibold">Overall Leaves Applied</div>
                            <h3 class="fw-bold mb-0 text-dark"><?php echo $overall_stats['total_days']; ?> <span class="fs-6 text-muted font-normal">day(s)</span></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3 h-100">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-danger-soft text-danger rounded-circle p-3 fs-3"><i class="bi bi-bell-fill"></i></div>
                        <div>
                            <div class="text-muted small fw-semibold">Pending Acknowledgment</div>
                            <h3 class="fw-bold mb-0 text-dark" id="stat_unack_count"><?php echo $unack_count; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3 h-100">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-success-soft text-success rounded-circle p-3 fs-3"><i class="bi bi-check-circle-fill"></i></div>
                        <div>
                            <div class="text-muted small fw-semibold">Parent Status</div>
                            <span class="badge bg-success fs-6 mt-1">Active Monitor</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 1: Leave Notifications & Received Tick Mark -->
        <div class="card border-0 shadow-sm p-4 mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                <div>
                    <h5 class="fw-bold mb-0"><i class="bi bi-bell text-primary me-2"></i> Student Leave Notifications</h5>
                    <p class="text-muted small mb-0">Whenever your ward applies for leave, alerts appear here. Click "Mark as Received" to acknowledge.</p>
                </div>
                <span class="badge bg-info text-dark"><?php echo count($notifications); ?> Total Alerts</span>
            </div>

            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Alert Title & Details</th>
                            <th>Leave Period</th>
                            <th>Total Days</th>
                            <th>Leave Status</th>
                            <th>Parent Received Tick</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($notifications)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="bi bi-bell-slash fs-3 d-block mb-2"></i> No leave notifications received yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($notifications as $pn): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($pn['title']); ?></div>
                                        <div class="text-muted small"><?php echo htmlspecialchars($pn['message']); ?></div>
                                        <small class="text-secondary" style="font-size:0.75rem;"><i class="bi bi-clock me-1"></i> <?php echo format_date($pn['created_at']); ?> at <?php echo format_time($pn['created_at']); ?></small>
                                    </td>
                                    <td>
                                        <?php if ($pn['start_date']): ?>
                                            <span class="fw-semibold text-dark"><?php echo format_date($pn['start_date']); ?></span>
                                            <span class="text-muted">to</span>
                                            <span class="fw-semibold text-dark"><?php echo format_date($pn['end_date']); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border fw-bold"><?php echo $pn['total_days'] ?? 1; ?> Day(s)</span>
                                    </td>
                                    <td>
                                        <?php 
                                        $l_status = $pn['leave_status'] ?? 'pending';
                                        ?>
                                        <span class="badge badge-status <?php echo get_badge_class($l_status); ?>">
                                            <?php echo strtoupper($l_status); ?>
                                        </span>
                                    </td>
                                    <td id="tick_cell_<?php echo $pn['id']; ?>">
                                        <?php if ($pn['status'] === 'received'): ?>
                                            <div class="d-flex align-items-center gap-1 text-success fw-bold">
                                                <i class="bi bi-check-circle-fill fs-5 text-success"></i> 
                                                <span>Received</span>
                                            </div>
                                            <small class="text-muted d-block" style="font-size:0.72rem;">
                                                Ack: <?php echo format_date($pn['received_at']); ?> <?php echo format_time($pn['received_at']); ?>
                                            </small>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline-success btn-mark-received fw-semibold" data-id="<?php echo $pn['id']; ?>">
                                                <i class="bi bi-check-lg me-1"></i> Mark as Received
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section 2: Monthly Leave Report View -->
        <div class="card border-0 shadow-sm p-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 border-bottom pb-3">
                <div>
                    <h5 class="fw-bold mb-0"><i class="bi bi-calendar-check text-success me-2"></i> Monthly Leave Report (Ward History)</h5>
                    <p class="text-muted small mb-0">Detailed list of dates on which your ward has applied for leave.</p>
                </div>
                <form method="GET" class="d-flex align-items-center gap-2 mt-2 mt-md-0">
                    <label class="form-label mb-0 small fw-semibold text-muted">Select Month:</label>
                    <input type="month" name="month" class="form-control form-control-sm" value="<?php echo htmlspecialchars($selected_month); ?>" onchange="this.form.submit()">
                </form>
            </div>

            <div class="row mb-3">
                <div class="col-12">
                    <div class="p-3 bg-light rounded border d-flex justify-content-between align-items-center">
                        <span class="fw-semibold text-secondary">
                            Month: <strong class="text-dark"><?php echo date('F Y', strtotime($selected_month . '-01')); ?></strong>
                        </span>
                        <span class="badge bg-primary fs-6">
                            Total Days Off: <?php echo $month_stats['total_days']; ?> Day(s)
                        </span>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Leave Category</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Duration</th>
                            <th>Reason</th>
                            <th>Faculty Approval</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($monthly_leaves)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    No leaves taken in <?php echo date('F Y', strtotime($selected_month . '-01')); ?>.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($monthly_leaves as $ml): ?>
                                <tr>
                                    <td><span class="badge bg-info text-dark fw-semibold"><?php echo htmlspecialchars($ml['leave_type']); ?></span></td>
                                    <td class="fw-bold text-dark"><?php echo format_date($ml['start_date']); ?></td>
                                    <td class="fw-bold text-dark"><?php echo format_date($ml['end_date']); ?></td>
                                    <td><span class="badge bg-dark"><?php echo $ml['total_days']; ?> Day(s)</span></td>
                                    <td class="text-secondary"><?php echo htmlspecialchars($ml['reason']); ?></td>
                                    <td>
                                        <span class="badge badge-status <?php echo get_badge_class($ml['status']); ?>">
                                            <?php echo strtoupper($ml['status']); ?>
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

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    $('.btn-mark-received').on('click', function() {
        const btn = $(this);
        const notificationId = btn.data('id');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

        $.ajax({
            url: '<?php echo BASE_URL; ?>/ajax/handler.php',
            type: 'POST',
            data: {
                action: 'mark_parent_notification_received',
                notification_id: notificationId
            },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    $('#tick_cell_' + notificationId).html(
                        '<div class="d-flex align-items-center gap-1 text-success fw-bold">' +
                        '<i class="bi bi-check-circle-fill fs-5 text-success"></i>' +
                        '<span>Received</span></div>' +
                        '<small class="text-muted d-block" style="font-size:0.72rem;">Ack: ' + res.received_at + '</small>'
                    );
                    
                    // Update pending stat count
                    let curCount = parseInt($('#stat_unack_count').text()) || 0;
                    if (curCount > 0) {
                        $('#stat_unack_count').text(curCount - 1);
                    }
                } else {
                    alert(res.message || 'Error updating status');
                    btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i> Mark as Received');
                }
            },
            error: function() {
                alert('Connection error. Please try again.');
                btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i> Mark as Received');
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
