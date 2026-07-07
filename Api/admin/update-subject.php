<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$id = intval($data['subject_id'] ?? 0);
$class_id = intval($data['class_id'] ?? 0);
$subject_name = trim($data['subject_name'] ?? '');

if ($id <= 0 || $class_id <= 0 || empty($subject_name)) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid Data"
    ]);
    exit;
}

try {

    // Subject Exists
    $subject = $pdo->prepare("SELECT id FROM subjects WHERE id=?");
    $subject->execute([$id]);

    if ($subject->rowCount() == 0) {
        echo json_encode([
            "status" => false,
            "message" => "Subject Not Found"
        ]);
        exit;
    }

    // Class Exists
    $class = $pdo->prepare("SELECT id FROM classes WHERE id=?");
    $class->execute([$class_id]);

    if ($class->rowCount() == 0) {
        echo json_encode([
            "status" => false,
            "message" => "Class Not Found"
        ]);
        exit;
    }

    // Duplicate Subject
    $dup = $pdo->prepare("
        SELECT id
        FROM subjects
        WHERE class_id = ?
        AND LOWER(TRIM(subject_name)) = LOWER(TRIM(?))
        AND id <> ?
    ");

    $dup->execute([$class_id, $subject_name, $id]);

    if ($dup->rowCount() > 0) {
        echo json_encode([
            "status" => false,
            "message" => "Subject Already Exists"
        ]);
        exit;
    }

    // Update
    $stmt = $pdo->prepare("
        UPDATE subjects
        SET class_id=?, subject_name=?
        WHERE id=?
    ");

    $stmt->execute([$class_id, $subject_name, $id]);

    if ($stmt->rowCount() > 0) {

        echo json_encode([
            "status" => true,
            "message" => "Subject Updated Successfully"
        ]);

    } else {

        echo json_encode([
            "status" => false,
            "message" => "No Changes Found"
        ]);

    }

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}