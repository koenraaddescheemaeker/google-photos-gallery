<?php
/** * FORCEKES - mijn-albums.php (Gekeurd door Manu) */
require_once 'config.php';
$userEmail = $_SESSION['user_email'] ?? '';
if (!$userEmail) { header("Location: login.php"); exit; }

$status = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_album'])) {
    $slug = strtolower(trim($_POST['slug']));
    $link = trim($_POST['google_link']);
    
    // Voeg toe aan instellingen met lid als eigenaar
    supabaseRequest("album_settings", "POST", [
        "slug" => $slug,
        "google_link" => $link,
        "created_by" => $userEmail,
        "priority" => 999,
        "is_visible" => true
    ], "upsert=true");
    $status = "Album '$slug' klaargezet voor de media-engine.";
}

// Haal alleen albums van dit lid op
$myAlbums = supabaseRequest("album_settings?created_by=eq.$userEmail", 'GET');
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8"><title>Mijn Albums | Forcekes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;900&family=Playfair+Display:ital,wght@1,900&display=swap');
        body { background: #000; color: #fff; font-family: 'Inter', sans-serif; }
        .card { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); border-radius: 2.5rem; padding: 2.5rem; }
        input { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 1rem; border-radius: 1rem; width: 100%; outline: none; }
    </style>
</head>
<body class="bg-black">
    <?php include 'navbar.php'; ?>
    <main class="max-w-4xl mx-auto px-10 pt-48 pb-32">
        <header class="mb-16">
            <h1 style="font-family:'Playfair Display', serif;" class="text-4xl italic mb-2">Mijn Albums</h1>
            <p class="text-zinc-600 text-[10px] uppercase tracking-[0.4em]">Deel uw momenten met de familie</p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
            <section class="card h-fit">
                <span class="text-blue-500 font-black text-[10px] uppercase tracking-widest block mb-6">Nieuw Album</span>
                <?php if($status): ?> <p class="text-green-500 text-[10px] mb-4"><?= $status ?></p> <?php endif; ?>
                <form method="POST" class="space-y-4">
                    <input type="text" name="slug" placeholder="NAAM (bijv: reis-italie)" required>
                    <input type="url" name="google_link" placeholder="GOOGLE PHOTOS LINK" required>
                    <button type="submit" name="add_album" class="w-full py-4 bg-white text-black rounded-xl font-black uppercase text-[10px] tracking-widest">Toevoegen</button>
                </form>
            </section>

            <section class="card">
                <span class="text-zinc-500 font-black text-[10px] uppercase tracking-widest block mb-6">Mijn Archief</span>
                <div class="space-y-4">
                    <?php if(empty($myAlbums)): ?>
                        <p class="text-zinc-700 text-xs italic">U heeft nog geen albums gedeeld.</p>
                    <?php else: ?>
                        <?php foreach($myAlbums as $a): ?>
                            <div class="flex justify-between items-center border-b border-white/5 pb-4">
                                <span class="text-sm font-bold"><?= ucfirst($a['slug']) ?></span>
                                <span class="text-[8px] bg-zinc-900 px-2 py-1 rounded text-zinc-500 uppercase font-black">Syncing...</span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>
</body>
</html>