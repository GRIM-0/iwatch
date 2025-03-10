<?php
session_start();
require "config.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iWatch - Search</title>
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
                <div class="nav-item dropdown">
                    <a class="username-nav dropdown-toggle" href="#" id="accountDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php echo htmlspecialchars($_SESSION["username"]); ?>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="accountDropdown">
                        <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                        <li><a class="dropdown-item" href="logout.php">Log Out</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <div class="nav-item">
                    <a class="nav-link btn btn-red" href="#" data-bs-toggle="modal" data-bs-target="#signInModal">Sign In</a>
                </div>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="section-title">SEARCH</h2>
        <form method="GET" action="search.php" class="mb-4">
            <div class="input-group">
                <input type="text" class="form-control" name="query" placeholder="Search for movies or TV series..." value="<?php echo htmlspecialchars($_GET['query'] ?? ''); ?>" required>
                <button type="submit" class="btn btn-red">Search</button>
            </div>
        </form>

        <?php
        if (isset($_GET['query']) && !empty(trim($_GET['query']))) {
            $query = urlencode($_GET['query']);
            $url = "https://api.themoviedb.org/3/search/multi?api_key=$tmdb_api_key&query=$query";
            $response = getCachedApiResponse($url);
            $results = json_decode($response, true)["results"] ?? [];
            if (!empty($results)) {
                echo '<div class="grid-container">';
                foreach ($results as $item) {
                    $poster = $item["poster_path"] ? "https://image.tmdb.org/t/p/w500" . $item["poster_path"] : "https://via.placeholder.com/200x300?text=No+Poster";
                    $rating = isset($item["vote_average"]) ? htmlspecialchars($item["vote_average"]) : "N/A";
                    $title = htmlspecialchars($item["title"] ?? $item["name"]);
                    $type = $item["media_type"] === "movie" ? "moviedetails.php" : "seriesdetails.php";
                    echo '<div class="grid-item">';
                    echo '<a href="' . $type . '?id=' . $item["id"] . '">';
                    echo '<img src="' . $poster . '" alt="' . $title . '" loading="lazy">';
                    echo '<div class="overlay">';
                    echo '<div class="rating">' . $rating . '/10</div>';
                    echo '<div class="title">' . $title . '</div>';
                    echo '<span class="play-btn">Play</span>';
                    echo '</div>';
                    echo '</a>';
                    echo '</div>';
                }
                echo '</div>';
            } else {
                echo '<p>No results found for "' . htmlspecialchars($_GET['query']) . '".</p>';
            }
        }
        ?>

        <?php include 'modals.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>