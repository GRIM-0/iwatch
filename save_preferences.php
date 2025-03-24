<?php
session_start();
require "config.php";

header('Content-Type: application/json');
$response = ['success' => false, 'error' => ''];

if (!isset($_SESSION["logged_in"]) || !$_SESSION["logged_in"]) {
    $response['error'] = 'Please sign in to save preferences.';
} elseif ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $response['error'] = 'Invalid request method.';
} else {
    $user_id = (int)$_SESSION["user_id"];
    $genres = isset($_POST['genres']) ? array_map('intval', $_POST['genres']) : [];
    $genres_string = implode(',', $genres);

    // Check if user already has preferences
    $stmt = $conn->prepare("SELECT id FROM user_preferences WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update existing preferences
        $stmt = $conn->prepare("UPDATE user_preferences SET preferred_genres = ? WHERE user_id = ?");
        $stmt->bind_param("si", $genres_string, $user_id);
    } else {
        // Insert new preferences
        $stmt = $conn->prepare("INSERT INTO user_preferences (user_id, preferred_genres) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $genres_string);
    }

    if ($stmt->execute()) {
        $response['success'] = true;
    } else {
        $response['error'] = 'Failed to save preferences.';
        error_log("Failed to save preferences: " . $stmt->error);
    }
    $stmt->close();
}

echo json_encode($response);
exit();
?>