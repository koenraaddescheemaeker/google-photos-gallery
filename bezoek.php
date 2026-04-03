<?php
require_once 'config.php';
$targetEmail = $_GET['user'] ?? '';
if (!$targetEmail) { header("Location: index.php"); exit; }
$member = supabaseRequest("members?email=eq." . rawurlencode($targetEmail), 'GET')[0] ?? null;
$albums = supabaseRequest("rpc/get_albums_by_owner", "POST", ["target_email" => $targetEmail]);
$nickname = $member['nickname'] ?? 'Familielid';
?>
<!DOCTYPE html>
<html lang="nl">
<head><meta charset="UTF-8"><title>FAMILIE | <?= $nickname ?></title><script src="https://cdn.tailwindcss.com"></script><style>@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@1,900&display=swap'); body { background: #000; color: #fff; font-family: 'Inter', sans-serif; }</style></head>
<body class="bg-black">
<?php include 'navbar.php'; ?>
<main class="max-w-7xl mx-auto px-10 pt-48 pb-32">
<header class="mb-20"><p class="text-blue-600 font-black uppercase text-[10px] tracking-widest mb-2">FAMILIE</p><h1 style="font-family:'Playfair Display', serif;" class="text-7xl italic"><?= $nickname ?></h1></header>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">
<?php if(!empty($albums)): foreach($albums as $a): ?>
<a href="gallery.php?page=<?= rawurlencode($a['category_name']) ?>" class="group relative aspect-square overflow-hidden rounded-[2.5rem] bg-zinc-900 border border-white/5">
<img src="<?= $a['thumbnail_url'] ?>" class="absolute inset-0 w-full h-full object-cover opacity-50 group-hover:opacity-100 transition-all duration-700">
<div class="absolute inset-0 p-8 flex flex-col justify-end"><h2 style="font-family:'Playfair Display', serif;" class="text-2xl italic"><?= ucfirst($a['category_name']) ?></h2></div>
</a>
<?php endforeach; endif; ?>
</div>
</main>
</body>
</html>