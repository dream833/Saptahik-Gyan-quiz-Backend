<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$class_id = intval($data['class_id'] ?? 0);
$subject_id = intval($data['subject_id'] ?? 0);

if ($class_id <= 0 || $subject_id <= 0) {

    echo json_encode([
        "status" => false,
        "message" => "Invalid Class or Subject"
    ]);
    exit;

}

try {

    $today = date("Y-m-d");
    $now = date("H:i:s");

    $stmt = $pdo->prepare("
        SELECT
            id,
            test_name,
            description,
            test_date,
            start_time,
            end_time,
            duration_minutes,
            total_questions,
            total_marks
        FROM mock_tests
        WHERE
            class_id = ?
        AND
            subject_id = ?
        AND
            status = 'active'
        ORDER BY
            test_date ASC,
            start_time ASC
    ");

    $stmt->execute([
        $class_id,
        $subject_id
    ]);

    $tests = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        if ($row['test_date'] < $today) {

            $row['status'] = "past";

        } elseif ($row['test_date'] > $today) {

            $row['status'] = "upcoming";

        } else {

            if ($now < $row['start_time']) {

                $row['status'] = "upcoming";

            } elseif ($now > $row['end_time']) {

                $row['status'] = "past";

            } else {

                $row['status'] = "live";

            }

        }

        $tests[] = $row;

    }

    echo json_encode([
        "status" => true,
        "data" => $tests
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}