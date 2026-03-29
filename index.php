<?php
/** * FORCEKES - index.php (Premium Grid met Zwaaikamer) */
require_once 'config.php';

$allMedia = supabaseRequest("album_photos?select=category,image_url", 'GET');
$albumGrid = [];

if (is_array($allMedia)) {
    $tempGrouped = [];
    foreach ($allMedia as $item) {
        $cat = $item['category'];
        if ($cat === 'zwaaikamer') continue; // We behandelen zwaaikamer apart
        $tempGrouped[$cat][] = $item['image_url'];
    }

    foreach ($tempGrouped as $cat => $images) {
        $albumGrid[] = [
            'slug' => $cat,
            'name' => ($cat === 'museum') ? 'Het Museum' : ucfirst($cat),
            'random_img' => $images[array_rand($images)]
        ];
    }
    
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
        .zwaai-card { background: linear-gradient(145deg, #090909 0%, #000 100%); border: 1px solid rgba(59, 130, 246, 0.2); }
        .zwaai-card:hover { border-color: #3b82f6; box-shadow: 0 0 50px rgba(59, 130, 246, 0.15); }
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
            
            <a href="zwaaikamer.php" class="zwaai-card group relative block aspect-[4/5] overflow-hidden rounded-[3rem] transition-all duration-500">
                <div class="absolute inset-0 flex flex-col items-center justify-center p-12 text-center">
                    <div class="relative mb-8">
                        <div class="absolute inset-0 bg-blue-600 blur-3xl opacity-20 group-hover:opacity-40 transition-opacity"></div>
                        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="relative transition-transform duration-500 group-hover:scale-110">
                            <path d="M23 7l-7 5 7 5V7z"></path>
                            <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
                        </svg>
                    </div>
                    <p class="text-[10px] font-black uppercase tracking-[0.4em] text-blue-500 mb-3">Live Verbinding</p>
                    <h2 class="text-4xl font-black italic uppercase tracking-tighter text-white">Zwaai<br>Kamer</h2>
                    <div class="mt-8 flex items-center space-x-2">
                        <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                        <span class="text-[9px] font-black uppercase tracking-widest text-zinc-500">Klaar voor gebruik</span>
                    </div>
                </div>
            </a>

            <?php foreach ($albumGrid as $album): ?>
                <a href="gallery.php?page=<?= rawurlencode($album['slug']) ?>" class="group relative block aspect-[4/5] overflow-hidden rounded-[3rem] border border-white/5 bg-zinc-900 transition-all duration-700">
                    <img src="<?= htmlspecialchars($album['random_img']) ?>" class="absolute inset-0 w-full h-full object-cover transition duration-1000 grayscale group-hover:grayscale-0 group-hover:scale-105" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black via-black/10 to-transparent"></div>
                    <div class="absolute bottom-10 left-10 right-10">
                        <p class="text-[10px] font-black uppercase tracking-[0.3em] text-zinc-500 mb-2">Album</p>
                        <h2 class="text-3xl font-black italic uppercase tracking-tighter text-white"><?= $album['name'] ?></h2>
                    </div>
                </a>
            <?php endforeach; ?>

        </div>
    </main>
</body>
</html>