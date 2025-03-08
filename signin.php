<?php
session_start();
require "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = mysqli_real_escape_string($conn, $_POST["username"]);
  $password = $_POST["password"]; // Plain text from form

  $query = "SELECT * FROM users WHERE username = ?";
  $stmt = mysqli_prepare($conn, $query);
  mysqli_stmt_bind_param($stmt, "s", $username);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);

  if ($row = mysqli_fetch_assoc($result)) {
    if (password_verify($password, $row["password"])) {
      $_SESSION["username"] = $username;
      header("Location: index.php");
      exit();
    } else {
      $error = "Invalid password";
    }
  } else {
    $error = "Username not found";
  }
  mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>iWatch - Sign In</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #1a1a1a; color: #fff; }
    .container { max-width: 400px; margin-top: 50px; }
    .form-control { background-color: #3a3a3a; border: none; color: #fff; }
    .form-control:focus { background-color: #3a3a3a; color: #fff; }
    .btn-red { background-color: #ff0000; color: #fff; }
    .btn-red:hover { background-color: #cc0000; }
    .alert { margin-top: 10px; }
  </style>
</head>
<body>
  <div class="container">
    <h2>Sign In</h2>
    <?php if (isset($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>
    <form method="POST" action="">
      <div class="mb-3">
        <input type="text" class="form-control" name="username" placeholder="Username" required>
      </div>
      <div class="mb-3">
        <input type="password" class="form-control" name="password" placeholder="Password" required>
      </div>
      <button type="submit" class="btn btn-red w-100">Sign In</button>
    </form>
    <p class="mt-3">Don't have an account? <a href="signup.php">Sign Up</a></p>
  </div>
</body>
</html>
