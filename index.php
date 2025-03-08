<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>iWatch</title>
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
    <h2 class="section-title">POPULAR MOVIES</h2>
    <div class="scroll-container">
      <?php
      require "config.php";
     // Popular Movies
      $url = "https://api.themoviedb.org/3/movie/popular?api_key=$tmdb_api_key";
      $response = getCachedApiResponse($url);
      $movies = json_decode($response, true)["results"];
      foreach ($movies as $movie) {
        echo '<div class="scroll-item">';
        echo '<img src="https://image.tmdb.org/t/p/w500' . $movie["poster_path"] . '" alt="' . $movie["title"] . '" loading="lazy">';
        echo '</div>';
      }
      ?>
    </div>

    <h2 class="section-title">POPULAR SERIES</h2>
    <div class="scroll-container">
      <?php
     // Popular Series
      $url = "https://api.themoviedb.org/3/tv/popular?api_key=$tmdb_api_key";
      $response = getCachedApiResponse($url);
      $series = json_decode($response, true)["results"];
      foreach ($series as $show) {
        echo '<div class="scroll-item">';
        echo '<img src="https://image.tmdb.org/t/p/w500' . $show["poster_path"] . '" alt="' . $show["name"] . '" loading="lazy">';
        echo '</div>';
      }
      ?>
    </div>

    <h2 class="section-title">TOP RATED MOVIES</h2>
    <div class="scroll-container">
      <?php
      // Top Rated Movies
        $url = "https://api.themoviedb.org/3/movie/top_rated?api_key=$tmdb_api_key";
        $response = getCachedApiResponse($url);
        $topMovies = json_decode($response, true)["results"];
      foreach ($topMovies as $movie) {
        echo '<div class="scroll-item">';
        echo '<img src="https://image.tmdb.org/t/p/w500' . $movie["poster_path"] . '" alt="' . $movie["title"] . '" loading="lazy">';
        echo '</div>';
      }
      ?>
    </div>

    <h2 class="section-title">TOP RATED SERIES</h2>
    <div class="scroll-container">
      <?php
     // Top Rated Series
      $url = "https://api.themoviedb.org/3/tv/top_rated?api_key=$tmdb_api_key";
      $response = getCachedApiResponse($url);
      $topSeries = json_decode($response, true)["results"];
      foreach ($topSeries as $show) {
        echo '<div class="scroll-item">';
        echo '<img src="https://image.tmdb.org/t/p/w500' . $show["poster_path"] . '" alt="' . $show["name"] . '" loading="lazy">';
        echo '</div>';
      }
      ?>
    </div>

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
