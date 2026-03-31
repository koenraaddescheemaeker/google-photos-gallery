<?php
/** * FORCEKES - index.php (Fase 11: Safety & Content Fix) */
require_once 'config.php';

// Foutrapportage voor debugging (zet dit uit in productie)
ini_set('display_errors', 1);
error_reporting(E_ALL);

$dashboardData = supabaseRequest("rpc/get_album_dashboard", 'GET');

// Veiligheid: Als de RPC faalt of geen array is, maken we er een lege lijst van
$albumGrid = (is_array($dashboardData) && !isset($dashboardData['error'])) ? $dashboardData : [];

// Sorteer-logica met vangnet
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Forcekes Portaal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;900&family=Playfair+Display:ital,wght@1,900&display=swap');
        body { background: #000; color: #fff; font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; overflow-x: hidden; }
        .serif-italic { font-family: 'Playfair Display', serif; font-style: italic; }
        .grain::before { content: ""; position: fixed; top: -150%; left: -150%; width: 300%; height: 300%; background-image: url("https://grainy-gradients.vercel.app/noise.svg"); opacity: 0.03; pointer-events: none; animation: grain 8s steps(10) infinite; z-index: 200; }
        @keyframes grain { 0%, 100% { transform:translate(0, 0) } 10% { transform:translate(-5%, -10%) } }
        .stagger { animation: fadeUp 1.2s cubic-bezier(0.2, 1, 0.3, 1) both; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="grain">
    <?php include 'menu.php'; ?>
    
    <main class="max-w-7xl mx-auto px-6 md:px-10 pt-32 md:pt-48 pb-32">
        <header class="mb-20 md:mb-32 text-center stagger" style="animation-delay: 0.1s;">
            <h1 class="text-5xl md:text-[8rem] font-black italic uppercase tracking-tighter leading-none mb-6">Force<span class="text-blue-600">kes</span></h1>
            <p class="serif-italic text-lg md:text-3xl text-zinc-500 italic">Archief van de <span class="text-white">Toekomst</span></p>
        </header>

        <?php if (empty($albumGrid)): ?>
            <div class="text-center py-20 border border-white/5 rounded-[3rem] bg-white/5">
                <p class="serif-italic text-xl text-zinc-600">Er zijn nog geen albums zichtbaar in het portaal.</p>
                <?php if (isset($dashboardData['error'])): ?>
                    <p class="text-[8px] uppercase tracking-widest text-red-900 mt-4">Systeem-notitie: <?= $dashboardData['error'] ?></p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 md:gap-12" id="album-wrapper">
                <?php $i=0; foreach ($albumGrid as $album): 
                    if (($album['is_visible'] ?? true) == false) continue;
                    $i++;
                    $displayUrl = !empty($album['thumbnail_url']) ? $album['thumbnail_url'] : ($album['cover_url'] ?? '');
                ?>
                    <div class="album-card group stagger" style="animation-delay: <?= 0.3 + ($i * 0.05) ?>s;" data-name="<?= strtolower($album['category_name'] ?? '') ?>">
                        <a href="gallery.php?page=<?= rawurlencode($album['category_name'] ?? '') ?>" class="relative block aspect-[4/5] overflow-hidden rounded-[2.5rem] md:rounded-[3.5rem] bg-zinc-900 border border-white/5 shadow-2xl">
                            <img src="<?= htmlspecialchars($displayUrl) ?>" class="absolute inset-0 w-full h-full object-cover opacity-60 group-hover:opacity-100 transition-all duration-1000 group-hover:scale-110" loading="lazy">
                            <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent opacity-90"></div>
                            <div class="absolute inset-0 p-8 md:p-12 flex flex-col justify-end">
                                <h2 class="serif-italic text-3xl md:text-4xl text-white italic"><?= ucfirst($album['category_name'] ?? 'Naamloos') ?></h2>
                                <p class="text-[8px] font-black uppercase text-blue-500 tracking-[0.3em] mt-3"><?= (int)($album['photo_count'] ?? 0) ?> Bestanden</p>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <script>
        const s = document.getElementById('search-input');
        if(s) {
            s.addEventListener('input', e => {
                const t = e.target.value.toLowerCase().trim();
                document.querySelectorAll('.album-card').forEach(card => { 
                    const name = card.getAttribute('data-name');
                    card.style.display = (name && name.includes(t)) ? 'block' : 'none';
                });
            });
        }
    </script>
</body>
</html>