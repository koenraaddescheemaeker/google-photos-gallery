<?php
/** * FORCEKES - gallery.php (WebP & WebM Multi-Media Edition) */
require_once 'config.php';

$pageSlug = $_GET['page'] ?? 'museum';
$photos = supabaseRequest("album_photos?category=eq.$pageSlug&select=*&order=created_at.desc", 'GET');
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

        /* Premium Lightbox Styling */
        .sl-overlay { background: rgba(0, 0, 0, 0.98) !important; backdrop-filter: blur(20px); }
        .sl-wrapper .sl-navigation button { color: #3b82f6 !important; opacity: 1 !important; }
        
        /* Video Play Icon Overlay */
        .video-indicator {
            background: rgba(59, 130, 246, 0.9);
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.4);
        }

        /* Download Button: Mobile First Position */
        #forcekes-download {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10000;
            background: #3b82f6;
            color: white;
            border-radius: 99px;
            padding: 12px 24px;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            display: none;
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.5);
        }
        @media (min-width: 768px) {
            #forcekes-download { bottom: 40px; right: 40px; left: auto; transform: none; font-size: 11px; }
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
                <?php foreach ($photos as $p): 
                    $isVid = (strpos($p['image_url'], '.webm') !== false);
                    $url = htmlspecialchars($p['image_url']);
                    $thumb = !empty($p['thumbnail_url']) ? htmlspecialchars($p['thumbnail_url']) : $url;
                ?>
                    <a href="<?= $url ?>" class="group" data-type="<?= $isVid ? 'video' : 'image' ?>">
                        <div class="aspect-square rounded-[1.8rem] md:rounded-[3rem] overflow-hidden border border-white/5 bg-zinc-900 shadow-2xl relative">
                            <?php if ($isVid): ?>
                                <video src="<?= $url ?>#t=0.1" class="w-full h-full object-cover opacity-60 group-hover:opacity-100 transition duration-700" muted playsinline></video>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="video-indicator w-10 h-10 md:w-14 md:h-14 rounded-full flex items-center justify-center pl-1 transition-transform group-hover:scale-110">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="white" viewBox="0 0 24 24" class="w-5 h-5 md:w-7 md:h-7"><path d="M8 5v14l11-7z"/></svg>
                                    </div>
                                </div>
                            <?php else: ?>
                                <img src="<?= $thumb ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-1000" loading="lazy">
                            <?php endif; ?>
                            
                            <div class="absolute inset-0 bg-blue-600/10 opacity-0 group-hover:opacity-100 transition duration-500"></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full py-20 text-center">
                    <p class="text-zinc-600 italic text-sm">Nog geen media in dit album.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <a href="#" id="forcekes-download" download>Download Media</a>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/simplelightbox/2.14.0/simple-lightbox.min.js"></script>
    <script>
        $(document).ready(function() {
            var lightbox = $('.gallery a').simpleLightbox({
                loop: true,
                nav: true,
                close: true,
                swipeClose: true,
                videoRegex: /\.webm/i // Vertel de lightbox dat .webm video's zijn
            });

            lightbox.on('shown.simplelightbox', function (e) {
                var mediaUrl = $(e.element).attr('href');
                $('#forcekes-download').attr('href', mediaUrl).fadeIn(200);
            });

            lightbox.on('close.simplelightbox', function () {
                $('#forcekes-download').fadeOut(200);
            });
        });
    </script>
</body>
</html>