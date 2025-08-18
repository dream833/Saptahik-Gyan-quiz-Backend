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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Users - Admin Panel</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Roboto', sans-serif;
    }

    body {
      min-height: 100vh;
      background: #f0f2f5;
      display: flex;
      flex-direction: column;
    }

    .topbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #1a237e;
      padding: 10px 20px;
      color: white;
      position: relative;
      z-index: 1001;
    }

    .toggle-btn {
      display: none;
      font-size: 20px;
      background: #1a237e;
      color: white;
      border: none;
      cursor: pointer;
    }

    .container {
      display: flex;
      flex: 1;
    }

    .sidebar {
      width: 230px;
      background: #1a237e;
      color: white;
      padding: 20px;
      transition: transform 0.3s ease-in-out;
      z-index: 1000;
    }

    .sidebar h2 {
      text-align: center;
      margin-bottom: 40px;
      font-size: 22px;
    }

    .sidebar ul {
      list-style: none;
    }

    .sidebar ul li {
      margin: 18px 0;
    }

    .sidebar ul li a {
      display: flex;
      align-items: center;
      text-decoration: none;
      color: white;
      font-size: 16px;
      padding: 10px;
      border-radius: 6px;
      transition: 0.3s;
    }

    .sidebar ul li a i {
      margin-right: 12px;
      font-size: 18px;
    }

    .sidebar ul li a:hover {
      background: rgba(255, 255, 255, 0.1);
    }

    .main-content {
      flex: 1;
      padding: 30px;
    }

    h1 {
      color: #1a237e;
      margin-bottom: 20px;
    }

    .search-box {
      margin-bottom: 20px;
      max-width: 400px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .search-box input {
      width: 100%;
      padding: 10px 15px;
      font-size: 16px;
      border: 1px solid #ccc;
      border-radius: 6px;
      outline: none;
    }

    .search-box i {
      font-size: 18px;
      color: #555;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      border-radius: 8px;
      overflow: hidden;
    }

    thead {
      background: #1a237e;
      color: white;
    }

    th, td {
      padding: 15px;
      text-align: left;
      border-bottom: 1px solid #eee;
    }

    tbody tr:hover {
      background: #f0f4ff;
    }

    .action-btn {
      background: #1a237e;
      color: white;
      border: none;
      padding: 8px 12px;
      border-radius: 4px;
      cursor: pointer;
      font-size: 14px;
    }

    .action-btn:hover {
      background: #0d165c;
    }

    #overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      background: rgba(0, 0, 0, 0.4);
      z-index: 999;
    }

    @media (max-width: 768px) {
      .toggle-btn {
        display: block;
      }

      .container {
        flex-direction: column;
      }

      .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        transform: translateX(-100%);
      }

      .sidebar.active {
        transform: translateX(0);
      }

      .main-content {
        padding: 20px;
      }

      table, thead, tbody, th, td, tr {
        display: block;
      }

      thead {
        display: none;
      }

      tr {
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background: white;
        padding: 10px;
      }

      td {
        padding: 10px;
        text-align: right;
        position: relative;
      }

      td::before {
        content: attr(data-label);
        position: absolute;
        left: 10px;
        font-weight: bold;
        color: #333;
        text-align: left;
      }
    }
  </style>
</head>
<body>

  <div class="topbar">
    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>
    <h2>Users List</h2>
  </div>

  <div id="overlay" onclick="closeSidebar()"></div>

  <div class="container">
    <div class="sidebar" id="sidebar">
      <h2>Admin Panel</h2>
      <ul>
        <li><a href="dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a></li>
        <!-- <li><a href="quiz.php"><i class="fas fa-calendar-check"></i> Daily Quiz</a></li> -->
        <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
        <!-- <li><a href="question.php"><i class="fas fa-question-circle"></i> Questions</a></li> -->
        <li><a href="leaderboard.php"><i class="fas fa-trophy"></i> Leaderboard</a></li>
      </ul>
    </div>

    <div class="main-content">
      <h1><i class="fas fa-users"></i> User Management</h1>

      <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" placeholder="Search users by name, email or phone..." onkeyup="searchUsers()">
      </div>

      <table id="userTable">
        <thead>
          <tr>
            <th>#ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Joined</th>
          </tr>
        </thead>
        <tbody id="userTableBody">
          <!-- Dynamic data will be loaded here -->
        </tbody>
      </table>
    </div>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", () => {
      fetchUsers();
    });

    function fetchUsers() {
      fetch("api/adminuserfetch.php")
        .then(res => res.json())
        .then(data => {
          const tbody = document.getElementById("userTableBody");
          tbody.innerHTML = "";

          if (data.users && data.users.length > 0) {
            data.users.forEach(user => {
              const row = document.createElement("tr");
              row.innerHTML = `
                <td data-label="#ID">${user.id}</td>
                <td data-label="Name">${user.name}</td>
                <td data-label="Email">${user.email}</td>
                <td data-label="Phone">${user.phone || ''}</td>
                <td data-label="Joined">${user.created_at}</td>
              `;
              tbody.appendChild(row);
            });
          } else {
            tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;">No users found</td></tr>`;
          }
        })
        .catch(error => {
          console.error("Error fetching users:", error);
          const tbody = document.getElementById("userTableBody");
          tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; color:red;">Failed to load users</td></tr>`;
        });
    }
    function searchUsers() {
      const input = document.getElementById("searchInput").value.toLowerCase();
      const table = document.getElementById("userTable");
      const tr = table.getElementsByTagName("tr");

      for (let i = 1; i < tr.length; i++) {
        const tds = tr[i].getElementsByTagName("td");
        const name = tds[1]?.textContent.toLowerCase();
        const email = tds[2]?.textContent.toLowerCase();
        const phone = tds[3]?.textContent.toLowerCase();
        const match = name.includes(input) || email.includes(input) || phone.includes(input);
        tr[i].style.display = match ? "" : "none";
      }
    }

    function toggleSidebar() {
      const sidebar = document.getElementById("sidebar");
      const overlay = document.getElementById("overlay");
      sidebar.classList.toggle("active");
      overlay.style.display = sidebar.classList.contains("active") ? "block" : "none";
    }

    function closeSidebar() {
      document.getElementById("sidebar").classList.remove("active");
      document.getElementById("overlay").style.display = "none";
    }
  </script>

</body>
</html>