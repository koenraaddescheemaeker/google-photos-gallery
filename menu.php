<?php
/** * FORCEKES - menu.php (Gekeurd door Manu - Mobile First Edition) */
require_once 'config.php';

$userEmail = $_SESSION['user_email'] ?? '';
$isLoggedIn = !empty($userEmail);
$isAdmin = ($userEmail === 'koen@lauwe.com');

// Data voor menu's
$navRaw = supabaseRequest("rpc/get_album_dashboard", 'GET');
$navAlbums = (is_array($navRaw) && !isset($navRaw['error'])) ? $navRaw : [];
$visibleNav = array_filter($navAlbums, fn($a) => ($a['is_visible'] ?? true) == true);
usort($visibleNav, fn($a, $b) => ($a['priority'] ?? 999) <=> ($b['priority'] ?? 999));

$bezoekLeden = supabaseRequest("members?select=nickname,email&nickname=not.is.null", 'GET');
$bezoekLeden = is_array($bezoekLeden) ? $bezoekLeden : [];
?>
<style>
    .glass-nav { background: rgba(0, 0, 0, 0.8); backdrop-filter: blur(20px); border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
    .nav-link { font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.15em; color: #fff; transition: all 0.3s; }
    .nav-link:hover { color: #3b82f6; }
    .nav-link-zinc { color: rgba(255,255,255,0.5); }
    
    /* Explorer & Mobile Panel Animation */
    .panel-hidden { transform: translateX(100%); transition: transform 0.6s cubic-bezier(0.2, 1, 0.3, 1); }
    .panel-visible { transform: translateX(0); }
    
    #mobile-overlay { transform: translateY(-100%); transition: transform 0.5s cubic-bezier(0.2, 1, 0.3, 1); }
    .mobile-open #mobile-overlay { transform: translateY(0); }

    /* Custom Dropdown for Desktop */
    .dropdown:hover .dropdown-menu { display: block; }
</style>

<nav class="fixed top-0 left-0 right-0 z-[200] transition-all duration-500 glass-nav" id="main-nav">
    <div class="max-w-7xl mx-auto px-6 py-5 flex justify-between items-center">
        <a href="index.php" class="text-lg font-black tracking-tighter z-[210]">
            <span class="text-white">Force</span><span class="text-blue-600">kes</span> <span class="text-white opacity-60">Portaal</span>
        </a>

        <div class="hidden md:flex items-center space-x-8">
            <a href="index.php" class="nav-link">Home</a>
            
            <div class="relative group dropdown">
                <button class="nav-link flex items-center gap-2">Bezoek <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M6 9l6 6 6-6"/></svg></button>
                <div class="dropdown-menu absolute hidden bg-black border border-white/10 rounded-2xl p-4 mt-2 min-w-[180px] shadow-2xl">
                    <?php foreach ($bezoekLeden as $lid): ?>
                        <a href="bezoek.php?user=<?= rawurlencode($lid['email']) ?>" class="block py-2 px-4 text-[11px] font-bold uppercase tracking-widest text-zinc-400 hover:text-blue-500">
                            <?= htmlspecialchars($lid['nickname']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <a href="zwaaikamer.php" class="nav-link italic">Zwaaikamer</a>
            <button onclick="toggleExplorer()" class="nav-link nav-link-zinc">Verkenner</button>
            
            <?php if ($isAdmin): ?>
                <a href="admin.php" class="nav-link text-blue-500/70 hover:text-blue-500">Beheer</a>
            <?php endif; ?>

            <a href="<?= $isLoggedIn ? 'profiel.php' : 'login.php' ?>" class="<?= $isLoggedIn ? 'nav-link nav-link-zinc' : 'px-5 py-2 bg-white text-black rounded-full text-[10px] font-black uppercase' ?>">
                <?= $isLoggedIn ? 'Mijn Profiel' : 'Toegang' ?>
            </a>
        </div>

        <button onclick="toggleMobile()" class="md:hidden z-[210] text-white p-2 focus:outline-none" id="hamburger-btn">
            <svg id="menu-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
            <svg id="close-icon" class="hidden" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>
</nav>

<div id="mobile-overlay" class="fixed inset-0 bg-black z-[190] md:hidden flex flex-col pt-32 px-10 overflow-y-auto">
    <nav class="flex flex-col space-y-8 pb-10">
        <a href="index.php" onclick="toggleMobile()" class="text-3xl font-black uppercase tracking-tighter">Home</a>
        
        <div>
            <p class="text-[10px] font-black text-blue-600 uppercase tracking-[0.4em] mb-4">Bezoek de familie</p>
            <div class="grid grid-cols-2 gap-4">
                <?php foreach ($bezoekLeden as $lid): ?>
                    <a href="bezoek.php?user=<?= rawurlencode($lid['email']) ?>" onclick="toggleMobile()" class="text-lg font-bold italic text-zinc-400">
                        <?= htmlspecialchars($lid['nickname']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <a href="zwaaikamer.php" onclick="toggleMobile()" class="text-3xl font-black uppercase tracking-tighter italic">Zwaaikamer</a>
        <button onclick="toggleMobile(); toggleExplorer();" class="text-left text-3xl font-black uppercase tracking-tighter text-zinc-600">Verkenner</button>
        
        <?php if ($isAdmin): ?>
            <a href="admin.php" onclick="toggleMobile()" class="text-3xl font-black uppercase tracking-tighter text-blue-500/50">Beheer</a>
        <?php endif; ?>

        <div class="pt-8 border-t border-white/5">
            <a href="<?= $isLoggedIn ? 'profiel.php' : 'login.php' ?>" onclick="toggleMobile()" class="text-xl font-black uppercase tracking-widest text-blue-500">
                <?= $isLoggedIn ? 'Mijn Profiel' : 'Login' ?>
            </a>
            <?php if($isLoggedIn): ?>
                <a href="logout.php" class="block mt-4 text-zinc-600 uppercase text-xs font-bold tracking-widest">Uitloggen</a>
            <?php endif; ?>
        </div>
    </nav>
</div>

<aside id="explorer-panel" class="panel-hidden fixed top-0 right-0 bottom-0 w-full max-w-xs border-l border-white/5 p-12 bg-[#020202] z-[300] overflow-y-auto">
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
    function toggleMobile() {
        const body = document.body;
        const menuIcon = document.getElementById('menu-icon');
        const closeIcon = document.getElementById('close-icon');
        
        body.classList.toggle('mobile-open');
        const isOpen = body.classList.contains('mobile-open');
        
        if(isOpen) {
            menuIcon.classList.add('hidden');
            closeIcon.classList.remove('hidden');
            body.style.overflow = 'hidden';
        } else {
            menuIcon.classList.remove('hidden');
            closeIcon.classList.add('hidden');
            body.style.overflow = '';
        }
    }

    function toggleExplorer() {
        const panel = document.getElementById('explorer-panel');
        panel.classList.toggle('panel-visible');
        panel.classList.toggle('panel-hidden');
    }

    // Scroll effect
    window.addEventListener('scroll', () => {
        const n = document.getElementById('main-nav');
        if (window.scrollY > 20) n.classList.add('py-3');
        else n.classList.remove('py-3');
    });
</script>