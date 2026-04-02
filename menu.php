<?php
/** * FORCEKES - menu.php (Architect Edition) */
require_once 'config.php';
$userEmail = $_SESSION['user_email'] ?? '';
$isLoggedIn = !empty($userEmail);
$isAdmin = ($userEmail === 'koen@lauwe.com');

// Data ophalen voor de menu-niveaus
$feesten = supabaseRequest("rpc/get_album_dashboard", 'GET'); // Toont alle albums/feesten
$familie = supabaseRequest("members?is_approved=eq.true&nickname=not.is.null", 'GET');
?>
<style>
    .glass-nav { background: rgba(0, 0, 0, 0.8); backdrop-filter: blur(20px); border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
    .nav-link { font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.2em; color: #fff; transition: 0.4s; }
    
    /* Rechter Zijbalk */
    .menu-panel { transform: translateX(100%); transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1); background: #020202; z-index: 1000; width: 400px; max-width: 85vw; border-l: 1px solid rgba(255,255,255,0.1); }
    .menu-open .menu-panel { transform: translateX(0); }
    
    .menu-level { position: absolute; inset: 0; padding: 4rem 3rem; transition: 0.4s; opacity: 0; visibility: hidden; }
    .menu-level.active { opacity: 1; visibility: visible; transform: translateX(0); }
    .menu-level.push-left { transform: translateX(-20%); opacity: 0; visibility: hidden; }
    .menu-level.push-right { transform: translateX(100%); opacity: 0; visibility: hidden; }

    .menu-item { display: flex; justify-content: space-between; align-items: center; width: 100%; border-bottom: 1px solid rgba(255,255,255,0.05); padding: 1.5rem 0; font-family: 'Playfair Display', serif; font-style: italic; font-size: 2rem; color: #fff; text-align: left; }
    .menu-item:hover { color: #3b82f6; padding-left: 10px; }
    .sub-label { font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.3em; color: #3b82f6; margin-bottom: 2rem; display: block; }
</style>

<nav class="fixed top-0 left-0 right-0 z-[100] glass-nav">
    <div class="max-w-7xl mx-auto px-8 py-6 flex justify-between items-center">
        <a href="index.php" class="text-lg font-black tracking-tighter text-white">
            Force<span class="text-blue-600">kes</span> Portaal
        </a>
        <div class="hidden md:flex items-center space-x-10">
            <a href="index.php" class="nav-link">HOME</a>
            <a href="zwaaikamer.php" class="nav-link italic">ZWAAIKAMER</a>
            <button onclick="toggleMenu(true)" class="nav-link text-blue-500">MENU</button>
            <?php if($isLoggedIn): ?>
                <a href="profiel.php" class="nav-link text-zinc-500">MIJN COCKPIT</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<aside id="menu-panel" class="menu-panel fixed top-0 right-0 bottom-0 overflow-hidden shadow-2xl">
    <button onclick="toggleMenu(false)" class="absolute top-8 right-8 z-[1100] text-zinc-500 hover:text-white"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg></button>

    <div id="level-home" class="menu-level active">
        <span class="sub-label">Hoofdmenu</span>
        <nav>
            <a href="index.php" class="menu-item">HOME</a>
            <a href="zwaaikamer.php" class="menu-item">ZWAAIKAMER</a>
            <button onclick="navTo('feesten')" class="menu-item">FEESTEN <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg></button>
            <button onclick="navTo('familie')" class="menu-item">FAMILIE <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg></button>
            <a href="handleiding.php" class="menu-item">HANDLEIDING</a>
            <a href="admin/login.php" class="menu-item text-zinc-600"><?= $isLoggedIn ? 'LOGOUT' : 'TOEGANG' ?></a>
        </nav>
    </div>

    <div id="level-feesten" class="menu-level push-right">
        <button onclick="navTo('home')" class="text-[10px] font-black text-zinc-600 uppercase mb-8 flex items-center gap-2 tracking-widest"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M19 12H5M12 19l-7-7 7-7"/></svg> TERUG</button>
        <span class="sub-label">FEESTEN</span>
        <div class="space-y-2 overflow-y-auto max-h-[70vh]">
            <?php foreach($feesten as $f): ?>
                <a href="gallery.php?page=<?= rawurlencode($f['category_name']) ?>" class="menu-item text-xl"><?= ucfirst($f['category_name']) ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <div id="level-familie" class="menu-level push-right">
        <button onclick="navTo('home')" class="text-[10px] font-black text-zinc-600 uppercase mb-8 flex items-center gap-2 tracking-widest"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M19 12H5M12 19l-7-7 7-7"/></svg> TERUG</button>
        <span class="sub-label">FAMILIE</span>
        <div class="space-y-2">
            <?php foreach($familie as $fam): ?>
                <a href="bezoek.php?user=<?= rawurlencode($fam['email']) ?>" class="menu-item text-xl"><?= htmlspecialchars($fam['nickname']) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</aside>

<script>
    function toggleMenu(open) { document.body.classList.toggle('menu-open', open); if(!open) navTo('home'); }
    function navTo(level) {
        document.querySelectorAll('.menu-level').forEach(el => {
            el.classList.remove('active', 'push-left', 'push-right');
            el.classList.add('push-right');
        });
        const target = document.getElementById('level-' + level);
        target.classList.remove('push-right');
        target.classList.add('active');
        if(level !== 'home') document.getElementById('level-home').classList.add('push-left');
    }
</script>