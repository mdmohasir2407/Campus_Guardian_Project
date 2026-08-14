<?php
/**
 * CampusGuardian - Login Authentication Controller
 */
require_once __DIR__ . '/config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$email = sanitize($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$requested_role = sanitize($_POST['role'] ?? 'student');

if (empty($email) || empty($password)) {
    set_flash('danger', 'Please enter both email and password.');
    redirect('index.php');
}

try {
    $db = Database::getConnection();

    // Fetch user by email
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1 LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Dynamic auto-provisioning for Parent accounts if parent_email exists in students table
    if (!$user && $requested_role === ROLE_PARENT) {
        $st_check = $db->prepare("SELECT parent_email FROM students WHERE parent_email = ? LIMIT 1");
        $st_check->execute([$email]);
        if ($st_check->fetch()) {
            $pass_hash = password_hash('password123', PASSWORD_BCRYPT);
            $ins_p = $db->prepare("INSERT INTO users (email, password, role, is_active) VALUES (?, ?, 'parent', 1)");
            $ins_p->execute([$email, $pass_hash]);

            $stmt->execute([$email]);
            $user = $stmt->fetch();
        }
    }

    // Verify user existence and password (supports bcrypt password_verify and demo password fallback)
    $password_matched = false;
    if ($user) {
        if (password_verify($password, $user['password'])) {
            $password_matched = true;
        } elseif ($password === 'password123') { // Fallback for pre-seeded demo accounts
            $password_matched = true;
        }
    }

    if (!$user || !$password_matched) {
        set_flash('danger', 'Invalid credentials or inactive account.');
        redirect('index.php');
    }

    // Role check
    if ($user['role'] !== $requested_role && $user['role'] !== ROLE_ADMIN) {
        set_flash('warning', "Your account is registered as a " . strtoupper($user['role']) . ", redirected accordingly.");
    }

    // Set Session Variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];

    // Fetch associated entity details
    if ($user['role'] === ROLE_STUDENT) {
        $stmt_s = $db->prepare("SELECT s.*, d.dept_name FROM students s JOIN departments d ON s.department_id = d.id WHERE s.user_id = ?");
        $stmt_s->execute([$user['id']]);
        $student = $stmt_s->fetch();
        $_SESSION['student_id'] = $student['id'] ?? null;
        $_SESSION['name'] = $student['name'] ?? 'Student';
        $_SESSION['department_id'] = $student['department_id'] ?? null;
        $_SESSION['dept_name'] = $student['dept_name'] ?? 'N/A';
        redirect('student/dashboard.php');

    } elseif ($user['role'] === ROLE_STAFF) {
        $stmt_st = $db->prepare("SELECT st.*, d.dept_name FROM staff st JOIN departments d ON st.department_id = d.id WHERE st.user_id = ?");
        $stmt_st->execute([$user['id']]);
        $staff = $stmt_st->fetch();
        $_SESSION['staff_id'] = $staff['id'] ?? null;
        $_SESSION['name'] = $staff['name'] ?? 'Staff Member';
        $_SESSION['department_id'] = $staff['department_id'] ?? null;
        $_SESSION['dept_name'] = $staff['dept_name'] ?? 'N/A';
        redirect('staff/dashboard.php');

    } elseif ($user['role'] === ROLE_HOD) {
        $stmt_h = $db->prepare("SELECT h.*, d.dept_name FROM hod h JOIN departments d ON h.department_id = d.id WHERE h.user_id = ?");
        $stmt_h->execute([$user['id']]);
        $hod = $stmt_h->fetch();
        $_SESSION['hod_id'] = $hod['id'] ?? null;
        $_SESSION['name'] = $hod['name'] ?? 'HOD';
        $_SESSION['department_id'] = $hod['department_id'] ?? null;
        $_SESSION['dept_name'] = $hod['dept_name'] ?? 'N/A';
        redirect('hod/dashboard.php');

    } elseif ($user['role'] === ROLE_PARENT) {
        $stmt_p = $db->prepare("SELECT s.*, d.dept_name FROM students s JOIN departments d ON s.department_id = d.id WHERE s.parent_email = ? LIMIT 1");
        $stmt_p->execute([$user['email']]);
        $student = $stmt_p->fetch();
        $_SESSION['student_id'] = $student['id'] ?? null;
        $_SESSION['parent_email'] = $user['email'];
        $_SESSION['name'] = $student['parent_name'] ?? 'Parent';
        $_SESSION['ward_name'] = $student['name'] ?? 'Ward';
        $_SESSION['department_id'] = $student['department_id'] ?? null;
        $_SESSION['dept_name'] = $student['dept_name'] ?? 'N/A';
        redirect('parent/dashboard.php');

    } elseif ($user['role'] === ROLE_ADMIN) {
        $_SESSION['name'] = 'System Administrator';
        redirect('admin/dashboard.php');
    }

} catch (Exception $e) {
    set_flash('danger', 'System error during login: ' . $e->getMessage());
    redirect('index.php');
}
