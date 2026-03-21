<?php
require_once 'config.php';

// Haal de gevalideerde token op
$token = getValidAccessToken();

if (!$token) {
    echo "<body style='background:#000;color:#fff;font-family:sans-serif;padding:50px;'>";
    echo "<h2 style='color:#ef4444;'>Geen geldige sleutel!</h2>";
    echo "<p>De database is leeg of de Refresh Token is ongeldig. Controleer je Supabase instellingen.</p>";
    echo "</body>";
    exit;
}

/**
 * Hulpfunctie om data op te halen van de Google Photos API
 */
function fetchGooglePhotos($endpoint, $token) {
    $ch = curl_init("https://photoslibrary.googleapis.com/v1/" . $endpoint);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Accept: application/json"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Matcht jouw server-omgeving
    $raw = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    
    return [
        'status' => $info['http_code'],
        'body'   => json_decode($raw, true)
    ];
}

// 1. Haal eigen albums op
$ownRes = fetchGooglePhotos("albums", $token);
$ownAlbums = $ownRes['body']['albums'] ?? [];

// 2. Haal gedeelde albums op
$sharedRes = fetchGooglePhotos("sharedAlbums", $token);
$sharedAlbums = $sharedRes['body']['sharedAlbums'] ?? [];

// 3. Voeg ze samen en markeer ze voor de UI
foreach ($ownAlbums as &$a) { $a['_type'] = 'Eigen'; }
foreach ($sharedAlbums as &$a) { $a['_type'] = 'Gedeeld'; }

$allAlbums = array_merge($ownAlbums, $sharedAlbums);
$httpCode = ($ownRes['status'] === 200 || $sharedRes['status'] === 200) ? 200 : $ownRes['status'];
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forcekes Admin | Foto Beheer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-black text-zinc-100 p-6 md:p-12">
    <div class="max-w-6xl mx-auto">
        <header class="flex flex-col md:flex-row md:items-center justify-between mb-12 gap-4">
            <div>
                <h1 class="text-4xl font-black italic tracking-tighter uppercase">
                    FORCEKES <span class="text-blue-500 underline decoration-4 underline-offset-8">ADMIN</span>
                </h1>
                <p class="text-zinc-500 mt-2 font-medium">Beheer je Google Photos bibliotheek</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-4 py-2 bg-zinc-900 border border-zinc-800 rounded-full text-xs font-bold uppercase tracking-widest text-zinc-400">
                    Systeem Status: <span class="text-green-500">Online</span>
                </span>
            </div>
        </header>

        <?php if ($httpCode === 200 && !empty($allAlbums)): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($allAlbums as $album): ?>
                    <div class="group bg-zinc-900/50 border border-zinc-800 rounded-[2rem] overflow-hidden hover:border-zinc-600 transition-all duration-300 hover:shadow-[0_20px_50px_rgba(0,0,0,0.5)]">
                        <div class="relative h-56 w-full overflow-hidden bg-zinc-800">
                            <?php if (isset($album['coverPhotoBaseUrl'])): ?>
                                <img src="<?= $album['coverPhotoBaseUrl'] ?>=w600-h400-c" 
                                     alt="Cover" 
                                     class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500">
                            <?php else: ?>
                                <div class="flex items-center justify-center h-full text-zinc-700 uppercase font-black tracking-tighter text-2xl">Geen Media</div>
                            <?php endif; ?>
                            
                            <div class="absolute top-4 right-4">
                                <span class="px-3 py-1 bg-black/60 backdrop-blur-md border border-white/10 rounded-full text-[10px] font-black uppercase tracking-widest">
                                    <?= $album['_type'] ?>
                                </span>
                            </div>
                        </div>

                        <div class="p-6">
                            <h3 class="text-lg font-bold truncate leading-tight mb-1" title="<?= htmlspecialchars($album['title'] ?? 'Naamloos') ?>">
                                <?= htmlspecialchars($album['title'] ?? 'Naamloos') ?>
                            </h3>
                            <div class="flex items-center justify-between mt-4">
                                <span class="text-sm font-medium text-zinc-500 italic">
                                    <?= $album['mediaItemsCount'] ?? 0 ?> items
                                </span>
                                <button class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl transition-colors">
                                    Selecteer
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif ($httpCode === 200): ?>
            <div class="bg-zinc-900 border border-zinc-800 p-20 rounded-[3rem] text-center">
                <p class="text-zinc-500 text-xl font-medium">Geen albums gevonden (eigen of gedeeld).</p>
            </div>
        <?php else: ?>
            <div class="bg-red-500/10 border border-red-500/50 p-8 rounded-[2rem] text-red-400">
                <h2 class="text-xl font-black mb-4 uppercase italic">Systeem Error (<?= $httpCode ?>)</h2>
                <pre class="bg-black/50 p-6 rounded-2xl text-xs font-mono overflow-auto border border-red-500/20 text-green-400"><?= htmlspecialchars(print_r($ownRes['body'] ?? $sharedRes['body'], true)) ?></pre>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>