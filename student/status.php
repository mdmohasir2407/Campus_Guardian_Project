<?php
/**
 * CampusGuardian - Student Request Status Tracking Timeline
 */
require_once __DIR__ . '/../includes/auth_check.php';
check_auth([ROLE_STUDENT]);

$page_title = "Request Approval Status";
$db = Database::getConnection();
$student_id = $_SESSION['student_id'] ?? 0;

$late_entries = $db->query("SELECT * FROM late_entries WHERE student_id = $student_id ORDER BY id DESC")->fetchAll();
$leave_requests = $db->query("SELECT * FROM leave_requests WHERE student_id = $student_id ORDER BY id DESC")->fetchAll();
$half_days = $db->query("SELECT * FROM half_day_permissions WHERE student_id = $student_id ORDER BY id DESC")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div id="content">
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container-fluid p-4">
        <?php echo get_flash(); ?>

        <div class="mb-4">
            <h3 class="fw-bold mb-1">Request Approval Status Timeline</h3>
            <p class="text-muted mb-0">Track multi-tier approval stages (Faculty Staff &rarr; HOD Final Approval).</p>
        </div>

        <ul class="nav nav-pills mb-4" id="statusTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active fw-semibold" id="late-tab" data-bs-toggle="tab" data-bs-target="#late-pane" type="button"><i class="bi bi-clock-history me-1"></i> Late Entries (<?php echo count($late_entries); ?>)</button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-semibold" id="leave-tab" data-bs-toggle="tab" data-bs-target="#leave-pane" type="button"><i class="bi bi-calendar-check me-1"></i> Leave Applications (<?php echo count($leave_requests); ?>)</button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-semibold" id="half-tab" data-bs-toggle="tab" data-bs-target="#half-pane" type="button"><i class="bi bi-hourglass-split me-1"></i> Half-Day Outpass (<?php echo count($half_days); ?>)</button>
            </li>
        </ul>

        <div class="tab-content" id="statusTabContent">
            <!-- Late Entries Pane -->
            <div class="tab-pane fade show active" id="late-pane" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Date & Arrival</th>
                                    <th>Reason</th>
                                    <th>Staff Verification</th>
                                    <th>HOD Approval</th>
                                    <th>Final Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($late_entries)): ?>
                                    <tr><td colspan="5" class="text-center py-4 text-muted">No late entry submissions found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($late_entries as $l): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark"><?php echo format_date($l['date']); ?></div>
                                                <small class="text-danger"><?php echo format_time($l['arrival_time']); ?> (<?php echo $l['late_minutes']; ?> min late)</small>
                                            </td>
                                            <td class="small text-muted" style="max-width:250px;"><?php echo htmlspecialchars($l['reason']); ?></td>
                                            <td>
                                                <span class="badge badge-status <?php echo get_badge_class($l['staff_approval']); ?>"><?php echo strtoupper($l['staff_approval']); ?></span>
                                                <?php if (!empty($l['staff_remarks'])): ?>
                                                    <div class="text-xs text-muted mt-1"><em>"<?php echo htmlspecialchars($l['staff_remarks']); ?>"</em></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-status <?php echo get_badge_class($l['hod_approval']); ?>"><?php echo strtoupper($l['hod_approval']); ?></span>
                                                <?php if (!empty($l['hod_remarks'])): ?>
                                                    <div class="text-xs text-muted mt-1"><em>"<?php echo htmlspecialchars($l['hod_remarks']); ?>"</em></div>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="badge badge-status <?php echo get_badge_class($l['status']); ?> px-3 py-2"><?php echo strtoupper($l['status']); ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Leave Requests Pane -->
            <div class="tab-pane fade" id="leave-pane" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Leave Type & Dates</th>
                                    <th>Duration</th>
                                    <th>Reason</th>
                                    <th>Staff Level</th>
                                    <th>HOD Level</th>
                                    <th>Final Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($leave_requests)): ?>
                                    <tr><td colspan="6" class="text-center py-4 text-muted">No leave requests logged.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($leave_requests as $lr): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-info text-dark mb-1"><?php echo htmlspecialchars($lr['leave_type']); ?></span>
                                                <div class="small fw-semibold"><?php echo format_date($lr['start_date']); ?> to <?php echo format_date($lr['end_date']); ?></div>
                                            </td>
                                            <td><strong><?php echo $lr['total_days']; ?> Day(s)</strong></td>
                                            <td class="small text-muted" style="max-width:250px;"><?php echo htmlspecialchars($lr['reason']); ?></td>
                                            <td><span class="badge badge-status <?php echo get_badge_class($lr['staff_approval']); ?>"><?php echo strtoupper($lr['staff_approval']); ?></span></td>
                                            <td><span class="badge badge-status <?php echo get_badge_class($lr['hod_approval']); ?>"><?php echo strtoupper($lr['hod_approval']); ?></span></td>
                                            <td><span class="badge badge-status <?php echo get_badge_class($lr['status']); ?> px-3 py-2"><?php echo strtoupper($lr['status']); ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Half Day Pane -->
            <div class="tab-pane fade" id="half-pane" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Timings</th>
                                    <th>Reason</th>
                                    <th>Staff Approval</th>
                                    <th>HOD Approval</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($half_days)): ?>
                                    <tr><td colspan="6" class="text-center py-4 text-muted">No half-day outpass requests logged.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($half_days as $hd): ?>
                                        <tr>
                                            <td><div class="fw-bold"><?php echo format_date($hd['date']); ?></div></td>
                                            <td><span class="text-primary fw-medium"><?php echo format_time($hd['permission_time']); ?></span> &rarr; <span class="text-success fw-medium"><?php echo format_time($hd['expected_return_time']); ?></span></td>
                                            <td class="small text-muted"><?php echo htmlspecialchars($hd['reason']); ?></td>
                                            <td><span class="badge badge-status <?php echo get_badge_class($hd['staff_approval']); ?>"><?php echo strtoupper($hd['staff_approval']); ?></span></td>
                                            <td><span class="badge badge-status <?php echo get_badge_class($hd['hod_approval']); ?>"><?php echo strtoupper($hd['hod_approval']); ?></span></td>
                                            <td><span class="badge badge-status <?php echo get_badge_class($hd['status']); ?>"><?php echo strtoupper($hd['status']); ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
