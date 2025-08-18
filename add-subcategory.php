<?php
require 'api/db.php';
require 'quizauth.php';

// Fetch all categories
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Sub-Category</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f2f4f8;
      padding: 20px;
      margin: 0;
    }
    .form-container {
      background: #fff;
      padding: 25px;
      max-width: 500px;
      margin: auto;
      border-radius: 10px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #1a237e;
    }
    label {
      font-weight: 500;
      display: block;
      margin-top: 15px;
      color: #333;
    }
    select, input[type="text"], input[type="file"] {
      width: 100%;
      padding: 10px;
      font-size: 16px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 6px;
      outline: none;
    }
    button {
      width: 100%;
      margin-top: 20px;
      padding: 12px;
      font-size: 16px;
      background: #1a237e;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }
    button:hover {
      background: #0d165c;
    }
    #message {
      margin-top: 15px;
      font-weight: 500;
    }
    .existing-subs ul {
      list-style: none;
      padding-left: 0;
    }
    .existing-subs li {
      padding: 5px 0;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .sub-thumb {
      width: 40px;
      height: 40px;
      object-fit: cover;
      border-radius: 4px;
      border: 1px solid #ccc;
      cursor: pointer;
      transition: 0.2s;
    }
    .sub-thumb:hover {
      transform: scale(1.05);
    }

    #zoomModal {
      display: none;
      position: fixed;
      z-index: 9999;
      left: 0;
      top: 0;
      width: 100vw;
      height: 100vh;
      background: rgba(0,0,0,0.8);
      justify-content: center;
      align-items: center;
    }
    #zoomModal img {
      max-width: 90%;
      max-height: 90%;
      border-radius: 8px;
      box-shadow: 0 0 15px #000;
    }

    @media (max-width: 480px) {
      .form-container {
        padding: 20px 15px;
        max-width: 90%;
      }
      .sub-thumb {
        width: 30px;
        height: 30px;
      }
    }
  </style>
</head>
<body>

  <div class="form-container">
    <h2><i class="fas fa-sitemap"></i> Add Sub-Category</h2>

    <form id="subCategoryForm" onsubmit="return createSubCategory(event)" enctype="multipart/form-data">
      <label for="category">Select Category</label>
      <select id="category" name="category_id" onchange="loadSubcategories()" required>
        <option value="">-- Select Category --</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
        <?php endforeach; ?>
      </select>

      <div id="subList" class="existing-subs" style="display: none;">
        <label>Existing Sub-Categories:</label>
        <ul id="subCategoryList"></ul>
      </div>

      <label for="newSub">Sub-Category Name</label>
      <input type="text" id="newSub" name="subcategory_name" placeholder="Enter sub-category name" required />

      <label for="subImage">Upload Image (optional)</label>
      <input type="file" id="subImage" name="subcategory_image" accept="image/*" />

      <button type="submit"><i class="fas fa-plus-circle"></i> Add Sub-Category</button>
      <div id="message"></div>
    </form>
  </div>

  <!-- Zoom Image Modal -->
  <div id="zoomModal" onclick="this.style.display='none'">
    <img id="zoomedImg" src="" alt="Zoomed Image" />
  </div>

<script>
  function loadSubcategories() {
    const categoryId = document.getElementById("category").value;
    const ul = document.getElementById("subCategoryList");
    const subList = document.getElementById("subList");

    ul.innerHTML = "";
    if (!categoryId) {
      subList.style.display = "none";
      return;
    }

    fetch('api/getsubcategory.php?category_id=' + categoryId)
      .then(res => res.json())
      .then(response => {
        if (response.status === 'success' && response.data.length > 0) {
          response.data.forEach(sub => {
            const li = document.createElement("li");
            if (sub.image) {
              li.innerHTML = `<img src="${sub.image}" class="sub-thumb" alt="${sub.name}" onclick="zoomImage(event, '${sub.image}')"> ${sub.name}`;
            } else {
              li.textContent = sub.name;
            }
            ul.appendChild(li);
          });
          subList.style.display = "block";
        } else {
          subList.style.display = "none";
        }
      })
      .catch(() => {
        subList.style.display = "none";
      });
  }

  function createSubCategory(e) {
    e.preventDefault();

    const categoryId = document.getElementById("category").value;
    const newSub = document.getElementById("newSub").value.trim();
    const imageInput = document.getElementById("subImage");
    const messageBox = document.getElementById("message");

    messageBox.textContent = "";
    if (!categoryId || !newSub) {
      messageBox.style.color = "red";
      messageBox.textContent = "Please select category and enter name.";
      return false;
    }

    const formData = new FormData();
    formData.append("category_id", categoryId);
    formData.append("subcategory_name", newSub);
    if (imageInput.files.length > 0) {
      formData.append("subcategory_image", imageInput.files[0]);
    }

    fetch("api/createsubcategory.php", {
      method: "POST",
      body: formData
    })
    .then(res => res.json())
    .then(response => {
      if (response.status === "success") {
        messageBox.style.color = "green";
        messageBox.textContent = "Sub-category added!";
        document.getElementById("newSub").value = "";
        imageInput.value = "";
        loadSubcategories();
      } else {
        messageBox.style.color = "red";
        messageBox.textContent = response.message;
      }
    })
    .catch(err => {
      console.error(err);
      messageBox.style.color = "red";
      messageBox.textContent = "Something went wrong!";
    });

    return false;
  }

  function zoomImage(event, src) {
    event.stopPropagation();
    const modal = document.getElementById('zoomModal');
    const zoomed = document.getElementById('zoomedImg');
    zoomed.src = src;
    modal.style.display = 'flex';
  }
</script>
</body>
</html>