<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => false,
        "message" => "Invalid request method"
    ]);
    exit;
}

if (!isset($_POST['categoryName']) || empty(trim($_POST['categoryName']))) {
    echo json_encode([
        "status" => false,
        "message" => "Category name is required"
    ]);
    exit;
}

$name = trim($_POST['categoryName']);
$imagePath = null;

try {
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE name = ?");
    $checkStmt->execute([$name]);
    $count = $checkStmt->fetchColumn();

    if ($count > 0) {
        echo json_encode([
            "status" => false,
            "message" => "Category already exists"
        ]);
        exit;
    }

    if (isset($_FILES['categoryImage']) && $_FILES['categoryImage']['error'] === UPLOAD_ERR_OK) {
  
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // 🟡 MIME TYPE VALIDATION (optional but recommended)
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['categoryImage']['type'], $allowedTypes)) {
            echo json_encode([
                "status" => false,
                "message" => "Only JPG, PNG, and GIF files are allowed"
            ]);
            exit;
        }

        $fileTmp = $_FILES['categoryImage']['tmp_name'];
        $fileName = uniqid() . '-' . basename($_FILES['categoryImage']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($fileTmp, $targetPath)) {
            $imagePath = 'uploads/' . $fileName;
        } else {
            echo json_encode([
                "status" => false,
                "message" => "Failed to upload image"
            ]);
            exit;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO categories (name, image) VALUES (?, ?)");
    $stmt->execute([$name, $imagePath]);

    echo json_encode([
        "status" => true,
        "message" => "Category created successfully",
        "data" => [
            "id" => $pdo->lastInsertId(),
            "name" => $name,
            "image" => $imagePath
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "status" => false,
        "message" => "DB Error: " . $e->getMessage()
    ]);
}
?>