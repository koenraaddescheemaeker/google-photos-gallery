<?php
/** * FORCEKES - gallery.php (Final Custom Modal - MAX DESKTOP SIZE) */
require_once 'config.php';

$pageSlug = $_GET['page'] ?? 'museum';
$photos = supabaseRequest("album_photos?category=eq.$pageSlug&select=*&order=captured_at.desc", 'GET');
$displayName = ucfirst(htmlspecialchars($pageSlug));
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forcekes | <?= $displayName ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        
        body { margin: 0; padding: 0; font-family: 'Inter', sans-serif; background-color: #000; color: #fff; overflow-x: hidden; }

        /* Premium Modal v2.3 Styling - MAX SIZE DESKTOP */
        #forcekes-modal.hidden { display: none; }
        #forcekes-modal { 
            position: fixed; inset: 0; z-index: 9999; 
            display: flex; align-items: center; justify-content: center; 
        }
        #modal-overlay { position: absolute; inset: 0; background-color: rgba(0, 0, 0, 0.98); backdrop-filter: blur(20px); }
        
        /* FIX: Content container centraal en MAXIMAAL breed/hoog */
        #modal-content { 
            position: relative; z-index: 10000; 
            width: 100%; height: 100%; 
            display: flex; align-items: center; justify-content: center; 
            pointer-events: none; 
            /* FIX: Minimale padding voor maximale mediagroote op alle schermen */
            padding: 10px; 
        }
        /* Consistent padding op desktop voor maximale grootte */
        @media (min-width: 768px) { #modal-content { padding: 10px; } }
        
        /* FIX: Media maxi-size, gecentreerd en SCHERP */
        .modal-media { 
            max-width: 100%; max-height: 100%; 
            object-fit: contain; /* Behou aspect ratio, vul maximaal */
            box-shadow: 0 50px 100px -20px rgba(0, 0, 0, 0.8); 
            pointer-events: auto; 
            /* Zorgt voor scherpere weergave bij schalen */
            image-rendering: -webkit-optimize-contrast;
            image-rendering: crisp-edges;
            /* Helpt bij scherp renderen van overgangen */
            will-change: transform;
        }
        .modal-media.hidden { display: none !important; }

        /* Modal Controls - Forceer zichtbaarheid en grootte */
        .modal-btn { position: absolute; z-index: 10010; color: #3b82f6; cursor: pointer; background: rgba(0,0,0,0.5); border: none; padding: 10px; opacity: 0.8; transition: opacity 0.2s; border-radius: 99px; }
        .modal-btn:hover { opacity: 1; background: rgba(0,0,0,0.8); }
        #modal-close { top: 20px; right: 20px; font-size: 2.5rem; font-weight: 300; line-height: 1; padding: 10px 18px; }
        #modal-prev { left: 20px; top: 50%; transform: translateY(-50%); font-size: 3rem; }
        #modal-next { right: 20px; top: 50%; transform: translateY(-50%); font-size: 3rem; }
        #modal-prev svg, #modal-next svg { width: 24px; height: 24px; }
        
        @media (min-width: 768px) {
            #modal-close { font-size: 3.5rem; padding: 10px 22px; }
            #modal-prev { font-size: 4rem; }
            #modal-next { font-size: 4rem; }
            #modal-prev svg, #modal-next svg { width: 32px; height: 32px; }
        }

        /* Download Knop Styling */
        #forcekes-download-btn {
            position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%); z-index: 10100;
            background: #3b82f6; color: white; border-radius: 99px; padding: 14px 28px;
            font-size: 11px; font-weight: 900; text-transform: uppercase; display: none;
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.5); text-decoration: none;
            letter-spacing: 1px; cursor: pointer; border: none;
        }
        @media (min-width: 768px) { #forcekes-download-btn { bottom: 40px; right: 40px; left: auto; transform: none; } }
    </style>
</head>
<body class="bg-black text-white min-h-screen">
    <?php include 'menu.php'; ?>

    <main class="max-w-7xl mx-auto px-6 py-8 md:py-20 mt-20 md:mt-24">
        <header class="mb-10 md:mb-16">
            <h1 class="text-3xl sm:text-4xl md:text-6xl font-black italic uppercase tracking-tighter leading-none"><?= $displayName ?></h1>
            <div class="h-1 md:h-2 w-16 md:w-24 bg-blue-600 mt-4 rounded-full"></div>
        </header>

        <div class="gallery grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-8 gallery-wrapper">
            <?php if (is_array($photos) && !empty($photos)): ?>
                <?php foreach ($photos as $index => $p): 
                    $isVid = (strpos($p['image_url'], '.webm') !== false);
                    $url = htmlspecialchars($p['image_url']);
                ?>
                    <a href="<?= $url ?>" class="gallery-item group" data-index="<?= $index ?>" data-type="<?= $isVid ? 'video' : 'image' ?>">
                        <div class="aspect-square rounded-[1.8rem] md:rounded-[3rem] overflow-hidden border border-white/5 bg-zinc-900 relative">
                            <?php if ($isVid): ?>
                                <video src="<?= $url ?>#t=0.1" class="w-full h-full object-cover opacity-60" muted playsinline></video>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center pl-1"><svg fill="white" viewBox="0 0 24 24" class="w-6 h-6"><path d="M8 5v14l11-7z"/></svg></div>
                                </div>
                            <?php else: ?>
                                <img src="<?= $url ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-1000" loading="lazy">
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <div id="forcekes-modal" class="hidden">
        <div id="modal-overlay"></div>
        <div id="modal-content">
            <img id="modal-img" class="modal-media hidden" src="" alt="">
            <video id="modal-video" class="modal-media hidden" controls autoplay loop playsinline></video>
        </div> 
        <button id="modal-close" class="modal-btn" aria-label="Sluiten">&times;</button>
        <button id="modal-prev" class="modal-btn" aria-label="Vorige"><svg fill="currentColor" viewBox="0 0 24 24"><path d="M15.41 16.59L10.83 12l4.58-4.59L14 6l-6 6 6 6 1.41-1.41z"/></svg></button>
        <button id="modal-next" class="modal-btn" aria-label="Volgende"><svg fill="currentColor" viewBox="0 0 24 24"><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/></svg></button>
    </div>

    <button id="forcekes-download-btn">Media Opslaan</button>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('forcekes-modal');
            const modalImg = document.getElementById('modal-img');
            const modalVideo = document.getElementById('modal-video');
            const modalClose = document.getElementById('modal-close');
            const modalPrev = document.getElementById('modal-prev');
            const modalNext = document.getElementById('modal-next');
            const modalOverlay = document.getElementById('modal-overlay');
            const downloadBtn = document.getElementById('forcekes-download-btn');
            const galleryItems = document.querySelectorAll('.gallery-item');

            let currentIndex = 0;
            let currentMediaUrl = "";

            function openModal(index) {
                const item = galleryItems[index];
                currentMediaUrl = item.href;
                const type = item.getAttribute('data-type');
                currentIndex = index;

                modalImg.classList.add('hidden');
                modalVideo.classList.add('hidden');
                modalVideo.pause();
                modalVideo.src = "";

                if (type === 'video') {
                    modalVideo.src = currentMediaUrl;
                    modalVideo.classList.remove('hidden');
                } else {
                    modalImg.src = currentMediaUrl;
                    modalImg.classList.remove('hidden');
                }

                downloadBtn.style.display = 'block';
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            function closeModal() {
                modal.classList.add('hidden');
                modalVideo.pause();
                modalVideo.src = "";
                modalImg.src = "";
                downloadBtn.style.display = 'none';
                document.body.style.overflow = '';
            }

            function showPrev() {
                currentIndex = (currentIndex - 1 + galleryItems.length) % galleryItems.length;
                openModal(currentIndex);
            }

            function showNext() {
                currentIndex = (currentIndex + 1) % galleryItems.length;
                openModal(currentIndex);
            }

            function startDownload() {
                if (!currentMediaUrl) return;
                window.location.href = 'download.php?file=' + encodeURIComponent(currentMediaUrl);
            }

            galleryItems.forEach((item, index) => {
                item.addEventListener('click', (e) => {
                    e.preventDefault();
                    openModal(index);
                });
            });

            modalClose.addEventListener('click', closeModal);
            modalPrev.addEventListener('click', showPrev);
            modalNext.addEventListener('click', showNext);
            modalOverlay.addEventListener('click', closeModal);
            downloadBtn.addEventListener('click', startDownload);

            document.addEventListener('keydown', (e) => {
                if (modal.classList.contains('hidden')) return;
                if (e.key === 'Escape') closeModal();
                if (e.key === 'ArrowLeft') showPrev();
                if (e.key === 'ArrowRight') showNext();
            });
        });
    </script>
</body>
</html>