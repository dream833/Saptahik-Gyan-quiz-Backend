<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$pyq_id = intval($data['pyq_id'] ?? 0);

if ($pyq_id <= 0) {

    echo json_encode([
        "status" => false,
        "message" => "Invalid Question ID"
    ]);
    exit;

}

try {

    // Check PYQ Exists and get file name
    $check = $pdo->prepare("
        SELECT id, pdf_file
        FROM previous_year_questions
        WHERE id = ?
    ");

    $check->execute([$pyq_id]);

    if ($check->rowCount() == 0) {

        echo json_encode([
            "status" => false,
            "message" => "Question Not Found"
        ]);
        exit;

    }

    $row = $check->fetch(PDO::FETCH_ASSOC);
    $pdf_file = $row['pdf_file'];

    // Delete from database
    $delete = $pdo->prepare("
        DELETE
        FROM previous_year_questions
        WHERE id = ?
    ");

    $delete->execute([$pyq_id]);

    // Delete the PDF file from disk
    if (!empty($pdf_file)) {
        $filepath = __DIR__ . "/../../uploads/pyq/" . $pdf_file;
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }

    echo json_encode([
        "status" => true,
        "message" => "Question Paper Deleted Successfully"
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}
