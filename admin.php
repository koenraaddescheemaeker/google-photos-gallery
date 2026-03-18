<?php
/**
 * admin.php - Premium Album Selector
 */
require_once 'config.php';

// 1. Controleer toegang (Olifant-check)
$token = getValidAccessToken();
if (!$token) {
    header('Location: google-auth.php');
    exit;
}

// 2. Afhandelen van album selectie (Sluis van de bever)
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['album_id'])) {
    $albumId = $_POST['album_id'];
    $albumTitle = $_POST['album_title'] ?? 'Geselecteerd album';
    
    $res = supabaseRequest('google_tokens?id=eq.1', 'PATCH', [
        'active_album_id'    => $albumId,
        'active_album_title' => $albumTitle
    ]);

    if (!isset($res['error'])) {
        $message = "✅ Album succesvol gekoppeld aan de homepage!";
    } else {
        $message = "❌ Fout bij opslaan: " . print_r($res['error'], true);
    }
}

// 3. Albums ophalen bij Google (Arend-blik)
$ch = curl_init("https://photoslibrary.googleapis.com/v1/albums?pageSize=50");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = json_decode(curl_exec($ch), true);
curl_close($ch);

$albums = $response['albums'] ?? [];

// 4. Huidig actief album ophalen uit de DB
$dbRes = supabaseRequest('google_tokens?id=eq.1&select=active_album_id');
$currentActiveId = $dbRes[0]['active_album_id'] ?? null;
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Familie Forcekes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .premium-gradient { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); }
        .album-card:hover { transform: translateY(-4px); }
    </style>
</head>
<body class="bg-slate-950 text-slate-200 font-sans min-h-screen">

    <header class="border-b border-slate-800 bg-slate-900/50 backdrop-blur-md sticky top-0 z-10">
        <div class="max-w-6xl mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-xl font-bold tracking-tight text-white">Familie <span class="text-blue-500">Forcekes</span> Admin</h1>
            <div class="flex items-center gap-4">
                <a href="index.php" class="text-sm text-slate-400 hover:text-white transition">Bekijk Site</a>
                <div class="h-8 w-8 rounded-full bg-blue-600 flex items-center justify-center text-xs font-bold text-white">KD</div>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-6 py-12">
        
        <?php if ($message): ?>
            <div class="mb-8 p-4 rounded-2xl bg-blue-500/10 border border-blue-500/50 text-blue-400 animate-pulse">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="mb-12">
            <h2 class="text-3xl font-bold text-white mb-2">Kies je Herinneringen</h2>
            <p class="text-slate-400">Selecteer welk Google Photos album op de homepage moet verschijnen.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (empty($albums)): ?>
                <p class="text-yellow-500 bg-yellow-500/10 p-4 rounded-xl border border-yellow-500/50">Geen albums gevonden in je Google Photos account.</p>
            <?php else: ?>
                <?php foreach ($albums as $album): 
                    $isActive = ($album['id'] === $currentActiveId);
                ?>
                    <div class="album-card transition-all duration-300 bg-slate-900 border <?php echo $isActive ? 'border-blue-500 shadow-lg shadow-blue-500/20' : 'border-slate-800'; ?> rounded-3xl overflow-hidden flex flex-col">
                        <div class="relative h-48 bg-slate-800 overflow-hidden">
                            <img src="<?php echo $album['coverPhotoBaseUrl']; ?>=w500-h500-c" alt="" class="w-full h-full object-cover opacity-80">
                            <?php if ($isActive): ?>
                                <span class="absolute top-4 right-4 bg-blue-500 text-white text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-widest">Actief</span>
                            <?php endif; ?>
                        </div>
                        <div class="p-6 flex-grow flex flex-col justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-white mb-1 truncate"><?php echo htmlspecialchars($album['title']); ?></h3>
                                <p class="text-sm text-slate-500 mb-6"><?php echo $album['mediaItemsCount'] ?? '0'; ?> foto's</p>
                            </div>
                            
                            <form method="POST">
                                <input type="hidden" name="album_id" value="<?php echo $album['id']; ?>">
                                <input type="hidden" name="album_title" value="<?php echo htmlspecialchars($album['title']); ?>">
                                <button type="submit" 
                                    class="w-full py-3 rounded-xl font-semibold text-sm transition-all
                                    <?php echo $isActive 
                                        ? 'bg-blue-600 text-white cursor-default' 
                                        : 'bg-slate-800 text-white hover:bg-slate-700 active:scale-95'; ?>">
                                    <?php echo $isActive ? 'Huidige Selectie' : 'Dit album tonen'; ?>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </main>

    <footer class="mt-20 py-10 border-t border-slate-900 text-center text-slate-600 text-xs">
        &copy; 2026 Familie Forcekes Portaalsysteem &bull; Gebouwd met rust en precisie.
    </footer>

</body>
</html>