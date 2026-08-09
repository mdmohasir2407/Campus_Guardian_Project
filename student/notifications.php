<?php
/**
 * CampusGuardian - Student Notifications Log
 */
require_once __DIR__ . '/../includes/auth_check.php';
check_auth([ROLE_STUDENT, ROLE_STAFF, ROLE_HOD, ROLE_ADMIN]);

$page_title = "Notifications";
$db = Database::getConnection();
$user_id = $_SESSION['user_id'] ?? 0;

// Mark all as read if requested
if (isset($_GET['mark_read'])) {
    $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$user_id]);
    set_flash('success', 'All notifications marked as read.');
    redirect($_SESSION['role'] . '/notifications.php');
}

$notifications = $db->query("SELECT * FROM notifications WHERE user_id = $user_id ORDER BY id DESC")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div id="content">
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container-fluid p-4">
        <?php echo get_flash(); ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">System Notifications</h3>
                <p class="text-muted mb-0">Live history of approval alerts, attendance notices, and automated updates.</p>
            </div>
            <a href="notifications.php?mark_read=1" class="btn btn-outline-secondary btn-sm"><i class="bi bi-check2-all me-1"></i> Mark All as Read</a>
        </div>

        <div class="card border-0 shadow-sm p-4" style="max-width:800px;">
            <?php if (empty($notifications)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-bell-slash display-4 mb-2"></i>
                    <p class="mb-0">No notifications found.</p>
                </div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($notifications as $n): ?>
                        <div class="list-group-item px-0 py-3 border-bottom <?php echo $n['is_read'] ? 'opacity-75' : ''; ?>">
                            <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                <h6 class="fw-bold mb-0 text-dark">
                                    <?php if (!$n['is_read']): ?>
                                        <span class="badge bg-danger rounded-circle p-1 me-2" style="font-size:0.4rem;"> </span>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($n['title']); ?>
                                </h6>
                                <small class="text-muted"><?php echo date('d M Y, h:i A', strtotime($n['created_at'])); ?></small>
                            </div>
                            <p class="mb-1 text-secondary small"><?php echo htmlspecialchars($n['message']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
