<?php
require_once 'config.php';

// Verwerk updates van Album IDs
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_config'])) {
    $slug = $_POST['page_slug'];
    $albumId = $_POST['AF1QipOuaNBIr1MZ2XaDHEvK_MWOvHGQ1UZE694sKXoeC5Vsrskccmkrm6nbj-hsTRIYrA?key=dWtDdmNEZUV1eTUteUFjS0xlMHM1aFI3dGhUcWNn'];
    supabaseRequest("page_configs?page_slug=eq.$slug", 'PATCH', ['google_album_id' => $albumId]);
    $msg = "Configuratie voor $slug bijgewerkt!";
}

$pages = supabaseRequest('page_configs', 'GET');
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Forcekes | Beheer</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white p-12">
    <?php include 'menu.php'; ?>
    
    <div class="max-w-4xl mx-auto">
        <header class="mb-12">
            <h1 class="text-4xl font-black italic uppercase tracking-tighter">Portaal <span class="text-blue-600">Beheer</span></h1>
            <?php if(isset($msg)) echo "<p class='mt-4 text-green-500 font-bold'>$msg</p>"; ?>
        </header>

        <div class="space-y-6">
            <?php foreach ($pages as $p): ?>
                <form method="POST" class="bg-zinc-900 p-8 rounded-[2rem] border border-white/5 flex flex-col md:flex-row gap-6 items-end">
                    <input type="hidden" name="page_slug" value="<?= $p['page_slug'] ?>">
                    <div class="flex-grow">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-zinc-500 mb-2"><?= $p['display_name'] ?> (Slug: <?= $p['page_slug'] ?>)</label>
                        <input type="text" name="google_album_id" value="<?= $p['google_album_id'] ?>" 
                               class="w-full bg-black border border-white/10 rounded-xl px-4 py-3 text-sm focus:border-blue-500 outline-none">
                    </div>
                    <button type="submit" name="save_config" class="bg-blue-600 hover:bg-blue-500 px-8 py-3 rounded-xl text-xs font-black uppercase tracking-widest transition">Opslaan</button>
                </form>
            <?php endforeach; ?>
        </div>

        <div class="mt-12 p-8 bg-blue-600/10 border border-blue-600/20 rounded-[2rem] flex justify-between items-center">
            <div>
                <h3 class="font-bold">Handmatige Synchronisatie</h3>
                <p class="text-xs text-zinc-400">Forceer een update van alle foto's uit de albums.</p>
            </div>
            <a href="sync-photos.php" target="_blank" class="bg-white text-black px-8 py-4 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-blue-500 hover:text-white transition">Nu Syncen</a>
        </div>
    </div>
</body>
</html>