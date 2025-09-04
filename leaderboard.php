<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Quiz Leaderboard</title>
  <style>
    body {
      font-family: "Inter", Arial, sans-serif;
      background: #f5f6fa;
      color: #2c3e50;
      padding: 20px;
      margin: 0;
    }
    h2 {
      text-align: center;
      color: #2c3e50;
      margin-bottom: 25px;
      font-size: 28px;
      font-weight: 700;
    }
    h3 {
      margin-top: 20px;
      color: #34495e;
    }
    input, button {
      padding: 10px 15px;
      margin: 10px 5px;
      border-radius: 8px;
      border: 1px solid #dcdde1;
      font-size: 16px;
      outline: none;
    }
    input {
      background: #fff;
      color: #2c3e50;
      width: 200px;
    }
    button {
      background: #4a90e2;
      color: #fff;
      font-weight: 500;
      cursor: pointer;
      border: none;
      box-shadow: 0 2px 6px rgba(0,0,0,0.15);
      transition: all 0.2s ease;
    }
    button:hover {
      background: #357abd;
      transform: translateY(-2px);
    }
    .quiz-btn {
      display: block;
      width: 100%;
      margin: 8px 0;
      background: #fff;
      color: #2c3e50;
      text-align: left;
      border: 1px solid #e0e0e0;
      border-radius: 10px;
      padding: 12px 16px;
      font-size: 16px;
      font-weight: 500;
      box-shadow: 0 2px 6px rgba(0,0,0,0.05);
      transition: all 0.3s ease;
    }
    .quiz-btn:hover {
      background: #f0f3f9;
      transform: translateX(4px);
    }
    #quizList {
      max-width: 500px;
      margin: 0 auto;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 25px;
      display: none;
      background: #fff;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    table th, table td {
      padding: 14px;
      text-align: center;
      font-size: 15px;
    }
    table th {
      background: #f0f2f5;
      color: #2c3e50;
      font-weight: 600;
    }
    table tr:nth-child(even) {
      background: #fafbfc;
    }
    table tr:hover {
      background: #f5f8fc;
    }
    /* Top 3 Highlight */
    tbody tr:nth-child(1) {
      background: #ffeaa7 !important;
      font-weight: 600;
    }
    tbody tr:nth-child(2) {
      background: #dfe6e9 !important;
      font-weight: 600;
    }
    tbody tr:nth-child(3) {
      background: #fab1a0 !important;
      font-weight: 600;
    }
    @media (max-width: 600px) {
      table, thead, tbody, th, td, tr {
        display: block;
      }
      th {
        display: none;
      }
      td {
        display: flex;
        justify-content: space-between;
        padding: 12px;
        border-bottom: 1px solid #eee;
      }
      td::before {
        content: attr(data-label);
        color: #4a90e2;
        flex: 1;
        text-align: left;
        font-weight: 600;
      }
    }
  </style>
</head>
<body>
  <h2>📊 Quiz Leaderboard</h2>

  <!-- Date Input -->
  <div style="text-align:center;">
    <input type="date" id="leaderboardDate">
    <button onclick="loadQuizList()">Load Quizzes</button>
  </div>

  <!-- Quiz List -->
  <div id="quizList"></div>

  <!-- Leaderboard Table -->
  <table id="leaderboardTable">
    <thead>
      <tr>
        <th>Rank</th>
        <th>Username</th>
        <th>Correct</th>
        <th>Wrong</th>
        <th>Time Taken</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>

  <script>
    async function loadQuizList() {
      const dateValue = document.getElementById("leaderboardDate").value;
      const quizList = document.getElementById("quizList");

      if (!dateValue) {
        alert("Please select a date first!");
        return;
      }

      quizList.innerHTML = `<p style="text-align:center; color:#666;">⏳ Loading quizzes...</p>`;

      try {
        const res = await fetch("api/get-leader-quizbydate.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ date: dateValue })
        });
        const data = await res.json();

        if (data.status !== "success" || !data.quizzes || data.quizzes.length === 0) {
          quizList.innerHTML = `<p style="text-align:center; color:#999;">❌ No quizzes found</p>`;
          document.getElementById("leaderboardTable").style.display = "none";
          return;
        }

        quizList.innerHTML = `<h3>Select a Quiz:</h3>`;
        data.quizzes.forEach(quiz => {
          const btn = document.createElement("button");
          btn.textContent = quiz.title;
          btn.className = "quiz-btn";
          btn.onclick = () => loadLeaderboard(dateValue, quiz.quiz_id, quiz.title);
          quizList.appendChild(btn);
        });

      } catch (err) {
        console.error(err);
        quizList.innerHTML = `<p style="text-align:center; color:red;">⚠️ Failed to load quizzes</p>`;
      }
    }

    async function loadLeaderboard(date, quizId, quizTitle) {
      const table = document.getElementById("leaderboardTable");
      const tbody = table.querySelector("tbody");

      tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; color:#666;">⏳ Loading leaderboard...</td></tr>`;
      table.style.display = "table";

      try {
        const res = await fetch("api/get-leaderboard.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ date: date, quizid: quizId })
        });
        const data = await res.json();

        if (data.status !== "success" || !data.leaderboard || data.leaderboard.length === 0) {
          tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; color:#999;">❌ No leaderboard found for "${quizTitle}"</td></tr>`;
          return;
        }

        tbody.innerHTML = "";
        data.leaderboard.forEach((player, index) => {
          const tr = document.createElement("tr");
          tr.innerHTML = `
            <td data-label="Rank">#${player.rank ?? index + 1}</td>
            <td data-label="Username">${player.name}</td>
            <td data-label="Correct">${player.correct_answers}</td>
            <td data-label="Wrong">${player.wrong_answers}</td>
            <td data-label="Time Taken">${player.time_taken} sec</td>
          `;
          tbody.appendChild(tr);
        });

      } catch (err) {
        console.error(err);
        tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; color:red;">⚠️ Failed to load leaderboard</td></tr>`;
      }
    }
  </script>
</body>
</html>