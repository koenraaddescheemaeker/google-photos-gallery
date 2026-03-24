<?php
/**
 * FORCEKES ADMIN - De Ultieme Album Picker
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- CONFIG ---
$googleClientID     = trim(getenv('GOOGLE_CLIENT_ID'));
$googleClientSecret = trim(getenv('GOOGLE_CLIENT_SECRET'));
$supabaseUrl        = rtrim(getenv('NEXT_PUBLIC_SUPABASE_URL'), '/');
$supabaseKey        = trim(getenv('SUPABASE_SERVICE_ROLE_KEY'));

// --- HELPERS ---
function callGoogle($endpoint, $token) {
    $ch = curl_init("https://photoslibrary.googleapis.com/v1/" . $endpoint);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $token"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true);
}

// Haal token uit Supabase
$ch = curl_init("$supabaseUrl/rest/v1/google_tokens?select=*&id=eq.1");
curl_setopt($ch, CURLOPT_HTTPHEADER, ["apikey: $supabaseKey", "Authorization: Bearer $supabaseKey"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$tokenData = json_decode(curl_exec($ch), true);
curl_close($ch);

$token = $tokenData[0]['access_token'] ?? null;

if (!$token) {
    die("Geen verbinding met Google. Ga eerst naar login.php");
}

// --- DATA OPHALEN ---
$own = callGoogle("albums?pageSize=50", $token);
$shared = callGoogle("sharedAlbums?pageSize=50", $token);

// Voeg alles samen en ontdubbel
$rawList = array_merge($own['albums'] ?? [], $shared['sharedAlbums'] ?? []);
$allAlbums = [];
$ids = [];
foreach ($rawList as $a) {
    if (!in_array($a['id'], $ids)) {
        $allAlbums[] = $a;
        $ids[] = $a['id'];
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Forcekes | Kies Mappen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { font-family: 'Inter', sans-serif; background: #000; color: #fff; }
    </style>
</head>
<body class="p-8">
    <div class="max-w-6xl mx-auto">
        <header class="flex justify-between items-center mb-16 border-b border-zinc-900 pb-8">
            <h1 class="text-4xl font-black italic uppercase tracking-tighter">
                FORCEKES <span class="text-blue-500">ADMIN</span>
            </h1>
            <div class="flex gap-4">
                <a href="zwaaikamer.php" class="text-xs font-bold uppercase tracking-widest text-zinc-500 hover:text-white transition-colors">Zwaaikamer</a>
                <a href="login.php?pw=jouw_wachtwoord" class="text-xs font-bold uppercase tracking-widest text-blue-500">Refresh Verbinding</a>
            </div>
        </header>

        <div class="mb-12">
            <h2 class="text-5xl font-bold tracking-tight">Kies je mappen</h2>
            <p class="text-zinc-500 mt-4 max-w-xl">Selecteer de Google Photos albums die je wilt synchroniseren met je kookboek.</p>
        </div>

        <?php if (!empty($allAlbums)): ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php foreach ($allAlbums as $album): ?>
                    <div class="bg-zinc-900 border border-zinc-800 rounded-[2.5rem] overflow-hidden group hover:border-blue-500 transition-all duration-500">
                        <div class="h-56 relative overflow-hidden">
                            <img src="<?= $album['coverPhotoBaseUrl'] ?>=w600-h400-c" class="w-full h-full object-cover opacity-50 group-hover:opacity-100 group-hover:scale-110 transition-all duration-700">
                            <div class="absolute inset-0 bg-gradient-to-t from-zinc-950 to-transparent"></div>
                            <span class="absolute bottom-6 left-6 bg-black/60 backdrop-blur px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest border border-white/10">
                                <?= $album['mediaItemsCount'] ?? 0 ?> foto's
                            </span>
                        </div>
                        <div class="p-8">
                            <h3 class="text-xl font-bold mb-6 truncate"><?= htmlspecialchars($album['title']) ?></h3>
                            
                            <form action="save-selection.php" method="POST">
                                <input type="hidden" name="album_id" value="<?= $album['id'] ?>">
                                <input type="hidden" name="title" value="<?= htmlspecialchars($album['title']) ?>">
                                <button type="submit" class="w-full py-4 bg-zinc-800 hover:bg-blue-600 rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] transition-all">
                                    Toevoegen aan Kookboek
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-zinc-950 border-2 border-dashed border-zinc-900 rounded-[3rem] p-24 text-center">
                <div class="inline-block p-6 bg-zinc-900 rounded-full mb-6 text-zinc-700">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                </div>
                <h3 class="text-2xl font-bold text-zinc-400 mb-2">Geen mappen gevonden</h3>
                <p class="text-zinc-600 max-w-sm mx-auto mb-8 text-sm">Zorg dat je in de Google Photos app mappen hebt die door jou zijn aangemaakt of waar je expliciet lid van bent.</p>
                <a href="login.php?pw=jouw_wachtwoord" class="inline-block bg-zinc-900 hover:bg-zinc-800 px-8 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all">
                    Handshake Herhalen
                </a>
            </div>
            
            <div class="mt-12 p-8 bg-red-950/20 border border-red-900/50 rounded-2xl">
                <h4 class="text-xs font-bold text-red-500 uppercase mb-4">Ruwe API Data (Debug)</h4>
                <pre class="text-[10px] text-zinc-500 overflow-auto"><?php print_r(['eigen' => $own, 'gedeeld' => $shared]); ?></pre>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>