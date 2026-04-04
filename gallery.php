<?php
// gallery.php - De vlijmscherpe presentatie
require_once 'config.php';
include 'navbar.php';

$view = $_GET['view'] ?? 'albums';

// De Harde ID-Logica
if ($view === 'museum') {
    $condition = "id < " . MUSEUM_THRESHOLD;
    $title = "HET MUSEUM";
    $isMuseum = true;
} else {
    $condition = "id >= " . MUSEUM_THRESHOLD;
    $title = "FAMILIE ALBUMS";
    $isMuseum = false;
}

// Query op de juiste tabel: album_settings
$query = "SELECT * FROM album_settings WHERE $condition AND is_visible = TRUE ORDER BY priority ASC";
$stmt = $db->query($query);
$albums = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="nl" class="bg-black text-white">
<head>
    <title><?= $title ?> | FORCEKES</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="pt-32 antialiased">
    <?php if ($isMuseum) include 'bg-video.php'; ?>

    <main class="max-w-7xl mx-auto px-6">
        <header class="mb-12 border-l-4 border-white pl-6">
            <h1 class="text-6xl font-bold tracking-tighter italic uppercase"><?= $title ?></h1>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php foreach ($albums as $album): ?>
                <div class="group relative aspect-video bg-zinc-900 overflow-hidden border border-white/5">
                    <img src="<?= $album['google_link'] ?>" class="absolute inset-0 w-full h-full object-cover opacity-40 group-hover:opacity-100 transition-all duration-700">
                    <div class="absolute inset-0 bg-gradient-to-t from-black p-6 flex flex-col justify-end">
                        <span class="text-[10px] font-mono text-white/30">ID: <?= $album['id'] ?></span>
                        <h2 class="text-xl font-bold uppercase"><?= htmlspecialchars($album['slug']) ?></h2>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>