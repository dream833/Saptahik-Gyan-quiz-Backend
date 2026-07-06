<?php

header("Content-Type: application/json");

require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$test_id = intval($data['test_id'] ?? 0);

if ($test_id <= 0) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid test id"
    ]);
    exit;
}

// Verify test exists
$stmt = $pdo->prepare("SELECT id FROM mock_tests WHERE id = ? LIMIT 1");
$stmt->execute([$test_id]);
if (!$stmt->fetch()) {
    echo json_encode([
        "status" => false,
        "message" => "Mock test not found"
    ]);
    exit;
}

try {
    $pdo->beginTransaction();

    // Delete user answers for this test
    $pdo->prepare("DELETE FROM user_answers WHERE test_id = ?")->execute([$test_id]);

    // Delete user mock results for this test
    $pdo->prepare("DELETE FROM user_mock_results WHERE test_id = ?")->execute([$test_id]);

    // Delete question links
    $pdo->prepare("DELETE FROM mock_test_questions WHERE test_id = ?")->execute([$test_id]);

    // Delete the mock test itself
    $pdo->prepare("DELETE FROM mock_tests WHERE id = ?")->execute([$test_id]);

    $pdo->commit();

    echo json_encode([
        "status" => true,
        "message" => "Mock test deleted successfully"
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();

    echo json_encode([
        "status" => false,
        "message" => "Failed to delete mock test: " . $e->getMessage()
    ]);
}
