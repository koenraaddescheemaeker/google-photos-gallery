<?php
/**
 * FORCEKES - admin.php (Diagnostic Dashboard)
 */
require_once 'config.php';

$token = getValidAccessToken();

if (!$token) {
    header("Location: login.php?pw=admin123");
    exit;
}

// 1. Controleer de scopes van de huidige token direct bij Google
$ch = curl_init("https://www.googleapis.com/oauth2/v1/tokeninfo?access_token=" . $token);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$tokenInfo = json_decode(curl_exec($ch), true);
curl_close($ch);

$activeScopes = $tokenInfo['scope'] ?? 'GEEN SCOPES GEVONDEN';

// 2. Help-functie voor Google Photos API
function callPhotosAPI($endpoint, $accessToken) {
    $ch = curl_init("https://photoslibrary.googleapis.com/v1/" . $endpoint);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $accessToken"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, curl_getinfo($ch, CURLINFO_HTTP_CODE));
    curl_close($ch);
    return ['status' => $status, 'data' => json_decode($response, true)];
}

$ownRes = callPhotosAPI("albums?pageSize=50", $token);
$sharedRes = callPhotosAPI("sharedAlbums?pageSize=50", $token);
$allAlbums = array_merge($ownRes['data']['albums'] ?? [], $sharedRes['data']['sharedAlbums'] ?? []);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Forcekes | Admin Diagnostic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap'); body { font-family: 'Inter', sans-serif; background:#000; }</style>
</head>
<body class="text-white p-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-5xl font-black italic uppercase text-blue-500 mb-12 italic">FORCEKES <span class="text-white">DIAGNOSTIC</span></h1>

        <div class="bg-zinc-900 border border-blue-500/50 p-6 rounded-[2rem] mb-12">
            <h4 class="text-blue-400 font-bold text-xs uppercase tracking-widest mb-3">Actieve Rechten (Scopes)</h4>
            <div class="bg-black/50 p-4 rounded-xl font-mono text-[11px] text-zinc-400 leading-relaxed break-all">
                <?= $activeScopes ?>
            </div>
            <?php if (strpos($activeScopes, 'photoslibrary') === false): ?>
                <p class="text-red-500 text-xs font-bold mt-4 animate-pulse">⚠️ WAARSCHUWING: De Photos Library scope ontbreekt!</p>
            <?php else: ?>
                <p class="text-green-500 text-xs font-bold mt-4">✅ Photos Library scope is actief.</p>
            <?php endif; ?>
        </div>

        <?php if (!empty($allAlbums)): ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php foreach ($allAlbums as $album): ?>
                    <div class="bg-zinc-900 border border-zinc-800 rounded-[2.5rem] overflow-hidden group hover:border-blue-500 transition-all">
                        <img src="<?= $album['coverPhotoBaseUrl'] ?>=w600-h400-c" class="w-full h-48 object-cover opacity-60">
                        <div class="p-6">
                            <h3 class="font-bold truncate"><?= htmlspecialchars($album['title']) ?></h3>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-zinc-950 border-2 border-dashed border-zinc-900 rounded-[3rem] p-20 text-center">
                <h3 class="text-2xl font-bold text-zinc-500 mb-4">Status: Geen albums zichtbaar</h3>
                <div class="bg-zinc-900 p-6 rounded-2xl text-left inline-block max-w-full text-[10px] font-mono text-zinc-500">
                    <p>Google HTTP Status (Eigen): <?= $ownRes['status'] ?></p>
                    <p>Google HTTP Status (Gedeeld): <?= $sharedRes['status'] ?></p>
                    <hr class="my-4 border-zinc-800">
                    <?php print_r(['error_own' => $ownRes['data']['error'] ?? 'none', 'error_shared' => $sharedRes['data']['error'] ?? 'none']); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>