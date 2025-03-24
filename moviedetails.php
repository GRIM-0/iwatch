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

// Fetch trailer video
$videoUrl = "https://api.themoviedb.org/3/movie/$id/videos?api_key=$tmdb_api_key";
$videoResponse = getCachedApiResponse($videoUrl);
$videoData = json_decode($videoResponse, true);
$trailer = null;
foreach ($videoData["results"] ?? [] as $video) {
    if (strtolower($video["type"]) === "trailer" && strtolower($video["site"]) === "youtube") {
        $trailer = $video;
        break;
    }
}
$trailerUrl = $trailer ? "https://www.youtube.com/watch?v=" . $trailer["key"] : null;

// Fetch cast (credits)
$castUrl = "https://api.themoviedb.org/3/movie/$id/credits?api_key=$tmdb_api_key";
$castResponse = getCachedApiResponse($castUrl);
$castData = json_decode($castResponse, true);

// Check if movie exists and handle errors
if (isset($movie["status_code"])) {
    $error = $movie["status_message"] ?? "An error occurred while fetching movie details.";
} elseif (!$movie || empty($movie["title"])) {
    $error = "Movie not found.";
}

// Check favorites and watchlist status
$isFavorited = false;
$watchlistStatus = null;
if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true && isset($_SESSION["user_id"])) {
    $user_id = (int)$_SESSION["user_id"];
    
    $stmt = $conn->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ? AND media_id = ? AND media_type = 'movie'");
    $stmt->bind_param("ii", $user_id, $id);
    $stmt->execute();
    $isFavorited = $stmt->get_result()->fetch_row()[0] > 0;
    $stmt->close();

    $stmt = $conn->prepare("SELECT status FROM watchlist WHERE user_id = ? AND media_id = ? AND media_type = 'movie'");
    $stmt->bind_param("ii", $user_id, $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $watchlistStatus = $row['status'];
    }
    $stmt->close();
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
<?php include 'navbar.php'; ?>

<div class="container mt-4">
    <h2 class="section-title">MOVIE DETAILS</h2>
    <div class="content-box">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php else: ?>
            <div class="row">
                <div class="col-md-4">
                    <img src="<?php echo $movie["poster_path"] ? 'https://image.tmdb.org/t/p/w500' . $movie["poster_path"] : 'https://via.placeholder.com/300x450?text=No+Poster'; ?>" 
                         alt="<?php echo htmlspecialchars($movie["title"]); ?>" 
                         class="poster img-fluid" 
                         loading="lazy">
                </div>
                <div class="col-md-8 details">
                    <h2><?php echo htmlspecialchars($movie["title"]); ?> (<?php echo htmlspecialchars(substr($movie["release_date"] ?? '', 0, 4)); ?>)</h2>
                    <p><strong>Rating:</strong> <?php echo htmlspecialchars($movie["vote_average"] ?? 'N/A'); ?>/10 (<?php echo htmlspecialchars($movie["vote_count"] ?? '0'); ?> votes)</p>
                    <p><strong>Release Date:</strong> <?php echo htmlspecialchars($movie["release_date"] ?? 'N/A'); ?></p>
                    <div class="genres">
                        <?php foreach ($movie["genres"] ?? [] as $genre): ?>
                            <span class="badge bg-secondary me-1"><?php echo htmlspecialchars($genre["name"]); ?></span>
                        <?php endforeach; ?>
                    </div>
                    <p><strong>Overview:</strong> <?php echo htmlspecialchars($movie["overview"] ?? 'No overview available'); ?></p>
                    <p><strong>Runtime:</strong> <?php echo isset($movie["runtime"]) ? htmlspecialchars(floor($movie["runtime"] / 60)) . 'h ' . ($movie["runtime"] % 60) . 'm' : 'N/A'; ?></p>
                    
                    <div class="cast">
                        <h4>Cast</h4>
                        <p>
                            <?php
                            $cast = array_slice($castData["cast"] ?? [], 0, 5);
                            $castNames = array_map(function($actor) { 
                                return htmlspecialchars($actor["name"]) . ' as ' . htmlspecialchars($actor["character"]); 
                            }, $cast);
                            echo implode(", ", $castNames) ?: 'No cast information available';
                            ?>
                        </p>
                    </div>

                    <div class="action-buttons mt-3">
                        <?php if ($trailerUrl): ?>
                            <a href="#" class="btn btn-red me-2 btn-watch-trailer" data-trailer-url="<?php echo htmlspecialchars($trailerUrl); ?>" data-bs-toggle="modal" data-bs-target="#trailerModal">Watch Trailer</a>
                        <?php else: ?>
                            <a href="#" class="btn btn-red me-2 disabled" disabled>Watch Trailer (Not Available)</a>
                        <?php endif; ?>
                        <?php if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true): ?>
                            <button class="btn btn-outline-primary btn-favorite <?php echo $isFavorited ? 'active' : ''; ?>" 
                                    data-media-id="<?php echo $id; ?>" 
                                    data-media-type="movie">
                                <?php echo $isFavorited ? 'Favorited' : 'Add to Favorites'; ?>
                            </button>
                            <select class="form-select d-inline-block w-auto me-2 btn-watchlist-status" 
                                    data-media-id="<?php echo $id; ?>" 
                                    data-media-type="movie">
                                <option value="" <?php echo !$watchlistStatus ? 'selected' : ''; ?>>
                                    <?php echo $watchlistStatus === null ? 'Add to Watchlist' : 'Remove from Watchlist'; ?>
                                </option>
                                <option value="planned" <?php echo $watchlistStatus === 'planned' ? 'selected' : ''; ?>>Planned</option>
                                <option value="watching" <?php echo $watchlistStatus === 'watching' ? 'selected' : ''; ?>>Watching</option>
                                <option value="completed" <?php echo $watchlistStatus === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        <?php endif; ?>
                    </div>

                    <?php if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true): ?>
                        <div class="review-section mt-4">
                            <h3>Leave a Review</h3>
                            <form id="reviewFormMovie" class="needs-validation" novalidate>
                                <div class="mb-3">
                                    <textarea name="review_text" 
                                              class="form-control" 
                                              placeholder="Write your review here" 
                                              required 
                                              minlength="10" 
                                              maxlength="500"></textarea>
                                    <div class="invalid-feedback">
                                        Review must be between 10 and 500 characters.
                                    </div>
                                </div>
                                <input type="hidden" name="media_id" value="<?php echo $id; ?>">
                                <input type="hidden" name="media_type" value="movie">
                                <button type="submit" class="btn btn-review">Submit Review</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'modals.php'; ?>
</div>

<!-- Trailer Modal -->
<div class="modal fade" id="trailerModal" tabindex="-1" aria-labelledby="trailerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content sign-box">
            <div class="modal-header">
                <h5 class="modal-title" id="trailerModalLabel">Watch Trailer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="ratio ratio-16x9">
                    <iframe id="trailerIframe" src="" frameborder="0" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js?v=<?php echo filemtime('script.js'); ?>"></script>
</body>
</html>