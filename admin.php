<?php
require_once 'config.php';

$token = getValidAccessToken();

if (!$token) {
    die("Sleutel ontbreekt of is ongeldig.");
}

// Focus enkel op de Shared Albums endpoint
$ch = curl_init("https://photoslibrary.googleapis.com/v1/sharedAlbums");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token",
    "Accept: application/json"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$raw = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$res = json_decode($raw, true);
curl_close($ch);

// Debug logging (alleen bij fout)
if ($httpCode !== 200) {
    error_log("Google API Error $httpCode: " . print_r($res, true));
}

$sharedAlbums = $res['sharedAlbums'] ?? [];
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Forcekes Admin | Gedeelde Albums</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-zinc-100 p-8">
    <div class="max-w-6xl mx-auto">
        <header class="mb-12">
            <h1 class="text-4xl font-black italic tracking-tighter">
                FORCEKES <span class="text-blue-500">SHARED</span>
            </h1>
            <p class="text-zinc-500 mt-2 italic">Focus: Gedeeld door mij, met mij, iedereen met link</p>
        </header>

        <?php if ($httpCode === 200 && !empty($sharedAlbums)): ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php foreach ($sharedAlbums as $album): ?>
                    <div class="bg-zinc-900 border border-zinc-800 rounded-[2rem] overflow-hidden hover:border-blue-500 transition-all">
                        <div class="h-48 bg-zinc-800 relative">
                            <?php if (isset($album['coverPhotoBaseUrl'])): ?>
                                <img src="<?= $album['coverPhotoBaseUrl'] ?>=w600-h400-c" class="w-full h-full object-cover">
                            <?php endif; ?>
                            <div class="absolute bottom-4 left-4">
                                <span class="bg-blue-600 text-[10px] font-bold px-2 py-1 rounded-md uppercase">Gedeeld</span>
                            </div>
                        </div>
                        <div class="p-6">
                            <h3 class="font-bold truncate text-lg"><?= htmlspecialchars($album['title'] ?? 'Naamloos') ?></h3>
                            <p class="text-sm text-zinc-500 mt-1"><?= $album['mediaItemsCount'] ?? 0 ?> foto's</p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif ($httpCode === 200): ?>
            <div class="bg-zinc-900 p-20 rounded-[3rem] text-center border border-zinc-800">
                <p class="text-zinc-500">Geen gedeelde albums gevonden in dit account.</p>
            </div>
        <?php else: ?>
            <div class="bg-red-900/20 border border-red-500 p-8 rounded-[2rem] text-red-400 font-mono text-sm">
                <h2 class="font-bold mb-2">API Error <?= $httpCode ?></h2>
                <pre><?= htmlspecialchars(print_r($res, true)) ?></pre>
                <p class="mt-4 text-xs text-zinc-400">Tip: Controleer of de scope 'photoslibrary.sharing' in je token zit.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>