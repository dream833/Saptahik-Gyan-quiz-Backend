<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";
require_once "../../utils/notification_helper.php";

$data = json_decode(file_get_contents("php://input"), true);

$class_id = intval($data['class_id'] ?? 0);
$subject_id = intval($data['subject_id'] ?? 0);
$test_name = trim($data['test_name'] ?? '');
$description = trim($data['description'] ?? '');
$test_date = trim($data['test_date'] ?? '');
$duration = intval($data['duration_minutes'] ?? 0);

if (
    $class_id <= 0 ||
    $subject_id <= 0 ||
    empty($test_name) ||
    empty($test_date) ||
    $duration <= 0
) {
    echo json_encode([
        "status" => false,
        "message" => "All Fields are Required"
    ]);
    exit;
}

try {

    // Class Exists
    $class = $pdo->prepare("SELECT id FROM classes WHERE id=?");
    $class->execute([$class_id]);

    if ($class->rowCount() == 0) {
        echo json_encode([
            "status" => false,
            "message" => "Class Not Found"
        ]);
        exit;
    }

    // Subject Exists & Belongs To Class
    $subject = $pdo->prepare("
        SELECT id
        FROM subjects
        WHERE id=?
        AND class_id=?
    ");

    $subject->execute([$subject_id, $class_id]);

    if ($subject->rowCount() == 0) {
        echo json_encode([
            "status" => false,
            "message" => "Invalid Subject"
        ]);
        exit;
    }

    // Insert
    $stmt = $pdo->prepare("
        INSERT INTO mock_tests
        (
            class_id,
            subject_id,
            test_name,
            description,
            test_date,
            start_time,
            end_time,
            duration_minutes,
            total_questions,
            total_marks,
            is_daily,
            status
        )
        VALUES
        (
            ?,?,?,?,?,'00:00:00','23:59:00',?,
            0,
            0,
            1,
            'scheduled'
        )
    ");

    $stmt->execute([
        $class_id,
        $subject_id,
        $test_name,
        $description,
        $test_date,
        $duration
    ]);

    $newMockTestId = $pdo->lastInsertId();

    // Auto-notification: new test added
    create_notification(
        $pdo,
        "New Test Added",
        "A new daily mock test \"" . $test_name . "\" has been scheduled on " . date("d M Y", strtotime($test_date)) . ".",
        "test"
    );

    echo json_encode([
        "status" => true,
        "message" => "Mock Test Added Successfully",
        "mock_test_id" => $newMockTestId
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}