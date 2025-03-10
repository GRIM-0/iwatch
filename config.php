
<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "iwatch";
$tmdb_api_key = "c7163b9122c94f924c110fb3c14417d7";

$conn = mysqli_connect($host, $username, $password, $database);
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

function getCachedApiResponse($url) {
    $cacheFile = 'cache/' . md5($url) . '.json';
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 24 * 3600) {
        return file_get_contents($cacheFile);
    }
    $response = file_get_contents($url);
    file_put_contents($cacheFile, $response);
    return $response;
}
?>