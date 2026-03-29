<?php
/**
 * FORCEKES - admin.php (Master Control)
 * Volledig beheer van albums (categorieën) en individuele media.
 */
require_once 'config.php';

// 1. STRIKTE TOEGANGSCONTROLE
$currentUser = isset($_SESSION['user_email']) ? strtolower($_SESSION['user_email']) : '';
if ($currentUser !== 'koen@lauwe.com') {
    header("Location: index.php?auth_error=not_authorized");
    exit;
}

$statusMessage = "";
$statusType = "info";

// 2. ACTIES VERWERKEN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // A. ALBUM HERNOEMEN (SLUG AANPASSEN)
    if ($_POST['action'] === 'rename_album') {
        $oldSlug = $_POST['old_slug'];
        $newSlug = strtolower(trim($_POST['new_slug']));
        if (!empty($newSlug)) {
            $res = supabaseRequest("album_photos?category=eq." . rawurlencode($oldSlug), "PATCH", ['category' => $newSlug]);
            $statusMessage = "Album '$oldSlug' is nu '$newSlug'.";
        }
    }

    // B. ALBUM VERWIJDEREN (INCLUSIEF ALLE FOTO'S)
    if ($_POST['action'] === 'delete_album') {
        $slug = $_POST['slug'];
        supabaseRequest("album_photos?category=eq." . rawurlencode($slug), "DELETE");
        $statusMessage = "Album '$slug' en alle bijbehorende foto's zijn verwijderd.";
        $statusType = "error";
    }

    // C. NIEUWE MEDIA TOEVOEGEN
    if ($_POST['action'] === 'add_photo') {
        $url = filter_var($_POST['url'], FILTER_SANITIZE_URL);
        $category = strtolower(trim($_POST['category']));
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $payload = [
                'id' => bin2hex(random_bytes(12)),
                'image_url' => $url,
                'category' => $category,
                'captured_at' => date('c'),
                'mime_type' => (strpos($url, '.mp4') !== false) ? 'video/mp4' : 'image/jpeg'
            ];
            supabaseRequest("album_photos", "POST", $payload);
            $statusMessage = "Media toegevoegd aan $category.";
        }
    }
}

// 3. DATA OPHALEN VIA RPC (Uit vorige stap)
$albumStats = supabaseRequest("rpc/get_album_dashboard", 'GET');
$recentItems = supabaseRequest("album_photos?select=*&order=captured_at.desc&limit=5", 'GET');
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forcekes | Master Admin</title>
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
        <header class="mb-16 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <h1 class="text-5xl font-black italic uppercase tracking-tighter">Master<span class="text-blue-600">Admin</span></h1>
                <p class="text-zinc-500 text-[10px] font-black uppercase tracking-[0.3em] mt-3">Beheer van Forcekes Portaal</p>
            </div>
            <?php if ($statusMessage): ?>
                <div class="px-6 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest border <?= $statusType === 'error' ? 'bg-red-900/10 border-red-900/50 text-red-500' : 'bg-blue-900/10 border-blue-900/50 text-blue-500' ?>">
                    <?= $statusMessage ?>
                </div>
            <?php endif; ?>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            
            <div class="lg:col-span-2 space-y-10">
                <section class="p-10 glass rounded-[3.5rem]">
                    <h3 class="text-xl font-black italic uppercase tracking-tight mb-8">Album Beheer</h3>
                    <div class="space-y-4">
                        <?php if (is_array($albumStats)): foreach ($albumStats as $album): ?>
                            <div class="flex flex-col md:flex-row md:items-center justify-between p-6 bg-zinc-900/50 rounded-3xl border border-white/5 gap-4">
                                <div>
                                    <span class="text-blue-600 font-black text-xl italic"><?= $album['photo_count'] ?></span>
                                    <span class="text-xs font-black uppercase tracking-widest ml-2"><?= $album['category_name'] ?></span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <form action="admin.php" method="POST" class="flex items-center space-x-2">
                                        <input type="hidden" name="action" value="rename_album">
                                        <input type="hidden" name="old_slug" value="<?= $album['category_name'] ?>">
                                        <input type="text" name="new_slug" placeholder="Nieuwe naam..." class="bg-black border border-white/10 rounded-full px-4 py-2 text-[10px] outline-none focus:border-blue-600">
                                        <button type="submit" class="bg-zinc-800 hover:bg-blue-600 px-4 py-2 rounded-full text-[9px] font-black uppercase transition">Update</button>
                                    </form>
                                    <form action="admin.php" method="POST" onsubmit="return confirm('ALLES in dit album wissen?')">
                                        <input type="hidden" name="action" value="delete_album">
                                        <input type="hidden" name="slug" value="<?= $album['category_name'] ?>">
                                        <button type="submit" class="bg-zinc-800 hover:bg-red-600 px-4 py-2 rounded-full text-[9px] font-black uppercase transition text-red-500 hover:text-white">Wis</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                </section>

                <section class="p-10 glass rounded-[3.5rem]">
                    <h3 class="text-xl font-black italic uppercase tracking-tight mb-8">Media Toevoegen</h3>
                    <form action="admin.php" method="POST" class="space-y-6">
                        <input type="hidden" name="action" value="add_photo">
                        <input type="url" name="url" required placeholder="Media URL (Google of Bucket)..." class="w-full bg-zinc-900 border border-white/5 rounded-2xl px-6 py-4 text-sm outline-none focus:border-blue-600 transition">
                        <div class="grid grid-cols-2 gap-4">
                            <input type="text" name="category" required placeholder="Categorie (bijv. feest 2026)" class="bg-zinc-900 border border-white/5 rounded-2xl px-6 py-4 text-sm outline-none focus:border-blue-600 transition">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-500 rounded-2xl font-black uppercase text-[10px] tracking-widest transition">Opslaan</button>
                        </div>
                    </form>
                </section>
            </div>

            <div class="space-y-10">
                <section class="p-10 glass rounded-[3.5rem]">
                    <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-500 mb-8">Laatste Media</h3>
                    <div class="space-y-4">
                        <?php if (is_array($recentItems)): foreach ($recentItems as $item): ?>
                            <div class="flex items-center space-x-4 p-3 bg-white/5 rounded-2xl border border-white/5">
                                <img src="<?= $item['image_url'] ?>" class="w-12 h-12 rounded-xl object-cover">
                                <div class="truncate">
                                    <p class="text-[9px] font-black uppercase text-blue-500"><?= $item['category'] ?></p>
                                    <p class="text-[8px] text-zinc-600 truncate"><?= basename($item['image_url']) ?></p>
                                </div>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                    <a href="sync-media.php" class="block w-full mt-10 py-4 bg-zinc-900 border border-white/5 hover:border-blue-600/30 rounded-2xl text-center text-[9px] font-black uppercase tracking-widest transition">
                        Start Sync Job (Bucket)
                    </a>
                </section>
            </div>

        </div>
    </main>
</body>
</html>