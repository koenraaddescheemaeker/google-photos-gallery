<?php
/** * FORCEKES - menu.php (Fase 16: Bezoek Integratie) */
require_once 'config.php';

$userEmail = $_SESSION['user_email'] ?? '';
$isLoggedIn = !empty($userEmail);

// Haal de roepnamen op van alle leden die albums hebben (voor het Bezoek-menu)
$bezoekLeden = supabaseRequest("members?select=nickname,email&nickname=not.is.null", 'GET');
?>
<style>
    /* ... bestaande stijlen ... */
    .nav-link { font-size: 14px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.2em; }
    .dropdown:hover .dropdown-menu { display: block; }
</style>

<nav class="fixed top-0 left-0 right-0 z-[100] transition-all duration-500 glass-nav">
    <div class="max-w-7xl mx-auto px-8 py-6 flex justify-between items-center">
        <a href="index.php" class="text-lg font-black tracking-tighter">
            <span class="text-white">Force</span><span class="text-blue-600">kes</span> <span class="text-white opacity-80">Portaal</span>
        </a>

        <div class="hidden md:flex items-center space-x-10">
            <a href="index.php" class="nav-link text-white">Home</a>
            
            <div class="relative group dropdown">
                <button class="nav-link text-white flex items-center gap-2">Bezoek <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M6 9l6 6 6-6"/></svg></button>
                <div class="dropdown-menu absolute hidden bg-black border border-white/10 rounded-2xl p-4 mt-2 min-w-[160px] shadow-2xl">
                    <?php foreach ($bezoekLeden as $lid): ?>
                        <a href="bezoek.php?user=<?= rawurlencode($lid['email']) ?>" class="block py-2 px-4 text-[11px] font-bold uppercase tracking-widest text-zinc-400 hover:text-blue-500 transition">
                            <?= htmlspecialchars($lid['nickname']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <a href="zwaaikamer.php" class="nav-link text-white italic">Zwaaikamer</a>
            <button onclick="toggleExplorer()" class="nav-link text-zinc-500">Verkenner</button>
            
            <?php if ($userEmail === 'koen@lauwe.com'): ?>
                <a href="admin.php" class="nav-link text-blue-500/50">Beheer</a>
            <?php endif; ?>

            <a href="<?= $isLoggedIn ? 'logout.php' : 'login.php' ?>" class="<?= $isLoggedIn ? 'nav-link text-zinc-500' : 'px-6 py-2 bg-white text-black rounded-full text-[11px] font-black uppercase' ?>">
                <?= $isLoggedIn ? 'Logout' : 'Toegang' ?>
            </a>
        </div>
    </div>
</nav>