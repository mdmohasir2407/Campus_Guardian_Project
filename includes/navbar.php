<?php
/**
 * CampusGuardian - Global Top Navbar
 */
$user_name = $_SESSION['name'] ?? 'User';
$user_role = strtoupper($_SESSION['role'] ?? 'GUEST');

// Get unread notifications count and user profile photo
$unread_count = 0;
$user_photo = null;
if (isset($_SESSION['user_id'])) {
    try {
        $db = Database::getConnection();
        
        $stmt = $db->prepare("SELECT COUNT(*) as unread FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$_SESSION['user_id']]);
        $res = $stmt->fetch();
        $unread_count = $res['unread'] ?? 0;
        
        $stmt_photo = $db->prepare("SELECT photo FROM users WHERE id = ?");
        $stmt_photo->execute([$_SESSION['user_id']]);
        $user_photo = $stmt_photo->fetchColumn();
    } catch (Exception $e) {}
}
?>
<nav class="navbar top-navbar navbar-expand navbar-light">
    <div class="container-fluid">
        <!-- 3-Line Header Bar Menu Button to Toggle Original Left Sidebar -->
        <button type="button" id="sidebarCollapse" class="btn btn-outline-secondary btn-sm me-3 border-0" title="Toggle Navigation Menu">
            <i class="bi bi-list fs-4"></i>
        </button>

        <div class="d-none d-md-block fw-semibold text-secondary">
            <span class="badge bg-primary me-2"><?php echo $user_role; ?> PORTAL</span>
            <span><?php echo get_db_setting('system_institution_name', 'CampusGuardian'); ?></span>
        </div>

        <ul class="navbar-nav ms-auto align-items-center">

            <!-- Dark Mode Switcher -->
            <li class="nav-item me-3">
                <button class="btn btn-link nav-link px-2 text-secondary" id="btnThemeToggle" title="Toggle Light/Dark Theme">
                    <i class="bi bi-moon-stars-fill fs-5" id="themeIcon"></i>
                </button>
            </li>

            <!-- Notifications Bell -->
            <li class="nav-item me-3 position-relative">
                <a class="nav-link text-secondary" href="<?php echo BASE_URL; ?>/<?php echo $_SESSION['role']; ?>/notifications.php" title="Notifications">
                    <i class="bi bi-bell-fill fs-5"></i>
                    <?php if ($unread_count > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                            <?php echo $unread_count; ?>
                        </span>
                    <?php endif; ?>
                </a>
            </li>

            <!-- User Profile Dropdown -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php if (!empty($user_photo) && file_exists(UPLOAD_DIR_PHOTOS . $user_photo)): ?>
                        <img src="<?php echo BASE_URL; ?>/uploads/profile_photos/<?php echo htmlspecialchars($user_photo); ?>" class="rounded-circle border" style="width:36px; height:36px; object-fit:cover;">
                    <?php else: ?>
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width:36px; height:36px;">
                            <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    <span class="d-none d-lg-inline text-dark fw-medium"><?php echo htmlspecialchars($user_name); ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/<?php echo $_SESSION['role']; ?>/profile.php"><i class="bi bi-person me-2"></i> Profile & Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Sign Out</a></li>
                </ul>
            </li>
        </ul>
    </div>
</nav>
