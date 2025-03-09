<?php
session_start();
require "config.php";

$id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;
if ($id <= 0) {
    header("Location: index.php");
    exit;
}

// Fetch movie details
$url = "https://api.themoviedb.org/3/movie/$id?api_key=$tmdb_api_key";
$response = getCachedApiResponse($url);
$movie = json_decode($response, true);

// Fetch cast (credits)
$castUrl = "https://api.themoviedb.org/3/movie/$id/credits?api_key=$tmdb_api_key";
$castResponse = getCachedApiResponse($castUrl);
$castData = json_decode($castResponse, true);

if (isset($movie["error"])) {
    $error = $movie["error"];
} elseif (!$movie || !isset($movie["title"])) {
    $error = "Movie not found.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iWatch - <?php echo htmlspecialchars($movie["title"] ?? "Movie Details"); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
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
                        <li class="nav-item"><a class="nav-link btn btn-red" href="signin.php">Sign In</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="content-box">
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php else: ?>
                <img src="<?php echo $movie["poster_path"] ? 'https://image.tmdb.org/t/p/w500' . $movie["poster_path"] : 'https://via.placeholder.com/300x450?text=No+Poster'; ?>" alt="<?php echo htmlspecialchars($movie["title"]); ?>" class="poster" loading="lazy">
                <div class="details">
                    <h2><?php echo htmlspecialchars($movie["title"]); ?> (<?php echo htmlspecialchars(substr($movie["release_date"], 0, 4)); ?>)</h2>
                    <p><strong>Rating:</strong> <?php echo htmlspecialchars($movie["vote_average"]); ?>/10</p>
                    <p><strong>Release Date:</strong> <?php echo htmlspecialchars($movie["release_date"]); ?></p>
                    <div class="genres">
                        <?php foreach ($movie["genres"] as $genre) {
                            echo '<span>' . htmlspecialchars($genre["name"]) . '</span>';
                        } ?>
                    </div>
                    <p><strong>Overview:</strong> <?php echo htmlspecialchars($movie["overview"]); ?></p>
                    <p><strong>Runtime:</strong> <?php echo htmlspecialchars($movie["runtime"]); ?> minutes</p>
                    <div class="cast">
                        <h4>Cast</h4>
                        <p>
                            <?php
                            $cast = array_slice($castData["cast"], 0, 5); // Limit to 5 cast members
                            $castNames = array_map(function($actor) { return htmlspecialchars($actor["name"]); }, $cast);
                            echo implode(", ", $castNames);
                            ?>
                        </p>
                    </div>
                    <a href="#" class="btn btn-red">Watch Now</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>