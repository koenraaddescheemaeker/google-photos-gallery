<?php
/** * FORCEKES - bezoek.php (Fase 20: Personal 16:9 Grid) */
require_once 'config.php';
$targetUser = $_GET['user'] ?? '';
if (!$targetUser) { header("Location: index.php"); exit; }

$memberData = supabaseRequest("members?email=eq." . rawurlencode($targetUser), 'GET');
$nickname = $memberData[0]['nickname'] ?? 'Familielid';

$albumsRaw = supabaseRequest("rpc/get_albums_by_owner", "POST", ["target_email" => $targetUser]);
$albums = (is_array($albumsRaw) && !isset($albumsRaw['error'])) ? $albumsRaw : [];
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8"><title><?= htmlspecialchars($nickname) ?> | Forcekes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;900&family=Playfair+Display:ital,wght@1,900&display=swap');
        body { background: #000; color: #fff; font-family: 'Inter', sans-serif; }
        .serif-italic { font-family: 'Playfair Display', serif; font-style: italic; }
    </style>
</head>
<body class="bg-black">
    <?php include 'menu.php'; ?>
    <main class="max-w-7xl mx-auto px-6 pt-48 pb-32">
        <header class="mb-20">
            <p class="text-blue-600 font-black uppercase text-[10px] tracking-[0.4em] mb-2">Bezoek bij</p>
            <h1 class="serif-italic text-6xl italic"><?= htmlspecialchars($nickname) ?></h1>
        </header>

        <?php if (empty($albums)): ?>
            <div class="py-20 border border-white/5 rounded-[3rem] text-center"><p class="serif-italic text-xl text-zinc-600 italic">Deze kamer wacht op herinneringen.</p></div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <?php foreach ($albums as $album): 
                    $catName = $album['category_name'] ?? 'Album';
                ?>
                    <a href="gallery.php?page=<?= rawurlencode($catName) ?>" class="group relative aspect-video sm:aspect-square overflow-hidden rounded-[2.5rem] bg-zinc-900 border border-white/5 shadow-2xl">
                        <img src="<?= $album['thumbnail_url'] ?>" class="absolute inset-0 w-full h-full object-cover opacity-50 group-hover:opacity-100 transition-all duration-700">
                        <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent"></div>
                        <div class="absolute inset-0 p-8 flex flex-col justify-end">
                            <h2 class="serif-italic text-xl text-white italic"><?= ucfirst($catName) ?></h2>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>