<?php
/**
 * API Configuration
 * 
 * Centralized base URL configuration for all API endpoints.
 * Automatically detects environment (localhost vs production).
 * 
 * Usage in JavaScript (via login.php):
 *   const ADMIN_API = "<?= ADMIN_API_URL ?>";
 *   fetch(ADMIN_API + 'admin_login.php', { ... })
 * 
 * Usage in PHP:
 *   $url = ADMIN_API_URL . 'admin_login.php';
 */

// ============================================
// Auto-detect BASE URL based on environment
// ============================================
// Production:        'https://saptahikgyan.space/wb-admin/'
// Local development: 'http://localhost/wb-admin/'
// ============================================

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Get the base path (e.g., /wb-admin/)
$scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
// Go up from /utils/ to project root
$basePath = dirname($scriptDir) . '/';
// Normalize: replace backslashes, ensure trailing slash
$basePath = rtrim(str_replace('\\', '/', $basePath), '/') . '/';

// Fallback if auto-detection fails (e.g., CLI or unusual setup)
if ($basePath === '/' || empty($host)) {
    $host = 'saptahikgyan.space';
    $basePath = '/wb-admin/';
    $protocol = 'https';
}

define('BASE_URL', "$protocol://$host$basePath");

// ============================================
// API Base URLs (append endpoint name)
// ============================================

// Admin API endpoints (e.g., admin_login.php, fetch-user.php, add-subject.php, etc.)
define('ADMIN_API_URL', BASE_URL . 'Api/admin/');

// App/User API endpoints (e.g., login.php, signup.php, get-class.php, etc.)
define('APP_API_URL', BASE_URL . 'Api/app/');

?>
