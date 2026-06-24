<?php

header("Content-Type: application/json");

require_once "../../utils/db.php";

$user_id     = intval($_POST['user_id'] ?? 0);
$full_name   = trim($_POST['full_name'] ?? '');
$email       = trim($_POST['email'] ?? '');
$mobile      = trim($_POST['mobile'] ?? '');
$address     = trim($_POST['address'] ?? '');
$class_grade = trim($_POST['class_grade'] ?? '');
$about_me    = trim($_POST['about_me'] ?? '');

if (
    $user_id <= 0 ||
    empty($full_name) ||
    empty($email) ||
    empty($mobile)
) {
    echo json_encode([
        "status" => false,
        "message" => "Required fields missing"
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid email address"
    ]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT id
    FROM users
    WHERE (email = ? OR mobile = ?)
    AND id != ?
");

$stmt->execute([
    $email,
    $mobile,
    $user_id
]);

if ($stmt->fetch()) {
    echo json_encode([
        "status" => false,
        "message" => "Email or mobile already exists"
    ]);
    exit;
}

$imagePath = null;

if (
    isset($_FILES['profile_image']) &&
    $_FILES['profile_image']['error'] == 0
) {

    $uploadDir = "../../uploads/profile/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $extension = strtolower(
        pathinfo(
            $_FILES['profile_image']['name'],
            PATHINFO_EXTENSION
        )
    );

    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($extension, $allowed)) {
        echo json_encode([
            "status" => false,
            "message" => "Only JPG, PNG, WEBP allowed"
        ]);
        exit;
    }

    $fileName = "user_" . $user_id . "_" . time() . "." . $extension;

    $targetPath = $uploadDir . $fileName;

    if (
        move_uploaded_file(
            $_FILES['profile_image']['tmp_name'],
            $targetPath
        )
    ) {
        $imagePath = "uploads/profile/" . $fileName;
    }
}

if ($imagePath) {

    $stmt = $pdo->prepare("
        UPDATE users
        SET
            full_name = ?,
            email = ?,
            mobile = ?,
            address = ?,
            class_grade = ?,
            about_me = ?,
            profile_image = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $full_name,
        $email,
        $mobile,
        $address,
        $class_grade,
        $about_me,
        $imagePath,
        $user_id
    ]);

} else {

    $stmt = $pdo->prepare("
        UPDATE users
        SET
            full_name = ?,
            email = ?,
            mobile = ?,
            address = ?,
            class_grade = ?,
            about_me = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $full_name,
        $email,
        $mobile,
        $address,
        $class_grade,
        $about_me,
        $user_id
    ]);
}

echo json_encode([
    "status" => true,
    "message" => "Profile updated successfully"
]);