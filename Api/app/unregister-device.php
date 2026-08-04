<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$token = trim($data['token'] ?? '');

if (empty($token)) {

    echo json_encode([
        "status" => false,
        "message" => "token is required"
    ]);
    exit;

}

try {

    $delete = $pdo->prepare("
        DELETE
        FROM device_tokens
        WHERE token = ?
    ");

    $delete->execute([$token]);

    echo json_encode([
        "status" => true,
        "message" => "Device unregistered successfully"
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}
