<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$class_id   = intval($data['class_id'] ?? 0);
$class_name = trim($data['class_name'] ?? '');

if ($class_id <= 0 || empty($class_name)) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid Data"
    ]);
    exit;
}

try {

    // Check class exists
    $check = $pdo->prepare("SELECT id FROM classes WHERE id=?");
    $check->execute([$class_id]);

    if ($check->rowCount() == 0) {
        echo json_encode([
            "status"=>false,
            "message"=>"Class Not Found"
        ]);
        exit;
    }

    // Duplicate check
    $dup = $pdo->prepare("SELECT id FROM classes WHERE class_name=? AND id<>?");
    $dup->execute([$class_name,$class_id]);

    if($dup->rowCount()>0){
        echo json_encode([
            "status"=>false,
            "message"=>"Class Already Exists"
        ]);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE classes SET class_name=? WHERE id=?");
    $stmt->execute([$class_name,$class_id]);

    echo json_encode([
        "status"=>true,
        "message"=>"Class Updated Successfully"
    ]);

} catch(PDOException $e){

    echo json_encode([
        "status"=>false,
        "message"=>$e->getMessage()
    ]);

}