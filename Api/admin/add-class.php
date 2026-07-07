<?php

header("Content-Type: application/json");

require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$class_name = trim($data['class_name'] ?? '');

if (empty($class_name)) {
    echo json_encode([
        "status" => false,
        "message" => "Class name is required"
    ]);
    exit;
}

try {

    // Duplicate Check
    $check = $pdo->prepare("SELECT id FROM classes WHERE class_name = ?");
    $check->execute([$class_name]);

    if ($check->rowCount() > 0) {

        echo json_encode([
            "status" => false,
            "message" => "Class Already Exists"
        ]);
        exit;
    }

    // Insert
    $stmt = $pdo->prepare("INSERT INTO classes(class_name) VALUES(?)");
    $stmt->execute([$class_name]);

    echo json_encode([
        "status" => true,
        "message" => "Class Added Successfully",
        "class_id" => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}