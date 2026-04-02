<?php
require_once 'config.php';
$userEmail = $_SESSION['user_email'] ?? '';
if (!$userEmail) { header("Location: login.php"); exit; }
$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_account'])) {
$updates = ["nickname" => $_POST['nickname'], "email" => $_POST['email']];
supabaseRequest("members?email=eq.$userEmail", "PATCH", $updates);
$msg = "Accountgegevens opgeslagen.";
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_album'])) {
$oldSlug = $_POST['original_slug'];
supabaseRequest("album_settings?slug=eq.$oldSlug", "PATCH", ["priority" => (int)$_POST['priority'], "is_visible" => $_POST['visibility'] === 'true']);
$msg = "Albuminstellingen bijgewerkt.";
}
$member = supabaseRequest("members?email=eq.$userEmail", 'GET')[0];
$myAlbums = supabaseRequest("album_settings?created_by=eq.$userEmail", 'GET');
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8"><title>Mijn Cockpit | Forcekes</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@1,900&display=swap'); body { background: #000; color: #fff; font-family: 'Inter', sans-serif; } .card { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); border-radius: 2rem; padding: 2.5rem; } input, select { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 0.8rem; border-radius: 1rem; width: 100%; outline: none; }</style>
</head>
<body class="bg-black">
<?php include 'menu.php'; ?>
<main class="max-w-6xl mx-auto px-10 pt-48 pb-32">
<header class="mb-12"><h1 style="font-family:'Playfair Display', serif;" class="text-5xl italic">Mijn Cockpit</h1><?php if($msg): ?><p class="text-blue-500 text-[10px] font-black uppercase mt-4"><?= $msg ?></p><?php endif; ?></header>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
<section class="card h-fit">
<h2 class="text-blue-600 font-black uppercase text-[10px] tracking-widest mb-8">Mijn Gegevens</h2>
<form method="POST" class="space-y-6">
<input type="text" name="nickname" value="<?= $member['nickname'] ?>" placeholder="ROEPNAAM">
<input type="email" name="email" value="<?= $member['email'] ?>" placeholder="EMAIL">
<input type="password" name="password" placeholder="NIEUW WACHTWOORD">
<button type="submit" name="save_account" class="w-full py-4 bg-white text-black rounded-xl font-black uppercase text-[10px]">Opslaan</button>
</form>
</section>
<section class="lg:col-span-2 card">
<h2 class="text-blue-600 font-black uppercase text-[10px] tracking-widest mb-8">Mijn Albums</h2>
<div class="space-y-4">
<?php foreach($myAlbums as $a): ?>
<form method="POST" class="bg-white/5 p-6 rounded-2xl flex flex-wrap gap-4 items-end border border-white/5">
<input type="hidden" name="original_slug" value="<?= $a['slug'] ?>">
<div class="flex-1 min-w-[200px]"><label class="text-[9px] text-zinc-600 block mb-2 uppercase font-black">Album Naam</label><p class="font-bold text-lg"><?= ucfirst($a['slug']) ?></p></div>
<div class="w-24"><label class="text-[9px] text-zinc-600 block mb-2 uppercase font-black">Prio</label><input type="number" name="priority" value="<?= $a['priority'] ?>"></div>
<div class="w-32"><label class="text-[9px] text-zinc-600 block mb-2 uppercase font-black">Status</label><select name="visibility"><option value="true" <?= $a['is_visible'] ? 'selected' : '' ?>>Zichtbaar</option><option value="false" <?= !$a['is_visible'] ? 'selected' : '' ?>>Verborgen</option></select></div>
<button type="submit" name="save_album" class="bg-zinc-800 text-white px-6 py-3 rounded-xl text-[10px] font-black uppercase">Update</button>
</form>
<?php endforeach; ?>
</div>
</section>
</div>
</main>
</body>
</html>