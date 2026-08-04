<?php

/**
 * Notification Helper
 *
 * Reusable function to insert a notification into the `notifications` table.
 * Used by admin add-APIs (auto notifications) and by add-notification.php
 * (manual custom notifications).
 *
 * Every successful insert ALSO triggers an FCM push broadcast to all
 * registered app devices (if google_service.json is configured).
 *
 * Usage:
 *   require_once __DIR__ . "/notification_helper.php";
 *   create_notification($pdo, "New Test Added", "A new test is live!", "test");
 */

require_once __DIR__ . "/fcm_helper.php";

/**
 * Insert a new notification and send an FCM push broadcast.
 *
 * @param PDO    $pdo     Database connection
 * @param string $title   Notification title (required)
 * @param string $message Notification body text
 * @param string $type    Type: 'test' | 'solution' | 'custom'
 * @return int|false      New notification id, or false on failure
 * @return array          ['id' => int, 'push' => array] on success
 */
function create_notification($pdo, $title, $message = '', $type = 'custom')
{
    if (empty(trim((string)$title))) {
        return false;
    }

    // Whitelist type values
    $allowedTypes = ['test', 'solution', 'custom'];
    if (!in_array($type, $allowedTypes, true)) {
        $type = 'custom';
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO notifications
            (
                title,
                message,
                type,
                is_active
            )
            VALUES
            (
                ?,?,?,1
            )
        ");

        $stmt->execute([
            trim((string)$title),
            trim((string)$message),
            $type
        ]);

        $notificationId = (int)$pdo->lastInsertId();

        // Send FCM push to all devices (best-effort, never breaks the flow)
        $pushResult = send_fcm_broadcast($pdo, trim((string)$title), trim((string)$message), $type, $notificationId);

        return [
            'id'   => $notificationId,
            'push' => $pushResult
        ];

    } catch (PDOException $e) {
        // Never break the main flow because of a notification failure
        error_log("Notification insert failed: " . $e->getMessage());
        return false;
    }
}
