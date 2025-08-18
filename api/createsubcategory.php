<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'db.php';

try {
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : null;
    $name = isset($_POST['subcategory_name']) ? trim($_POST['subcategory_name']) : '';

    if (!$category_id || $name === '') {
        echo json_encode([
            'status' => 'error',
            'message' => 'Both category and name are required.'
        ]);
        exit;
    }

    $imagePath = null;

    // ✅ Upload inside api/uploads/subcategories/
    if (!empty($_FILES['subcategory_image']) && $_FILES['subcategory_image']['error'] === 0) {
        $uploadDir = __DIR__ . '/uploads/subcategories/'; // Absolute path for moving
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = time() . '_' . basename($_FILES['subcategory_image']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['subcategory_image']['tmp_name'], $targetPath)) {
            // ✅ This will be accessible at: http://localhost/quiz/api/uploads/subcategories/filename.jpg
            $imagePath = 'api/uploads/subcategories/' . $fileName;
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Image upload failed.'
            ]);
            exit;
        }
    }

    // ✅ Save to DB
    $stmt = $pdo->prepare("INSERT INTO subcategories (category_id, name, image) VALUES (?, ?, ?)");
    $stmt->execute([$category_id, $name, $imagePath]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Subcategory created!',
        'inserted_id' => $pdo->lastInsertId()
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}