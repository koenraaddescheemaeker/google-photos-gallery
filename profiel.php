<?php
/** * FORCEKES - profiel.php (Fase 20: Member Cockpit) */
require_once 'config.php';
$userEmail = $_SESSION['user_email'] ?? '';
if (!$userEmail) { header("Location: login.php"); exit; }

$status = "";

// Acties
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
        $status = "Album '$slug' toegevoegd. De uil synchroniseert dit weldra.";
    }
}

// Data
$member = supabaseRequest("members?email=eq.$userEmail", 'GET')[0] ?? null;
$myAlbums = supabaseRequest("rpc/get_albums_by_owner", "POST", ["target_email" => $userEmail]);
$myAlbums = is_array($myAlbums) ? $myAlbums : [];
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Profiel | Forcekes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;900&family=Playfair+Display:ital,wght@1,900&display=swap');
        body { background: #000; color: #fff; font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        .card { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 2rem; padding: 2rem; }
        .serif-italic { font-family: 'Playfair Display', serif; font-style: italic; }
        input { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 0.85rem 1rem; border-radius: 1rem; font-size: 13px; outline: none; width: 100%; }
        .btn-blue { background: #3b82f6; color: #fff; font-weight: 900; text-transform: uppercase; font-size: 10px; letter-spacing: 0.1em; padding: 0.85rem; border-radius: 1rem; width: 100%; }
    </style>
</head>
<body class="bg-black">
    <?php include 'menu.php'; ?>
    <main class="max-w-7xl mx-auto px-6 pt-32 pb-32">
        <header class="mb-16">
            <h1 class="serif-italic text-5xl italic mb-2">Mijn Cockpit</h1>
            <p class="text-zinc-600 text-[10px] uppercase tracking-[0.4em]"><?= $userEmail ?></p>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10 mb-20">
            <section class="card">
                <h2 class="text-blue-500 font-black text-[10px] uppercase tracking-widest mb-6">Mijn Gegevens</h2>
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="text-[9px] text-zinc-600 uppercase font-bold mb-1 block tracking-widest">Roepnaam</label>
                        <input type="text" name="nickname" value="<?= htmlspecialchars($member['nickname'] ?? '') ?>" required>
                    </div>
                    <button type="submit" name="update_nickname" class="btn-blue">Update Profiel</button>
                </form>
                <div class="mt-8 pt-8 border-t border-white/5">
                    <a href="logout.php" class="text-[10px] font-black uppercase text-red-900 hover:text-red-500 transition tracking-widest">Afmelden</a>
                </div>
            </section>

            <section class="card lg:col-span-2">
                <h2 class="text-blue-500 font-black text-[10px] uppercase tracking-widest mb-6">Nieuw Album Toevoegen</h2>
                <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="text" name="slug" placeholder="ALBUM NAAM (bijv: herinneringen)" required>
                    <input type="url" name="google_link" placeholder="GOOGLE PHOTOS LINK" required>
                    <button type="submit" name="add_album" class="btn-blue md:col-span-2 mt-2">Toevoegen aan Archief</button>
                </form>
                <?php if($status): ?> <p class="mt-4 text-blue-500 text-[10px] font-bold uppercase text-center"><?= $status ?></p> <?php endif; ?>
            </section>
        </div>

        <section>
            <h2 class="serif-italic text-3xl italic mb-10 text-zinc-400">Mijn Archiefkasten</h2>
            <?php if (empty($myAlbums)): ?>
                <div class="py-20 border border-white/5 rounded-[3rem] text-center"><p class="serif-italic text-xl text-zinc-600 italic">Nog geen albums gedeeld.</p></div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    <?php foreach ($myAlbums as $a): ?>
                        <a href="gallery.php?page=<?= rawurlencode($a['category_name']) ?>" class="group relative aspect-video sm:aspect-square overflow-hidden rounded-[2.5rem] bg-zinc-900 border border-white/5 shadow-2xl">
                            <img src="<?= $a['thumbnail_url'] ?>" class="absolute inset-0 w-full h-full object-cover opacity-50 group-hover:opacity-100 transition-all duration-700" loading="lazy">
                            <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent"></div>
                            <div class="absolute inset-0 p-8 flex flex-col justify-end">
                                <h3 class="text-lg font-bold italic"><?= ucfirst($a['category_name']) ?></h3>
                                <p class="text-[9px] text-zinc-500 uppercase"><?= $a['photo_count'] ?> items</p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>