<?php
session_start();
require "config.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movies - iWatch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h2 class="section-title">Popular Movies</h2>
        <div class="scroll-container">
            <div class="grid-container">
                <?php
                $url = "https://api.themoviedb.org/3/movie/popular?api_key=" . TMDB_API_KEY;
                $response = getCachedApiResponse($url);
                $movies = json_decode($response, true)["results"] ?? [];
                foreach ($movies as $movie) {
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
                }
                ?>
            </div>
        </div>

        <h2 class="section-title">Top Rated Movies</h2>
        <div class="scroll-container">
            <div class="grid-container">
                <?php
                $url = "https://api.themoviedb.org/3/movie/top_rated?api_key=" . TMDB_API_KEY;
                $response = getCachedApiResponse($url);
                $topMovies = json_decode($response, true)["results"] ?? [];
                foreach ($topMovies as $movie) {
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
                }
                ?>
            </div>
        </div>

        <?php include 'modals.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>