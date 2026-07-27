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
        SELECT DISTINCT
            ch.id,
            ch.chapter_name AS name
        FROM chapters ch
        INNER JOIN subjects s ON s.id = ch.subject_id AND s.class_id = ?
        INNER JOIN sets st ON st.chapter_id = ch.id
        WHERE ch.subject_id = ?
        ORDER BY ch.chapter_name ASC
    ");

    $stmt->execute([$class_id, $subject_id]);

    $chapters = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "data" => $chapters
    ]);

} catch (PDOException $e) {

    http_response_code(500);
    echo json_encode([
        "message" => "Server error"
    ]);

}
