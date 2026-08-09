<?php
/**
 * CampusGuardian - Admin HOD Management
 */
require_once __DIR__ . '/../includes/auth_check.php';
check_auth([ROLE_ADMIN]);

$page_title = "Manage Department Heads (HOD)";
$db = Database::getConnection();

// Add HOD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_hod') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $department_id = (int)($_POST['department_id'] ?? 0);
    $phone = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? 'password123';

    try {
        $db->beginTransaction();
        $pass_hash = password_hash($password, PASSWORD_BCRYPT);
        $u_stmt = $db->prepare("INSERT INTO users (email, password, role, is_active) VALUES (?, ?, 'hod', 1)");
        $u_stmt->execute([$email, $pass_hash]);
        $user_id = $db->lastInsertId();

        $h_stmt = $db->prepare("INSERT INTO hod (user_id, department_id, name, phone, email) VALUES (?, ?, ?, ?, ?)");
        $h_stmt->execute([$user_id, $department_id, $name, $phone, $email]);

        $db->commit();
        set_flash('success', "Head of Department (HOD) account for {$name} created successfully!");
        redirect('admin/hod.php');
    } catch (Exception $e) {
        $db->rollBack();
        set_flash('danger', 'Error creating HOD account: ' . $e->getMessage());
    }
}

// Delete HOD
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    try {
        $db->prepare("DELETE FROM users WHERE id = (SELECT user_id FROM hod WHERE id = ?)")->execute([$del_id]);
        set_flash('success', 'HOD account removed.');
        redirect('admin/hod.php');
    } catch (Exception $e) {
        set_flash('danger', 'Error deleting HOD: ' . $e->getMessage());
    }
}

$hod_list = $db->query("SELECT h.*, d.dept_name, d.dept_code FROM hod h JOIN departments d ON h.department_id = d.id ORDER BY h.id DESC")->fetchAll();
$departments = $db->query("SELECT * FROM departments ORDER BY dept_name ASC")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div id="content">
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container-fluid p-4">
        <?php echo get_flash(); ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Head of Department (HOD) Management</h3>
                <p class="text-muted mb-0">Assign HODs for department-level final approval governance.</p>
            </div>
            <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addHodModal">
                <i class="bi bi-person-gear me-1"></i> Register New HOD
            </button>
        </div>

        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th>HOD Name</th>
                            <th>Department</th>
                            <th>Email Address</th>
                            <th>Phone Number</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($hod_list)): ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">No HOD records registered.</td></tr>
                        <?php else: ?>
                            <?php foreach ($hod_list as $h): ?>
                                <tr>
                                    <td><div class="fw-bold text-dark"><?php echo htmlspecialchars($h['name']); ?></div></td>
                                    <td><span class="badge bg-primary fs-6"><?php echo htmlspecialchars($h['dept_code']); ?></span> - <?php echo htmlspecialchars($h['dept_name']); ?></td>
                                    <td><i class="bi bi-envelope me-1 text-muted"></i><?php echo htmlspecialchars($h['email']); ?></td>
                                    <td><i class="bi bi-telephone me-1 text-muted"></i><?php echo htmlspecialchars($h['phone']); ?></td>
                                    <td>
                                        <a href="hod.php?delete=<?php echo $h['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this HOD account?');">
                                            <i class="bi bi-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add HOD -->
<div class="modal fade" id="addHodModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-header-title fw-bold mb-0"><i class="bi bi-person-gear me-2"></i> Register New HOD</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="hod.php" method="POST">
                <input type="hidden" name="action" value="create_hod">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">HOD Full Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="Dr. Full Name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address *</label>
                        <input type="email" name="email" class="form-control" placeholder="hod@campusguardian.edu" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Assigned Department *</label>
                        <select name="department_id" class="form-select" required>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['dept_name']); ?> (<?php echo $d['dept_code']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phone Number *</label>
                        <input type="text" name="phone" class="form-control" placeholder="9876543210" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Initial Password *</label>
                        <input type="password" name="password" class="form-control" value="password123" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create HOD Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
