<?php
/**
 * FORCEKES - admin.php (Verification Edition)
 */
require_once 'config.php';
$token = trim(getValidAccessToken());

// Check de token bij Google
$ch = curl_init("https://oauth2.googleapis.com/tokeninfo?access_token=" . $token);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$info = json_decode(curl_exec($ch), true);
curl_close($ch);

$activeScopes = $info['scope'] ?? '';

// API Call Helper
function callPhotosAPI($endpoint, $token) {
    $ch = curl_init("https://photoslibrary.googleapis.com/v1/" . $endpoint);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'data' => json_decode($res, true)];
}

$res = callPhotosAPI("albums?pageSize=50", $token);
?>
<!DOCTYPE html>
<html lang="nl">
<head><meta charset="UTF-8"><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-black text-white p-12">
    <h1 class="text-4xl font-black mb-8 italic uppercase text-blue-500 italic">Admin <span class="text-white">Panel</span></h1>
    
    <div class="bg-zinc-900 p-6 rounded-2xl mb-8 border border-zinc-800">
        <p class="text-[10px] uppercase font-bold text-zinc-500 mb-2">Gedetecteerde Scopes:</p>
        <code class="text-green-400 text-xs break-all"><?= $activeScopes ?></code>
    </div>

    <?php if ($res['code'] == 200 && !empty($res['data']['albums'])): ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php foreach ($res['data']['albums'] as $album): ?>
                <div class="bg-zinc-900 border border-zinc-800 rounded-[2.5rem] overflow-hidden">
                    <img src="<?= $album['coverPhotoBaseUrl'] ?>=w600-h400-c" class="w-full h-48 object-cover">
                    <div class="p-8">
                        <h3 class="font-bold uppercase italic tracking-tighter"><?= $album['title'] ?></h3>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="p-20 bg-zinc-950 border-2 border-dashed border-zinc-900 rounded-[3rem] text-center">
            <p class="text-zinc-600 italic">Status: <?= $res['code'] ?> - Nog steeds geen albums.</p>
            <p class="text-[10px] text-zinc-800 mt-4 uppercase">Check of 'photoslibrary' (zonder .readonly) in de groene balk hierboven staat.</p>
        </div>
    <?php endif; ?>
</body>
</html>