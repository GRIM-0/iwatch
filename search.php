<?php
require "config.php";
$query = isset($_GET["q"]) ? trim($_GET["q"]) : "";
$results = [];

if ($query) {
  $url = "https://api.themoviedb.org/3/search/multi?api_key=$tmdb_api_key&query=" . urlencode($query);
  $response = getCachedApiResponse($url); // Use caching from config.php
  $data = json_decode($response, true);
  if (isset($data["results"])) {
    $results = $data["results"];
    // Remove duplicates by ID
    $seen = [];
    $uniqueResults = [];
    foreach ($results as $item) {
      if (!isset($seen[$item["id"]])) {
        $seen[$item["id"]] = true;
        $uniqueResults[] = $item;
      }
    }
    $results = $uniqueResults;
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>iWatch - Search</title>
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
    .scroll-container { display: flex; overflow-x: auto; padding-bottom: 10px; scrollbar-width: thin; }
    .scroll-container::-webkit-scrollbar { height: 8px; }
    .scroll-container::-webkit-scrollbar-thumb { background: #ff0000; border-radius: 10px; }
    .scroll-item { flex: 0 0 auto; margin-right: 15px; }
    .scroll-item img { width: 200px; height: 300px; object-fit: cover; border-radius: 5px; }
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
          <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'tv-series.php' ? 'active' : ''; ?>" href="tv-series.php">TV Series</a>
          </li>
          <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'search.php' ? 'active' : ''; ?>" href="search.php">Search</a></li>
          <li class="nav-item"><a class="nav-link btn btn-red" href="#" data-bs-toggle="modal" data-bs-target="#signModal">Sign In</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container mt-4">
    <form method="GET" action="search.php" class="mb-4">
      <div class="input-group">
        <input type="text" name="q" class="form-control" value="<?php echo htmlspecialchars($query); ?>" placeholder="Search movies, TV series, or people">
        <button type="submit" class="btn btn-red">Search</button>
      </div>
    </form>

    <?php if (!empty($results)): ?>
      <div class="scroll-container">
        <?php foreach ($results as $item) {
          $title = $item["title"] ?? $item["name"] ?? $item["original_name"];
          $poster = $item["poster_path"] ? "https://image.tmdb.org/t/p/w500" . $item["poster_path"] : '';
          if ($poster) {
            echo '<div class="scroll-item">';
            echo '<img src="' . $poster . '" alt="' . $title . '" loading="lazy">';
            echo '</div>';
          }
        } ?>
      </div>
    <?php endif; ?>

    <!-- Sign In/Sign Up Modal -->
    <div class="modal fade" id="signModal" tabindex="-1" aria-labelledby="signModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content sign-box">
          <div class="modal-header">
            <h5 class="modal-title" id="signModalLabel">iWatch</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form method="POST" action="signin.php" id="signInForm">
              <div class="mb-3">
                <input type="text" class="form-control" name="username" placeholder="Username" required>
              </div>
              <div class="mb-3">
                <input type="password" class="form-control" name="password" placeholder="Password" required>
              </div>
              <button type="submit" class="btn btn-red w-100">Sign In</button>
            </form>
            <form method="POST" action="signup.php" id="signUpForm" style="display:none;">
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
            <button type="button" class="btn btn-link" id="toggleSignUp">Sign Up</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.getElementById('toggleSignUp').addEventListener('click', function() {
      document.getElementById('signInForm').style.display = 'none';
      document.getElementById('signUpForm').style.display = 'block';
    });
    document.getElementById('signModal').addEventListener('show.bs.modal', function() {
      document.getElementById('signInForm').style.display = 'block';
      document.getElementById('signUpForm').style.display = 'none';
    });
  </script>
</body>
</html>
