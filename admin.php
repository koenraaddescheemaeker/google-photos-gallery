<?php
/**
 * FORCEKES - admin.php (The Premium Dashboard)
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
    $response = curl_exec($ch);
    return json_decode($response, true);
}

$res = callPhotosAPI("albums?pageSize=50", $token);
$albums = $res['albums'] ?? [];
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
        <header class="flex justify-between items-center mb-16">
            <h1 class="text-4xl font-black italic uppercase text-blue-600 tracking-tighter">FORCEKES <span class="text-white font-normal opacity-20">/</span> ADMIN</h1>
            <div class="flex items-center gap-4">
                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                <span class="text-[10px] font-bold uppercase tracking-widest text-zinc-500">Live Connection</span>
            </div>
        </header>

        <?php if (!empty($albums)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                <?php foreach ($albums as $album): ?>
                    <div class="group relative bg-zinc-900 rounded-[2.5rem] border border-zinc-800 overflow-hidden hover:border-blue-500/50 transition-all duration-500">
                        <div class="aspect-[4/3] overflow-hidden">
                            <img src="<?= $album['coverPhotoBaseUrl'] ?>=w600-h450-c" class="w-full h-full object-cover opacity-60 group-hover:opacity-100 group-hover:scale-110 transition-all duration-700">
                        </div>
                        <div class="p-8">
                            <h3 class="text-xl font-bold italic uppercase tracking-tighter mb-1"><?= htmlspecialchars($album['title']) ?></h3>
                            <p class="text-[10px] text-zinc-500 font-bold uppercase tracking-widest mb-6"><?= $album['mediaItemsCount'] ?? 0 ?> Media Items</p>
                            
                            <form action="save-selection.php" method="POST">
                                <input type="hidden" name="album_id" value="<?= $album['id'] ?>">
                                <input type="hidden" name="title" value="<?= htmlspecialchars($album['title']) ?>">
                                <button type="submit" class="w-full py-4 bg-white text-black hover:bg-blue-600 hover:text-white rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all">
                                    Album Activeren
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-zinc-950 border-2 border-dashed border-zinc-900 rounded-[3rem] p-24 text-center">
                <p class="text-zinc-600 italic text-lg">Geen albums gevonden of toegang geweigerd.</p>
                <a href="login.php?pw=admin123" class="inline-block mt-6 text-blue-500 font-bold uppercase text-[10px] tracking-widest hover:text-white transition-colors underline underline-offset-8">Koppeling herstellen</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>