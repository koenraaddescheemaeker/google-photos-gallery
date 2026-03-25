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

// Check scopes direct bij Google
$ch = curl_init("https://oauth2.googleapis.com/tokeninfo?access_token=" . $token);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$tokenInfo = json_decode(curl_exec($ch), true);
curl_close($ch);
$activeScopes = $tokenInfo['scope'] ?? 'GEEN SCOPES GEVONDEN';

function callPhotosAPI($endpoint, $token) {
    $ch = curl_init("https://photoslibrary.googleapis.com/v1/" . $endpoint);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['status' => $httpCode, 'json' => json_decode($response, true)];
}

$own = callPhotosAPI("albums?pageSize=50", $token);
$shared = callPhotosAPI("sharedAlbums?pageSize=50", $token);
$allAlbums = array_merge($own['json']['albums'] ?? [], $shared['json']['sharedAlbums'] ?? []);
?>
<!DOCTYPE html>
<html lang="nl">
<head><meta charset="UTF-8"><title>Admin</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-black text-white p-12">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl font-black italic text-blue-500 mb-8 uppercase">DIAGNOSTIEK</h1>
        <div class="bg-zinc-900 p-8 rounded-[2rem] border border-zinc-800 mb-8">
            <h2 class="text-xs font-bold uppercase text-zinc-500 mb-4 tracking-widest">Actieve Rechten</h2>
            <div class="bg-black p-4 rounded-xl text-[10px] font-mono text-blue-400 break-all mb-4"><?= $activeScopes ?></div>
            <?php if (strpos($activeScopes, 'photoslibrary') === false): ?>
                <p class="text-red-500 text-xs font-bold italic">❌ FOUT: Foto-rechten ontbreken in de token.</p>
            <?php else: ?>
                <p class="text-green-500 text-xs font-bold italic">✅ RECHTEN OK: Bibliotheek is toegankelijk.</p>
            <?php endif; ?>
        </div>
        
        <div class="grid grid-cols-2 gap-6">
            <?php foreach($allAlbums as $a): ?>
                <div class="bg-zinc-900 p-4 rounded-2xl border border-zinc-800">
                    <img src="<?= $a['coverPhotoBaseUrl'] ?>=w400-h300-c" class="rounded-xl mb-4 w-full h-32 object-cover">
                    <p class="font-bold text-sm truncate"><?= htmlspecialchars($a['title']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>