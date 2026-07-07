<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$data=json_decode(file_get_contents("php://input"),true);

$class_id=intval($data['class_id'] ?? 0);
$subject_name=trim($data['subject_name'] ?? '');

if($class_id<=0 || $subject_name==''){
    echo json_encode([
        "status"=>false,
        "message"=>"Invalid Data"
    ]);
    exit;
}

try{

    // Class Exists
    $class=$pdo->prepare("SELECT id FROM classes WHERE id=?");
    $class->execute([$class_id]);

    if($class->rowCount()==0){
        echo json_encode([
            "status"=>false,
            "message"=>"Class Not Found"
        ]);
        exit;
    }

    // Duplicate
    $dup=$pdo->prepare("SELECT id FROM subjects WHERE class_id=? AND subject_name=?");
    $dup->execute([$class_id,$subject_name]);

    if($dup->rowCount()>0){
        echo json_encode([
            "status"=>false,
            "message"=>"Subject Already Exists"
        ]);
        exit;
    }

    $stmt=$pdo->prepare("INSERT INTO subjects(class_id,subject_name) VALUES(?,?)");
    $stmt->execute([$class_id,$subject_name]);

    echo json_encode([
        "status"=>true,
        "message"=>"Subject Added Successfully",
        "subject_id"=>$pdo->lastInsertId()
    ]);

}catch(PDOException $e){

    echo json_encode([
        "status"=>false,
        "message"=>$e->getMessage()
    ]);

}