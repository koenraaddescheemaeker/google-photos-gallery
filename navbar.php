<nav class="fixed top-0 left-0 w-full z-50 bg-black/80 backdrop-blur-md border-b border-white/10 text-white">
    <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
        
        <div class="flex items-center space-x-4">
            <a href="index.php" class="text-white font-bold tracking-tighter text-xl uppercase">
                FORCEKES <span class="text-white/40 font-light">2026</span>
            </a>
        </div>

        <div class="hidden md:flex items-center space-x-10">
            <a href="index.php" class="nav-link">HOME</a>
            <a href="zwaaikamer.php" class="nav-link">ZWAAIKAMER</a>
            <a href="gallery.php?cat=het%20museum" class="nav-link text-amber-200/80">MUSEUM</a>
            <a href="handleiding.php" class="nav-link">HANDLEIDING</a>
            <a href="gallery.php?view=albums" class="nav-link">ALBUMS</a>
        </div>

        <div class="md:hidden">
            <button class="text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                </svg>
            </button>
        </div>
    </div>
</nav>

<style>
    .nav-link {
        @apply text-white/70 text-xs font-semibold tracking-widest uppercase hover:text-white transition-colors duration-300;
    }
</style>