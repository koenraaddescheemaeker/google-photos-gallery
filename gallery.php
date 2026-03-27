<?php
require_once 'config.php';
$pageSlug = $_GET['page'] ?? 'museum';
$photos = supabaseRequest("album_photos?category=eq.$pageSlug&select=*", 'GET');
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Forcekes | <?= ucfirst($pageSlug) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white">
    <?php include 'menu.php'; ?>
    
    <main class="max-w-7xl mx-auto px-6 py-12">
        <h2 class="text-5xl font-black italic uppercase tracking-tighter mb-12"><?= $pageSlug ?></h2>
        
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($photos as $p): ?>
                <div class="aspect-square rounded-[2rem] overflow-hidden border border-white/5 bg-zinc-900 group">
                    <img src="<?= $p['thumbnail_url'] ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-700">
                </div>
            <?php endforeach; ?>
            <?php if(empty($photos)) echo "<p class='text-zinc-500 italic'>Nog geen foto's gesynchroniseerd voor deze pagina.</p>"; ?>
        </div>
    </main>
</body>
</html>