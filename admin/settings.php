<?php
/**
 * CampusGuardian - System Settings
 */
require_once __DIR__ . '/../includes/auth_check.php';
check_auth([ROLE_ADMIN]);

$page_title = "System Settings";
$db = Database::getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['settings'] as $key => $val) {
        $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$key, sanitize($val), sanitize($val)]);
    }
    set_flash('success', 'System settings saved successfully!');
    redirect('admin/settings.php');
}

$settings_raw = $db->query("SELECT * FROM settings")->fetchAll();
$settings = [];
foreach ($settings_raw as $s) {
    $settings[$s['setting_key']] = $s['setting_value'];
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div id="content">
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container-fluid p-4">
        <?php echo get_flash(); ?>

        <div class="mb-4">
            <h3 class="fw-bold mb-1">System Configuration Settings</h3>
            <p class="text-muted mb-0">Manage college start times, SMTP credentials, and automatic alert parameters.</p>
        </div>

        <form action="settings.php" method="POST">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm p-4">
                        <h5 class="fw-bold mb-3"><i class="bi bi-clock-history text-primary me-2"></i> Attendance & Cutoff Timings</h5>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">College Official Start Time *</label>
                            <input type="time" name="settings[college_start_time]" class="form-control" value="<?php echo htmlspecialchars($settings['college_start_time'] ?? '09:00:00'); ?>" required>
                            <small class="text-muted">Students arriving after this time are automatically logged as Late.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Automatic Absence Marking (09:00 AM)</label>
                            <select name="settings[auto_absence_enabled]" class="form-select">
                                <option value="1" <?php echo ($settings['auto_absence_enabled'] ?? '1') == '1' ? 'selected' : ''; ?>>Enabled - Mark missing students as Not Informed automatically</option>
                                <option value="0" <?php echo ($settings['auto_absence_enabled'] ?? '1') == '0' ? 'selected' : ''; ?>>Disabled</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Parent Email Alerts</label>
                            <select name="settings[parent_email_notify]" class="form-select">
                                <option value="1" <?php echo ($settings['parent_email_notify'] ?? '1') == '1' ? 'selected' : ''; ?>>Enabled - Send automatic email notifications to Parents</option>
                                <option value="0" <?php echo ($settings['parent_email_notify'] ?? '1') == '0' ? 'selected' : ''; ?>>Disabled</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Institution Name</label>
                            <input type="text" name="settings[system_institution_name]" class="form-control" value="<?php echo htmlspecialchars($settings['system_institution_name'] ?? 'CampusGuardian Institute'); ?>">
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm p-4">
                        <h5 class="fw-bold mb-3"><i class="bi bi-envelope-at-fill text-warning me-2"></i> SMTP Email Server Config</h5>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">SMTP Host</label>
                            <input type="text" name="settings[smtp_host]" class="form-control" value="<?php echo htmlspecialchars($settings['smtp_host'] ?? 'smtp.gmail.com'); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">SMTP Port</label>
                            <input type="text" name="settings[smtp_port]" class="form-control" value="<?php echo htmlspecialchars($settings['smtp_port'] ?? '587'); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">SMTP Username</label>
                            <input type="text" name="settings[smtp_user]" class="form-control" value="<?php echo htmlspecialchars($settings['smtp_user'] ?? 'alerts@campusguardian.edu'); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">SMTP Password</label>
                            <input type="password" name="settings[smtp_pass]" class="form-control" value="<?php echo htmlspecialchars($settings['smtp_pass'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Encryption</label>
                            <select name="settings[smtp_encryption]" class="form-select">
                                <option value="tls" <?php echo ($settings['smtp_encryption'] ?? 'tls') == 'tls' ? 'selected' : ''; ?>>TLS</option>
                                <option value="ssl" <?php echo ($settings['smtp_encryption'] ?? 'tls') == 'ssl' ? 'selected' : ''; ?>>SSL</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary btn-lg shadow-sm"><i class="bi bi-save me-1"></i> Save Configuration Settings</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
