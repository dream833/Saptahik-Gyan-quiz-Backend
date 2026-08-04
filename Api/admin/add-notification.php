<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";
require_once "../../utils/notification_helper.php";

$data = json_decode(file_get_contents("php://input"), true);

$title = trim($data['title'] ?? '');
$message = trim($data['message'] ?? '');
$type = trim($data['type'] ?? 'custom');

if (empty($title)) {

    echo json_encode([
        "status" => false,
        "message" => "Title is Required"
    ]);
    exit;

}

// create_notification() handles DB errors internally and returns false on failure.
// On success it returns ['id' => int, 'push' => array] — push is best-effort.
$result = create_notification($pdo, $title, $message, $type);

if ($result === false) {

    echo json_encode([
        "status" => false,
        "message" => "Failed to create notification"
    ]);
    exit;

}

$response = [
    "status"          => true,
    "message"         => "Notification Added Successfully",
    "notification_id" => $result['id']
];

// Include push status (useful so the admin knows if FCM is configured)
$push = $result['push'] ?? null;
if ($push) {
    $response['push_sent']  = $push['sent'];
    $response['push_failed'] = $push['failed'];
    if ($push['message'] !== 'success') {
        $response['push_message'] = $push['message'];
    }
}

echo json_encode($response);
