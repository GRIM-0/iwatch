<?php
session_start();
require "config.php";

header('Content-Type: application/json');
$response = ['success' => false, 'error' => ''];

// Enable error logging
ini_set('display_errors', 0);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

error_log("Starting signin_handler.php");

if (!isset($conn) || !$conn) {
    $response['error'] = 'Database connection failed.';
    error_log("Database connection failed: " . mysqli_connect_error());
} elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = filter_var($_POST["username"] ?? '', FILTER_SANITIZE_STRING);
    $password = $_POST["password"] ?? '';

    error_log("Sign-in attempt: username=$username");

    if (empty($username) || empty($password)) {
        $response['error'] = 'Please fill in all fields.';
    } else {
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
        if (!$stmt) {
            $response['error'] = 'Database error: ' . mysqli_error($conn);
            error_log("Prepare failed: " . mysqli_error($conn));
        } else {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if ($user && password_verify($password, $user["password"])) {
                $_SESSION["logged_in"] = true;
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = $username;
                $response['success'] = true;
                error_log("Sign-in successful: user_id=" . $user["id"]);
            } else {
                $response['error'] = 'Invalid username or password.';
                error_log("Sign-in failed: Invalid credentials for username=$username");
            }
        }
    }
} else {
    $response['error'] = 'Invalid request method.';
    error_log("Invalid request method: " . $_SERVER["REQUEST_METHOD"]);
}

echo json_encode($response);
exit();
?>