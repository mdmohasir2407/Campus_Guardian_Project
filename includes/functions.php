<?php
/**
 * CampusGuardian - Global Helper Functions
 */

if (!function_exists('sanitize')) {
    function sanitize($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = sanitize($value);
            }
            return $data;
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('redirect')) {
    function redirect($path) {
        if (strpos($path, 'http') === 0) {
            header("Location: " . $path);
        } else {
            $url = BASE_URL . '/' . ltrim($path, '/');
            header("Location: " . $url);
        }
        exit;
    }
}

if (!function_exists('set_flash')) {
    function set_flash($type, $message) {
        $_SESSION['flash'] = [
            'type' => $type, // success, danger, warning, info
            'message' => $message
        ];
    }
}

if (!function_exists('get_flash')) {
    function get_flash() {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return "<div class='alert alert-{$flash['type']} alert-dismissible fade show shadow-sm border-0 mb-4' role='alert'>
                        <div class='d-flex align-items-center'>
                            <i class='bi " . ($flash['type'] === 'success' ? 'bi-check-circle-fill' : ($flash['type'] === 'danger' ? 'bi-exclamation-triangle-fill' : 'bi-info-circle-fill')) . " me-2 fs-5'></i>
                            <div>" . htmlspecialchars($flash['message']) . "</div>
                        </div>
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                    </div>";
        }
        return '';
    }
}

if (!function_exists('get_db_setting')) {
    function get_db_setting($key, $default = '') {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
            $stmt->execute([$key]);
            $res = $stmt->fetch();
            return $res ? $res['setting_value'] : $default;
        } catch (Exception $e) {
            return $default;
        }
    }
}

if (!function_exists('add_notification')) {
    function add_notification($user_id, $title, $message, $type = 'info') {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("INSERT INTO notifications (user_id, title, message, type, is_read) VALUES (?, ?, ?, ?, 0)");
            $stmt->execute([$user_id, $title, $message, $type]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}

if (!function_exists('upload_file')) {
    function upload_file($file_key, $destination_dir, $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']) {
        if (!isset($_FILES[$file_key]) || $_FILES[$file_key]['error'] !== UPLOAD_ERR_OK) {
            return ['status' => false, 'message' => 'No file uploaded or file upload error.'];
        }

        $file = $_FILES[$file_key];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_extensions)) {
            return ['status' => false, 'message' => 'Invalid file extension. Allowed: ' . implode(', ', $allowed_extensions)];
        }

        if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
            return ['status' => false, 'message' => 'File size exceeds maximum limit of 5MB.'];
        }

        if (!file_exists($destination_dir)) {
            mkdir($destination_dir, 0777, true);
        }

        $new_filename = uniqid('cg_', true) . '.' . $file_ext;
        $target_path = rtrim($destination_dir, '/') . '/' . $new_filename;

        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            return ['status' => true, 'filename' => $new_filename, 'filepath' => $target_path];
        }

        return ['status' => false, 'message' => 'Failed to save file to destination directory.'];
    }
}

if (!function_exists('format_time')) {
    function format_time($timeStr) {
        if (!$timeStr) return '-';
        return date('h:i A', strtotime($timeStr));
    }
}

if (!function_exists('format_date')) {
    function format_date($dateStr) {
        if (!$dateStr) return '-';
        return date('d M Y', strtotime($dateStr));
    }
}

if (!function_exists('get_badge_class')) {
    function get_badge_class($status) {
        switch (strtolower($status)) {
            case 'approved':
            case 'present':
                return 'bg-success';
            case 'pending':
                return 'bg-warning text-dark';
            case 'rejected':
            case 'absent':
            case 'not informed':
                return 'bg-danger';
            case 'late':
                return 'bg-info text-dark';
            case 'leave':
            case 'half day':
                return 'bg-primary';
            default:
                return 'bg-secondary';
        }
    }
}
