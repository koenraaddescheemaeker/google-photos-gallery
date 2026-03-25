<?php
/**
 * FORCEKES - admin.php
 */
require_once 'config.php';
$token = getValidAccessToken();

if (!$token) {
    header("Location: login.php?pw=admin123");
    exit;
}

// 1. Check scopes voor de diagnostiek balk
$ch = curl_init("https://oauth2.googleapis.com/tokeninfo?access_token=" . $token);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$tokenInfo = json_decode(curl_exec($ch), true);
curl_close($ch);
$activeScopes = $tokenInfo['scope'] ?? 'GEEN SCOPES GEVONDEN';

// 2. API Call Helper
function callPhotosAPI($endpoint, $token) {
    $ch = curl_init("https://photoslibrary.googleapis.com/v1/" . $endpoint);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['status' => $status, 'json' => json_decode($response, true), 'raw' => $response];
}

// Haal albums en gedeelde albums op
$ownRes = callPhotosAPI("albums?pageSize=50", $token);
$sharedRes = callPhotosAPI("sharedAlbums?pageSize=50", $token);

$allAlbums = array_merge($ownRes['json']['albums'] ?? [], $sharedRes['json']['sharedAlbums'] ?? []);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Forcekes | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap'); body { font-family: 'Inter', sans-serif; background-color: #000; }</style>
</head>
<body class="bg-black text-white p-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-4xl font-black italic uppercase text-blue-500 mb-8 italic">FORCEKES <span class="text-white">DASHBOARD</span></h1>

        <div class="bg-zinc-900 border border-zinc-800 p-6 rounded-[2rem] mb-10">
            <h2 class="text-[10px] font-bold uppercase text-zinc-500 mb-3 tracking-widest">Actieve Rechten</h2>
            <div class="bg-black p-4 rounded-xl text-[10px] font-mono text-blue-400 break-all mb-4"><?= $activeScopes ?></div>
            <?php if (strpos($activeScopes, 'photoslibrary') !== false): ?>
                <p class="text-green-500 text-xs font-bold italic">✅ RECHTEN OK: Bibliotheek is toegankelijk.</p>
            <?php else: ?>
                <p class="text-red-500 text-xs font-bold italic">❌ FOUT: Foto-rechten ontbreken.</p>
            <?php endif; ?>
        </div>

        <?php if (!empty($allAlbums)): ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php foreach ($allAlbums as $album): ?>
                    <div class="bg-zinc-900 border border-zinc-800 rounded-[2.5rem] overflow-hidden group hover:border-blue-500 transition-all duration-500">
                        <img src="<?= $album['coverPhotoBaseUrl'] ?>=w600-h400-c" class="w-full h-48 object-cover opacity-60 group-hover:opacity-100 transition-all">
                        <div class="p-8">
                            <h3 class="text-xl font-bold truncate mb-2 italic uppercase tracking-tighter"><?= htmlspecialchars($album['title']) ?></h3>
                            <p class="text-[10px] text-zinc-500 font-bold mb-6 uppercase"><?= $album['mediaItemsCount'] ?? 0 ?> Items</p>
                            <form action="save-selection.php" method="POST">
                                <input type="hidden" name="album_id" value="<?= $album['id'] ?>">
                                <input type="hidden" name="title" value="<?= htmlspecialchars($album['title']) ?>">
                                <button type="submit" class="w-full py-4 bg-zinc-800 hover:bg-blue-600 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all">Selecteren</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-zinc-950 border-2 border-dashed border-zinc-900 rounded-[3rem] p-20 text-center">
                <h3 class="text-xl font-bold text-zinc-600 mb-4 italic">Geen albums gevonden</h3>
                
                <div class="bg-zinc-900 p-6 rounded-2xl text-left inline-block max-w-full text-[10px] font-mono text-zinc-500">
                    <p class="text-blue-500 mb-2 font-bold uppercase">Raw API Debug:</p>
                    <p>HTTP Status Eigen: <?= $ownRes['status'] ?></p>
                    <p>Response Eigen: <?= htmlspecialchars($ownRes['raw']) ?></p>
                    <hr class="my-3 border-zinc-800">
                    <p>HTTP Status Gedeeld: <?= $sharedRes['status'] ?></p>
                    <p>Response Gedeeld: <?= htmlspecialchars($sharedRes['raw']) ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>