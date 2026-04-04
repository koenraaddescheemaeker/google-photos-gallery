<?php
// gallery.php - De Google Photos Presentatie
require_once 'config.php';
include 'navbar.php';

$view = $_GET['view'] ?? 'albums';

// De Harde ID-Logica van de Architect
if ($view === 'museum') {
    $condition = "id < " . MUSEUM_THRESHOLD;
    $pageTitle = "HET MUSEUM";
    $isMuseum  = true;
} else {
    $condition = "id >= " . MUSEUM_THRESHOLD;
    $pageTitle = "FAMILIE ALBUMS";
    $isMuseum  = false;
}

// Query op de correcte tabelnaam
$stmt = $db->query("SELECT * FROM album_settings WHERE $condition AND is_visible = TRUE ORDER BY priority ASC");
$albums = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="nl" class="bg-black text-white">
<head>
    <title><?= $pageTitle ?> | FORCEKES 2026</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="pt-32 antialiased">
    <?php if ($isMuseum) include 'bg-video.php'; ?>

    <main class="max-w-7xl mx-auto px-6 pb-20">
        <header class="mb-16 border-l-2 border-white/20 pl-8">
            <h1 class="text-6xl font-bold tracking-tighter italic uppercase"><?= $pageTitle ?></h1>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
            <?php foreach ($albums as $album): ?>
                <div class="group relative aspect-[16/10] bg-zinc-900 overflow-hidden rounded-sm ring-1 ring-white/5 hover:ring-white/20 transition-all">
                    <img src="<?= $album['google_link'] ?>" class="absolute inset-0 w-full h-full object-cover opacity-50 group-hover:scale-105 group-hover:opacity-100 transition-all duration-700">
                    
                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent p-8 flex flex-col justify-end">
                        <span class="text-[9px] font-mono tracking-widest text-white/30 uppercase mb-2">Sector <?= $album['id'] ?></span>
                        <h2 class="text-2xl font-bold uppercase tracking-tight"><?= htmlspecialchars($album['slug']) ?></h2>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>