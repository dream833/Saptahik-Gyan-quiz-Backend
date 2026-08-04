<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$notification_id = intval($data['notification_id'] ?? 0);
$title = trim($data['title'] ?? '');
$message = trim($data['message'] ?? '');
$type = trim($data['type'] ?? 'custom');
// is_active: 1 = active, 0 = inactive (optional field for toggling)
$is_active = isset($data['is_active']) ? intval($data['is_active']) : -1;

// Toggle-only requests (status switch) have no title — that's valid
$isToggleOnly = (empty($title) && ($is_active === 0 || $is_active === 1));

if ($notification_id <= 0 || (empty($title) && !$isToggleOnly)) {

    echo json_encode([
        "status" => false,
        "message" => "Invalid Data"
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

    if ($isToggleOnly) {

        // Toggle active state only (status switch in the UI)
        $update = $pdo->prepare("
            UPDATE notifications
            SET is_active = ?
            WHERE id = ?
        ");

        $update->execute([$is_active, $notification_id]);

    } elseif ($is_active === 0 || $is_active === 1) {

        // Full edit including active state
        $update = $pdo->prepare("
            UPDATE notifications
            SET
                title = ?,
                message = ?,
                type = ?,
                is_active = ?
            WHERE id = ?
        ");

        $update->execute([
            $title,
            $message,
            $type,
            $is_active,
            $notification_id
        ]);

    } else {

        // Update only text fields (keep current active state)
        $update = $pdo->prepare("
            UPDATE notifications
            SET
                title = ?,
                message = ?,
                type = ?
            WHERE id = ?
        ");

        $update->execute([
            $title,
            $message,
            $type,
            $notification_id
        ]);

    }

    echo json_encode([
        "status" => true,
        "message" => "Notification Updated Successfully"
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}
