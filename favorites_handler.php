<?php
session_start();
require "config.php";

// Enable error reporting for logs, not display
ini_set('display_errors', 0);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
$response = ['success' => false, 'error' => ''];

error_log("Starting favorites_handler.php");

if (!isset($conn) || !$conn) {
    $response['error'] = 'Database connection failed: ' . mysqli_connect_error();
    error_log("Database connection failed: " . mysqli_connect_error());
} elseif (!isset($_SESSION["logged_in"]) || !$_SESSION["logged_in"]) {
    $response['error'] = 'Please sign in to add favorites.';
    error_log("User not logged in");
} elseif (!isset($_SESSION["user_id"])) {
    $response['error'] = 'User ID not set in session.';
    error_log("User ID not set in session");
} elseif ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST["action"]) || $_POST["action"] !== "add") {
    $response['error'] = 'Invalid request: Method=' . $_SERVER["REQUEST_METHOD"] . ', Action=' . ($_POST["action"] ?? 'N/A');
    error_log("Invalid request: Method=" . $_SERVER["REQUEST_METHOD"] . ", Action=" . ($_POST["action"] ?? 'N/A'));
} else {
    $user_id = (int)$_SESSION["user_id"];
    $media_id = filter_var($_POST["media_id"] ?? '', FILTER_VALIDATE_INT);
    $media_type = filter_var($_POST["media_type"] ?? '', FILTER_SANITIZE_STRING);

    error_log("Processing: user_id=$user_id, media_id=$media_id, media_type=$media_type");

    if ($media_id === false || !in_array($media_type, ['movie', 'tv'])) {
        $response['error'] = 'Invalid media details: media_id=' . $media_id . ', media_type=' . $media_type;
        error_log("Invalid media details: media_id=$media_id, media_type=$media_type");
    } else {
        // Check if already favorited
        $stmt = $conn->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ? AND media_id = ? AND media_type = ?");
        if (!$stmt) {
            $response['error'] = 'Prepare failed: ' . mysqli_error($conn);
            error_log("Select prepare failed: " . mysqli_error($conn));
        } else {
            $stmt->bind_param("iis", $user_id, $media_id, $media_type);
            if (!$stmt->execute()) {
                $response['error'] = 'Execute failed: ' . $stmt->error;
                error_log("Select execute failed: " . $stmt->error);
            } else {
                $count = $stmt->get_result()->fetch_row()[0];
                $stmt->close();

                if ($count > 0) {
                    $response['error'] = 'Already in favorites.';
                    error_log("Already favorited: user_id=$user_id, media_id=$media_id, media_type=$media_type");
                } else {
                    $stmt = $conn->prepare("INSERT INTO favorites (user_id, media_id, media_type) VALUES (?, ?, ?)");
                    if (!$stmt) {
                        $response['error'] = 'Prepare failed: ' . mysqli_error($conn);
                        error_log("Insert prepare failed: " . mysqli_error($conn));
                    } else {
                        $stmt->bind_param("iis", $user_id, $media_id, $media_type);
                        if ($stmt->execute()) {
                            $response['success'] = true;
                            error_log("Favorite added: user_id=$user_id, media_id=$media_id, media_type=$media_type");
                        } else {
                            $response['error'] = 'Insert failed: ' . $stmt->error;
                            error_log("Insert execute failed: " . $stmt->error);
                        }
                        $stmt->close();
                    }
                }
            }
        }
    }
}

echo json_encode($response);
exit();
?>