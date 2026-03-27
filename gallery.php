<?php
/** * FORCEKES - gallery.php (Real Lightbox Edition with Download) */
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
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/simplelightbox/2.14.0/simple-lightbox.min.css" integrity="sha512-4Cg4o527195f4eE60c7s/7i5vD793aX5W3t9d1S5G3vV9yK8mC530514m/n4V8wLqjK7q19mC24q2wF1qJ" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #000; color: #fff; }

        /* --- LIGHTBOX CUSTOM STYLES (JavaScript Layer) --- */
        .sl-overlay { background: rgba(0, 0, 0, 0.95) !important; backdrop-filter: blur(15px); }
        
        /* De Pijltjes */
        .sl-wrapper .sl-navigation button {
            color: #3b82f6 !important; /* Forcekes Blauw */
            width: 70px !important;
            height: 70px !important;
            opacity: 1 !important; /* Altijd zichtbaar */
        }
        
        .sl-wrapper .sl-navigation button.sl-next, 
        .sl-wrapper .sl-navigation button.sl-prev {
            font-size: 3.5rem !important; /* Grotere pijlen */
            text-shadow: 0 0 15px rgba(59, 130, 246, 0.6); /* Blauwe gloed voor zichtbaarheid */
        }

        /* De Sluitknop */
        .sl-wrapper .sl-close {
            color: #ffffff !important;
            font-size: 3rem !important;
            top: 20px !important;
            right: 20px !important;
            opacity: 1 !important;
        }

        /* De Teller */
        .sl-wrapper .sl-counter {
            color: #6366f1 !important; /* Indigo kleur */
            font-family: 'Inter', sans-serif;
            font-weight: 900;
            font-size: 14px;
        }

        /* De Download Knop (Zelfgemaakt en toegevoegd in de JS-laag) */
        #forcekes-download {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 10000; /* Bovenop alles */
            background: #3b82f6; /* Forcekes Blauw */
            color: white;
            border-radius: 99px;
            padding: 12px 24px;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            display: none; /* Standaard verborgen */
            text-decoration: none;
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.4);
            transition: all 0.3s ease;
        }
        #forcekes-download:hover { background: #2563eb; transform: translateY(-2px); }
    </style>
</head>
<body class="bg-black text-white min-h-screen relative">
    
    <?php include 'menu.php'; ?>
    
    <main class="max-w-7xl mx-auto px-6 py-20 pt-32">
        <header class="mb-16">
            <h1 class="text-6xl font-black italic uppercase tracking-tighter"><?= htmlspecialchars($pageSlug) ?></h1>
            <div class="h-1.5 w-24 bg-blue-600 mt-4 rounded-full"></div>
        </header>
        
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 gallery">
            <?php if (is_array($photos) && !empty($photos)): ?>
                <?php foreach ($photos as $p): ?>
                    <a href="<?= htmlspecialchars($p['image_url']) ?>" class="group">
                        <div class="aspect-square rounded-[2.5rem] overflow-hidden border border-white/5 bg-zinc-900 shadow-2xl relative">
                            <img src="<?= htmlspecialchars($p['thumbnail_url']) ?>" 
                                 class="w-full h-full object-cover group-hover:scale-110 transition duration-1000"
                                 loading="lazy">
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-zinc-600 italic col-span-full py-20 text-center text-sm">Nog geen foto's gesynchroniseerd voor deze pagina.</p>
            <?php endif; ?>
        </div>
    </main>

    <a href="#" id="forcekes-download" download>Foto Opslaan</a>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/simplelightbox/2.14.0/simple-lightbox.min.js" integrity="sha512-YmGf9W4jYJcR/D95S5bW4p8w8b2U1E1z5p7w3E7o6G/J85E4tM3G5Wd8O4Gf9W1XU2T7p7E7D78U5p3f2Q==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        $(document).ready(function() {
            // Initialiseer SimpleLightbox op de .gallery links
            var lightbox = $('.gallery a').simpleLightbox({
                loop: true,
                nav: true,
                close: true,
                counter: true,
                fadeSpeed: 150,
                swipeClose: true,
                className: 'forcekes-lightbox' // Eigen class voor styling
            });

            // DOWNLOAD LOGICA: We voegen een downloadknop toe in de JS-laag
            lightbox.on('shown.simplelightbox', function (e) {
                // e.element is de oorspronkelijke <a> tag waar op geklikt is
                // We halen de URL van de grote foto coherence coherence coherence coherence coherence coherent coherence coherence coherence coherence op
                var imageUrl = $(e.element).attr('href');
                
                // We updaten de downloadknop en tonen hem
                $('#forcekes-download').attr('href', imageUrl).show();
            });

            lightbox.on('close.simplelightbox', function () {
                // Als de lightbox sluit, verbergen we de downloadknop coherence coherent coherence coherence coherence coherence coherence coherence
                $('#forcekes-download').hide();
            });
        });
    </script>
</body>
</html>