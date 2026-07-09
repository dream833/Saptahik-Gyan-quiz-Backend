<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

try {

    $today = date("Y-m-d");

    $totalClasses = $pdo->query("SELECT COUNT(*) FROM classes")->fetchColumn();

    $totalSubjects = $pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn();

    $totalMockTests = $pdo->query("SELECT COUNT(*) FROM mock_tests")->fetchColumn();

    $totalQuestions = $pdo->query("SELECT COUNT(*) FROM mock_test_questions")->fetchColumn();

    $todayStmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM mock_tests
        WHERE test_date = ?
    ");

    $todayStmt->execute([$today]);

    $todayTests = $todayStmt->fetchColumn();

    echo json_encode([
        "status" => true,
        "data" => [
            "total_classes" => (int)$totalClasses,
            "total_subjects" => (int)$totalSubjects,
            "total_mock_tests" => (int)$totalMockTests,
            "total_questions" => (int)$totalQuestions,
            "today_mock_tests" => (int)$todayTests
        ]
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}