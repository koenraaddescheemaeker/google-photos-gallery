<?php
/** * FORCEKES - index.php (KISS Edition - Gekeurd door Manu) */
require_once 'config.php';

$data = supabaseRequest("rpc/get_album_dashboard", 'GET');
$albums = (is_array($data) && !isset($data['error'])) ? $data : [];

// Simpele sortering: Prioriteit (laag naar hoog)
if (!empty($albums)) {
    usort($albums, fn($a, $b) => ($a['priority'] ?? 999) <=> ($b['priority'] ?? 999));
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Forcekes Portaal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;900&family=Playfair+Display:ital,wght@1,900&display=swap');
        body { background: #000; color: #fff; font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        .serif-italic { font-family: 'Playfair Display', serif; font-style: italic; }
    </style>
</head>
<body>
    <?php include 'menu.php'; ?>
    <main class="max-w-7xl mx-auto px-10 pt-48 pb-32">
        <header class="mb-32 text-center">
            <h1 class="text-7xl md:text-[9rem] font-black italic uppercase tracking-tighter leading-none mb-8 text-white">Force<span class="text-blue-600">kes</span></h1>
            <p class="serif-italic text-2xl md:text-4xl text-zinc-500 italic">Archief van de <span class="text-white">Toekomst</span></p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-16">
            <?php foreach ($albums as $album): 
                if (($album['is_visible'] ?? true) == false) continue;
                $img = !empty($album['thumbnail_url']) ? $album['thumbnail_url'] : $album['cover_url'];
            ?>
                <a href="gallery.php?page=<?= rawurlencode($album['category_name']) ?>" class="group relative aspect-[3/4] overflow-hidden rounded-[3.5rem] bg-zinc-900 border border-white/5 transition-all duration-700 hover:-translate-y-4 shadow-2xl">
                    <img src="<?= htmlspecialchars($img) ?>" class="absolute inset-0 w-full h-full object-cover opacity-60 group-hover:opacity-100 transition-all duration-1000" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent opacity-90"></div>
                    <div class="absolute inset-0 p-12 flex flex-col justify-end">
                        <p class="text-[9px] font-black uppercase text-blue-500 tracking-[0.4em] mb-4 opacity-0 group-hover:opacity-100 transition-all"><?= (int)$album['photo_count'] ?> Bestanden</p>
                        <h2 class="serif-italic text-4xl text-white"><?= ucfirst($album['category_name']) ?></h2>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>