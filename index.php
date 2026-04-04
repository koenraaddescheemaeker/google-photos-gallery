<?php
require_once 'config.php'; // Altijd eerst de fundering
include 'navbar.php';     // Dan de rest
$feesten = supabaseRequest("rpc/get_album_dashboard", 'GET');
if(is_array($feesten)){usort($feesten,fn($a,$b)=>($a['priority']??999)<=>($b['priority']??999));}
?>
<!DOCTYPE html>
<html lang="nl">
<head><meta charset="UTF-8"><title>HOME | Forcekes</title><script src="https://cdn.tailwindcss.com"></script><style>@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@1,900&display=swap'); body{background:#000;color:#fff;font-family:'Inter',sans-serif;} .serif-italic{font-family:'Playfair Display',serif;font-style:italic;}</style></head>
<body class="bg-black">
<?php include 'navbar.php'; ?>
<main class="max-w-7xl mx-auto px-10 pt-48 pb-32">
<header class="mb-20 text-center"><h1 class="serif-italic text-5xl md:text-7xl mb-4">FEESTEN</h1></header>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">
<?php if(is_array($feesten) && !isset($feesten['error'])): foreach($feesten as $f): if(!($f['is_visible']??true))continue; ?>
<a href="gallery.php?page=<?=rawurlencode($f['category_name'])?>" class="group relative aspect-square overflow-hidden rounded-[2.5rem] bg-zinc-900 border border-white/5 transition-all duration-700 hover:-translate-y-2">
<img src="<?=$f['thumbnail_url'] ?? 'https://via.placeholder.com/400?text=Geen+Foto'?>" class="absolute inset-0 w-full h-full object-cover opacity-50 group-hover:opacity-100 transition-all duration-1000" loading="lazy">
<div class="absolute inset-0 p-8 flex flex-col justify-end"><h2 class="serif-italic text-2xl text-white"><?=ucfirst($f['category_name'])?></h2><p class="text-[9px] font-black text-blue-500 uppercase"><?=$f['photo_count']?> items</p></div>
</a>
<?php endforeach; else: ?>
<div class="col-span-full text-center py-20 border border-dashed border-white/10 rounded-3xl"><p class="text-zinc-600 italic">De kluis is momenteel leeg of onbereikbaar.</p></div>
<?php endif; ?>
</div>
</main>
</body>
</html>