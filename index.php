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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
        }
        .split-container {
            display: flex;
            min-height: 100vh;
        }

        /* ===== LEFT SIDE: Background Image + Branding ===== */
        .left-panel {
            flex: 1;
            position: relative;
            background: url('assets/images/pexels-photo-14905511.jpg') no-repeat center center / cover;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 50px;
            overflow: hidden;
        }
        .left-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.3) 0%, rgba(15, 23, 42, 0.6) 60%, rgba(15, 23, 42, 0.85) 100%);
            z-index: 1;
        }
        .left-content {
            position: relative;
            z-index: 2;
            color: #ffffff;
        }
        .left-content h1 {
            font-size: 2.8rem;
            font-weight: 800;
            line-height: 1.15;
            margin-bottom: 16px;
        }
        .left-content p {
            font-size: 1.05rem;
            color: rgba(255, 255, 255, 0.7);
            max-width: 500px;
            line-height: 1.6;
        }
        .left-stats {
            display: flex;
            gap: 30px;
            margin-top: 30px;
        }
        .left-stat-item .stat-val {
            font-size: 1.8rem;
            font-weight: 800;
            color: #3b82f6;
        }
        .left-stat-item .stat-label {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.5);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ===== RIGHT SIDE: Login Form ===== */
        .right-panel {
            width: 520px;
            min-width: 420px;
            background: #0f172a;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 50px 45px;
            overflow-y: auto;
            color: #e2e8f0;
        }
        .brand-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 35px;
        }
        .brand-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.5rem;
        }
        .brand-row h2 {
            font-weight: 700;
            font-size: 1.5rem;
            color: #ffffff;
            margin: 0;
        }
        .brand-row h2 span {
            color: #3b82f6;
        }
        .welcome-text {
            font-size: 1.6rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 6px;
        }
        .welcome-sub {
            font-size: 0.92rem;
            color: #64748b;
            margin-bottom: 28px;
        }

        /* Tabs */
        .role-tabs-row {
            display: flex;
            gap: 4px;
            background: rgba(255, 255, 255, 0.04);
            border-radius: 12px;
            padding: 5px;
            margin-bottom: 28px;
        }
        .role-tab-btn {
            flex: 1;
            text-align: center;
            padding: 10px 6px;
            border: none;
            background: transparent;
            color: #64748b;
            font-weight: 600;
            font-size: 0.78rem;
            border-radius: 9px;
            cursor: pointer;
            transition: all 0.35s cubic-bezier(0.22, 1, 0.36, 1);
            position: relative;
            overflow: hidden;
        }
        .role-tab-btn:hover {
            color: #94a3b8;
            background: rgba(255, 255, 255, 0.06);
            transform: translateY(-1px);
        }
        .role-tab-btn:active {
            transform: scale(0.92);
        }
        .role-tab-btn.active {
            background: #3b82f6;
            color: #ffffff;
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.45);
            transform: translateY(-2px);
        }
        .role-tab-btn i {
            display: block;
            font-size: 1.1rem;
            margin-bottom: 3px;
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .role-tab-btn.active i {
            transform: scale(1.18);
        }


        /* Form Inputs */
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #94a3b8;
            margin-bottom: 8px;
        }
        .input-wrap {
            position: relative;
        }
        .input-wrap input {
            width: 100%;
            padding: 14px 16px;
            padding-right: 48px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #f1f5f9;
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            outline: none;
            transition: all 0.2s ease;
        }
        .input-wrap input::placeholder {
            color: rgba(255, 255, 255, 0.25);
        }
        .input-wrap input:focus {
            border-color: #3b82f6;
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }
        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #475569;
            font-size: 1.1rem;
            pointer-events: none;
        }
        .input-wrap.has-icon input {
            padding-left: 46px;
        }

        /* Eye toggle */
        .btn-eye {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #475569;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 4px;
            z-index: 10;
            transition: color 0.15s, transform 0.15s;
        }
        .btn-eye:hover {
            color: #3b82f6;
            transform: translateY(-50%) scale(1.1);
        }

        /* Remember + Submit */
        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }
        .btn-signin {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            border-radius: 12px;
            color: #ffffff;
            font-size: 1rem;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(59, 130, 246, 0.35);
        }
        .btn-signin:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.45);
        }

        /* Register link */
        .register-link {
            text-align: center;
            margin-top: 20px;
            font-size: 0.88rem;
            color: #64748b;
        }
        .register-link a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 600;
        }
        .register-link a:hover {
            text-decoration: underline;
        }

        /* Demo badges */
        .demo-section {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
        }
        .demo-label {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #475569;
            margin-bottom: 10px;
        }
        .demo-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .demo-badge {
            padding: 7px 12px;
            border-radius: 8px;
            font-size: 0.78rem;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.04);
            color: #94a3b8;
            transition: all 0.2s;
        }
        .demo-badge:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #e2e8f0;
            transform: translateY(-2px);
            border-color: rgba(255, 255, 255, 0.15);
        }

        /* ===== Responsive Breakpoints ===== */
        /* Mobile View (<= 575px) */
        @media (max-width: 575px) {
            body {
                background: url('assets/images/pexels-photo-14905511.jpg') no-repeat center center / cover;
                background-attachment: fixed;
            }
            .left-panel {
                display: none !important;
            }
            .right-panel {
                width: 100% !important;
                min-width: 100% !important;
                background: rgba(15, 23, 42, 0.65) !important;
                backdrop-filter: blur(6px);
                -webkit-backdrop-filter: blur(6px);
                padding: 30px 20px;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                justify-content: center;
                color: #ffffff !important;
            }
            .welcome-sub {
                color: #cbd5e1 !important;
                font-weight: 500;
            }
            .form-group label {
                color: #f8fafc !important;
                font-weight: 600;
            }
            .role-tab-btn {
                color: #e2e8f0 !important;
                background: rgba(255, 255, 255, 0.1) !important;
            }
            .role-tab-btn.active {
                color: #ffffff !important;
                background: #3b82f6 !important;
            }
            .input-wrap input {
                background: rgba(15, 23, 42, 0.75) !important;
                border-color: rgba(255, 255, 255, 0.3) !important;
                color: #ffffff !important;
            }
            .input-wrap input::placeholder {
                color: rgba(255, 255, 255, 0.55) !important;
            }
            .remember-row label, .forgot-link {
                color: #e2e8f0 !important;
            }
            .demo-title {
                color: #cbd5e1 !important;
            }
        }

        /* Tablet & Mid-Size Screens (576px - 994px) */
        @media (min-width: 576px) and (max-width: 994px) {
            .split-container {
                display: flex;
                flex-direction: row;
                min-height: 100vh;
            }
            .left-panel {
                flex: 1;
                min-width: 0;
                padding: 35px 25px;
                display: flex;
                flex-direction: column;
                justify-content: flex-end;
            }
            .left-content h1 {
                font-size: 1.8rem;
                margin-bottom: 12px;
            }
            .left-content p {
                font-size: 0.88rem;
                margin-bottom: 20px;
            }
            .left-stats {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
                margin-top: 20px;
            }
            .left-stat-item .stat-val {
                font-size: 1.4rem;
            }
            .left-stat-item .stat-label {
                font-size: 0.72rem;
            }
            .right-panel {
                width: 440px;
                min-width: 320px;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                justify-content: center;
                padding: 35px 25px;
            }
        }
    </style>
</head>
<body>

<!-- Full Page Refresh Overlay -->
<div id="page-refresh-overlay"></div>

<div class="split-container">
    <!-- ===== LEFT: Campus Image + Branding ===== -->
    <div class="left-panel">
        <div class="left-content">
            <h1>Rajiv Gandhi <br>College Of Engineering <br>And Technology</h1>
            <p>Simplifies student request management with automated approvals, real-time notifications, secure authentication, and seamless communication between Students, Staff, HOD, Parents, and Admin.</p>
            
            <div class="left-stats">
                <div class="left-stat-item">
                    <div class="stat-val">1000+</div>
                    <div class="stat-label">Active Students</div>
                </div>
                <div class="left-stat-item">
                    <div class="stat-val">100%</div>
                    <div class="stat-label">Alert Reliability</div>
                </div>
                <div class="left-stat-item">
                    <div class="stat-val">5</div>
                    <div class="stat-label">Role Portals</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== RIGHT: Login Form ===== -->
    <div class="right-panel">
        <div class="brand-row stagger-item">
            <div class="brand-icon"><i class="bi bi-shield-check"></i></div>
            <h2>Campus <span>Guardian</span></h2>
        </div>

        <div class="welcome-text stagger-item">Welcome back</div>
        <div class="welcome-sub stagger-item">Sign in to access your portal dashboard</div>

        <?php echo get_flash(); ?>

        <!-- Role Selector Tabs -->
        <div class="role-tabs-row stagger-item">
            <button type="button" class="role-tab-btn active" data-role="student" id="student-tab">
                <i class="bi bi-person-circle"></i> Student
            </button>
            <button type="button" class="role-tab-btn" data-role="parent" id="parent-tab">
                <i class="bi bi-people-fill"></i> Parent
            </button>
            <button type="button" class="role-tab-btn" data-role="staff" id="staff-tab">
                <i class="bi bi-person-badge"></i> Staff
            </button>
            <button type="button" class="role-tab-btn" data-role="hod" id="hod-tab">
                <i class="bi bi-person-gear"></i> HOD
            </button>
            <button type="button" class="role-tab-btn" data-role="admin" id="admin-tab">
                <i class="bi bi-shield-lock"></i> Admin
            </button>
        </div>

        <!-- Login Form -->
        <form action="login.php" method="POST">
            <input type="hidden" name="role" id="selected_role" value="student">

            <div class="form-group stagger-item">
                <label>Email Address</label>
                <div class="input-wrap has-icon">
                    <i class="bi bi-envelope input-icon"></i>
                    <input type="email" name="email" id="email_input" placeholder="number@rgcet.edu.in" required>
                </div>
            </div>

            <div class="form-group stagger-item">
                <label>Password</label>
                <div class="input-wrap has-icon">
                    <i class="bi bi-lock input-icon"></i>
                    <input type="password" name="password" id="password_input" placeholder="Enter your password" required>
                    <button type="button" class="btn-eye" onclick="togglePasswordVisibility('password_input', this)" title="Show/Hide password">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                </div>
            </div>

            <div class="remember-row stagger-item">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label small" for="remember" style="color:#64748b;">Remember me</label>
                </div>
            </div>

            <button type="submit" class="btn-signin stagger-item">
                Sign In to Portal <i class="bi bi-arrow-right-short ms-1 fs-5"></i>
            </button>
        </form>

        <div class="register-link stagger-item">
            New Student? <a href="register.php"><i class="bi bi-person-plus-fill me-1"></i>Create Account</a>
        </div>

        <!-- Quick Demo Credentials -->
        <div class="demo-section stagger-item">
            <div class="demo-label"><i class="bi bi-lightning-charge-fill me-1" style="color:#f59e0b;"></i> Quick Demo Logins</div>
            <div class="demo-badges">
                <span class="demo-badge" onclick="setDemo('rahul.mca24@campusguardian.edu', 'password123', 'student', 'student-tab')">🎓 Student: Rahul</span>
                <span class="demo-badge" onclick="setDemo('parent.rahul@example.com', 'password123', 'parent', 'parent-tab')">👨‍👩‍👦 Parent: Suresh</span>
                <span class="demo-badge" onclick="setDemo('staff.sarah@campusguardian.edu', 'password123', 'staff', 'staff-tab')">👩‍🏫 Staff: Sarah</span>
                <span class="demo-badge" onclick="setDemo('hod.mca@campusguardian.edu', 'password123', 'hod', 'hod-tab')">👨‍💼 HOD: Dr. Aris</span>
                <span class="demo-badge" onclick="setDemo('admin@campusguardian.edu', 'password123', 'admin', 'admin-tab')">⚙️ Admin</span>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/main.js"></script>
<script>
    // Password eye toggle
    function togglePasswordVisibility(inputId, btnEl) {
        var input = document.getElementById(inputId);
        var icon = btnEl ? btnEl.querySelector('i') : null;
        if (!input) return;
        if (input.type === 'password') {
            input.type = 'text';
            if (icon) icon.className = 'bi bi-eye-fill';
            btnEl.style.color = '#3b82f6';
        } else {
            input.type = 'password';
            if (icon) icon.className = 'bi bi-eye-slash';
            btnEl.style.color = '#475569';
        }
    }

    // Role tab switching
    document.querySelectorAll('.role-tab-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.role-tab-btn').forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
            document.getElementById('selected_role').value = this.dataset.role;
        });
    });

    // 1-click demo fill
    function setDemo(email, pass, role, tabId) {
        document.getElementById('email_input').value = email;
        document.getElementById('password_input').value = pass;
        document.getElementById('selected_role').value = role;
        document.querySelectorAll('.role-tab-btn').forEach(function(b) { b.classList.remove('active'); });
        document.getElementById(tabId).classList.add('active');
    }
</script>
</body>
</html>
