<?php
/**
 * FORCEKES ADMIN - Clean Version
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'config.php'; // We gebruiken weer de centrale config

$token = getValidAccessToken();

if (!$token) {
    // Als er geen token is, sturen we de admin naar de login-fixer
    header("Location: login.php?pw=jouw_geheime_wachtwoord");
    exit;
}

// Haal albums op
$ch = curl_init("https://photoslibrary.googleapis.com/v1/sharedAlbums?pageSize=50");
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$res = json_decode(curl_exec($ch), true);
$sharedAlbums = $res['sharedAlbums'] ?? [];
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Forcekes Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white p-12">
    <h1 class="text-4xl font-black italic mb-8 uppercase text-blue-500">FORCEKES ADMIN</h1>
    <?php if (!empty($sharedAlbums)): ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php foreach ($sharedAlbums as $album): ?>
                <div class="bg-zinc-900 border border-zinc-800 p-4 rounded-[2rem]">
                    <img src="<?= $album['coverPhotoBaseUrl'] ?>=w600-h400-c" class="w-full h-48 object-cover rounded-2xl mb-4">
                    <h3 class="font-bold truncate"><?= htmlspecialchars($album['title'] ?? 'Naamloos') ?></h3>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-zinc-500">Geen albums gevonden. <a href="login.php?pw=jouw_geheime_wachtwoord" class="underline">Klik hier om opnieuw te koppelen.</a></p>
    <?php endif; ?>
</body>
</html>