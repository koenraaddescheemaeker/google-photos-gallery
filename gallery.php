<?php
/** * FORCEKES - gallery.php (Custom Modal v2.0 - Forced Download) */
require_once 'config.php';

$pageSlug = $_GET['page'] ?? 'museum';
$photos = supabaseRequest("album_photos?category=eq.$pageSlug&select=*&order=captured_at.desc", 'GET');
$displayName = ucfirst(htmlspecialchars($pageSlug));
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forcekes | <?= $displayName ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        
        body { margin: 0; padding: 0; font-family: 'Inter', sans-serif; background-color: #000; color: #fff; overflow-x: hidden; }

        /* Premium Modal v2.0 Styling */
        #forcekes-modal.hidden { display: none; }
        #forcekes-modal { position: fixed; inset: 0; z-index: 9999; display: flex; items: center; justify-center; }
        #modal-overlay { position: absolute; inset: 0; background-color: rgba(0, 0, 0, 0.98); backdrop-filter: blur(20px); }
        
        /* FIX: Content container centraal en maximaal */
        #modal-content { position: relative; z-index: 10000; width: 95%; height: 95%; display: flex; items: center; justify-center; pointer-events: none; }
        
        /* FIX: Media maxi-size en gecentreerd */
        .modal-media { max-width: 100%; max-height: 100%; object-fit: contain; box-shadow: 0 50px 100px -20px rgba(0, 0, 0, 0.8); pointer-events: auto; }
        .modal-media.hidden { display: none !important; }

        /* Modal Controls (Grote, blauwe, werkende knoppen) */
        .modal-btn { position: absolute; z-index: 10010; color: #3b82f6; cursor: pointer; background: none; border: none; padding: 10px; opacity: 0.8; transition: opacity 0.2s; }
        .modal-btn:hover { opacity: 1; }
        #modal-close { top: 20px; right: 20px; font-size: 3.5rem; font-weight: 100; line-height: 1; padding: 0 15px; }
        #modal-prev { left: 20px; top: 50%; transform: translateY(-50%); font-size: 4rem; }
        #modal-next { right: 20px; top: 50%; transform: translateY(-50%); font-size: 4rem; }
        #modal-prev svg, #modal-next svg { width: 32px; height: 32px; }

        /* Download Knop Overlay */
        #forcekes-download-btn {
            position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%); z-index: 10100;
            background: #3b82f6; color: white; border-radius: 99px; padding: 14px 28px;
            font-size: 11px; font-weight: 900; text-transform: uppercase; display: none;
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.5); text-decoration: none;
            letter-spacing: 1px; cursor: pointer; border: none;
        }
        @media (min-width: 768px) { #forcekes-download-btn { bottom: 40px; right: 40px; left: auto; transform: none; } }
        #forcekes-download-btn.loading { background: #555; cursor: wait; opacity: 0.7; }
    </style>
</head>
<body class="bg-black text-white min-h-screen">
    <?php include 'menu.php'; ?>

    <main class="max-w-7xl mx-auto px-6 py-8 md:py-20 mt-20 md:mt-24">
        <header class="mb-10 md:mb-16">
            <h1 class="text-3xl sm:text-4xl md:text-6xl font-black italic uppercase tracking-tighter leading-none"><?= $displayName ?></h1>
            <div class="h-1 md:h-2 w-16 md:w-24 bg-blue-600 mt-4 rounded-full"></div>
        </header>

        <div class="gallery grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-8">
            <?php if (is_array($photos) && !empty($photos)): ?>
                <?php foreach ($photos as $index => $p): 
                    $isVid = (strpos($p['image_url'], '.webm') !== false);
                    $url = htmlspecialchars($p['image_url']);
                ?>
                    <a href="<?= $url ?>" class="gallery-item group" data-index="<?= $index ?>" data-type="<?= $isVid ? 'video' : 'image' ?>">
                        <div class="aspect-square rounded-[1.8rem] md:rounded-[3rem] overflow-hidden border border-white/5 bg-zinc-900 relative">
                            <?php if ($isVid): ?>
                                <video src="<?= $url ?>#t=0.1" class="w-full h-full object-cover opacity-60 group-hover:opacity-100 transition" muted playsinline></video>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center pl-1 group-hover:scale-110 transition"><svg fill="white" viewBox="0 0 24 24" class="w-6 h-6"><path d="M8 5v14l11-7z"/></svg></div>
                                </div>
                            <?php else: ?>
                                <img src="<?= $url ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-1000" loading="lazy">
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <div id="forcekes-modal" class="hidden">
        <div id="modal-overlay"></div>
        <div id="modal-content">
            <img id="modal-img" class="modal-media hidden" src="" alt="">
            <video id="modal-video" class="modal-media hidden" controls autoplay loop playsinline></video>
        }
        <button id="modal-close" class="modal-btn" aria-label="Sluiten">&times;</button>
        <button id="modal-prev" class="modal-btn" aria-label="Vorige"><svg fill="currentColor" viewBox="0 0 24 24"><path d="M15.41 16.59L10.83 12l4.58-4.59L14 6l-6 6 6 6 1.41-1.41z"/></svg></button>
        <button id="modal-next" class="modal-btn" aria-label="Volgende"><svg fill="currentColor" viewBox="0 0 24 24"><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/></svg></button>
    </div>

    <button id="forcekes-download-btn" data-slug="<?= $pageSlug ?>">Media Opslaan</button>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('forcekes-modal');
            const modalImg = document.getElementById('modal-img');
            const modalVideo = document.getElementById('modal-video');
            const modalClose = document.getElementById('modal-close');
            const modalPrev = document.getElementById('modal-prev');
            const modalNext = document.getElementById('modal-next');
            const modalOverlay = document.getElementById('modal-overlay');
            const downloadBtn = document.getElementById('forcekes-download-btn');
            const galleryItems = document.querySelectorAll('.gallery-item');

            let currentIndex = 0;
            let currentMediaUrl = "";

            // Hoofdfunctie om de modal te openen en te vullen
            function openModal(index) {
                const item = galleryItems[index];
                currentMediaUrl = item.href;
                const type = item.getAttribute('data-type');
                currentIndex = index;

                // Stop en verberg beide media-elementen
                modalImg.classList.add('hidden');
                modalVideo.classList.add('hidden');
                modalVideo.pause();
                modalVideo.src = ""; // Reset video om buffer te wissen

                if (type === 'video') {
                    modalVideo.src = currentMediaUrl;
                    modalVideo.classList.remove('hidden');
                } else {
                    modalImg.src = currentMediaUrl;
                    modalImg.classList.remove('hidden');
                }

                // Toon download knop (JS doet de actie nu)
                downloadBtn.style.display = 'block';

                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden'; // Voorkom scrollen op de achtergrond
            }

            // Functie om de modal te sluiten
            function closeModal() {
                modal.classList.add('hidden');
                // Reset media om geheugen/bandbreedte te sparen
                modalVideo.pause();
                modalVideo.src = "";
                modalImg.src = "";
                downloadBtn.style.display = 'none';
                document.body.style.overflow = ''; // Herstel scrollen
            }

            function showPrev() {
                currentIndex = (currentIndex - 1 + galleryItems.length) % galleryItems.length;
                openModal(currentIndex);
            }

            function showNext() {
                currentIndex = (currentIndex + 1) % galleryItems.length;
                openModal(currentIndex);
            }

            // --- TRUC VAN DE AREND: JS-gestuurde cross-origin download ---
            async function startDownload() {
                const url = currentMediaUrl;
                if (!url) return;

                const originalText = downloadBtn.innerHTML;
                downloadBtn.innerHTML = "Wachten...";
                downloadBtn.classList.add('loading');
                downloadBtn.disabled = true;

                try {
                    // 1. Download bestand onder water via JS (moet CORS toelaten)
                    const response = await fetch(url);
                    const blob = await response.blob();
                    
                    // 2. Maak een lokale, tijdelijke 'blob:' URL
                    const blobUrl = window.URL.createObjectURL(blob);
                    
                    // 3. Maak een onzichtbaar linkje en 'klik' erop
                    const tempLink = document.createElement('a');
                    tempLink.style.display = 'none';
                    tempLink.href = blobUrl;
                    
                    // Haal bestandsextensie uit de URL
                    const ext = url.split('.').pop();
                    const pageSlug = downloadBtn.getAttribute('data-slug');
                    // Maak een nette bestandsnaam (bv. museum_joris_b5f3...webp)
                    tempLink.download = `forcekes_${pageSlug}_${url.split('/').pop()}`;
                    
                    document.body.appendChild(tempLink);
                    tempLink.click();
                    
                    // 4. Opruimen
                    document.body.removeChild(tempLink);
                    window.URL.revokeObjectURL(blobUrl);

                } catch (error) {
                    console.error("❌ Download gefaald:", error);
                    alert("Downloaden gefaald. Klik op de afbeelding in het nieuwe tabblad om op te slaan.");
                    // Als Blob fetch faalt, val dan terug op de oude methode
                    window.open(url, '_blank');
                } finally {
                    downloadBtn.innerHTML = originalText;
                    downloadBtn.classList.remove('loading');
                    downloadBtn.disabled = false;
                }
            }

            // --- Event Listeners ---

            // Klik op galerij-item
            galleryItems.forEach((item, index) => {
                item.addEventListener('click', (e) => {
                    e.preventDefault(); // Cruciaal: voorkom dat de link direct openklapt
                    openModal(index);
                });
            });

            // Klik-events voor de controls
            modalClose.addEventListener('click', closeModal);
            modalPrev.addEventListener('click', showPrev);
            modalNext.addEventListener('click', showNext);
            modalOverlay.addEventListener('click', closeModal); // Klik buiten de foto = sluiten
            downloadBtn.addEventListener('click', startDownload); // De nieuwe JS-actie

            // Toetsenbord ondersteuning
            document.addEventListener('keydown', (e) => {
                if (modal.classList.contains('hidden')) return; // Doe niets als de modal dicht is
                if (e.key === 'Escape') closeModal();
                if (e.key === 'ArrowLeft') showPrev();
                if (e.key === 'ArrowRight') showNext();
            });
        });
    </script>
</body>
</html>