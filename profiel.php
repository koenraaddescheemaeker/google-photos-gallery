<?php
/** * FORCEKES - profiel.php (Gekeurd door Manu) */
require_once 'config.php';
$userEmail = $_SESSION['user_email'] ?? '';
if (!$userEmail) { header("Location: login.php"); exit; }

$status = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nickname = trim($_POST['nickname']);
    supabaseRequest("members?email=eq.$userEmail", "PATCH", ["nickname" => $nickname]);
    $status = "Roepnaam vlijmscherp bijgewerkt.";
}

$member = supabaseRequest("members?email=eq.$userEmail", 'GET')[0] ?? null;
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8"><title>Mijn Profiel | Forcekes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;900&family=Playfair+Display:ital,wght@1,900&display=swap');
        body { background: #000; color: #fff; font-family: 'Inter', sans-serif; }
        .card { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); border-radius: 2.5rem; padding: 3rem; }
        input { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; padding: 1rem; border-radius: 1rem; width: 100%; outline: none; }
    </style>
</head>
<body class="bg-black">
    <?php include 'menu.php'; ?>
    <main class="max-w-xl mx-auto px-10 pt-48 pb-32">
        <header class="mb-12 text-center">
            <h1 style="font-family:'Playfair Display', serif;" class="text-4xl italic mb-2">Mijn Profiel</h1>
            <p class="text-zinc-600 text-[10px] uppercase tracking-[0.4em]"><?= $userEmail ?></p>
        </header>

        <div class="card space-y-8">
            <?php if($status): ?> <p class="text-blue-500 text-[10px] font-black uppercase text-center"><?= $status ?></p> <?php endif; ?>
            
            <form method="POST" class="space-y-6">
                <div>
                    <label class="text-[9px] text-zinc-500 uppercase font-bold tracking-widest mb-2 block">Roepnaam</label>
                    <input type="text" name="nickname" value="<?= htmlspecialchars($member['nickname'] ?? '') ?>" placeholder="Hoe noemt de familie u?" required>
                    <p class="text-[9px] text-zinc-700 mt-2 italic">Deze naam verschijnt in het 'Bezoek' menu.</p>
                </div>
                <button type="submit" class="w-full py-4 bg-white text-black rounded-xl font-black uppercase text-[10px] tracking-widest hover:bg-blue-600 hover:text-white transition">Opslaan</button>
            </form>
            
            <hr class="border-white/5">
            
            <div class="flex justify-between items-center text-[10px] uppercase font-black tracking-widest">
                <span class="text-zinc-600">Status</span>
                <span class="text-blue-500"><?= $member['role'] ?></span>
            </div>
        </div>
        
        <div class="mt-12 text-center">
            <a href="mijn-albums.php" class="text-[10px] font-black uppercase text-zinc-500 hover:text-white transition tracking-widest">→ Beheer mijn Albums</a>
        </div>
    </main>
</body>
</html>