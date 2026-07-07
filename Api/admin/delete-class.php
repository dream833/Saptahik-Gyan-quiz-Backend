<?php

header("Content-Type: application/json");

require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$class_id = intval($data['class_id'] ?? 0);

if($class_id<=0){

    echo json_encode([
        "status"=>false,
        "message"=>"Invalid Class"
    ]);
    exit;

}

try{

    // subject check
    $sub = $pdo->prepare("SELECT id FROM subjects WHERE class_id=? LIMIT 1");
    $sub->execute([$class_id]);

    if($sub->rowCount()>0){

        echo json_encode([
            "status"=>false,
            "message"=>"Class is linked with Subjects"
        ]);
        exit;

    }

    // mock check
    $mock = $pdo->prepare("SELECT id FROM mock_tests WHERE class_id=? LIMIT 1");
    $mock->execute([$class_id]);

    if($mock->rowCount()>0){

        echo json_encode([
            "status"=>false,
            "message"=>"Class is linked with Mock Tests"
        ]);
        exit;

    }

    $stmt=$pdo->prepare("DELETE FROM classes WHERE id=?");
    $stmt->execute([$class_id]);

    echo json_encode([
        "status"=>true,
        "message"=>"Class Deleted Successfully"
    ]);

}catch(PDOException $e){

    echo json_encode([
        "status"=>false,
        "message"=>$e->getMessage()
    ]);

}