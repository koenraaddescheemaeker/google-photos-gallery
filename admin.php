<?php
/**
 * FORCEKES - admin.php
 */
require_once 'config.php';

$token = getValidAccessToken();
$albums = [];
$errorMsg = null;

if (!$token) {
    $errorMsg = "Geen actieve verbinding met Google Photos.";
} else {
    $ch = curl_init("https://photoslibrary.googleapis.com/v1/albums?pageSize=50");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token", "Accept: application/json"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = json_decode(curl_exec($ch), true);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status === 200) {
        $albums = $res['albums'] ?? [];
    } else {
        $errorMsg = "Google weigert toegang (Fout $status). Mogelijk zijn de rechten verlopen.";
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Forcekes | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #000; }
    </style>
</head>
<body class="text-white p-8 md:p-16">
    <div class="max-w-6xl mx-auto">
        <header class="flex justify-between items-center mb-20">
            <h1 class="text-4xl font-black italic uppercase tracking-tighter text-blue-600">FORCEKES <span class="text-white opacity-20">/</span> ADMIN</h1>
            <a href="login.php?pw=admin123" class="text-[10px] font-bold uppercase tracking-[0.2em] text-zinc-500 hover:text-white transition-colors border-b border-zinc-800 pb-1">Verbinding Herstellen</a>
        </header>

        <?php if ($errorMsg): ?>
            <div class="bg-zinc-900 border border-zinc-800 rounded-[3rem] p-20 text-center shadow-2xl">
                <div class="w-16 h-16 bg-zinc-800 rounded-full flex items-center justify-center mx-auto mb-8">
                    <span class="text-2xl">🔑</span>
                </div>
                <h2 class="text-2xl font-bold mb-4 italic uppercase tracking-tight"><?= $errorMsg ?></h2>
                <p class="text-zinc-500 text-sm mb-10 max-w-md mx-auto italic leading-relaxed">De toegangstoken is ongeldig of de sessie is verlopen. Klik hieronder om opnieuw te koppelen.</p>
                <a href="login.php?pw=admin123" class="inline-block px-12 py-5 bg-blue-600 hover:bg-blue-500 text-white rounded-2xl font-black uppercase text-[11px] tracking-widest transition-all shadow-lg shadow-blue-900/40">Inloggen met Google</a>
            </div>
        <?php elseif (!empty($albums)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                <?php foreach ($albums as $album): ?>
                    <div class="group bg-zinc-900 border border-zinc-800 rounded-[2.5rem] overflow-hidden hover:border-blue-500/50 transition-all duration-500 shadow-xl">
                        <div class="aspect-video relative overflow-hidden">
                            <img src="<?= $album['coverPhotoBaseUrl'] ?>=w600-h400-c" class="w-full h-full object-cover opacity-50 group-hover:opacity-100 group-hover:scale-110 transition-all duration-700">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
                            <div class="absolute bottom-6 left-8">
                                <h3 class="text-xl font-bold italic uppercase tracking-tighter"><?= htmlspecialchars($album['title']) ?></h3>
                                <p class="text-[10px] text-zinc-400 font-bold uppercase tracking-widest mt-1"><?= $album['mediaItemsCount'] ?? 0 ?> foto's</p>
                            </div>
                        </div>
                        <div class="p-8">
                            <form action="save-selection.php" method="POST">
                                <input type="hidden" name="album_id" value="<?= $album['id'] ?>">
                                <button type="submit" class="w-full py-4 bg-zinc-800 hover:bg-white hover:text-black rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all">Album Activeren</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-20">
                <p class="text-zinc-500 italic">Geen albums gevonden in dit account.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>