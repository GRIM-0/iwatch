<?php
session_start();
require "config.php";

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// Fetch user details
$stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Fetch user's favorites
$stmt = mysqli_prepare($conn, "SELECT media_id, media_type FROM favorites WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$favorites_result = mysqli_stmt_get_result($stmt);
$favorites = mysqli_fetch_all($favorites_result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iWatch - Profile</title>
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
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'favorites.php' ? 'active' : ''; ?>" href="favorites.php">Favorites</a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="username-nav dropdown-toggle nav-link" href="#" id="accountDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php echo htmlspecialchars($_SESSION["username"]); ?>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="accountDropdown">
                        <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                        <li><a class="dropdown-item" href="logout.php">Log Out</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h2 class="section-title">PROFILE</h2>
    <div class="content-box">
        <p><strong>Username:</strong> <?php echo htmlspecialchars($_SESSION["username"]); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user["email"]); ?></p>
    </div>

    <h2 class="section-title mt-4">MY FAVORITES</h2>
    <div class="grid-container">
        <?php
        if (empty($favorites)) {
            echo '<p>No favorites yet. Add some from the Movies or TV Series pages!</p>';
        } else {
            foreach ($favorites as $favorite) {
                $media_id = $favorite['media_id'];
                $media_type = $favorite['media_type'];
                $url = "https://api.themoviedb.org/3/" . ($media_type === 'movie' ? 'movie' : 'tv') . "/$media_id?api_key=$tmdb_api_key&language=en-US";
                $response = getCachedApiResponse($url);
                $data = json_decode($response, true);

                if ($data) {
                    $poster = $data["poster_path"] ? "https://image.tmdb.org/t/p/w500" . $data["poster_path"] : "https://via.placeholder.com/200x300?text=No+Poster";
                    $rating = isset($data["vote_average"]) ? htmlspecialchars($data["vote_average"]) : "N/A";
                    $title = htmlspecialchars($data['title'] ?? $data['name']);
                    $detail_page = $media_type === 'movie' ? "moviedetails.php" : "seriesdetails.php";

                    echo '<div class="grid-item">';
                    echo '<a href="' . $detail_page . '?id=' . $media_id . '">';
                    echo '<img src="' . $poster . '" alt="' . $title . '" loading="lazy">';
                    echo '<div class="overlay">';
                    echo '<div class="rating">' . $rating . '/10</div>';
                    echo '<div class="title">' . $title . '</div>';
                    echo '<span class="play-btn">Play</span>';
                    echo '</div>';
                    echo '</a>';
                    echo '</div>';
                }
            }
        }
        ?>
    </div>

    <?php include 'modals.php'; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>
</body>
</html>