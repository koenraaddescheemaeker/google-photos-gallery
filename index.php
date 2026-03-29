<?php
/** * FORCEKES - index.php (Compact Edition with Counts & Random Cover) */
require_once 'config.php';

// 1. Haal unieke categorieën met foto-aantallen op uit de database
// We gebruiken de RPC-functie voor efficiëntie
$categoriesData = supabaseRequest("rpc/get_unique_categories_with_counts", 'GET');
$albumGrid = [];

if (is_array($categoriesData)) {
    foreach ($categoriesData as $album) {
        $cat = (string)($album['category'] ?? '');
        if ($cat === 'zwaaikamer' || empty($cat)) continue; // Sla zwaaikamer en leeg over
        
        // 2. Haal één willekeurige cover foto op uit de database voor deze categorie
        // We zoeken naar een Supabase URL om te tonen in de tegel
        // We sorteren op random() en pakken 1 item
        $coverPhotoRequest = supabaseRequest("album_photos?category=eq." . rawurlencode($cat) . "&order=random()&limit=1&select=image_url", 'GET');
        $randomImg = (is_array($coverPhotoRequest) && !empty($coverPhotoRequest)) ? $coverPhotoRequest[0]['image_url'] : '';

        $albumGrid[] = [
            'slug' => $cat,
            'name' => ($cat === 'museum') ? 'Het Museum' : ucfirst($cat),
            'photo_count' => (int)($album['photo_count'] ?? 0),
            'random_img' => $randomImg
        ];
    }
    
    // Sorteer de albums alfabetisch
    usort($albumGrid, function($a, $b) {
        return strcmp($a['name'], $b['name']);
    });
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forcekes | Portaal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { background-color: #000; color: #fff; font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        .zwaai-card { background: #050505; border: 1px solid rgba(59, 130, 246, 0.2); }
        .zwaai-card:hover { border-color: #3b82f6; box-shadow: 0 0 30px rgba(59, 130, 246, 0.1); }
        img { filter: grayscale(100%) opacity(0.6); transition: filter 1s duration-700; loading: lazy; }
        .group:hover img { filter: grayscale(0%) opacity(1) scale(1.1); }
    </style>
</head>
<body class="bg-black">
    <?php include 'menu.php'; ?>

    <main class="max-w-7xl mx-auto px-6 pt-32 pb-20">
        
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 md:gap-6">
            
            <a href="zwaaikamer.php" class="zwaai-card group relative block aspect-square overflow-hidden rounded-[2rem] transition-all duration-500">
                <div class="absolute inset-0 flex flex-col items-center justify-center p-4 text-center">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" class="mb-3 transition-transform duration-500 group-hover:scale-110">
                        <path d="M23 7l-7 5 7 5V7z"></path>
                        <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
                    </svg>
                    <h2 class="text-xs font-black uppercase tracking-widest text-white leading-tight">Zwaai<br>Kamer</h2>
                    <div class="mt-3 w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></div>
                </div>
            </a>

            <?php foreach ($albumGrid as $album): ?>
                <a href="gallery.php?page=<?= rawurlencode((string)$album['slug']) ?>" class="group relative block aspect-square overflow-hidden rounded-[2rem] border border-white/5 bg-zinc-900 transition-all duration-700">
                    <img src="<?= htmlspecialchars((string)$album['random_img']) ?>" class="absolute inset-0 w-full h-full object-cover grayscale group-hover:grayscale-0" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent transition duration-500"></div>
                    <div class="absolute bottom-5 left-5 right-5 text-center">
                        <h2 class="text-[10px] font-black uppercase tracking-widest text-white leading-tight truncate"><?= $album['name'] ?></h2>
                        <span class="text-[9px] font-bold text-blue-500 uppercase tracking-widest mt-1 block"><?= $album['photo_count'] ?> foto's</span>
                    </div>
                </a>
            <?php endforeach; ?>

        </div>
    </main>

    <footer class="py-12 text-center border-t border-white/5">
        <p class="text-[8px] font-black uppercase tracking-[0.3em] text-zinc-800">Forcekes Portaal &middot; v2.7</p>
    </footer>
</body>
</html>