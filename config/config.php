<?php
/**
 * CampusGuardian - General Configuration
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Set secure session parameters
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

// Timezone setup
date_default_timezone_set('Asia/Kolkata');

// Base URLs
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));

// Detect base folder path dynamically
$baseFolder = '/CampusGuardian';
if (strpos($_SERVER['REQUEST_URI'] ?? '', '/CampusGuardian') === false) {
    // In case running directly at root or custom port
    $baseFolder = '';
}

define('BASE_URL', $protocol . $host . $baseFolder);

// Include constants
require_once __DIR__ . '/constants.php';

// Include database
require_once __DIR__ . '/database.php';

// Include global helper functions
require_once __DIR__ . '/../includes/functions.php';
