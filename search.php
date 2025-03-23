<?php
session_start();
require "config.php";

$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$genres = isset($_GET['genres']) ? array_filter(explode(',', trim($_GET['genres'])), 'strlen') : [];
$searchResults = [];
$errorMessage = '';

error_log("Search query: '$query', genres: " . implode(',', $genres));

// Fetch genre list for movies
$genreUrl = "https://api.themoviedb.org/3/genre/movie/list?api_key=" . $tmdb_api_key;
$genreResponse = getCachedApiResponse($genreUrl);
$genresList = json_decode($genreResponse, true)["genres"] ?? [];

if ($query || !empty($genres)) {
    if ($query) {
        $url = "https://api.themoviedb.org/3/search/multi?api_key=" . $tmdb_api_key . "&query=" . urlencode($query);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        // Uncomment and configure if behind a proxy
        // curl_setopt($ch, CURLOPT_PROXY, 'http://proxy.example.com:port');
        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            error_log("cURL error for URL '$url': $error");
            $errorMessage = "Unable to connect to the API. Please check your internet connection.";
            $searchResults = [];
        } else {
            $data = json_decode($response, true);
            if ($data === null) {
                error_log("JSON decode failed for response: " . substr($response, 0, 200));
                $errorMessage = "Invalid API response.";
                $searchResults = [];
            } else {
                error_log("Raw API response: " . substr($response, 0, 200));
                $searchResults = $data["results"] ?? [];
                
                $searchResults = array_filter($searchResults, function ($result) {
                    $type = $result['media_type'] ?? '';
                    return $type === 'movie' || $type === 'tv';
                });
                
                if (!empty($genres)) {
                    $searchResults = array_filter($searchResults, function ($result) use ($genres) {
                        $mediaGenres = $result['genre_ids'] ?? [];
                        $match = !empty(array_intersect($genres, $mediaGenres));
                        return $match;
                    });
                }
            }
        }
        curl_close($ch);
    } elseif (!empty($genres)) {
        $movieResults = [];
        $tvResults = [];
        
        $movieUrl = "https://api.themoviedb.org/3/discover/movie?api_key=" . $tmdb_api_key . "&with_genres=" . implode(',', array_map('intval', $genres));
        $movieResponse = getCachedApiResponse($movieUrl);
        $movieResults = json_decode($movieResponse, true)["results"] ?? [];
        foreach ($movieResults as &$result) {
            $result['media_type'] = 'movie';
        }

        $tvGenreMap = [
            28 => 10759, 12 => 10759, 16 => 16, 35 => 35, 80 => 80, 99 => 99, 18 => 18, 10751 => 10751,
            14 => 10765, 36 => 10768, 27 => 9648, 9648 => 9648, 10749 => 10766, 878 => 10765, 53 => 10759,
            10752 => 10768, 37 => 37
        ];
        $tvGenres = array_filter(array_map(function ($g) use ($tvGenreMap) { return $tvGenreMap[$g] ?? null; }, $genres));
        if (!empty($tvGenres)) {
            $tvUrl = "https://api.themoviedb.org/3/discover/tv?api_key=" . $tmdb_api_key . "&with_genres=" . implode(',', array_unique($tvGenres));
            $tvResponse = getCachedApiResponse($tvUrl);
            $tvResults = json_decode($tvResponse, true)["results"] ?? [];
            foreach ($tvResults as &$result) {
                $result['media_type'] = 'tv';
            }
        }

        $searchResults = array_merge($movieResults, $tvResults);
        shuffle($searchResults);
    }

    if (empty($searchResults) && !$errorMessage) {
        error_log("No results after processing for query: '$query', genres: " . implode(',', $genres));
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search - iWatch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h1>Search</h1>
        <form id="searchForm" method="GET" action="search.php" class="mb-4">
            <div class="input-group mb-3">
                <input type="text" class="form-control" name="query" placeholder="Search for movies or TV series..." value="<?php echo htmlspecialchars($query); ?>">
                <button type="submit" class="btn btn-red">Search</button>
            </div>
            <div class="mb-3">
                <label for="genres" class="form-label">Filter by Genres:</label>
                <div class="d-flex align-items-center gap-2">
                    <div class="dropdown">
                        <button class="btn btn-secondary dropdown-toggle" type="button" id="genreDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            Select Genres
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="genreDropdown">
                            <?php foreach ($genresList as $genre): ?>
                                <li><a class="dropdown-item genre-item" href="#" data-genre-id="<?php echo $genre['id']; ?>"><?php echo htmlspecialchars($genre['name']); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <button type="button" class="btn btn-outline-danger" id="clearGenres">Clear Genres</button>
                </div>
                <input type="hidden" id="selectedGenresInput" name="genres" value="<?php echo htmlspecialchars(implode(',', $genres)); ?>">
                <div id="selectedGenres" class="mt-2 d-flex flex-wrap gap-2">
                    <?php foreach ($genres as $genreId): ?>
                        <?php
                        $genreMatch = array_filter($genresList, function ($g) use ($genreId) { return $g['id'] == $genreId; });
                        $genreName = !empty($genreMatch) ? $genreMatch[array_key_first($genreMatch)]['name'] : '';
                        if ($genreName): ?>
                            <span class="badge bg-danger d-flex align-items-center">
                                <?php echo htmlspecialchars($genreName); ?>
                                <button type="button" class="btn-close btn-close-white remove-genre ms-2" data-genre-id="<?php echo $genreId; ?>"></button>
                            </span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </form>

        <?php if ($query || !empty($genres)): ?>
            <h2 class="section-title">Search Results<?php echo $query ? ' for "' . htmlspecialchars($query) . '"' : ''; ?><?php echo !empty($genres) ? ' in selected genres' : ''; ?></h2>
            <?php if ($errorMessage): ?>
                <p class="text-danger"><?php echo $errorMessage; ?></p>
            <?php else: ?>
                <div class="scroll-container">
                    <div class="grid-container">
                        <?php foreach ($searchResults as $result): ?>
                            <?php
                            $media_type = $result["media_type"];
                            $poster = $result["poster_path"] ? "https://image.tmdb.org/t/p/w500" . $result["poster_path"] : "https://via.placeholder.com/200x300?text=No+Poster";
                            $rating = isset($result["vote_average"]) ? htmlspecialchars($result["vote_average"]) : "N/A";
                            $title = $media_type == 'movie' ? htmlspecialchars($result["title"] ?? 'Unknown Movie') : htmlspecialchars($result["name"] ?? 'Unknown Series');
                            $link = $media_type == 'movie' ? "moviedetails.php?id=" . $result["id"] : "seriesdetails.php?id=" . $result["id"];
                            ?>
                            <div class="grid-item">
                                <a href="<?php echo $link; ?>">
                                    <img src="<?php echo $poster; ?>" alt="<?php echo $title; ?>" loading="lazy">
                                    <div class="overlay">
                                        <div class="rating"><?php echo $rating; ?>/10</div>
                                        <div class="title"><?php echo $title; ?></div>
                                        <span class="play-btn">Play</span>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($searchResults)): ?>
                            <p>No results found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php include 'modals.php'; ?>
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script defer src="script.js?v=<?php echo filemtime('script.js'); ?>"></script>
</body>
</html>