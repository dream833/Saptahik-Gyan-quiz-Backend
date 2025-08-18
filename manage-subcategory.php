<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require 'api/db.php'; 
include 'quizauth.php';

// Fetch all categories
$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

// For each category, fetch its subcategories
$categoryData = [];
foreach ($categories as $cat) {
    $stmt = $pdo->prepare("SELECT * FROM subcategories WHERE category_id = ?");
    $stmt->execute([$cat['id']]);
    $subs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $categoryData[] = [
        'id' => $cat['id'],
        'name' => $cat['name'],
        'subcategories' => $subs
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Sub-Categories</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f4f6f9;
      margin: 0;
      padding: 20px;
    }

    .container {
      max-width: 900px;
      margin: auto;
      padding: 10px;
    }

    h1 {
      text-align: center;
      margin-bottom: 30px;
      color: #1a237e;
    }

    .category-card {
      background: white;
      padding: 20px;
      margin-bottom: 25px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.07);
      transition: 0.3s ease;
    }

    .category-title {
      font-size: 20px;
      font-weight: bold;
      color: #1a237e;
      margin-bottom: 10px;
      display: flex;
      align-items: center;
    }

    .category-title i {
      margin-right: 10px;
    }

    .sub-list {
      list-style: none;
      padding-left: 0;
      margin-top: 10px;
    }

    .sub-list li {
      padding: 10px;
      margin: 8px 0;
      background: #e8f0fe;
      border-left: 4px solid #1a237e;
      border-radius: 4px;
      color: #333;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
    }

    .sub-list img {
      max-height: 40px;
      border-radius: 4px;
      cursor: pointer;
      transition: transform 0.2s;
    }

    .sub-list img:hover {
      transform: scale(1.8);
      z-index: 10;
    }

    .delete-btn {
      background: #e53935;
      color: white;
      border: none;
      padding: 6px 10px;
      border-radius: 4px;
      cursor: pointer;
      font-size: 14px;
      margin-left: 10px;
    }

    .delete-btn:hover {
      background: #c62828;
    }

    @media (max-width: 600px) {
      .sub-list li {
        flex-direction: column;
        align-items: flex-start;
      }
      .delete-btn {
        margin-top: 10px;
        margin-left: 0;
      }
    }
  </style>
</head>
<body>

  <div class="container">
    <h1><i class="fas fa-sitemap"></i> Manage Sub-Categories</h1>

    <?php foreach ($categoryData as $category): ?>
      <div class="category-card">
        <div class="category-title"><i class="fas fa-folder"></i> <?= htmlspecialchars($category['name']) ?></div>
        <ul class="sub-list">
          <?php if (count($category['subcategories']) > 0): ?>
            <?php foreach ($category['subcategories'] as $sub): ?>
              <li>
                <div>
                  <strong><?= htmlspecialchars($sub['name']) ?></strong>
                  <?php if (!empty($sub['image'])): ?>
                    <br>
                    <img src="<?= htmlspecialchars($sub['image']) ?>" alt="<?= htmlspecialchars($sub['name']) ?>">
                  <?php endif; ?>
                </div>
                <form method="POST" action="delete-subcategory.php" style="margin: 0;">
                  <input type="hidden" name="id" value="<?= $sub['id'] ?>">
                 <button class="delete-btn" onclick="deleteSubcategory(<?= $sub['id'] ?>, '<?= htmlspecialchars($sub['name'], ENT_QUOTES) ?>')">Delete</button>
                </form>
              </li>
            <?php endforeach; ?>
          <?php else: ?>
            <li><em>No sub-categories found.</em></li>
          <?php endif; ?>
        </ul>
      </div>
    <?php endforeach; ?>

  </div>

  <script>
  function deleteSubcategory(id, name) {
    if (!confirm(`Are you sure you want to delete "${name}"?`)) return;

    fetch('api/delete-subcategory.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ id: id })
    })
    .then(res => res.json())
    .then(response => {
      if (response.status === 'success') {
        alert(`"${name}" deleted successfully`);
        location.reload();
      } else {
        alert("Failed to delete: " + (response.message || 'Unknown error'));
      }
    })
    .catch(err => {
      console.error(err);
      alert("Something went wrong.");
    });
  }
</script>
</body>
</html>