<?php
$host = 'localhost';
$dbname = 'quiz';
$username = 'root';
$password = '';
$charset = 'utf8mb4';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die(json_encode([
        "status" => "error",
        "message" => "DB error: " . $e->getMessage()
    ]));
}
?>