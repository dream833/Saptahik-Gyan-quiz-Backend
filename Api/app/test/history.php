<?php

header("Content-Type: application/json");
require_once "../../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$user_id = intval($data['user_id'] ?? ($_GET['user_id'] ?? 0));
$page = intval($data['page'] ?? ($_GET['page'] ?? 1));
$limit = intval($data['limit'] ?? ($_GET['limit'] ?? 20));

if ($user_id <= 0) {
    http_response_code(400);
    echo json_encode([
        "message" => "user_id is required"
    ]);
    exit;
}

if ($page < 1) $page = 1;
if ($limit < 1) $limit = 20;
$offset = ($page - 1) * $limit;

try {

    $stmt = $pdo->prepare("
        SELECT
            umr.id,
            mt.test_name AS name,
            umr.submitted_at AS date,
            umr.total_questions,
            umr.score,
            (umr.total_questions * 2) AS total_marks,
            umr.percentage
        FROM user_mock_results umr
        INNER JOIN mock_tests mt ON mt.id = umr.test_id
        WHERE umr.user_id = ?
        ORDER BY umr.submitted_at DESC
        LIMIT ? OFFSET ?
    ");

    $stmt->execute([$user_id, $limit, $offset]);

    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "data" => $records
    ]);

} catch (PDOException $e) {

    http_response_code(500);
    echo json_encode([
        "message" => "Server error"
    ]);

}
