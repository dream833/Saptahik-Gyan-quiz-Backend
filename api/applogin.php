<?php
header('Content-Type: application/json');
require_once 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
$phone = $data['phone'] ?? '';
$password = $data['password'] ?? '';

if (!$phone || !$password) {
    echo json_encode([
        "success" => false,
        "message" => "Phone and Password are required"
    ]);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ?");
$stmt->execute([$phone]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode([
        "success" => false,
        "message" => "User not found"
    ]);
    exit;
}

if (!password_verify($password, $user['password'])) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid password"
    ]);
    exit;
}

echo json_encode([
    "success" => true,
    "message" => "Login successful",
    "data" => [
        "user" => [
            "id" => $user['id'],
            "phone" => $user['phone']
        ]
    ]
]);
?>
