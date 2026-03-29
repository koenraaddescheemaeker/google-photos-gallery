<?php
/** * FORCEKES - index.php (Clean Single Grid) */
require_once 'config.php';

// Haal data op via de RPC
$dashboardData = supabaseRequest("rpc/get_album_dashboard", 'GET');

// Strenge controle op de data
$albumGrid = [];
if (is_array($dashboardData) && !isset($dashboardData['error'])) {
    $albumGrid = $dashboardData;
}

// Sorteren
if (!empty($albumGrid)) {
    usort($albumGrid, function($a, $b) {
        return strcmp((string)($a['category_name'] ?? ''), (string)($b['category_name'] ?? ''));
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
        img { transition: filter 0.8s, transform 0.8s; filter: grayscale(100%) opacity(0.5); }
        .group:hover img { filter: grayscale(0%) opacity(1); transform: scale(1.05); }
    </style>
</head>
<body class="bg-black">
    <?php include 'menu.php'; ?>

    <main class="max-w-7xl mx-auto px-6 pt-32 pb-20">
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 md:gap-6">
            
            <a href="zwaaikamer.php" class="zwaai-card group relative block aspect-square overflow-hidden rounded-[2rem] transition-all duration-500">
                <div class="absolute inset-0 flex flex-col items-center justify-center p-4 text-center">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" class="mb-3">
                        <path d="M23 7l-7 5 7 5V7z"></path><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
                    </svg>
                    <h2 class="text-[10px] font-black uppercase tracking-widest text-white leading-tight">Zwaai<br>Kamer</h2>
                    <div class="mt-3 w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></div>
                </div>
            </a>

            <?php if (!empty($albumGrid)): ?>
                <?php foreach ($albumGrid as $album): 
                    $slug = (string)($album['category_name'] ?? '');
                    if (empty($slug)) continue;
                    $name = ($slug === 'museum') ? 'HET MUSEUM' : strtoupper($slug);
                ?>
                    <a href="gallery.php?page=<?= rawurlencode($slug) ?>" class="group relative block aspect-square overflow-hidden rounded-[2rem] border border-white/5 bg-zinc-900 transition-all duration-700">
                        <img src="<?= htmlspecialchars($album['cover_url'] ?? '') ?>" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/95 via-black/20 to-transparent"></div>
                        <div class="absolute bottom-5 left-5 right-5 text-center">
                            <h2 class="text-[9px] font-black uppercase tracking-widest text-white leading-tight truncate"><?= $name ?></h2>
                            <span class="text-[8px] font-bold text-blue-500 uppercase tracking-widest mt-1 block"><?= (int)($album['photo_count'] ?? 0) ?> FOTO'S</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
    </main>

    <footer class="py-12 text-center">
        <p class="text-[8px] font-black uppercase tracking-[0.3em] text-zinc-800">Forcekes Portaal &middot; v2.7.1</p>
    </footer>
</body>
</html>