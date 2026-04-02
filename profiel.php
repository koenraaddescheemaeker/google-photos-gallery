<?php
/** * FORCEKES - profiel.php (Fase 21: Member Cockpit - Gekeurd door Manu) */
require_once 'config.php';
$userEmail = $_SESSION['user_email'] ?? '';
if (!$userEmail) { header("Location: login.php"); exit; }

$status = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_nickname'])) {
        $nickname = trim($_POST['nickname']);
        supabaseRequest("members?email=eq.$userEmail", "PATCH", ["nickname" => $nickname]);
        $status = "Profiel vlijmscherp bijgewerkt.";
    }
    
    if (isset($_POST['add_album'])) {
        $slug = strtolower(trim($_POST['slug']));
        $link = trim($_POST['google_link']);
        supabaseRequest("album_settings", "POST", [
            "slug" => $slug, "google_link" => $link, "created_by" => $userEmail, "priority" => 999, "is_visible" => true
        ], "upsert=true");
        $status = "Album '$slug' toegevoegd. De synchronisatie start spoedig.";
    }
}

$member = supabaseRequest("members?email=eq.$userEmail", 'GET')[0] ?? null;
$myAlbumsRaw = supabaseRequest("rpc/get_albums_by_owner", "POST", ["target_email" => $userEmail]);
$myAlbums = (is_array($myAlbumsRaw) && !isset($myAlbumsRaw['error'])) ? $myAlbumsRaw : [];
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Mijn Profiel | Forcekes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;900&family=Playfair+Display:ital,wght@1,900&display=swap');
        body { background: #000; color: #fff; font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        .card { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 2.5rem; padding: 2.5rem; }
        .serif-italic { font-family: 'Playfair Display', serif; font-style: italic; }
        input { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 1rem; border-radius: 1.25rem; font-size: 13px; outline: none; width: 100%; }
        .btn-premium { background: #3b82f6; color: #fff; font-weight: 900; text-transform: uppercase; font-size: 10px; letter-spacing: 0.1em; padding: 1rem; border-radius: 1.25rem; width: 100%; }
    </style>
</head>
<body class="bg-black">
    <?php include 'menu.php'; ?>
    <main class="max-w-7xl mx-auto px-6 pt-48 pb-32">
        <header class="mb-16">
            <h1 class="serif-italic text-5xl italic mb-2">Mijn Cockpit</h1>
            <p class="text-zinc-600 text-[10px] uppercase tracking-[0.4em]"><?= $userEmail ?></p>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10 mb-24">
            <section class="card">
                <h2 class="text-blue-500 font-black text-[10px] uppercase tracking-widest mb-8">Profiel</h2>
                <form method="POST" class="space-y-6">
                    <div>
                        <label class="text-[9px] text-zinc-600 uppercase font-bold mb-2 block tracking-widest">Roepnaam</label>
                        <input type="text" name="nickname" value="<?= htmlspecialchars($member['nickname'] ?? '') ?>" required>
                    </div>
                    <button type="submit" name="update_nickname" class="btn-premium">Instellingen Opslaan</button>
                </form>
                <div class="mt-8 pt-8 border-t border-white/5">
                    <a href="logout.php" class="text-[10px] font-black uppercase text-red-900 hover:text-red-500 transition tracking-widest">Sessie beëindigen</a>
                </div>
            </section>

            <section class="card lg:col-span-2">
                <h2 class="text-blue-500 font-black text-[10px] uppercase tracking-widest mb-8">Album Toevoegen</h2>
                <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <input type="text" name="slug" placeholder="NAAM (bijv: winter-2026)" required>
                    <input type="url" name="google_link" placeholder="GOOGLE PHOTOS LINK" required>
                    <button type="submit" name="add_album" class="btn-premium md:col-span-2">Album Vastleggen</button>
                </form>
                <?php if($status): ?> <p class="mt-6 text-blue-500 text-[10px] font-bold uppercase text-center"><?= $status ?></p> <?php endif; ?>
            </section>
        </div>

        <section>
            <h2 class="serif-italic text-3xl italic mb-12 text-zinc-400">Mijn Gedeelde Momenten</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <?php foreach ($myAlbums as $a): ?>
                    <a href="gallery.php?page=<?= rawurlencode($a['category_name']) ?>" class="group relative aspect-video sm:aspect-square overflow-hidden rounded-[2.5rem] bg-zinc-900 border border-white/5 shadow-2xl transition-all duration-700 hover:scale-[1.02]">
                        <?php if($a['thumbnail_url']): ?>
                            <img src="<?= $a['thumbnail_url'] ?>" class="absolute inset-0 w-full h-full object-cover opacity-50 group-hover:opacity-100 transition-all duration-1000">
                        <?php endif; ?>
                        <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent"></div>
                        <div class="absolute inset-0 p-8 flex flex-col justify-end">
                            <h3 class="text-lg font-bold italic"><?= ucfirst($a['category_name']) ?></h3>
                            <p class="text-[9px] text-zinc-500 uppercase mt-1"><?= (int)$a['photo_count'] ?> items</p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
</body>
</html>