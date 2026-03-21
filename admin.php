<?php
require_once 'config.php';

$token = getValidAccessToken();
if (!$token) die("Sleutel corrupt.");

function checkApi($url, $token) {
    $ch = curl_init("https://photoslibrary.googleapis.com/v1/" . $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = json_decode(curl_exec($ch), true);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'res' => $res];
}

$own = checkApi("albums", $token);
$shared = checkApi("sharedAlbums", $token);

if ($own['code'] !== 200) {
    echo "<h3>Fout bij Eigen Albums: " . $own['code'] . "</h3>";
    echo "<pre>" . print_r($own['res'], true) . "</pre>";
}

if ($shared['code'] !== 200) {
    echo "<h3>Fout bij Gedeelde Albums: " . $shared['code'] . "</h3>";
    echo "<pre>" . print_r($shared['res'], true) . "</pre>";
}

// Als beide 200 zijn, kun je de rest van je admin.php weergave laten draaien
if ($own['code'] === 200 && $shared['code'] === 200) {
    echo "<h1>Beide koppelingen werken!</h1>";
}
?>