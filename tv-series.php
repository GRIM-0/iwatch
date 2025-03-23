<?php
session_start();
require "config.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TV Series - iWatch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h2 class="section-title">Popular TV Series</h2>
        <div class="scroll-container">
            <div class="grid-container">
                <?php
                $url = "https://api.themoviedb.org/3/tv/popular?api_key=" . TMDB_API_KEY;
                $response = getCachedApiResponse($url);
                $series = json_decode($response, true)["results"] ?? [];
                foreach ($series as $serie) {
                    $poster = $serie["poster_path"] ? "https://image.tmdb.org/t/p/w500" . $serie["poster_path"] : "https://via.placeholder.com/200x300?text=No+Poster";
                    $rating = isset($serie["vote_average"]) ? htmlspecialchars($serie["vote_average"]) : "N/A";
                    $name = htmlspecialchars($serie["name"]);
                    echo '<div class="grid-item">';
                    echo '<a href="seriesdetails.php?id=' . $serie["id"] . '">';
                    echo '<img src="' . $poster . '" alt="' . $name . '" loading="lazy">';
                    echo '<div class="overlay">';
                    echo '<div class="rating">' . $rating . '/10</div>';
                    echo '<div class="title">' . $name . '</div>';
                    echo '<span class="play-btn">Play</span>';
                    echo '</div>';
                    echo '</a>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>

        <h2 class="section-title">Top Rated TV Series</h2>
        <div class="scroll-container">
            <div class="grid-container">
                <?php
                $url = "https://api.themoviedb.org/3/tv/top_rated?api_key=" . TMDB_API_KEY;
                $response = getCachedApiResponse($url);
                $topSeries = json_decode($response, true)["results"] ?? [];
                foreach ($topSeries as $serie) {
                    $poster = $serie["poster_path"] ? "https://image.tmdb.org/t/p/w500" . $serie["poster_path"] : "https://via.placeholder.com/200x300?text=No+Poster";
                    $rating = isset($serie["vote_average"]) ? htmlspecialchars($serie["vote_average"]) : "N/A";
                    $name = htmlspecialchars($serie["name"]);
                    echo '<div class="grid-item">';
                    echo '<a href="seriesdetails.php?id=' . $serie["id"] . '">';
                    echo '<img src="' . $poster . '" alt="' . $name . '" loading="lazy">';
                    echo '<div class="overlay">';
                    echo '<div class="rating">' . $rating . '/10</div>';
                    echo '<div class="title">' . $name . '</div>';
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