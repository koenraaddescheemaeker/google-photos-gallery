<?php
/** * FORCEKES - menu.php (Gekeurd door Manu - Failsafe Edition) */
require_once 'config.php';

$userEmail = $_SESSION['user_email'] ?? '';
$isLoggedIn = !empty($userEmail);
$isAdmin = ($userEmail === 'koen@lauwe.com');

// Data ophalen
$navRaw = supabaseRequest("rpc/get_album_dashboard", 'GET');

// Manu: Vlijmscherpe controle op de data
$navAlbums = (is_array($navRaw) && !isset($navRaw['error'])) ? $navRaw : [];

// Filteren op zichtbaarheid
$visibleNav = array_filter($navAlbums, function($a) {
    return is_array($a) && ($a['is_visible'] ?? true) == true;
});

// Sorteren op prioriteit
usort($visibleNav, function($a, $b) {
    $pA = $a['priority'] ?? 999; $pB = $b['priority'] ?? 999;
    return ($pA !== $pB) ? ($pA <=> $pB) : strcmp((string)($a['category_name'] ?? ''), (string)($b['category_name'] ?? ''));
});

// Bezoekers-lijst voor het menu
$bezoekLeden = supabaseRequest("members?select=nickname,email&nickname=not.is.null", 'GET');
$bezoekLeden = is_array($bezoekLeden) ? $bezoekLeden : [];
?>
<style>
    .glass-nav { background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(20px); border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
    .nav-link { font-size: 14px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.2em; color: #fff; transition: all 0.4s; }
    .nav-link:hover { color: #3b82f6; }
    .nav-link-zinc { color: rgba(255,255,255,0.4); }
    
    .explorer-panel { transform: translateX(100%); transition: transform 0.6s cubic-bezier(0.2, 1, 0.3, 1); background: #020202; z-index: 1000; }
    .explorer-open .explorer-panel { transform: translateX(0); }
    
    .dropdown:hover .dropdown-menu { display: block; }
</style>

<nav class="fixed top-0 left-0 right-0 z-[100] transition-all duration-500 glass-nav" id="main-nav">
    <div class="max-w-7xl mx-auto px-8 py-6 flex justify-between items-center">
        <a href="index.php" class="text-lg font-black tracking-tighter">
            <span class="text-white">Force</span><span class="text-blue-600">kes</span> <span class="text-white opacity-80">Portaal</span>
        </a>

        <div class="hidden md:flex items-center space-x-10">
            <a href="index.php" class="nav-link">Home</a>
            
            <div class="relative group dropdown">
                <button class="nav-link flex items-center gap-2">Bezoek <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M6 9l6 6 6-6"/></svg></button>
                <div class="dropdown-menu absolute hidden bg-black border border-white/10 rounded-2xl p-4 mt-2 min-w-[160px] shadow-2xl">
                    <?php if (empty($bezoekLeden)): ?>
                        <p class="px-4 py-2 text-[10px] text-zinc-600 uppercase">Geen profielen</p>
                    <?php else: ?>
                        <?php foreach ($bezoekLeden as $lid): ?>
                            <a href="bezoek.php?user=<?= rawurlencode($lid['email']) ?>" class="block py-2 px-4 text-[11px] font-bold uppercase tracking-widest text-zinc-400 hover:text-blue-500 transition">
                                <?= htmlspecialchars($lid['nickname']) ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <a href="zwaaikamer.php" class="nav-link italic">Zwaaikamer</a>
            <button onclick="toggleExplorer()" class="nav-link nav-link-zinc">Verkenner</button>
            
            <?php if ($isAdmin): ?>
                <a href="admin.php" class="nav-link text-blue-500/50 hover:text-blue-500">Beheer</a>
            <?php endif; ?>

            <a href="<?= $isLoggedIn ? 'logout.php' : 'login.php' ?>" class="<?= $isLoggedIn ? 'nav-link nav-link-zinc' : 'px-6 py-2 bg-white text-black rounded-full text-[11px] font-black uppercase' ?>">
                <?= $isLoggedIn ? 'Logout' : 'Toegang' ?>
            </a>
        </div>
    </div>
</nav>

<aside id="explorer-panel" class="explorer-panel fixed top-0 right-0 bottom-0 w-full max-w-xs border-l border-white/5 p-12 overflow-y-auto">
    <header class="mb-12 flex justify-between items-center">
        <p class="text-[10px] font-black uppercase tracking-[0.3em] text-blue-600 italic">Verkenner</p>
        <button onclick="toggleExplorer()" class="text-zinc-600 hover:text-white"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>
    </header>
    <nav class="space-y-4">
        <?php if (empty($visibleNav)): ?>
            <p class="text-[10px] uppercase tracking-widest text-zinc-700 italic">Geen albums gevonden</p>
        <?php else: ?>
            <?php foreach ($visibleNav as $album): ?>
                <a href="gallery.php?page=<?= rawurlencode($album['category_name']) ?>" class="group block border-b border-white/5 pb-3">
                    <span class="serif-italic text-lg group-hover:text-blue-500 transition-all block italic"><?= ucfirst($album['category_name']) ?></span>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </nav>
</aside>

<script>
    function toggleExplorer() { document.body.classList.toggle('explorer-open'); }
    window.addEventListener('scroll', () => {
        const n = document.getElementById('main-nav');
        if (window.scrollY > 20) n.classList.add('glass-nav');
        else n.classList.remove('glass-nav');
    });
</script>