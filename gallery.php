<?php
/** * FORCEKES - gallery.php (Phase 7: Full Sync) */
require_once 'config.php';

$page = $_GET['page'] ?? '';
if (empty($page)) { header("Location: index.php"); exit; }

// Haal alle media op voor deze categorie
$mediaItems = supabaseRequest("album_photos?category=eq." . rawurlencode($page) . "&order=captured_at.desc", 'GET');
$title = ($page === 'museum') ? 'HET MUSEUM' : strtoupper($page);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> | Forcekes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { background-color: #000; color: #fff; font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        img, video { transition: transform 0.5s ease; content-visibility: auto; }
        .media-container:hover img, .media-container:hover video { transform: scale(1.03); }
        .glass-nav { background: rgba(0,0,0,0.7); backdrop-filter: blur(15px); }
    </style>
</head>
<body class="bg-black">
    <?php include 'menu.php'; ?>

    <main class="max-w-7xl mx-auto px-6 pt-32 pb-20">
        <header class="mb-12 text-center">
            <h1 class="text-4xl md:text-6xl font-black italic uppercase tracking-tighter mb-2"><?= $title ?></h1>
            <p class="text-[10px] font-black uppercase tracking-[0.4em] text-blue-500"><?= count($mediaItems) ?> HERINNERINGEN</p>
        </header>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php 
            $i = 0;
            if (is_array($mediaItems)): foreach ($mediaItems as $item): 
                $i++;
                $isPriority = ($i <= 8);
                $isVid = (strpos($item['image_url'], '.mp4') !== false || ($item['mime_type'] ?? '') === 'video/mp4');
            ?>
                <div class="media-container relative aspect-[3/4] overflow-hidden rounded-[1.5rem] bg-zinc-900 border border-white/5">
                    <?php if ($isVid): ?>
                        <video src="<?= $item['image_url'] ?>" class="w-full h-full object-cover" muted playsinline loop onmouseover="this.play()" onmouseout="this.pause()"></video>
                        <div class="absolute top-4 right-4 bg-black/50 p-2 rounded-full backdrop-blur-md">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="white"><path d="M8 5v14l11-7z"/></svg>
                        </div>
                    <?php else: ?>
                        <img 
                            src="<?= $item['image_url'] ?>" 
                            class="w-full h-full object-cover" 
                            loading="<?= $isPriority ? 'eager' : 'lazy' ?>"
                            fetchpriority="<?= $isPriority ? 'high' : 'low' ?>"
                            decoding="async"
                        >
                    <?php endif; ?>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </main>

    <footer class="py-20 text-center">
        <a href="index.php" class="text-[10px] font-black uppercase tracking-[0.4em] text-zinc-500 hover:text-white transition">Terug naar overzicht</a>
    </footer>
</body>
</html>