<?php
session_start();
require "config.php";
require "auth.php";

$signInError = signIn($conn);
$url = "https://api.themoviedb.org/3/movie/popular?api_key=$tmdb_api_key";
$response = getCachedApiResponse($url);
$movies = json_decode($response, true)["results"] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iWatch - Movies</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon">☰</span>
        </button>
        <a class="navbar-brand" href="index.php">iWatch</a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'movies.php' ? 'active' : ''; ?>" href="movies.php">Movies</a></li>
                <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'tv-series.php' ? 'active' : ''; ?>" href="tv-series.php">TV Series</a></li>
                <li class="nav-item"><a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'search.php' ? 'active' : ''; ?>" href="search.php">Search</a></li>
            </ul>
        </div>
        <div class="sign-in-container">
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
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="section-title">Popular Movies</h2>
        <div class="grid-container">
            <?php foreach ($movies as $movie) {
                $poster = $movie["poster_path"] ? "https://image.tmdb.org/t/p/w500" . $movie["poster_path"] : "https://via.placeholder.com/200x300?text=No+Poster";
                $rating = isset($movie["vote_average"]) ? htmlspecialchars($movie["vote_average"]) : "N/A";
                $title = htmlspecialchars($movie["title"]);
                echo '<div class="grid-item">';
                echo '<a href="moviedetails.php?id=' . $movie["id"] . '">';
                echo '<img src="' . $poster . '" alt="' . $title . '" loading="lazy">';
                echo '<div class="overlay">';
                echo '<div class="rating">' . $rating . '/10</div>';
                echo '<div class="title">' . $title . '</div>';
                echo '<span class="play-btn">Play</span>';
                echo '</div>';
                echo '</a>';
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
                        <a href="signup.php">Need an account? Sign Up</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>