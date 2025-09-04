<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include 'quizauth.php';

require 'api/db.php'; // DB connection

// Delete question
if(isset($_GET['delete'])){
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM gk_questions WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: manage-spc-question.php");
    exit;
}

// Update question
if(isset($_POST['update'])){
    $id = (int)$_POST['id'];
    $question = trim($_POST['question']);
    $answer = trim($_POST['answer']);
    if($question && $answer){
        $stmt = $pdo->prepare("UPDATE gk_questions SET question = ?, answer = ? WHERE id = ?");
        $stmt->execute([$question, $answer, $id]);
    }
    header("Location: manage-spc-question.php");
    exit;
}

// Fetch all questions with title name
$stmt = $pdo->query("
    SELECT q.id, q.question, q.answer, t.title 
    FROM gk_questions q
    JOIN gk_titles t ON q.title_id = t.id
    ORDER BY t.title, q.id
");
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group by title
$grouped = [];
foreach($questions as $q){
    $grouped[$q['title']][] = $q;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage GK Questions</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <style>
    body { background:#f8f9fa; }
    .card { box-shadow:0 4px 10px rgba(0,0,0,0.1); border-radius:12px; margin-bottom:20px; padding:15px; }
    .table thead { background:#0d6efd; color:#fff; }
    .form-inline { display:flex; gap:5px; flex-wrap: wrap; }
    .form-inline input { flex: 1; }
  </style>
</head>
<body class="p-4">

<div class="container">
    <h3 class="mb-3">📚 Manage GK Questions</h3>

    <?php foreach($grouped as $title => $qs): ?>
    <div class="card mb-4">
        <h5 class="mb-3">📌 <?= htmlspecialchars($title) ?></h5>
        <?php foreach($qs as $q): ?>
        <form method="post" class="form-inline mb-2">
            <input type="hidden" name="id" value="<?= $q['id'] ?>">
            <input type="text" name="question" class="form-control" value="<?= htmlspecialchars($q['question']) ?>">
            <input type="text" name="answer" class="form-control" value="<?= htmlspecialchars($q['answer']) ?>">
            <button type="submit" name="update" class="btn btn-primary btn-sm">✏️ Save</button>
            <a href="?delete=<?= $q['id'] ?>" onclick="return confirm('Are you sure?')" class="btn btn-danger btn-sm">🗑️ Delete</a>
        </form>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
</div>

</body>
</html>