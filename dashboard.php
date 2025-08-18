<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: adminlogin.php");
    exit;
}

// ✅ These should be outside the `if` block:
require 'api/db.php'; 

// Get total users
$stmt = $pdo->query("SELECT COUNT(*) AS total FROM users");
$row = $stmt->fetch();
$totalUsers = $row['total'];
// Get total questions
$stmt = $pdo->query("SELECT COUNT(*) AS total FROM questions");
$row = $stmt->fetch();
$totalQuestions = $row['total'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Panel - Quiz</title>
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
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      background: #f0f2f5;
    }

    .container {
      display: flex;
      flex: 1;
      flex-direction: row;
    }

    .sidebar {
      width: 230px;
      background: #1a237e;
      color: white;
      padding: 20px;
      transition: transform 0.3s ease;
      z-index: 1000;
    }

    .sidebar h2 {
      text-align: center;
      margin-bottom: 40px;
      font-size: 22px;
      font-weight: bold;
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
      cursor: pointer;
    }

    .sidebar ul li a i {
      margin-right: 12px;
      font-size: 18px;
    }

    .sidebar ul li a:hover {
      background: rgba(255, 255, 255, 0.1);
    }

    .sidebar ul .submenu {
      margin-left: 30px;
      display: none;
    }

    .sidebar ul li.active .submenu {
      display: block;
    }

    .sidebar ul li.has-sub > a::after {
      content: "\f107";
      font-family: 'Font Awesome 6 Free';
      font-weight: 900;
      margin-left: auto;
    }

    .sidebar ul li.active > a::after {
      content: "\f106";
    }

    .sidebar ul li .submenu li a {
      font-size: 14px;
      padding-left: 35px;
      color: #cfd8dc;
    }

    .sidebar ul li .submenu li a:hover {
      background: rgba(255, 255, 255, 0.15);
    }

    .main-content {
      flex: 1;
      padding: 30px;
    }

    .topbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      padding: 10px 20px;
      background: #1a237e;
      color: white;
    }

    .topbar h1 {
      font-size: 24px;
    }

    .toggle-btn {
      font-size: 22px;
      background: #1a237e;
      color: white;
      border: none;
      padding: 10px 15px;
      cursor: pointer;
      border-radius: 4px;
      display: none;
    }

    .logout-btn {
      font-size: 16px;
      background: #e53935;
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 4px;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    .logout-btn:hover {
      background: #c62828;
    }

    .cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 40px;
    }

    .card {
      background: white;
      padding: 25px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
      transform: translateY(-6px);
      box-shadow: 0 6px 18px rgba(0,0,0,0.1);
    }

    .card h3 {
      font-size: 20px;
      margin-bottom: 12px;
      color: #1a237e;
    }

    .card p {
      font-size: 17px;
      color: #444;
    }

    #overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      height: 100%;
      width: 100%;
      background: rgba(0,0,0,0.4);
      z-index: 900;
    }

    @media (max-width: 768px) {
      .toggle-btn {
        display: inline-block;
      }

      .container {
        flex-direction: column;
      }

      .sidebar {
        position: fixed;
        left: 0;
        top: 0;
        height: 100%;
        transform: translateX(-100%);
      }

      .sidebar.show {
        transform: translateX(0);
      }

      .main-content {
        padding: 20px;
      }

      .topbar h1 {
        font-size: 20px;
      }

      .cards {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>

  <div class="topbar">
    <button class="toggle-btn" onclick="toggleSidebar()">☰</button>
    <h1>Dashboard</h1>
    <button class="logout-btn" onclick="logout()">Logout</button>
  </div>

  <div id="overlay" onclick="closeSidebar()"></div>

  <div class="container">
    <div class="sidebar" id="sidebar">
      <h2>Admin Panel</h2>
      <ul>
        <li><a href="dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a></li>

        <li class="has-sub">
          <a href="javascript:void(0)" onclick="toggleSubmenu(this)">
            <i class="fas fa-question-circle"></i> Quiz Management
          </a>
          <ul class="submenu">
            <li><a href="add-quiz.php"><i class="fas fa-plus-circle"></i> Add Quiz</a></li>
            <li><a href="manage-quiz.php"><i class="fas fa-edit"></i> Manage Quiz</a></li>
          </ul>
        </li>

        <li class="has-sub">
          <a href="javascript:void(0)" onclick="toggleSubmenu(this)">
            <i class="fas fa-database"></i> Question Bank
          </a>
          <ul class="submenu">
            <li><a href="upload-questions.php"><i class="fas fa-upload"></i> Upload Questions</a></li>
            <li><a href="manage-questions.php"><i class="fas fa-tasks"></i> Manage Questions</a></li>
          </ul>
        </li>

        <li><a href="adminusers.php"><i class="fas fa-users"></i> Users</a></li>

        <li class="has-sub">
          <a href="javascript:void(0)" onclick="toggleSubmenu(this)">
            <i class="fas fa-layer-group"></i> Category Management
          </a>
          <ul class="submenu">
            <li><a href="add-category.php"><i class="fas fa-plus-circle"></i> Add Category</a></li>
            <li><a href="manage-category.php"><i class="fas fa-cog"></i> Manage Category</a></li>
          </ul>
        </li>

        <li class="has-sub">
          <a href="javascript:void(0)" onclick="toggleSubmenu(this)">
            <i class="fas fa-sitemap"></i> Sub-Category Management
          </a>
          <ul class="submenu">
            <li><a href="add-subcategory.php"><i class="fas fa-plus-square"></i> Add Sub-Category</a></li>
            <li><a href="manage-subcategory.php"><i class="fas fa-cogs"></i> Manage Sub-Category</a></li>
          </ul>
        </li>

        <li><a href="leaderboard.php"><i class="fas fa-trophy"></i> Leaderboard</a></li>
      </ul>
    </div>

    <div class="main-content">
      <div class="cards">
        <div class="card">
          <h3><i class="fas fa-file-alt"></i> Total Quizzes</h3>
          <p>120</p>
        </div>
        <div class="card">
          <h3><a href="adminusers.php" style="text-decoration: none; color: #1a237e;"><i class="fas fa-user-friends"></i> Total Users</a></h3>
      <p><?= $totalUsers ?></p>
        </div>
        <div class="card">
          <h3><i class="fas fa-database"></i> Questions Uploaded</h3>
          <p><?= $totalQuestions ?></p>
        </div>
        <div class="card">
          <h3><i class="fas fa-star"></i> Top Scorer</h3>
          <p>Sbs Sen (98%)</p>
        </div>
      </div>
    </div>
  </div>

  <script>
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      const overlay = document.getElementById('overlay');
      sidebar.classList.toggle('show');
      overlay.style.display = sidebar.classList.contains('show') ? 'block' : 'none';
    }

    function closeSidebar() {
      document.getElementById('sidebar').classList.remove('show');
      document.getElementById('overlay').style.display = 'none';
    }

    function toggleSubmenu(element) {
      const li = element.parentElement;
      li.classList.toggle('active');
    }

    function logout() {
      fetch("logout.php")
        .then(() => {
          alert("You have been logged out.");
          window.location.href = "adminlogin.php";
        });
    }
  </script>

</body>
</html>