<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$mock_test_id = intval($data['mock_test_id'] ?? 0);
$class_id = intval($data['class_id'] ?? 0);
$subject_id = intval($data['subject_id'] ?? 0);

$test_name = trim($data['test_name'] ?? '');
$description = trim($data['description'] ?? '');
$test_date = trim($data['test_date'] ?? '');
$start_time = trim($data['start_time'] ?? '');
$end_time = trim($data['end_time'] ?? '');
$duration = intval($data['duration_minutes'] ?? 0);

if (
    $mock_test_id <= 0 ||
    $class_id <= 0 ||
    $subject_id <= 0 ||
    empty($test_name) ||
    empty($test_date) ||
    empty($start_time) ||
    empty($end_time) ||
    $duration <= 0
) {

    echo json_encode([
        "status"=>false,
        "message"=>"Invalid Data"
    ]);
    exit;
}

try{

    // Mock Exists
    $check=$pdo->prepare("SELECT id FROM mock_tests WHERE id=?");
    $check->execute([$mock_test_id]);

    if($check->rowCount()==0){

        echo json_encode([
            "status"=>false,
            "message"=>"Mock Test Not Found"
        ]);
        exit;

    }

    // Class + Subject Validation
    $subject=$pdo->prepare("
        SELECT id
        FROM subjects
        WHERE id=?
        AND class_id=?
    ");

    $subject->execute([
        $subject_id,
        $class_id
    ]);

    if($subject->rowCount()==0){

        echo json_encode([
            "status"=>false,
            "message"=>"Invalid Subject"
        ]);
        exit;

    }

    // Duplicate Check
    $dup=$pdo->prepare("
        SELECT id
        FROM mock_tests
        WHERE
        class_id=?
        AND subject_id=?
        AND test_name=?
        AND test_date=?
        AND id<>?
    ");

    $dup->execute([
        $class_id,
        $subject_id,
        $test_name,
        $test_date,
        $mock_test_id
    ]);

    if($dup->rowCount()>0){

        echo json_encode([
            "status"=>false,
            "message"=>"Mock Test Already Exists"
        ]);
        exit;

    }

    $stmt=$pdo->prepare("
        UPDATE mock_tests
        SET
            class_id=?,
            subject_id=?,
            test_name=?,
            description=?,
            test_date=?,
            start_time=?,
            end_time=?,
            duration_minutes=?
        WHERE id=?
    ");

    $stmt->execute([
        $class_id,
        $subject_id,
        $test_name,
        $description,
        $test_date,
        $start_time,
        $end_time,
        $duration,
        $mock_test_id
    ]);

    echo json_encode([
        "status"=>true,
        "message"=>"Mock Test Updated Successfully"
    ]);

}catch(PDOException $e){

    echo json_encode([
        "status"=>false,
        "message"=>$e->getMessage()
    ]);

}