<?php
/** * FORCEKES - index.php (Fase 8: Absolute Elegance) */
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
    <title>Forcekes Portaal | Archief</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700;900&family=Playfair+Display:ital,wght@1,700;1,900&display=swap');
        
        body { 
            background-color: #050505; 
            color: #fff; 
            font-family: 'Inter', sans-serif; 
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        /* Film Grain Overlay */
        .grain::before {
            content: "";
            position: fixed;
            top: -150%; left: -150%;
            width: 300%; height: 300%;
            background-image: url("https://grainy-gradients.vercel.app/noise.svg");
            opacity: 0.04;
            pointer-events: none;
            animation: grain 8s steps(10) infinite;
            z-index: 50;
        }
        @keyframes grain {
            0%, 100% { transform:translate(0, 0) }
            10% { transform:translate(-5%, -10%) }
            50% { transform:translate(-15%, 10%) }
            90% { transform:translate(-10%, 10%) }
        }

        .mesh-bg {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: radial-gradient(circle at 0% 0%, rgba(59, 130, 246, 0.08) 0%, transparent 40%);
            z-index: -1;
        }

        .serif-italic { font-family: 'Playfair Display', serif; font-style: italic; }
        
        .album-card { transition: all 0.7s cubic-bezier(0.2, 1, 0.3, 1); }
        .album-card:hover { transform: translateY(-8px) scale(1.02); }

        .img-zoom {
            transition: transform 1.2s cubic-bezier(0.2, 1, 0.3, 1), filter 1.2s ease;
            filter: grayscale(40%) brightness(0.8);
        }
        .album-card:hover .img-zoom { transform: scale(1.1); filter: grayscale(0%) brightness(1); }

        .glass-ui {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .stagger-in { animation: staggerIn 0.8s cubic-bezier(0.2, 1, 0.3, 1) both; }
        @keyframes staggerIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-black grain">
    <div class="mesh-bg"></div>
    <?php include 'menu.php'; ?>

    <main class="max-w-7xl mx-auto px-8 pt-40 pb-32">
        
        <header class="mb-24 text-center stagger-in" style="animation-delay: 0.1s;">
            <h1 class="text-6xl md:text-8xl font-black italic uppercase tracking-tighter leading-none mb-6">
                Force<span class="text-blue-600">kes</span> Portaal
            </h1>
            <p class="serif-italic text-2xl md:text-3xl text-zinc-400">Het familiearchief, <span class="text-white">vlijmscherp</span> bewaard.</p>
        </header>

        <div class="mb-20 flex justify-center stagger-in" style="animation-delay: 0.2s;">
            <div id="search-container" class="glass-ui relative w-full max-w-2xl rounded-2xl px-8 py-5 flex items-center transition-all duration-500 hover:border-blue-600/30">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.4)" stroke-width="2.5" class="mr-6">
                    <circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" id="search-input" placeholder="Zoek in de geschiedenis..." 
                       class="bg-transparent border-none outline-none text-sm font-medium w-full uppercase tracking-[0.2em] placeholder:text-zinc-700">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-10" id="album-wrapper">
            <?php 
            $i = 0;
            foreach ($albumGrid as $album): 
                $i++;
                $slug = (string)($album['category_name'] ?? '');
                if (empty($slug)) continue;
                $name = ($slug === 'museum') ? 'HET MUSEUM' : strtoupper($slug);
            ?>
                <div class="album-card stagger-in" style="animation-delay: <?= 0.3 + ($i * 0.05) ?>s;" data-name="<?= strtolower($slug) ?>">
                    <a href="gallery.php?page=<?= rawurlencode($slug) ?>" class="relative block aspect-[4/5] overflow-hidden rounded-[3rem] glass-ui group">
                        <img src="<?= htmlspecialchars($album['cover_url'] ?? '') ?>" class="img-zoom absolute inset-0 w-full h-full object-cover" loading="lazy">
                        <div class="absolute inset-0 bg-gradient-to-t from-black via-black/20 to-transparent opacity-80"></div>
                        <div class="absolute inset-0 p-10 flex flex-col justify-end">
                            <h2 class="text-lg font-black uppercase tracking-tighter text-white italic"><?= $name ?></h2>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <script>
        // Real-time Filter
        const searchInput = document.getElementById('search-input');
        const albumCards = document.querySelectorAll('.album-card[data-name]');

        searchInput.addEventListener('input', (e) => {
            const term = e.target.value.toLowerCase().trim();
            albumCards.forEach(card => {
                const name = card.getAttribute('data-name');
                card.style.display = name.includes(term) ? 'block' : 'none';
            });
        });
    </script>
</body>
</html>