<?php
session_start();
require "config.php";

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

$user_id = $_SESSION["user_id"] ?? null;

if (!isset($_SESSION["logged_in"]) || !$_SESSION["logged_in"]) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watchlist - iWatch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h1>Your Watchlist</h1>
        
        <?php
        $sections = [
            "watching" => "Currently Watching",
            "planned" => "Planned",
            "completed" => "Completed"
        ];

        foreach ($sections as $status_key => $status_label): ?>
            <h2 class="section-title"><?php echo $status_label; ?></h2>
            <div class="scroll-container">
                <div class="grid-container">
                    <?php
                    $stmt = $conn->prepare(
                        "SELECT media_id, media_type, title, poster_path 
                         FROM watchlist 
                         WHERE user_id = ? AND status = ?"
                    );
                    $stmt->bind_param("is", $user_id, $status_key);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    while ($row = $result->fetch_assoc()):
                        $title = $row['title'] ?? ($row['media_type'] == 'movie' ? 'Unknown Movie' : 'Unknown Series');
                        $poster = $row['poster_path'] 
                            ? "https://image.tmdb.org/t/p/w500{$row['poster_path']}" 
                            : 'https://via.placeholder.com/200x300?text=No+Poster';
                    ?>
                        <div class="grid-item">
                            <a href="<?php echo $row['media_type'] == 'movie' ? 'moviedetails.php' : 'seriesdetails.php'; ?>?id=<?php echo $row['media_id']; ?>">
                                <img src="<?php echo htmlspecialchars($poster); ?>" alt="<?php echo htmlspecialchars($title); ?>" loading="lazy">
                                <div class="overlay">
                                    <div class="title"><?php echo htmlspecialchars($title); ?></div>
                                    <span class="play-btn">Play</span>
                                </div>
                            </a>
                            <select class="status-select form-select mt-2"
                                    data-media-id="<?php echo $row['media_id']; ?>"
                                    data-media-type="<?php echo $row['media_type']; ?>">
                                <option value="planned" <?php echo $status_key == "planned" ? "selected" : ""; ?>>Planning</option>
                                <option value="watching" <?php echo $status_key == "watching" ? "selected" : ""; ?>>Currently Watching</option>
                                <option value="completed" <?php echo $status_key == "completed" ? "selected" : ""; ?>>Completed</option>
                            </select>
                            <button class="btn btn-danger btn-remove-watchlist mt-2" 
                                    data-media-id="<?php echo $row['media_id']; ?>" 
                                    data-media-type="<?php echo $row['media_type']; ?>">Remove</button>
                        </div>
                    <?php endwhile; ?>
                    <?php $stmt->close(); ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script defer src="script.js?v=<?php echo filemtime('script.js'); ?>"></script>
</body>
</html>