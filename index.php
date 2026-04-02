<?php
/** * FORCEKES - index.php (Fase 20: Mobile 16:9 Edition) */
require_once 'config.php';
$data = supabaseRequest("rpc/get_album_dashboard", 'GET');
$albums = (is_array($data) && !isset($data['error'])) ? $data : [];
if (!empty($albums)) usort($albums, fn($a, $b) => ($a['priority'] ?? 999) <=> ($b['priority'] ?? 999));
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Forcekes Portaal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=Playfair+Display:ital,wght@1,700&display=swap');
        body { background: #000; color: #fff; font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        .serif-italic { font-family: 'Playfair Display', serif; font-style: italic; }
    </style>
</head>
<body class="bg-black">
    <?php include 'menu.php'; ?>
    <main class="max-w-7xl mx-auto px-6 pt-48 pb-32">
        <header class="mb-32 text-center px-4">
            <h1 class="serif-italic text-2xl md:text-4xl text-zinc-500 italic leading-snug">
                <span class="text-white font-black not-italic tracking-tighter">Force<span class="text-blue-600">kes</span> Portaal</span>: 
                Onze momenten, vlijmscherp bewaard.
            </h1>
        </header>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8 sm:gap-12">
            <?php foreach ($albums as $album): 
                if (($album['is_visible'] ?? true) == false) continue;
                $img = !empty($album['thumbnail_url']) ? $album['thumbnail_url'] : ($album['cover_url'] ?? '');
            ?>
                <a href="gallery.php?page=<?= rawurlencode($album['category_name']) ?>" class="group relative aspect-video sm:aspect-square overflow-hidden rounded-[2rem] sm:rounded-[3rem] bg-zinc-900 border border-white/5 shadow-2xl transition-all duration-700 hover:scale-[1.02]">
                    <img src="<?= htmlspecialchars($img) ?>" class="absolute inset-0 w-full h-full object-cover opacity-60 group-hover:opacity-100 transition-all duration-1000" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent"></div>
                    <div class="absolute inset-0 p-8 flex flex-col justify-end">
                        <p class="text-[9px] font-black uppercase text-blue-500 tracking-[0.4em] mb-2 opacity-0 group-hover:opacity-100 transition-all"><?= (int)$album['photo_count'] ?> Bestanden</p>
                        <h2 class="serif-italic text-2xl text-white"><?= ucfirst($album['category_name']) ?></h2>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>