<?php
/** * FORCEKES - index.php (Random Album Grid) */
require_once 'config.php';

// 1. Haal alle foto's op om ze in PHP te groeperen (meest flexibele methode voor random)
$allMedia = supabaseRequest("album_photos?select=category,image_url", 'GET');

$albumGrid = [];
if (is_array($allMedia)) {
    // Groepeer URL's per categorie
    $tempGrouped = [];
    foreach ($allMedia as $item) {
        $cat = $item['category'];
        $tempGrouped[$cat][] = $item['image_url'];
    }

    // Kies van elke categorie 1 willekeurige foto
    foreach ($tempGrouped as $cat => $images) {
        if ($cat === 'zwaaikamer') continue; // Sla zwaaikamer over in de grid
        
        $albumGrid[] = [
            'slug' => $cat,
            'name' => ($cat === 'museum') ? 'Het Museum' : ucfirst($cat),
            'random_img' => $images[array_rand($images)]
        ];
    }
    
    // Sorteer de albums alfabetisch (Museum altijd bovenaan is ook een optie)
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
        body { background-color: #000; color: #fff; font-family: 'Inter', sans-serif; }
        .album-card:hover .album-overlay { background: rgba(0, 0, 0, 0.2); }
        .album-card:hover img { transform: scale(1.05); }
    </style>
</head>
<body class="bg-black">
    <?php include 'menu.php'; ?>

    <main class="max-w-7xl mx-auto px-6 py-24 md:py-32">
        <header class="mb-16 md:mb-24">
            <h1 class="text-5xl md:text-8xl font-black italic uppercase tracking-tighter leading-none">
                Welkom bij<br><span class="text-blue-600 text-4xl md:text-7xl">Forcekes Portaal</span>
            </h1>
            <div class="h-2 w-24 bg-blue-600 mt-8 rounded-full"></div>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 md:gap-12">
            <?php if (!empty($albumGrid)): ?>
                <?php foreach ($albumGrid as $album): ?>
                    <a href="gallery.php?page=<?= rawurlencode($album['slug']) ?>" class="album-card group relative block aspect-[4/5] overflow-hidden rounded-[3rem] border border-white/5 bg-zinc-900 transition-all duration-700">
                        
                        <img src="<?= htmlspecialchars($album['random_img']) ?>" 
                             class="absolute inset-0 w-full h-full object-cover transition duration-1000 ease-out grayscale group-hover:grayscale-0"
                             loading="lazy">
                        
                        <div class="album-overlay absolute inset-0 bg-gradient-to-t from-black via-black/20 to-transparent transition-all duration-500"></div>

                        <div class="absolute bottom-10 left-10 right-10">
                            <p class="text-[10px] font-black uppercase tracking-[0.3em] text-blue-500 mb-2">Album</p>
                            <h2 class="text-3xl font-black italic uppercase tracking-tighter text-white"><?= $album['name'] ?></h2>
                            <div class="w-0 group-hover:w-12 h-1 bg-white mt-4 rounded-full transition-all duration-500"></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full py-20 text-center border border-dashed border-white/10 rounded-[3rem]">
                    <p class="text-zinc-600 italic">Nog geen foto's gevonden in het systeem...</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="max-w-7xl mx-auto px-6 py-12 border-t border-white/5 text-center">
        <p class="text-[10px] font-black uppercase tracking-widest text-zinc-800">Forcekes Portaal &copy; 2026</p>
    </footer>
</body>
</html>