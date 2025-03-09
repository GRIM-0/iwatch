<?php
session_start(); // Start the session
require "config.php";

$signInError = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["signInSubmit"])) {
  $username = trim($_POST["username"] ?? '');
  $password = trim($_POST["password"] ?? '');

  if (empty($username) || empty($password)) {
    $signInError = "Please fill in all fields.";
  } else {
    error_log("Sign-in attempt for username: '$username'");
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE LOWER(username) = LOWER(?)");
    if ($stmt === false) {
      $signInError = "Database preparation failed: " . $conn->error;
      error_log("Database error: " . $conn->error);
    } else {
      $stmt->bind_param("s", $username);
      $stmt->execute();
      $result = $stmt->get_result();

      error_log("Number of rows found: " . $result->num_rows);

      if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        error_log("Fetched user: " . print_r($user, true));
        if (password_verify($password, $user["password"])) {
          $_SESSION["user_id"] = $user["id"];
          $_SESSION["username"] = $user["username"];
          $_SESSION["logged_in"] = true;
          error_log("Sign-in successful for user: " . $user["username"]);
          header("Location: tv-series.php");
          exit;
        } else {
          $signInError = "Invalid password.";
          error_log("Invalid password for username: '$username'");
        }
      } else {
        $signInError = "User not found.";
        error_log("No user found for username: '$username'");
      }
      $stmt->close();
    }
  }
  $conn->close();
}

$url = "https://api.themoviedb.org/3/tv/popular?api_key=$tmdb_api_key";
$response = getCachedApiResponse($url);
$series = json_decode($response, true)["results"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>iWatch - TV Series</title>
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
    .section-title { margin-bottom: 10px; font-size: 1.5rem; }
    .poster-container {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
    }
    .poster-item {
      flex: 0 0 auto;
    }
    .poster-item img {
      width: 200px;
      height: 300px;
      object-fit: cover;
      border-radius: 5px;
    }
    .username-nav {
      color: #fff;
      font-weight: bold;
      text-decoration: none;
      padding: 0.5rem 1rem;
      background: none;
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
    .error { color: #ff0000; margin-top: 10px; }
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
            <li class="nav-item"><a class="nav-link btn btn-red" href="#" data-bs-toggle="modal" data-bs-target="#signInModal">Sign In</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container mt-4">
    <h2 class="section-title">Popular TV Series</h2>
    <div class="poster-container">
      <?php foreach ($series as $show) {
        echo '<div class="poster-item">';
        echo '<img src="https://image.tmdb.org/t/p/w500' . $show["poster_path"] . '" alt="' . htmlspecialchars($show["name"]) . '" loading="lazy">';
        echo '</div>';
      } ?>
    </div>

    <!-- Sign In Modal -->
    <div class="modal fade" id="signInModal" tabindex="-1" aria-labelledby="signInModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content sign-box">
          <div class="modal-header">
            <h5 class="modal-title" id="signInModalLabel">iWatch - Sign In</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form method="POST" action="" id="signInForm">
              <?php if ($signInError): ?>
                <div class="error"><?php echo $signInError; ?></div>
              <?php endif; ?>
              <div class="mb-3">
                <input type="text" class="form-control" name="username" placeholder="Username" required value="<?php echo htmlspecialchars($_POST["username"] ?? ''); ?>">
              </div>
              <div class="mb-3">
                <input type="password" class="form-control" name="password" placeholder="Password" required>
              </div>
              <button type="submit" name="signInSubmit" class="btn btn-red w-100">Sign In</button>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <a href="#" data-bs-toggle="modal" data-bs-target="#signUpModal" data-bs-dismiss="modal">Need an account? Sign Up</a>
          </div>
        </div>
      </div>
    </div>

    <!-- Sign Up Modal -->
    <div class="modal fade" id="signUpModal" tabindex="-1" aria-labelledby="signUpModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content sign-box">
          <div class="modal-header">
            <h5 class="modal-title" id="signUpModalLabel">iWatch - Sign Up</h5>
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
            <a href="#" data-bs-toggle="modal" data-bs-target="#signInModal" data-bs-dismiss="modal">Already have an account? Sign In</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>