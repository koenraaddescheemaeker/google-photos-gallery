<?php
/** * FORCEKES - index.php (Fase 9: Thumbnail Optimized Grid) */
require_once 'config.php';

$dashboardData = supabaseRequest("rpc/get_album_dashboard", 'GET');
$albumGrid = $dashboardData ?: [];

if (!empty($albumGrid)) {
    usort($albumGrid, function($a, $b) {
        return strcmp((string)($a['category_name'] ?? ''), (string)($b['category_name'] ?? ''));
    });
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8"><title>Forcekes Portaal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;900&family=Playfair+Display:ital,wght@1,900&display=swap');
        body { background: #000; color: #fff; font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        .serif-italic { font-family: 'Playfair Display', serif; font-style: italic; }
        .album-card { transition: all 0.8s cubic-bezier(0.2, 1, 0.3, 1); }
        .album-card:hover { transform: translateY(-10px) scale(1.02); }
        .img-zoom { transition: transform 1.5s ease; filter: brightness(0.6); }
        .album-card:hover .img-zoom { transform: scale(1.1); filter: brightness(1); }
        .grain::before { content: ""; position: fixed; top: -150%; left: -150%; width: 300%; height: 300%; background-image: url("https://grainy-gradients.vercel.app/noise.svg"); opacity: 0.04; pointer-events: none; animation: grain 8s steps(10) infinite; z-index: 200; }
        @keyframes grain { 0%, 100% { transform:translate(0, 0) } 10% { transform:translate(-5%, -10%) } }
    </style>
</head>
<body class="grain overflow-x-hidden">
    <?php include 'menu.php'; ?>
    
    <main class="max-w-7xl mx-auto px-10 pt-48 pb-32">
        <header class="mb-32 text-center">
            <h1 class="text-7xl md:text-[8rem] font-black italic uppercase tracking-tighter leading-none mb-8">Force<span class="text-blue-600">kes</span></h1>
            <p class="serif-italic text-2xl md:text-3xl text-zinc-500 italic">Archief van de <span class="text-white">Toekomst</span></p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
            <?php foreach ($albumGrid as $album): 
                // OPTIMALISATIE: We gebruiken de thumbnail_url voor de covers
                // Als de thumbnail nog niet gemigreerd is, valt hij terug op de cover_url
                $displayUrl = !empty($album['thumbnail_url']) ? $album['thumbnail_url'] : $album['cover_url'];
            ?>
                <div class="album-card group" onmouseenter="playSound('click')">
                    <a href="gallery.php?page=<?= rawurlencode($album['category_name']) ?>" class="relative block aspect-[4/5] overflow-hidden rounded-[3.5rem] bg-zinc-900 border border-white/5">
                        <img src="<?= htmlspecialchars($displayUrl) ?>" class="img-zoom absolute inset-0 w-full h-full object-cover" loading="lazy">
                        <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent"></div>
                        <div class="absolute inset-0 p-12 flex flex-col justify-end">
                            <h2 class="serif-italic text-4xl text-white italic"><?= ucfirst($album['category_name']) ?></h2>
                            <p class="text-[8px] font-black uppercase text-blue-500 tracking-[0.3em] mt-4 opacity-0 group-hover:opacity-100 transition-all"><?= $album['photo_count'] ?> Bestanden</p>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>