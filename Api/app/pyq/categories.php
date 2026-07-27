<?php

header("Content-Type: application/json");
require_once "../../../utils/db.php";

try {

    $stmt = $pdo->prepare("
        SELECT
            ec.title AS name,
            ec.description AS subtitle
        FROM exam_categories ec
        ORDER BY ec.title ASC
    ");

    $stmt->execute();

    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // For each category, get available years
    foreach ($categories as &$cat) {
        $yearStmt = $pdo->prepare("
            SELECT DISTINCT p.year
            FROM previous_year_questions p
            INNER JOIN exam_categories ec ON ec.id = p.exam_category_id
            WHERE ec.title = ?
            ORDER BY p.year DESC
        ");
        $yearStmt->execute([$cat['name']]);
        $years = $yearStmt->fetchAll(PDO::FETCH_COLUMN);
        $cat['years'] = array_map('intval', $years);
    }
    unset($cat);

    echo json_encode([
        "data" => $categories
    ]);

} catch (PDOException $e) {

    http_response_code(500);
    echo json_encode([
        "message" => "Server error"
    ]);

}
