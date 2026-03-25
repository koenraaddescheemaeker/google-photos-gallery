<?php
/**
 * FORCEKES - admin.php (Album Selector)
 */
require_once 'config.php';
$token = getValidAccessToken();

if (!$token) {
    header("Location: login.php?pw=admin123");
    exit;
}

function callPhotosAPI($endpoint, $token) {
    $ch = curl_init("https://photoslibrary.googleapis.com/v1/" . $endpoint);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = json_decode(curl_exec($ch), true);
    curl_close($ch);
    return $res;
}

// Haal eigen albums op
$ownRes = callPhotosAPI("albums?pageSize=50", $token);
$albums = $ownRes['albums'] ?? [];
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Forcekes | Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap'); body { font-family: 'Inter', sans-serif; background-color: #000; }</style>
</head>
<body class="bg-black text-white p-8">
    <div class="max-w-6xl mx-auto">
        <header class="flex justify-between items-center mb-12">
            <h1 class="text-4xl font-black italic uppercase text-blue-500">FORCEKES <span class="text-white">ADMIN</span></h1>
            <a href="login.php?pw=admin123" class="text-[10px] font-bold text-zinc-600 uppercase tracking-widest hover:text-white transition-colors">Opnieuw koppelen</a>
        </header>

        <?php if (!empty($albums)): ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php foreach ($albums as $album): ?>
                    <div class="bg-zinc-900 border border-zinc-800 rounded-[2.5rem] overflow-hidden group hover:border-blue-500 transition-all duration-500">
                        <div class="h-56 relative">
                            <img src="<?= $album['coverPhotoBaseUrl'] ?>=w600-h400-c" class="w-full h-full object-cover opacity-70 group-hover:opacity-100 transition-all">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
                            <div class="absolute bottom-6 left-8">
                                <h3 class="text-xl font-bold italic uppercase tracking-tighter"><?= htmlspecialchars($album['title']) ?></h3>
                                <p class="text-[10px] text-zinc-400 font-bold uppercase tracking-widest mt-1"><?= $album['mediaItemsCount'] ?? 0 ?> Foto's</p>
                            </div>
                        </div>
                        <div class="p-8">
                            <form action="save-selection.php" method="POST">
                                <input type="hidden" name="album_id" value="<?= $album['id'] ?>">
                                <input type="hidden" name="title" value="<?= htmlspecialchars($album['title']) ?>">
                                <button type="submit" class="w-full py-4 bg-zinc-800 hover:bg-blue-600 text-white rounded-2xl font-black text-[11px] uppercase tracking-widest transition-all">
                                    Dit album activeren
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-zinc-900 border-2 border-dashed border-zinc-800 rounded-[3rem] p-24 text-center">
                <h3 class="text-2xl font-bold text-zinc-500 mb-2 italic">Geen albums gevonden</h3>
                <p class="text-zinc-600 text-sm italic">Check of je albums hebt aangemaakt op photos.google.com</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>