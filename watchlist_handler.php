<?php
session_start();
require "config.php";

ob_start(); // Buffer output
header('Content-Type: application/json');
$response = ['success' => false, 'error' => ''];

error_log("Received POST: " . json_encode($_POST));

if (!isset($_SESSION["logged_in"]) || !$_SESSION["logged_in"]) {
    $response['error'] = 'Please sign in to manage your watchlist.';
} elseif (!isset($_SESSION["user_id"])) {
    $response['error'] = 'User ID not set in session.';
} elseif ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $response['error'] = 'Invalid request method.';
} else {
    $user_id = (int)$_SESSION["user_id"];
    $action = $_POST["action"] ?? '';
    $media_id = filter_var($_POST["media_id"] ?? '', FILTER_VALIDATE_INT);
    $media_type = htmlspecialchars($_POST["media_type"] ?? '', ENT_QUOTES, 'UTF-8');
    $status = htmlspecialchars($_POST["status"] ?? '', ENT_QUOTES, 'UTF-8');

    error_log("Processed: user_id=$user_id, action=$action, media_id=$media_id, media_type=$media_type, status=$status");

    if ($media_id === false || !in_array($media_type, ['movie', 'tv'])) {
        $response['error'] = 'Invalid input.';
    } elseif ($action == 'add' || $action == 'update') {
        if (!in_array($status, ['planned', 'watching', 'completed'])) {
            $response['error'] = 'Invalid status.';
        } else {
            // Fetch details from TMDb API
            $url = "https://api.themoviedb.org/3/{$media_type}/{$media_id}?api_key={$tmdb_api_key}";
            $api_response = getCachedApiResponse($url);
            $data = json_decode($api_response, true);

            if (!is_array($data) || isset($data['status_code'])) {
                $response['error'] = 'Failed to fetch media details.';
                error_log("API error for $url: " . ($data['status_message'] ?? 'No response'));
            } else {
                $title = $media_type == 'movie' ? ($data['title'] ?? 'Unknown Movie') : ($data['name'] ?? 'Unknown Series');
                $poster_path = $data['poster_path'] ?? null;

                // Check if entry exists, update or insert accordingly
                $stmt = $conn->prepare("SELECT id FROM watchlist WHERE user_id = ? AND media_id = ? AND media_type = ?");
                $stmt->bind_param("iis", $user_id, $media_id, $media_type);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    // Update existing
                    $stmt = $conn->prepare(
                        "UPDATE watchlist SET status = ?, title = ?, poster_path = ?, updated_at = NOW() 
                         WHERE user_id = ? AND media_id = ? AND media_type = ?"
                    );
                    $stmt->bind_param("sssiis", $status, $title, $poster_path, $user_id, $media_id, $media_type);
                } else {
                    // Insert new
                    $stmt = $conn->prepare(
                        "INSERT INTO watchlist (user_id, media_id, media_type, status, title, poster_path, created_at, updated_at) 
                         VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())"
                    );
                    $stmt->bind_param("iissss", $user_id, $media_id, $media_type, $status, $title, $poster_path);
                }

                if ($stmt->execute()) {
                    $response['success'] = true;
                } else {
                    $response['error'] = 'Database error: ' . $conn->error;
                    error_log("DB error: " . $conn->error);
                }
                $stmt->close();
            }
        }
    } elseif ($action == 'remove') {
        $stmt = $conn->prepare("DELETE FROM watchlist WHERE user_id = ? AND media_id = ? AND media_type = ?");
        $stmt->bind_param("iis", $user_id, $media_id, $media_type);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $response['success'] = true;
            } else {
                $response['error'] = 'No such entry found.';
            }
        } else {
            $response['error'] = 'Database error: ' . $conn->error;
            error_log("DB error: " . $conn->error);
        }
        $stmt->close();
    } else {
        $response['error'] = 'Invalid action.';
    }
}

ob_end_clean(); // Clear buffer
echo json_encode($response);
exit();
?>