<?php
$db_host = "localhost";
$db_user = "root";
$db_pass = "newpassword"; // Update if you set a password
$db_name = "iwatch";

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}

$tmdb_api_key = "c7163b9122c94f924c110fb3c14417d7";
function getCachedApiResponse($url, $cacheTime = 3600) {
  $cacheDir = __DIR__ . '/cache/';
  $cacheFile = $cacheDir . md5($url) . '.json';
  if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
  }
  if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
    return file_get_contents($cacheFile);
  }
  $response = file_get_contents($url);
  file_put_contents($cacheFile, $response);
  return $response;
}
?>

