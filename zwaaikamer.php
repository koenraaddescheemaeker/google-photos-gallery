<?php
/**
 * FORCEKES - Zwaaikamer (Publieke Editie)
 * Geen login vereist voor familieleden.
 */
require_once 'config.php';

// 1. Haal de gesyncte foto's op voor de sfeervolle achtergrond
$photosRes = supabaseRequest('album_photos?category=eq.zwaaikamer&select=image_url', 'GET');
$photoUrls = (is_array($photosRes) && !empty($photosRes)) ? array_column($photosRes, 'image_url') : [];

// Fallback afbeelding mocht het album leeg zijn
if (empty($photoUrls)) {
    $photoUrls = ['https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1920&q=80'];
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forcekes | Zwaaikamer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://meet.jit.si/external_api.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #000; overflow: hidden; }
        
        .slideshow-img {
            transition: opacity 2s ease-in-out;
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: brightness(0.25) blur(5px);
        }
        .jitsi-glass {
            background: rgba(24, 24, 27, 0.4);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
    </style>
</head>
<body class="text-zinc-100 flex flex-col h-screen">

    <div class="z-50 relative">
        <?php include 'menu.php'; ?>
    </div>

    <div id="slideshow-container" class="fixed inset-0 z-0">
        <?php foreach ($photoUrls as $index => $url): ?>
            <img src="<?= htmlspecialchars($url) ?>" 
                 class="slideshow-img <?= $index === 0 ? 'opacity-100' : 'opacity-0' ?>" 
                 data-index="<?= $index ?>">
        <?php endforeach; ?>
    </div>

    <main class="flex-grow relative z-10 p-4 md:p-8 pt-28">
        <div id="jitsi-container" class="w-full h-full rounded-[3rem] overflow-hidden shadow-2xl jitsi-glass"></div>
    </main>

    <script type="text/javascript">
        // 1. Slideshow Logica
        const images = document.querySelectorAll('.slideshow-img');
        let currentIndex = 0;
        if (images.length > 1) {
            setInterval(() => {
                images[currentIndex].classList.replace('opacity-100', 'opacity-0');
                currentIndex = (currentIndex + 1) % images.length;
                images[currentIndex].classList.replace('opacity-0', 'opacity-100');
            }, 12000);
        }

        // 2. Jitsi Configuratie
        window.onload = () => {
            const domain = "meet.jit.si";
            // We gebruiken een unieke, lange kamernaam om beveiligingsmeldingen te voorkomen
            const uniqueRoomName = "ForcekesZwaaikamer_Premium_v2_Secure"; 

            const options = {
                roomName: uniqueRoomName,
                width: "100%",
                height: "100%",
                parentNode: document.querySelector('#jitsi-container'),
                configOverwrite: {
                    prejoinPageEnabled: false,
                    startWithAudioMuted: false,
                    startWithVideoMuted: false,
                    enableWelcomePage: false,
                    toolbarButtons: [
                        'microphone', 'camera', 'desktop', 'chat', 
                        'participants-pane', 'tileview', 'hangup', 'settings'
                    ]
                },
                interfaceConfigOverwrite: {
                    SHOW_JITSI_WATERMARK: false,
                    SHOW_WATERMARK_FOR_GUESTS: false,
                    DEFAULT_BACKGROUND: '#000000',
                    TOOLBAR_BUTTONS: [] // Forceert de knoppen uit configOverwrite
                }
            };
            const api = new JitsiMeetExternalAPI(domain, options);
        }
    </script>
</body>
</html>