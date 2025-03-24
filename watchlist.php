<?php
session_start();
require_once "config.php";

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

$user_id = $_SESSION["user_id"] ?? null;
$is_logged_in = isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true;
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
        <?php if ($is_logged_in): ?>
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
                        
                        if ($result->num_rows === 0) {
                            echo '<p>No items in this section.</p>';
                        } else {
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
                        <?php } $stmt->close(); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="access-denied text-center mt-5">
                <h4>You must sign in to access this page.</h4>
                <p>Please <a href="#" class="access-link" data-bs-toggle="modal" data-bs-target="#signInModal">sign in</a> or <a href="#" class="access-link" data-bs-toggle="modal" data-bs-target="#signUpModal">sign up</a> to view your watchlist.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Include Modals -->
    <?php include 'modals.php'; ?> <!-- Assuming modals are in a separate file; adjust as needed -->

    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script defer src="script.js?v=<?php echo filemtime('script.js'); ?>"></script>
</body>
</html>