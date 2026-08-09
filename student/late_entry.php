<?php
/**
 * CampusGuardian - Submit Late Entry Request
 */
require_once __DIR__ . '/../includes/auth_check.php';
check_auth([ROLE_STUDENT]);

$page_title = "Submit Late Entry Request";
$db = Database::getConnection();
$student_id = $_SESSION['student_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = sanitize($_POST['date'] ?? date('Y-m-d'));
    $arrival_time = sanitize($_POST['arrival_time'] ?? '');
    $reason = sanitize($_POST['reason'] ?? '');

    // Calculate late minutes from college start time 09:00 AM
    $cutoff = get_db_setting('college_start_time', '09:00:00');
    $late_minutes = 0;
    
    if (!empty($arrival_time)) {
        $start_ts = strtotime($date . ' ' . $cutoff);
        $arr_ts = strtotime($date . ' ' . $arrival_time);
        if ($arr_ts > $start_ts) {
            $late_minutes = round(($arr_ts - $start_ts) / 60);
        }
    }

    // Photo attachment handler
    $photo_filename = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $up = upload_file('photo', UPLOAD_DIR_ATTACHMENTS, ['jpg', 'jpeg', 'png']);
        if ($up['status']) {
            $photo_filename = $up['filename'];
        }
    }

    try {
        $stmt = $db->prepare("INSERT INTO late_entries (student_id, date, arrival_time, late_minutes, reason, photo, status, staff_approval, hod_approval) VALUES (?, ?, ?, ?, ?, ?, 'pending', 'pending', 'pending')");
        $stmt->execute([$student_id, $date, $arrival_time, $late_minutes, $reason, $photo_filename]);

        set_flash('success', 'Late entry request submitted successfully! Pending faculty review.');
        redirect('student/status.php');

    } catch (Exception $e) {
        set_flash('danger', 'Error submitting late entry: ' . $e->getMessage());
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div id="content">
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container-fluid p-4">
        <?php echo get_flash(); ?>

        <div class="max-w-700 mx-auto">
            <div class="card border-0 shadow-sm p-4" style="max-width: 700px; margin: 0 auto;">
                <div class="d-flex align-items-center gap-3 mb-4 border-bottom pb-3">
                    <div class="bg-warning-soft stat-icon fs-3"><i class="bi bi-clock-history"></i></div>
                    <div>
                        <h4 class="fw-bold mb-0">Submit Late Arrival Record</h4>
                        <p class="text-muted mb-0 small">Official college start time is 09:00 AM.</p>
                    </div>
                </div>

                <form action="late_entry.php" method="POST" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date of Late Entry *</label>
                            <input type="date" name="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Actual Arrival Time *</label>
                            <input type="time" name="arrival_time" id="arrival_time" class="form-control" value="<?php echo date('H:i'); ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Reason for Delay *</label>
                            <textarea name="reason" class="form-control" rows="3" placeholder="Provide detailed explanation (e.g. Bus delay, medical emergency, heavy traffic)..." required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Supporting Photo / Slip (Optional)</label>
                            <input type="file" name="photo" class="form-control" accept="image/*">
                            <small class="text-muted">Upload proof like bus ticket, traffic receipt, or gate entry slip (Max 5MB).</small>
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm">
                                <i class="bi bi-send me-1"></i> Submit Late Entry Application
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
