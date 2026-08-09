<?php
/**
 * CampusGuardian - Role-Based Dynamic Sidebar Navigation
 */
$role = $_SESSION['role'] ?? '';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<nav id="sidebar">
    <div class="sidebar-brand d-flex align-items-center gap-2">
        <i class="bi bi-shield-check text-primary fs-3"></i>
        <div>
            <div class="lh-1">CampusGuardian</div>
            <small class="text-muted fw-normal" style="font-size: 0.72rem;">Monitoring & Alert System</small>
        </div>
    </div>

    <ul class="list-unstyled components">
        <?php if ($role === ROLE_ADMIN): ?>
            <li class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/admin/dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
            </li>
            <li class="<?php echo $currentPage === 'students.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/admin/students.php"><i class="bi bi-people"></i> Manage Students</a>
            </li>
            <li class="<?php echo $currentPage === 'staff.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/admin/staff.php"><i class="bi bi-person-badge"></i> Manage Staff</a>
            </li>
            <li class="<?php echo $currentPage === 'hod.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/admin/hod.php"><i class="bi bi-person-gear"></i> Manage HODs</a>
            </li>
            <li class="<?php echo $currentPage === 'departments.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/admin/departments.php"><i class="bi bi-building"></i> Departments</a>
            </li>
            <li class="<?php echo $currentPage === 'parents.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/admin/parents.php"><i class="bi bi-people-fill"></i> Parent Monitoring</a>
            </li>
            <li class="<?php echo $currentPage === 'reports.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/admin/reports.php"><i class="bi bi-file-earmark-bar-graph"></i> System Reports</a>
            </li>
            <li class="<?php echo $currentPage === 'settings.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/admin/settings.php"><i class="bi bi-sliders"></i> Settings</a>
            </li>

        <?php elseif ($role === ROLE_HOD): ?>
            <li class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/hod/dashboard.php"><i class="bi bi-speedometer2"></i> HOD Dashboard</a>
            </li>
            <li class="<?php echo $currentPage === 'approvals.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/hod/approvals.php"><i class="bi bi-check2-square"></i> Final Approvals</a>
            </li>
            <li class="<?php echo $currentPage === 'reports.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/hod/reports.php"><i class="bi bi-bar-chart-line"></i> Dept Analytics</a>
            </li>
            <li class="<?php echo $currentPage === 'performance.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/hod/performance.php"><i class="bi bi-graph-up-arrow"></i> Student Performance</a>
            </li>
            <li class="<?php echo $currentPage === 'profile.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/hod/profile.php"><i class="bi bi-person-circle"></i> Profile</a>
            </li>

        <?php elseif ($role === ROLE_STAFF): ?>
            <li class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/staff/dashboard.php"><i class="bi bi-speedometer2"></i> Staff Dashboard</a>
            </li>
            <li class="<?php echo $currentPage === 'requests.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/staff/requests.php"><i class="bi bi-inbox"></i> Pending Requests</a>
            </li>
            <li class="<?php echo $currentPage === 'history.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/staff/history.php"><i class="bi bi-clock-history"></i> Approval History</a>
            </li>
            <li class="<?php echo $currentPage === 'profile.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/staff/profile.php"><i class="bi bi-person-circle"></i> Profile</a>
            </li>

        <?php elseif ($role === ROLE_STUDENT): ?>
            <li class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/student/dashboard.php"><i class="bi bi-grid-1x2"></i> Student Overview</a>
            </li>
            <li class="<?php echo $currentPage === 'late_entry.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/student/late_entry.php"><i class="bi bi-clock-fill"></i> Late Entry Form</a>
            </li>
            <li class="<?php echo $currentPage === 'leave_request.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/student/leave_request.php"><i class="bi bi-calendar-event"></i> Apply Leave</a>
            </li>
            <li class="<?php echo $currentPage === 'half_day.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/student/half_day.php"><i class="bi bi-hourglass-split"></i> Half Day Permission</a>
            </li>
            <li class="<?php echo $currentPage === 'status.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/student/status.php"><i class="bi bi-list-check"></i> Request Status</a>
            </li>
            <li class="<?php echo $currentPage === 'notifications.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/student/notifications.php"><i class="bi bi-bell"></i> Notifications</a>
            </li>
            <li class="<?php echo $currentPage === 'profile.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/student/profile.php"><i class="bi bi-person"></i> Profile</a>
            </li>

        <?php elseif ($role === ROLE_PARENT): ?>
            <li class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/parent/dashboard.php"><i class="bi bi-speedometer2"></i> Parent Dashboard</a>
            </li>
            <li class="<?php echo $currentPage === 'notifications.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/parent/notifications.php"><i class="bi bi-bell-fill"></i> Leave Notifications</a>
            </li>
            <li class="<?php echo $currentPage === 'reports.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/parent/reports.php"><i class="bi bi-calendar-range"></i> Monthly Leave Report</a>
            </li>
            <li class="<?php echo $currentPage === 'profile.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/parent/profile.php"><i class="bi bi-person-badge"></i> Ward Profile</a>
            </li>
        <?php endif; ?>
    </ul>

    <div class="p-3 mt-auto border-top border-secondary text-center text-muted small">
        <div>CampusGuardian v<?php echo APP_VERSION; ?></div>
        <div class="text-xs">PHP 8 Core Edition</div>
    </div>
</nav>

<!-- Backdrop Overlay for Animated Sliding Drawer -->
<div id="sidebarOverlay" class="sidebar-overlay"></div>
