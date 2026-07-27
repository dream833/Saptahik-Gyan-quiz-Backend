<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$category_id = intval($data['category_id'] ?? 0);
$title = trim($data['title'] ?? '');
$description = trim($data['description'] ?? '');

if (
    $category_id <= 0 ||
    empty($title)
) {

    echo json_encode([
        "status" => false,
        "message" => "Invalid Data"
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

    // Duplicate Check (excluding current)
    $duplicate = $pdo->prepare("
        SELECT id
        FROM exam_categories
        WHERE title = ? AND id <> ?
    ");

    $duplicate->execute([$title, $category_id]);

    if ($duplicate->rowCount() > 0) {

        echo json_encode([
            "status" => false,
            "message" => "Category Already Exists"
        ]);
        exit;

    }

    // Update
    $update = $pdo->prepare("
        UPDATE exam_categories
        SET
            title = ?,
            description = ?
        WHERE id = ?
    ");

    $update->execute([
        $title,
        $description,
        $category_id
    ]);

    echo json_encode([
        "status" => true,
        "message" => "Category Updated Successfully"
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}
