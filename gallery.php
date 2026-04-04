<?php
require_once 'config.php';
include 'navbar.php';

$view = $_GET['view'] ?? 'albums';
$cat_id = $_GET['cat_id'] ?? null;

// De Vlijmscherpe ID-Grens Logica
if ($view === 'museum' || ($cat_id && $cat_id == 1)) {
    $condition = "id < 100";
    $pageTitle = "HET MUSEUM";
    $isMuseum = true;
} else {
    $condition = "id >= 100";
    $pageTitle = "ALBUMS";
    $isMuseum = false;
}

// Eventueel filteren op specifieke categorie-ID als die meegegeven is
if ($cat_id) {
    $condition .= " AND category_id = " . intval($cat_id);
}

$albums = $db->query("SELECT * FROM album_settings WHERE $condition AND is_visible = true ORDER BY priority ASC, created_at DESC");
?>

<body class="bg-black text-white pt-32 antialiased">
    <?php if ($isMuseum): include 'bg-video.php'; endif; ?>

    <main class="max-w-7xl mx-auto px-6">
        <header class="mb-12">
            <h1 class="text-6xl font-bold tracking-tighter italic uppercase"><?= $pageTitle ?></h1>
            <div class="h-1 w-24 bg-white mt-4"></div>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
            <?php foreach ($albums as $album): ?>
                <div class="group relative aspect-video overflow-hidden rounded-sm border border-white/10 hover:border-white/40 transition-all">
                    <img src="<?= $album['cover_url'] ?>" class="absolute inset-0 w-full h-full object-cover opacity-50 group-hover:scale-110 transition-transform duration-1000">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 to-transparent p-6 flex flex-col justify-end">
                        <span class="text-[10px] font-mono text-white/30 uppercase">ID: <?= str_pad($album['id'], 3, '0', STR_PAD_LEFT) ?></span>
                        <h2 class="text-xl font-bold uppercase tracking-tight"><?= $album['title'] ?></h2>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>