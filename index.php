<?php
/** FORCEKES - index.php (Full Premium & Fixed Auth) */
require_once 'config.php';

$isLoggedIn = isset($_SESSION['user_id']); 
$view = $_GET['view'] ?? 'login';
if ($isLoggedIn) $view = 'dashboard';

$recentPhotos = [];
$onlineUsers = [];

if ($view === 'dashboard') {
    $recentPhotos = supabaseRequest('album_photos?order=created_at.desc&limit=4', 'GET');
    $onlineUsers = supabaseRequest('presence?last_seen=gt.now()-interval \'5 minutes\'', 'GET');
}
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
        body { font-family: 'Inter', sans-serif; background-color: #000; color: #fff; }
        .glass { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.08); }
        .badge { background: rgba(37, 99, 235, 0.85); backdrop-filter: blur(4px); }
    </style>
</head>
<body class="min-h-screen flex flex-col">

    <?php if ($view === 'dashboard'): ?>
        <?php include 'menu.php'; ?>
        <main class="max-w-7xl mx-auto px-6 py-12 w-full mt-20">
            <header class="mb-12">
                <h1 class="text-5xl font-black italic uppercase tracking-tighter">Welkom <span class="text-blue-500">Thuis</span></h1>
            </header>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <section class="glass p-8 rounded-[2.5rem] flex flex-col">
                    <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-500 mb-6">Nu online</h3>
                    <div class="space-y-4 flex-grow">
                        <?php if(!empty($onlineUsers)) foreach ($onlineUsers as $user): ?>
                            <div class="flex items-center gap-3">
                                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse shadow-[0_0_8px_#22c55e]"></span>
                                <span class="font-bold text-sm"><?= htmlspecialchars($user['display_name'] ?? 'Familielid') ?></span>
                            </div>
                        <?php endforeach; else: ?>
                            <p class="text-xs text-zinc-600 italic">Even geen activiteit...</p>
                        <?php endif; ?>
                    </div>
                    <a href="zwaaikamer.php" class="mt-8 block w-full bg-blue-600 text-center py-4 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-blue-500 transition">Open Zwaaikamer</a>
                </section>
                <section class="lg:col-span-2 glass p-8 rounded-[2.5rem]">
                    <div class="flex justify-between items-center mb-8">
                        <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-500">Nieuwste Herinneringen</h3>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <?php if(!empty($recentPhotos)) foreach ($recentPhotos as $img): ?>
                            <div class="group relative aspect-square rounded-3xl overflow-hidden border border-white/5 bg-zinc-900 shadow-xl">
                                <img src="<?= $img['thumbnail_url'] ?>" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition duration-700">
                                <div class="absolute top-3 left-3 badge px-3 py-1 rounded-full text-[8px] font-black uppercase text-white"><?= ucfirst($img['category']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>
        </main>
    <?php else: ?>
        <div class="flex-grow flex items-center justify-center p-6">
            <div class="w-full max-w-md glass p-12 rounded-[3.5rem] shadow-2xl">
                <div class="text-center mb-10">
                    <h1 class="text-2xl font-black italic uppercase tracking-tighter">FORCEKES <span class="text-blue-600">PORTAAL</span></h1>
                </div>

                <?php if (isset($_GET['msg'])) echo "<p class='mb-6 p-4 bg-blue-500/10 border border-blue-500/20 rounded-xl text-blue-400 text-center text-xs font-bold uppercase'>".htmlspecialchars($_GET['msg'])."</p>"; ?>

                <?php if ($view === 'login'): ?>
                    <form action="auth-handler.php?action=login" method="POST" class="space-y-4">
                        <input type="email" name="email" placeholder="E-mailadres" required class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 outline-none focus:border-blue-600 text-sm">
                        <input type="password" name="password" placeholder="Wachtwoord" required class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 outline-none focus:border-blue-600 text-sm">
                        <button type="submit" class="w-full bg-blue-600 py-4 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-blue-500 transition shadow-lg shadow-blue-600/20">Inloggen</button>
                    </form>
                    <div class="mt-8 flex justify-between text-[9px] font-black uppercase tracking-widest text-zinc-500">
                        <a href="index.php?view=forgot" class="hover:text-white transition">Vergeten?</a>
                        <a href="index.php?view=register" class="hover:text-white transition">Nieuw account</a>
                    </div>

                <?php elseif ($view === 'register'): ?>
                    <form action="auth-handler.php?action=register" method="POST" class="space-y-4">
                        <input type="text" name="name" placeholder="Voornaam" required class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 outline-none focus:border-blue-600 text-sm">
                        <input type="email" name="email" placeholder="E-mailadres" required class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 outline-none focus:border-blue-600 text-sm">
                        <input type="password" name="password" placeholder="Wachtwoord" required class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 outline-none focus:border-blue-600 text-sm">
                        <button type="submit" class="w-full bg-blue-600 py-4 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-blue-500 transition shadow-lg shadow-blue-600/20">Registreren</button>
                    </form>
                    <div class="mt-8 text-center">
                        <a href="index.php?view=login" class="text-[9px] font-black uppercase text-zinc-500 hover:text-white transition">Terug naar login</a>
                    </div>

                <?php elseif ($view === 'forgot'): ?>
                    <form action="auth-handler.php?action=recover" method="POST" class="text-center space-y-6">
                        <p class="text-xs text-zinc-400 px-4">Vul je e-mailadres in voor een herstel-link.</p>
                        <input type="email" name="email" required placeholder="naam@familie.be" class="w-full bg-white/5 border border-white/10 rounded-2xl px-6 py-4 outline-none focus:border-blue-600 text-sm text-center">
                        <button type="submit" class="w-full bg-blue-600 py-4 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-blue-500 transition">Verstuur Link</button>
                        <a href="index.php?view=login" class="block text-[9px] font-black uppercase text-zinc-500 hover:text-white transition pt-4">Annuleren</a>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</body>
</html>