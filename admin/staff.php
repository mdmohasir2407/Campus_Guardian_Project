<?php
/**
 * CampusGuardian - Admin Staff Management
 */
require_once __DIR__ . '/../includes/auth_check.php';
check_auth([ROLE_ADMIN]);

$page_title = "Manage Faculty Staff";
$db = Database::getConnection();

// Handle Add Staff
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_staff') {
    $staff_code = sanitize($_POST['staff_code'] ?? '');
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $designation = sanitize($_POST['designation'] ?? 'Assistant Professor');
    $department_id = (int)($_POST['department_id'] ?? 0);
    $phone = sanitize($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? 'password123';

    try {
        $db->beginTransaction();

        $pass_hash = password_hash($password, PASSWORD_BCRYPT);
        $u_stmt = $db->prepare("INSERT INTO users (email, password, role, is_active) VALUES (?, ?, 'staff', 1)");
        $u_stmt->execute([$email, $pass_hash]);
        $user_id = $db->lastInsertId();

        $st_stmt = $db->prepare("INSERT INTO staff (user_id, department_id, staff_code, name, designation, phone, email) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $st_stmt->execute([$user_id, $department_id, $staff_code, $name, $designation, $phone, $email]);

        $db->commit();
        set_flash('success', "Faculty staff account for {$name} created successfully!");
        redirect('admin/staff.php');

    } catch (Exception $e) {
        $db->rollBack();
        set_flash('danger', 'Error creating staff member: ' . $e->getMessage());
    }
}

// Handle Delete Staff
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    try {
        $db->prepare("DELETE FROM users WHERE id = (SELECT user_id FROM staff WHERE id = ?)")->execute([$del_id]);
        set_flash('success', 'Staff account removed.');
        redirect('admin/staff.php');
    } catch (Exception $e) {
        set_flash('danger', 'Error deleting staff: ' . $e->getMessage());
    }
}

$staff_members = $db->query("SELECT st.*, d.dept_name, d.dept_code FROM staff st JOIN departments d ON st.department_id = d.id ORDER BY st.id DESC")->fetchAll();
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
                <h3 class="fw-bold mb-1">Faculty Staff Management</h3>
                <p class="text-muted mb-0">Manage teaching staff, designations, and department approvals.</p>
            </div>
            <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                <i class="bi bi-person-badge-fill me-1"></i> Add Faculty Staff
            </button>
        </div>

        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Staff Code</th>
                            <th>Faculty Name</th>
                            <th>Designation</th>
                            <th>Department</th>
                            <th>Contact</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($staff_members)): ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted">No faculty members found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($staff_members as $st): ?>
                                <tr>
                                    <td><span class="fw-bold text-dark"><?php echo htmlspecialchars($st['staff_code']); ?></span></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?php echo htmlspecialchars($st['name']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($st['email']); ?></small>
                                    </td>
                                    <td><span class="badge bg-info-soft text-dark"><?php echo htmlspecialchars($st['designation']); ?></span></td>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($st['dept_code']); ?></span></td>
                                    <td><i class="bi bi-telephone text-muted me-1"></i><?php echo htmlspecialchars($st['phone']); ?></td>
                                    <td>
                                        <a href="staff.php?delete=<?php echo $st['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this staff member?');">
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

<!-- Modal: Add Staff -->
<div class="modal fade" id="addStaffModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-header-title fw-bold mb-0"><i class="bi bi-person-badge-fill me-2"></i> Add Faculty Staff</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="staff.php" method="POST">
                <input type="hidden" name="action" value="create_staff">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Staff Code *</label>
                        <input type="text" name="staff_code" class="form-control" placeholder="STF-MCA-002" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="Prof. Name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address *</label>
                        <input type="email" name="email" class="form-control" placeholder="staff@campusguardian.edu" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Designation *</label>
                        <input type="text" name="designation" class="form-control" value="Assistant Professor" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Department *</label>
                        <select name="department_id" class="form-select" required>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['dept_name']); ?> (<?php echo $d['dept_code']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phone Number *</label>
                        <input type="text" name="phone" class="form-control" placeholder="9812345678" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password *</label>
                        <input type="password" name="password" class="form-control" value="password123" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Staff Member</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
