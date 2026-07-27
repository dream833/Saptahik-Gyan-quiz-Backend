<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$title = trim($data['title'] ?? '');
$description = trim($data['description'] ?? '');

if (empty($title)) {

    echo json_encode([
        "status" => false,
        "message" => "Title is required"
    ]);
    exit;

}

try {

    // Duplicate Check
    $duplicate = $pdo->prepare("
        SELECT id
        FROM exam_categories
        WHERE title = ?
    ");

    $duplicate->execute([$title]);

    if ($duplicate->rowCount() > 0) {

        echo json_encode([
            "status" => false,
            "message" => "Category Already Exists"
        ]);
        exit;

    }

    // Insert
    $insert = $pdo->prepare("
        INSERT INTO exam_categories
        (
            title,
            description
        )
        VALUES
        (
            ?,?
        )
    ");

    $insert->execute([
        $title,
        $description
    ]);

    echo json_encode([
        "status" => true,
        "message" => "Category Added Successfully",
        "category_id" => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}
