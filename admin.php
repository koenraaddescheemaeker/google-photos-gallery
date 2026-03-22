<?php
// FORCEER FOUTMELDINGEN (Zet dit bovenaan!)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "";

// Controleer of het bestand bestaat voordat we crashen
if (!file_exists('config.php')) {
    die("FOUT: config.php niet gevonden op de server.");
}

require_once 'config.php';

echo "";

$token = getValidAccessToken();

if (!$token) {
    echo "<body style='background:#000;color:#fff;padding:50px;font-family:sans-serif;'>";
    echo "<h1 style='color:red;'>FOUT: Geen token verkregen.</h1>";
    echo "<p>Check de console/logs of de REFRESH FAILED melding hieronder.</p>";
    echo "</body>";
    exit;
}

// Haal Gedeelde Albums op
$ch = curl_init("https://photoslibrary.googleapis.com/v1/sharedAlbums?pageSize=50");
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$res = json_decode(curl_exec($ch), true);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$sharedAlbums = $res['sharedAlbums'] ?? [];
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Forcekes Admin | Debug Mode</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white p-12">
    <h1 class="text-4xl font-black italic mb-8 uppercase">FORCEKES <span class="text-blue-500">ADMIN</span></h1>
    
    <?php if ($httpCode === 200 && !empty($sharedAlbums)): ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php foreach ($sharedAlbums as $album): ?>
                <div class="bg-zinc-900 border border-zinc-800 p-4 rounded-[2rem]">
                    <img src="<?= $album['coverPhotoBaseUrl'] ?>=w600-h400-c" class="w-full h-48 object-cover rounded-2xl mb-4">
                    <h3 class="font-bold truncate text-lg"><?= htmlspecialchars($album['title'] ?? 'Naamloos') ?></h3>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="bg-red-500/10 border border-red-500 p-8 rounded-2xl">
            <h2 class="font-bold text-red-500">API STATUS: <?= $httpCode ?></h2>
            <pre class="mt-4 text-xs text-green-400"><?= print_r($res, true) ?></pre>
        </div>
    <?php endif; ?>
</body>
</html>