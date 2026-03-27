<?php
/** FORCEKES - admin.php (Fix Edition) */
require_once 'config.php';

$msg = "";
$error = "";

// 1. Verwerk updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_config'])) {
    $slug = $_POST['page_slug'] ?? null;
    $albumUrl = trim($_POST['google_album_id'] ?? '');
    
    if ($slug && !empty($albumUrl)) {
        // We proberen de PATCH uit te voeren
        $res = supabaseRequest("page_configs?page_slug=eq.$slug", 'PATCH', ['google_album_id' => $albumUrl]);
        
        // Controleer of het gelukt is (Supabase geeft bij PATCH vaak een lege string of null terug bij succes)
        $msg = "Link voor " . htmlspecialchars($slug) . " verstuurd naar database...";
    } else {
        $error = "Vul een geldige link in voor " . htmlspecialchars($slug);
    }
}

// 2. Haal pagina-configs op
$pages = supabaseRequest('page_configs?select=*&order=id.asc', 'GET');
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Portaal Beheer</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white p-12">
    <?php include 'menu.php'; ?>
    
    <div class="max-w-4xl mx-auto mt-20">
        <header class="mb-12">
            <h1 class="text-4xl font-black italic uppercase tracking-tighter">Portaal <span class="text-blue-600">Beheer</span></h1>
            <?php if($msg): ?>
                <div class="mt-4 p-4 bg-blue-500/10 border border-blue-500/20 rounded-xl text-blue-400 text-xs font-bold uppercase tracking-widest">
                    <?= $msg ?> (Vernieuw de pagina om te controleren)
                </div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="mt-4 p-4 bg-red-500/10 border border-red-500/20 rounded-xl text-red-500 text-xs font-bold uppercase tracking-widest">
                    <?= $error ?>
                </div>
            <?php endif; ?>
        </header>

        <div class="space-y-6">
            <?php if (!is_array($pages) || empty($pages)): ?>
                <p class="text-zinc-500 italic">Geen configuraties gevonden. Controleer de tabel 'page_configs'.</p>
            <?php else: ?>
                <?php foreach ($pages as $p): ?>
                    <form method="POST" class="bg-zinc-900 p-8 rounded-[2rem] border border-white/5 flex flex-col md:flex-row gap-6 items-end">
                        <input type="hidden" name="page_slug" value="<?= $p['page_slug'] ?>">
                        <div class="flex-grow w-full">
                            <label class="block text-[10px] font-black uppercase tracking-widest text-zinc-500 mb-2">
                                <?= $p['display_name'] ?> (<?= $p['page_slug'] ?>)
                            </label>
                            <input type="text" name="google_album_id" value="<?= htmlspecialchars($p['google_album_id'] ?? '') ?>" 
                                   placeholder="https://photos.app.goo.gl/..."
                                   class="w-full bg-black border border-white/10 rounded-xl px-4 py-3 text-sm focus:border-blue-500 outline-none transition">
                        </div>
                        <button type="submit" name="save_config" class="w-full md:w-auto bg-blue-600 hover:bg-blue-500 px-8 py-3 rounded-xl text-xs font-black uppercase tracking-widest transition">
                            Opslaan
                        </button>
                    </form>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="mt-12 p-8 bg-zinc-900 border border-white/5 rounded-[2.5rem] flex justify-between items-center">
            <p class="text-xs text-zinc-500 font-bold uppercase tracking-widest">Stap 2: Start de motor</p>
            <a href="sync-photos.php" class="bg-white text-black px-6 py-3 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-blue-500 hover:text-white transition">
                Nu Syncen
            </a>
        </div>
    </div>
</body>
</html>