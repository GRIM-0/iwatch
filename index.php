<?php
session_start();
require_once "config.php";

// Fetch popular and top-rated content
$popularMoviesUrl = "https://api.themoviedb.org/3/movie/popular?api_key=" . TMDB_API_KEY;
$popularMoviesResponse = getCachedApiResponse($popularMoviesUrl);
$popularMovies = json_decode($popularMoviesResponse, true)["results"] ?? [];

$popularSeriesUrl = "https://api.themoviedb.org/3/tv/popular?api_key=" . TMDB_API_KEY;
$popularSeriesResponse = getCachedApiResponse($popularSeriesUrl);
$popularSeries = json_decode($popularSeriesResponse, true)["results"] ?? [];

$topRatedMoviesUrl = "https://api.themoviedb.org/3/movie/top_rated?api_key=" . TMDB_API_KEY;
$topRatedMoviesResponse = getCachedApiResponse($topRatedMoviesUrl);
$topRatedMovies = json_decode($topRatedMoviesResponse, true)["results"] ?? [];

$topRatedSeriesUrl = "https://api.themoviedb.org/3/tv/top_rated?api_key=" . TMDB_API_KEY;
$topRatedSeriesResponse = getCachedApiResponse($topRatedSeriesUrl);
$topRatedSeries = json_decode($topRatedSeriesResponse, true)["results"] ?? [];

// Recommendations for logged-in users
$recommendations = [];
if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] && isset($conn)) {
    $user_id = $_SESSION["user_id"];

    // 1. Try preferences first
    $stmt = $conn->prepare("SELECT preferred_genres FROM user_preferences WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $preferences = $result->fetch_assoc();
    $stmt->close();

    if ($preferences && !empty($preferences['preferred_genres'])) {
        $genres = explode(',', $preferences['preferred_genres']);
        $movieRecUrl = "https://api.themoviedb.org/3/discover/movie?api_key=" . TMDB_API_KEY . "&with_genres=" . implode(',', $genres);
        $movieRecResponse = getCachedApiResponse($movieRecUrl);
        $movieRecData = json_decode($movieRecResponse, true);
        $movieRecommendations = $movieRecData["results"] ?? [];
        foreach ($movieRecommendations as &$rec) {
            $rec['media_type'] = 'movie';
        }

        $tvRecUrl = "https://api.themoviedb.org/3/discover/tv?api_key=" . TMDB_API_KEY . "&with_genres=" . implode(',', $genres);
        $tvRecResponse = getCachedApiResponse($tvRecUrl);
        $tvRecData = json_decode($tvRecResponse, true);
        $tvRecommendations = $tvRecData["results"] ?? [];
        foreach ($tvRecommendations as &$rec) {
            $rec['media_type'] = 'tv';
        }

        $recommendations = array_merge($movieRecommendations, $tvRecommendations);
    }

    // 2. If no preferences or no results, try favorites
    if (empty($recommendations)) {
        $stmt = $conn->prepare("SELECT media_id, media_type FROM favorites WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $favorite = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($favorite) {
            $media_id = $favorite['media_id'];
            $media_type = $favorite['media_type'];
            $recUrl = "https://api.themoviedb.org/3/$media_type/$media_id/similar?api_key=" . TMDB_API_KEY;
            $recResponse = getCachedApiResponse($recUrl);
            $recData = json_decode($recResponse, true);
            $recommendations = $recData["results"] ?? [];
            foreach ($recommendations as &$rec) {
                $rec['media_type'] = $media_type;
            }
        }
    }

    // 3. If still no recommendations, try watchlist
    if (empty($recommendations)) {
        $stmt = $conn->prepare("SELECT media_id, media_type FROM watchlist WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $watchlist = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($watchlist) {
            $media_id = $watchlist['media_id'];
            $media_type = $watchlist['media_type'];
            $recUrl = "https://api.themoviedb.org/3/$media_type/$media_id/similar?api_key=" . TMDB_API_KEY;
            $recResponse = getCachedApiResponse($recUrl);
            $recData = json_decode($recResponse, true);
            $recommendations = $recData["results"] ?? [];
            foreach ($recommendations as &$rec) {
                $rec['media_type'] = $media_type;
            }
        }
    }

    // Shuffle and limit recommendations
    if (!empty($recommendations)) {
        shuffle($recommendations);
        $recommendations = array_slice($recommendations, 0, 10); // Limit to 10 items
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iWatch - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container mt-4">
    <!-- Section: Recommended for You -->
    <?php if (!empty($recommendations)): ?>
        <h2 class="section-title">Recommended for You</h2>
        <div class="scroll-container">
            <div class="grid-container">
                <?php
                foreach ($recommendations as $rec) {
                    $poster = $rec["poster_path"] ? "https://image.tmdb.org/t/p/w500" . $rec["poster_path"] : "https://via.placeholder.com/200x300?text=No+Poster";
                    $rating = isset($rec["vote_average"]) ? htmlspecialchars($rec["vote_average"]) : "N/A";
                    $title = htmlspecialchars($rec["title"] ?? $rec["name"] ?? "Unknown");
                    $link = $rec["media_type"] == "movie" ? "moviedetails.php?id=" . $rec["id"] : "seriesdetails.php?id=" . $rec["id"];
                    echo '<div class="grid-item">';
                    echo '<a href="' . $link . '">';
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
    <?php endif; ?>

    <!-- Section: Popular Movies -->
    <h2 class="section-title">Popular Movies</h2>
    <div class="scroll-container">
        <div class="grid-container">
            <?php
            foreach ($popularMovies as $movie) {
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

    <!-- Section: Popular TV Series -->
    <h2 class="section-title">Popular TV Series</h2>
    <div class="scroll-container">
        <div class="grid-container">
            <?php
            foreach ($popularSeries as $serie) {
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

    <!-- Section: Top Rated Movies -->
    <h2 class="section-title">Top Rated Movies</h2>
    <div class="scroll-container">
        <div class="grid-container">
            <?php
            foreach ($topRatedMovies as $movie) {
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

    <!-- Section: Top Rated TV Series -->
    <h2 class="section-title">Top Rated TV Series</h2>
    <div class="scroll-container">
        <div class="grid-container">
            <?php
            foreach ($topRatedSeries as $serie) {
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