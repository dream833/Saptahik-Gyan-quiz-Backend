
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: adminlogin.php");
    exit;
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
    .table thead { background:#0d6efd; color:#fff; }
    .card { box-shadow:0 4px 10px rgba(0,0,0,0.1); border-radius:12px; }
    .btn-sm { min-width: 36px; }
  </style>
</head>
<body class="p-4">

<div class="container">
  <div class="card p-4">
    <h3 class="mb-3">📚 Manage GK Questions</h3>

    <!-- Title -->
    <div class="mb-3">
      <label class="form-label">Title / Category</label>
      <input type="text" id="title" class="form-control" placeholder="Enter new title (e.g. ভারতের ভূগোল)">
    </div>

    <!-- Questions Table -->
    <table class="table table-bordered align-middle" id="questionTable">
      <thead>
        <tr>
          <th style="width:40%">Question</th>
          <th style="width:40%">Answer</th>
          <th style="width:20%">Action</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><input type="text" class="form-control" name="question[]"></td>
          <td><input type="text" class="form-control" name="answer[]"></td>
          <td class="text-center">
            <button type="button" class="btn btn-success btn-sm addRow">➕</button>
            <button type="button" class="btn btn-danger btn-sm removeRow">❌</button>
          </td>
        </tr>
      </tbody>
    </table>

    <button class="btn btn-primary mt-3" id="saveBtn">💾 Save Questions</button>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function createRow(q='', a=''){
    return `<tr>
        <td><input type="text" class="form-control" name="question[]" value="${q}"></td>
        <td><input type="text" class="form-control" name="answer[]" value="${a}"></td>
        <td class="text-center">
          <button type="button" class="btn btn-success btn-sm addRow">➕</button>
          <button type="button" class="btn btn-danger btn-sm removeRow">❌</button>
        </td>
    </tr>`;
}

// Add new row
$(document).on("click",".addRow",function(){
    $("#questionTable tbody").append(createRow());
});

// Remove row
$(document).on("click",".removeRow",function(){
    if($("#questionTable tbody tr").length > 1){
        $(this).closest("tr").remove();
    } else {
        alert("At least one question row is required!");
    }
});

// Save questions
$("#saveBtn").click(function(){
    let title = $("#title").val().trim();
    if(title === ""){
        alert("Please enter a title!");
        return;
    }

    let questions = [];
    $("#questionTable tbody tr").each(function(){
        let q = $(this).find("input[name='question[]']").val().trim();
        let a = $(this).find("input[name='answer[]']").val().trim();
        if(q && a){
            questions.push({question:q, answer:a});
        }
    });

    if(questions.length === 0){
        alert("Please enter at least one question and answer!");
        return;
    }

    $.ajax({
        url:"api/save-spc-question.php",
        method:"POST",
        data:JSON.stringify({title:title, data:questions}),
        contentType:"application/json",
        success:function(res){
            let data = (typeof res === "string") ? JSON.parse(res) : res;
            if(data.status === "success"){
                alert("✅ " + data.message);
                $("#title").val("");
                $("#questionTable tbody").html(createRow());
            } else {
                alert("❌ " + data.message);
            }
        },
        error:function(xhr){
            console.error(xhr.responseText);
            alert("❌ API Error - Please check console");
        }
    });
});
</script>
</body>
</html>