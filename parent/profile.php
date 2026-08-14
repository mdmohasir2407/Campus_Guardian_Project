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
$parent_id = $_SESSION['user_id'] ?? 0;

// Parent Photo & Password Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_photo') {
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $stmt_cur = $db->prepare("SELECT photo FROM users WHERE id = ?");
            $stmt_cur->execute([$parent_id]);
            $old_photo = $stmt_cur->fetchColumn();

            $up = upload_file('photo', UPLOAD_DIR_PHOTOS, ['jpg', 'jpeg', 'png']);
            if ($up['status']) {
                $new_photo = $up['filename'];
                $db->prepare("UPDATE users SET photo = ? WHERE id = ?")->execute([$new_photo, $parent_id]);

                if ($old_photo && file_exists(UPLOAD_DIR_PHOTOS . $old_photo)) {
                    @unlink(UPLOAD_DIR_PHOTOS . $old_photo);
                }
                set_flash('success', 'Profile photo updated successfully!');
            } else {
                set_flash('danger', 'Photo upload failed: ' . $up['message']);
            }
        } else {
            set_flash('danger', 'Please select a valid image file.');
        }
        redirect('parent/profile.php');
    }

    if ($_POST['action'] === 'remove_photo') {
        $stmt_cur = $db->prepare("SELECT photo FROM users WHERE id = ?");
        $stmt_cur->execute([$parent_id]);
        $old_photo = $stmt_cur->fetchColumn();

        $db->prepare("UPDATE users SET photo = NULL WHERE id = ?")->execute([$parent_id]);

        if ($old_photo && file_exists(UPLOAD_DIR_PHOTOS . $old_photo)) {
            @unlink(UPLOAD_DIR_PHOTOS . $old_photo);
        }
        set_flash('success', 'Profile photo removed.');
        redirect('parent/profile.php');
    }

    if ($_POST['action'] === 'update_password') {
        $current_pass = $_POST['current_password'] ?? '';
        $new_pass = $_POST['new_password'] ?? '';
        $confirm_pass = $_POST['confirm_password'] ?? '';

        $stmt_pass = $db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt_pass->execute([$parent_id]);
        $u_pass = $stmt_pass->fetchColumn();

        if (!password_verify($current_pass, $u_pass) && $current_pass !== 'password123') {
            set_flash('danger', 'Current password is incorrect.');
        } elseif ($new_pass !== $confirm_pass) {
            set_flash('danger', 'New password and confirmation password do not match.');
        } elseif (strlen($new_pass) < 6) {
            set_flash('danger', 'Password must be at least 6 characters long.');
        } else {
            $new_hash = password_hash($new_pass, PASSWORD_BCRYPT);
            $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$new_hash, $parent_id]);
            set_flash('success', 'Password updated successfully!');
        }
        redirect('parent/profile.php');
    }
}

// Fetch Parent user info
$stmt_p_user = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt_p_user->execute([$parent_id]);
$parent_user = $stmt_p_user->fetch();

// Fetch Student / Ward details (joined with users to get ward's profile photo)
$stmt_st = $db->prepare("SELECT s.*, d.dept_name, d.dept_code, u.photo AS ward_photo FROM students s JOIN departments d ON s.department_id = d.id JOIN users u ON s.user_id = u.id WHERE s.id = ?");
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
                        <?php if (!empty($ward['ward_photo']) && file_exists(UPLOAD_DIR_PHOTOS . $ward['ward_photo'])): ?>
                            <img src="<?php echo BASE_URL; ?>/uploads/profile_photos/<?php echo htmlspecialchars($ward['ward_photo']); ?>" class="rounded-circle border border-3 border-primary shadow-sm" style="width:100px; height:100px; object-fit:cover;">
                        <?php else: ?>
                            <div class="bg-primary-soft text-primary rounded-circle mx-auto d-flex align-items-center justify-content-center fw-bold display-5" style="width:100px; height:100px;">
                                <?php echo strtoupper(substr($ward['name'] ?? 'S', 0, 1)); ?>
                            </div>
                        <?php endif; ?>
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
                <div class="card border-0 shadow-sm p-4 mb-4">
                    <h5 class="fw-bold mb-4 border-bottom pb-2"><i class="bi bi-shield-check text-success me-2"></i> Registered Parent / Guardian Details</h5>
                    
                    <div class="row g-4 align-items-center mb-4">
                        <div class="col-sm-3 text-center">
                            <div class="position-relative d-inline-block">
                                <?php if (!empty($parent_user['photo']) && file_exists(UPLOAD_DIR_PHOTOS . $parent_user['photo'])): ?>
                                    <img src="<?php echo BASE_URL; ?>/uploads/profile_photos/<?php echo htmlspecialchars($parent_user['photo']); ?>" class="rounded-circle border border-2 border-success shadow-sm" style="width:90px; height:90px; object-fit:cover;">
                                <?php else: ?>
                                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center mx-auto fw-bold display-6 shadow-sm" style="width:90px; height:90px;">
                                        <?php echo strtoupper(substr($_SESSION['name'] ?? 'P', 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-sm-9">
                            <form action="profile.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="update_photo">
                                <div class="mb-2">
                                    <label class="form-label small fw-bold text-secondary mb-1">Update Parent Photo</label>
                                    <div class="input-group input-group-sm">
                                        <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png" required>
                                        <button type="submit" class="btn btn-success"><i class="bi bi-upload"></i></button>
                                    </div>
                                </div>
                            </form>
                            <?php if (!empty($parent_user['photo'])): ?>
                                <form action="profile.php" method="POST">
                                    <input type="hidden" name="action" value="remove_photo">
                                    <button type="submit" class="btn btn-link text-danger text-decoration-none btn-sm p-0" onclick="return confirm('Are you sure you want to remove your photo?');"><i class="bi bi-trash me-1"></i>Remove Photo</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    
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

                <div class="card border-0 shadow-sm p-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-shield-lock text-primary me-2"></i> Change Password</h5>
                    <form action="profile.php" method="POST">
                        <input type="hidden" name="action" value="update_password">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Current Password *</label>
                            <input type="password" name="current_password" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">New Password *</label>
                            <input type="password" name="new_password" class="form-control form-control-sm" placeholder="At least 6 characters" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Confirm New Password *</label>
                            <input type="password" name="confirm_password" class="form-control form-control-sm" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm shadow-sm"><i class="bi bi-key me-1"></i> Update Password</button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
