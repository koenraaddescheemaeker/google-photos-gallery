<?php
/**
 * FORCEKES PORTAAL - Admin Dashboard
 * Premium Album Overzicht
 */
require_once 'config.php';

// 1. Validatie van de sessie/token
$token = getValidAccessToken();

if (!$token) {
    // Geen geldige token? Terug naar de login-fixer
    header("Location: login.php?pw=jouw_geheime_wachtwoord");
    exit;
}

/**
 * Helper om API calls te doen naar Google Photos
 */
function fetchFromGoogle($endpoint, $token) {
    $ch = curl_init("https://photoslibrary.googleapis.com/v1/" . $endpoint);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// 2. Haal data op (Beide bronnen voor maximale dekking)
$resAlbums = fetchFromGoogle("albums?pageSize=50", $token);
$resShared = fetchFromGoogle("sharedAlbums?pageSize=50", $token);

// 3. Samenvoegen en ontdubbelen
$rawList = array_merge($resAlbums['albums'] ?? [], $resShared['sharedAlbums'] ?? []);

// Filter op unieke ID's om dubbele kaarten te voorkomen
$allAlbums = [];
$seenIds = [];
foreach ($rawList as $album) {
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
    <title>Forcekes | Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #000; }
        .premium-card { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        .premium-card:hover { transform: translateY(-8px); border-color: #3b82f6; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 10px 10px -5px rgba(59, 130, 246, 0.1); }
    </style>
</head>
<body class="text-zinc-100 min-h-screen">

    <header class="p-8 border-b border-zinc-900 bg-black/50 backdrop-blur-xl sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <h1 class="text-3xl font-black italic uppercase tracking-tighter">
                FORCEKES <span class="text-blue-500">ADMIN</span>
            </h1>
            <nav class="flex gap-6">
                <a href="zwaaikamer.php" class="text-xs font-bold uppercase tracking-widest text-zinc-400 hover:text-white transition-colors">Zwaaikamer</a>
                <a href="login.php?pw=jouw_geheime_wachtwoord" class="text-[10px] border border-zinc-800 px-4 py-2 rounded-full hover:bg-zinc-900 transition-all uppercase tracking-widest text-zinc-500">Re-connect</a>
            </nav>
        </div>
    </header>

    <main class="max-w-7xl mx-auto p-8">
        <div class="mb-12">
            <h2 class="text-zinc-500 font-medium uppercase tracking-[0.3em] text-xs mb-2">Google Photos Library</h2>
            <h3 class="text-4xl font-bold">Kies je mappen</h3>
        </div>

        <?php if (!empty($allAlbums)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                <?php foreach ($allAlbums as $album): ?>
                    <div class="premium-card bg-zinc-900/50 border border-zinc-800 rounded-[2.5rem] overflow-hidden group">
                        <div class="relative h-64 overflow-hidden">
                            <img src="<?= $album['coverPhotoBaseUrl'] ?>=w800-h600-c" 
                                 alt="<?= htmlspecialchars($album['title']) ?>" 
                                 class="w-full h-full object-cover opacity-60 group-hover:opacity-100 group-hover:scale-110 transition-all duration-700">
                            <div class="absolute inset-0 bg-gradient-to-t from-zinc-950 to-transparent opacity-80"></div>
                            
                            <?php if (isset($album['shareInfo'])): ?>
                                <span class="absolute top-6 left-6 bg-blue-500 text-[10px] font-black uppercase px-3 py-1 rounded-full">Gedeeld</span>
                            <?php endif; ?>
                        </div>

                        <div class="p-8">
                            <h4 class="text-xl font-bold mb-1 truncate"><?= htmlspecialchars($album['title'] ?? 'Naamloos Album') ?></h4>
                            <p class="text-zinc-500 text-sm italic mb-6"><?= $album['mediaItemsCount'] ?? 0 ?> items gevonden</p>
                            
                            <button class="w-full py-4 bg-zinc-800 group-hover:bg-blue-600 rounded-2xl font-bold text-xs uppercase tracking-widest transition-colors duration-300">
                                Album Selecteren
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="py-32 text-center border-2 border-dashed border-zinc-900 rounded-[3rem]">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-zinc-900 rounded-full mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#3f3f46" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                </div>
                <h4 class="text-xl font-bold text-zinc-400">Nog geen albums zichtbaar</h4>
                <p class="text-zinc-600 max-w-sm mx-auto mt-2">Zorg dat je in Google Photos minstens één album hebt aangemaakt of gedeeld.</p>
            </div>
        <?php endif; ?>
    </main>

    <footer class="p-12 text-center text-zinc-700 text-[10px] uppercase tracking-[0.5em]">
        &copy; <?= date('Y') ?> Forcekes Portaal &bull; Built with React & Supabase
    </footer>

</body>
</html>