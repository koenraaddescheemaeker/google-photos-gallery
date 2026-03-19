<?php
/**
 * admin.php - De Premium Album-Selector
 */
require_once 'config.php';

$token = getValidAccessToken();
if (!$token) {
    header('Location: google-auth.php');
    exit;
}

$msg = "";
$error = null;

// 1. Verwerk het activeren van een album (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aid'])) {
    $update = supabaseRequest('google_tokens?id=eq.1', 'PATCH', [
        'active_album_id' => $_POST['aid'],
        'active_album_title' => $_POST['atitle']
    ]);
    
    if (isset($update['error'])) {
        $error = "Database fout: " . $update['error'];
    } else {
        $msg = "✅ Album '" . htmlspecialchars($_POST['atitle']) . "' is nu live!";
    }
}

// 2. Haal de albums op bij Google
$ch = curl_init("https://photoslibrary.googleapis.com/v1/albums");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token",
    "Accept: application/json",
    "Content-Length: 0",
    "User-Agent: Forcekes-App/1.0"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

$raw = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$res = json_decode($raw, true);
curl_close($ch);

$albums = $res['albums'] ?? [];

// Foutafhandeling voor de API
if ($httpCode !== 200) {
    $error = "Google API Fout ($httpCode): " . ($res['error']['message'] ?? 'Onbekende fout');
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Forcekes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #020617; }
        .premium-card { background: linear-gradient(145deg, #0f172a, #1e293b); }
    </style>
</head>
<body class="text-slate-100 font-sans antialiased">

    <div class="max-w-6xl mx-auto px-6 py-12">
        <header class="flex justify-between items-center mb-12 border-b border-slate-800 pb-8">
            <div>
                <h1 class="text-4xl font-black italic tracking-tighter text-white">FORCEKES <span class="text-blue-500 not-italic text-sm ml-2">ADMIN</span></h1>
                <p class="text-slate-500 text-sm mt-1 uppercase tracking-widest">Beheer je Google Photos connectie</p>
            </div>
            <div class="flex gap-4">
                <a href="debug-token.php" class="bg-slate-800 hover:bg-slate-700 text-slate-300 px-4 py-2 rounded-xl text-xs font-bold transition">Debug Token</a>
                <a href="google-auth.php" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-lg shadow-blue-900/20">Re-login</a>
            </div>
        </header>

        <?php if ($msg): ?>
            <div class="mb-8 p-4 bg-emerald-500/10 border border-emerald-500/50 text-emerald-400 rounded-2xl flex items-center gap-3">
                <span class="text-xl">✨</span> <?= $msg ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="mb-8 p-6 bg-red-500/10 border border-red-500/50 text-red-400 rounded-2xl">
                <div class="flex items-center gap-3 mb-2 font-bold uppercase text-xs tracking-widest">
                    <span>⚠️</span> Google Toegangsfout
                </div>
                <p class="text-sm italic opacity-90"><?= $error ?></p>
                <p class="mt-4 text-xs text-red-300/60">Tip: Controleer of de Photos API is ingeschakeld in de Google Cloud Console en of je de vinkjes hebt gezet bij het inloggen.</p>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if (!empty($albums)): ?>
                <?php foreach ($albums as $album): ?>
                    <div class="premium-card rounded-[2rem] overflow-hidden border border-slate-800 hover:border-blue-500/50 transition-all duration-300 group shadow-2xl">
                        <div class="h-56 relative overflow-hidden">
                            <img src="<?= htmlspecialchars($album['coverPhotoBaseUrl'] ?? '') ?>=w600-h400-c" 
                                 alt="Album Cover" 
                                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110 opacity-70 group-hover:opacity-100">
                            <div class="absolute inset-0 bg-gradient-to-t from-[#0f172a] to-transparent"></div>
                            <div class="absolute bottom-4 left-6">
                                <span class="text-[10px] bg-blue-600 text-white px-3 py-1 rounded-full font-black uppercase tracking-widest">
                                    <?= $album['mediaItemsCount'] ?? '0' ?> Media
                                </span>
                            </div>
                        </div>

                        <div class="p-8">
                            <h3 class="text-xl font-bold text-white mb-2 truncate"><?= htmlspecialchars($album['title'] ?? 'Naamloos Album') ?></h3>
                            <p class="text-slate-500 text-xs mb-8 font-medium uppercase tracking-tighter">Google Photos Album</p>
                            
                            <form method="POST">
                                <input type="hidden" name="aid" value="<?= htmlspecialchars($album['id']) ?>">
                                <input type="hidden" name="atitle" value="<?= htmlspecialchars($album['title']) ?>">
                                <button type="submit" class="w-full bg-slate-800 hover:bg-white hover:text-black py-4 rounded-2xl font-black text-sm transition-all duration-300 uppercase tracking-widest active:scale-95">
                                    Activeer op Home
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <?php if (!$error): ?>
                    <div class="col-span-full py-24 text-center bg-slate-900/50 rounded-[3rem] border-2 border-dashed border-slate-800">
                        <p class="text-slate-500 font-medium">Geen albums gevonden in dit Google account.</p>
                        <p class="text-slate-600 text-xs mt-2 italic">Maak eerst een album aan in de Google Photos app.</p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>