<?php
/**
 * CampusGuardian - Staff Dashboard
 */
require_once __DIR__ . '/../includes/auth_check.php';
check_auth([ROLE_STAFF]);

$page_title = "Faculty Staff Dashboard";
$db = Database::getConnection();
$dept_id = $_SESSION['department_id'] ?? 0;

// Metric queries for department
$pending_late = $db->query("SELECT COUNT(*) FROM late_entries l JOIN students s ON l.student_id = s.id WHERE s.department_id = $dept_id AND l.staff_approval = 'pending'")->fetchColumn();
$pending_leave = $db->query("SELECT COUNT(*) FROM leave_requests l JOIN students s ON l.student_id = s.id WHERE s.department_id = $dept_id AND l.staff_approval = 'pending'")->fetchColumn();
$pending_half = $db->query("SELECT COUNT(*) FROM half_day_permissions h JOIN students s ON h.student_id = s.id WHERE s.department_id = $dept_id AND h.staff_approval = 'pending'")->fetchColumn();

// Fetch Pending Requests Queue for Department
$stmt_lates = $db->query("SELECT l.id, 'late_entry' as request_type, 'Late Arrival' as title, s.name as student_name, s.register_number, l.date, l.arrival_time as detail, l.reason 
    FROM late_entries l JOIN students s ON l.student_id = s.id 
    WHERE s.department_id = $dept_id AND l.staff_approval = 'pending' ORDER BY l.id DESC LIMIT 10")->fetchAll();

$stmt_leaves = $db->query("SELECT lr.id, 'leave_request' as request_type, lr.leave_type as title, s.name as student_name, s.register_number, lr.start_date as date, CONCAT(lr.total_days, ' Day(s)') as detail, lr.reason 
    FROM leave_requests lr JOIN students s ON lr.student_id = s.id 
    WHERE s.department_id = $dept_id AND lr.staff_approval = 'pending' ORDER BY lr.id DESC LIMIT 10")->fetchAll();

$pending_queue = array_merge($stmt_lates, $stmt_leaves);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div id="content">
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container-fluid p-4">
        <?php echo get_flash(); ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Faculty Staff Dashboard</h3>
                <p class="text-muted mb-0">Department of <?php echo htmlspecialchars($_SESSION['dept_name']); ?> Approval Management.</p>
            </div>
            <a href="requests.php" class="btn btn-primary shadow-sm"><i class="bi bi-inbox me-1"></i> View All Pending Queue</a>
        </div>

        <!-- Stat Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card card-stat p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fw-medium small text-uppercase">Pending Late Entries</span>
                            <h2 class="fw-bold text-warning mb-0 mt-1"><?php echo $pending_late; ?></h2>
                        </div>
                        <div class="stat-icon bg-warning-soft"><i class="bi bi-clock-history"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stat p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fw-medium small text-uppercase">Pending Leaves</span>
                            <h2 class="fw-bold text-primary mb-0 mt-1"><?php echo $pending_leave; ?></h2>
                        </div>
                        <div class="stat-icon bg-primary-soft"><i class="bi bi-calendar-event"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stat p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fw-medium small text-uppercase">Pending Half-Days</span>
                            <h2 class="fw-bold text-info mb-0 mt-1"><?php echo $pending_half; ?></h2>
                        </div>
                        <div class="stat-icon bg-info-soft"><i class="bi bi-hourglass-split"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Approvals Queue -->
        <div class="card border-0 shadow-sm p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-check2-square text-primary me-2"></i> Faculty Verification Queue</h5>
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Student Details</th>
                            <th>Date / Details</th>
                            <th>Reason</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pending_queue)): ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">No pending student requests require verification.</td></tr>
                        <?php else: ?>
                            <?php foreach ($pending_queue as $q): ?>
                                <tr>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($q['title']); ?></span></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?php echo htmlspecialchars($q['student_name']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($q['register_number']); ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-medium text-dark"><?php echo format_date($q['date']); ?></div>
                                        <small class="text-primary"><?php echo htmlspecialchars($q['detail']); ?></small>
                                    </td>
                                    <td class="small text-muted" style="max-width:260px;"><?php echo htmlspecialchars($q['reason']); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-success btn-action-modal me-1" data-id="<?php echo $q['id']; ?>" data-type="<?php echo $q['request_type']; ?>" data-action="approved" data-student="<?php echo htmlspecialchars($q['student_name']); ?>">
                                            <i class="bi bi-check-lg"></i> Approve
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger btn-action-modal" data-id="<?php echo $q['id']; ?>" data-type="<?php echo $q['request_type']; ?>" data-action="rejected" data-student="<?php echo htmlspecialchars($q['student_name']); ?>">
                                            <i class="bi bi-x-lg"></i> Reject
                                        </button>
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

<!-- Modal for Action Remarks -->
<div class="modal fade" id="approvalActionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-header-title fw-bold mb-0">Faculty Approval Action</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formApprovalAction">
                <input type="hidden" name="action" value="process_approval">
                <input type="hidden" name="request_id" id="modal_request_id">
                <input type="hidden" name="request_type" id="modal_request_type">
                <input type="hidden" name="status" id="modal_action">
                
                <div class="modal-body p-4">
                    <p class="mb-2">Student: <strong id="modal_student_name"></strong></p>
                    <p class="mb-3">Action: <span class="badge" id="modal_action_label"></span></p>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Faculty Remarks / Comments</label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="Enter remarks (e.g. Bus ticket verified, medical slip checked)..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitAction">Confirm Action</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
