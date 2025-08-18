





<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="login-container">
    <h2>Admin Login</h2>
    <form action="#" method="post"id="login">
      <div class="input-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required placeholder="Enter email">
      </div>
      <div class="input-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required placeholder="Enter password">
      </div>
      <button type="submit">Login</button>
    </form>


  <script>
  const form = document.getElementById("login");

  form.addEventListener("submit", async function (event) {
    event.preventDefault();

    const email = form.email.value;
    const password = document.getElementById("password").value;

    const response = await fetch("./api/login.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({ email, password })
    });

    if (response.ok) {
      const data = await response.json();
      if (data.status) {
        window.location.href = "dashboard.php"; 
      } else {
        alert(data.message);
      }
    } else {
      alert("Login failed. Please try again.");
    }

  }); 
</script>
  </div>
</body>
</html>