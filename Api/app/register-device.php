<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$user_id  = intval($data['user_id'] ?? 0);
$token    = trim($data['token'] ?? '');
$platform = trim($data['platform'] ?? 'android');

if ($user_id <= 0 || empty($token)) {

    echo json_encode([
        "status" => false,
        "message" => "user_id and token are required"
    ]);
    exit;

}

if (!in_array($platform, ['android', 'ios'], true)) {
    $platform = 'android';
}

try {

    // User Exists
    $user = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $user->execute([$user_id]);

    if ($user->rowCount() == 0) {

        echo json_encode([
            "status" => false,
            "message" => "User Not Found"
        ]);
        exit;

    }

    // Token may already exist → update user/platform, otherwise insert
    $check = $pdo->prepare("SELECT id FROM device_tokens WHERE token = ?");
    $check->execute([$token]);

    if ($check->rowCount() > 0) {

        $update = $pdo->prepare("
            UPDATE device_tokens
            SET user_id = ?, platform = ?
            WHERE token = ?
        ");

        $update->execute([$user_id, $platform, $token]);

        echo json_encode([
            "status" => true,
            "message" => "Device token updated successfully"
        ]);
        exit;

    }

    $insert = $pdo->prepare("
        INSERT INTO device_tokens
        (
            user_id,
            token,
            platform
        )
        VALUES
        (
            ?,?,?
        )
    ");

    $insert->execute([$user_id, $token, $platform]);

    echo json_encode([
        "status" => true,
        "message" => "Device registered successfully",
        "device_id" => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}
