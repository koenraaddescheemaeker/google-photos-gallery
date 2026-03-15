<?php
require_once 'config.php';
$url = $_GET['url'];
$cacheFile = '/tmp/cache_' . md5($url) . '.json';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$content = curl_exec($ch);
curl_close($ch);

if ($content) {
    preg_match('/<meta property="og:title" content="([^"]+)">/', $content, $titleMatches);
    $title = isset($titleMatches[1]) ? str_replace(" - Google Photos", "", $titleMatches[1]) : "Album";
    preg_match_all('/https:\/\/lh3\.googleusercontent\.com\/pw\/[a-zA-Z0-9\-_]+/', $content, $matches);
    $photos = array_unique($matches[0]);
    file_put_contents($cacheFile, json_encode(['photos' => $photos, 'title' => $title]));
}
// Geef een transparante 1x1 pixel terug zodat de browser niet klaagt
header('Content-Type: image/png');
echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');