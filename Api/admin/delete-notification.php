<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$notification_id = intval($data['notification_id'] ?? 0);

if ($notification_id <= 0) {

    echo json_encode([
        "status" => false,
        "message" => "Invalid Notification"
    ]);
    exit;

}

try {

    // Notification Exists
    $check = $pdo->prepare("
        SELECT id
        FROM notifications
        WHERE id = ?
    ");

    $check->execute([$notification_id]);

    if ($check->rowCount() == 0) {

        echo json_encode([
            "status" => false,
            "message" => "Notification Not Found"
        ]);
        exit;

    }

    $delete = $pdo->prepare("
        DELETE
        FROM notifications
        WHERE id = ?
    ");

    $delete->execute([$notification_id]);

    echo json_encode([
        "status" => true,
        "message" => "Notification Deleted Successfully"
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}
