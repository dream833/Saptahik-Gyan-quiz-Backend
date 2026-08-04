<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

// Admin browser device: no user_id required (web console device)
$token    = trim($data['token'] ?? '');
$platform = trim($data['platform'] ?? 'web');

if (empty($token)) {

    echo json_encode([
        "status" => false,
        "message" => "token is required"
    ]);
    exit;

}

if (!in_array($platform, ['android', 'ios', 'web'], true)) {
    $platform = 'web';
}

try {

    // Token may already exist → refresh updated_at, otherwise insert
    $check = $pdo->prepare("SELECT id FROM device_tokens WHERE token = ?");
    $check->execute([$token]);

    if ($check->rowCount() > 0) {

        $update = $pdo->prepare("
            UPDATE device_tokens
            SET platform = ?
            WHERE token = ?
        ");

        $update->execute([$platform, $token]);

        echo json_encode([
            "status" => true,
            "message" => "Web device updated successfully"
        ]);
        exit;

    }

    // user_id = 0 marks an admin/browser device
    $insert = $pdo->prepare("
        INSERT INTO device_tokens
        (
            user_id,
            token,
            platform
        )
        VALUES
        (
            0,?,?
        )
    ");

    $insert->execute([$token, $platform]);

    echo json_encode([
        "status" => true,
        "message" => "Web device registered successfully",
        "device_id" => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}
