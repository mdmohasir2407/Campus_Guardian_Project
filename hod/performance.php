<?php
/**
 * CampusGuardian - HOD Student Performance & Attendance Analytics
 */
require_once __DIR__ . '/../includes/auth_check.php';
check_auth([ROLE_HOD]);

$page_title = "Student Performance Analytics";
$db = Database::getConnection();
$dept_id = $_SESSION['department_id'] ?? 0;

// Fetch student risk list
$students_perf = $db->query("SELECT s.*,
    (SELECT COUNT(*) FROM late_entries WHERE student_id = s.id) as total_lates,
    (SELECT COUNT(*) FROM leave_requests WHERE student_id = s.id AND status = 'approved') as approved_leaves,
    (SELECT COUNT(*) FROM attendance WHERE student_id = s.id AND status IN ('Absent', 'Not Informed')) as unexcused_absences
    FROM students s WHERE s.department_id = $dept_id ORDER BY unexcused_absences DESC, total_lates DESC")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div id="content">
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container-fluid p-4">
        <?php echo get_flash(); ?>

        <div class="mb-4">
            <h3 class="fw-bold mb-1">Student Performance & Risk Analysis</h3>
            <p class="text-muted mb-0">Monitor department student attendance, late entry trends, and absence risks.</p>
        </div>

        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Student Details</th>
                            <th>Reg Number</th>
                            <th>Total Lates</th>
                            <th>Leaves Granted</th>
                            <th>Unexcused Absences</th>
                            <th>Attendance Risk Level</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($students_perf)): ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted">No student records found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($students_perf as $sp): 
                                $risk = 'LOW';
                                $risk_badge = 'bg-success';
                                if ($sp['unexcused_absences'] >= 3 || $sp['total_lates'] >= 5) {
                                    $risk = 'HIGH RISK';
                                    $risk_badge = 'bg-danger';
                                } elseif ($sp['unexcused_absences'] >= 1 || $sp['total_lates'] >= 2) {
                                    $risk = 'MODERATE';
                                    $risk_badge = 'bg-warning text-dark';
                                }
                            ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($sp['name']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($sp['student_email']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($sp['register_number']); ?></td>
                                    <td><span class="fw-bold text-dark"><?php echo $sp['total_lates']; ?></span></td>
                                    <td><span class="fw-bold text-primary"><?php echo $sp['approved_leaves']; ?></span></td>
                                    <td><span class="fw-bold text-danger"><?php echo $sp['unexcused_absences']; ?></span></td>
                                    <td><span class="badge <?php echo $risk_badge; ?> px-3 py-2"><?php echo $risk; ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
