<?php
session_start();
require "config.php";

if (!isset($_SESSION["logged_in"]) || !$_SESSION["logged_in"]) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION["user_id"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favorites - iWatch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h1>My Favorites</h1>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php
            $stmt = mysqli_prepare($conn, "SELECT media_id, media_type FROM favorites WHERE user_id = ?");
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $favorites = mysqli_fetch_all($result, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt);

            if (empty($favorites)) {
                echo '<p>No favorites yet. </p><br><p>Add some from the Movies or TV Series pages!</p>';
            } else {
                foreach ($favorites as $favorite) {
                    $media_id = $favorite['media_id'];
                    $media_type = $favorite['media_type'];
                    $url = "https://api.themoviedb.org/3/" . ($media_type === 'movie' ? 'movie' : 'tv') . "/$media_id?api_key=$tmdb_api_key&language=en-US";
                    $response = getCachedApiResponse($url);
                    $data = json_decode($response, true);

                    if ($data) {
                        echo '<div class="col">';
                        echo '<div class="card">';
                        echo '<img src="https://image.tmdb.org/t/p/w500' . htmlspecialchars($data['poster_path'] ?? '') . '" class="card-img-top" alt="' . htmlspecialchars($data['title'] ?? $data['name']) . '">';
                        echo '<div class="card-body">';
                        echo '<h5 class="card-title">' . htmlspecialchars($data['title'] ?? $data['name']) . '</h5>';
                        echo '<p class="card-text">' . ($media_type === 'movie' ? 'Release Date: ' : 'First Air Date: ') . htmlspecialchars($data['release_date'] ?? $data['first_air_date']) . '</p>';
                        echo '<p class="card-text">Rating: ' . htmlspecialchars($data['vote_average'] ?? 0) . '</p>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                    }
                }
            }
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>