<?php
/** * FORCEKES - admin.php (Fase 10: Prioriteitsbeheer) */
require_once 'config.php';
set_time_limit(3600); 
ignore_user_abort(true);

$currentUser = isset($_SESSION['user_email']) ? strtolower($_SESSION['user_email']) : '';
if ($currentUser !== 'koen@lauwe.com') { header("Location: index.php"); exit; }

$statusMessage = ""; $statusType = "info";

// --- ACTIE: PRIORITEIT BIJWERKEN ---
if (isset($_POST['action']) && $_POST['action'] === 'update_priority') {
    $category = $_POST['category'];
    $priority = (int)$_POST['priority'];
    
    // We updaten alle foto's in deze categorie met de nieuwe prioriteit
    // (In een perfecte wereld is er een 'categories' tabel, maar dit werkt direct met je huidige setup)
    supabaseRequest("album_photos?category=eq." . rawurlencode($category), "PATCH", ['priority' => $priority]);
    
    $statusMessage = "Prioriteit voor '$category' bijgewerkt naar $priority.";
    $statusType = "info";
}

$albumStats = supabaseRequest("rpc/get_album_dashboard", 'GET');
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8"><title>Master Control | Forcekes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;900&display=swap');
        body { background: #020202; color: #fff; font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        .glass-panel { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); backdrop-filter: blur(40px); }
        input { background: #000 !important; border: 1px solid rgba(255,255,255,0.1) !important; color: #fff !important; }
    </style>
</head>
<body class="bg-black">
    <?php include 'menu.php'; ?>
    <main class="max-w-7xl mx-auto px-10 pt-48 pb-32">
        <header class="mb-24 flex justify-between items-end">
            <div>
                <h1 class="text-5xl font-black italic uppercase tracking-tighter">Master<span class="text-blue-600">Control</span></h1>
                <p class="text-[9px] font-black uppercase tracking-[0.5em] text-zinc-600">Systeembeheer & Volgorde</p>
            </div>
            <?php if ($statusMessage): ?>
                <div class="px-6 py-3 rounded-full text-[10px] font-black uppercase border border-blue-500 text-blue-500"><?= $statusMessage ?></div>
            <?php endif; ?>
        </header>

        <section class="glass-panel p-12 rounded-[3.5rem]">
            <h3 class="text-xl font-black italic uppercase tracking-tight mb-10 text-white/50">Beheer Album Volgorde</h3>
            <div class="space-y-6">
                <?php if (is_array($albumStats)): foreach ($albumStats as $album): ?>
                    <div class="flex items-center justify-between p-6 bg-white/5 rounded-3xl border border-white/5">
                        <div class="flex-grow">
                            <h4 class="text-xs font-black uppercase tracking-widest"><?= $album['category_name'] ?></h4>
                            <p class="text-[9px] font-bold text-zinc-600 mt-1 italic"><?= $album['photo_count'] ?> Bestanden</p>
                        </div>
                        
                        <form action="admin.php" method="POST" class="flex items-center gap-4">
                            <input type="hidden" name="action" value="update_priority">
                            <input type="hidden" name="category" value="<?= $album['category_name'] ?>">
                            <div class="flex flex-col items-center">
                                <label class="text-[8px] font-black text-zinc-500 uppercase mb-1">Positie</label>
                                <input type="number" name="priority" value="<?= $album['priority'] ?? 999 ?>" class="w-16 text-center py-2 rounded-lg text-xs font-bold">
                            </div>
                            <button type="submit" class="p-3 bg-blue-600 rounded-xl hover:bg-blue-500 transition-all">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </button>
                        </form>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </section>
    </main>
</body>
</html>