<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$data=json_decode(file_get_contents("php://input"),true);

$mock_test_id=intval($data['mock_test_id'] ?? 0);

if($mock_test_id<=0){

    echo json_encode([
        "status"=>false,
        "message"=>"Invalid Mock Test"
    ]);
    exit;

}

try{

    // Mock Exists
    $check=$pdo->prepare("
        SELECT id
        FROM mock_tests
        WHERE id=?
    ");

    $check->execute([$mock_test_id]);

    if($check->rowCount()==0){

        echo json_encode([
            "status"=>false,
            "message"=>"Mock Test Not Found"
        ]);
        exit;

    }

    // Question Exists
    $question=$pdo->prepare("
        SELECT id
        FROM mock_test_questions
        WHERE mock_test_id=?
        LIMIT 1
    ");

    $question->execute([$mock_test_id]);

    if($question->rowCount()>0){

        echo json_encode([
            "status"=>false,
            "message"=>"Delete Questions First"
        ]);
        exit;

    }

    $delete=$pdo->prepare("
        DELETE
        FROM mock_tests
        WHERE id=?
    ");

    $delete->execute([$mock_test_id]);

    echo json_encode([
        "status"=>true,
        "message"=>"Mock Test Deleted Successfully"
    ]);

}catch(PDOException $e){

    echo json_encode([
        "status"=>false,
        "message"=>$e->getMessage()
    ]);

}