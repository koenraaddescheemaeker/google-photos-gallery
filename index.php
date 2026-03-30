<?php
/** * FORCEKES - index.php (Fase 8: Absolute Elegance + Fixed Clicks) */
require_once 'config.php';

$dashboardData = supabaseRequest("rpc/get_album_dashboard", 'GET');
$albumGrid = (is_array($dashboardData) && !isset($dashboardData['error'])) ? $dashboardData : [];

if (!empty($albumGrid)) {
    usort($albumGrid, function($a, $b) {
        return strcmp((string)($a['category_name'] ?? ''), (string)($b['category_name'] ?? ''));
    });
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Forcekes Portaal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700;900&family=Playfair+Display:ital,wght@1,700;1,900&display=swap');
        body { background: #050505; color: #fff; font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; overflow-x: hidden; }
        .grain::before { content: ""; position: fixed; top: -150%; left: -150%; width: 300%; height: 300%; background-image: url("https://grainy-gradients.vercel.app/noise.svg"); opacity: 0.04; pointer-events: none; animation: grain 8s steps(10) infinite; z-index: 200; }
        @keyframes grain { 0%, 100% { transform:translate(0, 0) } 10% { transform:translate(-5%, -10%) } 50% { transform:translate(-15%, 10%) } }
        .serif-italic { font-family: 'Playfair Display', serif; font-style: italic; }
        .album-card { transition: all 0.8s cubic-bezier(0.2, 1, 0.3, 1); }
        .album-card:hover { transform: translateY(-12px); z-index: 10; }
        .img-zoom { transition: transform 1.5s cubic-bezier(0.2, 1, 0.3, 1); filter: grayscale(30%) brightness(0.7); }
        .album-card:hover .img-zoom { transform: scale(1.1); filter: grayscale(0%) brightness(1); }
        .stagger { animation: fadeUp 1.2s cubic-bezier(0.2, 1, 0.3, 1) both; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }
        .glass-ui { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.08); }
    </style>
</head>
<body class="grain">
    <?php include 'menu.php'; ?>
    <main class="max-w-7xl mx-auto px-10 pt-48 pb-32">
        <header class="mb-32 text-center stagger" style="animation-delay: 0.1s;">
            <h1 class="text-7xl md:text-[9rem] font-black italic uppercase tracking-tighter leading-none mb-8">Force<span class="text-blue-600">kes</span></h1>
            <p class="serif-italic text-2xl md:text-4xl text-zinc-500">Documentatie van een <span class="text-white">Gezamenlijk Pad</span></p>
        </header>

        <div class="mb-24 flex justify-center stagger" style="animation-delay: 0.2s;">
            <div class="relative w-full max-w-2xl">
                <input type="text" id="search-input" placeholder="Zoek in het archief..." class="w-full bg-white/5 border border-white/5 rounded-2xl px-10 py-6 text-sm uppercase tracking-widest outline-none focus:border-blue-600/50 transition-all text-center">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-16" id="album-wrapper">
            <div class="album-card stagger" style="animation-delay: 0.25s;">
                <a href="zwaaikamer.php" class="relative block aspect-[3/4] overflow-hidden rounded-[3.5rem] glass-ui group transition-all duration-500">
                    <div class="absolute inset-0 flex flex-col items-center justify-center p-10 text-center z-20">
                        <div class="mb-6 w-16 h-16 rounded-full border border-blue-600/30 flex items-center justify-center group-hover:bg-blue-600 transition-all">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blue-500 group-hover:text-white"><path d="M23 7l-7 5 7 5V7z"></path><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg>
                        </div>
                        <h2 class="serif-italic text-3xl text-white">Zwaaikamer</h2>
                    </div>
                    <div class="absolute inset-0 bg-blue-600/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                </a>
            </div>

            <?php $i = 0; foreach ($albumGrid as $album): $i++; $slug = $album['category_name']; ?>
                <div class="album-card stagger" style="animation-delay: <?= 0.3 + ($i * 0.05) ?>s;" data-name="<?= strtolower($slug) ?>">
                    <a href="gallery.php?page=<?= rawurlencode($slug) ?>" class="relative block aspect-[3/4] overflow-hidden rounded-[3.5rem] bg-zinc-900 border border-white/5 group">
                        <img src="<?= htmlspecialchars($album['cover_url'] ?? '') ?>" class="img-zoom absolute inset-0 w-full h-full object-cover" loading="lazy">
                        <div class="absolute inset-0 bg-gradient-to-t from-black via-black/20 to-transparent opacity-90 transition-opacity group-hover:opacity-70"></div>
                        <div class="absolute inset-0 p-12 flex flex-col justify-end z-10">
                            <p class="text-[9px] font-black uppercase text-blue-500 tracking-[0.4em] mb-4 opacity-0 group-hover:opacity-100 transition-all translate-y-4 group-hover:translate-y-0"><?= (int)$album['photo_count'] ?> Bestanden</p>
                            <h2 class="serif-italic text-4xl text-white"><?= ucfirst($slug) ?></h2>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
    <script>
        const s = document.getElementById('search-input');
        const c = document.querySelectorAll('.album-card');
        s.addEventListener('input', e => {
            const t = e.target.value.toLowerCase().trim();
            c.forEach(card => { 
                if(card.getAttribute('data-name')) {
                    card.style.display = card.getAttribute('data-name').includes(t) ? 'block' : 'none';
                }
            });
        });
    </script>
</body>
</html>