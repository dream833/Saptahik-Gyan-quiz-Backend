<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

try {

    $stmt = $pdo->prepare("
        SELECT
            id,
            title,
            message,
            type,
            is_active,
            created_at
        FROM notifications
        ORDER BY created_at DESC, id DESC
    ");

    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => true,
        "message" => "Notifications fetched successfully",
        "total_notifications" => count($notifications),
        "data" => $notifications
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => "Failed to fetch notifications: " . $e->getMessage()
    ]);

}
