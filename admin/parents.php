<?php
/**
 * CampusGuardian - Admin Parent Monitoring & Leave Acknowledgement Tracking
 */
require_once __DIR__ . '/../includes/auth_check.php';
check_auth([ROLE_ADMIN]);

$page_title = "Parent Monitoring & Leave Acknowledgements";
$db = Database::getConnection();

$search = sanitize($_GET['search'] ?? '');
$dept_filter = (int)($_GET['dept_id'] ?? 0);
$ack_filter = sanitize($_GET['ack_status'] ?? '');

// Fetch Departments for filter
$departments = $db->query("SELECT * FROM departments ORDER BY dept_name ASC")->fetchAll();

// Base SQL query
$sql = "SELECT pn.*, s.name as student_name, s.register_number, s.parent_name, s.parent_phone, s.parent_email, d.dept_code, lr.leave_type, lr.start_date, lr.end_date, lr.total_days, lr.status as leave_status 
        FROM parent_notifications pn 
        JOIN students s ON pn.student_id = s.id 
        JOIN departments d ON s.department_id = d.id 
        LEFT JOIN leave_requests lr ON pn.leave_request_id = lr.id 
        WHERE 1=1";

$params = [];

if (!empty($search)) {
    $sql .= " AND (s.name LIKE ? OR s.register_number LIKE ? OR s.parent_name LIKE ? OR s.parent_email LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($dept_filter > 0) {
    $sql .= " AND s.department_id = ?";
    $params[] = $dept_filter;
}

if (!empty($ack_filter)) {
    $sql .= " AND pn.status = ?";
    $params[] = $ack_filter;
}

$sql .= " ORDER BY pn.id DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$records = $stmt->fetchAll();

// Total Stats
$total_alerts = count($records);
$received_count = 0;
$pending_count = 0;

foreach ($records as $r) {
    if ($r['status'] === 'received') {
        $received_count++;
    } else {
        $pending_count++;
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div id="content">
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container-fluid p-4">
        <?php echo get_flash(); ?>

        <!-- Header -->
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 border-bottom pb-3">
            <div>
                <h3 class="fw-bold mb-1"><i class="bi bi-people-fill text-primary me-2"></i> Parent Monitoring & Leave Acknowledgements</h3>
                <p class="text-muted mb-0">Track parent contact records, leave notification alerts, and parent "Received" tick acknowledgements.</p>
            </div>
        </div>

        <!-- Overview Stat Widgets -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-3 h-100">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary-soft text-primary rounded-circle p-3 fs-3"><i class="bi bi-bell"></i></div>
                        <div>
                            <div class="text-muted small fw-semibold">Total Leave Alerts Sent</div>
                            <h3 class="fw-bold mb-0 text-dark"><?php echo $total_alerts; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-3 h-100">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-success-soft text-success rounded-circle p-3 fs-3"><i class="bi bi-check-circle-fill"></i></div>
                        <div>
                            <div class="text-muted small fw-semibold">Parent Received (Ticked ✔)</div>
                            <h3 class="fw-bold mb-0 text-success"><?php echo $received_count; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm p-3 h-100">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-warning-soft text-warning rounded-circle p-3 fs-3"><i class="bi bi-clock-history"></i></div>
                        <div>
                            <div class="text-muted small fw-semibold">Pending Parent Tick</div>
                            <h3 class="fw-bold mb-0 text-warning"><?php echo $pending_count; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Form -->
        <div class="card border-0 shadow-sm p-3 mb-4 bg-light">
            <form method="GET" class="row g-3 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search student or parent..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="dept_id" class="form-select">
                        <option value="0">All Departments</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?php echo $d['id']; ?>" <?php echo $dept_filter === $d['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($d['dept_name']); ?> (<?php echo $d['dept_code']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="ack_status" class="form-select">
                        <option value="">All Parent Tick Status</option>
                        <option value="received" <?php echo $ack_filter === 'received' ? 'selected' : ''; ?>>✔ Received Only</option>
                        <option value="sent" <?php echo $ack_filter === 'sent' ? 'selected' : ''; ?>>⏳ Pending Tick Only</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 fw-semibold">Filter</button>
                    <a href="parents.php" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>

        <!-- Main Records Table -->
        <div class="card border-0 shadow-sm p-4">
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Student & Department</th>
                            <th>Parent Details</th>
                            <th>Leave Application & Dates</th>
                            <th>Faculty Status</th>
                            <th>Parent Received Tick Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($records)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    No parent leave notifications found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($records as $r): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($r['student_name']); ?></div>
                                        <div class="text-muted small">Reg: <span class="badge bg-secondary"><?php echo htmlspecialchars($r['register_number']); ?></span> | <span class="badge bg-info text-dark"><?php echo htmlspecialchars($r['dept_code']); ?></span></div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark"><i class="bi bi-person me-1"></i> <?php echo htmlspecialchars($r['parent_name']); ?></div>
                                        <div class="text-muted small"><i class="bi bi-telephone me-1"></i> <?php echo htmlspecialchars($r['parent_phone']); ?></div>
                                        <div class="text-secondary small" style="font-size:0.75rem;"><i class="bi bi-envelope me-1"></i> <?php echo htmlspecialchars($r['parent_email']); ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-primary mb-1"><?php echo htmlspecialchars($r['title']); ?></div>
                                        <div class="text-muted small mb-1">
                                            <?php if ($r['start_date']): ?>
                                                <?php echo format_date($r['start_date']); ?> to <?php echo format_date($r['end_date']); ?> (<strong><?php echo $r['total_days']; ?> Days</strong>)
                                            <?php else: ?>
                                                Leave application alert
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted" style="font-size:0.72rem;">Alert Sent: <?php echo format_date($r['created_at']); ?> @ <?php echo format_time($r['created_at']); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge badge-status <?php echo get_badge_class($r['leave_status'] ?? 'pending'); ?>">
                                            <?php echo strtoupper($r['leave_status'] ?? 'pending'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($r['status'] === 'received'): ?>
                                            <span class="badge bg-success-soft text-success p-2 fs-6 fw-bold border border-success">
                                                <i class="bi bi-check-circle-fill me-1"></i> ✔ Received
                                            </span>
                                            <div class="text-muted small mt-1" style="font-size:0.72rem;">
                                                Ack Time: <?php echo format_date($r['received_at']); ?> <?php echo format_time($r['received_at']); ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge bg-warning-soft text-dark p-2 fs-6 fw-semibold border border-warning">
                                                <i class="bi bi-hourglass-split me-1"></i> ⏳ Sent (Pending Parent Tick)
                                            </span>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
