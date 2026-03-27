<nav class="fixed top-0 left-0 right-0 z-50 bg-black/80 backdrop-blur-xl border-b border-white/5">
    <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
        <a href="index.php" class="text-xl font-black italic uppercase tracking-tighter hover:text-blue-500 transition">
            FORCEKES <span class="text-blue-600">PORTAAL</span>
        </a>

        <div class="flex items-center gap-10">
            <div class="group relative py-7">
                <button class="text-[11px] font-black uppercase tracking-[0.2em] flex items-center gap-2 hover:text-blue-400 transition">
                    Verkennen <svg class="w-3 h-3 group-hover:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="m19 9-7 7-7-7"/></svg>
                </button>
                
                <div class="absolute top-full right-0 w-[400px] bg-zinc-900 border border-white/10 p-8 rounded-[2rem] shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform group-hover:translate-y-2">
                    <div class="grid grid-cols-2 gap-8">
                        <div>
                            <h3 class="text-[9px] font-black text-blue-500 uppercase tracking-widest mb-4">Live</h3>
                            <a href="zwaaikamer.php" class="block group/item mb-2">
                                <span class="text-sm font-bold group-hover/item:text-blue-400">Zwaaikamer</span>
                            </a>
                        </div>
                        <div>
                            <h3 class="text-[9px] font-black text-blue-500 uppercase tracking-widest mb-4">Herinneringen</h3>
                            <a href="gallery.php?page=museum" class="block text-sm font-bold mb-2 hover:text-blue-400">Museum</a>
                            <a href="gallery.php?page=joris" class="block text-sm font-bold mb-2 hover:text-blue-400">Joris</a>
                        </div>
                    </div>
                </div>
            </div>

            <a href="admin.php" class="p-2 bg-zinc-800 rounded-full hover:bg-blue-600 transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 256 256"><path d="M128,80a48,48,0,1,0,48,48A48.05,48.05,0,0,0,128,80Zm0,80a32,32,0,1,1,32-32A32,32,0,0,1,128,160Z"></path></svg>
            </a>
        </div>
    </div>
</nav>
<div class="h-20"></div>