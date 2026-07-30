<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

$class_id = intval($_GET['class_id'] ?? 0);
$subject_id = intval($_GET['subject_id'] ?? 0);

try {

    $today = date("Y-m-d");

    if ($class_id > 0 && $subject_id > 0) {
        // Fetch filtered by class + subject
        
        // Today's Tests
        $todayStmt = $pdo->prepare("
            SELECT
                mt.*,
                c.class_name,
                s.subject_name
            FROM mock_tests mt
            INNER JOIN classes c ON c.id = mt.class_id
            INNER JOIN subjects s ON s.id = mt.subject_id
            WHERE
                mt.class_id = ?
                AND mt.subject_id = ?
                AND mt.test_date = ?
            ORDER BY mt.start_time ASC
        ");
        $todayStmt->execute([$class_id, $subject_id, $today]);

        $upcomingStmt = $pdo->prepare("
            SELECT
                mt.*,
                c.class_name,
                s.subject_name
            FROM mock_tests mt
            INNER JOIN classes c ON c.id = mt.class_id
            INNER JOIN subjects s ON s.id = mt.subject_id
            WHERE
                mt.class_id = ?
                AND mt.subject_id = ?
                AND mt.test_date > ?
            ORDER BY mt.test_date ASC, mt.start_time ASC
        ");
        $upcomingStmt->execute([$class_id, $subject_id, $today]);

        $pastStmt = $pdo->prepare("
            SELECT
                mt.*,
                c.class_name,
                s.subject_name
            FROM mock_tests mt
            INNER JOIN classes c ON c.id = mt.class_id
            INNER JOIN subjects s ON s.id = mt.subject_id
            WHERE
                mt.class_id = ?
                AND mt.subject_id = ?
                AND mt.test_date < ?
            ORDER BY mt.test_date DESC, mt.start_time DESC
        ");
        $pastStmt->execute([$class_id, $subject_id, $today]);
        
        echo json_encode([
            "status" => true,
            "today" => $todayStmt->fetchAll(PDO::FETCH_ASSOC),
            "upcoming" => $upcomingStmt->fetchAll(PDO::FETCH_ASSOC),
            "past" => $pastStmt->fetchAll(PDO::FETCH_ASSOC)
        ]);
    } else {
        // Fetch ALL tests (no class/subject filter) - for initial page load
        
        $todayStmt = $pdo->prepare("
            SELECT
                mt.*,
                c.class_name,
                s.subject_name
            FROM mock_tests mt
            INNER JOIN classes c ON c.id = mt.class_id
            INNER JOIN subjects s ON s.id = mt.subject_id
            WHERE mt.test_date = ?
            ORDER BY mt.start_time ASC
        ");
        $todayStmt->execute([$today]);

        $upcomingStmt = $pdo->prepare("
            SELECT
                mt.*,
                c.class_name,
                s.subject_name
            FROM mock_tests mt
            INNER JOIN classes c ON c.id = mt.class_id
            INNER JOIN subjects s ON s.id = mt.subject_id
            WHERE mt.test_date > ?
            ORDER BY mt.test_date ASC, mt.start_time ASC
        ");
        $upcomingStmt->execute([$today]);

        $pastStmt = $pdo->prepare("
            SELECT
                mt.*,
                c.class_name,
                s.subject_name
            FROM mock_tests mt
            INNER JOIN classes c ON c.id = mt.class_id
            INNER JOIN subjects s ON s.id = mt.subject_id
            WHERE mt.test_date < ?
            ORDER BY mt.test_date DESC, mt.start_time DESC
        ");
        $pastStmt->execute([$today]);
        
        echo json_encode([
            "status" => true,
            "today" => $todayStmt->fetchAll(PDO::FETCH_ASSOC),
            "upcoming" => $upcomingStmt->fetchAll(PDO::FETCH_ASSOC),
            "past" => $pastStmt->fetchAll(PDO::FETCH_ASSOC)
        ]);
    }

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}