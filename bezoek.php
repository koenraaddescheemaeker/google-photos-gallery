<?php
/** * FORCEKES - bezoek.php (Fase 16: De Persoonlijke Kamer) */
require_once 'config.php';

$targetUser = $_GET['user'] ?? '';
if (!$targetUser) { header("Location: index.php"); exit; }

// Haal roepnaam op
$memberData = supabaseRequest("members?email=eq." . rawurlencode($targetUser), 'GET');
$nickname = $memberData[0]['nickname'] ?? 'Familielid';

// Haal albums van dit lid op
$albums = supabaseRequest("rpc/get_albums_by_owner", "POST", ["target_email" => $targetUser]);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8"><title><?= $nickname ?> | Forcekes</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white">
    <?php include 'menu.php'; ?>
    <main class="max-w-7xl mx-auto px-10 pt-48 pb-32">
        <header class="mb-20">
            <p class="text-blue-600 font-black uppercase text-[10px] tracking-[0.4em] mb-2">Bezoek bij</p>
            <h1 style="font-family:'Playfair Display', serif;" class="text-6xl italic"><?= $nickname ?></h1>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
            <?php foreach ($albums as $album): ?>
                <a href="gallery.php?page=<?= rawurlencode($album['category_name']) ?>" class="group relative aspect-square overflow-hidden rounded-[2rem] bg-zinc-900 border border-white/5">
                    <img src="<?= $album['thumbnail_url'] ?>" class="absolute inset-0 w-full h-full object-cover opacity-50 group-hover:opacity-100 transition-all">
                    <div class="absolute inset-0 p-8 flex flex-col justify-end">
                        <h2 class="text-xl font-bold italic"><?= ucfirst($album['category_name']) ?></h2>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>