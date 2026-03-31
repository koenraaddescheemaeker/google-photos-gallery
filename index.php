<?php
/** * FORCEKES - index.php (Fase 11: Volledig Herstel) */
require_once 'config.php';

// Data ophalen via de vernieuwde RPC
$dashboardData = supabaseRequest("rpc/get_album_dashboard", 'GET');
$albumGrid = (is_array($dashboardData) && !isset($dashboardData['error'])) ? $dashboardData : [];

// Sorteren op de nieuwe prioriteit
if (!empty($albumGrid)) {
    usort($albumGrid, function($a, $b) {
        $prioA = $a['priority'] ?? 999;
        $prioB = $b['priority'] ?? 999;
        if ($prioA !== $prioB) return $prioA <=> $prioB;
        return strcmp((string)($a['category_name'] ?? ''), (string)($b['category_name'] ?? ''));
    });
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forcekes Portaal | Archief</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700;900&family=Playfair+Display:ital,wght@1,700;1,900&display=swap');
        body { background: #000; color: #fff; font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        .serif-italic { font-family: 'Playfair Display', serif; font-style: italic; }
        .grain::before { content: ""; position: fixed; top: -150%; left: -150%; width: 300%; height: 300%; background-image: url("https://grainy-gradients.vercel.app/noise.svg"); opacity: 0.03; pointer-events: none; animation: grain 8s steps(10) infinite; z-index: 200; }
        @keyframes grain { 0%, 100% { transform:translate(0,0) } 10% { transform:translate(-5%,-10%) } 50% { transform:translate(-15%,10%) } }
        .album-card { transition: all 0.8s cubic-bezier(0.2, 1, 0.3, 1); }
        .album-card:hover { transform: translateY(-12px) scale(1.02); }
    </style>
</head>
<body class="grain">
    <?php include 'menu.php'; ?>
    <main class="max-w-7xl mx-auto px-10 pt-48 pb-32">
        <header class="mb-32 text-center">
            <h1 class="text-7xl md:text-[9rem] font-black italic uppercase tracking-tighter leading-none mb-8">Force<span class="text-blue-600">kes</span></h1>
            <p class="serif-italic text-2xl md:text-4xl text-zinc-500 italic">Archief van de <span class="text-white">Toekomst</span></p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-16" id="album-wrapper">
            <?php 
            $i = 0;
            foreach ($albumGrid as $album): 
                if (($album['is_visible'] ?? true) == false) continue;
                $i++;
                $slug = $album['category_name'] ?? 'Naamloos';
                $displayUrl = !empty($album['thumbnail_url']) ? $album['thumbnail_url'] : ($album['cover_url'] ?? '');
            ?>
                <div class="album-card group" data-name="<?= strtolower($slug) ?>">
                    <a href="gallery.php?page=<?= rawurlencode($slug) ?>" class="relative block aspect-[3/4] overflow-hidden rounded-[3.5rem] bg-zinc-900 border border-white/5">
                        <img src="<?= htmlspecialchars($displayUrl) ?>" class="absolute inset-0 w-full h-full object-cover opacity-60 group-hover:opacity-100 transition-all duration-1000 group-hover:scale-110" loading="lazy">
                        <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent opacity-90"></div>
                        <div class="absolute inset-0 p-12 flex flex-col justify-end">
                            <p class="text-[9px] font-black uppercase text-blue-500 tracking-[0.4em] mb-4 opacity-0 group-hover:opacity-100 transition-all"><?= (int)$album['photo_count'] ?> Bestanden</p>
                            <h2 class="serif-italic text-4xl text-white"><?= ucfirst($slug) ?></h2>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>