<?php
/** * FORCEKES - menu.php (Fase 13: Navigation Refinement - Gekeurd door Manu) */
require_once 'config.php';

$userEmail = isset($_SESSION['user_email']) ? strtolower($_SESSION['user_email']) : '';
$isLoggedIn = !empty($userEmail);
$isAdmin = ($userEmail === 'koen@lauwe.com');

$navRaw = supabaseRequest("rpc/get_album_dashboard", 'GET');
$navAlbums = (is_array($navRaw) && !isset($navRaw['error'])) ? $navRaw : [];

$visibleNav = array_filter($navAlbums, function($a) { return ($a['is_visible'] ?? true) == true; });
usort($visibleNav, function($a, $b) {
    $pA = $a['priority'] ?? 999; $pB = $b['priority'] ?? 999;
    return ($pA !== $pB) ? ($pA <=> $pB) : strcmp((string)($a['category_name'] ?? ''), (string)($b['category_name'] ?? ''));
});
?>
<style>
    .glass-nav { background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(20px); border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
    /* Manu: Lettertype 1.5x groter (ca 14px) en wit voor Home/Zwaaikamer */
    .nav-link { font-size: 14px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.2em; transition: all 0.4s; }
    .nav-link-white { color: #fff; }
    .nav-link-zinc { color: rgba(255,255,255,0.4); }
    .nav-link:hover { color: #3b82f6; }
    
    .explorer-panel { transform: translateX(100%); transition: transform 0.6s cubic-bezier(0.2, 1, 0.3, 1); background: #020202; z-index: 1000; }
    .explorer-open .explorer-panel { transform: translateX(0); }
    
    /* Mobile Overlay */
    #mobile-menu { transform: translateY(-100%); transition: transform 0.5s ease; }
    .mobile-open #mobile-menu { transform: translateY(0); }
</style>

<nav class="fixed top-0 left-0 right-0 z-[100] transition-all duration-500" id="main-nav">
    <div class="max-w-7xl mx-auto px-8 py-6 flex justify-between items-center">
        <a href="index.php" class="text-lg font-black tracking-tighter">
            <span class="text-white">Force</span><span class="text-blue-600">kes</span> <span class="text-white opacity-80">Portaal</span>
        </a>

        <div class="hidden md:flex items-center space-x-10">
            <a href="index.php" class="nav-link nav-link-white">Home</a>
            <a href="zwaaikamer.php" class="nav-link nav-link-white italic">Zwaaikamer</a>
            <button onclick="toggleExplorer()" class="nav-link nav-link-zinc">Verkenner</button>
            <?php if ($isAdmin): ?> <a href="admin.php" class="nav-link text-blue-500/50 hover:text-blue-500">Beheer</a> <?php endif; ?>
            <a href="<?= $isLoggedIn ? 'logout.php' : 'login.php' ?>" class="<?= $isLoggedIn ? 'nav-link nav-link-zinc' : 'px-6 py-2 bg-white text-black rounded-full text-[11px] font-black uppercase' ?>">
                <?= $isLoggedIn ? 'Logout' : 'Toegang' ?>
            </a>
        </div>

        <button onclick="toggleMobile()" class="md:hidden text-white p-2">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>
    </div>
</nav>

<div id="mobile-menu" class="fixed inset-0 bg-black z-[150] md:hidden flex flex-col items-center justify-center space-y-8">
    <button onclick="toggleMobile()" class="absolute top-8 right-8 text-white"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg></button>
    <a href="index.php" onclick="toggleMobile()" class="nav-link nav-link-white text-2xl">Home</a>
    <a href="zwaaikamer.php" onclick="toggleMobile()" class="nav-link nav-link-white text-2xl italic">Zwaaikamer</a>
    <button onclick="toggleMobile(); toggleExplorer();" class="nav-link nav-link-zinc text-2xl">Verkenner</button>
    <a href="login.php" onclick="toggleMobile()" class="nav-link nav-link-zinc text-2xl">Toegang</a>
</div>

<aside id="explorer-panel" class="explorer-panel fixed top-0 right-0 bottom-0 w-full max-w-xs border-l border-white/5 p-12 overflow-y-auto">
    <header class="mb-12 flex justify-between items-center">
        <p class="text-[10px] font-black uppercase tracking-[0.3em] text-blue-600 italic">Menu</p>
        <button onclick="toggleExplorer()" class="text-zinc-600 hover:text-white"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>
    </header>
    <nav class="space-y-4"> <?php foreach ($visibleNav as $album): ?>
            <a href="gallery.php?page=<?= rawurlencode($album['category_name']) ?>" class="group block border-b border-white/5 pb-3">
                <span class="serif-italic text-lg group-hover:text-blue-500 transition-all block italic"><?= ucfirst($album['category_name']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
</aside>

<script>
    function toggleExplorer() { document.body.classList.toggle('explorer-open'); }
    function toggleMobile() { document.body.classList.toggle('mobile-open'); }
    window.addEventListener('scroll', () => {
        const n = document.getElementById('main-nav');
        if (window.scrollY > 20) n.classList.add('glass-nav');
        else n.classList.remove('glass-nav');
    });
</script>