<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$user_id = intval($data['user_id'] ?? 0);

if ($user_id <= 0) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid user id"
    ]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT
        id,
        full_name,
        email,
        mobile,
        address,
        class_grade,
        about_me,
        profile_image,
        created_at
    FROM users
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$user_id]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode([
        "status" => false,
        "message" => "User not found"
    ]);
    exit;
}

echo json_encode([
    "status" => true,
    "message" => "Profile fetched successfully",
    "data" => $user
]);