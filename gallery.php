<?php
require_once 'config.php';
$cat_id = $_GET['id'] ?? 1;
$isMuseum = ($cat_id == 1);
$condition = $isMuseum ? "id < " . MUSEUM_THRESHOLD : "id >= " . MUSEUM_THRESHOLD;

$stmt = $db->prepare("SELECT * FROM album_settings WHERE category_id = ? AND $condition AND is_visible = TRUE ORDER BY priority ASC");
$stmt->execute([$cat_id]);
$albums = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="nl" class="bg-black text-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isMuseum ? 'Het Museum' : 'Familie Albums' ?> | FORCEKES</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="pt-32 p-8">
    <?php include 'navbar.php'; ?>
    <h1 class="text-6xl font-black italic mb-12 uppercase tracking-tighter opacity-80">
        <?= $isMuseum ? 'Het Museum' : 'Familie Albums' ?>
    </h1>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php foreach ($albums as $album): ?>
            <div class="group relative aspect-video bg-zinc-900 border border-white/5 overflow-hidden rounded-lg">
                <img src="<?= $album['google_link'] ?>" class="absolute inset-0 w-full h-full object-cover opacity-60 group-hover:opacity-100 transition-all duration-700 scale-105 group-hover:scale-100">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent p-6 flex flex-col justify-end">
                    <h2 class="text-xl font-bold uppercase tracking-tight"><?= htmlspecialchars($album['title']) ?></h2>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
