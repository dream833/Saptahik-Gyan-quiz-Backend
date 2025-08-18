<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");
require 'db.php'; 

$data = json_decode(file_get_contents("php://input"));

if (!$data || !isset($data->name, $data->email, $data->phone, $data->password)) {
    echo json_encode(["status" => false, "message" => "Invalid input"]);
    exit;
}

$name = $data->name;
$email = $data->email;
$phone = $data->phone;
$password = $data->password;


$stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email OR phone = :phone");
$stmt->execute(['email' => $email, 'phone' => $phone]);

if ($stmt->rowCount() > 0) {
    echo json_encode(["status" => false, "message" => "Email or phone already exists"]);
} else {
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    $insert = $pdo->prepare("INSERT INTO users (name, email, phone, password) VALUES (:name, :email, :phone, :password)");
    $success = $insert->execute([
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'password' => $hashed_password
    ]);

    if ($success) {
        echo json_encode(["status" => true, "message" => "Signup successful"]);
    } else {
        echo json_encode(["status" => false, "message" => "Signup failed"]);
    }
}
?>