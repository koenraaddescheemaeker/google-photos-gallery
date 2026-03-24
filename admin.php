<?php
/**
 * FORCEKES - admin.php (Expert Diagnostic Edition)
 */
require_once 'config.php';
$token = getValidAccessToken();

if (!$token) {
    header("Location: login.php?pw=admin123");
    exit;
}

// 1. Check scopes direct bij Google
$ch = curl_init("https://oauth2.googleapis.com/tokeninfo?access_token=" . $token);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$tokenInfo = json_decode(curl_exec($ch), true);
curl_close($ch);

$activeScopes = $tokenInfo['scope'] ?? 'GEEN SCOPES GEVONDEN';

// 2. Photos API Call Helper
function callPhotosAPI($endpoint, $token) {
    $ch = curl_init("https://photoslibrary.googleapis.com/v1/" . $endpoint);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['status' => $httpCode, 'json' => json_decode($response, true), 'raw' => $response];
}

$own = callPhotosAPI("albums?pageSize=50", $token);
$shared = callPhotosAPI("sharedAlbums?pageSize=50", $token);

$allAlbums = array_merge($own['json']['albums'] ?? [], $shared['json']['sharedAlbums'] ?? []);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Forcekes | Admin Expert</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap'); body { font-family: 'Inter', sans-serif; background:#000; }</style>
</head>
<body class="text-white p-8">
    <div class="max-w-6xl mx-auto">
        <header class="flex justify-between items-center mb-12 border-b border-zinc-900 pb-8">
            <h1 class="text-4xl font-black italic uppercase text-blue-500 italic">FORCEKES <span class="text-white">ADMIN</span></h1>
            <div class="text-right">
                <p class="text-[10px] text-zinc-500 uppercase font-bold tracking-widest">Systeem Status</p>
                <div class="flex gap-2 mt-1">
                    <span class="w-3 h-3 rounded-full bg-green-500 animate-pulse"></span>
                    <span class="text-xs font-mono text-zinc-400">API CONNECTED</span>
                </div>
            </div>
        </header>

        <div class="bg-zinc-900 border border-blue-500/30 p-8 rounded-[2.5rem] mb-10">
            <h2 class="text-xs font-black uppercase tracking-[0.2em] text-blue-400 mb-4">Actieve Token Scopes</h2>
            <div class="bg-black/50 p-4 rounded-xl font-mono text-[10px] text-zinc-400 break-all mb-4">
                <?= $activeScopes ?>
            </div>
            <?php if (strpos($activeScopes, 'photoslibrary') !== false): ?>
                <div class="flex items-center gap-2 text-green-500 text-xs font-bold uppercase italic">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    Rechten zijn succesvol geactiveerd!
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($allAlbums)): ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php foreach ($allAlbums as $album): ?>
                    <div class="bg-zinc-900 border border-zinc-800 rounded-[2.5rem] overflow-hidden group hover:border-blue-500 transition-all duration-500">
                        <div class="h-48 relative">
                            <img src="<?= $album['coverPhotoBaseUrl'] ?>=w600-h400-c" class="w-full h-full object-cover opacity-60 group-hover:opacity-100 transition-all duration-700">
                            <div class="absolute bottom-4 left-6">
                                <span class="bg-black/60 backdrop-blur px-3 py-1 rounded-full text-[10px] font-bold border border-white/10 italic">
                                    <?= $album['mediaItemsCount'] ?? 0 ?> items
                                </span>
                            </div>
                        </div>
                        <div class="p-8">
                            <h3 class="text-xl font-bold truncate mb-6"><?= htmlspecialchars($album['title']) ?></h3>
                            <button class="w-full py-4 bg-zinc-800 hover:bg-blue-600 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all">Selecteren</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-zinc-950 border-2 border-dashed border-zinc-900 rounded-[3rem] p-16 text-center">
                <h3 class="text-2xl font-bold text-zinc-500 mb-4 italic">Geen albums gevonden</h3>
                <p class="text-zinc-600 text-sm max-w-md mx-auto mb-10 text-balance">
                    We hebben verbinding, maar Google geeft 0 albums terug. Check of je albums hebt aangemaakt op <strong>photos.google.com</strong>.
                </p>

                <div class="text-left bg-zinc-900 p-8 rounded-3xl border border-zinc-800 max-w-2xl mx-auto">
                    <h4 class="text-[10px] font-bold text-blue-500 uppercase mb-4 tracking-widest">Raw API Response (Debug)</h4>
                    <div class="space-y-4 font-mono text-[10px] text-zinc-500">
                        <div>
                            <p class="text-zinc-400 mb-1 font-bold italic">Eigen Albums (HTTP <?= $own['status'] ?>):</p>
                            <pre class="bg-black p-3 rounded-lg overflow-auto"><?php print_r($own['json']); ?></pre>
                        </div>
                        <div>
                            <p class="text-zinc-400 mb-1 font-bold italic">Gedeelde Albums (HTTP <?= $shared['status'] ?>):</p>
                            <pre class="bg-black p-3 rounded-lg overflow-auto"><?php print_r($shared['json']); ?></pre>
                        </div>
                    </div>
                </div>
                <a href="login.php?pw=admin123" class="inline-block mt-10 text-[10px] font-bold uppercase tracking-widest text-zinc-700 hover:text-white transition-colors underline decoration-zinc-800 underline-offset-8">Opnieuw inloggen</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>