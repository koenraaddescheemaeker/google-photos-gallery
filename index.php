<?php
/** FORCEKES - index.php (Responsive Hero & Clean UI) */
require_once 'config.php';

$view = $_GET['view'] ?? 'dashboard';

// Haal data op voor het publieke dashboard
$recentPhotos = supabaseRequest('album_photos?order=created_at.desc&limit=4', 'GET');
$onlineUsers = supabaseRequest('presence?last_seen=gt.now()-interval \'5 minutes\'', 'GET');
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forcekes Portaal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #000; color: #fff; overflow-x: hidden; }
        .glass { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.08); }
        .badge { background: rgba(37, 99, 235, 0.85); backdrop-filter: blur(4px); }
    </style>
</head>
<body class="min-h-screen flex flex-col">

    <?php if ($view === 'dashboard'): ?>
        <?php include 'menu.php'; ?>
        
        <main class="max-w-7xl mx-auto px-6 py-8 md:py-12 w-full mt-20 md:mt-24">
            <header class="mb-10 md:mb-16">
                <h1 class="text-3xl sm:text-4xl md:text-6xl font-black italic uppercase tracking-tighter leading-none">
                    Forcekes <span class="text-blue-600">Portaal</span>
                </h1>
                <p class="text-zinc-500 mt-3 md:mt-4 text-xs md:text-sm font-medium tracking-wide">
                    Welkom bij de familie herinneringen.
                </p>
            </header>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">
                <section class="glass p-6 md:p-8 rounded-[2rem] md:rounded-[2.5rem] flex flex-col">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-400">Nu aanwezig</h3>
                        <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse shadow-[0_0_8px_#22c55e]"></span>
                    </div>
                    
                    <div class="space-y-4 flex-grow">
                        <?php if(!empty($onlineUsers) && is_array($onlineUsers)): ?>
                            <?php foreach ($onlineUsers as $user): ?>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-zinc-800 flex items-center justify-center text-[10px] font-black border border-white/5 uppercase">
                                        <?= substr($user['display_name'] ?? 'B', 0, 1) ?>
                                    </div>
                                    <span class="font-bold text-sm"><?= htmlspecialchars($user['display_name'] ?? 'Bezoeker') ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-xs text-zinc-600 italic">Het is even rustig in het portaal...</p>
                        <?php endif; ?>
                    </div>
                    
                    <a href="zwaaikamer.php" class="mt-8 block w-full bg-blue-600 text-center py-4 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-blue-500 transition shadow-lg shadow-blue-600/20">
                        Open Zwaaikamer
                    </a>
                </section>

                <section class="lg:col-span-2 glass p-6 md:p-8 rounded-[2rem] md:rounded-[2.5rem]">
                    <div class="flex justify-between items-center mb-8">
                        <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-400">Laatste toevoegingen</h3>
                    </div>
                    
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 md:gap-6">
                        <?php if(!empty($recentPhotos) && is_array($recentPhotos)): ?>
                            <?php foreach ($recentPhotos as $img): ?>
                                <a href="gallery.php?page=<?= $img['category'] ?>" class="group relative aspect-square rounded-2xl md:rounded-3xl overflow-hidden border border-white/5 bg-zinc-900 shadow-xl">
                                    <img src="<?= htmlspecialchars($img['thumbnail_url']) ?>" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition duration-700">
                                    <div class="absolute top-2 left-2 md:top-3 md:left-3 badge px-2 md:px-3 py-1 rounded-full text-[7px] md:text-[8px] font-black uppercase text-white">
                                        <?= ucfirst(htmlspecialchars($img['category'])) ?>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </main>

    <?php elseif ($view === 'login'): ?>
        <div class="flex-grow flex items-center justify-center p-6">
            <div class="w-full max-w-md glass p-10 md:p-12 rounded-[3rem] md:rounded-[3.5rem] shadow-2xl">
                <div class="text-center mb-10">
                    <h1 class="text-2xl font-black italic uppercase tracking-tighter">Admin <span class="text-blue-600">Toegang</span></h1>
                </div>
                <form action="auth-handler.php?action=login" method="POST" class="space-y-4">
                    <input type="email" name="email" placeholder="E-mailadres" required class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 outline-none focus:border-blue-600 text-sm">
                    <input type="password" name="password" placeholder="Wachtwoord" required class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 outline-none focus:border-blue-600 text-sm">
                    <button type="submit" class="w-full bg-blue-600 py-4 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-blue-500 transition">Inloggen</button>
                </form>
            </div>
        </div>
    <?php endif; ?>

</body>
</html>