<?php
/** * FORCEKES - admin.php (Master Control) */
require_once 'config.php';
$userEmail = $_SESSION['user_email'] ?? '';
if ($userEmail !== 'koen@lauwe.com') { header("Location: index.php"); exit; }

$status = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['upsert_album'])) {
        $slug = strtolower(trim($_POST['slug']));
        $parent = strtolower(trim($_POST['parent_category']));
        $priority = (int)$_POST['priority'];
        $link = trim($_POST['google_link'] ?? '');
        
        supabaseRequest("album_settings", "POST", [
            "slug" => $slug, 
            "parent_category" => $parent, 
            "priority" => $priority, 
            "google_link" => $link,
            "is_visible" => true
        ], "upsert=true");
        $status = "Instellingen vlijmscherp opgeslagen.";
    }
}

$albums = supabaseRequest("rpc/get_album_dashboard", 'GET');
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8"><title>Master Control | Forcekes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;900&family=Playfair+Display:ital,wght@1,900&display=swap');
        body { background: #000; color: #fff; font-family: 'Inter', sans-serif; }
        .card { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 2rem; padding: 2.5rem; }
        input { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 1rem; border-radius: 1.25rem; width: 100%; outline: none; }
        .btn-blue { background: #3b82f6; color: #fff; font-weight: 900; text-transform: uppercase; font-size: 11px; padding: 1.25rem; border-radius: 1.25rem; width: 100%; }
    </style>
</head>
<body class="bg-black">
    <?php include 'menu.php'; ?>
    <main class="max-w-7xl mx-auto px-6 pt-48 pb-32">
        <h1 style="font-family:'Playfair Display', serif;" class="text-6xl italic mb-12">Architectuur</h1>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <section class="card lg:col-span-2">
                <h2 class="text-blue-600 font-black text-[10px] uppercase tracking-widest mb-10">Album Indeling</h2>
                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <input type="text" name="slug" placeholder="ALBUM (slug)" required>
                        <input type="text" name="parent_category" placeholder="GROEP (bijv: feesten, museum)" required>
                        <input type="url" name="google_link" placeholder="GOOGLE LINK" class="md:col-span-2">
                        <input type="number" name="priority" value="999">
                    </div>
                    <button type="submit" name="upsert_album" class="btn-blue">Opslaan in Systeem</button>
                </form>

                <div class="mt-16 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <tr class="text-zinc-600 uppercase text-[10px] tracking-widest border-b border-white/5">
                            <th class="pb-4">Naam</th>
                            <th class="pb-4">Groep</th>
                            <th class="pb-4 text-right">Prio</th>
                        </tr>
                        <?php foreach ($albums as $a): ?>
                        <tr class="border-b border-white/5 hover:bg-white/5 transition">
                            <td class="py-5 font-bold text-white"><?= ucfirst($a['category_name']) ?></td>
                            <td class="py-5 text-blue-500 uppercase text-[10px] font-black"><?= $a['parent_category'] ?? 'Geen' ?></td>
                            <td class="py-5 text-right font-mono"><?= $a['priority'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </section>

            <section class="card h-fit">
                <h2 class="text-blue-600 font-black text-[10px] uppercase tracking-widest mb-8">Systeem</h2>
                <a href="sync-media.php" class="block text-center py-4 bg-white/5 border border-white/10 rounded-2xl text-[10px] font-black uppercase hover:bg-white hover:text-black transition">Start Sync Motor</a>
            </section>
        </div>
    </main>
</body>
</html>