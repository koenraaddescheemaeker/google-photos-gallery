<?php
// navbar.php - De Dynamische Toegang
require_once 'config.php';

try {
    $navQuery = $db->query("SELECT * FROM navigation WHERE is_active = true ORDER BY order_num ASC");
    $navItems = $navQuery->fetchAll();
} catch (Exception $e) {
    $navItems = []; // Fallback als de tabel nog leeg is
}
?>
<nav class="fixed top-0 left-0 w-full z-50 bg-black/80 backdrop-blur-md border-b border-white/10 text-white">
    <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
        <a href="index.php" class="font-bold tracking-tighter text-xl uppercase italic">
            FORCEKES <span class="opacity-40 font-light">2026</span>
        </a>

        <div class="hidden md:flex items-center space-x-10 text-[10px] font-semibold tracking-[0.2em]">
            <?php foreach ($navItems as $item): ?>
                <a href="<?= htmlspecialchars($item['target_url']) ?>" class="hover:text-amber-200 transition-colors">
                    <?= htmlspecialchars($item['label']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</nav>