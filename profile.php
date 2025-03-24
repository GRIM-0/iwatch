<?php
session_start();
require "config.php";

if (!isset($_SESSION["logged_in"]) || !$_SESSION["logged_in"]) {
    header("Location: index.php");
    exit;
}

if (!isset($conn) || !$conn) {
    die("Database connection failed.");
}

// Fetch user details
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$users = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch user's preferences
$stmt = $conn->prepare("SELECT preferred_genres FROM user_preferences WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$preferences = $stmt->get_result()->fetch_assoc();
$stmt->close();
$preferred_genres = $preferences ? explode(',', $preferences['preferred_genres']) : [];

// Fetch genre list for movies
$genreUrl = "https://api.themoviedb.org/3/genre/movie/list?api_key=" . $tmdb_api_key;
$genreResponse = getCachedApiResponse($genreUrl);
$genresList = json_decode($genreResponse, true)["genres"] ?? [];

// Fetch user's reviews
$stmt = $conn->prepare("SELECT * FROM reviews WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Prepare titles and posters for reviews
$reviewData = [];
foreach ($reviews as $review) {
    $media_id = $review['media_id'];
    $media_type = $review['media_type'];
    $url = "https://api.themoviedb.org/3/" . ($media_type == "movie" ? "movie" : "tv") . "/" . $media_id . "?api_key=$tmdb_api_key";
    $response = getCachedApiResponse($url);
    $data = json_decode($response, true);
    $title = $data["title"] ?? $data["name"] ?? "Unknown";
    $poster = $data["poster_path"] ? "https://image.tmdb.org/t/p/w500" . $data["poster_path"] : "https://via.placeholder.com/200x300?text=No+Poster";
    $rating = isset($data["vote_average"]) ? $data["vote_average"] : "N/A";
    $reviewData[$media_id] = [
        'title' => $title,
        'poster' => $poster,
        'review_text' => $review['review_text'],
        'created_at' => $review['created_at'],
        'media_type' => $media_type,
        'rating' => $rating
    ];
}

// Fetch user's favorites
$stmt = $conn->prepare("SELECT media_id, media_type FROM favorites WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$favorites = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Prepare titles and posters for favorites
$favoriteData = [];
foreach ($favorites as $favorite) {
    $media_id = $favorite['media_id'];
    $media_type = $favorite['media_type'];
    $url = "https://api.themoviedb.org/3/" . ($media_type == "movie" ? "movie" : "tv") . "/" . $media_id . "?api_key=$tmdb_api_key";
    $response = getCachedApiResponse($url);
    $data = json_decode($response, true);
    $title = $data["title"] ?? $data["name"] ?? "Unknown";
    $poster = $data["poster_path"] ? "https://image.tmdb.org/t/p/w500" . $data["poster_path"] : "https://via.placeholder.com/200x300?text=No+Poster";
    $rating = isset($data["vote_average"]) ? $data["vote_average"] : "N/A";
    $favoriteData[$media_id] = [
        'title' => $title,
        'poster' => $poster,
        'rating' => $rating,
        'media_type' => $media_type
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iWatch - Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <style>
        .content-box {
            background-color: rgb(35, 44, 53);
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .section-title {
            color: #dc3545;
            margin-bottom: 10px;
        }
        .review-text {
            color: #ccc;
            font-size: 0.9rem;
            margin-top: 10px;
            word-wrap: break-word;
        }
        .review-date {
            color: #aaa;
            font-size: 0.8rem;
            margin-top: 5px;
        }
        .vertical-grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            max-width: 100%;
        }
        .toggle-btn {
            background-color: #e50914;
            color: #fff;
            border: none;
            padding: 0.5rem 1rem;
            margin-right: 10px;
            transition: background-color 0.3s;
        }
        .toggle-btn:hover {
            background-color: #f40612;
        }
        .toggle-btn.active {
            background-color: #007bff;
        }
        #content-area {
            transition: opacity 0.3s ease;
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container mt-4">
    <h2 class="section-title"><strong>Profile</strong></h2>
    <div class="content-box">
        <p><strong>Username:</strong> <?php echo htmlspecialchars($users["username"]); ?></p>
        <br><p><strong>Email:</strong> <?php echo htmlspecialchars($users["email"]); ?></p>
    </div>

    <h2 class="section-title">Preferences</h2>
    <div class="content-box">
        <h5>Current Preferred Genres:</h5>
        <?php if (empty($preferred_genres)): ?>
            <p>No preferences set.</p>
        <?php else: ?>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($preferred_genres as $genre_id): ?>
                    <?php
                    $genre = array_filter($genresList, fn($g) => $g['id'] == $genre_id);
                    $genre_name = $genre ? reset($genre)['name'] : 'Unknown';
                    ?>
                    <span class="badge bg-secondary"><?php echo htmlspecialchars($genre_name); ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <button type="button" class="btn btn-red mt-3" data-bs-toggle="modal" data-bs-target="#preferencesModal">Edit Preferences</button>
    </div>

    <div class="mt-4">
        <button id="show-favorites" class="toggle-btn active">Show Favorites</button>
        <button id="show-reviews" class="toggle-btn">Show Reviews</button>
    </div>

    <div id="content-area" class="mt-4">
        <div id="favorites-content" class="vertical-grid-container">
            <?php if (empty($favorites)): ?>
                <p>No favorites yet.</p>
            <?php else: ?>
                <?php foreach ($favoriteData as $media_id => $favorite): ?>
                    <div class="grid-item">
                        <a href="<?php echo ($favorite['media_type'] == 'movie') ? 'moviedetails.php' : 'seriesdetails.php'; ?>?id=<?php echo $media_id; ?>">
                            <img src="<?php echo $favorite['poster']; ?>" alt="<?php echo htmlspecialchars($favorite['title']); ?>" loading="lazy">
                            <div class="overlay">
                                <div class="rating"><?php echo $favorite['rating']; ?>/10</div>
                                <div class="title"><?php echo htmlspecialchars($favorite['title']); ?></div>
                                <span class="play-btn">Play</span>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div id="reviews-content" class="vertical-grid-container" style="display: none;">
            <?php if (empty($reviews)): ?>
                <p>No reviews yet.</p>
            <?php else: ?>
                <?php foreach ($reviewData as $media_id => $review): ?>
                    <div class="grid-item">
                        <a href="<?php echo ($review['media_type'] == 'movie') ? 'moviedetails.php' : 'seriesdetails.php'; ?>?id=<?php echo $media_id; ?>">
                            <img src="<?php echo $review['poster']; ?>" alt="<?php echo htmlspecialchars($review['title']); ?>" loading="lazy">
                            <div class="overlay">
                                <div class="rating"><?php echo $review['rating']; ?>/10</div>
                                <div class="title"><?php echo htmlspecialchars($review['title']); ?></div>
                                <span class="play-btn">Play</span>
                            </div>
                        </a>
                        <div class="review-text"><?php echo htmlspecialchars($review['review_text']); ?></div>
                        <div class="review-date"><?php echo date('F j, Y', strtotime($review['created_at'])); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'modals.php'; ?>

<script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script defer src="script.js?v=<?php echo filemtime('script.js'); ?>"></script>
<script>
    const showFavoritesBtn = document.getElementById('show-favorites');
    const showReviewsBtn = document.getElementById('show-reviews');
    const favoritesContent = document.getElementById('favorites-content');
    const reviewsContent = document.getElementById('reviews-content');
    const contentArea = document.getElementById('content-area');

    function toggleContent(showFavorites) {
        contentArea.style.opacity = '0';
        setTimeout(() => {
            if (showFavorites) {
                favoritesContent.style.display = 'grid';
                reviewsContent.style.display = 'none';
                showFavoritesBtn.classList.add('active');
                showReviewsBtn.classList.remove('active');
            } else {
                favoritesContent.style.display = 'none';
                reviewsContent.style.display = 'grid';
                showFavoritesBtn.classList.remove('active');
                showReviewsBtn.classList.add('active');
            }
            contentArea.style.opacity = '1';
        }, 300);
    }

    showFavoritesBtn.addEventListener('click', () => toggleContent(true));
    showReviewsBtn.addEventListener('click', () => toggleContent(false));

    // Initial state
    toggleContent(true);
</script>
</body>
</html>