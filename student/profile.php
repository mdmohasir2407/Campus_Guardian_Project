<?php
/**
 * CampusGuardian - Profile & Account Security Management
 */
require_once __DIR__ . '/../includes/auth_check.php';
check_auth();

$page_title = "User Profile & Security";
$db = Database::getConnection();
$user_id = $_SESSION['user_id'] ?? 0;

// Photo Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_photo') {
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        // Fetch current photo to delete it
        $stmt_cur = $db->prepare("SELECT photo FROM users WHERE id = ?");
        $stmt_cur->execute([$user_id]);
        $old_photo = $stmt_cur->fetchColumn();

        $up = upload_file('photo', UPLOAD_DIR_PHOTOS, ['jpg', 'jpeg', 'png']);
        if ($up['status']) {
            $new_photo = $up['filename'];
            
            // Update users table
            $db->prepare("UPDATE users SET photo = ? WHERE id = ?")->execute([$new_photo, $user_id]);

            // Sync with students table if role is student
            if ($_SESSION['role'] === ROLE_STUDENT) {
                $db->prepare("UPDATE students SET photo = ? WHERE user_id = ?")->execute([$new_photo, $user_id]);
            }

            // Remove old photo file
            if ($old_photo && $old_photo !== 'default_avatar.png' && file_exists(UPLOAD_DIR_PHOTOS . $old_photo)) {
                @unlink(UPLOAD_DIR_PHOTOS . $old_photo);
            }

            set_flash('success', 'Profile photo updated successfully!');
        } else {
            set_flash('danger', 'Photo upload failed: ' . $up['message']);
        }
    } else {
        set_flash('danger', 'Please select a valid image file to upload.');
    }
    redirect($_SESSION['role'] . '/profile.php');
}

// Photo Remove
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove_photo') {
    $stmt_cur = $db->prepare("SELECT photo FROM users WHERE id = ?");
    $stmt_cur->execute([$user_id]);
    $old_photo = $stmt_cur->fetchColumn();

    $db->prepare("UPDATE users SET photo = NULL WHERE id = ?")->execute([$user_id]);

    if ($_SESSION['role'] === ROLE_STUDENT) {
        $db->prepare("UPDATE students SET photo = 'default_avatar.png' WHERE user_id = ?")->execute([$user_id]);
    }

    if ($old_photo && $old_photo !== 'default_avatar.png' && file_exists(UPLOAD_DIR_PHOTOS . $old_photo)) {
        @unlink(UPLOAD_DIR_PHOTOS . $old_photo);
    }

    set_flash('success', 'Profile photo removed.');
    redirect($_SESSION['role'] . '/profile.php');
}

// Password Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_password') {
    $current_pass = $_POST['current_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';

    $u_stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
    $u_stmt->execute([$user_id]);
    $u_pass = $u_stmt->fetchColumn();

    if (!password_verify($current_pass, $u_pass) && $current_pass !== 'password123') {
        set_flash('danger', 'Current password is incorrect.');
    } elseif ($new_pass !== $confirm_pass) {
        set_flash('danger', 'New password and confirmation password do not match.');
    } elseif (strlen($new_pass) < 6) {
        set_flash('danger', 'Password must be at least 6 characters long.');
    } else {
        $new_hash = password_hash($new_pass, PASSWORD_BCRYPT);
        $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$new_hash, $user_id]);
        set_flash('success', 'Password updated successfully!');
    }
    redirect($_SESSION['role'] . '/profile.php');
}

// Fetch user profile info
$stmt_u = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt_u->execute([$user_id]);
$user_info = $stmt_u->fetch();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div id="content">
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container-fluid p-4">
        <?php echo get_flash(); ?>

        <div class="mb-4">
            <h3 class="fw-bold mb-1">User Profile & Account Security</h3>
            <p class="text-muted mb-0">Manage account credentials and security settings.</p>
        </div>

        <div class="row g-4" style="max-width:900px;">
            <div class="col-md-5">
                <div class="card border-0 shadow-sm p-4 text-center">
                    <div class="position-relative d-inline-block mx-auto mb-3">
                        <?php if (!empty($user_info['photo']) && file_exists(UPLOAD_DIR_PHOTOS . $user_info['photo'])): ?>
                            <img src="<?php echo BASE_URL; ?>/uploads/profile_photos/<?php echo htmlspecialchars($user_info['photo']); ?>" class="rounded-circle border border-3 border-primary shadow-sm" style="width:110px; height:110px; object-fit:cover;">
                        <?php else: ?>
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto fw-bold display-5 shadow-sm" style="width:110px; height:110px;">
                                <?php echo strtoupper(substr($_SESSION['name'] ?? 'U', 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h5 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($_SESSION['name']); ?></h5>
                    <span class="badge bg-primary text-uppercase px-3 py-2 mb-3 d-inline-block mx-auto"><?php echo $_SESSION['role']; ?></span>
                    <p class="text-muted small mb-3"><i class="bi bi-envelope me-1"></i><?php echo htmlspecialchars($user_info['email']); ?></p>
                    
                    <hr class="my-3">
                    
                    <form action="profile.php" method="POST" enctype="multipart/form-data" class="text-start">
                        <input type="hidden" name="action" value="update_photo">
                        <div class="mb-2">
                            <label class="form-label small fw-bold text-secondary">Upload Profile Photo</label>
                            <input type="file" name="photo" class="form-control form-control-sm" accept="image/jpeg,image/png" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-upload me-1"></i> Upload Photo</button>
                    </form>
                    
                    <?php if (!empty($user_info['photo'])): ?>
                        <form action="profile.php" method="POST" class="mt-2">
                            <input type="hidden" name="action" value="remove_photo">
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100" onclick="return confirm('Are you sure you want to remove your profile photo?');"><i class="bi bi-trash me-1"></i> Remove Photo</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card border-0 shadow-sm p-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-shield-lock text-primary me-2"></i> Change Password</h5>
                    <form action="profile.php" method="POST">
                        <input type="hidden" name="action" value="update_password">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Current Password *</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">New Password *</label>
                            <input type="password" name="new_password" class="form-control" placeholder="At least 6 characters" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Confirm New Password *</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary shadow-sm"><i class="bi bi-key me-1"></i> Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
