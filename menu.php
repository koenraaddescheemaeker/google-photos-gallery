<?php
/** * FORCEKES - menu.php (Fase 22: Navigatie Update - Gekeurd door Manu) */
require_once 'config.php';
$userEmail = $_SESSION['user_email'] ?? '';
$isLoggedIn = !empty($userEmail);
$isAdmin = ($userEmail === 'koen@lauwe.com');

// Data ophalen voor Verkenner & Bezoek
$navRaw = supabaseRequest("rpc/get_album_dashboard", 'GET');
$navAlbums = (is_array($navRaw) && !isset($navRaw['error'])) ? $navRaw : [];
$visibleNav = array_filter($navAlbums, fn($a) => ($a['is_visible'] ?? true) == true);
usort($visibleNav, fn($a, $b) => ($a['priority'] ?? 999) <=> ($b['priority'] ?? 999));

$bezoekLeden = supabaseRequest("members?select=nickname,email&nickname=not.is.null", 'GET');
$bezoekLeden = is_array($bezoekLeden) ? $bezoekLeden : [];
?>
<style>
    .glass-nav { background: rgba(0, 0, 0, 0.85); backdrop-filter: blur(25px); border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
    .nav-link { font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.15em; color: #fff; transition: all 0.3s; }
    .nav-link:hover { color: #3b82f6; }
    .nav-link-zinc { color: rgba(255,255,255,0.4); }
    .dropdown { position: relative; }
    .dropdown-menu { display: none; position: absolute; top: 100%; left: 0; padding-top: 20px; z-index: 500; }
    .dropdown:hover .dropdown-menu { display: block; }
    .dropdown-content { background: #000; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 1.5rem; padding: 1rem; min-width: 180px; box-shadow: 0 20px 40px rgba(0,0,0,0.5); }
    #mobile-overlay { transform: translateY(-100%); transition: transform 0.5s cubic-bezier(0.2, 1, 0.3, 1); background: #000; }
    .mobile-open #mobile-overlay { transform: translateY(0); }
    .panel-hidden { transform: translateX(100%); transition: transform 0.6s cubic-bezier(0.2, 1, 0.3, 1); background: #020202; }
    .panel-visible { transform: translateX(0); }
</style>

<nav class="fixed top-0 left-0 right-0 z-[200] glass-nav" id="main-nav">
    <div class="max-w-7xl mx-auto px-6 py-5 flex justify-between items-center">
        <a href="index.php" class="text-lg font-black tracking-tighter z-[210]">
            <span class="text-white">Force</span><span class="text-blue-600">kes</span> <span class="text-white opacity-60">Portaal</span>
        </a>

        <div class="hidden md:flex items-center space-x-8">
            <a href="index.php" class="nav-link">Home</a>
            <div class="dropdown">
                <button class="nav-link flex items-center gap-2">Bezoek <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M6 9l6 6 6-6"/></svg></button>
                <div class="dropdown-menu"><div class="dropdown-content">
                    <?php foreach ($bezoekLeden as $lid): ?>
                        <a href="bezoek.php?user=<?= rawurlencode($lid['email']) ?>" class="block py-2 px-4 text-[11px] font-bold uppercase tracking-widest text-zinc-400 hover:text-blue-500"><?= htmlspecialchars($lid['nickname']) ?></a>
                    <?php endforeach; ?>
                </div></div>
            </div>
            <a href="zwaaikamer.php" class="nav-link italic">Zwaaikamer</a>
            <button onclick="toggleExplorer()" class="nav-link nav-link-zinc">Verkenner</button>
            <?php if ($isAdmin): ?> <a href="admin.php" class="nav-link text-blue-500/70 hover:text-blue-500">Beheer</a> <?php endif; ?>
            
            <a href="handleiding.php" class="nav-link text-zinc-500 hover:text-white">Handleiding</a>

            <a href="<?= $isLoggedIn ? 'profiel.php' : 'login.php' ?>" class="<?= $isLoggedIn ? 'px-5 py-2 bg-white text-black rounded-full text-[10px] font-black uppercase' : 'nav-link' ?>">
                <?= $isLoggedIn ? 'Profiel' : 'Toegang' ?>
            </a>
        </div>

        <button onclick="toggleMobile()" class="md:hidden z-[210] text-white p-2">
            <svg id="menu-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>
    </div>
</nav>

<div id="mobile-overlay" class="fixed inset-0 z-[190] md:hidden flex flex-col pt-32 px-10 overflow-y-auto">
    <nav class="flex flex-col space-y-8 pb-10">
        <a href="index.php" onclick="toggleMobile()" class="text-4xl font-black uppercase tracking-tighter">Home</a>
        <div><p class="text-[10px] font-black text-blue-600 uppercase tracking-[0.4em] mb-4">Leden</p>
            <div class="grid grid-cols-2 gap-4">
                <?php foreach ($bezoekLeden as $lid): ?>
                    <a href="bezoek.php?user=<?= rawurlencode($lid['email']) ?>" onclick="toggleMobile()" class="text-xl font-bold italic text-zinc-400"><?= htmlspecialchars($lid['nickname']) ?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <a href="handleiding.php" onclick="toggleMobile()" class="text-4xl font-black uppercase tracking-tighter text-zinc-500">Handleiding</a>
        <a href="zwaaikamer.php" onclick="toggleMobile()" class="text-4xl font-black uppercase tracking-tighter italic text-white">Zwaaikamer</a>
        <button onclick="toggleMobile(); toggleExplorer();" class="text-left text-4xl font-black uppercase tracking-tighter text-zinc-600">Verkenner</button>
        <?php if($isAdmin): ?> <a href="admin.php" onclick="toggleMobile()" class="text-4xl font-black uppercase tracking-tighter text-blue-500/50">Beheer</a> <?php endif; ?>
        <div class="pt-8 border-t border-white/5">
            <a href="<?= $isLoggedIn ? 'profiel.php' : 'login.php' ?>" onclick="toggleMobile()" class="text-xl font-black uppercase tracking-widest text-blue-500"><?= $isLoggedIn ? 'Mijn Profiel' : 'Inloggen' ?></a>
        </div>
    </nav>
</div>

<aside id="explorer-panel" class="panel-hidden fixed top-0 right-0 bottom-0 w-full max-w-xs border-l border-white/5 p-12 z-[300] overflow-y-auto shadow-2xl">
    <header class="mb-12 flex justify-between items-center">
        <p class="text-[10px] font-black uppercase tracking-[0.3em] text-blue-600 italic">Verkenner</p>
        <button onclick="toggleExplorer()" class="text-zinc-600 hover:text-white"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg></button>
    </header>
    <nav class="space-y-4">
        <?php foreach ($visibleNav as $album): ?>
            <a href="gallery.php?page=<?= rawurlencode($album['category_name']) ?>" class="group block border-b border-white/5 pb-3">
                <span class="text-lg italic group-hover:text-blue-500 transition-all block"><?= ucfirst($album['category_name']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
</aside>

<script>
    function toggleMobile() { document.body.classList.toggle('mobile-open'); }
    function toggleExplorer() { const p = document.getElementById('explorer-panel'); p.classList.toggle('panel-visible'); p.classList.toggle('panel-hidden'); }
</script>