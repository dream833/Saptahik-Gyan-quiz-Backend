<?php
header("Content-Type: application/json");
require 'db.php';

$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['phone']) || !isset($input['new_password'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Phone and new password required"
    ]);
    exit;
}

$phone = trim($input['phone']);
$new_password = password_hash($input['new_password'], PASSWORD_BCRYPT);

// check if user exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
$stmt->execute([$phone]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode([
        "status" => "error",
        "message" => "User not found with this phone number"
    ]);
    exit;
}

// update password
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE phone = ?");
$success = $stmt->execute([$new_password, $phone]);

if ($success) {
    echo json_encode([
        "status" => "success",
        "message" => "Password updated successfully"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to update password"
    ]);
}