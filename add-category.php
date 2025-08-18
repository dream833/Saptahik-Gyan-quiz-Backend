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
  <title>Add Category</title>
  <style>
    * { box-sizing: border-box; }
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f4f4f4;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .card {
      background: white;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.1);
      width: 100%;
      max-width: 400px;
    }
    .card h2 {
      margin-bottom: 1rem;
      text-align: center;
      color: #333;
    }
    .form-group {
      margin-bottom: 1rem;
    }
    label {
      display: block;
      margin-bottom: 0.5rem;
      color: #555;
    }
    input[type="text"], input[type="file"] {
      width: 100%;
      padding: 0.7rem;
      border: 1px solid #ccc;
      border-radius: 6px;
    }
    button {
      width: 100%;
      padding: 0.75rem;
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 1rem;
    }
    button:hover {
      background-color: #0056b3;
    }
    .success-msg {
      margin-top: 1rem;
      text-align: center;
      display: none;
    }
  </style>
</head>
<body>
  <div class="card">
    <h2>Add Category</h2>
    <form id="categoryForm" enctype="multipart/form-data">
      <div class="form-group">
        <label for="categoryName">Category Name</label>
        <input type="text" id="categoryName" name="categoryName" required />
      </div>
      <div class="form-group">
        <label for="categoryImage">Category Image</label>
        <input type="file" id="categoryImage" name="categoryImage" accept="image/*" />
      </div>
      <button type="submit">Add</button>
      <div class="success-msg" id="successMsg"></div>
    </form>
  </div>

<script>
  const form = document.getElementById('categoryForm');
  const msg = document.getElementById('successMsg');

  form.addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(form);

    try {
      const response = await fetch('api/createcat.php', {
        method: 'POST',
        body: formData
      });

      const result = await response.json();

      if (response.ok && result.status) {
        msg.textContent = "Category added successfully!";
        msg.style.color = "green";
        msg.style.display = "block";
        setTimeout(() => {
          window.location.href = "manage-category.php";
        }, 800);
      } else {
        msg.textContent = result.message || "Failed to add category.";
        msg.style.color = "red";
        msg.style.display = "block";
      }
    } catch (error) {
      msg.textContent = "Error connecting to server.";
      msg.style.color = "red";
      msg.style.display = "block";
    }
  });
</script>
</body>
</html>