<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include 'quizauth.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Questions</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f5f5f5;
      padding: 20px;
    }
    .container {
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      max-width: 900px;
      margin: auto;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    h2 {
      text-align: center;
      margin-bottom: 20px;
    }
    label {
      font-weight: bold;
      display: block;
      margin: 15px 0 5px;
    }
    select {
      width: 100%;
      padding: 10px;
      margin-bottom: 20px;
      border-radius: 6px;
      border: 1px solid #ccc;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }
    th, td {
      border: 1px solid #ccc;
      padding: 10px;
      text-align: left;
    }
    th {
      background-color: #f2f2f2;
    }
    .delete-btn {
      padding: 6px 12px;
      color: white;
      background: red;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
  </style>
</head>
<body>

<div class="container">
  <h2>Manage Questions</h2>

  <label for="category">Category</label>
  <select id="category">
    <option value="">-- Select Category --</option>
  </select>

  <label for="subCategory">Sub-Category</label>
  <select id="subCategory">
    <option value="">-- Select Sub-Category --</option>
  </select>

  <table id="questionTable" style="display: none;">
    <thead>
      <tr>
        <th>#</th>
        <th>Category</th>
        <th>Sub-Category</th>
        <th>Question</th>
        <th>Answer</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<script>
  window.addEventListener('DOMContentLoaded', () => {
    fetch('api/getcategory.php')
      .then(res => res.json())
      .then(res => {
        const catSel = document.getElementById('category');
        if (res.status === 'success' && Array.isArray(res.data)) {
          res.data.forEach(cat => {
            const opt = document.createElement('option');
            opt.value = cat.id;
            opt.textContent = cat.name;
            catSel.appendChild(opt);
          });
        } else {
          alert("Category load failed");
        }
      });
  });

  document.getElementById('category').addEventListener('change', function () {
    const catId = this.value;
    const subSel = document.getElementById('subCategory');
    subSel.innerHTML = '<option value="">-- Select Sub-Category --</option>';

    if (!catId) return;

    fetch('api/getsubcategory.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ category_id: catId })
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success' && Array.isArray(data.data)) {
        data.data.forEach(sub => {
          const opt = document.createElement('option');
          opt.value = sub.id;
          opt.textContent = sub.name;
          subSel.appendChild(opt);
        });
      } else {
        alert("Sub-category load failed");
      }
    });
  });

  document.getElementById('subCategory').addEventListener('change', function () {
    const catId = document.getElementById('category').value;
    const subId = this.value;
    if (!catId || !subId) return;

    fetch('api/getquestions.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ category_id: catId, sub_category_id: subId })
    })
    .then(res => res.json())
    .then(data => {
      const table = document.getElementById('questionTable');
      const tbody = table.querySelector('tbody');
      tbody.innerHTML = '';

      if (data.status === 'success' && Array.isArray(data.data)) {
        data.data.forEach((q, index) => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${index + 1}</td>
            <td>${q.category}</td>
            <td>${q.sub_category}</td>
            <td>${q.question}</td>
            <td>${q.answer}</td>
            <td><button class="delete-btn" onclick="deleteQuestion(${q.id}, this)">Delete</button></td>
          `;
          tbody.appendChild(tr);
        });

        table.style.display = 'table';
      } else {
        table.style.display = 'none';
        alert('No questions found.');
      }
    });
  });

  function deleteQuestion(id, btn) {
    if (!confirm("Are you sure to delete this question?")) return;

    fetch('api/deletequestion.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ question_id: id })
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        alert(data.message);
        btn.closest('tr').remove();
      } else {
        alert('Delete failed: ' + (data.message || 'Unknown error'));
      }
    })
    .catch(err => {
      console.error(err);
      alert('Delete error');
    });
  }
</script>

</body>
</html>