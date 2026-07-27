<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$mock_test_id = intval($data['mock_test_id'] ?? 0);
$class_id = intval($data['class_id'] ?? 0);
$subject_id = intval($data['subject_id'] ?? 0);
$type = trim($data['type'] ?? 'daily'); // 'daily' or 'all'

try {

    if ($type === 'daily' && $mock_test_id > 0) {
        // Fetch results for a specific daily mock test
        $stmt = $pdo->prepare("
            SELECT
                umr.id AS result_id,
                umr.user_id,
                u.full_name,
                u.email,
                u.mobile,
                u.class_grade,
                umr.test_id,
                mt.test_name,
                mt.test_date,
                mt.duration_minutes,
                umr.total_questions,
                umr.attempted_questions,
                umr.correct_answers,
                umr.wrong_answers,
                umr.score,
                umr.percentage,
                umr.time_taken,
                umr.submitted_at
            FROM user_mock_results umr
            INNER JOIN users u ON u.id = umr.user_id
            INNER JOIN mock_tests mt ON mt.id = umr.test_id
            WHERE umr.test_id = ?
            ORDER BY umr.percentage DESC, umr.score DESC
        ");
        $stmt->execute([$mock_test_id]);

    } elseif ($type === 'daily' && $class_id > 0 && $subject_id > 0) {
        // Fetch all daily mock test results for a class+subject
        $stmt = $pdo->prepare("
            SELECT
                umr.id AS result_id,
                umr.user_id,
                u.full_name,
                u.email,
                u.mobile,
                umr.test_id,
                mt.test_name,
                mt.test_date,
                mt.duration_minutes,
                umr.total_questions,
                umr.attempted_questions,
                umr.correct_answers,
                umr.wrong_answers,
                umr.score,
                umr.percentage,
                umr.time_taken,
                umr.submitted_at
            FROM user_mock_results umr
            INNER JOIN users u ON u.id = umr.user_id
            INNER JOIN mock_tests mt ON mt.id = umr.test_id
            WHERE mt.class_id = ? AND mt.subject_id = ?
            ORDER BY mt.test_date DESC, umr.percentage DESC
        ");
        $stmt->execute([$class_id, $subject_id]);

    } elseif ($type === 'all') {
        // Fetch results for all mock tests (from all_mock_tests - sets based)
        // Note: Currently results are only stored in user_mock_results for mock_tests.
        // For sets-based tests, this may need to be extended.
        $stmt = $pdo->prepare("
            SELECT
                umr.id AS result_id,
                umr.user_id,
                u.full_name,
                u.email,
                u.mobile,
                umr.test_id,
                mt.test_name,
                mt.test_date,
                mt.duration_minutes,
                umr.total_questions,
                umr.attempted_questions,
                umr.correct_answers,
                umr.wrong_answers,
                umr.score,
                umr.percentage,
                umr.time_taken,
                umr.submitted_at
            FROM user_mock_results umr
            INNER JOIN users u ON u.id = umr.user_id
            INNER JOIN mock_tests mt ON mt.id = umr.test_id
            ORDER BY mt.test_date DESC, umr.percentage DESC
        ");
        $stmt->execute();

    } else {
        echo json_encode([
            "status" => false,
            "message" => "Please provide required filters (mock_test_id or class_id + subject_id)"
        ]);
        exit;
    }

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => true,
        "total_results" => count($results),
        "data" => $results
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);
}
