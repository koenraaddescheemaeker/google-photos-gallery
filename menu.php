<?php
/** * FORCEKES - menu.php (Fase 8: Verkenner & Portaal Edition) */
require_once 'config.php';

// Haal albums op voor de Verkenner (indien nog niet geladen in de hoofd-pagina)
$navAlbumsRaw = supabaseRequest("rpc/get_album_dashboard", 'GET');
$navAlbums = (is_array($navAlbumsRaw) && !isset($navAlbumsRaw['error'])) ? $navAlbumsRaw : [];
if (!empty($navAlbums)) {
    usort($navAlbums, function($a, $b) {
        return strcmp((string)($a['category_name'] ?? ''), (string)($b['category_name'] ?? ''));
    });
}
?>
<style>
    .glass-menu { background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(20px); border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
    .explorer-panel { transform: translateX(100%); transition: transform 0.6s cubic-bezier(0.2, 1, 0.3, 1); }
    .explorer-open .explorer-panel { transform: translateX(0); }
    .explorer-overlay { opacity: 0; pointer-events: none; transition: opacity 0.6s ease; }
    .explorer-open .explorer-overlay { opacity: 1; pointer-events: auto; }
    .nav-link { font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.2em; transition: all 0.3s ease; }
    .nav-link:hover { color: #3b82f6; letter-spacing: 0.3em; }
</style>

<nav class="fixed top-0 left-0 right-0 z-[100] transition-all duration-500" id="main-nav">
    <div class="max-w-7xl mx-auto px-8 py-6 flex justify-between items-center">
        <a href="index.php" class="text-xl font-black italic tracking-tighter uppercase group flex items-center gap-2">
            <span class="text-white">Force<span class="text-blue-600">kes</span></span>
            <span class="text-[10px] font-light tracking-[0.4em] text-zinc-500 ml-2 group-hover:text-white transition-colors">Portaal</span>
        </a>

        <div class="hidden md:flex items-center space-x-12">
            <a href="index.php" class="nav-link">Home</a>
            <button onclick="toggleExplorer()" class="nav-link flex items-center gap-2 group">
                Verkenner 
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" class="group-hover:translate-y-1 transition-transform">
                    <path d="M6 9l6 6 6-6"/>
                </svg>
            </button>
            <a href="zwaaikamer.php" class="nav-link italic">Zwaaikamer</a>
            <a href="admin.php" class="px-6 py-2 bg-white text-black rounded-full text-[9px] font-black uppercase tracking-[0.2em] hover:bg-blue-600 hover:text-white transition-all shadow-xl shadow-white/5">Beheer</a>
        </div>

        <button onclick="toggleMobileMenu()" class="md:hidden p-2 text-white">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>
    </div>
</nav>

<div id="explorer-overlay" onclick="toggleExplorer()" class="explorer-overlay fixed inset-0 bg-black/80 backdrop-blur-sm z-[110]"></div>
<aside id="explorer-panel" class="explorer-panel fixed top-0 right-0 bottom-0 w-full max-w-sm bg-zinc-950 border-l border-white/10 z-[120] p-12 overflow-y-auto">
    <div class="flex justify-between items-center mb-16">
        <h3 class="text-[10px] font-black uppercase tracking-[0.5em] text-blue-500 italic">De Albums</h3>
        <button onclick="toggleExplorer()" class="text-zinc-500 hover:text-white transition">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
    </div>

    <nav class="space-y-6">
        <?php foreach ($navAlbums as $album): 
            $slug = (string)($album['category_name'] ?? '');
            if (empty($slug)) continue;
        ?>
            <a href="gallery.php?page=<?= rawurlencode($slug) ?>" class="group block">
                <span class="text-[8px] font-black text-zinc-600 uppercase tracking-widest block mb-1"><?= (int)($album['photo_count'] ?? 0) ?> items</span>
                <span class="text-xl font-black uppercase tracking-tighter group-hover:text-blue-600 transition-colors italic"><?= strtoupper($slug) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
</aside>

<div id="mobile-menu" class="fixed inset-0 bg-black/98 backdrop-blur-3xl translate-x-full transition-transform duration-500 md:hidden flex flex-col items-center justify-center space-y-10 z-[130]">
    <button onclick="toggleMobileMenu()" class="absolute top-8 right-8 text-white/50"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>
    <a href="index.php" class="text-3xl font-black uppercase italic tracking-widest">Home</a>
    <button onclick="toggleExplorer(); toggleMobileMenu();" class="text-3xl font-black uppercase italic tracking-widest text-blue-600">Verkenner</button>
    <a href="zwaaikamer.php" class="text-3xl font-black uppercase italic tracking-widest">Zwaaikamer</a>
    <a href="admin.php" class="text-3xl font-black uppercase italic tracking-widest">Beheer</a>
</div>

<script>
    const nav = document.getElementById('main-nav');
    const body = document.body;

    function toggleExplorer() {
        body.classList.toggle('explorer-open');
    }

    function toggleMobileMenu() {
        document.getElementById('mobile-menu').classList.toggle('translate-x-full');
    }

    window.addEventListener('scroll', () => {
        if (window.scrollY > 20) nav.classList.add('glass-menu', 'py-4');
        else nav.classList.remove('glass-menu', 'py-4');
    });
</script>