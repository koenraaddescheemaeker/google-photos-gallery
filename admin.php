<?php
/** * FORCEKES - admin.php (Fase 10: Backup & Management) */
require_once 'config.php';
set_time_limit(3600); 

$currentUser = isset($_SESSION['user_email']) ? strtolower($_SESSION['user_email']) : '';
if ($currentUser !== 'koen@lauwe.com') { header("Location: index.php"); exit; }

// --- ACTIE: BACKUP EXPORT (CSV) ---
if (isset($_GET['action']) && $_GET['action'] === 'export_backup') {
    $data = supabaseRequest("album_photos?select=*", 'GET');
    if (is_array($data)) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="forcekes_backup_'.date('Y-m-d').'.csv"');
        $output = fopen('php://output', 'w');
        if (!empty($data)) fputcsv($output, array_keys($data[0])); // Kolomnamen
        foreach ($data as $row) fputcsv($output, $row); // De data zelf
        fclose($output);
        exit;
    }
}

$statusMessage = ""; 
$statusType = "info";

// --- ACTIE: UPDATE SETTINGS (PRIORITEIT & ZICHTBAARHEID) ---
if (isset($_POST['action']) && $_POST['action'] === 'update_album_settings') {
    $category = $_POST['category'];
    $priority = (int)$_POST['priority'];
    $isVisible = isset($_POST['is_visible']) ? true : false;
    
    supabaseRequest("album_photos?category=eq." . rawurlencode($category), "PATCH", [
        'priority' => $priority,
        'is_visible' => $isVisible
    ]);
    
    $statusMessage = "Instellingen voor '$category' bijgewerkt.";
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
        input[type="number"] { background: #000; border: 1px solid rgba(255,255,255,0.1); color: #fff; text-align: center; }
    </style>
</head>
<body class="bg-black">
    <?php include 'menu.php'; ?>
    <main class="max-w-7xl mx-auto px-10 pt-48 pb-32">
        <header class="mb-24 flex justify-between items-end">
            <div>
                <h1 class="text-5xl font-black italic uppercase tracking-tighter mb-4">Master<span class="text-blue-600">Control</span></h1>
                <?php if ($statusMessage): ?>
                    <p class="text-blue-500 text-[10px] font-black uppercase tracking-widest"><?= $statusMessage ?></p>
                <?php endif; ?>
            </div>
            
            <a href="admin.php?action=export_backup" class="px-8 py-4 bg-zinc-900 hover:bg-white hover:text-black rounded-2xl text-[9px] font-black uppercase tracking-[0.3em] transition-all flex items-center gap-3">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                Backup Archief
            </a>
        </header>

        <section class="glass-panel p-12 rounded-[3.5rem]">
            <div class="space-y-6">
                <?php if (is_array($albumStats)): foreach ($albumStats as $album): 
                    $visible = $album['is_visible'] ?? true;
                ?>
                    <div class="flex items-center justify-between p-8 bg-white/5 rounded-[2rem] border border-white/5">
                        <div class="flex-grow">
                            <h4 class="text-xs font-black uppercase tracking-widest <?= !$visible ? 'text-zinc-600' : '' ?>"><?= $album['category_name'] ?></h4>
                        </div>
                        
                        <form action="admin.php" method="POST" class="flex items-center gap-8">
                            <input type="hidden" name="action" value="update_album_settings">
                            <input type="hidden" name="category" value="<?= $album['category_name'] ?>">
                            
                            <div class="flex flex-col items-center">
                                <span class="text-[7px] font-black text-zinc-500 uppercase mb-2">Positie</span>
                                <input type="number" name="priority" value="<?= $album['priority'] ?? 999 ?>" class="w-16 py-2 rounded-xl text-xs font-bold">
                            </div>

                            <div class="flex flex-col items-center">
                                <span class="text-[7px] font-black text-zinc-500 uppercase mb-2">Zichtbaar</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_visible" class="sr-only peer" <?= $visible ? 'checked' : '' ?> onchange="this.form.submit()">
                                    <div class="w-11 h-6 bg-zinc-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-blue-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                                </label>
                            </div>

                            <button type="submit" class="p-4 bg-white/5 hover:bg-blue-600 rounded-2xl transition-all">
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