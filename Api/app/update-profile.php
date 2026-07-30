<?php

header("Content-Type: application/json");

require_once "../../utils/db.php";

// ===== Detect request format: JSON vs Form-data =====
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

if (strpos($contentType, 'application/json') !== false) {
    // === JSON PAYLOAD (base64 image support — bypasses ModSecurity multipart blocking) ===
    $data = json_decode(file_get_contents("php://input"), true);
    $user_id     = intval($data['user_id'] ?? 0);
    $full_name   = trim($data['full_name'] ?? '');
    $email       = trim($data['email'] ?? '');
    $mobile      = trim($data['mobile'] ?? '');
    $address     = trim($data['address'] ?? '');
    $class_grade = trim($data['class_grade'] ?? '');
    $about_me    = trim($data['about_me'] ?? '');
    $imageBase64 = $data['profile_image'] ?? '';
} else {
    // === MULTIPART FORM-DATA (existing behavior) ===
    $user_id     = intval($_POST['user_id'] ?? 0);
    $full_name   = trim($_POST['full_name'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $mobile      = trim($_POST['mobile'] ?? '');
    $address     = trim($_POST['address'] ?? '');
    $class_grade = trim($_POST['class_grade'] ?? '');
    $about_me    = trim($_POST['about_me'] ?? '');
    $imageBase64 = '';
}

// ===== Validate required fields =====
if ($user_id <= 0 || empty($full_name) || empty($email) || empty($mobile)) {
    echo json_encode([
        "status"  => false,
        "message" => "Required fields missing"
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "status"  => false,
        "message" => "Invalid email address"
    ]);
    exit;
}

// ===== Check for duplicate email/mobile =====
$stmt = $pdo->prepare("SELECT id FROM users WHERE (email = ? OR mobile = ?) AND id != ?");
$stmt->execute([$email, $mobile, $user_id]);

if ($stmt->fetch()) {
    echo json_encode([
        "status"  => false,
        "message" => "Email or mobile already exists"
    ]);
    exit;
}

// ===== Handle Image Upload =====
$imagePath = null;

$hasMultipartFile = (
    isset($_FILES['profile_image']) &&
    $_FILES['profile_image']['error'] == 0
);

$hasBase64Image = (
    !empty($imageBase64) &&
    strpos($imageBase64, 'data:image/') === 0
);

if ($hasMultipartFile || $hasBase64Image) {
    $uploadDir = __DIR__ . "/../../uploads/profile/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $extension = '';
    $fileData  = null;

    if ($hasMultipartFile) {
        // Multipart: get extension from uploaded file
        $extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $allowed   = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($extension, $allowed)) {
            echo json_encode([
                "status"  => false,
                "message" => "Only JPG, PNG, WEBP allowed"
            ]);
            exit;
        }

        $fileData = file_get_contents($_FILES['profile_image']['tmp_name']);

    } else {
        // Base64: decode and detect extension
        preg_match('/^data:image\/(\w+);base64,/', $imageBase64, $matches);
        $mimeExt = strtolower($matches[1] ?? '');

        $mimeMap = [
            'jpeg' => 'jpg', 'jpg' => 'jpg',
            'png'  => 'png', 'webp' => 'webp', 'gif' => 'gif'
        ];

        $extension = $mimeMap[$mimeExt] ?? null;

        if (!$extension) {
            echo json_encode([
                "status"  => false,
                "message" => "Only JPG, PNG, WEBP images allowed"
            ]);
            exit;
        }

        $base64Data = substr($imageBase64, strpos($imageBase64, ',') + 1);
        $fileData   = base64_decode($base64Data);

        if ($fileData === false || strlen($fileData) === 0) {
            echo json_encode([
                "status"  => false,
                "message" => "Invalid image data"
            ]);
            exit;
        }
    }

    $fileName   = "user_" . $user_id . "_" . time() . "." . $extension;
    $targetPath = $uploadDir . $fileName;

    if (file_put_contents($targetPath, $fileData) !== false) {
        $imagePath = "uploads/profile/" . $fileName;
    } else {
        echo json_encode([
            "status"  => false,
            "message" => "Failed to save image"
        ]);
        exit;
    }
}

// ===== Update user profile in database =====
if ($imagePath) {
    $stmt = $pdo->prepare("
        UPDATE users
        SET full_name = ?, email = ?, mobile = ?, address = ?,
            class_grade = ?, about_me = ?, profile_image = ?
        WHERE id = ?
    ");
    $stmt->execute([
        $full_name, $email, $mobile,
        $address, $class_grade, $about_me,
        $imagePath, $user_id
    ]);
} else {
    $stmt = $pdo->prepare("
        UPDATE users
        SET full_name = ?, email = ?, mobile = ?, address = ?,
            class_grade = ?, about_me = ?
        WHERE id = ?
    ");
    $stmt->execute([
        $full_name, $email, $mobile,
        $address, $class_grade, $about_me,
        $user_id
    ]);
}

echo json_encode([
    "status"  => true,
    "message" => "Profile updated successfully"
]);