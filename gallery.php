<?php
/** * FORCEKES - gallery.php (Lightbox & Download Fix) */
require_once 'config.php';
$page = $_GET['page'] ?? '';
if (empty($page)) { header("Location: index.php"); exit; }

$mediaRaw = supabaseRequest("album_photos?category=eq." . rawurlencode($page) . "&order=captured_at.desc", 'GET');
$mediaItems = (is_array($mediaRaw) && !isset($mediaRaw['error'])) ? $mediaRaw : [];
$title = strtoupper($page);

// Data voor JS
$jsMedia = [];
foreach ($mediaItems as $m) {
    $jsMedia[] = ['url' => $m['image_url'], 'isVid' => (strpos($m['image_url'], '.mp4') !== false)];
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title><?= $title ?> | Forcekes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;900&family=Playfair+Display:ital,wght@1,900&display=swap');
        body { background: #000; color: #fff; font-family: 'Inter', sans-serif; }
        .glass-modal { background: rgba(0, 0, 0, 0.96); backdrop-filter: blur(20px); }
        .nav-btn { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); transition: all 0.3s; }
        .nav-btn:hover { background: rgba(255,255,255,0.2); border-color: #3b82f6; }
    </style>
</head>
<body class="bg-black">
    <?php include 'menu.php'; ?>
    <main class="max-w-[1600px] mx-auto px-10 pt-48 pb-32">
        <header class="mb-24">
            <h1 class="serif-italic text-6xl md:text-9xl italic"><?= ucfirst($page) ?></h1>
        </header>

        <div class="columns-1 sm:columns-2 lg:columns-3 xl:columns-4 gap-8 space-y-8">
            <?php foreach ($mediaItems as $idx => $item): 
                $isVid = (strpos($item['image_url'], '.mp4') !== false); ?>
                <div class="relative overflow-hidden rounded-[2.5rem] bg-zinc-900 border border-white/5 break-inside-avoid cursor-zoom-in" onclick="openLightbox(<?= $idx ?>)">
                    <img src="<?= $item['thumbnail_url'] ?: $item['image_url'] ?>" class="w-full opacity-70 hover:opacity-100 transition-opacity duration-700">
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <div id="lightbox" class="fixed inset-0 z-[500] hidden items-center justify-center glass-modal">
        <button onclick="closeLightbox()" class="absolute top-10 right-10 z-[510] p-4 text-white hover:text-blue-500 transition">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
        </button>

        <button onclick="downloadCurrent()" class="absolute bottom-10 right-10 z-[510] nav-btn px-6 py-3 rounded-full text-[10px] font-black uppercase tracking-widest flex items-center gap-3">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
            Download
        </button>

        <button onclick="changeMedia(-1)" class="nav-btn absolute left-10 p-5 rounded-full"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M15 18l-6-6 6-6"/></svg></button>
        <div id="lightbox-content" class="max-w-[90vw] max-h-[85vh]"></div>
        <button onclick="changeMedia(1)" class="nav-btn absolute right-10 p-5 rounded-full"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M9 18l6-6-6-6"/></svg></button>
    </div>

    <script>
        const media = <?= json_encode($jsMedia) ?>;
        let cur = 0;

        function openLightbox(i) {
            cur = i; updateLightbox();
            document.getElementById('lightbox').classList.remove('hidden');
            document.getElementById('lightbox').classList.add('flex');
        }
        function closeLightbox() {
            document.getElementById('lightbox').classList.add('hidden');
        }
        function changeMedia(d) {
            cur = (cur + d + media.length) % media.length;
            updateLightbox();
        }
        function updateLightbox() {
            const container = document.getElementById('lightbox-content');
            const item = media[cur];
            container.innerHTML = item.isVid 
                ? `<video src="${item.url}" class="max-h-[85vh] rounded-3xl" controls autoplay></video>`
                : `<img src="${item.url}" class="max-h-[85vh] rounded-3xl shadow-2xl">`;
        }
        function downloadCurrent() {
            const link = document.createElement('a');
            link.href = media[cur].url;
            link.download = 'forcekes-archief';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</body>
</html>