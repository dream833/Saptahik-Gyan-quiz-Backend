<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require 'db.php';      // Must create $pdo here
require 'utils.php';   // Must define sendResponse()

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, "Only POST method allowed", null, 405);
}

$data = json_decode(file_get_contents("php://input"));

$email = trim($data->email ?? '');
$password = trim($data->password ?? '');

// Check input
if (empty($email) || empty($password)) {
    sendResponse(false, "Email or password missing", null, 400);
}

try {
    // Prepare PDO query
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        if ($password === $admin['password']) {  // Note: Plain password check
            // Set session
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_email'] = $admin['email'];

            sendResponse(true, "Login successful", null, 200);
        } else {
            sendResponse(false, "Invalid email or password", null, 401);
        }
    } else {
        sendResponse(false, "Invalid email or password", null, 401);
    }
} catch (PDOException $e) {
    sendResponse(false, "Database error: " . $e->getMessage(), null, 500);
}