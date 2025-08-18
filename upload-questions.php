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
  <title>Add Question</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 20px;
      background: #f2f2f2;
    }

    .container {
      max-width: 700px;
      background: white;
      padding: 20px;
      margin: auto;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    h2 {
      text-align: center;
      margin-bottom: 5px;
    }

    .date {
      text-align: center;
      font-size: 14px;
      margin-bottom: 20px;
      color: #555;
    }

    label {
      display: block;
      margin: 10px 0 5px;
      font-weight: bold;
    }

    select, input[type="text"], textarea {
      width: 100%;
      padding: 10px;
      border-radius: 6px;
      border: 1px solid #ccc;
      margin-bottom: 15px;
    }

    .submit-btn {
      background: #007bff;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      width: 100%;
      font-size: 16px;
    }

    .submit-btn:hover {
      background: #0056b3;
    }
  </style>
</head>
<body>

<div class="container">
  <h2>Add Question</h2>
  <div class="date" id="currentDate"></div>
  
  <form id="questionForm">
    <label for="category">Category</label>
    <select id="category" required>
      <option value="">-- Select Category  --</option>
    </select>

    <label for="subCategory">Sub-Category</label>
    <select id="subCategory" required>
      <option value="">-- Sub-Category boro --</option>
    </select>

    <label for="question">Question</label>
    <textarea id="question" rows="3" required placeholder="Write your question here..."></textarea>

    <label for="answer">Answer</label>
    <textarea id="answer" rows="2" required placeholder="Write the answer here..."></textarea>

    <button type="submit" class="submit-btn">Submit</button>
  </form>
</div>

<script>
  // 🔸 Show today's date
  const today = new Date();
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  document.getElementById('currentDate').textContent = "Date: " + today.toLocaleDateString('en-IN', options);

  // 🔸 Load categories on page load
  window.addEventListener('DOMContentLoaded', () => {
    fetch('api/getcategory.php')
      .then(res => res.json())
      .then(res => {
        const categorySelect = document.getElementById('category');
        if (res.status === 'success' && Array.isArray(res.data)) {
          res.data.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat.id;
            option.textContent = cat.name;
            categorySelect.appendChild(option);
          });
        } else {
          alert('Category load failed.');
        }
      })
      .catch(err => {
        console.error('Category load fail:', err);
        alert('Category load error');
      });
  });

  document.getElementById('category').addEventListener('change', function () {
    const categoryId = this.value;
    const subCategorySelect = document.getElementById('subCategory');
    subCategorySelect.innerHTML = '<option value="">-- Select Sub-Category --</option>';

    if (!categoryId) return;

    console.log("Selected Category ID:", categoryId);

    fetch('api/getsubcategory.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ category_id: categoryId })
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success' && Array.isArray(data.data)) {
        data.data.forEach(sub => {
          const option = document.createElement('option');
          option.value = sub.id;
          option.textContent = sub.name;
          subCategorySelect.appendChild(option);
        });
      } else {
        alert('Sub-category load fail: ' + (data.message || 'Unknown error'));
      }
    })
    .catch(err => {
      console.error('Sub-category load error:', err);
      alert('Sub-category error');
    });
  });

 document.getElementById('questionForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const cat = document.getElementById('category').value;
    const sub = document.getElementById('subCategory').value;
    const ques = document.getElementById('question').value.trim();
    const ans = document.getElementById('answer').value.trim();

    if (!cat || !sub || !ques || !ans) {
        alert('Shob field fill koro!');
        return;
    }

    fetch('api/addquestions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            category_id: cat,
            sub_category_id: sub,
            question: ques,
            answer: ans
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            document.getElementById('question').value = '';
            document.getElementById('answer').value = '';
        } else {
            alert('Submit failed: ' + (data.message || 'Unknown error'));
            if (data.debug) console.error('Server debug:', data.debug);
        }
    })
    .catch(err => {
        console.error('Submit error:', err);
        alert('Something went wrong while submitting.');
    });
});

  // document.getElementById('questionForm').addEventListener('submit', function (e) {
  //   e.preventDefault();

  //   document.getElementById('question').value = '';
  //   document.getElementById('answer').value = '';
  // });
</script>

</body>
</html>