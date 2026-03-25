<?php
/**
 * FORCEKES - admin.php (Header Fix)
 */
require_once 'config.php';
$token = getValidAccessToken();

if (!$token) { 
    header("Location: login.php?pw=admin123"); 
    exit; 
}

function callPhotosAPI($endpoint, $token) {
    $ch = curl_init("https://photoslibrary.googleapis.com/v1/" . $endpoint);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Accept: application/json",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['status' => $status, 'json' => json_decode($response, true), 'raw' => $response];
}

$ownRes = callPhotosAPI("albums?pageSize=50", $token);
$albums = $ownRes['json']['albums'] ?? [];
?>