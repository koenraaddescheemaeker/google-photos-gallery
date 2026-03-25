<?php
/**
 * FORCEKES - admin.php (The Final Boss Edition)
 */
// 1. Forceer foutmeldingen (tegen het witte scherm)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';

// 2. Haal de token op en poets hem op (trimming)
$rawToken = getValidAccessToken();
$token = trim($rawToken); 

if (!$token) {
    die("<h1>Fout</h1><p>Geen token gevonden. <a href='login.php?pw=admin123'>Log opnieuw in</a>.</p>");
}

/**
 * Super-robuuste API call
 */
function fetchGoogle($url, $token) {
    $ch = curl_init($url);
    $headers = [
        "Authorization: Bearer " . $token,
        "Accept: application/json",
        "User-Agent: Forcekes-Portaal-v3" // Soms eist Google een User-Agent
    ];
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'data' => json_decode($res, true),
        'raw'  => $res,
        'error' => $err
    ];
}

// 3. Voer de tests uit
$testAlbums = fetchGoogle("https://photoslibrary.googleapis.com/v1/albums?pageSize=10", $token);
$testShared = fetchGoogle("https://photoslibrary.googleapis.com/v1/sharedAlbums?pageSize=10", $token);

$allAlbums = array_merge($testAlbums['data']['albums'] ?? [], $testShared['data']['sharedAlbums'] ?? []);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Forcekes | Admin Ultimate</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap'); body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-black text-white p-6 md:p-12">
    <div class="max-w-6xl mx-auto">
        <header class="flex justify-between items-center mb-12 border-b border-zinc-900 pb-8">
            <h1 class="text-4xl font-black italic text-blue-500 uppercase">FORCEKES <span class="text-white">DASHBOARD</span></h1>
            <a href="login.php?pw=admin123" class="px-6 py-3 bg-zinc-800 rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-zinc-700">Reset Verbinding</a>
        </header>

        <details class="mb-10 bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden">
            <summary class="p-6 cursor-pointer font-bold text-xs uppercase tracking-widest text-zinc-500 hover:bg-zinc-800 transition-all">
                Technische Analyse (Klik voor details)
            </summary>
            <div class="p-8 space-y-6 border-t border-zinc-800 font-mono text-[10px]">
                <div>
                    <p class="text-blue-400 mb-1">Token status:</p>
                    <p class="text-zinc-500 italic">Eerste 15 tekens: <?= substr($token, 0, 15) ?>...</p>
                </div>
                <div>
                    <p class="text-blue-400 mb-1">Albums API Response (HTTP <?= $testAlbums['code'] ?>):</p>
                    <pre class="bg-black p-4 rounded-xl text-zinc-400 overflow-auto"><?= htmlspecialchars($testAlbums['raw']) ?></pre>
                </div>
                <?php if ($testAlbums['error']): ?>
                    <div class="text-red-500 italic">CURL Error: <?= $testAlbums['error'] ?></div>
                <?php endif; ?>
            </div>
        </details>

        <?php if (!empty($allAlbums)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($allAlbums as $album): ?>
                    <div class="bg-zinc-900 border border-zinc-800 rounded-[2.5rem] overflow-hidden group">
                        <div class="aspect-video relative">
                            <img src="<?= $album['coverPhotoBaseUrl'] ?>=w600-h400-c" class="w-full h-full object-cover opacity-60 group-hover:opacity-100 transition-opacity">
                            <div class="absolute bottom-4 left-6 bg-black/50 backdrop-blur-md px-3 py-1 rounded-full text-[9px] font-bold border border-white/10 uppercase">
                                <?= $album['mediaItemsCount'] ?? 0 ?> items
                            </div>
                        </div>
                        <div class="p-8">
                            <h3 class="text-lg font-bold truncate mb-6 italic uppercase tracking-tighter"><?= htmlspecialchars($album['title']) ?></h3>
                            <button class="w-full py-4 bg-white text-black rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-blue-600 hover:text-white transition-all">
                                Album Activeren
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="py-32 text-center bg-zinc-950 border-2 border-dashed border-zinc-900 rounded-[3rem]">
                <p class="text-zinc-600 text-lg italic mb-2">Er zijn geen albums zichtbaar.</p>
                <p class="text-zinc-800 text-[10px] uppercase font-bold tracking-widest">Controleer de technische analyse hierboven voor de foutcode.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>