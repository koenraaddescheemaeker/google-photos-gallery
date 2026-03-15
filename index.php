<?php
require_once 'config.php';

$albumId = isset($_GET['album_id']) ? $_GET['album_id'] : "https://goo.gl/photos/H5nmVV473rJw1d6aA";
$accessToken = getValidAccessToken();

if (!$accessToken) {
    die("Geen Google verbinding gevonden. Ga naar <a href='google-auth.php'>google-auth.php</a> om in te loggen.");
}

// Haal foto's op via de MediaItems:search endpoint
$ch = curl_init("https://photoslibrary.googleapis.com/v1/mediaItems:search");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $accessToken", "Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    "albumId" => $albumId,
    "pageSize" => 50
]));

$response = json_decode(curl_exec($ch), true);
curl_close($ch);

$photos = $response['mediaItems'] ?? [];
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Familie Album</title>
</head>
<body class="bg-gray-50">
    <?php include 'menu.php'; ?>

    <div class="max-w-7xl mx-auto px-4 py-12">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <?php foreach ($photos as $item): ?>
                <div class="aspect-square overflow-hidden rounded-xl shadow-md">
                    <img src="<?= $item['baseUrl'] ?>=w600-h600-c" class="w-full h-full object-cover">
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>