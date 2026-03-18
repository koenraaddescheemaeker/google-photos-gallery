<?php
require_once 'config.php';
$token = getValidAccessToken();

if (!$token) {
    echo "<h1>Welkom bij Familie Forcekes</h1><p>Nog geen foto's gekoppeld. <a href='google-auth.php'>Beheerder: Log hier in</a></p>";
    exit;
}

// Haal het gekozen album op (instelling uit de database)
$res = supabaseRequest('google_tokens?select=active_album_id&id=eq.1');
$albumId = $res[0]['active_album_id'] ?? null;

if (!$albumId) {
    echo "Welkom! Beheerder, kies een album in de <a href='admin.php'>Admin</a>.";
    exit;
}

// Haal foto's uit het album
$ch = curl_init("https://photoslibrary.googleapis.com/v1/mediaItems:search");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["albumId" => $albumId, "pageSize" => 20]));
$photos = json_decode(curl_exec($ch), true);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Familie Forcekes</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white p-10">
    <h1 class="text-4xl font-bold mb-10">Onze Herinneringen</h1>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <?php foreach ($photos['mediaItems'] as $item): ?>
            <img src="<?php echo $item['baseUrl']; ?>=w500" class="rounded-lg shadow-lg">
        <?php endforeach; ?>
    </div>
</body>
</html>