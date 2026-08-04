<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";
require_once "../../utils/fcm_helper.php";

try {

    // Is the service account key file present?
    // (Check file_exists first — fcm_get_credentials() logs an error when missing)
    $creds = null;
    if (defined('FCM_CREDENTIALS_FILE') && file_exists(FCM_CREDENTIALS_FILE)) {
        $creds = fcm_get_credentials();
    }

    // Count registered devices
    $deviceCount = 0;
    try {
        $deviceCount = (int)$pdo->query("SELECT COUNT(*) FROM device_tokens")->fetchColumn();
    } catch (PDOException $e) {
        // table may not exist yet — report 0
    }

    echo json_encode([
        "status" => true,
        "data" => [
            "configured"    => ($creds !== null),
            "project_id"    => $creds['project_id'] ?? null,
            "client_email"  => $creds['client_email'] ?? null,
            "total_devices" => $deviceCount
        ]
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}
