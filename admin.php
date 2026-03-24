<?php
/**
 * FORCEKES ADMIN - Deep Scan & Selector
 */
require_once 'config.php';
$token = getValidAccessToken();

if (!$token) {
    header("Location: login.php?pw=skoen123");
    exit;
}

// 1. Haal ALLES op met extra foutopsporing
function googleRequest($endpoint, $token) {
    $ch = curl_init("https://photoslibrary.googleapis.com/v1/" . $endpoint);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true);
}

$ownRes = googleRequest("albums?pageSize=50", $token);
$sharedRes = googleRequest("sharedAlbums?pageSize=50", $token);

$allAlbums = array_merge($ownRes['albums'] ?? [], $resShared['sharedAlbums'] ?? []);

// Voor de zekerheid: als er echt NIKS is, laten we de ruime API response zien aan de admin
$debugRaw = [
    'eigen_albums_count' => count($ownRes['albums'] ?? []),
    'gedeelde_albums_count' => count($sharedRes['sharedAlbums'] ?? []),
    'google_foutmelding_eigen' => $ownRes['error'] ?? 'geen',
    'google_foutmelding_gedeeld' => $sharedRes['error'] ?? 'geen'
];
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Forcekes Admin | Kies Mappen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #000; color: #fff; }
    </style>
</head>
<body class="p-8">
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-end mb-12">
            <div>
                <h1 class="text-5xl font-black italic uppercase text-blue-500">Kies je mappen</h1>
                <p class="text-zinc-500 mt-2">Selecteer welke Google Photos albums in je kookboek verschijnen.</p>
            </div>
            <div class="text-right">
                <span class="text-[10px] text-zinc-600 block mb-1">DEBUG INFO</span>
                <code class="text-[10px] bg-zinc-900 p-2 rounded">Own: <?= $debugRaw['eigen_albums_count'] ?> | Shared: <?= $debugRaw['gedeelde_albums_count'] ?></code>
            </div>
        </div>

        <?php if (!empty($allAlbums)): ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php foreach ($allAlbums as $album): ?>
                    <div class="bg-zinc-900 border border-zinc-800 rounded-[2.5rem] overflow-hidden group hover:border-blue-500 transition-all">
                        <div class="h-48 relative">
                            <img src="<?= $album['coverPhotoBaseUrl'] ?>=w600-h400-c" class="w-full h-full object-cover opacity-60 group-hover:opacity-100 transition-all">
                            <div class="absolute bottom-4 left-4">
                                <span class="bg-black/50 backdrop-blur px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest">
                                    <?= $album['mediaItemsCount'] ?? 0 ?> foto's
                                </span>
                            </div>
                        </div>
                        <div class="p-6">
                            <h3 class="font-bold text-lg mb-4 truncate"><?= htmlspecialchars($album['title']) ?></h3>
                            <form method="POST" action="save-selection.php">
                                <input type="hidden" name="album_id" value="<?= $album['id'] ?>">
                                <input type="hidden" name="album_title" value="<?= htmlspecialchars($album['title']) ?>">
                                <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-500 rounded-2xl font-black text-xs uppercase tracking-tighter transition-all">
                                    Toevoegen aan kookboek
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-zinc-900 border-2 border-dashed border-zinc-800 p-20 rounded-[3rem] text-center">
                <p class="text-xl text-zinc-500 italic mb-4">Geen albums gevonden in dit Google account.</p>
                <div class="flex justify-center gap-4">
                    <a href="login.php?pw=jouw_geheime_wachtwoord" class="text-blue-500 underline text-sm">Opnieuw koppelen</a>
                    <span class="text-zinc-700">|</span>
                    <p class="text-zinc-500 text-sm">Check of je albums in de Google Photos app op 'Gedeeld' staan.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>