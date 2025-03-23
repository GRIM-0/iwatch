<?php
// Database configuration
$host = "localhost";
$username = "root";
$password = "";
$database = "iwatch";

$tmdb_api_key = "c7163b9122c94f924c110fb3c14417d7";

$conn = mysqli_connect($host, $username, $password, $database);
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");

define("TMDB_API_KEY", $tmdb_api_key);

date_default_timezone_set("UTC");
function getCachedApiResponse($url) {
    $cacheDir = 'cache/';
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0777, true);
    }
    
    $cacheFile = $cacheDir . md5($url) . '.json';
    $cacheLifetime = 24 * 3600; // 24 hours

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheLifetime) {
        return file_get_contents($cacheFile);
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 120); // Increased timeout
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20); // Increased connection timeout
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        error_log("cURL error for URL '$url': $error");
        curl_close($ch);
        return '{}'; // Return empty JSON on failure
    }
    
    curl_close($ch);
    file_put_contents($cacheFile, $response);
    return $response;
}
error_reporting(E_ALL);
ini_set("display_errors", 1);
?>