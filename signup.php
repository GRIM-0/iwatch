<?php
session_start();
require "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = mysqli_real_escape_string($conn, $_POST["username"]);
  $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
  $email = mysqli_real_escape_string($conn, $_POST["email"]);

  $check_query = "SELECT * FROM users WHERE username = ?";
  $check_stmt = mysqli_prepare($conn, $check_query);
  if ($check_stmt === false) {
    die("Prepare failed: " . mysqli_error($conn));
  }
  mysqli_stmt_bind_param($check_stmt, "s", $username);
  mysqli_stmt_execute($check_stmt);
  $check_result = mysqli_stmt_get_result($check_stmt);

  if (mysqli_num_rows($check_result) == 0) {
    $query = "INSERT INTO users (username, password, email) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt === false) {
      die("Prepare failed: " . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmt, "sss", $username, $password, $email);
    if (mysqli_stmt_execute($stmt)) {
      $_SESSION["username"] = $username;
      header("Location: index.php");
      exit();
    } else {
      $error = "Error creating account: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
  } else {
    $error = "Username already exists";
  }
  mysqli_stmt_close($check_stmt);
} else {
  // If not a POST request, initialize $error as null to avoid undefined variable
  $error = null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>iWatch - Sign Up</title>
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
    <h2>Sign Up</h2>
    <?php if (isset($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>
    <form method="POST" action="">
      <div class="mb-3">
        <input type="text" class="form-control" name="username" placeholder="Username" required>
      </div>
      <div class="mb-3">
        <input type="password" class="form-control" name="password" placeholder="Password" required>
      </div>
      <div class="mb-3">
        <input type="email" class="form-control" name="email" placeholder="Email" required>
      </div>
      <button type="submit" class="btn btn-red w-100">Sign Up</button>
    </form>
    <p class="mt-3">Already have an account? <a href="signin.php">Sign In</a></p>
  </div>
</body>
</html>
