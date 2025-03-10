<?php
session_start();
require "config.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iWatch - TV Series</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
   <nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">iWatch</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon">☰</span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'movies.php' ? 'active' : ''; ?>" href="movies.php">Movies</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'tv-series.php' ? 'active' : ''; ?>" href="tv-series.php">TV Series</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'search.php' ? 'active' : ''; ?>" href="search.php">Search</a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true): ?>
                    <li class="nav-item dropdown">
                        <a class="username-nav dropdown-toggle nav-link" href="#" id="accountDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php echo htmlspecialchars($_SESSION["username"]); ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="accountDropdown">
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <li><a class="dropdown-item" href="logout.php">Log Out</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link btn btn-red" href="#" data-bs-toggle="modal" data-bs-target="#signInModal">Sign In</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

    <div class="container mt-4">
        <h2 class="section-title">TV SERIES</h2>
        <div class="grid-container">
            <?php
            $url = "https://api.themoviedb.org/3/discover/tv?api_key=$tmdb_api_key";
            $response = getCachedApiResponse($url);
            $series = json_decode($response, true)["results"] ?? [];
            foreach ($series as $show) {
                $poster = $show["poster_path"] ? "https://image.tmdb.org/t/p/w500" . $show["poster_path"] : "https://via.placeholder.com/200x300?text=No+Poster";
                $rating = isset($show["vote_average"]) ? htmlspecialchars($show["vote_average"]) : "N/A";
                $title = htmlspecialchars($show["name"]);
                echo '<div class="grid-item">';
                echo '<a href="seriesdetails.php?id=' . $show["id"] . '">';
                echo '<img src="' . $poster . '" alt="' . $title . '" loading="lazy">';
                echo '<div class="overlay">';
                echo '<div class="rating">' . $rating . '/10</div>';
                echo '<div class="title">' . $title . '</div>';
                echo '<span class="play-btn">Play</span>';
                echo '</div>';
                echo '</a>';
                echo '</div>';
            }
            ?>
        </div>

        <?php include 'modals.php'; ?>
        
        

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>

