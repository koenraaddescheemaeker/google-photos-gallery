<?php
/** * FORCEKES - admin.php (Fase 9: Ambient & Sound Edition) */
require_once 'config.php';
set_time_limit(3600); ignore_user_abort(true);
$userEmail = isset($_SESSION['user_email']) ? strtolower($_SESSION['user_email']) : '';
if ($userEmail !== 'koen@lauwe.com') { header("Location: index.php"); exit; }

$albumStats = supabaseRequest("rpc/get_album_dashboard", 'GET');
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8"><title>Master Command | Forcekes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;900&display=swap');
        body { background: #020202; color: #fff; font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        .glass-panel { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); backdrop-filter: blur(40px); }
        input { background: #000 !important; border: 1px solid rgba(255,255,255,0.1) !important; color: #fff !important; }
        input:focus { border-color: #3b82f6 !important; }
    </style>
</head>
<body class="bg-black">
    <?php include 'menu.php'; ?>
    <main class="max-w-7xl mx-auto px-10 pt-48 pb-32">
        <header class="mb-24">
            <h1 class="text-5xl font-black italic uppercase tracking-tighter mb-4">Master<span class="text-blue-600">Command</span></h1>
            <p class="text-[9px] font-black uppercase tracking-[0.5em] text-zinc-600">Archief Synchronisatie & Controle</p>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
            <div class="space-y-12">
                <section class="glass-panel p-12 rounded-[3.5rem]" onmouseenter="updateAmbientGlow('rgba(59, 130, 246, 0.1)')" onmouseleave="updateAmbientGlow()">
                    <h3 class="text-[10px] font-black uppercase tracking-widest text-blue-500 mb-8 italic">Data Ingestie</h3>
                    <form action="sync-photos.php" method="GET" class="space-y-6">
                        <input type="url" name="album_url" required placeholder="Google Photos URL..." class="w-full rounded-2xl px-6 py-5 text-xs font-bold uppercase tracking-widest">
                        <input type="text" name="app_name" required placeholder="Nieuwe Categorie..." class="w-full rounded-2xl px-6 py-5 text-xs font-bold uppercase tracking-widest">
                        <button onclick="playSound('click')" class="w-full py-6 bg-blue-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-500 transition-all">Start Scraper</button>
                    </form>
                </section>
            </div>

            <div class="lg:col-span-2">
                <section class="glass-panel p-12 rounded-[3.5rem]">
                    <h3 class="text-xl font-black italic uppercase tracking-tight mb-10 text-white/50">Actieve Archiefstukken</h3>
                    <div class="space-y-4">
                        <?php if (is_array($albumStats)): foreach ($albumStats as $album): ?>
                            <div class="flex items-center justify-between p-6 bg-white/5 rounded-3xl border border-white/5 hover:bg-white/10 transition-colors group">
                                <div>
                                    <h4 class="text-sm font-black uppercase tracking-widest"><?= $album['category_name'] ?></h4>
                                    <p class="text-[9px] font-bold text-zinc-600 mt-1 italic"><?= $album['photo_count'] ?> Bestanden Opgeslagen</p>
                                </div>
                                <div class="flex gap-4">
                                    <button class="p-4 bg-zinc-900 rounded-2xl hover:bg-red-600 transition-all text-red-500 hover:text-white" onclick="playSound('click')">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6"/></svg>
                                    </button>
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