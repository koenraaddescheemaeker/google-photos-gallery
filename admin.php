<?php
/** * FORCEKES - admin.php (The Control Center) */
require_once 'config.php';

// Harde check: Alleen Koen mag hier zijn
if (!isset($_SESSION['user_email']) || $_SESSION['user_email'] !== 'koen@lauwe.com') {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Forcekes | Beheer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { background-color: #000; color: #fff; font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-black">
    <?php include 'menu.php'; ?>

    <main class="max-w-4xl mx-auto px-6 py-32">
        <header class="mb-12">
            <h1 class="text-4xl font-black italic uppercase tracking-tighter">Beheer<span class="text-blue-600">paneel</span></h1>
            <p class="text-zinc-500 text-sm mt-2 uppercase tracking-widest">Systeemstatus en synchronisatie</p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="p-8 bg-zinc-900 rounded-[2.5rem] border border-white/5">
                <h3 class="text-lg font-bold mb-4">Google Photos Sync</h3>
                <p class="text-zinc-500 text-xs mb-8 leading-relaxed">
                    Haal de nieuwste beelden op uit de albums 'Museum' en 'Joris'. Dit proces draait op de achtergrond.
                </p>
                <a href="scraper.php?manual=true" class="inline-block px-8 py-4 bg-blue-600 rounded-full text-[10px] font-black uppercase tracking-widest hover:bg-blue-500 transition shadow-xl shadow-blue-600/20">
                    Start Handmatige Sync
                </a>
            </div>

            <div class="p-8 bg-zinc-900 rounded-[2.5rem] border border-white/5">
                <h3 class="text-lg font-bold mb-4">Systeem Info</h3>
                <ul class="space-y-4 text-[10px] font-bold uppercase tracking-widest text-zinc-400">
                    <li class="flex justify-between"><span>Domein:</span> <span class="text-white">forcekes.be</span></li>
                    <li class="flex justify-between"><span>Database:</span> <span class="text-green-500">Connected</span></li>
                    <li class="flex justify-between"><span>Admin:</span> <span class="text-blue-500"><?= $_SESSION['user_email'] ?></span></li>
                </ul>
            </div>
        </div>
    </main>
</body>
</html>