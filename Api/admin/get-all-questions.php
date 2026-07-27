<?php

header("Content-Type: application/json");
require_once "../../utils/db.php";

try {

    $stmt = $pdo->prepare("
        SELECT
            a.id,
            a.question,
            a.option_a,
            a.option_b,
            a.option_c,
            a.option_d,
            a.correct_answer,
            a.explanation,
            a.created_at,
            a.set_id,
            COALESCE(s.set_name, '') AS set_name,
            COALESCE(s.chapter_id, 0) AS chapter_id,
            COALESCE(ch.chapter_name, '') AS chapter_name,
            COALESCE(ch.subject_id, 0) AS subject_id,
            COALESCE(sub.subject_name, '') AS subject_name,
            COALESCE(sub.class_id, 0) AS class_id,
            COALESCE(c.class_name, '') AS class_name
        FROM all_mock_tests a
        LEFT JOIN sets s ON s.id = a.set_id
        LEFT JOIN chapters ch ON ch.id = s.chapter_id
        LEFT JOIN subjects sub ON sub.id = ch.subject_id
        LEFT JOIN classes c ON c.id = sub.class_id
        ORDER BY a.created_at DESC, a.id DESC
    ");

    $stmt->execute();

    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => true,
        "total_questions" => count($questions),
        "data" => $questions
    ]);

} catch (PDOException $e) {

    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);

}
