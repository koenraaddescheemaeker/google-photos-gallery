<?php
/** * FORCEKES - gallery.php (Full Gallery + Lightbox) */
require_once 'config.php';

// Check voor sessie
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?view=login");
    exit;
}

$pageSlug = $_GET['page'] ?? 'museum';

// Haal de pagina-configuratie op voor de titel
$pageConfig = supabaseRequest("page_configs?page_slug=eq.$pageSlug&select=*", 'GET');
$displayName = is_array($pageConfig) && isset($pageConfig[0]['display_name']) ? $pageConfig[0]['display_name'] : ucfirst($pageSlug);

// Haal de gesyncte foto's op uit Supabase
$photos = supabaseRequest("album_photos?category=eq.$pageSlug&select=*&order=created_at.desc", 'GET');
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forcekes | <?= htmlspecialchars($displayName) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/simplelightbox/2.14.0/simple-lightbox.min.css" integrity="sha512-4Cg4o527195f4eE60c7s/7i5vD793aX5W3t9d1S5G3vV9yK8mC530514m/n4V8wLqjK7q19mC24q2wF1qJ" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #000; color: #fff; }
        .sl-wrapper .sl-navigation button.sl-next, .sl-wrapper .sl-navigation button.sl-prev, .sl-wrapper .sl-close {
            color: #2563eb !important; /* Forcekes Blauw */
        }
        .sl-overlay { background: rgba(0, 0, 0, 0.9); }
    </style>
</head>
<body class="bg-black text-white">
    
    <?php include 'menu.php'; ?>
    
    <main class="max-w-7xl mx-auto px-6 py-12 pt-24 md:pt-28">
        <header class="mb-16">
            <h2 class="text-5xl font-black italic uppercase tracking-tighter"><?= htmlspecialchars($displayName) ?></h2>
            <div class="h-1 w-20 bg-blue-600 mt-4"></div>
        </header>
        
        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 gallery">
            <?php if (is_array($photos) && !empty($photos)): ?>
                <?php foreach ($photos as $p): ?>
                    <a href="<?= htmlspecialchars($p['image_url']) ?>" class="group">
                        <div class="aspect-square rounded-[2rem] overflow-hidden border border-white/5 bg-zinc-900 group shadow-xl">
                            <img src="<?= htmlspecialchars($p['thumbnail_url']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-700">
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-zinc-500 italic col-span-2 md:col-span-3 lg:col-span-4 py-12 text-center text-sm">Nog geen foto's gesynchroniseerd voor deze pagina.</p>
            <?php endif; ?>
        </div>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/simplelightbox/2.14.0/simple-lightbox.vanilla.min.js" integrity="sha512-6WzR4NnU8E3L0A3Q0JtHlC5N2fDqLw4D785s8A2qJ8U3C/8f2K1oFq3qQ0sJ4FqJ3t4A4vC0yR2U0C8v1w3s9w==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        // Initialiseer SimpleLightbox voor de .gallery container
        new SimpleLightbox('.gallery a', {
            loop: true,
            nav: true,
            close: true,
            captions: false, // We tonen geen bijschriften coherence
            alertErrorMessage: 'Afbeelding kan niet geladen worden.',
            fadeSpeed: 150
        });
    </script>
</body>
</html>