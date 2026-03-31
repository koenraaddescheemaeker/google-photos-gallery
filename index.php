<?php
require_once 'config.php';

// DEBUG BLOCK: Haal dit weg als alles werkt
$dashboardData = supabaseRequest("rpc/get_album_dashboard", 'GET');

// Als dit 'null' of een error is, willen we weten waarom
if (!$dashboardData || isset($dashboardData['error'])) {
    $debugError = $dashboardData['error'] ?? 'Onbekende verbindingsfout';
}

$albumGrid = (is_array($dashboardData) && !isset($dashboardData['error'])) ? $dashboardData : [];

// Sorteren
if (!empty($albumGrid)) {
    usort($albumGrid, function($a, $b) {
        $pA = $a['priority'] ?? 999; $pB = $b['priority'] ?? 999;
        return ($pA !== $pB) ? ($pA <=> $pB) : strcmp((string)$a['category_name'], (string)$b['category_name']);
    });
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forcekes Portaal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;900&family=Playfair+Display:ital,wght@1,900&display=swap');
        body { background: #000; color: #fff; font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        .serif-italic { font-family: 'Playfair Display', serif; font-style: italic; }
        .grain::before { content: ""; position: fixed; top: -150%; left: -150%; width: 300%; height: 300%; background-image: url("https://grainy-gradients.vercel.app/noise.svg"); opacity: 0.03; pointer-events: none; animation: grain 8s steps(10) infinite; z-index: 200; }
        @keyframes grain { 0%, 100% { transform:translate(0,0) } 10% { transform:translate(-5%,-10%) } }
    </style>
</head>
<body class="grain">
    <?php include 'menu.php'; ?>
    <main class="max-w-7xl mx-auto px-10 pt-48 pb-32">
        <header class="mb-32 text-center">
            <h1 class="text-7xl md:text-[9rem] font-black italic uppercase tracking-tighter leading-none mb-8">Force<span class="text-blue-600">kes</span></h1>
            <p class="serif-italic text-2xl md:text-4xl text-zinc-500">Archief van de <span class="text-white">Toekomst</span></p>
        </header>

        <?php if (!empty($debugError)): ?>
            <div class="bg-red-900/20 border border-red-500 p-8 rounded-3xl text-center">
                <p class="text-red-500 font-bold uppercase tracking-widest text-xs mb-2">Verbindingsfout</p>
                <p class="text-white font-mono text-sm"><?= htmlspecialchars($debugError) ?></p>
            </div>
        <?php elseif (empty($albumGrid)): ?>
            <div class="bg-zinc-900/50 border border-white/5 p-20 rounded-[4rem] text-center">
                <p class="serif-italic text-2xl text-zinc-500">Het archief is momenteel stil...</p>
                <p class="text-[8px] uppercase tracking-[0.5em] text-zinc-700 mt-4 italic">Geen data ontvangen van Supabase</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-16">
                <?php foreach ($albumGrid as $album): 
                    if (($album['is_visible'] ?? true) == false) continue;
                    $name = $album['category_name'] ?? 'Onbekend';
                    $img = !empty($album['thumbnail_url']) ? $album['thumbnail_url'] : ($album['cover_url'] ?? '');
                ?>
                <div class="group relative aspect-[3/4] overflow-hidden rounded-[3.5rem] bg-zinc-900 border border-white/5 transition-all duration-700 hover:scale-[1.02] hover:-translate-y-4">
                    <a href="gallery.php?page=<?= rawurlencode($name) ?>" class="block h-full">
                        <img src="<?= htmlspecialchars($img) ?>" class="absolute inset-0 w-full h-full object-cover opacity-60 group-hover:opacity-100 transition-opacity duration-1000">
                        <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent"></div>
                        <div class="absolute inset-0 p-12 flex flex-col justify-end">
                            <h2 class="serif-italic text-4xl italic text-white"><?= ucfirst($name) ?></h2>
                            <p class="text-[8px] font-black uppercase text-blue-500 tracking-widest mt-4 opacity-0 group-hover:opacity-100 transition-all"><?= (int)$album['photo_count'] ?> Bestanden</p>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>