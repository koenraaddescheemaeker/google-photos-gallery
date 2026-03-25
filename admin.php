<?php
/**
 * FORCEKES - admin.php (Final Diagnostic)
 */
require_once 'config.php';
$token = getValidAccessToken();

if (!$token) { header("Location: login.php?pw=admin123"); exit; }

function callPhotosAPI($endpoint, $token) {
    $ch = curl_init("https://photoslibrary.googleapis.com/v1/" . $endpoint);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Content-Type: application/json",
        "Accept: application/json"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['status' => $status, 'json' => json_decode($response, true), 'raw' => $response];
}

$own = callPhotosAPI("albums?pageSize=50", $token);
$shared = callPhotosAPI("sharedAlbums?pageSize=50", $token);
$albums = array_merge($own['json']['albums'] ?? [], $shared['json']['sharedAlbums'] ?? []);
?>