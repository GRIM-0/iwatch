<?php
require "config.php";
$url = "https://api.themoviedb.org/3/tv/popular?api_key=$tmdb_api_key";
$response = file_get_contents($url);
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
    .nav-link.active { color: #ff0000 !important; font-weight: bold; background-color: #2a2a2a; border-radius: 5px; }
    .card { background-color: #2a2a2a; border: none; }
    .card-img-top { height: 300px; object-fit: cover; }
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
    <h2>Popular TV Series</h2>
    <div class="row">
      <?php foreach ($series as $show) {
        echo '<div class="col-md-3 mb-4">';
        echo '<div class="card">';
        echo '<img src="https://image.tmdb.org/t/p/w500' . $show["poster_path"] . '" class="card-img-top" alt="' . $show["name"] . '">';
        echo '<div class="card-body">';
        echo '<h5 class="card-title">' . $show["name"] . '</h5>';
        echo '</div></div></div>';
      } ?>
    </div>
  </div>
  <!-- Sign In/Sign Up Modal (same as index.php) -->
  <?php include 'index.php'; ?>
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
