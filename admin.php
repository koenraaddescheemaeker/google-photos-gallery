<?php
/** * FORCEKES - admin.php (Master Storage Control) */
require_once 'config.php';

$currentUser = isset($_SESSION['user_email']) ? strtolower($_SESSION['user_email']) : '';
if ($currentUser !== 'koen@lauwe.com') { header("Location: index.php"); exit; }

$statusMessage = ""; $statusType = "info";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // 1. HARD RENAME (Database + Storage)
    if ($_POST['action'] === 'rename_album') {
        $old = $_POST['old_slug'];
        $new = strtolower(trim($_POST['new_slug']));
        
        if (!empty($new) && $old !== $new) {
            // A. Haal alle foto's uit dit album op
            $items = supabaseRequest("album_photos?category=eq." . rawurlencode($old), 'GET');
            if (is_array($items)) {
                foreach ($items as $item) {
                    $oldUrl = $item['image_url'];
                    $fileName = basename(parse_url($oldUrl, PHP_URL_PATH));
                    $newUrl = SUPABASE_URL . "/storage/v1/object/public/familie-media/" . rawurlencode($new) . "/" . rawurlencode($fileName);
                    
                    // B. Verplaats fysiek in Supabase Storage
                    $moveUrl = SUPABASE_URL . "/storage/v1/object/move";
                    $movePayload = [
                        "bucketId" => "familie-media",
                        "sourceKey" => "$old/$fileName",
                        "destinationKey" => "$new/$fileName"
                    ];
                    
                    $ch = curl_init($moveUrl);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($movePayload));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.SUPABASE_SERVICE_KEY,'Content-Type: application/json']);
                    curl_exec($ch); curl_close($ch);

                    // C. Update Database record
                    supabaseRequest("album_photos?id=eq.".$item['id'], "PATCH", [
                        'category' => $new,
                        'image_url' => $newUrl
                    ]);
                }
            }
            $statusMessage = "Album en Storage verplaatst naar '$new'.";
        }
    }

    // 2. HARD DELETE (Database + Storage)
    if ($_POST['action'] === 'delete_album') {
        $slug = $_POST['slug'];
        // A. Database wissen
        supabaseRequest("album_photos?category=eq." . rawurlencode($slug), "DELETE");
        
        // B. Storage map wissen (Supabase wist mappen door prefixes te verwijderen)
        // We moeten eerst alle bestanden in de map ophalen om ze te kunnen wissen
        $listUrl = SUPABASE_URL . "/storage/v1/object/list/familie-media";
        $ch = curl_init($listUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['prefix' => $slug]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.SUPABASE_SERVICE_KEY,'Content-Type: application/json']);
        $files = json_decode(curl_exec($ch), true); curl_close($ch);

        if (is_array($files)) {
            $toDelete = array_map(fn($f) => "$slug/".$f['name'], $files);
            $delUrl = SUPABASE_URL . "/storage/v1/object/familie-media";
            $ch = curl_init($delUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['prefixes' => $toDelete]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.SUPABASE_SERVICE_KEY,'Content-Type: application/json']);
            curl_exec($ch); curl_close($ch);
        }
        $statusMessage = "Album '$slug' volledig gewist uit DB en Storage.";
        $statusType = "error";
    }
}

$albumStats = supabaseRequest("rpc/get_album_dashboard", 'GET');
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8"><title>Forcekes Admin | Fase 5</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { background-color: #000; color: #fff; font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        .glass { background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05); backdrop-filter: blur(20px); }
    </style>
</head>
<body class="bg-black">
    <?php include 'menu.php'; ?>
    <main class="max-w-6xl mx-auto px-6 py-32">
        <header class="mb-16 flex justify-between items-end">
            <div>
                <h1 class="text-5xl font-black italic uppercase tracking-tighter">Master<span class="text-blue-600">Admin</span></h1>
                <p class="text-zinc-500 text-[10px] font-black uppercase tracking-[0.3em] mt-3">Secretaris Uil: Fase 5 - Storage Sync Actief</p>
            </div>
            <?php if ($statusMessage): ?>
                <div class="px-6 py-3 rounded-full text-[10px] font-black uppercase border <?= $statusType === 'error' ? 'bg-red-900/10 border-red-900/50 text-red-500' : 'bg-blue-900/10 border-blue-900/50 text-blue-500' ?>">
                    <?= $statusMessage ?>
                </div>
            <?php endif; ?>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-1 space-y-6">
                <section class="p-8 glass rounded-[2.5rem] border border-blue-600/30">
                    <h3 class="text-xs font-black uppercase tracking-widest text-blue-500 mb-6">Systeem Onderhoud</h3>
                    <a href="import-bucket.php" class="block w-full py-5 bg-blue-600 hover:bg-blue-500 rounded-2xl text-center font-black uppercase text-[10px] tracking-widest shadow-lg shadow-blue-600/20 transition">
                        🔍 Scan & Import Bucket
                    </a>
                </section>
            </div>

            <div class="lg:col-span-2">
                <section class="p-10 glass rounded-[3rem]">
                    <h3 class="text-xl font-black italic uppercase tracking-tight mb-8">Albums & Fysieke Opslag</h3>
                    <div class="space-y-4">
                        <?php if (is_array($albumStats)): foreach ($albumStats as $album): ?>
                            <div class="flex items-center justify-between p-5 bg-white/5 rounded-3xl border border-white/5">
                                <div>
                                    <h4 class="text-sm font-black uppercase tracking-wider"><?= $album['category_name'] ?></h4>
                                    <p class="text-[9px] font-bold text-blue-500 uppercase"><?= $album['photo_count'] ?> foto's in storage</p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <form action="admin.php" method="POST" class="flex space-x-2">
                                        <input type="hidden" name="action" value="rename_album"><input type="hidden" name="old_slug" value="<?= $album['category_name'] ?>">
                                        <input type="text" name="new_slug" placeholder="Nieuwe naam..." class="bg-black border border-white/10 rounded-lg px-3 py-2 text-[9px] outline-none focus:border-blue-600 w-24">
                                        <button type="submit" class="p-2 bg-zinc-800 hover:bg-blue-600 rounded-lg">Rename</button>
                                    </form>
                                    <form action="admin.php" method="POST" onsubmit="return confirm('Wist ook alle bestanden in de bucket!')">
                                        <input type="hidden" name="action" value="delete_album"><input type="hidden" name="slug" value="<?= $album['category_name'] ?>">
                                        <button type="submit" class="p-2 bg-zinc-800 hover:bg-red-600 rounded-lg text-red-500">Wissen</button>
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