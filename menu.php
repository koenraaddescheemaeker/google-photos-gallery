<?php
/** * FORCEKES - menu.php (Megamenu Multi-level Edition) */
require_once 'config.php';
$userEmail = $_SESSION['user_email'] ?? '';
$isLoggedIn = !empty($userEmail);
$isAdmin = ($userEmail === 'koen@lauwe.com');

// Data voor Feesten (Albums)
$feestenRaw = supabaseRequest("rpc/get_album_dashboard", 'GET');
$feesten = is_array($feestenRaw) ? array_filter($feestenRaw, fn($a) => ($a['is_visible'] ?? true)) : [];
usort($feesten, fn($a, $b) => ($a['priority'] ?? 999) <=> ($b['priority'] ?? 999));

// Data voor Familie (Leden met roepnaam)
$familie = supabaseRequest("members?is_approved=eq.true&nickname=not.is.null", 'GET');
$familie = is_array($familie) ? $familie : [];
?>
<style>
    .glass-nav { background: rgba(0, 0, 0, 0.8); backdrop-filter: blur(20px); border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
    .nav-link { font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.2em; color: #fff; transition: 0.4s; }
    .nav-link:hover { color: #3b82f6; }
    
    /* Sidebar Layout */
    .explorer-panel { transform: translateX(100%); transition: all 0.6s cubic-bezier(0.2, 1, 0.3, 1); background: #020202; z-index: 1000; width: 400px; max-width: 90vw; }
    .explorer-open .explorer-panel { transform: translateX(0); }
    .menu-level { transition: all 0.5s ease; position: absolute; inset: 0; padding: 3rem; visibility: hidden; opacity: 0; }
    .menu-level.active { visibility: visible; opacity: 1; transform: translateX(0); }
    .menu-level.hidden-left { transform: translateX(-20%); visibility: hidden; opacity: 0; }
    .menu-level.hidden-right { transform: translateX(100%); visibility: hidden; opacity: 0; }
    
    .menu-item { display: block; border-bottom: 1px solid rgba(255,255,255,0.05); padding: 1.5rem 0; transition: 0.3s; }
    .menu-item:hover { padding-left: 10px; color: #3b82f6; }
</style>

<nav class="fixed top-0 left-0 right-0 z-[100] glass-nav">
    <div class="max-w-7xl mx-auto px-8 py-6 flex justify-between items-center">
        <a href="index.php" class="text-lg font-black tracking-tighter text-white">
            Force<span class="text-blue-600">kes</span> <span class="opacity-60">Portaal</span>
        </a>
        <div class="hidden md:flex items-center space-x-10">
            <a href="index.php" class="nav-link">Home</a>
            <a href="zwaaikamer.php" class="nav-link italic">Zwaaikamer</a>
            <button onclick="openMenu('main')" class="nav-link text-blue-500">Menu</button>
            <?php if ($isLoggedIn): ?>
                <a href="profiel.php" class="nav-link text-zinc-500">Mijn Cockpit</a>
            <?php else: ?>
                <a href="login.php" class="px-6 py-2 bg-white text-black rounded-full text-[11px] font-black uppercase">Toegang</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<aside id="explorer-panel" class="explorer-panel fixed top-0 right-0 bottom-0 overflow-hidden shadow-2xl border-l border-white/5">
    <button onclick="closeMenu()" class="absolute top-8 right-8 z-[1100] text-zinc-500 hover:text-white"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg></button>

    <div id="level-main" class="menu-level active">
        <p class="text-[10px] font-black uppercase tracking-[0.4em] text-blue-600 mb-12">Navigatie</p>
        <nav class="space-y-2">
            <a href="index.php" class="menu-item serif-italic text-3xl italic">Home</a>
            <a href="zwaaikamer.php" class="menu-item serif-italic text-3xl italic">Zwaaikamer</a>
            <button onclick="openMenu('feesten')" class="menu-item w-full text-left serif-italic text-3xl italic flex justify-between items-center">Feesten <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg></button>
            <button onclick="openMenu('familie')" class="menu-item w-full text-left serif-italic text-3xl italic flex justify-between items-center">Familie <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg></button>
            <a href="handleiding.php" class="menu-item serif-italic text-3xl italic">Handleiding</a>
            <a href="login.php" class="menu-item serif-italic text-3xl italic text-zinc-500"><?= $isLoggedIn ? 'Logout' : 'Toegang' ?></a>
        </nav>
    </div>

    <div id="level-feesten" class="menu-level hidden-right">
        <button onclick="openMenu('main')" class="text-[10px] font-black uppercase tracking-widest text-zinc-500 flex items-center gap-2 mb-12"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M19 12H5M12 19l-7-7 7-7"/></svg> Terug</p>
        <p class="text-[10px] font-black uppercase tracking-[0.4em] text-blue-600 mb-8">Feesten</p>
        <div class="space-y-4">
            <?php foreach($feesten as $f): ?>
                <a href="gallery.php?page=<?= rawurlencode($f['category_name']) ?>" class="block group">
                    <span class="text-[9px] font-black text-zinc-600 block"><?= $f['photo_count'] ?> Momenten</span>
                    <span class="serif-italic text-2xl italic group-hover:text-blue-500 transition-colors"><?= ucfirst($f['category_name']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div id="level-familie" class="menu-level hidden-right">
        <button onclick="openMenu('main')" class="text-[10px] font-black uppercase tracking-widest text-zinc-500 flex items-center gap-2 mb-12"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M19 12H5M12 19l-7-7 7-7"/></svg> Terug</p>
        <p class="text-[10px] font-black uppercase tracking-[0.4em] text-blue-600 mb-8">Familieleden</p>
        <div class="space-y-6">
            <?php foreach($familie as $f): ?>
                <a href="bezoek.php?user=<?= rawurlencode($f['email']) ?>" class="serif-italic text-3xl italic block hover:text-blue-500 transition-colors"><?= htmlspecialchars($f['nickname']) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</aside>

<script>
    function openMenu(level) {
        document.body.classList.add('explorer-open');
        document.querySelectorAll('.menu-level').forEach(el => {
            el.classList.remove('active', 'hidden-left', 'hidden-right');
            el.classList.add('hidden-right');
        });
        const current = document.getElementById('level-' + level);
        current.classList.remove('hidden-right');
        current.classList.add('active');
        
        if(level !== 'main') document.getElementById('level-main').classList.add('hidden-left');
    }
    function closeMenu() { document.body.classList.remove('explorer-open'); }
</script>