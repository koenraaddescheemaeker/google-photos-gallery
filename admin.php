<?php
/** * FORCEKES - admin.php (Phase 4: Album Scraper) */
require_once 'config.php';

// TOEGANGSCONTROLE
$currentUser = isset($_SESSION['user_email']) ? strtolower($_SESSION['user_email']) : '';
if ($currentUser !== 'koen@lauwe.com') { header("Location: index.php"); exit; }

$statusMessage = "";
$statusType = "info";

// DATA OPHALEN
$albumStats = supabaseRequest("rpc/get_album_dashboard", 'GET');
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forcekes Admin | Fase 4</title>
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
                <h1 class="text-5xl font-black italic uppercase tracking-tighter">Master<span class="text-blue-600">Admin</span></h1>
                <p class="text-zinc-500 text-[10px] font-black uppercase tracking-[0.3em] mt-3">Secretaris Uil: Fase 4 - Album Scraper Actief</p>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            
            <div class="lg:col-span-1 space-y-8">
                <section class="p-8 glass rounded-[2.5rem] border border-blue-600/30 shadow-2xl shadow-blue-600/5">
                    <h3 class="text-xs font-black uppercase tracking-widest text-blue-500 mb-6 italic">Nieuw Album Scraper</h3>
                    
                    <form action="sync-photos.php" method="GET" class="space-y-6">
                        <div class="space-y-2">
                            <label class="text-[9px] font-black uppercase text-zinc-500 ml-2">Google Photos Album URL</label>
                            <input type="url" name="album_url" required placeholder="https://photos.app.goo.gl/..." 
                                   class="w-full bg-black border border-white/10 rounded-2xl px-5 py-4 text-xs outline-none focus:border-blue-600 transition">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[9px] font-black uppercase text-zinc-500 ml-2">Naam in de App</label>
                            <input type="text" name="app_name" required placeholder="bijv. Feest 2024" 
                                   class="w-full bg-black border border-white/10 rounded-2xl px-5 py-4 text-xs outline-none focus:border-blue-600 transition">
                        </div>

                        <button type="submit" class="w-full py-5 bg-blue-600 hover:bg-blue-500 rounded-2xl text-center btn-premium shadow-lg shadow-blue-600/20">
                            🚀 Album Scrapen & Toevoegen
                        </button>
                    </form>
                    <p class="text-[9px] text-zinc-600 mt-6 leading-relaxed px-2">
                        De uil houdt toezicht: na het klikken worden alle beelden in dit album gezocht en automatisch toegevoegd aan de database onder de opgegeven naam.
                    </p>
                </section>

                <section class="p-8 glass rounded-[2.5rem]">
                    <h3 class="text-xs font-black uppercase tracking-widest text-zinc-500 mb-6">Systeem Onderhoud</h3>
                    <div class="space-y-3">
                        <a href="import-bucket.php" class="block w-full py-4 bg-zinc-900 hover:bg-zinc-800 rounded-2xl text-center btn-premium">🔍 Scan Bucket</a>
                        <a href="sync-media.php" class="block w-full py-4 bg-zinc-900 hover:bg-zinc-800 rounded-2xl text-center btn-premium">🚀 Migreer naar Bucket</a>
                    </div>
                </section>
            </div>

            <div class="lg:col-span-2">
                <section class="p-10 glass rounded-[3rem]">
                    <h3 class="text-xl font-black italic uppercase tracking-tight mb-8 text-zinc-400">Actieve Albums</h3>
                    <div class="grid gap-4">
                        <?php if (is_array($albumStats)): foreach ($albumStats as $album): ?>
                            <div class="group flex items-center justify-between p-5 bg-white/5 rounded-3xl border border-white/5 hover:border-white/10 transition">
                                <div class="flex items-center space-x-6">
                                    <div class="w-12 h-12 rounded-2xl overflow-hidden bg-zinc-800">
                                        <img src="<?= $album['cover_url'] ?>" class="w-full h-full object-cover opacity-50">
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-black uppercase tracking-wider"><?= $album['category_name'] ?></h4>
                                        <p class="text-[9px] font-bold text-blue-500 uppercase"><?= $album['photo_count'] ?> foto's</p>
                                    </div>
                                </div>
                                <a href="gallery.php?page=<?= rawurlencode($album['category_name']) ?>" class="p-3 bg-zinc-800 hover:bg-blue-600 rounded-xl transition">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6M15 3h6v6M10 14L21 3"/></svg>
                                </a>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                </section>
            </div>
        </div>
    </main>
</body>
</html>