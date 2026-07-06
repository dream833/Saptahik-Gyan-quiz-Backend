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
        u.id,
        u.full_name,
        u.email,
        u.mobile,
        u.address,
        u.class_grade,
        c.class_name,
        u.about_me,
        u.profile_image,
        u.created_at
    FROM users u
    LEFT JOIN classes c ON u.class_grade = c.id
    WHERE u.id = ?
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
    "message" => "User fetched successfully",
    "data" => $user
]);
