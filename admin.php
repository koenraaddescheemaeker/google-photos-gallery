<?php
/**
 * FORCEKES - admin.php (Final Diagnostic)
 */
require_once 'config.php';
$token = getValidAccessToken();

if (!$token) { header("Location: login.php?pw=admin123"); exit; }

// Check scopes bij de officiële Google endpoint
$ch = curl_init("https://oauth2.googleapis.com/tokeninfo?access_token=" . $token);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$tokenInfo = json_decode(curl_exec($ch), true);
$activeScopes = $tokenInfo['scope'] ?? 'GEEN SCOPES';

function callPhotosAPI($endpoint, $token) {
    $ch = curl_init("https://photoslibrary.googleapis.com/v1/" . $endpoint);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = json_decode(curl_exec($ch), true);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'res' => $res];
}

$own = callPhotosAPI("albums?pageSize=50", $token);
$shared = callPhotosAPI("sharedAlbums?pageSize=50", $token);
$all = array_merge($own['res']['albums'] ?? [], $shared['res']['sharedAlbums'] ?? []);
?>
<!DOCTYPE html>
<html lang="nl">
<head><meta charset="UTF-8"><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-black text-white p-12 font-sans">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl font-black italic text-blue-500 mb-8 uppercase">DIAGNOSTIEK</h1>
        
        <div class="bg-zinc-900 p-8 rounded-[2rem] border border-zinc-800 mb-8">
            <h2 class="text-xs font-bold uppercase text-zinc-500 mb-4 tracking-widest">Actieve Rechten van Google</h2>
            <div class="bg-black p-4 rounded-xl text-[10px] font-mono text-blue-400 break-all"><?= $activeScopes ?></div>
            
            <?php if(strpos($activeScopes, 'photoslibrary') === false): ?>
                <div class="mt-6 p-4 bg-red-900/20 border border-red-500 rounded-xl text-red-500 text-xs font-bold">
                    ❌ FOUT: Google geeft de foto-rechten niet door. <br><br>
                    Oplossing: Voeg 'koenraad.descheemaeker@gmail.com' toe aan de 'Test Users' in je Google Cloud Console!
                </div>
            <?php else: ?>
                <div class="mt-6 p-4 bg-green-900/20 border border-green-500 rounded-xl text-green-500 text-xs font-bold">
                    ✅ RECHTEN OK: We hebben toegang tot de bibliotheek.
                </div>
            <?php endif; ?>
        </div>

        <?php if(!empty($all)): ?>
            <div class="grid grid-cols-2 gap-6">
                <?php foreach($all as $a): ?>
                    <div class="bg-zinc-900 p-4 rounded-2xl border border-zinc-800">
                        <img src="<?= $a['coverPhotoBaseUrl'] ?>=w400-h300-c" class="rounded-xl mb-4 w-full h-32 object-cover">
                        <p class="font-bold text-sm truncate"><?= htmlspecialchars($a['title']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-zinc-600 italic">Geen albums gevonden (of nog steeds 403).</p>
        <?php endif; ?>
    </div>
</body>
</html>