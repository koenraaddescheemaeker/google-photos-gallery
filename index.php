<?php
/** * FORCEKES - index.php (Fase 9: Ambient & Elegant) */
require_once 'config.php';
$dashboardData = supabaseRequest("rpc/get_album_dashboard", 'GET');
$albumGrid = $dashboardData ?: [];
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
    </style>
</head>
<body class="overflow-x-hidden">
    <?php include 'menu.php'; ?>
    
    <main class="max-w-7xl mx-auto px-10 pt-48 pb-32">
        <header class="mb-32 text-center">
            <h1 class="text-7xl md:text-[8rem] font-black italic uppercase tracking-tighter leading-none mb-8">Force<span class="text-blue-600">kes</span></h1>
            <p class="serif-italic text-2xl md:text-3xl text-zinc-500 italic">Een reis door de <span class="text-white">tijd</span></p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
            <?php foreach ($albumGrid as $album): ?>
                <div class="album-card group" 
                     onmouseenter="updateAmbientGlow('rgba(59, 130, 246, 0.15)'); playSound('click');"
                     onmouseleave="updateAmbientGlow();">
                    <a href="gallery.php?page=<?= rawurlencode($album['category_name']) ?>" class="relative block aspect-[4/5] overflow-hidden rounded-[3.5rem] bg-zinc-900 border border-white/5">
                        <img src="<?= htmlspecialchars($album['cover_url']) ?>" class="img-zoom absolute inset-0 w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent"></div>
                        <div class="absolute inset-0 p-12 flex flex-col justify-end">
                            <h2 class="serif-italic text-4xl text-white italic"><?= ucfirst($album['category_name']) ?></h2>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>