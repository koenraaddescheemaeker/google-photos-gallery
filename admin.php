<?php
// 1. Foutrapportage aan (voor het geval dat)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. De ontbrekende schakel:
require_once 'config.php';

// 3. Haal de token op
$token = getValidAccessToken();

if (!$token) {
    echo "<body style='background:#000;color:#fff;font-family:sans-serif;padding:50px;'>";
    echo "<h2 style='color:#ef4444;'>Sleutel ontbreekt of is ongeldig.</h2>";
    echo "<p>Controleer de refresh_token in de database of check de REFRESH FAILED melding hierboven.</p>";
    echo "</body>";
    exit;
}

// 4. Haal Gedeelde Albums op
$ch = curl_init("https://photoslibrary.googleapis.com/v1/sharedAlbums?pageSize=50");
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

$sharedAlbums = $res['sharedAlbums'] ?? [];
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forcekes Admin | Gedeelde Albums</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-zinc-100 p-6 md:p-12">
    <div class="max-w-6xl mx-auto">
        <header class="flex justify-between items-center mb-12">
            <h1 class="text-4xl font-black italic tracking-tighter uppercase">
                FORCEKES <span class="text-blue-500">SHARED</span>
            </h1>
            <span class="px-4 py-2 bg-zinc-900 border border-zinc-800 rounded-full text-[10px] font-bold uppercase tracking-widest text-green-500">
                Verbonden
            </span>
        </header>

        <?php if ($httpCode === 200 && !empty($sharedAlbums)): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($sharedAlbums as $album): ?>
                    <div class="group bg-zinc-900/50 border border-zinc-800 rounded-[2.5rem] overflow-hidden hover:border-blue-500/50 transition-all duration-300">
                        <div class="relative h-52 w-full bg-zinc-800">
                            <?php if (isset($album['coverPhotoBaseUrl'])): ?>
                                <img src="<?= $album['coverPhotoBaseUrl'] ?>=w600-h400-c" 
                                     class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-700">
                            <?php endif; ?>
                            <div class="absolute top-4 left-4">
                                <span class="px-3 py-1 bg-blue-600 rounded-lg text-[10px] font-black uppercase tracking-widest">Shared</span>
                            </div>
                        </div>
                        <div class="p-6">
                            <h3 class="text-lg font-bold truncate mb-1"><?= htmlspecialchars($album['title'] ?? 'Naamloos') ?></h3>
                            <p class="text-sm text-zinc-500 italic"><?= $album['mediaItemsCount'] ?? 0 ?> items</p>
                            <button class="w-full mt-6 py-3 bg-zinc-800 hover:bg-blue-600 rounded-2xl text-xs font-bold transition-colors">Selecteer Album</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif ($httpCode === 200): ?>
            <div class="bg-zinc-900/50 border border-zinc-800 p-20 rounded-[3rem] text-center">
                <p class="text-zinc-500 text-lg font-medium italic">Geen gedeelde albums gevonden in dit account.</p>
            </div>
        <?php else: ?>
            <div class="bg-red-500/10 border border-red-500/50 p-8 rounded-[2rem] text-red-400">
                <h2 class="text-xl font-black mb-4 uppercase italic tracking-widest">API Fout (<?= $httpCode ?>)</h2>
                <pre class="bg-black/50 p-6 rounded-2xl text-xs font-mono overflow-auto border border-red-500/20 text-green-400"><?= htmlspecialchars(print_r($res, true)) ?></pre>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>