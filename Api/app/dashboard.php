<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$user_id = intval($data['user_id'] ?? 0);

if ($user_id <= 0) {
    http_response_code(400);
    echo json_encode([
        "message" => "user_id is required"
    ]);
    exit;
}

try {

    // Get user info
    $userStmt = $pdo->prepare("
        SELECT full_name AS name, email, mobile AS phone
        FROM users
        WHERE id = ?
        LIMIT 1
    ");
    $userStmt->execute([$user_id]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode([
            "message" => "User not found"
        ]);
        exit;
    }

    // Get stats
    $statsStmt = $pdo->prepare("
        SELECT
            COUNT(*) AS total_tests_taken,
            COALESCE(SUM(correct_answers), 0) AS total_correct,
            COALESCE(SUM(wrong_answers), 0) AS total_wrong,
            COALESCE(ROUND(AVG(percentage), 0), 0) AS average_score
        FROM user_mock_results
        WHERE user_id = ?
    ");
    $statsStmt->execute([$user_id]);
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

    // Check if daily quiz available today
    $today = date("Y-m-d");
    $dailyStmt = $pdo->prepare("
        SELECT COUNT(*) AS cnt
        FROM mock_tests
        WHERE is_daily = 1 AND test_date = ? AND status = 'scheduled'
        LIMIT 1
    ");
    $dailyStmt->execute([$today]);
    $dailyAvailable = $dailyStmt->fetchColumn() > 0;

    // Get previous test records (last 5)
    $recordsStmt = $pdo->prepare("
        SELECT
            mt.test_name AS name,
            umr.submitted_at AS date,
            umr.total_questions,
            umr.score,
            umr.percentage
        FROM user_mock_results umr
        INNER JOIN mock_tests mt ON mt.id = umr.test_id
        WHERE umr.user_id = ?
        ORDER BY umr.submitted_at DESC
        LIMIT 5
    ");
    $recordsStmt->execute([$user_id]);
    $records = $recordsStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "data" => [
            "user" => $user,
            "stats" => [
                "total_tests_taken" => (int)$stats['total_tests_taken'],
                "average_score" => (int)$stats['average_score'],
                "total_correct" => (int)$stats['total_correct'],
                "total_wrong" => (int)$stats['total_wrong']
            ],
            "daily_quiz_available" => $dailyAvailable,
            "banners" => [
                [
                    "title" => "Daily Quiz Challenge",
                    "subtitle" => "Test your knowledge with today's quiz",
                    "icon" => "quiz",
                    "gradient" => "primary"
                ]
            ],
            "previous_test_records" => $records
        ]
    ]);

} catch (PDOException $e) {

    http_response_code(500);
    echo json_encode([
        "message" => "Server error"
    ]);

}
