<?php
/**
 * CampusGuardian - Staff Processed Approval History
 */
require_once __DIR__ . '/../includes/auth_check.php';
check_auth([ROLE_STAFF]);

$page_title = "Approval History";
$db = Database::getConnection();
$dept_id = $_SESSION['department_id'] ?? 0;

$history_lates = $db->query("SELECT l.*, s.name as student_name, s.register_number FROM late_entries l JOIN students s ON l.student_id = s.id WHERE s.department_id = $dept_id AND l.staff_approval != 'pending' ORDER BY l.id DESC")->fetchAll();
$history_leaves = $db->query("SELECT lr.*, s.name as student_name, s.register_number FROM leave_requests lr JOIN students s ON lr.student_id = s.id WHERE s.department_id = $dept_id AND lr.staff_approval != 'pending' ORDER BY lr.id DESC")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div id="content">
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container-fluid p-4">
        <?php echo get_flash(); ?>

        <div class="mb-4">
            <h3 class="fw-bold mb-1">Faculty Approval History</h3>
            <p class="text-muted mb-0">Record of all requests processed by staff.</p>
        </div>

        <div class="card border-0 shadow-sm p-4 mb-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-clock-history text-secondary me-2"></i> Processed Late Arrival Verification History</h5>
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Date</th>
                            <th>Staff Status</th>
                            <th>Staff Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($history_lates)): ?>
                            <tr><td colspan="4" class="text-center py-3 text-muted">No processed late entry history found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($history_lates as $hl): ?>
                                <tr>
                                    <td class="fw-semibold text-dark"><?php echo htmlspecialchars($hl['student_name']); ?> (<?php echo $hl['register_number']; ?>)</td>
                                    <td><?php echo format_date($hl['date']); ?></td>
                                    <td><span class="badge badge-status <?php echo get_badge_class($hl['staff_approval']); ?>"><?php echo strtoupper($hl['staff_approval']); ?></span></td>
                                    <td class="small text-muted"><?php echo htmlspecialchars($hl['staff_remarks'] ?? '-'); ?></td>
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
