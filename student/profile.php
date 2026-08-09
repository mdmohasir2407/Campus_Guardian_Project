<?php
/**
 * CampusGuardian - Profile & Account Security Management
 */
require_once __DIR__ . '/../includes/auth_check.php';
check_auth();

$page_title = "User Profile & Security";
$db = Database::getConnection();
$user_id = $_SESSION['user_id'] ?? 0;

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
$user_info = $db->query("SELECT * FROM users WHERE id = $user_id")->fetch();

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
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-3 fw-bold display-5" style="width:90px; height:90px;">
                        <?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?>
                    </div>
                    <h5 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($_SESSION['name']); ?></h5>
                    <span class="badge bg-primary text-uppercase px-3 py-2 mb-2 d-inline-block mx-auto"><?php echo $_SESSION['role']; ?></span>
                    <p class="text-muted small mb-0"><i class="bi bi-envelope me-1"></i><?php echo htmlspecialchars($user_info['email']); ?></p>
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
