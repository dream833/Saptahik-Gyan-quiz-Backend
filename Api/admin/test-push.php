<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";
require_once "../../utils/fcm_helper.php";

$data = json_decode(file_get_contents("php://input"), true);

$title   = trim($data['title'] ?? 'Test Push');
$message = trim($data['message'] ?? 'This is a test push from the admin panel.');
$type    = trim($data['type'] ?? 'custom');

if (empty($title)) {
    $title = 'Test Push';
}

// Send directly to FCM — does NOT create a notification in the database
$result = send_fcm_broadcast($pdo, $title, $message, $type, 0);

echo json_encode([
    "status" => $result['status'],
    "message" => $result['message'],
    "data" => [
        "sent"   => $result['sent'],
        "failed" => $result['failed'],
        "detail" => $result['message']
    ]
]);
