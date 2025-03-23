<?php
session_start();
require "config.php";
require "auth.php";

header('Content-Type: application/json');
$response = ['success' => false, 'error' => ''];

ini_set('display_errors', 0);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

error_log("Starting signin_handler.php");

if (!isset($conn) || !$conn) {
    $response['error'] = 'Database connection failed.';
    error_log("Database connection failed: " . mysqli_connect_error());
} elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
    $signInResult = signIn($conn);
    if ($signInResult === false) {
        $response['success'] = true;
        error_log("Sign-in successful: username=" . $_SESSION["username"]);
    } else {
        $response['error'] = $signInResult;
        error_log("Sign-in failed: " . $signInResult);
    }
} else {
    $response['error'] = 'Invalid request method.';
    error_log("Invalid request method: " . $_SERVER["REQUEST_METHOD"]);
}

echo json_encode($response);
exit();
?>