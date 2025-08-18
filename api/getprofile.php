<?php
// Show errors for debugging (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Include DB connection file (with $pdo)
include 'db.php';

// Read JSON body
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id'])) {
    echo json_encode([
        "status" => false,
        "message" => "User ID is missing"
    ]);
    exit;
}

$user_id = intval($data['id']); 

try {
    
    $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->execute([$user_id]);

    // Check if user found
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            "status" => true,
            "message" => "User found",
            "data" => [
                "name" => $user['name']
            ]
        ]);
    } else {
        echo json_encode([
            "status" => false,
            "message" => "User not found"
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        "status" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
?>