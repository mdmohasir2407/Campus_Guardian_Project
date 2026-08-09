<?php
/**
 * CampusGuardian - Parent & Ward Profile Page
 */
require_once __DIR__ . '/../includes/auth_check.php';
check_auth([ROLE_PARENT]);

$page_title = "Ward Profile";
$db = Database::getConnection();

$parent_email = $_SESSION['parent_email'] ?? '';
$student_id = $_SESSION['student_id'] ?? 0;

// Fetch Student / Ward details
$stmt_st = $db->prepare("SELECT s.*, d.dept_name, d.dept_code FROM students s JOIN departments d ON s.department_id = d.id WHERE s.id = ?");
$stmt_st->execute([$student_id]);
$ward = $stmt_st->fetch();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div id="content">
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container-fluid p-4">
        <?php echo get_flash(); ?>

        <div class="mb-4 border-bottom pb-3">
            <h3 class="fw-bold mb-1"><i class="bi bi-person-badge text-primary me-2"></i> Parent & Ward Profile</h3>
            <p class="text-muted mb-0">Overview of student academic details and parent registration records.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-5">
                <div class="card border-0 shadow-sm p-4 text-center">
                    <div class="mb-3">
                        <div class="bg-primary-soft text-primary rounded-circle mx-auto d-flex align-items-center justify-content-center fw-bold display-5" style="width:100px; height:100px;">
                            <?php echo strtoupper(substr($ward['name'] ?? 'S', 0, 1)); ?>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($ward['name'] ?? 'N/A'); ?></h4>
                    <p class="text-muted mb-2">Reg. No: <strong><?php echo htmlspecialchars($ward['register_number'] ?? 'N/A'); ?></strong></p>
                    <span class="badge bg-primary px-3 py-2 fs-6 rounded-pill mx-auto mb-3"><?php echo htmlspecialchars($ward['dept_name'] ?? 'N/A'); ?></span>
                    
                    <hr>

                    <div class="text-start">
                        <div class="mb-2"><strong class="text-secondary">Year & Section:</strong> Year <?php echo htmlspecialchars($ward['year'] ?? 'N/A'); ?> - Section <?php echo htmlspecialchars($ward['section'] ?? 'N/A'); ?></div>
                        <div class="mb-2"><strong class="text-secondary">Student ID Code:</strong> <?php echo htmlspecialchars($ward['student_id_code'] ?? 'N/A'); ?></div>
                        <div class="mb-2"><strong class="text-secondary">Student Phone:</strong> <?php echo htmlspecialchars($ward['phone'] ?? 'N/A'); ?></div>
                        <div class="mb-2"><strong class="text-secondary">Student Email:</strong> <?php echo htmlspecialchars($ward['student_email'] ?? 'N/A'); ?></div>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card border-0 shadow-sm p-4">
                    <h5 class="fw-bold mb-4 border-bottom pb-2"><i class="bi bi-shield-check text-success me-2"></i> Registered Parent / Guardian Details</h5>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">PARENT / GUARDIAN NAME</label>
                            <div class="fw-bold fs-5 text-dark"><?php echo htmlspecialchars($ward['parent_name'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">CONTACT PHONE</label>
                            <div class="fw-bold fs-5 text-dark"><?php echo htmlspecialchars($ward['parent_phone'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="col-12 mt-3">
                            <label class="form-label text-muted small fw-bold">REGISTERED EMAIL ADDRESS FOR ALERTS</label>
                            <div class="fw-bold fs-5 text-dark"><?php echo htmlspecialchars($ward['parent_email'] ?? 'N/A'); ?></div>
                            <small class="text-muted">All leave notifications and acknowledgment updates are logged for this email address.</small>
                        </div>
                    </div>

                    <div class="alert alert-info mt-4 mb-0 border-0 shadow-sm">
                        <i class="bi bi-info-circle-fill me-2"></i> If parent contact information requires modification, please contact the institution Admin or Department HOD.
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
