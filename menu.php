<nav class="fixed top-0 left-0 right-0 z-[100] px-6 py-6 transition-all duration-500" id="main-nav">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <a href="index.php" class="text-2xl font-black italic tracking-tighter uppercase group">
            Force<span class="text-blue-600 group-hover:text-white transition-colors">kes</span>
        </a>

        <div class="hidden md:flex items-center space-x-10">
            <a href="index.php" class="text-[10px] font-black uppercase tracking-[0.3em] hover:text-blue-500 transition">Home</a>
            <a href="zwaaikamer.php" class="text-[10px] font-black uppercase tracking-[0.3em] hover:text-blue-500 transition italic">Zwaaikamer</a>
            <a href="admin.php" class="px-5 py-2 bg-white text-black rounded-full text-[10px] font-black uppercase tracking-widest hover:bg-blue-600 transition">Beheer</a>
        </div>

        <button id="menu-toggle" class="md:hidden p-2 text-white focus:outline-none">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>
    </div>

    <div id="mobile-menu" class="fixed inset-0 bg-black/98 backdrop-blur-2xl translate-x-full transition-transform duration-500 md:hidden flex flex-col items-center justify-center space-y-10 z-[110]">
        <button id="menu-close" class="absolute top-8 right-8 p-2 text-white/50 hover:text-white">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
        <a href="index.php" class="mobile-link text-3xl font-black uppercase tracking-widest italic">Home</a>
        <a href="zwaaikamer.php" class="mobile-link text-3xl font-black uppercase tracking-widest italic">Zwaaikamer</a>
        <a href="admin.php" class="mobile-link text-3xl font-black uppercase tracking-widest italic text-blue-600">Beheer</a>
    </div>
</nav>

<script>
    const menuToggle = document.getElementById('menu-toggle');
    const menuClose = document.getElementById('menu-close');
    const mobileMenu = document.getElementById('mobile-menu');
    const nav = document.getElementById('main-nav');

    menuToggle.addEventListener('click', () => { 
        mobileMenu.classList.remove('translate-x-full'); 
        document.body.style.overflow = 'hidden'; 
    });

    const closeDrawer = () => { 
        mobileMenu.classList.add('translate-x-full'); 
        document.body.style.overflow = ''; 
    };

    menuClose.addEventListener('click', closeDrawer);
    document.querySelectorAll('.mobile-link').forEach(link => link.addEventListener('click', closeDrawer));

    window.addEventListener('scroll', () => {
        if (window.scrollY > 20) { nav.classList.add('bg-black/80', 'backdrop-blur-md', 'py-4'); nav.classList.remove('py-6'); }
        else { nav.classList.remove('bg-black/80', 'backdrop-blur-md', 'py-4'); nav.classList.add('py-6'); }
    });
</script>