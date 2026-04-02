<?php
require_once 'config.php';
$feesten = supabaseRequest("rpc/get_album_dashboard", 'GET');
if (is_array($feesten)) { usort($feesten, fn($a, $b) => ($a['priority'] ?? 999) <=> ($b['priority'] ?? 999)); }
?>
<!DOCTYPE html>
<html lang="nl">
<head><meta charset="UTF-8"><title>HOME | Forcekes</title><script src="https://cdn.tailwindcss.com"></script><style>@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@1,900&display=swap'); body { background: #000; color: #fff; font-family: 'Inter', sans-serif; } .serif-italic { font-family: 'Playfair Display', serif; font-style: italic; }</style></head>
<body class="bg-black">
<?php include 'menu.php'; ?>
<main class="max-w-7xl mx-auto px-10 pt-48 pb-32">
<header class="mb-20 text-center"><h1 class="serif-italic text-5xl md:text-7xl mb-4">Onze FEESTEN</h1><p class="text-zinc-500 uppercase tracking-[0.4em] text-[10px]">Vlijmscherp bewaarde herinneringen</p></header>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">
<?php if(is_array($feesten)): foreach($feesten as $f): if(!($f['is_visible'] ?? true)) continue; ?>
<a href="gallery.php?page=<?= rawurlencode($f['category_name']) ?>" class="group relative aspect-square overflow-hidden rounded-[2.5rem] bg-zinc-900 border border-white/5 transition-all duration-700 hover:-translate-y-2">
<img src="<?= $f['thumbnail_url'] ?>" class="absolute inset-0 w-full h-full object-cover opacity-50 group-hover:opacity-100 transition-all duration-1000" loading="lazy">
<div class="absolute inset-0 p-8 flex flex-col justify-end"><h2 class="serif-italic text-2xl"><?= ucfirst($f['category_name']) ?></h2></div>
</a>
<?php endforeach; endif; ?>
</div>
</main>
</body>
</html>