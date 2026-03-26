<?php
/**
 * FORCEKES - admin.php (Premium Grid)
 */
require_once 'config.php';
$token = getValidAccessToken();
$albums = [];

if ($token) {
    $ch = curl_init("https://photoslibrary.googleapis.com/v1/albums?pageSize=50");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = json_decode(curl_exec($ch), true);
    $albums = $res['albums'] ?? [];
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Forcekes | Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap'); body { font-family: 'Inter', sans-serif; background-color: #000; }</style>
</head>
<body class="bg-black text-white p-12">
    <div class="max-w-6xl mx-auto">
        <header class="flex justify-between items-center mb-20 border-b border-zinc-900 pb-8">
            <h1 class="text-4xl font-black italic uppercase text-blue-600">FORCEKES <span class="text-white opacity-20">/</span> ADMIN</h1>
            <a href="login.php" class="text-[10px] font-bold uppercase text-zinc-500 hover:text-white tracking-widest transition-all">Reset Sync</a>
        </header>

        <?php if (!empty($albums)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
                <?php foreach ($albums as $album): ?>
                    <div class="group bg-zinc-900 rounded-[3rem] border border-zinc-800 overflow-hidden hover:border-blue-500/50 transition-all duration-500 shadow-2xl">
                        <div class="aspect-video relative overflow-hidden">
                            <img src="<?= $album['coverPhotoBaseUrl'] ?>=w600-h450-c" class="w-full h-full object-cover opacity-50 group-hover:opacity-100 group-hover:scale-110 transition-all duration-700">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
                            <div class="absolute bottom-6 left-8">
                                <h3 class="text-xl font-bold italic uppercase tracking-tighter"><?= htmlspecialchars($album['title']) ?></h3>
                                <p class="text-[10px] text-zinc-400 font-bold uppercase tracking-widest mt-1"><?= $album['mediaItemsCount'] ?? 0 ?> foto's</p>
                            </div>
                        </div>
                        <div class="p-8">
                            <form action="save-selection.php" method="POST">
                                <input type="hidden" name="album_id" value="<?= $album['id'] ?>">
                                <input type="hidden" name="title" value="<?= htmlspecialchars($album['title']) ?>">
                                <button type="submit" class="w-full py-4 bg-zinc-800 hover:bg-blue-600 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all">Activeren voor Zwaaikamer</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-zinc-900 border border-zinc-800 rounded-[3rem] p-24 text-center">
                <p class="text-zinc-500 italic mb-10">Geen verbinding of geen albums gevonden.</p>
                <a href="login.php" class="px-12 py-5 bg-blue-600 rounded-2xl font-black uppercase text-[11px] tracking-widest">Opnieuw Koppelen</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>