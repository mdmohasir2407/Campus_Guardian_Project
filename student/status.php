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

<<<<<<< HEAD
// Compile all requests into a unified chronological history feed (newest first)
$all_requests = [];
foreach ($late_entries as $le) {
    $all_requests[] = [
        'type' => 'Late Entry',
        'badge_bg' => 'bg-danger-soft text-danger border border-danger-subtle',
        'icon' => 'bi-clock-fill',
        'date_info' => format_date($le['date']) . ' (' . format_time($le['arrival_time']) . ')',
        'details' => $le['late_minutes'] . ' minutes late. Reason: ' . htmlspecialchars($le['reason']),
        'staff_status' => $le['staff_approval'],
        'staff_remarks' => $le['staff_remarks'],
        'hod_status' => $le['hod_approval'],
        'hod_remarks' => $le['hod_remarks'],
        'status' => $le['status'],
        'created_at' => $le['created_at']
    ];
}
foreach ($leave_requests as $lr) {
    $all_requests[] = [
        'type' => 'Leave Application (' . htmlspecialchars($lr['leave_type']) . ')',
        'badge_bg' => 'bg-info-soft text-info border border-info-subtle',
        'icon' => 'bi-calendar-event-fill',
        'date_info' => format_date($lr['start_date']) . ' to ' . format_date($lr['end_date']),
        'details' => $lr['total_days'] . ' Day(s). Reason: ' . htmlspecialchars($lr['reason']),
        'staff_status' => $lr['staff_approval'],
        'staff_remarks' => $lr['staff_remarks'],
        'hod_status' => $lr['hod_approval'],
        'hod_remarks' => $lr['hod_remarks'],
        'status' => $lr['status'],
        'created_at' => $lr['created_at']
    ];
}
foreach ($half_days as $hd) {
    $all_requests[] = [
        'type' => 'Half-Day Outpass',
        'badge_bg' => 'bg-primary-soft text-primary border border-primary-subtle',
        'icon' => 'bi-hourglass-split',
        'date_info' => format_date($hd['date']) . ' (' . format_time($hd['permission_time']) . ' to ' . format_time($hd['expected_return_time']) . ')',
        'details' => 'Outpass requested. Reason: ' . htmlspecialchars($hd['reason']),
        'staff_status' => $hd['staff_approval'],
        'staff_remarks' => $hd['staff_remarks'],
        'hod_status' => $hd['hod_approval'],
        'hod_remarks' => $hd['hod_remarks'],
        'status' => $hd['status'],
        'created_at' => $hd['created_at']
    ];
}

// Sort all requests by created_at DESC (Newest on top)
usort($all_requests, function ($a, $b) {
    return strcmp($b['created_at'], $a['created_at']);
});

=======
>>>>>>> 46e8e96fd34928274cd800f4a3cc75a72bc4109b
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div id="content">
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container-fluid p-4">
        <?php echo get_flash(); ?>

        <div class="mb-4">
            <h3 class="fw-bold mb-1">Request Approval Status Timeline</h3>
<<<<<<< HEAD
            <p class="text-muted mb-0"> (Faculty Staff &rarr; HOD Final Approval).</p>
=======
            <p class="text-muted mb-0">Track multi-tier approval stages (Faculty Staff &rarr; HOD Final Approval).</p>
>>>>>>> 46e8e96fd34928274cd800f4a3cc75a72bc4109b
        </div>

        <ul class="nav nav-pills mb-4" id="statusTabs" role="tablist">
            <li class="nav-item">
<<<<<<< HEAD
                <button class="nav-link active fw-semibold" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-pane" type="button"><i class="bi bi-list-task me-1"></i> All Requests (<?php echo count($all_requests); ?>)</button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-semibold" id="late-tab" data-bs-toggle="tab" data-bs-target="#late-pane" type="button"><i class="bi bi-clock-history me-1"></i> Late Entries (<?php echo count($late_entries); ?>)</button>
=======
                <button class="nav-link active fw-semibold" id="late-tab" data-bs-toggle="tab" data-bs-target="#late-pane" type="button"><i class="bi bi-clock-history me-1"></i> Late Entries (<?php echo count($late_entries); ?>)</button>
>>>>>>> 46e8e96fd34928274cd800f4a3cc75a72bc4109b
            </li>
            <li class="nav-item">
                <button class="nav-link fw-semibold" id="leave-tab" data-bs-toggle="tab" data-bs-target="#leave-pane" type="button"><i class="bi bi-calendar-check me-1"></i> Leave Applications (<?php echo count($leave_requests); ?>)</button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-semibold" id="half-tab" data-bs-toggle="tab" data-bs-target="#half-pane" type="button"><i class="bi bi-hourglass-split me-1"></i> Half-Day Outpass (<?php echo count($half_days); ?>)</button>
            </li>
        </ul>

        <div class="tab-content" id="statusTabContent">
<<<<<<< HEAD
            <!-- All Requests Pane (Chronological Timeline) -->
            <div class="tab-pane fade show active" id="all-pane" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Request Type & Date</th>
                                    <th>Details</th>
                                    <th>Staff Verification</th>
                                    <th>HOD Approval</th>
                                    <th>Final Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($all_requests)): ?>
                                    <tr><td colspan="5" class="text-center py-4 text-muted">No request history found. All requests will remain here permanently.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($all_requests as $req): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge rounded-circle p-2 <?php echo $req['badge_bg']; ?>">
                                                        <i class="bi <?php echo $req['icon']; ?> fs-6"></i>
                                                    </span>
                                                    <div>
                                                        <div class="fw-bold text-dark"><?php echo $req['type']; ?></div>
                                                        <small class="text-secondary"><?php echo $req['date_info']; ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="small text-muted" style="max-width:280px;"><?php echo $req['details']; ?></td>
                                            <td>
                                                <span class="badge badge-status <?php echo get_badge_class($req['staff_status']); ?>"><?php echo strtoupper($req['staff_status']); ?></span>
                                                <?php if (!empty($req['staff_remarks'])): ?>
                                                    <div class="text-xs text-muted mt-1"><em>"<?php echo $req['staff_remarks']; ?>"</em></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-status <?php echo get_badge_class($req['hod_status']); ?>"><?php echo strtoupper($req['hod_status']); ?></span>
                                                <?php if (!empty($req['hod_remarks'])): ?>
                                                    <div class="text-xs text-muted mt-1"><em>"<?php echo $req['hod_remarks']; ?>"</em></div>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="badge badge-status <?php echo get_badge_class($req['status']); ?> px-3 py-2"><?php echo strtoupper($req['status']); ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Late Entries Pane -->
            <div class="tab-pane fade" id="late-pane" role="tabpanel">
=======
            <!-- Late Entries Pane -->
            <div class="tab-pane fade show active" id="late-pane" role="tabpanel">
>>>>>>> 46e8e96fd34928274cd800f4a3cc75a72bc4109b
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
