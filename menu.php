<?php
/** * FORCEKES - menu.php (Admin Aware) */
$isAdmin = (isset($_SESSION['user_email']) && $_SESSION['user_email'] === 'koen@lauwe.com');
?>
<nav class="fixed top-0 left-0 right-0 z-[5000] bg-black/60 backdrop-blur-xl border-b border-white/5">
    <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
        
        <a href="index.php" class="flex items-center space-x-3 group">
            <span class="text-white font-black italic uppercase tracking-tighter text-xl">
                FORCEKES<span class="text-blue-600">PORTAAL</span>
            </span>
        </a>

        <div class="hidden md:flex items-center space-x-8">
            <div class="relative group">
                <button class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-400 group-hover:text-white transition flex items-center">
                    Verkennen <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div class="absolute top-full right-0 mt-2 w-48 bg-zinc-900 border border-white/5 rounded-2xl p-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300">
                    <a href="gallery.php?page=museum" class="block px-4 py-2 text-xs font-bold text-zinc-400 hover:text-white hover:bg-white/5 rounded-xl transition">Het Museum</a>
                    <a href="gallery.php?page=joris" class="block px-4 py-2 text-xs font-bold text-zinc-400 hover:text-white hover:bg-white/5 rounded-xl transition">Joris</a>
                </div>
            </div>

            <a href="zwaaikamer.php" class="p-2 bg-zinc-900 rounded-full hover:bg-blue-600 transition relative">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 7l-7 5 7 5V7z"></path><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg>
                <span class="absolute top-0 right-0 w-2 h-2 bg-blue-500 rounded-full animate-ping"></span>
            </a>

            <?php if ($isAdmin): ?>
                <a href="admin.php" class="flex items-center space-x-2 px-4 py-2 bg-zinc-800 border border-blue-600/30 rounded-full hover:bg-blue-600 transition">
                    <span class="text-[10px] font-black uppercase tracking-widest text-white">Beheer</span>
                </a>
            <?php endif; ?>

            <a href="auth-handler.php?action=logout" class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-600 hover:text-red-500 transition">Logout</a>
        </div>
    </div>
</nav>