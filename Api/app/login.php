<?php

header("Content-Type: application/json");

require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$mobile   = trim($data['mobile'] ?? '');
$password = trim($data['password'] ?? '');

if (empty($mobile) || empty($password)) {
    echo json_encode([
        "status" => false,
        "message" => "Mobile and Password Required"
    ]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT *
    FROM users
    WHERE mobile = ?
    LIMIT 1
");

$stmt->execute([$mobile]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode([
        "status" => false,
        "message" => "User Not Found"
    ]);
    exit;
}

if (!password_verify($password, $user['password'])) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid Password"
    ]);
    exit;
}

$pdo->prepare("
    UPDATE users
    SET last_login = NOW()
    WHERE id = ?
")->execute([$user['id']]);

echo json_encode([
    "status" => true,
    "message" => "Login Successful",
    "user" => [
        "id" => $user['id'],
        "name" => $user['full_name'],
        "email" => $user['email'],
        "mobile" => $user['mobile']
    ]
]);