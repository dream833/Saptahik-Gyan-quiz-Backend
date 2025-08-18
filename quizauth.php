<?php
// Prevent session already started notice
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Example: check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: adminlogin.php");
    exit;
}
?>