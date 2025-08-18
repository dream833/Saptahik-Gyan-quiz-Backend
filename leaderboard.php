
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
<title>Daily Leaderboard</title>
<style>
    body {
        font-family: 'Segoe UI', sans-serif;
        background: linear-gradient(135deg, #1f1c2c, #928dab);
        color: #fff;
        padding: 20px;
    }
    .container {
        max-width: 1100px;
        margin: auto;
        background: rgba(255, 255, 255, 0.08);
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        backdrop-filter: blur(8px);
    }
    h1 {
        text-align: center;
        margin-bottom: 20px;
    }
    .date-select {
        display: flex;
        justify-content: center;
        margin-bottom: 20px;
    }
    .date-select input {
        padding: 10px;
        border-radius: 8px;
        border: none;
        font-size: 16px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    thead {
        background: rgba(255,255,255,0.15);
    }
    thead th {
        padding: 12px;
        text-align: left;
        font-weight: bold;
    }
    tbody tr {
        background: rgba(255,255,255,0.08);
        transition: 0.3s;
    }
    tbody tr:hover {
        background: rgba(255,255,255,0.15);
    }
    td {
        padding: 12px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        word-wrap: break-word;
        white-space: normal;
    }

    /* Responsive Table */
    @media (max-width: 768px) {
        table, thead, tbody, th, td, tr {
            display: block;
            width: 100%;
        }
        thead {
            display: none;
        }
        tr {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            margin-bottom: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            padding: 10px;
        }
        td {
            text-align: right;
            padding: 10px 15px;
            position: relative;
            border: none;
        }
        td::before {
            content: attr(data-label);
            position: absolute;
            left: 15px;
            font-weight: bold;
            color: #ffda79;
            text-align: left;
        }
    }
</style>
</head>
<body>

<div class="container">
    <h1>🏆 Daily Leaderboard</h1>

    <div class="date-select">
        <input type="date" id="leaderboardDate" onchange="loadLeaderboard()">
    </div>

    <table id="leaderboardTable">
        <thead>
            <tr>
                <th>Rank</th>
                <th>Username</th>
                <th>Submission Time</th>
                <th>Correct Answers</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="4" style="text-align:center; color: #ccc;">📅 Please select a date to view the leaderboard</td>
            </tr>
        </tbody>
    </table>
</div>
<script>
document.addEventListener("DOMContentLoaded", () => {
    // Yesterday’s date ber korbo
    const today = new Date();
    today.setDate(today.getDate() - 1);
    const yesterday = today.toISOString().split("T")[0];

    // Input e set kore dibo
    const dateInput = document.getElementById("leaderboardDate");
    dateInput.value = yesterday;

    // Auto load yesterday’s leaderboard
    loadLeaderboard();
});

async function loadLeaderboard() {
    const dateValue = document.getElementById("leaderboardDate").value;
    const tbody = document.querySelector("#leaderboardTable tbody");

    if (!dateValue) {
        tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; color: #ccc;">📅 Please select a date to view the leaderboard</td></tr>`;
        return;
    }

    // Check if selected date is today → leaderboard show kora jabe na
    const today = new Date().toISOString().split("T")[0];
    if (dateValue === today) {
        tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; color: orange;">⏳ Today's leaderboard will be available after midnight</td></tr>`;
        return;
    }

    tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; color: #ccc;">⏳ Loading...</td></tr>`;

    try {
        const res = await fetch(`api/get-leaderboard.php?date=${dateValue}`);
        const data = await res.json();

        if (data.status !== "success" || data.leaderboard.length === 0) {
            tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; color: #ccc;">❌ No leaderboard found for this date</td></tr>`;
            return;
        }

        tbody.innerHTML = "";
        data.leaderboard.forEach((player, index) => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td data-label="Rank">#${index + 1}</td>
                <td data-label="Username">${player.first_name} ${player.last_name}</td>
                <td data-label="Submission Time">${player.time_taken} sec</td>
                <td data-label="Correct Answers">${player.correct_answers}/${player.total_questions}</td>
            `;
            tbody.appendChild(tr);
        });

    } catch (err) {
        console.error(err);
        tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; color: red;">⚠️ Failed to load leaderboard</td></tr>`;
    }
}
</script>
</body>
</html>