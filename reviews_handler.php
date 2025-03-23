<?php
session_start();
error_log("Received POST request: " . json_encode($_POST));

require "config.php";

// Enable error reporting for logs, not display
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
$response = ['success' => false, 'error' => ''];

error_log("Starting reviews_handler.php");

if (!isset($conn) || !$conn) {
    $response['error'] = 'Database connection failed: ' . mysqli_connect_error();
    error_log("Database connection failed: " . mysqli_connect_error());
} elseif (!isset($_SESSION["logged_in"]) || !$_SESSION["logged_in"]) {
    $response['error'] = 'Please sign in to submit a review.';
    error_log("User not logged in");
} elseif (!isset($_SESSION["user_id"])) {
    $response['error'] = 'User ID not set in session.';
    error_log("User ID not set in session");
} elseif ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST["review_text"])) {
    $response['error'] = 'Invalid request: Method=' . $_SERVER["REQUEST_METHOD"] . ', Missing review text';
    error_log("Invalid request: Method=" . $_SERVER["REQUEST_METHOD"] . ", Missing review text");
} else {
    $user_id = (int)$_SESSION["user_id"];
    $media_id = filter_var($_POST["media_id"] ?? '', FILTER_VALIDATE_INT);
    $media_type = filter_var($_POST["media_type"] ?? '', FILTER_SANITIZE_STRING);
    $review_text = filter_var($_POST["review_text"], FILTER_SANITIZE_STRING);

    error_log("Processing review: user_id=$user_id, media_id=$media_id, media_type=$media_type, review_text=$review_text");

    if ($media_id === false || !in_array($media_type, ['movie', 'tv']) || empty($review_text)) {
        $response['error'] = 'Invalid review details: media_id=' . $media_id . ', media_type=' . $media_type . ', review_text=' . $review_text;
        error_log("Invalid review details: media_id=$media_id, media_type=$media_type, review_text=$review_text");
    } else {
        // Insert review into database
        $stmt = $conn->prepare("INSERT INTO reviews (user_id, media_id, media_type, review_text) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            $response['error'] = 'Prepare failed: ' . mysqli_error($conn);
            error_log("Insert prepare failed: " . mysqli_error($conn));
        } else {
            $stmt->bind_param("iiss", $user_id, $media_id, $media_type, $review_text);
            if ($stmt->execute()) {
                $response['success'] = true;
                error_log("Review added: user_id=$user_id, media_id=$media_id, media_type=$media_type");
            } else {
                $response['error'] = 'Insert failed: ' . $stmt->error;
                error_log("Insert execute failed: " . $stmt->error);
            }
            $stmt->close();
        }
    }
}

echo json_encode($response);
exit();
?>