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
  <title>Manage Categories</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f0f2f5;
      padding: 2rem;
    }

    .container {
      max-width: 1000px;
      margin: auto;
      background-color: white;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    }

    h2 {
      text-align: center;
      margin-bottom: 1.5rem;
      color: #333;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
      word-wrap: break-word;
    }

    th, td {
      padding: 1rem;
      text-align: left;
      border-bottom: 1px solid #ddd;
      vertical-align: middle;
    }

    th {
      background-color: #f5f5f5;
    }

    img.cat-thumb {
      width: 60px;
      height: 60px;
      object-fit: cover;
      border-radius: 8px;
      cursor: pointer;
    }

    .actions {
      display: flex;
      gap: 0.5rem;
    }

    .btn {
      padding: 0.4rem 0.8rem;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 0.9rem;
    }

    .btn-edit {
      background-color: #ffc107;
      color: white;
    }

    .btn-delete {
      background-color: #dc3545;
      color: white;
    }

    .btn-edit:hover {
      background-color: #e0a800;
    }

    .btn-delete:hover {
      background-color: #c82333;
    }

    /* Modal */
    .modal {
      display: none;
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.4);
      justify-content: center;
      align-items: center;
      z-index: 9999;
    }

    .modal-content {
      background: white;
      padding: 2rem;
      border-radius: 10px;
      width: 100%;
      max-width: 400px;
      position: relative;
      box-shadow: 0 8px 16px rgba(0,0,0,0.2);
    }

    .modal-content h3 {
      margin-top: 0;
    }

    .modal-content input[type="text"] {
      width: 100%;
      padding: 0.6rem;
      margin-top: 0.5rem;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    .modal-actions {
      margin-top: 1rem;
      display: flex;
      justify-content: flex-end;
      gap: 1rem;
    }

    .close-btn {
      position: absolute;
      top: 10px;
      right: 15px;
      font-size: 1.5rem;
      cursor: pointer;
      color: #888;
    }

    .close-btn:hover {
      color: #000;
    }

    /* Zoom Image Modal */
    .image-zoom-modal {
      display: none;
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.8);
      justify-content: center;
      align-items: center;
      z-index: 9999;
    }

    .image-zoom-modal img {
      max-width: 90%;
      max-height: 90%;
      border-radius: 10px;
      box-shadow: 0 0 20px rgba(255,255,255,0.3);
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
      body { padding: 1rem; }
      .container { padding: 1rem; }

      table, thead, tbody, th, td, tr {
        display: block;
        width: 100%;
      }

      thead {
        display: none;
      }

      tr {
        margin-bottom: 1rem;
        border: 1px solid #ddd;
        border-radius: 8px;
        background-color: #fff;
        padding: 1rem;
      }

      td {
        text-align: left;
        padding: 0.5rem 0;
        position: relative;
        font-size: 0.95rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
      }

      td::before {
        content: attr(data-label);
        font-weight: bold;
        color: #555;
        flex: 1;
      }

      img.cat-thumb {
        width: 50px;
        height: 50px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Manage Categories</h2>
    <table id="categoryTable">
      <thead>
        <tr>
          <th>ID</th>
          <th>Category</th>
          <th>Image</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="categoryBody">
        <!-- Filled by JS -->
      </tbody>
    </table>
  </div>

  <!-- Edit Modal -->
  <div class="modal" id="editModal">
    <div class="modal-content">
      <span class="close-btn" id="closeModal">&times;</span>
      <h3>Edit Category</h3>
      <input type="hidden" id="editId">
      <label for="editName">Category Name</label>
      <input type="text" id="editName">
      <div class="modal-actions">
        <button class="btn btn-edit" id="saveEdit">Save</button>
      </div>
    </div>
  </div>

  <!-- Image Zoom Modal -->
  <div class="image-zoom-modal" id="imageZoomModal">
    <img src="" id="zoomedImage" alt="Preview" />
  </div>

<script>
  const categoryBody = document.getElementById("categoryBody");
  const editModal = document.getElementById("editModal");
  const closeModal = document.getElementById("closeModal");
  const saveEditBtn = document.getElementById("saveEdit");
  let currentRow = null;

  async function loadCategories() {
    try {
      const response = await fetch("./api/getcategory.php");
      const result = await response.json();

      if (result.status === "success") {
        categoryBody.innerHTML = "";

        result.data.forEach(category => {
          const row = document.createElement("tr");
          row.dataset.id = category.id;
          row.dataset.name = category.name;

          row.innerHTML = `
            <td data-label="ID">${category.id}</td>
            <td data-label="Category Name" class="cat-name">${category.name}</td>
            <td data-label="Image">
              ${
                category.image
                  ? `<img src="api/${category.image}" alt="${category.name}" class="cat-thumb" onclick="zoomImage('api/${category.image}')">`
                  : `<span style="color:#999;">No Image</span>`
              }
            </td>
            <td data-label="Actions" class="actions">
              <button class="btn btn-edit">Edit</button>
              <button class="btn btn-delete">Delete</button>
            </td>
          `;
          categoryBody.appendChild(row);
        });

        bindEditButtons();
        bindDeleteButtons();
      }
    } catch (error) {
      alert("Failed to load categories.");
    }
  }

  function bindEditButtons() {
    document.querySelectorAll(".btn-edit").forEach(button => {
      button.addEventListener("click", function () {
        currentRow = this.closest("tr");
        const id = currentRow.dataset.id;
        const name = currentRow.dataset.name;

        document.getElementById("editId").value = id;
        document.getElementById("editName").value = name;
        editModal.style.display = "flex";
      });
    });
  }

  function bindDeleteButtons() {
    document.querySelectorAll(".btn-delete").forEach(button => {
      button.addEventListener("click", async function () {
        const row = this.closest("tr");
        const id = row.dataset.id;
        const name = row.dataset.name;

        if (!confirm(`Delete category "${name}"?`)) return;

        try {
          const response = await fetch("./api/deletecategory.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id })
          });

          const result = await response.json();

          if (result.status === "success") {
            row.remove();
            showToast("Category deleted successfully.");
          } else {
            alert("Delete failed: " + result.message);
          }
        } catch (error) {
          alert("Error deleting.");
        }
      });
    });
  }

  saveEditBtn.addEventListener("click", async () => {
    const id = document.getElementById("editId").value;
    const newName = document.getElementById("editName").value.trim();

    if (!newName) {
      alert("Category name cannot be empty.");
      return;
    }

    try {
      const response = await fetch("./api/updatecategory.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id, name: newName }),
      });

      const result = await response.json();

      if (result.status === "success") {
        const updatedRow = document.querySelector(`tr[data-id="${id}"]`);
        updatedRow.querySelector(".cat-name").textContent = newName;
        updatedRow.dataset.name = newName;

        editModal.style.display = "none";
        currentRow = null;
        showToast("Category updated.");
      } else {
        alert("Update failed: " + result.message);
      }
    } catch (err) {
      alert("Something went wrong.");
    }
  });

  closeModal.addEventListener("click", () => {
    editModal.style.display = "none";
  });

  window.onclick = function (event) {
    if (event.target == editModal) editModal.style.display = "none";
    if (event.target == imageZoomModal) imageZoomModal.style.display = "none";
  }

  function showToast(message) {
    const toast = document.createElement("div");
    toast.textContent = message;
    toast.style.position = "fixed";
    toast.style.bottom = "20px";
    toast.style.left = "50%";
    toast.style.transform = "translateX(-50%)";
    toast.style.backgroundColor = "#333";
    toast.style.color = "#fff";
    toast.style.padding = "12px 20px";
    toast.style.borderRadius = "6px";
    toast.style.boxShadow = "0 4px 10px rgba(0,0,0,0.2)";
    toast.style.zIndex = "9999";
    toast.style.opacity = "0";
    toast.style.transition = "opacity 0.3s ease-in-out";

    document.body.appendChild(toast);
    setTimeout(() => toast.style.opacity = "1", 100);
    setTimeout(() => {
      toast.style.opacity = "0";
      setTimeout(() => toast.remove(), 300);
    }, 2500);
  }

  // Image zoom modal
  const imageZoomModal = document.getElementById("imageZoomModal");
  const zoomedImage = document.getElementById("zoomedImage");

  function zoomImage(src) {
    zoomedImage.src = src;
    imageZoomModal.style.display = "flex";
  }

  window.addEventListener("DOMContentLoaded", loadCategories);
</script>
</body>
</html>