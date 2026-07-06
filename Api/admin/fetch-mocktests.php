<?php

header("Content-Type: application/json");

require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$filter   = trim($data['filter'] ?? 'today'); // 'today', 'upcoming', 'date'
$test_date = trim($data['test_date'] ?? '');

$today = date('Y-m-d');

$sql = "
    SELECT
        mt.id,
        mt.test_name AS name,
        mt.test_date AS date,
        mt.duration_minutes AS duration,
        mt.description,
        mt.total_questions,
        mt.class_id,
        mt.subject_id,
        c.class_name AS className,
        s.subject_name AS subjectName
    FROM mock_tests mt
    LEFT JOIN classes c ON mt.class_id = c.id
    LEFT JOIN subjects s ON mt.subject_id = s.id
    WHERE mt.status = 'active'
";

$params = [];

if ($filter === 'today') {
    $sql .= " AND mt.test_date = ?";
    $params[] = $today;
} elseif ($filter === 'upcoming') {
    $sql .= " AND mt.test_date > ?";
    $params[] = $today;
} elseif ($filter === 'date' && !empty($test_date)) {
    $sql .= " AND mt.test_date = ?";
    $params[] = $test_date;
} else {
    echo json_encode([
        "status" => false,
        "message" => "Invalid filter or missing test_date"
    ]);
    exit;
}

$sql .= " ORDER BY mt.test_date DESC, mt.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Add questions count for each test
foreach ($tests as &$test) {
    $qStmt = $pdo->prepare("
        SELECT COUNT(*) AS qcount
        FROM mock_test_questions
        WHERE test_id = ?
    ");
    $qStmt->execute([$test['id']]);
    $test['questions_count'] = intval($qStmt->fetchColumn());
}
unset($test);

echo json_encode([
    "status" => true,
    "data" => $tests
]);
