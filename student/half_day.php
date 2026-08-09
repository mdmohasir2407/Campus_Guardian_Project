<?php
/**
 * CampusGuardian - Apply Half Day Permission
 */
require_once __DIR__ . '/../includes/auth_check.php';
check_auth([ROLE_STUDENT]);

$page_title = "Half Day Permission";
$db = Database::getConnection();
$student_id = $_SESSION['student_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = sanitize($_POST['date'] ?? date('Y-m-d'));
    $permission_time = sanitize($_POST['permission_time'] ?? '12:00');
    $expected_return_time = sanitize($_POST['expected_return_time'] ?? '16:00');
    $reason = sanitize($_POST['reason'] ?? '');

    try {
        $stmt = $db->prepare("INSERT INTO half_day_permissions (student_id, date, permission_time, expected_return_time, reason, status, staff_approval, hod_approval) VALUES (?, ?, ?, ?, ?, 'pending', 'pending', 'pending')");
        $stmt->execute([$student_id, $date, $permission_time, $expected_return_time, $reason]);

        set_flash('success', 'Half-day permission request submitted successfully.');
        redirect('student/status.php');
    } catch (Exception $e) {
        set_flash('danger', 'Error submitting permission request: ' . $e->getMessage());
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div id="content">
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container-fluid p-4">
        <?php echo get_flash(); ?>

        <div class="max-w-700 mx-auto" style="max-width:700px; margin: 0 auto;">
            <div class="card border-0 shadow-sm p-4">
                <div class="d-flex align-items-center gap-3 mb-4 border-bottom pb-3">
                    <div class="bg-info-soft stat-icon fs-3"><i class="bi bi-hourglass-split"></i></div>
                    <div>
                        <h4 class="fw-bold mb-0">Apply Half-Day Outpass Permission</h4>
                        <p class="text-muted mb-0 small">Request temporary campus exit & return clearance.</p>
                    </div>
                </div>

                <form action="half_day.php" method="POST">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Permission Date *</label>
                            <input type="date" name="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Campus Exit Time *</label>
                            <input type="time" name="permission_time" class="form-control" value="12:30" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Expected Return Time *</label>
                            <input type="time" name="expected_return_time" class="form-control" value="16:00" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Reason for Outpass *</label>
                            <textarea name="reason" class="form-control" rows="3" placeholder="Specify official, medical, or bank work reason..." required></textarea>
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm">
                                <i class="bi bi-check-circle me-1"></i> Submit Outpass Request
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
