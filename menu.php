<nav class="fixed top-0 left-0 right-0 z-50 bg-black/70 backdrop-blur-2xl border-b border-white/5">
    <div class="max-w-7xl mx-auto px-6 h-24 flex items-center justify-between">
        <a href="index.php" class="text-2xl font-black italic uppercase tracking-tighter hover:text-blue-500 transition duration-300">
            FORCEKES <span class="text-blue-600">PORTAAL</span>
        </a>

        <div class="flex items-center gap-10">
            <div class="group relative py-8">
                <button class="text-[11px] font-black uppercase tracking-[0.25em] flex items-center gap-2 hover:text-blue-400 transition">
                    Verkennen <svg class="w-3 h-3 group-hover:rotate-180 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="m19 9-7 7-7-7"/></svg>
                </button>
                
                <div class="absolute top-full right-0 w-[420px] bg-zinc-900/95 border border-white/10 p-10 rounded-[3rem] shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-500 transform group-hover:translate-y-4 backdrop-blur-3xl">
                    <div class="grid grid-cols-2 gap-10">
                        <div>
                            <h3 class="text-[9px] font-black text-blue-500 uppercase tracking-widest mb-6">Sociaal</h3>
                            <a href="zwaaikamer.php" class="block text-sm font-bold hover:text-blue-400 transition mb-3">Zwaaikamer</a>
                        </div>
                        <div>
                            <h3 class="text-[9px] font-black text-blue-500 uppercase tracking-widest mb-6">Herinneringen</h3>
                            <a href="gallery.php?page=museum" class="block text-sm font-bold hover:text-blue-400 mb-3 transition">Museum</a>
                            <a href="gallery.php?page=joris" class="block text-sm font-bold hover:text-blue-400 mb-3 transition">Joris</a>
                        </div>
                    </div>
                </div>
            </div>

            <a href="admin.php" class="p-3 bg-zinc-800/50 rounded-2xl hover:bg-blue-600 transition duration-300 group">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="text-zinc-400 group-hover:text-white" viewBox="0 0 256 256"><path d="M128,80a48,48,0,1,0,48,48A48.05,48.05,0,0,0,128,80Zm0,80a32,32,0,1,1,32-32A32,32,0,0,1,128,160ZM233.37,103.36l-20.81-12a12,12,0,0,0-15,3.64l-7.2,10.64a92.27,92.27,0,0,0-24.72-14.27l-2-12.65A12,12,0,0,0,151.81,68H104.19a12,12,0,0,0-11.83,10.12l-2,12.65a92.27,92.27,0,0,0-24.72,14.27l-7.2-10.64a12,12,0,0,0-15-3.64l-20.81,12a12,12,0,0,0-4.37,16.4l10.37,17.41a92.4,92.4,0,0,0,0,28.56l-10.37,17.41a12,12,0,0,0,4.37,16.4l20.81,12a12,12,0,0,0,15-3.64l7.2-10.64a92.27,92.27,0,0,0,24.72,14.27l2,12.65A12,12,0,0,0,104.19,188h47.62a12,12,0,0,0,11.83-10.12l2-12.65a92.27,92.27,0,0,0,24.72-14.27l7.2,10.64a12,12,0,0,0,15,3.64l20.81-12a12,12,0,0,0,4.37-16.4l-10.37-17.41a92.4,92.4,0,0,0,0-28.56l10.37-17.41A12,12,0,0,0,233.37,103.36Z"></path></svg>
            </a>
            
            <a href="auth-handler.php?action=logout" class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-500 hover:text-red-500 transition">Exit</a>
        </div>
    </div>
</nav>
<div class="h-24"></div>