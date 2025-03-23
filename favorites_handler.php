<?php
session_start();
require "config.php";

ini_set('display_errors', 0);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
$response = ['success' => false, 'error' => '', 'isFavorited' => false];

error_log("Starting favorites_handler.php");

if (!isset($conn) || !$conn) {
    $response['error'] = 'Database connection failed: ' . mysqli_connect_error();
} elseif (!isset($_SESSION["logged_in"]) || !$_SESSION["logged_in"]) {
    $response['error'] = 'Please sign in to manage favorites.';
} elseif (!isset($_SESSION["user_id"])) {
    $response['error'] = 'User ID not set in session.';
} elseif ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST["action"])) {
    $response['error'] = 'Invalid request.';
} else {
    $user_id = (int)$_SESSION["user_id"];
    $media_id = filter_var($_POST["media_id"] ?? '', FILTER_VALIDATE_INT);
    $media_type = filter_var($_POST["media_type"] ?? '', FILTER_SANITIZE_STRING);

    if ($media_id === false || !in_array($media_type, ['movie', 'tv'])) {
        $response['error'] = 'Invalid media details.';
    } else {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ? AND media_id = ? AND media_type = ?");
        $stmt->bind_param("iis", $user_id, $media_id, $media_type);
        $stmt->execute();
        $count = $stmt->get_result()->fetch_row()[0];
        $stmt->close();

        if ($_POST["action"] === "toggle") {
            if ($count > 0) {
                $stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND media_id = ? AND media_type = ?");
                $stmt->bind_param("iis", $user_id, $media_id, $media_type);
                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['isFavorited'] = false;
                    $response['message'] = 'Removed from favorites.';
                }
                $stmt->close();
            } else {
                $stmt = $conn->prepare("INSERT INTO favorites (user_id, media_id, media_type) VALUES (?, ?, ?)");
                $stmt->bind_param("iis", $user_id, $media_id, $media_type);
                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['isFavorited'] = true;
                    $response['message'] = 'Added to favorites.';
                }
                $stmt->close();
            }
        } elseif ($_POST["action"] === "add" && $count == 0) {
            $stmt = $conn->prepare("INSERT INTO favorites (user_id, media_id, media_type, created_at) VALUES (?, ?, ?, NOW())");            $stmt->bind_param("iis", $user_id, $media_id, $media_type);
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['isFavorited'] = true;
            }
            $stmt->close();
        } elseif ($_POST["action"] === "add") {
            $response['error'] = 'Already in favorites.';
        }
    }
}

echo json_encode($response);
exit();
?>