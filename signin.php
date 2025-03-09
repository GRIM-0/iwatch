<?php
session_start(); // Start the session
require "config.php";

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Debugging: Check if POST data is received
  error_log("POST data: " . print_r($_POST, true)); // Log to error log for debugging

  $username = trim($_POST["username"] ?? '');
  $password = trim($_POST["password"] ?? '');

  if (empty($username) || empty($password)) {
    $error = "Please fill in all fields.";
  } else {
    // Query the database for the user
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    if ($stmt === false) {
      $error = "Database preparation failed: " . $conn->error;
    } else {
      $stmt->bind_param("s", $username);
      $stmt->execute();
      $result = $stmt->get_result();

      if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        // Verify password
        if (password_verify($password, $user["password"])) {
          // Set session variables
          $_SESSION["user_id"] = $user["id"];
          $_SESSION["username"] = $user["username"];
          $_SESSION["logged_in"] = true;
          // Redirect to the homepage
          header("Location: index.php");
          exit;
        } else {
          $error = "Invalid password.";
        }
      } else {
        $error = "User not found.";
      }

      $stmt->close();
    }
  }
  $conn->close();
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
    .navbar { background-color: #1a1a1a; }
    .navbar-brand, .nav-link { color: #fff !important; }
    .navbar-brand:hover, .nav-link:hover { color: #ff0000 !important; }
    .nav-link.active { color: #ff0000 !important; font-weight: bold; background-color: #2a2a2a; border-radius: 5px; }
    .btn-red { background-color: #ff0000; color: #fff; }
    .btn-red:hover { background-color: #cc0000; }
    .sign-box { background-color: #2a2a2a; padding: 20px; border-radius: 10px; max-width: 400px; margin: 20px auto; }
    .form-control { background-color: #3a3a3a; border: none; color: #fff; }
    .form-control:focus { background-color: #3a3a3a; color: #fff; }
    .error { color: #ff0000; margin-top: 10px; }
    .username-nav {
      color: #fff;
      font-weight: bold;
      text-decoration: none;
      padding: 0.5rem 1rem;
      background: none; /* No background */
    }
    .username-nav:hover {
      color: #ff0000;
    }
    .dropdown-menu {
      background-color: #2a2a2a;
      border: none;
    }
    .dropdown-item {
      color: #fff;
    }
    .dropdown-item:hover {
      background-color: #3a3a3a;
      color: #ff0000;
    }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg">
    <div class="container">
      <a class="navbar-brand" href="index.php">iWatch</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'movies.php' ? 'active' : ''; ?>" href="movies.php">Movies</a></li>
          <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'tv-series.php' ? 'active' : ''; ?>" href="tv-series.php">TV Series</a></li>
          <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'search.php' ? 'active' : ''; ?>" href="search.php">Search</a></li>
          <?php if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true): ?>
            <li class="nav-item dropdown">
              <a class="username-nav dropdown-toggle" href="#" id="accountDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <?php echo htmlspecialchars($_SESSION["username"]); ?>
              </a>
              <ul class="dropdown-menu" aria-labelledby="accountDropdown">
                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                <li><a class="dropdown-item" href="logout.php">Log Out</a></li>
              </ul>
            </li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link btn btn-red" href="#" data-bs-toggle="modal" data-bs-target="#signModal">Sign In</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container mt-4">
    <div class="sign-box">
      <h2 class="text-center">Sign In</h2>
      <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
      <?php endif; ?>
      <form method="POST" action="signin.php" id="signInForm">
        <div class="mb-3">
          <input type="text" class="form-control" name="username" placeholder="Username" required value="<?php echo htmlspecialchars($_POST["username"] ?? ''); ?>">
        </div>
        <div class="mb-3">
          <input type="password" class="form-control" name="password" placeholder="Password" required>
        </div>
        <button type="submit" class="btn btn-red w-100">Sign In</button>
      </form>
      <div class="mt-3 text-center">
        <a href="#" data-bs-toggle="modal" data-bs-target="#signModal">Need an account? Sign Up</a>
      </div>
    </div>

    <!-- Sign Up Modal -->
    <div class="modal fade" id="signModal" tabindex="-1" aria-labelledby="signModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content sign-box">
          <div class="modal-header">
            <h5 class="modal-title" id="signModalLabel">iWatch - Sign Up</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form method="POST" action="signup.php" id="signUpForm">
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
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>