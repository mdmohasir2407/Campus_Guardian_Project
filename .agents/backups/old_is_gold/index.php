<?php
/**
 * CampusGuardian - Landing Portal & Multi-Role Authentication Page
 */
require_once __DIR__ . '/config/config.php';

// Redirect logged-in users directly to their respective dashboards
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case ROLE_ADMIN: redirect('admin/dashboard.php'); break;
        case ROLE_HOD: redirect('hod/dashboard.php'); break;
        case ROLE_STAFF: redirect('staff/dashboard.php'); break;
        case ROLE_STUDENT: redirect('student/dashboard.php'); break;
        case ROLE_PARENT: redirect('parent/dashboard.php'); break;
    }
}

$page_title = "Login Portal";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusGuardian - Smart Student Monitoring & Alert System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #090d16 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 480px;
            overflow: hidden;
        }
        .login-header {
            background: #0f172a;
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .nav-tabs .nav-link {
            color: #64748b;
            font-weight: 500;
            border: none;
            border-bottom: 3px solid transparent;
            padding: 12px 16px;
        }
        .nav-tabs .nav-link.active {
            color: #0f172a;
            border-bottom-color: #3b82f6;
            background: transparent;
            font-weight: 700;
        }
        .demo-badge {
            cursor: pointer;
            transition: all 0.2s;
        }
        .demo-badge:hover {
            transform: translateY(-2px);
            filter: brightness(0.9);
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <i class="bi bi-shield-lock-fill display-4 text-primary mb-2"></i>
        <h3 class="fw-bold mb-1">CampusGuardian</h3>
        <p class="text-white-50 mb-0 fs-6">Smart Student Monitoring, Approval & Alert System</p>
    </div>

    <div class="p-4">
        <?php echo get_flash(); ?>

        <!-- Role Selector Tabs -->
        <ul class="nav nav-tabs nav-fill mb-4" id="loginTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="student-tab" data-bs-toggle="tab" data-bs-target="#student-login" type="button"><i class="bi bi-person-circle"></i> Student</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="parent-tab" data-bs-toggle="tab" data-bs-target="#parent-login" type="button"><i class="bi bi-people-fill"></i> Parent</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="staff-tab" data-bs-toggle="tab" data-bs-target="#staff-login" type="button"><i class="bi bi-person-badge"></i> Staff</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="hod-tab" data-bs-toggle="tab" data-bs-target="#hod-login" type="button"><i class="bi bi-person-gear"></i> HOD</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="admin-tab" data-bs-toggle="tab" data-bs-target="#admin-login" type="button"><i class="bi bi-shield-lock"></i> Admin</button>
            </li>
        </ul>

        <!-- Login Form -->
        <form action="login.php" method="POST">
            <input type="hidden" name="role" id="selected_role" value="student">

            <div class="mb-3">
                <label class="form-label fw-semibold"><i class="bi bi-envelope me-1"></i> Email Address</label>
                <input type="email" name="email" id="email_input" class="form-control form-control-lg fs-6" placeholder="name@campusguardian.edu" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold"><i class="bi bi-lock me-1"></i> Password</label>
                <input type="password" name="password" id="password_input" class="form-control form-control-lg fs-6" placeholder="••••••••" required>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label text-muted small" for="remember">Remember me</label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100 fw-semibold shadow-sm mb-3">
                Sign In to Account <i class="bi bi-arrow-right-short ms-1 fs-5"></i>
            </button>
        </form>

        <div class="text-center p-2 rounded bg-light border mb-3">
            <span class="text-muted small">New Student?</span> 
            <a href="register.php" class="fw-bold text-primary text-decoration-none small ms-1"><i class="bi bi-person-plus-fill me-1"></i> Self Register Student Account</a>
        </div>

        <!-- Quick Demo Credentials Shortcuts -->
        <div class="pt-2 border-top">
            <div class="small fw-bold text-muted mb-2 text-uppercase">Quick One-Click Demo Logins:</div>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge bg-secondary demo-badge p-2" onclick="setDemo('rahul.mca24@campusguardian.edu', 'password123', 'student', 'student-tab')">🎓 Student: Rahul</span>
                <span class="badge bg-success demo-badge p-2" onclick="setDemo('parent.rahul@example.com', 'password123', 'parent', 'parent-tab')">👨‍👩‍👦 Parent: Suresh</span>
                <span class="badge bg-info text-dark demo-badge p-2" onclick="setDemo('staff.sarah@campusguardian.edu', 'password123', 'staff', 'staff-tab')">👩‍🏫 Staff: Sarah</span>
                <span class="badge bg-warning text-dark demo-badge p-2" onclick="setDemo('hod.mca@campusguardian.edu', 'password123', 'hod', 'hod-tab')">👨‍💼 HOD: Dr. Aris</span>
                <span class="badge bg-dark demo-badge p-2" onclick="setDemo('admin@campusguardian.edu', 'password123', 'admin', 'admin-tab')">⚙️ Admin Portal</span>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $('#loginTabs button').on('click', function () {
        const role = $(this).attr('id').replace('-tab', '');
        $('#selected_role').val(role);
    });

    function setDemo(email, pass, role, tabId) {
        $('#email_input').val(email);
        $('#password_input').val(pass);
        $('#selected_role').val(role);
        $('#' + tabId).tab('show');
    }
</script>
</body>
</html>
