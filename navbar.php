<?php
require_once 'config.php';
$navItems = $db->query("SELECT * FROM navigation ORDER BY sort_order ASC")->fetchAll();
?>
<nav class="fixed top-0 w-full h-20 bg-black/90 backdrop-blur-md border-b border-white/10 z-50 flex items-center justify-between px-8 text-white">
    <a href="index.php" class="font-bold tracking-tighter italic text-2xl">FORCEKES <span class="opacity-30 text-sm">2026</span></a>
    <div class="flex gap-8 text-[10px] tracking-widest font-bold uppercase">
        <?php foreach ($navItems as $item): ?>
            <a href="<?= htmlspecialchars($item['url']) ?>" class="hover:text-amber-400 transition-colors">
                <?= htmlspecialchars($item['label']) ?>
            </a>
        <?php endforeach; ?>
    </div>
</nav>
