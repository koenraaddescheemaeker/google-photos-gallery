<?php
/** * FORCEKES - gallery.php (Fixed Albums: Museum & Joris) */
require_once 'config.php';

// De slug bepaalt welk album we laden (standaard museum)
$pageSlug = $_GET['page'] ?? 'museum';
$photos = supabaseRequest("album_photos?category=eq.$pageSlug&select=*&order=captured_at.desc", 'GET');

// Dynamische naam voor de titels
$displayName = ($pageSlug === 'joris') ? 'Joris' : ucfirst(htmlspecialchars($pageSlug));

$hasError = false;
$errorMessage = "";

if (!is_array($photos)) {
    $hasError = true;
    $errorMessage = "Databaseverbinding mislukt.";
} elseif (isset($photos['message'])) {
    $hasError = true;
    $errorMessage = "Supabase meldt: " . $photos['message'];
}
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
        
        /* Modal - Solid Black */
        #forcekes-modal { position: fixed; inset: 0; z-index: 9999; display: none; align-items: center; justify-content: center; background-color: #000; }
        #modal-content { position: relative; z-index: 10000; width: 95vw; height: 85vh; display: flex; align-items: center; justify-content: center; pointer-events: none; }
        .modal-media { width: 100% !important; height: 100% !important; object-fit: contain; pointer-events: auto; image-rendering: -webkit-optimize-contrast; }
        .modal-media.hidden { display: none !important; }

        .modal-btn { position: absolute; z-index: 10010; color: #3b82f6; background: rgba(255,255,255,0.05); border: none; cursor: pointer; border-radius: 99px; transition: all 0.2s ease; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(5px); }
        .modal-btn:hover { background: #3b82f6; color: white; transform: scale(1.1); }
        #modal-close { top: 25px; right: 25px; width: 60px; height: 60px; font-size: 3rem; }
        #modal-prev { left: 20px; top: 50%; transform: translateY(-50%); width: 60px; height: 60px; }
        #modal-next { right: 20px; top: 50%; transform: translateY(-50%); width: 60px; height: 60px; }

        #forcekes-download-btn { position: fixed; bottom: 30px; right: 30px; z-index: 10100; background: #3b82f6; color: white; border-radius: 99px; padding: 16px 32px; font-size: 11px; font-weight: 900; text-transform: uppercase; display: none; box-shadow: 0 10px 40px rgba(59, 130, 246, 0.6); border: none; letter-spacing: 2px; cursor: pointer; }
    </style>
</head>
<body class="bg-black">
    <?php include 'menu.php'; ?>

    <main class="max-w-7xl mx-auto px-6 py-8 md:py-20 mt-20">
        <header class="mb-10 md:mb-16">
            <h1 class="text-3xl md:text-6xl font-black italic uppercase tracking-tighter leading-none"><?= $displayName ?></h1>
            <div class="h-1 md:h-2 w-16 md:w-24 bg-blue-600 mt-4 rounded-full"></div>
        </header>

        <?php if ($hasError): ?>
            <div class="bg-zinc-900 border border-red-900/50 p-8 rounded-[2rem] text-center">
                <p class="text-red-500 font-bold"><?= htmlspecialchars($errorMessage) ?></p>
            </div>
        <?php else: ?>
            <div class="gallery grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-8">
                <?php if (!empty($photos)): ?>
                    <?php foreach ($photos as $index => $p): 
                        if (!is_array($p) || !isset($p['image_url'])) continue;
                        $url = htmlspecialchars($p['image_url']);
                        $isVid = (strpos($url, '.webm') !== false);
                    ?>
                        <a href="<?= $url ?>" class="gallery-item group" data-index="<?= $index ?>" data-type="<?= $isVid ? 'video' : 'image' ?>">
                            <div class="aspect-square rounded-[1.8rem] md:rounded-[3rem] overflow-hidden border border-white/5 bg-zinc-900 relative">
                                <?php if ($isVid): ?>
                                    <video src="<?= $url ?>#t=0.1" class="w-full h-full object-cover opacity-60" muted playsinline></video>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center pl-1"><svg fill="white" viewBox="0 0 24 24" class="w-6 h-6"><path d="M8 5v14l11-7z"/></svg></div>
                                    </div>
                                <?php else: ?>
                                    <img src="<?= $url ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-700" loading="lazy">
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-full py-20 text-center border border-dashed border-white/10 rounded-[3rem]">
                        <p class="text-zinc-500 italic">Dit gedeelte van <strong><?= strtolower($displayName) ?></strong> is momenteel leeg...</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>

    <div id="forcekes-modal">
        <button id="modal-close" class="modal-btn">&times;</button>
        <button id="modal-prev" class="modal-btn"><svg width="30" height="30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg></button>
        <div id="modal-content">
            <img id="modal-img" class="modal-media hidden">
            <video id="modal-video" class="modal-media hidden" controls autoplay loop playsinline></video>
        </div>
        <button id="modal-next" class="modal-btn"><svg width="30" height="30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg></button>
    </div>

    <button id="forcekes-download-btn">Media Opslaan</button>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('forcekes-modal');
            const modalImg = document.getElementById('modal-img');
            const modalVideo = document.getElementById('modal-video');
            const downloadBtn = document.getElementById('forcekes-download-btn');
            const galleryItems = document.querySelectorAll('.gallery-item');
            let currentIndex = 0; let currentMediaUrl = "";

            function openModal(index) {
                const item = galleryItems[index]; if(!item) return;
                currentMediaUrl = item.href; currentIndex = index;
                modalImg.classList.add('hidden'); modalVideo.classList.add('hidden');
                modalVideo.pause(); modalVideo.src = "";
                if (item.getAttribute('data-type') === 'video') { modalVideo.src = currentMediaUrl; modalVideo.classList.remove('hidden'); }
                else { modalImg.src = currentMediaUrl; modalImg.classList.remove('hidden'); }
                modal.style.display = 'flex'; downloadBtn.style.display = 'block'; document.body.style.overflow = 'hidden';
            }
            function closeModal() { modal.style.display = 'none'; modalVideo.pause(); modalVideo.src = ""; downloadBtn.style.display = 'none'; document.body.style.overflow = ''; }
            function navigate(direction) { currentIndex = (currentIndex + direction + galleryItems.length) % galleryItems.length; openModal(currentIndex); }
            galleryItems.forEach((item, index) => { item.addEventListener('click', (e) => { e.preventDefault(); openModal(index); }); });
            document.getElementById('modal-close').onclick = closeModal;
            document.getElementById('modal-prev').onclick = (e) => { e.stopPropagation(); navigate(-1); };
            document.getElementById('modal-next').onclick = (e) => { e.stopPropagation(); navigate(1); };
            downloadBtn.onclick = () => { window.location.href = 'download.php?file=' + encodeURIComponent(currentMediaUrl); };
            document.addEventListener('keydown', (e) => { if (modal.style.display !== 'flex') return; if (e.key === 'Escape') closeModal(); if (e.key === 'ArrowLeft') navigate(-1); if (e.key === 'ArrowRight') navigate(1); });
            modal.onclick = (e) => { if (e.target === modal || e.target.id === 'modal-content') closeModal(); };
        });
    </script>
</body>
</html>