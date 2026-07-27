<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$category_id = intval($data['category_id'] ?? 0);

if ($category_id <= 0) {

    echo json_encode([
        "status" => false,
        "message" => "Invalid Category"
    ]);
    exit;

}

try {

    // Check Category Exists
    $check = $pdo->prepare("
        SELECT id
        FROM exam_categories
        WHERE id = ?
    ");

    $check->execute([$category_id]);

    if ($check->rowCount() == 0) {

        echo json_encode([
            "status" => false,
            "message" => "Category Not Found"
        ]);
        exit;

    }

    // Delete (CASCADE will delete related PYQs)
    $delete = $pdo->prepare("
        DELETE
        FROM exam_categories
        WHERE id = ?
    ");

    $delete->execute([$category_id]);

    echo json_encode([
        "status" => true,
        "message" => "Category Deleted Successfully"
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}
