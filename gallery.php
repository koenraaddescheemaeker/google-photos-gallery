<?php
/** * FORCEKES - gallery.php (Fase 8: Cinema Navigation Edition) */
require_once 'config.php';

$page = $_GET['page'] ?? '';
if (empty($page)) { header("Location: index.php"); exit; }

// Haal alle media op voor deze categorie
$mediaItemsRaw = supabaseRequest("album_photos?category=eq." . rawurlencode($page) . "&order=captured_at.desc", 'GET');
$mediaItems = (is_array($mediaItemsRaw) && !isset($mediaItemsRaw['error'])) ? $mediaItemsRaw : [];
$title = ($page === 'museum') ? 'HET MUSEUM' : strtoupper($page);

// Bereid data voor voor JavaScript navigatie
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> | Forcekes Portaal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700;900&family=Playfair+Display:ital,wght@1,700;1,900&display=swap');
        
        body { background: #000; color: #fff; font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; overflow-x: hidden; }
        .serif-italic { font-family: 'Playfair Display', serif; font-style: italic; }
        
        /* Media Item Styling */
        .media-item { transition: all 1s cubic-bezier(0.2, 1, 0.3, 1); cursor: zoom-in; }
        .media-item:hover { transform: scale(1.02); z-index: 10; }
        img, video { filter: brightness(0.8); transition: filter 0.8s, transform 0.8s; content-visibility: auto; }
        .media-item:hover img { filter: brightness(1.1); }
        
        /* Lightbox Styles */
        #lightbox { display: none; opacity: 0; transition: opacity 0.5s ease; }
        #lightbox.active { display: flex; opacity: 1; }
        .glass-modal { background: rgba(0, 0, 0, 0.95); backdrop-filter: blur(25px); }
        
        .nav-btn {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        .nav-btn:hover { background: rgba(255, 255, 255, 0.1); border-color: #3b82f6; color: #3b82f6; transform: scale(1.1); }

        /* Animatie voor content wissel */
        .fade-content { animation: fadeContent 0.4s ease; }
        @keyframes fadeContent { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
    </style>
</head>
<body class="bg-black">
    <?php include 'menu.php'; ?>

    <main class="max-w-[1600px] mx-auto px-10 pt-48 pb-32">
        <header class="mb-24 flex flex-col md:flex-row justify-between items-end gap-10">
            <div class="max-w-2xl">
                <p class="text-[9px] font-black uppercase tracking-[0.5em] text-blue-600 mb-6 italic">Collectie Archief</p>
                <h1 class="serif-italic text-6xl md:text-8xl leading-none"><?= ucfirst($page) ?></h1>
            </div>
            <div class="text-right">
                <p class="text-5xl font-black italic tracking-tighter text-white/10"><?= count($mediaItems) ?></p>
                <p class="text-[8px] font-black uppercase tracking-widest text-zinc-500">Geregistreerde Momenten</p>
            </div>
        </header>

        <div class="columns-1 sm:columns-2 lg:columns-3 xl:columns-4 gap-8 space-y-8">
            <?php foreach ($mediaItems as $index => $item): 
                $url = $item['image_url'];
                $isVid = (strpos($url, '.mp4') !== false || strpos($url, '.mov') !== false);
            ?>
                <div class="media-item relative overflow-hidden rounded-[2.5rem] bg-zinc-900 border border-white/5 break-inside-avoid" 
                     onclick="openLightbox(<?= $index ?>)">
                    <?php if ($isVid): ?>
                        <video src="<?= $url ?>" class="w-full object-cover" muted loop playsinline></video>
                        <div class="absolute top-6 right-6 bg-black/50 p-3 rounded-full backdrop-blur-md">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="white"><path d="M8 5v14l11-7z"/></svg>
                        </div>
                    <?php else: ?>
                        <img src="<?= $url ?>" class="w-full object-cover" loading="lazy" alt="Media item">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <div id="lightbox" class="fixed inset-0 z-[200] items-center justify-center glass-modal" onclick="closeLightbox()">
        
        <button class="absolute top-10 right-10 p-4 text-white/30 hover:text-white transition-all z-[220]" onclick="closeLightbox()">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>

        <button id="prev-btn" class="nav-btn absolute left-6 md:left-10 p-5 rounded-full z-[210] hidden md:block" onclick="event.stopPropagation(); changeMedia(-1)">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M15 18l-6-6 6-6"/></svg>
        </button>

        <div id="lightbox-content" class="relative max-w-[90vw] max-h-[85vh] flex items-center justify-center fade-content" onclick="event.stopPropagation()">
            </div>

        <button id="next-btn" class="nav-btn absolute right-6 md:right-10 p-5 rounded-full z-[210] hidden md:block" onclick="event.stopPropagation(); changeMedia(1)">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M9 18l6-6-6-6"/></svg>
        </button>

        <div class="absolute bottom-10 left-1/2 -translate-x-1/2 flex gap-8 md:hidden z-[210]">
            <button class="nav-btn p-4 rounded-full" onclick="event.stopPropagation(); changeMedia(-1)">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M15 18l-6-6 6-6"/></svg>
            </button>
            <button class="nav-btn p-4 rounded-full" onclick="event.stopPropagation(); changeMedia(1)">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M9 18l6-6-6-6"/></svg>
            </button>
        </div>
    </div>

    <script>
        const mediaData = <?php echo json_encode($jsMedia); ?>;
        let currentIndex = 0;

        function openLightbox(index) {
            currentIndex = index;
            updateLightbox();
            const lightbox = document.getElementById('lightbox');
            lightbox.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            const lightbox = document.getElementById('lightbox');
            lightbox.classList.remove('active');
            document.getElementById('lightbox-content').innerHTML = '';
            document.body.style.overflow = '';
        }

        function changeMedia(direction) {
            currentIndex += direction;
            // Loop de navigatie
            if (currentIndex >= mediaData.length) currentIndex = 0;
            if (currentIndex < 0) currentIndex = mediaData.length - 1;
            
            updateLightbox();
        }

        function updateLightbox() {
            const content = document.getElementById('lightbox-content');
            const media = mediaData[currentIndex];
            
            // Voeg fade effect toe
            content.classList.remove('fade-content');
            void content.offsetWidth; // Trigger reflow
            content.classList.add('fade-content');

            if (media.isVid) {
                content.innerHTML = `<video src="${media.url}" class="max-w-full max-h-[85vh] rounded-3xl shadow-2xl" controls autoplay loop></video>`;
            } else {
                content.innerHTML = `<img src="${media.url}" class="max-w-full max-h-[85vh] rounded-3xl shadow-2xl object-contain">`;
            }
        }

        // Toetsenbord ondersteuning
        document.addEventListener('keydown', (e) => {
            const lightbox = document.getElementById('lightbox');
            if (!lightbox.classList.contains('active')) return;

            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowRight') changeMedia(1);
            if (e.key === 'ArrowLeft') changeMedia(-1);
        });
    </script>
</body>
</html>