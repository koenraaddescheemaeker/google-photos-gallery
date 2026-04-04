<?php
// navbar.php - De dynamische toegangspoort
require_once 'config.php';

// Haal menu-items op uit de 'navigation' tabel (zoals gezien in je screenshot)
$navQuery = $db->query("SELECT * FROM navigation WHERE is_active = true ORDER BY order_num ASC");
$navItems = $navQuery->fetchAll();
?>
<nav class="fixed top-0 left-0 w-full z-50 bg-black/80 backdrop-blur-md border-b border-white/10">
    <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
        <a href="index.php" class="text-white font-bold tracking-tighter text-xl uppercase italic">
            FORCEKES <span class="text-white/40 font-light">2026</span>
        </a>

        <div class="hidden md:flex items-center space-x-8">
            <?php foreach ($navItems as $item): ?>
                <a href="<?= htmlspecialchars($item['target_url']) ?>" 
                   class="text-white/70 text-xs font-semibold tracking-widest uppercase hover:text-white transition-all">
                    <?= htmlspecialchars($item['label']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</nav>