<?php

header("Content-Type: application/json");
require_once "../../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$class_id = intval($data['class_id'] ?? ($_GET['class_id'] ?? 0));
$subject_id = intval($data['subject_id'] ?? ($_GET['subject_id'] ?? 0));

if ($class_id <= 0 || $subject_id <= 0) {
    echo json_encode([
        "data" => []
    ]);
    exit;
}

try {

    $stmt = $pdo->prepare("
        SELECT
            ss.id,
            ss.title AS name,
            ss.subject_id,
            s.class_id
        FROM solution_suggestions ss
        INNER JOIN subjects s ON s.id = ss.subject_id
        WHERE s.class_id = ?
        AND ss.subject_id = ?
        ORDER BY ss.id ASC
    ");

    $stmt->execute([$class_id, $subject_id]);

    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "data" => $items
    ]);

} catch (PDOException $e) {

    http_response_code(500);
    echo json_encode([
        "message" => "Server error"
    ]);

}
