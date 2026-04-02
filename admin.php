<?php
/** * FORCEKES - admin.php (Fase 23: Hiërarchie Beheer) */
require_once 'config.php';
$userEmail = $_SESSION['user_email'] ?? '';
if ($userEmail !== 'koen@lauwe.com') { header("Location: index.php"); exit; }

$status = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['upsert_album'])) {
        $slug = strtolower(trim($_POST['slug']));
        $googleLink = trim($_POST['google_link']);
        $priority = (int)$_POST['priority'];
        $parent = strtolower(trim($_POST['parent_category']));
        
        supabaseRequest("album_settings", "POST", [
            "slug" => $slug,
            "google_link" => $googleLink,
            "priority" => $priority,
            "parent_category" => $parent,
            "is_visible" => true,
            "created_by" => $userEmail
        ], "upsert=true");
        $status = "Album '$slug' vlijmscherp ingedeeld bij '$parent'.";
    }
}

$albums = supabaseRequest("rpc/get_album_dashboard", 'GET');
$members = supabaseRequest("members?order=role.asc", 'GET');
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Master Control | Forcekes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=Playfair+Display:ital,wght@1,900&display=swap');
        body { background: #000; color: #fff; font-family: 'Inter', sans-serif; }
        .card { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 2rem; padding: 2.5rem; }
        input, select { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 1rem; border-radius: 1.25rem; font-size: 13px; outline: none; width: 100%; }
        .btn-premium { background: #3b82f6; color: #fff; font-weight: 900; text-transform: uppercase; font-size: 10px; padding: 1.25rem; border-radius: 1.25rem; width: 100%; transition: all 0.3s; }
        .btn-premium:hover { background: #fff; color: #000; }
    </style>
</head>
<body class="bg-black">
    <?php include 'menu.php'; ?>
    <main class="max-w-7xl mx-auto px-6 pt-48 pb-32">
        <header class="mb-16">
            <h1 style="font-family:'Playfair Display', serif;" class="text-5xl italic">Architectuur</h1>
            <?php if($status): ?> <p class="text-blue-500 text-[10px] font-black uppercase mt-4 tracking-widest"><?= $status ?></p> <?php endif; ?>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <section class="card lg:col-span-2">
                <h2 class="text-blue-600 font-black text-[10px] uppercase tracking-widest mb-10">Album Indeling</h2>
                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <input type="text" name="slug" placeholder="ALBUM NAAM (slug)" required>
                        <input type="text" name="parent_category" placeholder="GROEP (bijv: feesten, familie, museum)" required>
                        <input type="url" name="google_link" placeholder="GOOGLE PHOTOS LINK" class="md:col-span-2">
                        <input type="number" name="priority" value="999" placeholder="VOLGORDE">
                    </div>
                    <button type="submit" name="upsert_album" class="btn-premium">Opslaan & Categoriseren</button>
                </form>

                <div class="mt-20 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <tr class="text-zinc-600 uppercase text-[10px] tracking-widest border-b border-white/5">
                            <th class="pb-4">Album</th>
                            <th class="pb-4">Parent</th>
                            <th class="pb-4 text-right">Prioriteit</th>
                        </tr>
                        <?php foreach ($albums as $a): ?>
                        <tr class="border-b border-white/5 hover:bg-white/5 transition">
                            <td class="py-5 font-bold"><?= ucfirst($a['category_name']) ?></td>
                            <td class="py-5"><span class="bg-blue-600/10 text-blue-500 px-3 py-1 rounded-full text-[9px] font-black uppercase"><?= $a['parent_category'] ?? 'los' ?></span></td>
                            <td class="py-5 text-right font-mono"><?= $a['priority'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </section>

            <section class="card h-fit">
                <h2 class="text-blue-600 font-black text-[10px] uppercase tracking-widest mb-8">Systeem</h2>
                <a href="sync-media.php" class="block text-center py-4 bg-white/5 border border-white/10 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-white hover:text-black transition">Start Sync Motor</a>
            </section>
        </div>
    </main>
</body>
</html>