<?php
require_once 'config.php';

// We halen gewoon alle zichtbare albums op, zonder de ID-grens complicaties voor nu
$query = "SELECT * FROM album_settings WHERE is_visible = TRUE ORDER BY priority ASC";
$albums = $db->query($query)->fetchAll();
?>
<!DOCTYPE html>
<html lang="nl" class="bg-black text-white">
<head>
    <meta charset="UTF-8">
    <title>FORCEKES | NIEUWE START</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-8">
    <header class="mb-12">
        <h1 class="text-4xl font-bold tracking-tighter uppercase italic">FORCEKES <span class="text-white/30">NEW</span></h1>
        <p class="text-white/50 text-sm">Familieportaal - Google Photos Archief</p>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php foreach ($albums as $album): ?>
            <div class="border border-white/10 p-4 rounded-lg bg-zinc-900">
                <img src="<?= $album['google_link'] ?>" class="w-full aspect-video object-cover rounded mb-4">
                <h2 class="font-bold uppercase text-lg"><?= htmlspecialchars($album['slug']) ?></h2>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>