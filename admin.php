<?php
/** * FORCEKES - admin.php (Database Status Dashboard) */
require_once 'config.php';

// Harde check: Alleen Koen mag hier zijn
if (!isset($_SESSION['user_email']) || $_SESSION['user_email'] !== 'koen@lauwe.com') {
    header("Location: index.php");
    exit;
}

// Haal statistieken op uit de database
$museumCount = supabaseRequest("album_photos?category=eq.museum&select=id", 'GET');
$jorisCount = supabaseRequest("album_photos?category=eq.joris&select=id", 'GET');

// Tel de resultaten (supabaseRequest geeft een array van IDs terug)
$countM = is_array($museumCount) ? count($museumCount) : 0;
$countJ = is_array($jorisCount) ? count($jorisCount) : 0;
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forcekes | Beheer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { background-color: #000; color: #fff; font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-black">
    <?php include 'menu.php'; ?>

    <main class="max-w-4xl mx-auto px-6 py-32">
        <header class="mb-12">
            <h1 class="text-4xl font-black italic uppercase tracking-tighter">Beheer<span class="text-blue-600">paneel</span></h1>
            <p class="text-zinc-500 text-xs mt-2 uppercase tracking-widest font-bold">Live database status</p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            
            <div class="p-8 bg-zinc-900 rounded-[2.5rem] border border-white/5 flex flex-col justify-between">
                <div>
                    <h3 class="text-zinc-500 text-[10px] font-black uppercase tracking-[0.2em] mb-2">Album</h3>
                    <h2 class="text-2xl font-black italic uppercase">Het Museum</h2>
                </div>
                <div class="mt-8 flex items-end justify-between">
                    <span class="text-6xl font-black text-blue-600 tracking-tighter"><?= $countM ?></span>
                    <span class="text-zinc-600 text-[10px] font-bold uppercase mb-2">Items in database</span>
                </div>
            </div>

            <div class="p-8 bg-zinc-900 rounded-[2.5rem] border border-white/5 flex flex-col justify-between">
                <div>
                    <h3 class="text-zinc-500 text-[10px] font-black uppercase tracking-[0.2em] mb-2">Album</h3>
                    <h2 class="text-2xl font-black italic uppercase">Joris</h2>
                </div>
                <div class="mt-8 flex items-end justify-between">
                    <span class="text-6xl font-black text-blue-600 tracking-tighter"><?= $countJ ?></span>
                    <span class="text-zinc-600 text-[10px] font-bold uppercase mb-2">Items in database</span>
                </div>
            </div>

            <div class="md:col-span-2 p-8 bg-zinc-900/50 rounded-[2.5rem] border border-white/5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-zinc-400">Database verbinding actief</span>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-zinc-600">Portaal v2.1</span>
                </div>
            </div>

        </div>
    </main>
</body>
</html>