<?php
/** * FORCEKES - admin.php (Dashboard Edition 2) */
require_once 'config.php';
// 1. TOEGANGSCONTROLE
$currentUser = isset($_SESSION['user_email']) ? strtolower($_SESSION['user_email']) : '';
if ($currentUser !== 'koen@lauwe.com') {
    header("Location: index.php?auth_error=not_authorized");
    exit;
}
$statusMessage = "";
$statusType = "info";
// 2. ACTIES
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // ALBUM HERNOEMEN (Update database & herbereken URLs)
    if ($_POST['action'] === 'rename_album') {
        $oldSlug = $_POST['old_slug'];
        $newSlug = strtolower(trim($_POST['new_slug']));
        if (!empty($newSlug) && $oldSlug !== $newSlug) {
            // Update categorie in DB
            supabaseRequest("album_photos?category=eq." . rawurlencode($oldSlug), "PATCH", ['category' => $newSlug]);
            $statusMessage = "Album hernoemd naar '$newSlug'. Draai de 'Sync Job' om de bestanden fysiek te verplaatsen.";
        }
    }

    // ALBUM VERWIJDEREN
    if ($_POST['action'] === 'delete_album') {
        $slug = $_POST['slug'];
        supabaseRequest("album_photos?category=eq." . rawurlencode($slug), "DELETE");
        $statusMessage = "Album '$slug' verwijderd uit database.";
        $statusType = "error";
    }
}

// 3. DATA OPHALEN
$albumStats = supabaseRequest("rpc/get_album_dashboard", 'GET');
$recentItems = supabaseRequest("album_photos?select=*&order=captured_at.desc&limit=5", 'GET');
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forcekes | Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { background-color: #000; color: #fff; font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        .glass { background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05); backdrop-filter: blur(20px); }
        .btn-premium { transition: all 0.3s; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; font-size: 10px; }
    </style>
</head>
<body class="bg-black">
    <?php include 'menu.php'; ?>

    <main class="max-w-6xl mx-auto px-6 py-32">
        <header class="mb-16 flex justify-between items-end">
            <div>
                <h1 class="text-5xl font-black italic uppercase tracking-tighter">Beheer<span class="text-blue-600">paneel</span></h1>
                <p class="text-zinc-500 text-[10px] font-black uppercase tracking-[0.3em] mt-3">Forcekes Portaal v2.8</p>
            </div>
            <?php if ($statusMessage): ?>
                <div class="px-6 py-3 rounded-full text-[10px] font-black uppercase tracking-widest border <?= $statusType === 'error' ? 'bg-red-900/10 border-red-900/50 text-red-500' : 'bg-blue-900/10 border-blue-900/50 text-blue-500' ?>">
                    <?= $statusMessage ?>
                </div>
            <?php endif; ?>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-1 space-y-8">
                <section class="p-8 glass rounded-[2.5rem] border border-blue-600/20">
                    <h3 class="text-xs font-black uppercase tracking-widest text-blue-500 mb-6">Systeem Onderhoud</h3>
                    <div class="space-y-4">
                        <a href="import-bucket.php" class="block w-full py-4 bg-blue-600 hover:bg-blue-500 rounded-2xl text-center btn-premium shadow-lg shadow-blue-600/20">
                            🔍 Scan Bucket & Importeer
                        </a>
                        <p class="text-[9px] text-zinc-500 px-2">Gebruik dit als je handmatig mappen hebt toegevoegd aan Supabase Storage.</p>
                        
                        <div class="h-px bg-white/5 my-6"></div>
                        
                        <a href="sync-media.php" class="block w-full py-4 bg-zinc-900 hover:bg-zinc-800 border border-white/5 rounded-2xl text-center btn-premium">
                            🚀 Start Media Migratie
                        </a>
                        <p class="text-[9px] text-zinc-500 px-2">Verplaatst beelden van Google naar de Bucket.</p>
                    </div>
                </section>

                <section class="p-8 glass rounded-[2.5rem]">
                    <h3 class="text-xs font-black uppercase tracking-widest text-zinc-500 mb-6">Snelle Toevoeging</h3>
                    <form action="admin.php" method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="add_photo">
                        <input type="url" name="url" required placeholder="Media URL..." class="w-full bg-black border border-white/10 rounded-xl px-4 py-3 text-xs outline-none focus:border-blue-600">
                        <input type="text" name="category" required placeholder="Categorie..." class="w-full bg-black border border-white/10 rounded-xl px-4 py-3 text-xs outline-none focus:border-blue-600">
                        <button type="submit" class="w-full py-3 bg-white text-black rounded-xl btn-premium hover:bg-zinc-200">Toevoegen</button>
                    </form>
                </section>
            </div>

            <div class="lg:col-span-2 space-y-8">
                <section class="p-10 glass rounded-[3rem]">
                    <h3 class="text-xl font-black italic uppercase tracking-tight mb-8">Albums & Status</h3>
                    <div class="grid gap-4">
                        <?php if (is_array($albumStats)): foreach ($albumStats as $album): ?>
                            <div class="group flex items-center justify-between p-5 bg-white/5 rounded-3xl border border-white/5 hover:border-white/10 transition">
                                <div class="flex items-center space-x-6">
                                    <div class="w-12 h-12 rounded-2xl overflow-hidden bg-zinc-800">
                                        <img src="<?= $album['cover_url'] ?>" class="w-full h-full object-cover opacity-50">
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-black uppercase tracking-wider"><?= $album['category_name'] ?></h4>
                                        <p class="text-[9px] font-bold text-blue-500 uppercase"><?= $album['photo_count'] ?> items</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <form action="admin.php" method="POST" class="flex space-x-2">
                                        <input type="hidden" name="action" value="rename_album">
                                        <input type="hidden" name="old_slug" value="<?= $album['category_name'] ?>">
                                        <input type="text" name="new_slug" placeholder="Nieuwe naam..." class="bg-black border border-white/10 rounded-lg px-3 py-2 text-[9px] outline-none focus:border-blue-600 w-24">
                                        <button type="submit" class="p-2 bg-zinc-800 hover:bg-blue-600 rounded-lg transition"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3l4 4L7 21H3v-4L17 3z"/></svg></button>
                                    </form>
                                    <form action="admin.php" method="POST" onsubmit="return confirm('Dit album uit de database wissen?')">
                                        <input type="hidden" name="action" value="delete_album">
                                        <input type="hidden" name="slug" value="<?= $album['category_name'] ?>">
                                        <button type="submit" class="p-2 bg-zinc-800 hover:bg-red-600 rounded-lg transition text-red-500 hover:text-white"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button>
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