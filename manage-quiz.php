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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Quizzes</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f9fafb;
      margin: 0;
      padding: 20px;
    }
    h1 {
      text-align: center;
      margin-bottom: 20px;
      color: #2c3e50;
    }
    .date-box {
      text-align: center;
      margin-bottom: 20px;
    }
    .date-box input {
      padding: 10px;
      border: 2px solid #3498db;
      border-radius: 6px;
      font-size: 16px;
      outline: none;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      border-radius: 8px;
      overflow: hidden;
    }
    table th, table td {
      padding: 12px;
      text-align: left;
      border-bottom: 1px solid #eee;
    }
    table th {
      background: #3498db;
      color: #fff;
      text-transform: uppercase;
      font-size: 14px;
    }
    table tr:nth-child(even) {
      background: #f8f9fa;
    }
    table tr:hover {
      background: #eaf2fd;
    }
    .btn-delete {
      padding: 6px 12px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      color: #fff;
      background: #e74c3c;
      font-size: 14px;
      transition: background 0.3s;
    }
    .btn-delete:hover {
      background: #c0392b;
    }
    .no-data {
      text-align: center;
      margin-top: 20px;
      color: #666;
      font-style: italic;
    }

    @media (max-width: 768px) {
      table, thead, tbody, th, td, tr {
        display: block;
      }
      thead tr {
        display: none;
      }
      tbody tr {
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 10px;
        background: #fff;
      }
      td {
        border: none;
        padding: 8px 10px;
      }
      td::before {
        content: attr(data-label);
        font-weight: bold;
        display: block;
        margin-bottom: 4px;
        color: #333;
      }
    }
  </style>
</head>
<body>

  <h1>📋 Manage Quizzes</h1>

  <div class="date-box">
    <input type="date" id="quizDate" onchange="loadQuizzes()" />
  </div>

  <div id="quizTableWrapper"></div>

  <script>
    async function loadQuizzes() {
      const date = document.getElementById("quizDate").value;
      if (!date) return;

      try {
        const res = await fetch(`api/get-quiz.php?date=${date}`);
        const data = await res.json();

        if (data.status === "success" && data.quizzes.length > 0) {
          renderTable(data.quizzes);
        } else {
          document.getElementById("quizTableWrapper").innerHTML = `<p class="no-data">No quizzes found for this date.</p>`;
        }
      } catch (err) {
        console.error(err);
        document.getElementById("quizTableWrapper").innerHTML = `<p class="no-data">Error loading data.</p>`;
      }
    }

    function renderTable(quizzes) {
      let html = `
        <table>
          <thead>
            <tr>
              <th>Title</th>
              <th>Description</th>
              <th>Question</th>
              <th>Options</th>
              <th>Correct Answer</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
      `;

      quizzes.forEach(q => {
        q.questions.forEach(question => {
          html += `
            <tr id="quiz-${q.id}">
              <td data-label="Title">${q.title}</td>
              <td data-label="Description">${q.description}</td>
              <td data-label="Question">${question.question}</td>
              <td data-label="Options">
                A: ${question.option_a} <br>
                B: ${question.option_b} <br>
                C: ${question.option_c} <br>
                D: ${question.option_d}
              </td>
              <td data-label="Correct">${question.correct_option}</td>
              <td data-label="Action">
                <button class="btn-delete" onclick="deleteQuiz(${q.id})">Delete</button>
              </td>
            </tr>
          `;
        });
      });

      html += `</tbody></table>`;
      document.getElementById("quizTableWrapper").innerHTML = html;
    }

    async function deleteQuiz(id) {
      if (!confirm("Are you sure you want to delete this quiz (including all questions)?")) return;

      const date = document.getElementById("quizDate").value; // ✅ ekhane date save korlam

      try {
        const res = await fetch("api/delete-quiz.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ quiz_id: id })
        });

        const data = await res.json();
        if (data.status === "success") {
          alert("Quiz deleted successfully!");
          if (date) {
            loadQuizzes(); // ✅ same date diye abar reload
          }
        } else {
          alert("Failed to delete: " + data.message);
        }
      } catch (err) {
        console.error(err);
        alert("Error deleting quiz.");
      }
    }
  </script>

</body>
</html>