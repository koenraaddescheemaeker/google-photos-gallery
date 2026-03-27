<?php
require_once 'config.php';
$token = getValidAccessToken();
if (!$token) die("Log eerst even in via login.php");

$ch = curl_init("https://photoslibrary.googleapis.com/v1/albums");
curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER => ["Authorization: Bearer $token"],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false
]);
$res = json_decode(curl_exec($ch), true);

echo "<h1>Kopieer de IDs hieronder:</h1>";
if (isset($res['albums'])) {
    foreach ($res['albums'] as $a) {
        echo "<b>" . $a['title'] . "</b><br>";
        echo "<code style='background:#eee; padding:2px;'>" . $a['id'] . "</code><br><br>";
    }
} else {
    print_r($res);
}