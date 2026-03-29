<?php
/**
 * FORCEKES - gallery.php (Final Premium Version)
 * Bevat de volledige grid-logica en de interactieve lightbox.
 */
require_once 'config.php';

// 1. Categorie bepalen en beveiligen
$pageSlug = isset($_GET['page']) ? $_GET['page'] : 'museum';
$encodedSlug = rawurlencode($pageSlug);

// 2. Naamgeving bepalen (voorkomt de 'Undefined variable' error)
if (strtolower($pageSlug) === 'joris') {
    $displayName = 'Joris';
} elseif (strtolower($pageSlug) === 'museum') {
    $displayName = 'Het Museum';
} else {
    $displayName = ucfirst(htmlspecialchars($pageSlug));
}

// 3. Foto's ophalen uit Supabase
$photos = supabaseRequest("album_photos?category=eq.$encodedSlug&select=*&order=captured_at.desc", 'GET');

$hasError = false;
if ($photos === null || (isset($photos['error']) && $photos['error'] === true)) {
    $hasError = true;
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
        body { background-color: #000; color: #fff; font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        .glass { background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.05); backdrop-filter: blur(20px); }
        #forcekes-modal.active { display: flex; opacity: 1; }
    </style>
</head>
<body class="bg-black">
    <?php include 'menu.php'; ?>

    <main class="max-w-7xl mx-auto px-6 py-32">
        <header class="mb-12">
            <h1 class="text-4xl md:text-6xl font-black italic uppercase tracking-tighter"><?= $displayName ?></h1>
            <div class="h-1.5 w-20 bg-blue-600 mt-4 rounded-full"></div>
        </header>

        <?php if ($hasError): ?>
            <div class="py-20 text-center glass rounded-[3rem] border border-red-900/20">
                <p class="text-red-500 font-bold uppercase tracking-widest text-xs">Databaseverbinding mislukt</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <?php if (!empty($photos) && is_array($photos)): ?>
                    <?php foreach ($photos as $index => $p): 
                        $url = htmlspecialchars($p['image_url']);
                        $isVid = (strpos($url, '.mp4') !== false || strpos($url, '.webm') !== false);
                    ?>
                        <a href="<?= $url ?>" class="gallery-item group relative aspect-square overflow-hidden rounded-[2rem] border border-white/5 bg-zinc-900 transition-all duration-500" data-index="<?= $index ?>" data-type="<?= $isVid ? 'video' : 'image' ?>">
                            <?php if ($isVid): ?>
                                <video src="<?= $url ?>#t=0.1" class="w-full h-full object-cover opacity-60" muted playsinline></video>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <svg class="w-8 h-8 text-white/50" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                </div>
                            <?php else: ?>
                                <img src="<?= $url ?>" class="w-full h-full object-cover transition duration-700 grayscale group-hover:grayscale-0 group-hover:scale-110" loading="lazy">
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-full py-20 text-center border border-dashed border-white/10 rounded-[3rem]">
                        <p class="text-zinc-600 text-xs uppercase tracking-widest">Dit album is nog leeg</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>

    <div id="forcekes-modal" class="fixed inset-0 z-[9999] bg-black hidden flex-col items-center justify-center opacity-0 transition-all duration-300">
        
        <div class="absolute top-0 left-0 right-0 p-6 flex justify-between items-center z-[10001]">
            <button id="modal-close" class="flex items-center space-x-3 bg-white/5 hover:bg-white/10 backdrop-blur-xl border border-white/10 px-6 py-3 rounded-full transition-all group">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-zinc-400 group-hover:text-white transition">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-400 group-hover:text-white">Sluiten</span>
            </button>
        </div>

        <button id="modal-prev" class="absolute left-6 top-1/2 -translate-y-1/2 z-[10001] p-5 bg-black/20 hover:bg-blue-600 rounded-full border border-white/5 transition-all text-white">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M15 18l-6-6 6-6"/></svg>
        </button>
        <button id="modal-next" class="absolute right-6 top-1/2 -translate-y-1/2 z-[10001] p-5 bg-black/20 hover:bg-blue-600 rounded-full border border-white/5 transition-all text-white">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M9 18l6-6-6-6"/></svg>
        </button>

        <div id="modal-content" class="w-full h-full flex items-center justify-center p-4 md:p-24 pointer-events-none">
            <img id="modal-img" class="max-w-full max-h-full object-contain hidden pointer-events-auto">
            <video id="modal-video" class="max-w-full max-h-full hidden pointer-events-auto" controls autoplay loop playsinline></video>
        </div>

        <div class="absolute bottom-10 left-0 right-0 text-center">
            <p class="text-[9px] font-black uppercase tracking-[0.4em] text-zinc-600">
                <?= $displayName ?> &middot; <span id="modal-counter" class="text-zinc-400">0 / 0</span>
            </p>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('forcekes-modal');
        const modalImg = document.getElementById('modal-img');
        const modalVideo = document.getElementById('modal-video');
        const modalCounter = document.getElementById('modal-counter');
        const galleryItems = document.querySelectorAll('.gallery-item');
        let currentIndex = 0;

        function openModal(index) {
            const item = galleryItems[index];
            if (!item) return;

            currentIndex = index;
            const url = item.href;
            const type = item.getAttribute('data-type');

            modalImg.classList.add('hidden');
            modalVideo.classList.add('hidden');
            modalVideo.pause();

            if (type === 'video') {
                modalVideo.src = url;
                modalVideo.classList.remove('hidden');
            } else {
                modalImg.src = url;
                modalImg.classList.remove('hidden');
            }

            modalCounter.innerText = `${currentIndex + 1} / ${galleryItems.length}`;
            modal.classList.remove('hidden');
            setTimeout(() => modal.classList.add('active'), 10);
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            modal.classList.remove('active');
            setTimeout(() => {
                modal.classList.add('hidden');
                modalVideo.pause();
                modalVideo.src = "";
            }, 300);
            document.body.style.overflow = '';
        }

        function navigate(dir) {
            currentIndex = (currentIndex + dir + galleryItems.length) % galleryItems.length;
            openModal(currentIndex);
        }

        galleryItems.forEach((item, i) => {
            item.onclick = (e) => { e.preventDefault(); openModal(i); };
        });

        document.getElementById('modal-close').onclick = closeModal;
        document.getElementById('modal-prev').onclick = (e) => { e.stopPropagation(); navigate(-1); };
        document.getElementById('modal-next').onclick = (e) => { e.stopPropagation(); navigate(1); };
        
        document.addEventListener('keydown', (e) => {
            if (modal.classList.contains('hidden')) return;
            if (e.key === 'Escape') closeModal();
            if (e.key === 'ArrowLeft') navigate(-1);
            if (e.key === 'ArrowRight') navigate(1);
        });

        modal.onclick = (e) => { if (e.target === modal || e.target.id === 'modal-content') closeModal(); };
    });
    </script>
</body>
</html>