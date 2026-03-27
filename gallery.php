<?php
/** * FORCEKES - gallery.php (Lightbox with Visible Arrows) */
require_once 'config.php';

$pageSlug = $_GET['page'] ?? 'museum';

// Haal de gesyncte foto's op uit Supabase
$photos = supabaseRequest("album_photos?category=eq.$pageSlug&select=*&order=created_at.desc", 'GET');
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forcekes | <?= ucfirst(htmlspecialchars($pageSlug)) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/simplelightbox/2.14.0/simple-lightbox.min.css" />
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #000; color: #fff; }

        /* --- LIGHTBOX CUSTOM STYLES --- */
        .sl-overlay { background: rgba(0, 0, 0, 0.9) !important; backdrop-filter: blur(10px); }
        
        /* De Pijltjes Fix */
        .sl-wrapper .sl-navigation button {
            width: 60px !important;
            height: 60px !important;
            color: #3b82f6 !important; /* Forcekes Blauw */
            font-weight: bold !important;
        }
        
        /* Maak de pijltjes groter en prominenter */
        .sl-wrapper .sl-navigation button.sl-next, 
        .sl-wrapper .sl-navigation button.sl-prev {
            font-size: 3rem !important; /* Grotere pijlen */
            opacity: 1 !important; /* Altijd zichtbaar */
            text-shadow: 0 0 15px rgba(59, 130, 246, 0.5); /* Blauwe gloed */
        }

        .sl-wrapper .sl-close {
            color: #ffffff !important;
            font-size: 3rem !important;
            top: 30px !important;
            right: 30px !important;
        }

        .sl-wrapper .sl-counter {
            color: #6366f1 !important;
            font-family: 'Inter', sans-serif;
            font-weight: 900;
            font-size: 14px;
        }
    </style>
</head>
<body class="bg-black text-white min-h-screen">
    
    <?php include 'menu.php'; ?>
    
    <main class="max-w-7xl mx-auto px-6 py-20 pt-32">
        <header class="mb-16">
            <h1 class="text-6xl font-black italic uppercase tracking-tighter"><?= htmlspecialchars($pageSlug) ?></h1>
            <div class="h-1.5 w-24 bg-blue-600 mt-4 rounded-full"></div>
        </header>
        
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 gallery-container">
            <?php if (is_array($photos) && !empty($photos)): ?>
                <?php foreach ($photos as $p): ?>
                    <a href="<?= htmlspecialchars($p['image_url']) ?>" class="group">
                        <div class="aspect-square rounded-[2.5rem] overflow-hidden border border-white/5 bg-zinc-900 shadow-2xl relative">
                            <img src="<?= htmlspecialchars($p['thumbnail_url']) ?>" 
                                 class="w-full h-full object-cover group-hover:scale-110 transition duration-1000"
                                 loading="lazy">
                            <div class="absolute inset-0 bg-blue-600/10 opacity-0 group-hover:opacity-100 transition duration-500"></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-zinc-600 italic col-span-full py-20 text-center">Nog geen herinneringen in dit album...</p>
            <?php endif; ?>
        </div>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/simplelightbox/2.14.0/simple-lightbox.vanilla.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var lightbox = new SimpleLightbox('.gallery-container a', {
                nav: true,          // Zorg dat navigatie aan staat
                loop: true,         // Oneindig doorbladeren
                close: true,        // Sluitknop tonen
                showCounter: true,  // Toon 1/10 teller
                fadeSpeed: 200,     // Snelle overgang
                swipeClose: true,   // Swipen op mobiel
                className: 'forcekes-lightbox' // Eigen class voor styling
            });
        });
    </script>
</body>
</html>