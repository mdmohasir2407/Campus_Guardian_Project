<?php
/**
 * CampusGuardian - Admin System Notifications
 */
require_once __DIR__ . '/../includes/auth_check.php';
check_auth([ROLE_ADMIN]);

$page_title = "Notifications";
$db = Database::getConnection();
$user_id = $_SESSION['user_id'] ?? 0;

// Mark all as read if requested
if (isset($_GET['mark_read'])) {
    $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$user_id]);
    set_flash('success', 'All notifications marked as read.');
    redirect('admin/notifications.php');
}

// Auto-mark notifications as read when viewing page
$db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0")->execute([$user_id]);

// Fetch admin notifications
$stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div id="content">
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container-fluid p-4">
        <?php echo get_flash(); ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1"><i class="bi bi-bell-fill text-primary me-2"></i> System Notifications</h3>
                <p class="text-muted mb-0">System alerts, admin notifications, and background process logs.</p>
            </div>
            <a href="notifications.php?mark_read=1" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-check2-all me-1"></i> Mark All as Read
            </a>
        </div>

        <div class="card border-0 shadow-sm p-4" style="max-width:900px;">
            <?php if (empty($notifications)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-bell-slash display-4 mb-2"></i>
                    <p class="mb-0">No notifications found for Admin.</p>
                </div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($notifications as $n): ?>
                        <?php 
                            $badge_color = 'bg-info';
                            $icon_class = 'bi-info-circle-fill';
                            if ($n['type'] === 'success') {
                                $badge_color = 'bg-success';
                                $icon_class = 'bi-check-circle-fill';
                            } elseif ($n['type'] === 'warning') {
                                $badge_color = 'bg-warning text-dark';
                                $icon_class = 'bi-exclamation-triangle-fill';
                            } elseif ($n['type'] === 'danger') {
                                $badge_color = 'bg-danger';
                                $icon_class = 'bi-x-circle-fill';
                            }
                        ?>
                        <div class="list-group-item px-0 py-3 border-bottom">
                            <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                                    <span class="badge <?php echo $badge_color; ?> rounded-circle p-2">
                                        <i class="bi <?php echo $icon_class; ?> text-white fs-6"></i>
                                    </span>
                                    <span><?php echo htmlspecialchars($n['title']); ?></span>
                                </h6>
                                <small class="text-muted"><i class="bi bi-clock me-1"></i><?php echo date('d M Y, h:i A', strtotime($n['created_at'])); ?></small>
                            </div>
                            <p class="mb-1 text-secondary small ms-4 ps-2"><?php echo htmlspecialchars($n['message']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
