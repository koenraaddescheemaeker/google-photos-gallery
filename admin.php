<?php
/** * FORCEKES - admin.php (Admin Master View) */
require_once 'config.php';
$userEmail = $_SESSION['user_email'] ?? '';
if ($userEmail !== 'koen@lauwe.com') { header("Location: index.php"); exit; }

$albums = supabaseRequest("rpc/get_album_dashboard", 'GET');
$members = supabaseRequest("members?order=role.asc", 'GET');
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8"><title>Beheer | Forcekes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@1,900&display=swap');
        body { background: #000; color: #fff; font-family: 'Inter', sans-serif; }
        .card { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 2rem; padding: 2rem; }
    </style>
</head>
<body class="bg-black">
    <?php include 'menu.php'; ?>
    <main class="max-w-7xl mx-auto px-10 pt-48 pb-32">
        <h1 style="font-family:'Playfair Display', serif;" class="text-5xl italic mb-12">Beheer</h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <section class="card lg:col-span-2">
                <h2 class="text-blue-500 font-black uppercase text-[10px] tracking-widest mb-6">Alle Feesten</h2>
                <div class="space-y-2">
                    <?php foreach($albums as $a): ?>
                        <div class="flex justify-between items-center py-3 border-b border-white/5">
                            <span class="font-bold"><?= ucfirst($a['category_name']) ?></span>
                            <span class="text-zinc-500 text-[10px] font-black uppercase tracking-widest"><?= $a['photo_count'] ?> Bestanden</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="card">
                <h2 class="text-blue-500 font-black uppercase text-[10px] tracking-widest mb-6">Systeem</h2>
                <a href="sync-media.php" class="block w-full text-center py-4 bg-white/5 rounded-2xl text-[10px] font-black uppercase hover:bg-white hover:text-black transition">Start Sync</a>
            </section>
        </div>
    </main>
</body>
</html>