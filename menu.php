<?php
/** * FORCEKES - menu.php (Fase 11: Perfect Sync) */
require_once 'config.php';

$userEmail = isset($_SESSION['user_email']) ? strtolower($_SESSION['user_email']) : '';
$isLoggedIn = !empty($userEmail);
$isAdmin = ($userEmail === 'koen@lauwe.com');

$navRaw = supabaseRequest("rpc/get_album_dashboard", 'GET');
$navAlbums = (is_array($navRaw) && !isset($navRaw['error'])) ? $navRaw : [];

// Alleen zichtbare albums sorteren voor de verkenner
$visibleNav = array_filter($navAlbums, function($a) { return ($a['is_visible'] ?? true) == true; });
usort($visibleNav, function($a, $b) {
    $pA = $a['priority'] ?? 999; $pB = $b['priority'] ?? 999;
    if ($pA !== $pB) return $pA <=> $pB;
    return strcmp((string)($a['category_name'] ?? ''), (string)($b['category_name'] ?? ''));
});
?>
<style>
    .glass-nav { background: rgba(0, 0, 0, 0.5); backdrop-filter: blur(25px); border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
    .nav-link { font-size: 9px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.3em; color: rgba(255,255,255,0.4); transition: all 0.4s; }
    .nav-link:hover { color: #fff; letter-spacing: 0.4em; }
    .explorer-panel { transform: translateX(100%); transition: transform 0.8s cubic-bezier(0.2, 1, 0.3, 1); background: #020202; z-index: 1000; }
    .explorer-open .explorer-panel { transform: translateX(0); }
</style>

<nav class="fixed top-0 left-0 right-0 z-[100] transition-all duration-700" id="main-nav">
    <div class="max-w-7xl mx-auto px-10 py-8 flex justify-between items-center">
        <a href="index.php" class="group flex flex-col">
            <span class="text-xl font-black italic uppercase tracking-tighter leading-none text-white">Force<span class="text-blue-600">kes</span></span>
            <span class="text-[8px] font-black uppercase tracking-[0.5em] text-zinc-600 group-hover:text-blue-500 transition-colors">Portaal</span>
        </a>
        <div class="hidden md:flex items-center space-x-12">
            <a href="index.php" class="nav-link">Home</a>
            <a href="zwaaikamer.php" class="nav-link italic">Zwaaikamer</a>
            <button onclick="toggleExplorer()" class="nav-link">Verkenner</button>
            <?php if ($isAdmin): ?> <a href="admin.php" class="nav-link text-blue-500">Beheer</a> <?php endif; ?>
            <a href="<?= $isLoggedIn ? 'logout.php' : 'login.php' ?>" class="<?= $isLoggedIn ? 'nav-link' : 'px-8 py-3 bg-white text-black rounded-full text-[9px] font-black uppercase tracking-[0.3em]' ?>">
                <?= $isLoggedIn ? 'Logout' : 'Toegang' ?>
            </a>
        </div>
    </div>
</nav>

<aside id="explorer-panel" class="explorer-panel fixed top-0 right-0 bottom-0 w-full max-w-sm border-l border-white/5 p-16 overflow-y-auto">
    <header class="mb-20 flex justify-between items-center">
        <p class="text-[9px] font-black uppercase tracking-[0.4em] text-blue-600 italic">Archief</p>
        <button onclick="toggleExplorer()" class="text-zinc-600 hover:text-white"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>
    </header>
    <nav class="space-y-10">
        <?php foreach ($visibleNav as $album): ?>
            <a href="gallery.php?page=<?= rawurlencode($album['category_name']) ?>" class="group block border-b border-white/5 pb-6">
                <span class="text-[8px] font-black text-zinc-600 uppercase tracking-widest block mb-2"><?= (int)$album['photo_count'] ?> items</span>
                <span class="serif-italic text-2xl group-hover:text-blue-500 transition-all block italic"><?= ucfirst($album['category_name']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
</aside>

<script>
    function toggleExplorer() { document.body.classList.toggle('explorer-open'); }
    window.addEventListener('scroll', () => {
        const n = document.getElementById('main-nav');
        if (window.scrollY > 20) n.classList.add('glass-nav', 'py-4');
        else n.classList.remove('glass-nav', 'py-4');
    });
</script>