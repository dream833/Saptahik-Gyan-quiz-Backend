<?php

header("Content-Type: application/json");

require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$full_name = trim($data['full_name'] ?? '');
$email     = trim($data['email'] ?? '');
$mobile    = trim($data['mobile'] ?? '');
$password  = trim($data['password'] ?? '');

if (
    empty($full_name) ||
    empty($email) ||
    empty($mobile) ||
    empty($password)
) {
    echo json_encode([
        "success" => false,
        "message" => "All fields are required"
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid email"
    ]);
    exit;
}

$stmt = $pdo->prepare(
    "SELECT id FROM users WHERE email = ? OR mobile = ?"
);
$stmt->execute([$email, $mobile]);

if ($stmt->fetch()) {
    echo json_encode([
        "success" => false,
        "message" => "Email or mobile already exists"
    ]);
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("
    INSERT INTO users
    (full_name, email, mobile, password)
    VALUES
    (?, ?, ?, ?)
");

$stmt->execute([
    $full_name,
    $email,
    $mobile,
    $hashedPassword
]);

$user_id = $pdo->lastInsertId();

echo json_encode([
    "success" => true,
    "message" => "Registration successful",
    "data" => [
        "user_id" => $user_id,
        "full_name" => $full_name,
        "email" => $email,
        "mobile" => $mobile
    ]
]);