<?php
/**
 * CampusGuardian - Admin Dashboard
 */
require_once __DIR__ . '/../includes/auth_check.php';
check_auth([ROLE_ADMIN]);

$page_title = "Admin Overview Dashboard";
$db = Database::getConnection();

// Metrics queries
$total_students = $db->query("SELECT COUNT(*) FROM students")->fetchColumn();
$total_staff = $db->query("SELECT COUNT(*) FROM staff")->fetchColumn();
$total_hod = $db->query("SELECT COUNT(*) FROM hod")->fetchColumn();

// Today's attendance stats
$today = date('Y-m-d');
$today_present = $db->query("SELECT COUNT(*) FROM attendance WHERE date = '$today' AND status = 'Present'")->fetchColumn();
$today_late = $db->query("SELECT COUNT(*) FROM attendance WHERE date = '$today' AND status = 'Late'")->fetchColumn();
$today_leave = $db->query("SELECT COUNT(*) FROM attendance WHERE date = '$today' AND status IN ('Leave', 'Half Day')")->fetchColumn();
$today_absent = $db->query("SELECT COUNT(*) FROM attendance WHERE date = '$today' AND status IN ('Absent', 'Not Informed')")->fetchColumn();

// Recent late entries
$stmt_recent_late = $db->query("SELECT l.*, s.name as student_name, s.register_number, d.dept_code FROM late_entries l JOIN students s ON l.student_id = s.id JOIN departments d ON s.department_id = d.id ORDER BY l.id DESC LIMIT 5");
$recent_lates = $stmt_recent_late->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div id="content">
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container-fluid p-4">
        <?php echo get_flash(); ?>

        <!-- Welcome Banner -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">System Administration Dashboard</h3>
                <p class="text-muted mb-0">Overview of campus monitoring, approval workflows, and system statistics.</p>
            </div>
            <div>
                <a href="parents.php" class="btn btn-outline-success shadow-sm me-2"><i class="bi bi-people-fill me-1"></i> Parent Monitoring</a>
                <a href="reports.php" class="btn btn-outline-primary shadow-sm me-2"><i class="bi bi-file-earmark-pdf me-1"></i> System Reports</a>
                <a href="settings.php" class="btn btn-primary shadow-sm"><i class="bi bi-gear-fill me-1"></i> Settings</a>
            </div>
        </div>

        <!-- Metric Stat Cards -->
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card card-stat p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fw-medium small text-uppercase">Total Students</span>
                            <h2 class="fw-bold text-dark mb-0 mt-1"><?php echo number_format($total_students); ?></h2>
                        </div>
                        <div class="stat-icon bg-primary-soft"><i class="bi bi-people-fill"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-stat p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fw-medium small text-uppercase">Today Present</span>
                            <h2 class="fw-bold text-success mb-0 mt-1"><?php echo number_format($today_present); ?></h2>
                        </div>
                        <div class="stat-icon bg-success-soft"><i class="bi bi-check-circle-fill"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-stat p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fw-medium small text-uppercase">Today Late Entries</span>
                            <h2 class="fw-bold text-info mb-0 mt-1"><?php echo number_format($today_late); ?></h2>
                        </div>
                        <div class="stat-icon bg-info-soft"><i class="bi bi-clock-history"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-stat p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fw-medium small text-uppercase">Not Informed / Absent</span>
                            <h2 class="fw-bold text-danger mb-0 mt-1"><?php echo number_format($today_absent); ?></h2>
                        </div>
                        <div class="stat-icon bg-danger-soft"><i class="bi bi-exclamation-octagon-fill"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts & Recent Activity Row -->
        <div class="row g-4 mb-4">
            <div class="col-lg-5">
                <div class="card card-stat p-4 h-100">
                    <h5 class="fw-bold mb-3"><i class="bi bi-pie-chart-fill text-primary me-2"></i> Today's Attendance Summary</h5>
                    <div style="height: 250px;" class="position-relative">
                        <canvas id="attendanceChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card card-stat p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0"><i class="bi bi-clock-fill text-warning me-2"></i> Recent Late Entry Records</h5>
                        <a href="reports.php?type=late" class="btn btn-sm btn-link text-decoration-none">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Dept</th>
                                    <th>Arrival</th>
                                    <th>Delay</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_lates)): ?>
                                    <tr><td colspan="5" class="text-center text-muted py-3">No late entry records found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($recent_lates as $l): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-semibold"><?php echo htmlspecialchars($l['student_name']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($l['register_number']); ?></small>
                                            </td>
                                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($l['dept_code']); ?></span></td>
                                            <td><?php echo format_time($l['arrival_time']); ?></td>
                                            <td><span class="text-danger fw-medium"><?php echo $l['late_minutes']; ?> min</span></td>
                                            <td><span class="badge badge-status <?php echo get_badge_class($l['status']); ?>"><?php echo strtoupper($l['status']); ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<script>
    $(document).ready(function() {
        initAttendanceDoughnutChart('attendanceChart', <?php echo $today_present; ?>, <?php echo $today_late; ?>, <?php echo $today_leave; ?>, <?php echo $today_absent; ?>);
    });
</script>
