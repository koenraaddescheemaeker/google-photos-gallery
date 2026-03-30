<?php
/** * FORCEKES - gallery.php (Fase 8: Cinema Lightbox Edition) */
require_once 'config.php';
$page = $_GET['page'] ?? '';
if (empty($page)) { header("Location: index.php"); exit; }
$mediaItems = supabaseRequest("album_photos?category=eq." . rawurlencode($page) . "&order=captured_at.desc", 'GET');
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title><?= strtoupper($page) ?> | Forcekes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700;900&family=Playfair+Display:ital,wght@1,700;1,900&display=swap');
        body { background: #000; color: #fff; font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        .serif-italic { font-family: 'Playfair Display', serif; font-style: italic; }
        .media-item { transition: all 1s cubic-bezier(0.2, 1, 0.3, 1); cursor: zoom-in; }
        .media-item:hover { transform: scale(1.02); z-index: 10; }
        img, video { filter: brightness(0.8); transition: filter 0.8s; }
        .media-item:hover img { filter: brightness(1.1); }
        
        /* Lightbox Styles */
        #lightbox { display: none; }
        #lightbox.active { display: flex; animation: fadeIn 0.5s ease both; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        .glass-modal { background: rgba(0, 0, 0, 0.9); backdrop-filter: blur(20px); }
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
            <?php if (is_array($mediaItems)): foreach ($mediaItems as $item): 
                $url = $item['image_url'];
                $isVid = (strpos($url, '.mp4') !== false); ?>
                <div class="media-item relative overflow-hidden rounded-[2rem] bg-zinc-900 border border-white/5 break-inside-avoid" 
                     onclick="openLightbox('<?= $url ?>', <?= $isVid ? 'true' : 'false' ?>)">
                    <?php if ($isVid): ?>
                        <video src="<?= $url ?>" class="w-full object-cover" muted loop playsinline></video>
                        <div class="absolute top-6 right-6 bg-black/50 p-2 rounded-full"><svg width="12" height="12" viewBox="0 0 24 24" fill="white"><path d="M8 5v14l11-7z"/></svg></div>
                    <?php else: ?>
                        <img src="<?= $url ?>" class="w-full object-cover" loading="lazy">
                    <?php endif; ?>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </main>

    <div id="lightbox" class="fixed inset-0 z-[200] items-center justify-center p-6 md:p-20 glass-modal" onclick="closeLightbox()">
        <button class="absolute top-10 right-10 text-white/50 hover:text-white transition-all z-[210]">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
        <div id="lightbox-content" class="relative max-w-full max-h-full flex items-center justify-center" onclick="event.stopPropagation()">
            </div>
    </div>

    <script>
        function openLightbox(url, isVid) {
            const lightbox = document.getElementById('lightbox');
            const content = document.getElementById('lightbox-content');
            
            if (isVid) {
                content.innerHTML = `<video src="${url}" class="max-w-full max-h-[85vh] rounded-3xl shadow-2xl" controls autoplay loop></video>`;
            } else {
                content.innerHTML = `<img src="${url}" class="max-w-full max-h-[85vh] rounded-3xl shadow-2xl object-contain">`;
            }
            
            lightbox.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            const lightbox = document.getElementById('lightbox');
            lightbox.classList.remove('active');
            document.getElementById('lightbox-content').innerHTML = '';
            document.body.style.overflow = '';
        }

        // Sluit met Escape toets
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeLightbox();
        });
    </script>
</body>
</html>