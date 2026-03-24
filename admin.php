<?php
/**
 * FORCEKES ADMIN - Dashboard & Album Picker
 * Inclusief identiteitscontrole (Wie ben ik?)
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';

// 1. Token ophalen
$token = getValidAccessToken();

if (!$token) {
    // Geen token? Dan moet de admin opnieuw inloggen
    header("Location: login.php?pw=jouw_geheime_wachtwoord");
    exit;
}

/**
 * 2. Diagnose: Welk account is dit?
 */
$ch = curl_init("https://www.googleapis.com/oauth2/v3/userinfo");
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$userInfo = json_decode(curl_exec($ch), true);
curl_close($ch);

$userEmail = $userInfo['email'] ?? 'Onbekend account';

/**
 * 3. Albums ophalen (Eigen + Gedeeld)
 */
function fetchGooglePhotos($endpoint, $token) {
    $url = "https://photoslibrary.googleapis.com/v1/" . $endpoint;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true);
}

$ownRes    = fetchGooglePhotos("albums?pageSize=50", $token);
$sharedRes = fetchGooglePhotos("sharedAlbums?pageSize=50", $token);

// Voeg de lijsten samen
$rawAlbums = array_merge($ownRes['albums'] ?? [], $sharedRes['sharedAlbums'] ?? []);

// Ontdubbelen op ID
$allAlbums = [];
$seenIds = [];
foreach ($rawAlbums as $album) {
    if (!in_array($album['id'], $seenIds)) {
        $allAlbums[] = $album;
        $seenIds[] = $album['id'];
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forcekes Admin | Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #000; color: #fff; }
        .album-card { transition: all 0.3s ease; }
        .album-card:hover { border-color: #3b82f6; transform: translateY(-4px); }
    </style>
</head>
<body class="min-h-screen p-6 md:p-12">

    <div class="max-w-7xl mx-auto">
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-12 gap-6">
            <div>
                <h1 class="text-5xl font-black italic uppercase tracking-tighter text-blue-500">FORCEKES <span class="text-white">ADMIN</span></h1>
                <p class="text-zinc-500 mt-2">Beheer je Google Photos mappen en kookboek.</p>
            </div>
            
            <div class="bg-zinc-900 border border-zinc-800 p-4 rounded-3xl flex items-center gap-4">
                <div class="text-right">
                    <p class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest">Verbonden als</p>
                    <p class="text-sm font-bold text-blue-400"><?= htmlspecialchars($userEmail) ?></p>
                </div>
                <a href="login.php?pw=jouw_geheime_wachtwoord" class="bg-zinc-800 hover:bg-zinc-700 p-2 rounded-2xl transition-colors" title="Wissel van account">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </a>
            </div>
        </header>

        <?php if (!empty($allAlbums)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($allAlbums as $album): ?>
                    <div class="album-card bg-zinc-900 border border-zinc-800 rounded-[2.5rem] overflow-hidden group">
                        <div class="h-56 relative">
                            <img src="<?= $album['coverPhotoBaseUrl'] ?>=w600-h400-c" class="w-full h-full object-cover opacity-60 group-hover:opacity-100 transition-opacity duration-500">
                            <div class="absolute bottom-4 left-6">
                                <span class="bg-black/60 backdrop-blur-md px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest border border-white/10">
                                    <?= $album['mediaItemsCount'] ?? 0 ?> foto's
                                </span>
                            </div>
                        </div>
                        <div class="p-8">
                            <h3 class="text-xl font-bold mb-4 truncate"><?= htmlspecialchars($album['title']) ?></h3>
                            <button class="w-full py-4 bg-zinc-800 group-hover:bg-blue-600 rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] transition-all">
                                Album Selecteren
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-zinc-950 border-2 border-dashed border-zinc-900 rounded-[3rem] p-20 text-center">
                <h3 class="text-2xl font-bold text-zinc-500 mb-2">Geen albums gevonden</h3>
                <p class="text-zinc-600 mb-8 max-w-md mx-auto">Google zegt dat er geen albums zijn voor <strong><?= $userEmail ?></strong>. Controleer of je albums hebt aangemaakt in de Google Photos app.</p>
                
                <div class="bg-red-950/20 border border-red-900/50 p-6 rounded-2xl text-left inline-block max-w-full overflow-auto">
                    <p class="text-[10px] font-bold text-red-500 uppercase mb-2">Systeem Status</p>
                    <pre class="text-[10px] text-zinc-500"><?php 
                        print_r([
                            'eigen_count' => count($ownRes['albums'] ?? []),
                            'shared_count' => count($sharedRes['sharedAlbums'] ?? []),
                            'errors' => [
                                'own' => $ownRes['error'] ?? 'geen',
                                'shared' => $sharedRes['error'] ?? 'geen'
                            ]
                        ]); 
                    ?></pre>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <footer class="mt-20 py-12 border-t border-zinc-900 flex justify-between items-center text-zinc-600">
        <p class="text-[10px] font-bold uppercase tracking-[0.3em]">&copy; 2026 FORCEKES</p>
        <div class="flex gap-8">
            <a href="zwaaikamer.php" class="text-[10px] font-bold uppercase tracking-widest hover:text-blue-500 transition-colors">Zwaaikamer</a>
            <a href="test.php" class="text-[10px] font-bold uppercase tracking-widest hover:text-white transition-colors">Systeem Test</a>
        </div>
    </footer>

</body>
</html>