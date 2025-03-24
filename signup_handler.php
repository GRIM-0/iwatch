<?php
session_start();
require "config.php";

header('Content-Type: application/json');
$response = ['success' => false, 'error' => '', 'showPreferences' => false];

ini_set('display_errors', 0);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

error_log("Starting signup_handler.php");

if (!isset($conn) || !$conn) {
    $response['error'] = 'Database connection failed.';
    error_log("Database connection failed: " . mysqli_connect_error());
} elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = filter_var($_POST["username"] ?? '', FILTER_SANITIZE_STRING);
    $email = filter_var($_POST["email"] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST["password"] ?? '';

    error_log("Sign-up attempt: username=$username, email=$email");

    if (empty($username) || empty($email) || empty($password)) {
        $response['error'] = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['error'] = 'Invalid email format.';
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        if (!$stmt) {
            $response['error'] = 'Database error: ' . mysqli_error($conn);
            error_log("Select prepare failed: " . mysqli_error($conn));
        } else {
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $response['error'] = 'Username or email already taken.';
                error_log("Sign-up failed: Username or email taken - username=$username, email=$email");
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                if (!$stmt) {
                    $response['error'] = 'Database error: ' . mysqli_error($conn);
                    error_log("Insert prepare failed: " . mysqli_error($conn));
                } else {
                    $stmt->bind_param("sss", $username, $email, $hashed_password);
                    if ($stmt->execute()) {
                        $user_id = $stmt->insert_id;
                        $_SESSION["logged_in"] = true;
                        $_SESSION["user_id"] = $user_id;
                        $_SESSION["username"] = $username;
                        $response['success'] = true;
                        $response['showPreferences'] = true; // Signal to show preferences modal
                        error_log("Sign-up successful: username=$username");
                    } else {
                        $response['error'] = 'Failed to sign up: ' . $stmt->error;
                        error_log("Sign-up failed: " . $stmt->error);
                    }
                }
            }
            $stmt->close();
        }
    }
} else {
    $response['error'] = 'Invalid request method.';
    error_log("Invalid request method: " . $_SERVER["REQUEST_METHOD"]);
}

echo json_encode($response);
exit();
?>