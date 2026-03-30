<?php
/** * FORCEKES - index.php (Fase 7: Search & Performance) */
require_once 'config.php';

// Data ophalen via RPC
$dashboardData = supabaseRequest("rpc/get_album_dashboard", 'GET');
$albumGrid = (is_array($dashboardData) && !isset($dashboardData['error'])) ? $dashboardData : [];

// Sorteren op naam
if (!empty($albumGrid)) {
    usort($albumGrid, function($a, $b) {
        return strcmp((string)($a['category_name'] ?? ''), (string)($b['category_name'] ?? ''));
    });
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forcekes | Portaal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { background-color: #000; color: #fff; font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        .zwaai-card { background: #050505; border: 1px solid rgba(59, 130, 246, 0.2); }
        .album-card { transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); }
        img { transition: filter 0.8s, transform 0.8s; filter: grayscale(100%) opacity(0.5); content-visibility: auto; }
        .group:hover img { filter: grayscale(0%) opacity(1); transform: scale(1.05); }
        
        /* Custom Search Style */
        #search-input::placeholder { color: rgba(255,255,255,0.2); text-transform: uppercase; letter-spacing: 0.1em; font-size: 10px; font-weight: 900; }
        .search-container { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); }
        
        @media (min-width: 1024px) { .grid { gap: 2rem; } }
    </style>
</head>
<body class="bg-black">
    <?php include 'menu.php'; ?>

    <main class="max-w-7xl mx-auto px-6 pt-32 pb-20">
        
        <div class="mb-12 flex justify-center">
            <div class="search-container relative w-full max-w-md rounded-full px-6 py-3 flex items-center focus-within:border-blue-600/50 transition-all">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="3" class="mr-4">
                    <circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" id="search-input" placeholder="Zoek een herinnering..." 
                       class="bg-transparent border-none outline-none text-xs font-bold w-full uppercase tracking-widest">
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 md:gap-6" id="album-wrapper">
            
            <a href="zwaaikamer.php" class="zwaai-card group relative block aspect-square overflow-hidden rounded-[2rem] transition-all duration-500">
                <div class="absolute inset-0 flex flex-col items-center justify-center p-4 text-center">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" class="mb-3">
                        <path d="M23 7l-7 5 7 5V7z"></path><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
                    </svg>
                    <h2 class="text-[10px] font-black uppercase tracking-widest text-white leading-tight">Zwaai<br>Kamer</h2>
                    <div class="mt-3 w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></div>
                </div>
            </a>

            <?php 
            $count = 0;
            foreach ($albumGrid as $album): 
                $count++;
                $slug = (string)($album['category_name'] ?? '');
                if (empty($slug)) continue;
                $name = ($slug === 'museum') ? 'HET MUSEUM' : strtoupper($slug);
                $isPriority = ($count <= 6);
            ?>
                <div class="album-card" data-name="<?= strtolower($slug) ?>">
                    <a href="gallery.php?page=<?= rawurlencode($slug) ?>" class="group relative block aspect-square overflow-hidden rounded-[2rem] border border-white/5 bg-zinc-900 transition-all duration-700">
                        <img 
                            src="<?= htmlspecialchars($album['cover_url'] ?? '') ?>" 
                            class="absolute inset-0 w-full h-full object-cover" 
                            loading="<?= $isPriority ? 'eager' : 'lazy' ?>"
                            fetchpriority="<?= $isPriority ? 'high' : 'low' ?>"
                            decoding="async"
                            alt="<?= $name ?>"
                        >
                        <div class="absolute inset-0 bg-gradient-to-t from-black/95 via-black/20 to-transparent"></div>
                        <div class="absolute bottom-5 left-5 right-5 text-center">
                            <h2 class="text-[9px] font-black uppercase tracking-widest text-white truncate"><?= $name ?></h2>
                            <span class="text-[8px] font-bold text-blue-500 uppercase mt-1 block"><?= (int)($album['photo_count'] ?? 0) ?> FOTO'S</span>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="no-results" class="hidden text-center py-20">
            <p class="text-[10px] font-black uppercase tracking-[0.4em] text-zinc-700 italic">Niets gevonden in de archieven...</p>
        </div>
    </main>

    <script>
        const searchInput = document.getElementById('search-input');
        const albumCards = document.querySelectorAll('.album-card');
        const noResults = document.getElementById('no-results');

        searchInput.addEventListener('input', (e) => {
            const term = e.target.value.toLowerCase().trim();
            let hasVisible = false;

            albumCards.forEach(card => {
                const name = card.getAttribute('data-name');
                if (name.includes(term)) {
                    card.style.display = 'block';
                    hasVisible = true;
                } else {
                    card.style.display = 'none';
                }
            });

            noResults.style.display = hasVisible ? 'none' : 'block';
        });
    </script>
</body>
</html>