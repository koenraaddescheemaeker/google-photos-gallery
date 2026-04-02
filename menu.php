<?php
/** * FORCEKES - menu.php (Fase 23: Megamenu Edition) */
require_once 'config.php';
$userEmail = $_SESSION['user_email'] ?? '';
$isAdmin = ($userEmail === 'koen@lauwe.com');

$raw = supabaseRequest("rpc/get_album_dashboard", 'GET');
$albums = (is_array($raw) && !isset($raw['error'])) ? $raw : [];

// Megamenu Groepering
$megamenu = [];
foreach ($albums as $a) {
    if (($a['is_visible'] ?? true) == false) continue;
    $parent = !empty($a['parent_category']) ? ucfirst($a['parent_category']) : 'Overig';
    $megamenu[$parent][] = $a;
}
ksort($megamenu);

$bezoekLeden = supabaseRequest("members?select=nickname,email&nickname=not.is.null", 'GET');
$bezoekLeden = is_array($bezoekLeden) ? $bezoekLeden : [];
?>
<style>
    .glass-nav { background: rgba(0, 0, 0, 0.9); backdrop-filter: blur(25px); border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
    .nav-link { font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.15em; color: #fff; transition: 0.3s; }
    .nav-link:hover { color: #3b82f6; }
    
    .dropdown { position: relative; }
    .megamenu { display: none; position: absolute; top: 100%; left: 50%; transform: translateX(-50%); padding-top: 25px; z-index: 500; }
    .dropdown:hover .megamenu { display: block; }
    
    .megamenu-content { 
        background: #000; border: 1px solid rgba(255,255,255,0.1); border-radius: 2.5rem; padding: 3rem; 
        min-width: 800px; display: grid; grid-template-columns: repeat(4, 1fr); gap: 3rem; box-shadow: 0 40px 100px rgba(0,0,0,0.9);
    }
    
    #mobile-overlay { transform: translateY(-100%); transition: 0.5s cubic-bezier(0.2, 1, 0.3, 1); background: #000; }
    .mobile-open #mobile-overlay { transform: translateY(0); }
</style>

<nav class="fixed top-0 left-0 right-0 z-[200] glass-nav" id="main-nav">
    <div class="max-w-7xl mx-auto px-6 py-6 flex justify-between items-center">
        <a href="index.php" class="text-xl font-black tracking-tighter italic">Forcekes<span class="text-blue-600">.</span></a>

        <div class="hidden md:flex items-center space-x-10">
            <a href="index.php" class="nav-link">Home</a>

            <div class="dropdown">
                <button class="nav-link flex items-center gap-2">Collectie <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M6 9l6 6 6-6"/></svg></button>
                <div class="megamenu">
                    <div class="megamenu-content">
                        <?php foreach ($megamenu as $parent => $items): ?>
                            <div>
                                <h3 class="text-blue-600 font-black text-[10px] uppercase tracking-[0.4em] mb-6 border-b border-white/5 pb-2"><?= $parent ?></h3>
                                <div class="space-y-3">
                                    <?php foreach ($items as $item): ?>
                                        <a href="gallery.php?page=<?= rawurlencode($item['category_name']) ?>" class="block text-[12px] text-zinc-500 hover:text-white transition">
                                            <?= ucfirst($item['category_name']) ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <a href="zwaaikamer.php" class="nav-link italic">Zwaaikamer</a>
            <a href="handleiding.php" class="nav-link text-zinc-600">Gids</a>
            <?php if ($isAdmin): ?> <a href="admin.php" class="nav-link text-blue-500/50">Beheer</a> <?php endif; ?>
            <a href="profiel.php" class="px-6 py-2 bg-white text-black rounded-full text-[10px] font-black uppercase">Profiel</a>
        </div>

        <button onclick="toggleMobile()" class="md:hidden text-white"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg></button>
    </div>
</nav>

<div id="mobile-overlay" class="fixed inset-0 z-[190] md:hidden flex flex-col pt-32 px-10 overflow-y-auto">
    <nav class="flex flex-col space-y-8">
        <?php foreach ($megamenu as $parent => $items): ?>
            <div>
                <p class="text-[10px] font-black text-blue-600 uppercase tracking-widest mb-4"><?= $parent ?></p>
                <div class="grid grid-cols-1 gap-4">
                    <?php foreach ($items as $item): ?>
                        <a href="gallery.php?page=<?= rawurlencode($item['category_name']) ?>" onclick="toggleMobile()" class="text-2xl font-bold italic"><?= ucfirst($item['category_name']) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </nav>
</div>

<script> function toggleMobile() { document.body.classList.toggle('mobile-open'); } </script>