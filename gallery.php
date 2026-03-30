<?php
/** * FORCEKES - gallery.php (Fase 9: Ambient & Sound Edition) */
require_once 'config.php';

$page = $_GET['page'] ?? '';
if (empty($page)) { header("Location: index.php"); exit; }

$mediaItemsRaw = supabaseRequest("album_photos?category=eq." . rawurlencode($page) . "&order=captured_at.desc", 'GET');
$mediaItems = (is_array($mediaItemsRaw) && !isset($mediaItemsRaw['error'])) ? $mediaItemsRaw : [];
$title = ($page === 'museum') ? 'HET MUSEUM' : strtoupper($page);

// Data voor JS Navigatie
$jsMedia = [];
foreach ($mediaItems as $item) {
    $url = $item['image_url'];
    $isVid = (strpos($url, '.mp4') !== false || strpos($url, '.mov') !== false);
    $jsMedia[] = ['url' => $url, 'isVid' => $isVid];
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title><?= $title ?> | Forcekes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;900&family=Playfair+Display:ital,wght@1,900&display=swap');
        body { background: #000; color: #fff; font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        .serif-italic { font-family: 'Playfair Display', serif; font-style: italic; }
        .media-item { transition: all 1s cubic-bezier(0.2, 1, 0.3, 1); cursor: zoom-in; }
        .media-item:hover { transform: scale(1.02); }
        img, video { filter: brightness(0.7); transition: filter 0.8s; }
        .media-item:hover img { filter: brightness(1.1); }
        .glass-modal { background: rgba(0, 0, 0, 0.95); backdrop-filter: blur(25px); }
        .nav-btn { background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); transition: all 0.3s ease; }
        .nav-btn:hover { background: rgba(255, 255, 255, 0.1); border-color: #3b82f6; }
    </style>
</head>
<body class="overflow-x-hidden">
    <?php include 'menu.php'; ?>

    <main class="max-w-[1600px] mx-auto px-10 pt-48 pb-32">
        <header class="mb-24 flex flex-col md:flex-row justify-between items-end gap-10">
            <div class="max-w-2xl">
                <p class="text-[9px] font-black uppercase tracking-[0.5em] text-blue-600 mb-6 italic">Collectie Archief</p>
                <h1 class="serif-italic text-6xl md:text-9xl leading-none italic"><?= ucfirst($page) ?></h1>
            </div>
            <div class="text-right">
                <p class="text-5xl font-black italic tracking-tighter text-white/10"><?= count($mediaItems) ?></p>
                <p class="text-[8px] font-black uppercase tracking-widest text-zinc-500">Geregistreerde Momenten</p>
            </div>
        </header>

        <div class="columns-1 sm:columns-2 lg:columns-3 xl:columns-4 gap-8 space-y-8">
            <?php foreach ($mediaItems as $index => $item): 
                $url = $item['image_url'];
                $isVid = (strpos($url, '.mp4') !== false); ?>
                <div class="media-item relative overflow-hidden rounded-[2.5rem] bg-zinc-900 border border-white/5 break-inside-avoid" 
                     onmouseenter="updateAmbientGlow('rgba(255, 255, 255, 0.03)')"
                     onmouseleave="updateAmbientGlow()"
                     onclick="openLightbox(<?= $index ?>); playSound('ui-open');">
                    <?php if ($isVid): ?>
                        <video src="<?= $url ?>" class="w-full object-cover" muted loop playsinline></video>
                    <?php else: ?>
                        <img src="<?= $url ?>" class="w-full object-cover" loading="lazy">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <div id="lightbox" class="fixed inset-0 z-[200] items-center justify-center glass-modal" style="display:none;" onclick="closeLightbox(); playSound('ui-close');">
        <button id="prev-btn" class="nav-btn absolute left-10 p-5 rounded-full z-[210] hidden md:block" onclick="event.stopPropagation(); changeMedia(-1); playSound('click');">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M15 18l-6-6 6-6"/></svg>
        </button>
        <div id="lightbox-content" class="relative max-w-[90vw] max-h-[85vh] flex items-center justify-center" onclick="event.stopPropagation()"></div>
        <button id="next-btn" class="nav-btn absolute right-10 p-5 rounded-full z-[210] hidden md:block" onclick="event.stopPropagation(); changeMedia(1); playSound('click');">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M9 18l6-6-6-6"/></svg>
        </button>
    </div>

    <script>
        const mediaData = <?php echo json_encode($jsMedia); ?>;
        let currentIndex = 0;

        function openLightbox(index) {
            currentIndex = index;
            updateLightbox();
            document.getElementById('lightbox').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            document.getElementById('lightbox').style.display = 'none';
            document.body.style.overflow = '';
        }

        function changeMedia(dir) {
            currentIndex = (currentIndex + dir + mediaData.length) % mediaData.length;
            updateLightbox();
        }

        function updateLightbox() {
            const content = document.getElementById('lightbox-content');
            const media = mediaData[currentIndex];
            content.innerHTML = media.isVid 
                ? `<video src="${media.url}" class="max-w-full max-h-[85vh] rounded-3xl" controls autoplay loop></video>`
                : `<img src="${media.url}" class="max-w-full max-h-[85vh] rounded-3xl shadow-2xl">`;
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight') changeMedia(1);
            if (e.key === 'ArrowLeft') changeMedia(-1);
            if (e.key === 'Escape') closeLightbox();
        });
    </script>
</body>
</html>