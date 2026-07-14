<?php
/**
 * API Configuration
 * 
 * Centralized base URL configuration for all API endpoints.
 * Change BASE_URL based on your environment (local/dev/production).
 * 
 * Usage in JavaScript (via login.php):
 *   const ADMIN_API = "<?= ADMIN_API_URL ?>";
 *   fetch(ADMIN_API + 'admin_login.php', { ... })
 * 
 * Usage in PHP:
 *   $url = ADMIN_API_URL . 'admin_login.php';
 */

// ============================================
// BASE URL - Change this per environment
// ============================================
// Local development: 'http://localhost/wb-admin/'
// Production:        'https://saptahikgyan.space/wb-admin/'
// ============================================
define('BASE_URL', 'https://saptahikgyan.space/wb-admin/');

// ============================================
// API Base URLs (append endpoint name)
// ============================================

// Admin API endpoints (e.g., admin_login.php, fetch-user.php, add-subject.php, etc.)
define('ADMIN_API_URL', BASE_URL . 'Api/admin/');

// App/User API endpoints (e.g., login.php, signup.php, get-class.php, etc.)
define('APP_API_URL', BASE_URL . 'Api/app/');

?>
