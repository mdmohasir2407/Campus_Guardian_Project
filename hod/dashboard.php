<?php
/**
 * CampusGuardian - HOD Executive Dashboard
 */
require_once __DIR__ . '/../includes/auth_check.php';
check_auth([ROLE_HOD]);

$page_title = "HOD Dashboard";
$db = Database::getConnection();
$dept_id = $_SESSION['department_id'] ?? 0;
$today = date('Y-m-d');

// Department Metrics
$total_dept_students = $db->query("SELECT COUNT(*) FROM students WHERE department_id = $dept_id")->fetchColumn();
$pending_hod_approvals = $db->query("SELECT COUNT(*) FROM leave_requests l JOIN students s ON l.student_id = s.id WHERE s.department_id = $dept_id AND l.hod_approval = 'pending'")->fetchColumn() +
                         $db->query("SELECT COUNT(*) FROM late_entries l JOIN students s ON l.student_id = s.id WHERE s.department_id = $dept_id AND l.hod_approval = 'pending'")->fetchColumn();

// Escalated requests requiring HOD action
$stmt_lates = $db->query("SELECT l.id, 'late_entry' as request_type, 'Late Arrival' as title, s.name as student_name, s.register_number, l.date, l.arrival_time as detail, l.reason, l.staff_approval 
    FROM late_entries l JOIN students s ON l.student_id = s.id 
    WHERE s.department_id = $dept_id AND l.hod_approval = 'pending' ORDER BY l.id DESC LIMIT 10")->fetchAll();

$stmt_leaves = $db->query("SELECT lr.id, 'leave_request' as request_type, lr.leave_type as title, s.name as student_name, s.register_number, lr.start_date as date, CONCAT(lr.total_days, ' Day(s)') as detail, lr.reason, lr.staff_approval 
    FROM leave_requests lr JOIN students s ON lr.student_id = s.id 
    WHERE s.department_id = $dept_id AND lr.hod_approval = 'pending' ORDER BY lr.id DESC LIMIT 10")->fetchAll();

$hod_queue = array_merge($stmt_lates, $stmt_leaves);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div id="content">
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container-fluid p-4">
        <?php echo get_flash(); ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Head of Department (HOD) Dashboard</h3>
                <p class="text-muted mb-0">Executive oversight for <?php echo htmlspecialchars($_SESSION['dept_name']); ?>.</p>
            </div>
            <div>
                <a href="approvals.php" class="btn btn-warning text-dark fw-semibold shadow-sm"><i class="bi bi-check2-circle me-1"></i> Pending Final Approvals (<?php echo $pending_hod_approvals; ?>)</a>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card card-stat p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fw-medium small text-uppercase">Department Strength</span>
                            <h2 class="fw-bold text-dark mb-0 mt-1"><?php echo number_format($total_dept_students); ?></h2>
                        </div>
                        <div class="stat-icon bg-primary-soft"><i class="bi bi-people-fill"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stat p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fw-medium small text-uppercase">Escalated Approvals Queue</span>
                            <h2 class="fw-bold text-warning mb-0 mt-1"><?php echo $pending_hod_approvals; ?></h2>
                        </div>
                        <div class="stat-icon bg-warning-soft"><i class="bi bi-hourglass-split"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stat p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fw-medium small text-uppercase">System Status</span>
                            <h2 class="fw-bold text-success mb-0 mt-1">ACTIVE</h2>
                        </div>
                        <div class="stat-icon bg-success-soft"><i class="bi bi-shield-check"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Escalated Queue Table -->
        <div class="card border-0 shadow-sm p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-shield-lock-fill text-warning me-2"></i> HOD Final Approval Escalation Queue</h5>
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Student</th>
                            <th>Dates / Time</th>
                            <th>Reason</th>
                            <th>Staff Recommendation</th>
                            <th>HOD Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($hod_queue)): ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted">No pending approvals requiring HOD authorization.</td></tr>
                        <?php else: ?>
                            <?php foreach ($hod_queue as $hq): ?>
                                <tr>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($hq['title']); ?></span></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?php echo htmlspecialchars($hq['student_name']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($hq['register_number']); ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-medium text-dark"><?php echo format_date($hq['date']); ?></div>
                                        <small class="text-primary"><?php echo htmlspecialchars($hq['detail']); ?></small>
                                    </td>
                                    <td class="small text-muted" style="max-width:240px;"><?php echo htmlspecialchars($hq['reason']); ?></td>
                                    <td><span class="badge badge-status <?php echo get_badge_class($hq['staff_approval']); ?>"><?php echo strtoupper($hq['staff_approval']); ?></span></td>
                                    <td>
                                        <button class="btn btn-sm btn-success btn-action-modal me-1" data-id="<?php echo $hq['id']; ?>" data-type="<?php echo $hq['request_type']; ?>" data-action="approved" data-student="<?php echo htmlspecialchars($hq['student_name']); ?>">
                                            <i class="bi bi-check-circle"></i> Grant Approval
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger btn-action-modal" data-id="<?php echo $hq['id']; ?>" data-type="<?php echo $hq['request_type']; ?>" data-action="rejected" data-student="<?php echo htmlspecialchars($hq['student_name']); ?>">
                                            <i class="bi bi-x-circle"></i> Reject
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
                <h5 class="modal-header-title fw-bold mb-0">HOD Final Approval Action</h5>
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
                        <label class="form-label fw-semibold">HOD Executive Decision Remarks</label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="Enter HOD remarks..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitAction">Submit Final Decision</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
