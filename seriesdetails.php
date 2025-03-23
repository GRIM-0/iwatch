<?php
session_start();
require "config.php";

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

$id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;
if ($id <= 0) {
    header("Location: index.php");
    exit;
}

// Fetch series details
$url = "https://api.themoviedb.org/3/tv/$id?api_key=$tmdb_api_key";
$response = getCachedApiResponse($url);
$series = json_decode($response, true);

// Fetch cast (credits)
$castUrl = "https://api.themoviedb.org/3/tv/$id/credits?api_key=$tmdb_api_key";
$castResponse = getCachedApiResponse($castUrl);
$castData = json_decode($castResponse, true);

// Check if series exists and handle errors
if (isset($series["status_code"])) {
    $error = $series["status_message"] ?? "An error occurred while fetching series details.";
} elseif (!$series || empty($series["name"])) {
    $error = "Series not found.";
}

// Check favorites and watchlist status
$isFavorited = false;
$watchlistStatus = null;
if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true && isset($_SESSION["user_id"])) {
    $user_id = (int)$_SESSION["user_id"];
    
    // Check if series is favorited
    $stmt = $conn->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ? AND media_id = ? AND media_type = 'tv'");
    $stmt->bind_param("ii", $user_id, $id);
    $stmt->execute();
    $isFavorited = $stmt->get_result()->fetch_row()[0] > 0;
    $stmt->close();

    // Check watchlist status
    $stmt = $conn->prepare("SELECT status FROM watchlist WHERE user_id = ? AND media_id = ? AND media_type = 'tv'");
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
    <title>iWatch - <?php echo htmlspecialchars($series["name"] ?? "Series Details"); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
    <h2 class="section-title">SERIES DETAILS</h2>
    <div class="content-box">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php else: ?>
            <div class="row">
                <div class="col-md-4">
                    <img src="<?php echo $series["poster_path"] ? 'https://image.tmdb.org/t/p/w500' . $series["poster_path"] : 'https://via.placeholder.com/300x450?text=No+Poster'; ?>" 
                         alt="<?php echo htmlspecialchars($series["name"]); ?>" 
                         class="poster img-fluid" 
                         loading="lazy">
                </div>
                <div class="col-md-8 details">
                    <h2><?php echo htmlspecialchars($series["name"]); ?> (<?php echo htmlspecialchars(substr($series["first_air_date"] ?? '', 0, 4)); ?>)</h2>
                    <p><strong>Rating:</strong> <?php echo htmlspecialchars($series["vote_average"] ?? 'N/A'); ?>/10 (<?php echo htmlspecialchars($series["vote_count"] ?? '0'); ?> votes)</p>
                    <p><strong>First Air Date:</strong> <?php echo htmlspecialchars($series["first_air_date"] ?? 'N/A'); ?></p>
                    <div class="genres">
                        <?php foreach ($series["genres"] ?? [] as $genre): ?>
                            <span class="badge bg-secondary me-1"><?php echo htmlspecialchars($genre["name"]); ?></span>
                        <?php endforeach; ?>
                    </div>
                    <p><strong>Overview:</strong> <?php echo htmlspecialchars($series["overview"] ?? 'No overview available'); ?></p>
                    <p><strong>Number of Seasons:</strong> <?php echo htmlspecialchars($series["number_of_seasons"] ?? 'N/A'); ?></p>
                    <p><strong>Number of Episodes:</strong> <?php echo htmlspecialchars($series["number_of_episodes"] ?? 'N/A'); ?></p>
                    
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
                        <a href="#" class="btn btn-red me-2">Watch Now</a>
                        <?php if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true): ?>
                            <button class="btn btn-outline-primary btn-favorite <?php echo $isFavorited ? 'active' : ''; ?>" 
                                    data-media-id="<?php echo $id; ?>" 
                                    data-media-type="tv">
                                <?php echo $isFavorited ? 'Favorited' : 'Add to Favorites'; ?>
                            </button>
                            <?php error_log("watchlistStatus for series ID $id: " . var_export($watchlistStatus, true)); ?>
                            <select class="form-select d-inline-block w-auto me-2 btn-watchlist-status" 
                                    data-media-id="<?php echo $id; ?>" 
                                    data-media-type="tv">
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
                            <form id="reviewFormSeries" class="needs-validation" novalidate>
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
                                <input type="hidden" name="media_type" value="tv">
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js?v=<?php echo filemtime('script.js'); ?>"></script>
</body>
</html>