<?php
/** * FORCEKES - gallery.php (KISS Edition - Gekeurd door Manu) */
require_once 'config.php';
$page = $_GET['page'] ?? '';
if (!$page) { header("Location: index.php"); exit; }

$data = supabaseRequest("album_photos?category=eq." . rawurlencode($page) . "&order=captured_at.desc", 'GET');
$items = (is_array($data) && !isset($data['error'])) ? $data : [];

// Voorbereiden voor JS Lightbox
$jsItems = array_map(fn($m) => ['url' => $m['image_url'], 'isVid' => str_ends_with($m['image_url'], '.mp4')], $items);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title><?= strtoupper($page) ?> | Forcekes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;900&family=Playfair+Display:ital,wght@1,900&display=swap');
        body { background: #000; color: #fff; font-family: 'Inter', sans-serif; }
        .serif-italic { font-family: 'Playfair Display', serif; font-style: italic; }
        .lightbox-bg { background: rgba(0,0,0,0.98); backdrop-filter: blur(20px); }
    </style>
</head>
<body>
    <?php include 'menu.php'; ?>
    <main class="max-w-[1600px] mx-auto px-10 pt-48 pb-32">
        <h1 class="serif-italic text-6xl md:text-9xl mb-24 italic"><?= ucfirst($page) ?></h1>
        <div class="columns-1 sm:columns-2 lg:columns-3 xl:columns-4 gap-8 space-y-8">
            <?php foreach ($items as $idx => $item): ?>
                <div class="overflow-hidden rounded-[2.5rem] bg-zinc-900 border border-white/5 break-inside-avoid cursor-pointer" onclick="openLightbox(<?= $idx ?>)">
                    <img src="<?= $item['thumbnail_url'] ?: $item['image_url'] ?>" class="w-full opacity-80 hover:opacity-100 transition-opacity">
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <div id="lb" class="fixed inset-0 z-[500] hidden items-center justify-center lightbox-bg">
        <button onclick="closeLB()" class="absolute top-10 right-10 text-white hover:text-blue-500"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg></button>
        <button onclick="navLB(-1)" class="absolute left-10 p-5 bg-white/5 rounded-full hover:bg-white/10"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M15 18l-6-6 6-6"/></svg></button>
        <div id="lb-content" class="max-w-[90vw] max-h-[85vh]"></div>
        <button onclick="navLB(1)" class="absolute right-10 p-5 bg-white/5 rounded-full hover:bg-white/10"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M9 18l6-6-6-6"/></svg></button>
        <a id="lb-dl" href="#" download class="absolute bottom-10 right-10 px-6 py-3 bg-blue-600 rounded-full text-[10px] font-black uppercase tracking-widest">Download</a>
    </div>

    <script>
        const items = <?= json_encode($jsItems) ?>;
        let cur = 0;
        function openLightbox(i) { cur = i; renderLB(); document.getElementById('lb').classList.replace('hidden', 'flex'); }
        function closeLB() { document.getElementById('lb').classList.replace('flex', 'hidden'); }
        function navLB(d) { cur = (cur + d + items.length) % items.length; renderLB(); }
        function renderLB() {
            const c = document.getElementById('lb-content');
            const d = document.getElementById('lb-dl');
            const item = items[cur];
            c.innerHTML = item.isVid ? `<video src="${item.url}" class="max-h-[85vh] rounded-3xl" controls autoplay></video>` : `<img src="${item.url}" class="max-h-[85vh] rounded-3xl">`;
            d.href = item.url;
        }
    </script>
</body>
</html>