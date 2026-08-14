<?php
/**
 * CampusGuardian - Parent Notifications Page
 */
require_once __DIR__ . '/../includes/auth_check.php';
check_auth([ROLE_PARENT]);

$page_title = "Parent Leave Notifications";
$db = Database::getConnection();

$parent_email = $_SESSION['parent_email'] ?? '';
$student_id = $_SESSION['student_id'] ?? 0;
$user_id = $_SESSION['user_id'] ?? 0;

if ($user_id) {
    $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0")->execute([$user_id]);
}

// Fetch Student / Ward details
$stmt_st = $db->prepare("SELECT s.*, d.dept_name, d.dept_code FROM students s JOIN departments d ON s.department_id = d.id WHERE s.id = ?");
$stmt_st->execute([$student_id]);
$ward = $stmt_st->fetch();

$filter = sanitize($_GET['filter'] ?? 'all');
$sql = "SELECT pn.*, lr.leave_type, lr.start_date, lr.end_date, lr.total_days, lr.status as leave_status FROM parent_notifications pn LEFT JOIN leave_requests lr ON pn.leave_request_id = lr.id WHERE pn.student_id = ?";

if ($filter === 'pending') {
    $sql .= " AND pn.status = 'sent'";
} elseif ($filter === 'received') {
    $sql .= " AND pn.status = 'received'";
}
$sql .= " ORDER BY pn.id DESC";

$stmt_pn = $db->prepare($sql);
$stmt_pn->execute([$student_id]);
$notifications = $stmt_pn->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div id="content">
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container-fluid p-4">
        <?php echo get_flash(); ?>

        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 border-bottom pb-3">
            <div>
                <h3 class="fw-bold mb-1"><i class="bi bi-bell-fill text-primary me-2"></i> Leave Notifications</h3>
                <p class="text-muted mb-0">Official leave alerts and acknowledgements for ward: <strong><?php echo htmlspecialchars($ward['name'] ?? 'Ward'); ?></strong></p>
            </div>
            <div class="btn-group mt-2 mt-md-0" role="group">
                <a href="notifications.php?filter=all" class="btn btn-outline-primary btn-sm <?php echo $filter === 'all' ? 'active' : ''; ?>">All Alerts</a>
                <a href="notifications.php?filter=pending" class="btn btn-outline-primary btn-sm <?php echo $filter === 'pending' ? 'active' : ''; ?>">Pending Ack</a>
                <a href="notifications.php?filter=received" class="btn btn-outline-primary btn-sm <?php echo $filter === 'received' ? 'active' : ''; ?>">Received</a>
            </div>
        </div>

        <div class="card border-0 shadow-sm p-4">
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Alert Details</th>
                            <th>Leave Dates</th>
                            <th>Total Days</th>
                            <th>Faculty Approval</th>
                            <th>Parent Received Tick</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($notifications)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="bi bi-bell-slash fs-3 d-block mb-2"></i> No notifications matching this filter.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($notifications as $pn): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($pn['title']); ?></div>
                                        <div class="text-muted small"><?php echo htmlspecialchars($pn['message']); ?></div>
                                        <small class="text-secondary" style="font-size:0.75rem;"><i class="bi bi-clock me-1"></i> Sent on <?php echo format_date($pn['created_at']); ?> at <?php echo format_time($pn['created_at']); ?></small>
                                    </td>
                                    <td>
                                        <?php if ($pn['start_date']): ?>
                                            <span class="fw-semibold text-dark"><?php echo format_date($pn['start_date']); ?></span> to <span class="fw-semibold text-dark"><?php echo format_date($pn['end_date']); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo $pn['total_days'] ?? 1; ?> Day(s)</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-status <?php echo get_badge_class($pn['leave_status'] ?? 'pending'); ?>">
                                            <?php echo strtoupper($pn['leave_status'] ?? 'pending'); ?>
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
