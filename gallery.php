<?php
/** * FORCEKES - gallery.php (Mobile-First & Premium Lightbox) */
require_once 'config.php';

$pageSlug = $_GET['page'] ?? 'museum';

// Haal de gesyncte foto's op uit Supabase
$photos = supabaseRequest("album_photos?category=eq.$pageSlug&select=*&order=created_at.desc", 'GET');

// Dynamische weergavenaam
$displayName = ucfirst(htmlspecialchars($pageSlug));
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forcekes | <?= $displayName ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/simplelightbox/2.14.0/simple-lightbox.min.css" />
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #000; color: #fff; overflow-x: hidden; }

        /* Lightbox Customization */
        .sl-overlay { background: rgba(0, 0, 0, 0.96) !important; backdrop-filter: blur(15px); }
        .sl-wrapper .sl-navigation button { color: #3b82f6 !important; opacity: 1 !important; }
        .sl-wrapper .sl-navigation button.sl-next, 
        .sl-wrapper .sl-navigation button.sl-prev {
            font-size: 2.5rem !important;
            text-shadow: 0 0 15px rgba(59, 130, 246, 0.5);
        }
        @media (min-width: 768px) {
            .sl-wrapper .sl-navigation button.sl-next, 
            .sl-wrapper .sl-navigation button.sl-prev { font-size: 4rem !important; }
        }

        /* Download Button Overlay */
        #forcekes-download {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10000;
            background: #3b82f6;
            color: white;
            border-radius: 99px;
            padding: 10px 20px;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            display: none;
            text-decoration: none;
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.4);
        }
        @media (min-width: 768px) {
            #forcekes-download { bottom: 40px; right: 40px; left: auto; transform: none; font-size: 11px; padding: 14px 28px; }
        }
    </style>
</head>
<body class="bg-black text-white min-h-screen">
    
    <?php include 'menu.php'; ?>
    
    <main class="max-w-7xl mx-auto px-6 py-8 md:py-20 mt-20 md:mt-24">
        <header class="mb-10 md:mb-16">
            <h1 class="text-3xl sm:text-4xl md:text-6xl font-black italic uppercase tracking-tighter leading-none">
                <?= $displayName ?>
            </h1>
            <div class="h-1 md:h-2 w-16 md:w-24 bg-blue-600 mt-4 rounded-full"></div>
        </header>
        
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-8 gallery">
            <?php if (is_array($photos) && !empty($photos)): ?>
                <?php foreach ($photos as $p): ?>
                    <a href="<?= htmlspecialchars($p['image_url']) ?>" class="group">
                        <div class="aspect-square rounded-[1.8rem] md:rounded-[3rem] overflow-hidden border border-white/5 bg-zinc-900 shadow-2xl relative">
                            <img src="<?= htmlspecialchars($p['thumbnail_url']) ?>" 
                                 class="w-full h-full object-cover group-hover:scale-110 transition duration-1000"
                                 loading="lazy">
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full py-20 text-center">
                    <p class="text-zinc-600 italic text-sm">Dit album is momenteel nog leeg.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <a href="#" id="forcekes-download" download>Foto Opslaan</a>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/simplelightbox/2.14.0/simple-lightbox.min.js"></script>
    <script>
        $(document).ready(function() {
            var lightbox = $('.gallery a').simpleLightbox({
                loop: true,
                nav: true,
                close: true,
                counter: true,
                fadeSpeed: 150,
                swipeClose: true,
                history: false // Voorkomt vervuiling van browser-geschiedenis op mobiel
            });

            lightbox.on('shown.simplelightbox', function (e) {
                var imageUrl = $(e.element).attr('href');
                $('#forcekes-download').attr('href', imageUrl).fadeIn(200);
            });

            lightbox.on('close.simplelightbox', function () {
                $('#forcekes-download').fadeOut(200);
            });
        });
    </script>
</body>
</html>