<div id="forcekes-modal" class="fixed inset-0 z-[9999] bg-black hidden flex-col items-center justify-center opacity-0 transition-opacity duration-300">
    
    <div class="absolute top-0 left-0 right-0 p-6 flex justify-between items-center z-[10001]">
        <button id="modal-close" class="flex items-center space-x-3 bg-white/5 hover:bg-white/10 backdrop-blur-md border border-white/10 px-6 py-3 rounded-full transition-all group">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-zinc-400 group-hover:text-white">
                <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-400 group-hover:text-white">Sluiten</span>
        </button>
        
        <button id="forcekes-download-btn" class="bg-blue-600 hover:bg-blue-500 px-6 py-3 rounded-full text-[10px] font-black uppercase tracking-[0.2em] transition-all shadow-xl shadow-blue-600/20">
            Opslaan
        </button>
    </div>

    <button id="modal-prev" class="absolute left-4 md:left-10 top-1/2 -translate-y-1/2 z-[10001] p-4 bg-black/40 hover:bg-blue-600 rounded-full transition-all group border border-white/5">
        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" class="text-white">
            <path d="M15 18l-6-6 6-6"/>
        </svg>
    </button>

    <button id="modal-next" class="absolute right-4 md:right-10 top-1/2 -translate-y-1/2 z-[10001] p-4 bg-black/40 hover:bg-blue-600 rounded-full transition-all group border border-white/5">
        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" class="text-white">
            <path d="M9 18l6-6-6-6"/>
        </svg>
    </button>

    <div id="modal-content" class="w-full h-full flex items-center justify-center p-4 md:p-20">
        <img id="modal-img" class="max-w-full max-h-full object-contain rounded-lg shadow-2xl hidden shadow-black/50">
        <video id="modal-video" class="max-w-full max-h-full rounded-lg hidden" controls autoplay loop playsinline></video>
    </div>

    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 text-[9px] font-black uppercase tracking-[0.4em] text-zinc-600">
        <?= $displayName ?> &middot; <span id="modal-counter">0 / 0</span>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('forcekes-modal');
    const modalImg = document.getElementById('modal-img');
    const modalVideo = document.getElementById('modal-video');
    const modalCounter = document.getElementById('modal-counter');
    const galleryItems = document.querySelectorAll('.gallery-item');
    let currentIndex = 0;

    function openModal(index) {
        const item = galleryItems[index];
        if (!item) return;

        currentIndex = index;
        const url = item.href;
        const type = item.getAttribute('data-type');

        // Reset
        modalImg.classList.add('hidden');
        modalVideo.classList.add('hidden');
        modalVideo.pause();
        modalVideo.src = "";

        if (type === 'video') {
            modalVideo.src = url;
            modalVideo.classList.remove('hidden');
        } else {
            modalImg.src = url;
            modalImg.classList.remove('hidden');
        }

        // Update Counter
        modalCounter.innerText = `${currentIndex + 1} / ${galleryItems.length}`;

        // Show Modal with animation
        modal.classList.remove('hidden');
        setTimeout(() => modal.classList.remove('opacity-0'), 10);
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.classList.add('opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            modalVideo.pause();
            modalVideo.src = "";
        }, 300);
        document.body.style.overflow = '';
    }

    function navigate(direction) {
        currentIndex = (currentIndex + direction + galleryItems.length) % galleryItems.length;
        openModal(currentIndex);
    }

    // Event Listeners
    galleryItems.forEach((item, index) => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            openModal(index);
        });
    });

    document.getElementById('modal-close').onclick = closeModal;
    document.getElementById('modal-prev').onclick = (e) => { e.stopPropagation(); navigate(-1); };
    document.getElementById('modal-next').onclick = (e) => { e.stopPropagation(); navigate(1); };
    
    document.getElementById('forcekes-download-btn').onclick = () => {
        const currentUrl = galleryItems[currentIndex].href;
        window.location.href = 'download.php?file=' + encodeURIComponent(currentUrl);
    };

    // Keyboard support
    document.addEventListener('keydown', (e) => {
        if (modal.classList.contains('hidden')) return;
        if (e.key === 'Escape') closeModal();
        if (e.key === 'ArrowLeft') navigate(-1);
        if (e.key === 'ArrowRight') navigate(1);
    });

    // Close on background click
    modal.onclick = (e) => {
        if (e.target === modal || e.target.id === 'modal-content') closeModal();
    };
});
</script>