<?php
/**
 * CampusGuardian - Apply Leave Application
 */
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../mail/mailer.php';
check_auth([ROLE_STUDENT]);

$page_title = "Apply Leave Request";
$db = Database::getConnection();
$student_id = $_SESSION['student_id'] ?? 0;

// Fetch student details for parent notification
$stmt_st = $db->prepare("SELECT * FROM students WHERE id = ?");
$stmt_st->execute([$student_id]);
$student_data = $stmt_st->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leave_type = sanitize($_POST['leave_type'] ?? 'Full Day');
    $start_date = sanitize($_POST['start_date'] ?? '');
    $end_date = sanitize($_POST['end_date'] ?? '');
    $reason = sanitize($_POST['reason'] ?? '');

    // Calculate total days
    $d1 = new DateTime($start_date);
    $d2 = new DateTime($end_date);
    $interval = $d1->diff($d2);
    $total_days = $interval->days + 1;

    // File upload
    $attachment_filename = null;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $up = upload_file('attachment', UPLOAD_DIR_ATTACHMENTS, ['jpg', 'jpeg', 'png', 'pdf']);
        if ($up['status']) {
            $attachment_filename = $up['filename'];
        }
    }

    try {
        $stmt = $db->prepare("INSERT INTO leave_requests (student_id, leave_type, start_date, end_date, total_days, reason, attachment, status, staff_approval, hod_approval) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', 'pending', 'pending')");
        $stmt->execute([$student_id, $leave_type, $start_date, $end_date, $total_days, $reason, $attachment_filename]);
        $leave_request_id = $db->lastInsertId();

        // Create Parent Portal Notification
        if ($student_data && !empty($student_data['parent_email'])) {
            $pn_stmt = $db->prepare("INSERT INTO parent_notifications (student_id, leave_request_id, parent_email, title, message, status) VALUES (?, ?, ?, ?, ?, 'sent')");
            $pn_title = "Student Leave Application Filed: " . $leave_type;
            $pn_msg = "Your ward " . $student_data['name'] . " submitted a " . $leave_type . " request for " . $total_days . " day(s) from " . format_date($start_date) . " to " . format_date($end_date) . ". Reason: " . $reason;
            $pn_stmt->execute([$student_id, $leave_request_id, $student_data['parent_email'], $pn_title, $pn_msg]);
        }

        // Dispatch instant alert notification to parent email
        if ($student_data) {
            $email_html = EmailTemplates::getLeaveNotification(
                $student_data['name'], 
                $leave_type, 
                $start_date, 
                $end_date, 
                $reason, 
                'PENDING REVIEW', 
                'Leave application submitted by student; pending faculty verification.'
            );
            try {
                CampusMailer::send(
                    $student_data['parent_email'], 
                    $student_data['parent_name'], 
                    "CampusGuardian Leave Notice: Application Filed for " . $student_data['name'], 
                    $email_html
                );
            } catch (Exception $e) {
                error_log("Mail Error: " . $e->getMessage());
            }
        }

        set_flash('success', 'Leave application submitted successfully for ' . $total_days . ' day(s)! Parent email notice logged.');
        redirect('student/status.php');

    } catch (Exception $e) {
        set_flash('danger', 'Error applying for leave: ' . $e->getMessage());
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
                    <div class="bg-primary-soft stat-icon fs-3"><i class="bi bi-calendar-event"></i></div>
                    <div>
                        <h4 class="fw-bold mb-0">Apply for Student Leave</h4>
                        <p class="text-muted mb-0 small">Automated notification will be sent to your registered parent email (<?php echo htmlspecialchars($student_data['parent_email'] ?? ''); ?>).</p>
                    </div>
                </div>

                <form action="leave_request.php" method="POST" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Leave Type *</label>
                            <select name="leave_type" class="form-select" required>
                                <option value="Full Day">Full Day Leave</option>
                                <option value="Medical Leave">Medical Leave</option>
                                <option value="Personal Leave">Personal Leave</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Start Date *</label>
                            <input type="date" name="start_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">End Date *</label>
                            <input type="date" name="end_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Detailed Reason *</label>
                            <textarea name="reason" class="form-control" rows="3" placeholder="Explain medical condition or personal reason..." required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Medical / Support Certificate Attachment</label>
                            <input type="file" name="attachment" class="form-control" accept=".pdf,image/*">
                            <small class="text-muted">Mandatory for Medical Leave (PDF or Image, max 5MB).</small>
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm">
                                <i class="bi bi-paperclip me-1"></i> Submit Leave Application & Notify Parent
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
