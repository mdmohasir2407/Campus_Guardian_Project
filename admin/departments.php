<?php
/**
 * CampusGuardian - Admin Department Management
 */
require_once __DIR__ . '/../includes/auth_check.php';
check_auth([ROLE_ADMIN]);

$page_title = "Manage Departments";
$db = Database::getConnection();

// Add Department
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_dept') {
    $code = strtoupper(sanitize($_POST['dept_code'] ?? ''));
    $name = sanitize($_POST['dept_name'] ?? '');

    try {
        $stmt = $db->prepare("INSERT INTO departments (dept_code, dept_name) VALUES (?, ?)");
        $stmt->execute([$code, $name]);
        set_flash('success', "Department {$code} added successfully!");
        redirect('admin/departments.php');
    } catch (Exception $e) {
        set_flash('danger', 'Error adding department: ' . $e->getMessage());
    }
}

$departments = $db->query("SELECT d.*, 
    (SELECT COUNT(*) FROM students WHERE department_id = d.id) as total_students,
    (SELECT COUNT(*) FROM staff WHERE department_id = d.id) as total_staff
    FROM departments d ORDER BY d.id ASC")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div id="content">
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container-fluid p-4">
        <?php echo get_flash(); ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Academic Departments</h3>
                <p class="text-muted mb-0">Configure academic streams and monitor student strength per department.</p>
            </div>
            <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addDeptModal">
                <i class="bi bi-building-add me-1"></i> Add Department
            </button>
        </div>

        <div class="row g-3">
            <?php foreach ($departments as $d): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="card card-stat p-4 border-top border-4 border-primary">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="badge bg-primary fs-6 px-3 py-2"><?php echo htmlspecialchars($d['dept_code']); ?></span>
                            <i class="bi bi-mortarboard-fill text-muted fs-3"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-3"><?php echo htmlspecialchars($d['dept_name']); ?></h5>
                        <div class="d-flex justify-content-between text-muted small border-top pt-2">
                            <span><i class="bi bi-people me-1"></i> Students: <strong><?php echo $d['total_students']; ?></strong></span>
                            <span><i class="bi bi-person-badge me-1"></i> Staff: <strong><?php echo $d['total_staff']; ?></strong></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Modal: Add Dept -->
<div class="modal fade" id="addDeptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-header-title fw-bold mb-0"><i class="bi bi-building-add me-2"></i> Add Department</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="departments.php" method="POST">
                <input type="hidden" name="action" value="create_dept">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Department Code (Short) *</label>
                        <input type="text" name="dept_code" class="form-control" placeholder="e.g. MCA, CSE, ECE" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Department Name *</label>
                        <input type="text" name="dept_name" class="form-control" placeholder="Master of Computer Applications" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Department</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
