<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$class_id = intval($data['class_id'] ?? 0);
$subject_id = intval($data['subject_id'] ?? 0);
$test_name = trim($data['test_name'] ?? '');
$description = trim($data['description'] ?? '');
$test_date = trim($data['test_date'] ?? '');
$start_time = trim($data['start_time'] ?? '');
$end_time = trim($data['end_time'] ?? '');
$duration = intval($data['duration_minutes'] ?? 0);

if (
    $class_id <= 0 ||
    $subject_id <= 0 ||
    empty($test_name) ||
    empty($test_date) ||
    empty($start_time) ||
    empty($end_time) ||
    $duration <= 0
) {
    echo json_encode([
        "status" => false,
        "message" => "All Fields are Required"
    ]);
    exit;
}

if (strtotime($start_time) >= strtotime($end_time)) {

    echo json_encode([
        "status" => false,
        "message" => "End Time must be greater than Start Time"
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

    // Duplicate Check
    $dup = $pdo->prepare("
        SELECT id
        FROM mock_tests
        WHERE class_id=?
        AND subject_id=?
        AND test_date=?
        AND start_time=?
    ");

    $dup->execute([
        $class_id,
        $subject_id,
        $test_date,
        $start_time
    ]);

    if ($dup->rowCount() > 0) {

        echo json_encode([
            "status" => false,
            "message" => "Mock Test Already Exists"
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
            ?,?,?,?,?,?,?,?,
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
        $start_time,
        $end_time,
        $duration
    ]);

    echo json_encode([
        "status" => true,
        "message" => "Mock Test Added Successfully",
        "mock_test_id" => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}