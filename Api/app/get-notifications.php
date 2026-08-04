<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

// App-side endpoint: returns active notifications for the app, newest first.
// No user_id required (notifications are broadcast to all users).
// Accepts POST (JSON, empty body ok) or GET.

try {

    $stmt = $pdo->prepare("
        SELECT
            id,
            title,
            message,
            type,
            created_at
        FROM notifications
        WHERE is_active = 1
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

    http_response_code(500);
    echo json_encode([
        "status" => false,
        "message" => "Failed to fetch notifications: " . $e->getMessage()
    ]);

}
