<?php

header("Content-Type: application/json");

require_once "../../utils/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$user_id = intval($data['user_id'] ?? 0);
$test_id = intval($data['test_id'] ?? 0);
$answers = $data['answers'] ?? [];

if (
    $user_id <= 0 ||
    $test_id <= 0 ||
    empty($answers)
) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid request"
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Retake Support
|--------------------------------------------------------------------------
|
| Old answers delete
|
*/

$pdo->prepare("
    DELETE FROM user_answers
    WHERE user_id = ?
    AND test_id = ?
")->execute([
    $user_id,
    $test_id
]);

$correct = 0;
$wrong = 0;

/*
|--------------------------------------------------------------------------
| Save Answers + Calculate Result
|--------------------------------------------------------------------------
*/

foreach ($answers as $question_id => $selected_answer) {

    $selected_answer = strtoupper(trim($selected_answer));

    $stmt = $pdo->prepare("
        SELECT correct_answer
        FROM questions
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$question_id]);

    $question = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$question) {
        continue;
    }

    $correct_answer = strtoupper(
        $question['correct_answer']
    );

    if ($selected_answer === $correct_answer) {
        $correct++;
    } else {
        $wrong++;
    }

    $stmt = $pdo->prepare("
        INSERT INTO user_answers
        (
            user_id,
            test_id,
            question_id,
            selected_answer
        )
        VALUES
        (?, ?, ?, ?)
    ");

    $stmt->execute([
        $user_id,
        $test_id,
        $question_id,
        $selected_answer
    ]);
}

$total_questions = $correct + $wrong;
$score = $correct;

/*
|--------------------------------------------------------------------------
| Save Latest Result
|--------------------------------------------------------------------------
*/

$pdo->prepare("
    DELETE FROM user_mock_results
    WHERE user_id = ?
    AND test_id = ?
")->execute([
    $user_id,
    $test_id
]);

$stmt = $pdo->prepare("
    INSERT INTO user_mock_results
    (
        user_id,
        test_id,
        total_questions,
        correct_answers,
        wrong_answers,
        score
    )
    VALUES
    (?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    $user_id,
    $test_id,
    $total_questions,
    $correct,
    $wrong,
    $score
]);

echo json_encode([
    "status" => true,
    "message" => "Test submitted successfully",
    "data" => [
        "score" => $score,
        "correct" => $correct,
        "wrong" => $wrong,
        "total_questions" => $total_questions
    ]
]);