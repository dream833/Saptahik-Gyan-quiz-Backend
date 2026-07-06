<?php

header("Content-Type: application/json");

require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$email = trim($data['email'] ?? '');
$password = trim($data['password'] ?? '');

if (empty($email) || empty($password)) {
    echo json_encode([
        "status" => false,
        "message" => "Email and Password are required"
    ]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT *
    FROM admins
    WHERE email = ?
    AND status = 'active'
    LIMIT 1
");

$stmt->execute([$email]);

$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    echo json_encode([
        "status" => false,
        "message" => "Admin not found"
    ]);
    exit;
}

if ($password !== $admin['password']) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid password"
    ]);
    exit;
}

echo json_encode([
    "status" => true,
    "message" => "Login successful",
    "data" => [
        "id" => $admin['id'],
        "name" => $admin['name'],
        "email" => $admin['email'],
        "role" => $admin['role']
    ]
]);