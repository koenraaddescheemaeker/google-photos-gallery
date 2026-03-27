<?php
/** FORCEKES - gallery.php (Lightbox Edition) */
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?view=login");
    exit;
}

$pageSlug = $_GET['page'] ?? 'museum';
$photos = supabaseRequest("album_photos?category=eq.$pageSlug&select=*&order=created_at.desc", 'GET');
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Forcekes | <?= ucfirst($pageSlug) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/simplelightbox/2.14.0/simple-lightbox.min.css" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #000; color: #fff; }
        .sl-wrapper .sl-navigation button { color: #3b82f6 !important; }
        .sl-overlay { background: rgba(0, 0, 0, 0.95); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="bg-black text-white min-h-screen">
    <?php include 'menu.php'; ?>
    
    <main class="max-w-7xl mx-auto px-6 py-20 pt-32">
        <header class="mb-12">
            <h1 class="text-6xl font-black italic uppercase tracking-tighter"><?= $pageSlug ?></h1>
            <div class="h-1.5 w-24 bg-blue-600 mt-4 rounded-full"></div>
        </header>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 gallery-grid">
            <?php if (is_array($photos)) foreach ($photos as $p): ?>
                <a href="<?= htmlspecialchars($p['image_url']) ?>" class="group">
                    <div class="aspect-square rounded-[2.5rem] overflow-hidden border border-white/5 bg-zinc-900 shadow-2xl relative">
                        <img src="<?= htmlspecialchars($p['thumbnail_url']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-1000">
                        <div class="absolute inset-0 bg-blue-600/20 opacity-0 group-hover:opacity-100 transition duration-500"></div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/simplelightbox/2.14.0/simple-lightbox.vanilla.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new SimpleLightbox('.gallery-grid a', { 
                loop: true, 
                nav: true, 
                close: true, 
                fadeSpeed: 200,
                swipeClose: true
            });
        });
    </script>
</body>
</html>