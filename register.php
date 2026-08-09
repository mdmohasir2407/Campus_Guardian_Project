<?php
/**
 * CampusGuardian - Public Student Self-Registration Page
 */
require_once __DIR__ . '/config/config.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    redirect($_SESSION['role'] . '/dashboard.php');
}

$db = Database::getConnection();
$departments = $db->query("SELECT * FROM departments ORDER BY dept_name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reg_no = sanitize($_POST['register_number'] ?? '');
    $student_code = sanitize($_POST['student_id_code'] ?? '');
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['student_email'] ?? '');
    $department_id = (int)($_POST['department_id'] ?? 0);
    $year = sanitize($_POST['year'] ?? 'I');
    $section = sanitize($_POST['section'] ?? 'A');
    $phone = sanitize($_POST['phone'] ?? '');
    $parent_name = sanitize($_POST['parent_name'] ?? '');
    $parent_phone = sanitize($_POST['parent_phone'] ?? '');
    $parent_email = sanitize($_POST['parent_email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if ($password !== $confirm_password) {
        set_flash('danger', 'Passwords do not match. Please try again.');
    } elseif (strlen($password) < 6) {
        set_flash('danger', 'Password must be at least 6 characters long.');
    } else {
        try {
            // Check if email or reg_no already exists
            $check = $db->prepare("SELECT id FROM users WHERE email = ?");
            $check->execute([$email]);
            if ($check->fetch()) {
                set_flash('danger', 'This email address is already registered. Please login.');
            } else {
                $db->beginTransaction();

                // 1. Create user account
                $pass_hash = password_hash($password, PASSWORD_BCRYPT);
                $u_stmt = $db->prepare("INSERT INTO users (email, password, role, is_active) VALUES (?, ?, 'student', 1)");
                $u_stmt->execute([$email, $pass_hash]);
                $user_id = $db->lastInsertId();

                // 2. Create student profile
                $s_stmt = $db->prepare("INSERT INTO students (user_id, register_number, student_id_code, name, department_id, year, section, phone, parent_name, parent_phone, parent_email, student_email) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $s_stmt->execute([$user_id, $reg_no, $student_code, $name, $department_id, $year, $section, $phone, $parent_name, $parent_phone, $parent_email, $email]);

                // 3. Provision Parent user account
                if (!empty($parent_email)) {
                    $p_check = $db->prepare("SELECT id FROM users WHERE email = ?");
                    $p_check->execute([$parent_email]);
                    if (!$p_check->fetch()) {
                        $p_stmt = $db->prepare("INSERT INTO users (email, password, role, is_active) VALUES (?, ?, 'parent', 1)");
                        $p_stmt->execute([$parent_email, $pass_hash]);
                    }
                }

                $db->commit();
                set_flash('success', 'Student Registration Successful! You can now sign in with your credentials.');
                redirect('index.php');
            }
        } catch (Exception $e) {
            $db->rollBack();
            set_flash('danger', 'Registration error: ' . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Self-Registration | CampusGuardian</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #090d16 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 15px;
        }
        .reg-card {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 750px;
            overflow: hidden;
        }
        .reg-header {
            background: #0f172a;
            color: #ffffff;
            padding: 25px 30px;
        }

        @media (max-width: 575px) {
            body {
                padding: 15px 10px;
            }
            .reg-header {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 12px;
                padding: 20px 18px;
            }
            .reg-card {
                border-radius: 12px;
            }
            .p-4 {
                padding: 1.25rem !important;
            }
        }
    </style>
</head>
<body>

<div class="reg-card">
    <div class="reg-header d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-0"><i class="bi bi-person-plus-fill me-2 text-primary"></i> Student Registration Portal</h4>
            <p class="text-white-50 mb-0 small">Create your CampusGuardian student account</p>
        </div>
        <a href="index.php" class="btn btn-outline-light btn-sm"><i class="bi bi-arrow-left me-1"></i> Back to Login</a>
    </div>
        
    <div class="p-4">
        <?php echo get_flash(); ?>

        <form action="register.php" method="POST">
            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-person-vcard me-1"></i> Academic & Personal Information</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Register Number *</label>
                    <input type="text" name="register_number" class="form-control" placeholder="e.g. 2024MCA045" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Student ID Code *</label>
                    <input type="text" name="student_id_code" class="form-control" placeholder="e.g. STD-MCA-102" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Full Student Name *</label>
                    <input type="text" name="name" class="form-control" placeholder="Full Name" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Student Email *</label>
                    <input type="email" name="student_email" class="form-control" placeholder="student@campusguardian.edu" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Department *</label>
                    <select name="department_id" class="form-select" required>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['dept_name']); ?> (<?php echo $d['dept_code']; ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Academic Year *</label>
                    <select name="year" class="form-select" required>
                        <option value="I">I Year</option>
                        <option value="II">II Year</option>
                        <option value="III">III Year</option>
                        <option value="IV">IV Year</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Section *</label>
                    <input type="text" name="section" class="form-control" value="A" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Student Phone Number *</label>
                    <input type="text" name="phone" class="form-control" placeholder="9876543210" required>
                </div>
            </div>

            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-people-fill me-1"></i> Parent / Guardian Contact Details (For Emergency & Leave Alerts)</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Parent Name *</label>
                    <input type="text" name="parent_name" class="form-control" placeholder="Father / Mother Name" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Parent Phone *</label>
                    <input type="text" name="parent_phone" class="form-control" placeholder="Parent Mobile Number" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Parent Email *</label>
                    <input type="email" name="parent_email" class="form-control" placeholder="parent@example.com" required>
                </div>
            </div>

            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-shield-lock me-1"></i> Security Password</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Create Password *</label>
                    <div class="position-relative">
                        <input type="password" name="password" id="reg_password" class="form-control pe-5" placeholder="At least 6 characters" required>
                        <button type="button" class="btn-password-toggle" onclick="togglePasswordVisibility('reg_password', this)" title="Toggle password visibility">
                            <i class="bi bi-eye-fill text-muted"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Confirm Password *</label>
                    <div class="position-relative">
                        <input type="password" name="confirm_password" id="reg_confirm_password" class="form-control pe-5" placeholder="Re-enter password" required>
                        <button type="button" class="btn-password-toggle" onclick="togglePasswordVisibility('reg_confirm_password', this)" title="Toggle password visibility">
                            <i class="bi bi-eye-fill text-muted"></i>
                        </button>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100 fw-semibold shadow-sm">
                <i class="bi bi-check-circle-fill me-1"></i> Register Student Account
            </button>
        </form>

        <div class="text-center mt-3">
            <span class="text-muted">Already have an account?</span> <a href="index.php" class="fw-bold text-decoration-none">Sign In Here</a>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/main.js"></script>
</body>
</html>
