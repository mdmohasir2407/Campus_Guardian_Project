<?php
/**
 * CampusGuardian - Authentication & Authorization Middleware
 */

require_once __DIR__ . '/../config/config.php';

function check_auth($allowed_roles = []) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        set_flash('danger', 'Please login to access the system.');
        redirect('index.php');
    }

    if (!empty($allowed_roles)) {
        if (!in_array($_SESSION['role'], (array)$allowed_roles)) {
            set_flash('danger', 'Unauthorized access level for this module.');
            
            // Redirect user to their own role dashboard
            switch ($_SESSION['role']) {
                case ROLE_ADMIN:
                    redirect('admin/dashboard.php');
                    break;
                case ROLE_HOD:
                    redirect('hod/dashboard.php');
                    break;
                case ROLE_STAFF:
                    redirect('staff/dashboard.php');
                    break;
                case ROLE_STUDENT:
                    redirect('student/dashboard.php');
                    break;
                case ROLE_PARENT:
                    redirect('parent/dashboard.php');
                    break;
                default:
                    redirect('logout.php');
            }
        }
    }
}
