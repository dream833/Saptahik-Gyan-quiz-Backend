<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

try {

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
        ORDER BY u.created_at DESC
    ");

    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => true,
        "message" => "Users fetched successfully",
        "total_users" => count($users),
        "data" => $users
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => "Failed to fetch users: " . $e->getMessage()
    ]);

}
