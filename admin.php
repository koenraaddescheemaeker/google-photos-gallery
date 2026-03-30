<?php
/**
 * FORCEKES - admin.php (Fase 6: Master Dashboard)
 * Inclusief: Batch Processing, Storage Sync & Scraper Interface
 */
require_once 'config.php';

// 1. SERVER-INSTELLINGEN (Anti-Timeout)
set_time_limit(0); 
ignore_user_abort(true);
ini_set('memory_limit', '512M');

// 2. TOEGANGSCONTROLE
$currentUser = isset($_SESSION['user_email']) ? strtolower($_SESSION['user_email']) : '';
if ($currentUser !== 'koen@lauwe.com') {
    header("Location: index.php?auth_error=unauthorized");
    exit;
}

$statusMessage = ""; 
$statusType = "info";

// 3. BATCH RENAME LOGICA (Fysieke Move + DB Update)
if (isset($_GET['action']) && $_GET['action'] === 'batch_rename') {
    $old = $_GET['old'];
    $new = strtolower(trim($_GET['new']));
    $limit = 12; // Aantal bestanden per hapje

    // Haal items op die nog op de OUDE categorie staan
    $items = supabaseRequest("album_photos?category=eq." . rawurlencode($old) . "&limit=$limit", 'GET');
    
    if (is_array($items) && count($items) > 0) {
        foreach ($items as $item) {
            $fileName = basename(parse_url($item['image_url'], PHP_URL_PATH));
            $newPath = rawurlencode($new) . "/" . rawurlencode($fileName);
            $newUrl = SUPABASE_URL . "/storage/v1/object/public/familie-media/" . $newPath;
            
            // A. Fysiek verplaatsen in Supabase Storage
            $movePayload = [
                "bucketId" => "familie-media",
                "sourceKey" => "$old/$fileName",
                "destinationKey" => "$new/$fileName"
            ];
            
            $ch = curl_init(SUPABASE_URL . "/storage/v1/object/move");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($movePayload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . SUPABASE_SERVICE_KEY,
                'Content-Type: application/json'
            ]);
            curl_exec($ch); 
            curl_close($ch);

            // B. Database record bijwerken naar de nieuwe URL en categorie
            supabaseRequest("album_photos?id=eq." . $item['id'], "PATCH", [
                'category' => $new,
                'image_url' => $newUrl
            ]);
        }
        // Redirect voor de volgende batch
        header("Location: admin.php?action=batch_rename&old=".urlencode($old)."&new=".urlencode($new)."&status=processing");
        exit;
    } else {
        $statusMessage = "Hernoemen voltooid: '$old' is volledig verhuisd naar '$new'.";
    }
}

// 4. POST ACTIES (Delete & Rename Start)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // START RENAME PROCES
    if ($_POST['action'] === 'rename_album') {
        $old = $_POST['old_slug'];
        $new = strtolower(trim($_POST['new_slug']));
        if (!empty($new) && $old !== $new) {
            header("Location: admin.php?action=batch_rename&old=".urlencode($old)."&new=".urlencode($new));
            exit;
        }
    }
    
    // DELETE PROCES (DB + Storage)
    if ($_POST['action'] === 'delete_album') {
        $slug = $_POST['slug'];

        // A. Bestanden in de bucket map opsporen
        $listUrl = SUPABASE_URL . "/storage/v1/object/list/familie-media";
        $ch = curl_init($listUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['prefix' => $slug]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.SUPABASE_SERVICE_KEY, 'Content-Type: application/json']);
        $files = json_decode(curl_exec($ch), true);
        curl_close($ch);

        // B. Bestanden fysiek wissen
        if (is_array($files) && !empty($files)) {
            $toDelete = array_map(fn($f) => "$slug/" . $f['name'], $files);
            $delUrl = SUPABASE_URL . "/storage/v1/object/familie-media";
            $ch = curl_init($delUrl);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['prefixes' => $toDelete]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.SUPABASE_SERVICE_KEY, 'Content-Type: application/json']);
            curl_exec($ch);
            curl_close($ch);
        }

        // C. Database records wissen
        supabaseRequest("album_photos?category=eq." . rawurlencode($slug), "DELETE");
        $statusMessage = "Album '$slug' is volledig gewist uit de database en de bucket.";
        $statusType = "error";
    }
}

// 5. DASHBOARD DATA
$albumStats = supabaseRequest("rpc/get_album_dashboard", 'GET');
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
        body { background-color: #000; color: #fff; font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        .glass { background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05); backdrop-filter: blur(20px); }
        .loader { border-top-color: #3b82f6; animation: spinner 1.5s linear infinite; }
        @keyframes spinner { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body class="bg-black">
    <?php include 'menu.php'; ?>

    <main class="max-w-7xl mx-auto px-6 py-32">

        <?php if (isset($_GET['status']) && $_GET['status'] === 'processing'): ?>
            <div class="mb-12 p-10 glass rounded-[3rem] border border-blue-600/30 text-center animate-pulse">
                <div class="loader w-12 h-12 border-4 border-white/10 rounded-full mx-auto mb-6"></div>
                <h2 class="text-xl font-black uppercase italic tracking-widest text-blue-500">Bezig met verhuizen...</h2>
                <p class="text-[10px] text-zinc-500 mt-2">Sluit dit venster niet. De uil verplaatst je bestanden in veilige batches.</p>
            </div>
        <?php endif; ?>

        <header class="mb-16 flex justify-between items-end">
            <div>
                <h1 class="text-5xl font-black italic uppercase tracking-tighter">Master<span class="text-blue-600">Admin</span></h1>
                <p class="text-zinc-500 text-[10px] font-black uppercase tracking-[0.3em] mt-3">Secretaris Uil: Fase 6 Actief</p>
            </div>
            <?php if ($statusMessage): ?>
                <div class="px-6 py-3 rounded-full text-[10px] font-black uppercase border <?= $statusType === 'error' ? 'bg-red-900/10 border-red-900/50 text-red-500' : 'bg-blue-900/10 border-blue-900/50 text-blue-500' ?>">
                    <?= $statusMessage ?>
                </div>
            <?php endif; ?>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            
            <div class="space-y-8">
                <section class="p-8 glass rounded-[2.5rem] border border-blue-600/30 shadow-2xl shadow-blue-600/5">
                    <h3 class="text-xs font-black uppercase tracking-widest text-blue-500 mb-6 italic">Nieuw Album Scraper</h3>
                    <form action="sync-photos.php" method="GET" class="space-y-6">
                        <div class="space-y-2">
                            <label class="text-[9px] font-black uppercase text-zinc-500 ml-2">Google Photos Link</label>
                            <input type="url" name="album_url" required placeholder="https://photos.app.goo.gl/..." 
                                   class="w-full bg-black border border-white/10 rounded-2xl px-5 py-4 text-xs outline-none focus:border-blue-600 transition">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] font-black uppercase text-zinc-500 ml-2">Naam in de App</label>
                            <input type="text" name="app_name" required placeholder="bijv. Vakantie 2024" 
                                   class="w-full bg-black border border-white/10 rounded-2xl px-5 py-4 text-xs outline-none focus:border-blue-600 transition">
                        </div>
                        <button type="submit" class="w-full py-5 bg-blue-600 hover:bg-blue-500 rounded-2xl text-center text-[10px] font-black uppercase tracking-widest shadow-lg shadow-blue-600/20 transition">
                            🚀 Start Automatisatie
                        </button>
                    </form>
                </section>

                <section class="p-8 glass rounded-[2.5rem]">
                    <h3 class="text-xs font-black uppercase tracking-widest text-zinc-500 mb-6">Onderhoud</h3>
                    <div class="space-y-3">
                        <a href="import-bucket.php" class="block w-full py-4 bg-zinc-900 hover:bg-zinc-800 rounded-2xl text-center text-[9px] font-black uppercase tracking-widest transition">🔍 Scan Bucket</a>
                        <a href="sync-media.php" class="block w-full py-4 bg-zinc-900 hover:bg-zinc-800 rounded-2xl text-center text-[9px] font-black uppercase tracking-widest transition">🚀 Migreer Google -> Bucket</a>
                    </div>
                </section>
            </div>

            <div class="lg:col-span-2">
                <section class="p-10 glass rounded-[3rem]">
                    <h3 class="text-xl font-black italic uppercase tracking-tight mb-8">Actieve Albums</h3>
                    <div class="space-y-4">
                        <?php if (is_array($albumStats)): foreach ($albumStats as $album): ?>
                            <div class="flex items-center justify-between p-5 bg-white/5 rounded-3xl border border-white/5 hover:border-white/10 transition group">
                                <div class="truncate pr-4">
                                    <h4 class="text-sm font-black uppercase tracking-wider truncate"><?= $album['category_name'] ?></h4>
                                    <p class="text-[9px] font-bold text-blue-500 uppercase"><?= $album['photo_count'] ?> items in kluis</p>
                                </div>
                                <div class="flex items-center space-x-2 shrink-0">
                                    <form action="admin.php" method="POST" class="flex space-x-2">
                                        <input type="hidden" name="action" value="rename_album">
                                        <input type="hidden" name="old_slug" value="<?= $album['category_name'] ?>">
                                        <input type="text" name="new_slug" placeholder="Nieuwe naam..." class="bg-black border border-white/10 rounded-lg px-3 py-2 text-[9px] outline-none focus:border-blue-600 w-24">
                                        <button type="submit" class="p-2 bg-zinc-800 hover:bg-blue-600 rounded-lg transition-colors">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                        </button>
                                    </form>
                                    <form action="admin.php" method="POST" onsubmit="return confirm('LET OP: Dit wist ook alle fysieke bestanden in de bucket!')">
                                        <input type="hidden" name="action" value="delete_album">
                                        <input type="hidden" name="slug" value="<?= $album['category_name'] ?>">
                                        <button type="submit" class="p-2 bg-zinc-800 hover:bg-red-600 rounded-lg text-red-500 hover:text-white transition-colors">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                </section>
            </div>
        </div>
    </main>
</body>
</html>