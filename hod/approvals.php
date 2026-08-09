<?php
/**
 * CampusGuardian - HOD Approvals Page
 */
require_once __DIR__ . '/../includes/auth_check.php';
check_auth([ROLE_HOD]);

$page_title = "HOD Final Approvals";
$db = Database::getConnection();
$dept_id = $_SESSION['department_id'] ?? 0;

$lates = $db->query("SELECT l.*, s.name as student_name, s.register_number FROM late_entries l JOIN students s ON l.student_id = s.id WHERE s.department_id = $dept_id AND l.hod_approval = 'pending' ORDER BY l.id DESC")->fetchAll();
$leaves = $db->query("SELECT lr.*, s.name as student_name, s.register_number FROM leave_requests lr JOIN students s ON lr.student_id = s.id WHERE s.department_id = $dept_id AND lr.hod_approval = 'pending' ORDER BY lr.id DESC")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div id="content">
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container-fluid p-4">
        <?php echo get_flash(); ?>

        <div class="mb-4">
            <h3 class="fw-bold mb-1">Department Final Approvals</h3>
            <p class="text-muted mb-0">HOD final authorization for student late entries and leave applications.</p>
        </div>

        <div class="card border-0 shadow-sm p-4 mb-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-clock-history text-warning me-2"></i> Pending Late Entries (HOD Level)</h5>
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Date & Time</th>
                            <th>Delay</th>
                            <th>Staff Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($lates)): ?>
                            <tr><td colspan="5" class="text-center py-3 text-muted">No pending late entries.</td></tr>
                        <?php else: ?>
                            <?php foreach ($lates as $l): ?>
                                <tr>
                                    <td class="fw-semibold text-dark"><?php echo htmlspecialchars($l['student_name']); ?> (<?php echo $l['register_number']; ?>)</td>
                                    <td><?php echo format_date($l['date']); ?> @ <?php echo format_time($l['arrival_time']); ?></td>
                                    <td class="text-danger fw-bold"><?php echo $l['late_minutes']; ?> min</td>
                                    <td><span class="badge badge-status <?php echo get_badge_class($l['staff_approval']); ?>"><?php echo strtoupper($l['staff_approval']); ?></span></td>
                                    <td>
                                        <button class="btn btn-sm btn-success btn-action-modal me-1" data-id="<?php echo $l['id']; ?>" data-type="late_entry" data-action="approved" data-student="<?php echo htmlspecialchars($l['student_name']); ?>">Approve</button>
                                        <button class="btn btn-sm btn-outline-danger btn-action-modal" data-id="<?php echo $l['id']; ?>" data-type="late_entry" data-action="rejected" data-student="<?php echo htmlspecialchars($l['student_name']); ?>">Reject</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card border-0 shadow-sm p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-calendar-event text-primary me-2"></i> Pending Leave Applications (HOD Level)</h5>
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Leave Type</th>
                            <th>Dates</th>
                            <th>Staff Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($leaves)): ?>
                            <tr><td colspan="5" class="text-center py-3 text-muted">No pending leave requests.</td></tr>
                        <?php else: ?>
                            <?php foreach ($leaves as $lr): ?>
                                <tr>
                                    <td class="fw-semibold text-dark"><?php echo htmlspecialchars($lr['student_name']); ?> (<?php echo $lr['register_number']; ?>)</td>
                                    <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($lr['leave_type']); ?></span></td>
                                    <td><?php echo format_date($lr['start_date']); ?> to <?php echo format_date($lr['end_date']); ?></td>
                                    <td><span class="badge badge-status <?php echo get_badge_class($lr['staff_approval']); ?>"><?php echo strtoupper($lr['staff_approval']); ?></span></td>
                                    <td>
                                        <button class="btn btn-sm btn-success btn-action-modal me-1" data-id="<?php echo $lr['id']; ?>" data-type="leave_request" data-action="approved" data-student="<?php echo htmlspecialchars($lr['student_name']); ?>">Approve</button>
                                        <button class="btn btn-sm btn-outline-danger btn-action-modal" data-id="<?php echo $lr['id']; ?>" data-type="leave_request" data-action="rejected" data-student="<?php echo htmlspecialchars($lr['student_name']); ?>">Reject</button>
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
                <h5 class="modal-header-title fw-bold mb-0">HOD Approval Action</h5>
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
                        <label class="form-label fw-semibold">HOD Remarks</label>
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
