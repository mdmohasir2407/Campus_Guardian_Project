<?php
/**
 * CampusGuardian - Admin Student Management
 */
require_once __DIR__ . '/../includes/auth_check.php';
check_auth([ROLE_ADMIN]);

$page_title = "Manage Students";
$db = Database::getConnection();

// Handle Add Student Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_student') {
    $reg_no = sanitize($_POST['register_number'] ?? '');
    $student_code = sanitize($_POST['student_id_code'] ?? '');
    $course_type = sanitize($_POST['course_type'] ?? 'UG');
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['student_email'] ?? '');
    $department_id = (int)($_POST['department_id'] ?? 0);
    $year = sanitize($_POST['year'] ?? 'I');
    $section = sanitize($_POST['section'] ?? 'A');
    $phone = sanitize($_POST['phone'] ?? '');
    $parent_name = sanitize($_POST['parent_name'] ?? '');
    $parent_phone = sanitize($_POST['parent_phone'] ?? '');
    $parent_email = sanitize($_POST['parent_email'] ?? '');
    $password = $_POST['password'] ?? 'password123';

    try {
        $db->beginTransaction();
        
        // 1. Create User account
        $pass_hash = password_hash($password, PASSWORD_BCRYPT);
        $u_stmt = $db->prepare("INSERT INTO users (email, password, role, is_active) VALUES (?, ?, 'student', 1)");
        $u_stmt->execute([$email, $pass_hash]);
        $user_id = $db->lastInsertId();

        // 2. Create Student profile
        $s_stmt = $db->prepare("INSERT INTO students (user_id, register_number, student_id_code, course_type, name, department_id, year, section, phone, parent_name, parent_phone, parent_email, student_email) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $s_stmt->execute([$user_id, $reg_no, $student_code, $course_type, $name, $department_id, $year, $section, $phone, $parent_name, $parent_phone, $parent_email, $email]);

        $db->commit();
        set_flash('success', "Student account for {$name} created successfully!");
        redirect('admin/students.php');

    } catch (Exception $e) {
        $db->rollBack();
        set_flash('danger', 'Error creating student: ' . $e->getMessage());
    }
}

// Handle Delete Student
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    try {
        // Deleting user row cascades to student record
        $stmt = $db->prepare("DELETE FROM users WHERE id = (SELECT user_id FROM students WHERE id = ?)");
        $stmt->execute([$del_id]);
        set_flash('success', 'Student record deleted successfully.');
        redirect('admin/students.php');
    } catch (Exception $e) {
        set_flash('danger', 'Error deleting student: ' . $e->getMessage());
    }
}

// Fetch Students List
$students = $db->query("SELECT s.*, d.dept_name, d.dept_code FROM students s JOIN departments d ON s.department_id = d.id ORDER BY s.id DESC")->fetchAll();
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
                <h3 class="fw-bold mb-1">Student Management</h3>
                <p class="text-muted mb-0">Register, manage profiles, and view parent details.</p>
            </div>
            <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                <i class="bi bi-person-plus-fill me-1"></i> Register New Student
            </button>
        </div>

        <!-- Student Data Table -->
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Reg / Student ID</th>
                            <th>Student Details</th>
                            <th>Dept & Year</th>
                            <th>Parent Details</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($students)): ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">No registered students found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($students as $st): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($st['register_number']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($st['student_id_code']); ?></small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width:36px; height:36px;">
                                                <?php echo strtoupper(substr($st['name'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="fw-semibold text-dark"><?php echo htmlspecialchars($st['name']); ?></div>
                                                <small class="text-muted"><i class="bi bi-envelope"></i> <?php echo htmlspecialchars($st['student_email']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($st['dept_code']); ?></span>
                                        <span class="badge bg-info text-dark"><?php echo htmlspecialchars($st['course_type'] ?? 'UG'); ?></span>
                                        <span class="badge bg-light text-dark border">Year <?php echo htmlspecialchars($st['year']); ?> - Sec <?php echo htmlspecialchars($st['section']); ?></span>
                                    </td>
                                    <td>
                                        <div class="fw-medium text-dark"><?php echo htmlspecialchars($st['parent_name']); ?></div>
                                        <small class="text-muted"><i class="bi bi-telephone"></i> <?php echo htmlspecialchars($st['parent_phone']); ?></small><br>
                                        <small class="text-muted"><i class="bi bi-envelope"></i> <?php echo htmlspecialchars($st['parent_email']); ?></small>
                                    </td>
                                    <td>
                                        <a href="students.php?delete=<?php echo $st['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this student record?');">
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

<!-- Modal: Register Student -->
<div class="modal fade" id="addStudentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-header-title fw-bold mb-0"><i class="bi bi-person-plus-fill me-2"></i> Register New Student</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="students.php" method="POST">
                <input type="hidden" name="action" value="create_student">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Register Number *</label>
                            <input type="text" name="register_number" class="form-control" placeholder="2024MCA001" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Student ID Code *</label>
                            <input type="text" name="student_id_code" class="form-control" placeholder="STD-MCA-101" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Student Name *</label>
                            <input type="text" name="name" class="form-control" placeholder="Full Student Name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Student Email *</label>
                            <input type="email" name="student_email" class="form-control" placeholder="student@campusguardian.edu" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Department *</label>
                            <select name="department_id" id="admin_department_id" class="form-select" required>
                                <option value="">-- Select Department --</option>
                                <?php 
                                $pg_codes = ['MCA', 'MBA', 'MTech', 'ME', 'MSc'];
                                foreach ($departments as $d): 
                                    $dtype = in_array($d['dept_code'], $pg_codes) ? 'PG' : 'UG';
                                ?>
                                    <option value="<?php echo $d['id']; ?>" data-type="<?php echo $dtype; ?>"><?php echo htmlspecialchars($d['dept_name']); ?> (<?php echo $d['dept_code']; ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Academic Year *</label>
                            <select name="year" id="admin_academic_year" class="form-select" required>
                                <option value="I">I Year</option>
                                <option value="II">II Year</option>
                                <option value="III">III Year</option>
                                <option value="IV">IV Year</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Section *</label>
                            <select name="section" class="form-select" required>
                                <option value="A">Section A</option>
                                <option value="B">Section B</option>
                                <option value="C">Section C</option>
                                <option value="D">Section D</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                                <label class="form-label fw-semibold">Course Type *</label>
                                <select name="course_type" id="admin_course_type" class="form-select" required>
                                    <option value="UG">UG (Undergraduate)</option>
                                    <option value="PG">PG (Postgraduate)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Student Phone *</label>
                                <input type="text" name="phone" class="form-control" placeholder="9123456789" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Initial Password *</label>
                                <input type="password" name="password" class="form-control" value="password123" required>
                            </div>
                        
                        <hr class="my-3">
                        <h6 class="fw-bold text-primary mb-2">Parent / Guardian Contact Information</h6>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Parent Name *</label>
                            <input type="text" name="parent_name" class="form-control" placeholder="Parent / Guardian Name" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Parent Phone *</label>
                            <input type="text" name="parent_phone" class="form-control" placeholder="Parent Phone Number" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Parent Email *</label>
                            <input type="email" name="parent_email" class="form-control" placeholder="parent@example.com" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Student Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const courseTypeSelect = document.getElementById('admin_course_type');
    const departmentSelect = document.getElementById('admin_department_id');
    const yearSelect = document.getElementById('admin_academic_year');

    if (courseTypeSelect && departmentSelect && yearSelect) {
        // Store original option nodes to dynamically rebuild
        const originalDeptOptions = Array.from(departmentSelect.querySelectorAll('option:not([value=""])'));
        const originalYearOptions = Array.from(yearSelect.querySelectorAll('option'));

        function filterAdminOptions() {
            const selectedType = courseTypeSelect.value; // 'UG' or 'PG'

            // 1. Rebuild Department Dropdown with optgroups
            departmentSelect.innerHTML = '<option value="">-- Select Department --</option>';
            
            const filteredDepts = originalDeptOptions.filter(opt => opt.getAttribute('data-type') === selectedType);
            if (filteredDepts.length > 0) {
                const optGroup = document.createElement('optgroup');
                optGroup.label = selectedType === 'UG' ? 'Undergraduate (Bachelors)' : 'Postgraduate (Masters)';
                filteredDepts.forEach(opt => {
                    optGroup.appendChild(opt.cloneNode(true));
                });
                departmentSelect.appendChild(optGroup);
            }

            // 2. Rebuild Academic Year Options (PG courses usually have 2 Years, e.g. I & II)
            yearSelect.innerHTML = '';
            originalYearOptions.forEach(opt => {
                if (selectedType === 'PG' && (opt.value === 'III' || opt.value === 'IV')) {
                    // Skip III and IV Year for PG courses
                    return;
                }
                yearSelect.appendChild(opt.cloneNode(true));
            });
        }

        courseTypeSelect.addEventListener('change', filterAdminOptions);
        filterAdminOptions(); // Run once on startup
    }
});
</script>
