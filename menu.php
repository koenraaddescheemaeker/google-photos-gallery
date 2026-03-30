<?php
/** * FORCEKES - menu.php (Fase 9: Ambient & Sound Engine) */
require_once 'config.php';
$userEmail = isset($_SESSION['user_email']) ? strtolower($_SESSION['user_email']) : '';
$isLoggedIn = !empty($userEmail);
$isAdmin = ($userEmail === 'koen@lauwe.com');

$navAlbumsRaw = supabaseRequest("rpc/get_album_dashboard", 'GET');
$navAlbums = (is_array($navAlbumsRaw) && !isset($navAlbumsRaw['error'])) ? $navAlbumsRaw : [];
?>
<style>
    /* Ambient Glow Layer */
    #ambient-glow {
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background: radial-gradient(circle at 50% 50%, rgba(59, 130, 246, 0.05) 0%, transparent 70%);
        pointer-events: none;
        z-index: -1;
        transition: background 2s ease;
    }
    .glass-nav { background: rgba(0, 0, 0, 0.4); backdrop-filter: blur(30px); border-bottom: 1px solid rgba(255, 255, 255, 0.03); }
    .nav-link { font-size: 9px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.3em; transition: all 0.4s ease; color: rgba(255,255,255,0.4); }
    .nav-link:hover { color: #fff; letter-spacing: 0.4em; }
    .explorer-panel { transform: translateX(100%); transition: transform 0.8s cubic-bezier(0.2, 1, 0.3, 1); background: #020202; }
    .explorer-open .explorer-panel { transform: translateX(0); }
</style>

<div id="ambient-glow"></div>

<nav class="fixed top-0 left-0 right-0 z-[100] transition-all duration-700" id="main-nav">
    <div class="max-w-7xl mx-auto px-10 py-8 flex justify-between items-center">
        <a href="index.php" class="group flex flex-col" onclick="playSound('click')">
            <span class="text-xl font-black italic uppercase tracking-tighter leading-none">Force<span class="text-blue-600">kes</span></span>
            <span class="text-[8px] font-black uppercase tracking-[0.5em] text-zinc-600 group-hover:text-blue-500 transition-colors">Portaal</span>
        </a>

        <div class="hidden md:flex items-center space-x-12">
            <a href="index.php" class="nav-link" onclick="playSound('click')">Home</a>
            <a href="zwaaikamer.php" class="nav-link italic" onclick="playSound('click')">Zwaaikamer</a>
            <button onclick="toggleExplorer(); playSound('ui-open');" class="nav-link flex items-center gap-2">Verkenner</button>
            
            <?php if ($isLoggedIn): ?>
                <a href="logout.php" class="nav-link text-red-900/50 hover:text-red-500" onclick="playSound('click')">Exit</a>
            <?php else: ?>
                <a href="login.php" class="px-8 py-3 bg-white text-black rounded-full text-[9px] font-black uppercase tracking-[0.3em] hover:bg-blue-600 hover:text-white transition-all">Toegang</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<aside id="explorer-panel" class="explorer-panel fixed top-0 right-0 bottom-0 w-full max-w-sm border-l border-white/5 z-[120] p-16 overflow-y-auto shadow-2xl">
    <header class="mb-20 flex justify-between items-center">
        <p class="text-[9px] font-black uppercase tracking-[0.4em] text-blue-600">Archief</p>
        <button onclick="toggleExplorer(); playSound('ui-close');" class="text-zinc-600 hover:text-white"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>
    </header>
    <nav class="space-y-10">
        <?php foreach ($navAlbums as $album): ?>
            <a href="gallery.php?page=<?= rawurlencode($album['category_name']) ?>" class="group block border-b border-white/5 pb-6" onclick="playSound('click')">
                <span class="text-[20px] font-black uppercase italic group-hover:text-blue-500 transition-all block"><?= $album['category_name'] ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
</aside>

<script>
    const sounds = {
        'click': new Audio('https://assets.mixkit.co/active_storage/sfx/2571/2571-preview.mp3'), // Kristallen tik
        'ui-open': new Audio('https://assets.mixkit.co/active_storage/sfx/2568/2568-preview.mp3'), // Zachte woosh
        'ui-close': new Audio('https://assets.mixkit.co/active_storage/sfx/2569/2569-preview.mp3')
    };

    function playSound(name) {
        const s = sounds[name].cloneNode();
        s.volume = 0.2;
        s.play();
    }

    function toggleExplorer() { document.body.classList.toggle('explorer-open'); }

    // Ambient Mirror Logic
    function updateAmbientGlow(color = 'rgba(59, 130, 246, 0.05)') {
        document.getElementById('ambient-glow').style.background = `radial-gradient(circle at 50% 50%, ${color} 0%, transparent 70%)`;
    }

    window.addEventListener('scroll', () => {
        const n = document.getElementById('main-nav');
        if (window.scrollY > 20) n.classList.add('glass-nav', 'py-4');
        else n.classList.remove('glass-nav', 'py-4');
    });
</script>