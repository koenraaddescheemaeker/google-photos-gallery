<?php
/** * FORCEKES - zwaaikamer.php (Evolix Premium Edition) */
require_once 'config.php';

// We maken een unieke kamernaam voor de familie
$roomName = "Forcekes_Zwaaikamer_Familie";
$jitsiDomain = "meet.evolix.org";
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forcekes | Zwaaikamer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        
        /* Deep Black Theme: Geen afleiding, puur beeld */
        body { margin: 0; padding: 0; background-color: #000; color: #fff; font-family: 'Inter', sans-serif; overflow: hidden; }
        #jitsi-container { width: 100vw; height: 100vh; background-color: #000; }
        
        /* Subtiele loader */
        #loader {
            position: fixed; inset: 0; background: #000; z-index: 100;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            transition: opacity 0.8s ease;
        }
    </style>
</head>
<body>

    <div id="loader">
        <div class="w-10 h-10 border-2 border-blue-600 border-t-transparent rounded-full animate-spin mb-4"></div>
        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-600">Verbinding maken met Evolix...</p>
    </div>

    <div id="jitsi-container"></div>

    <script src="https://<?= $jitsiDomain ?>/external_api.js"></script>
    <script>
        window.onload = () => {
            const domain = "<?= $jitsiDomain ?>";
            const options = {
                roomName: "<?= $roomName ?>",
                width: "100%",
                height: "100%",
                parentNode: document.querySelector('#jitsi-container'),
                lang: 'nl',
                configOverwrite: {
                    prejoinPageEnabled: false,      // Direct de kamer in
                    startWithAudioMuted: false,     // Direct praten
                    startWithVideoMuted: false,     // Direct zwaaien
                    disableDeepLinking: true,       // Geen gezeur over apps op mobiel
                    enableLobbyChat: false,
                    enableWelcomePage: false,
                    disableModeratorIndicator: true,
                    requireDisplayName: true,
                    toolbarButtons: [
                        'microphone', 'camera', 'fullscreen', 'fodeviceselection', 
                        'hangup', 'profile', 'chat', 'settings', 'tileview'
                    ]
                },
                interfaceConfigOverwrite: {
                    SHOW_JITSI_WATERMARK: false,
                    SHOW_BRAND_WATERMARK: false,
                    SHOW_POWERED_BY: false,
                    DEFAULT_BACKGROUND: '#000000',
                    DISABLE_VIDEO_BACKGROUND: true
                }
            };

            const api = new JitsiMeetExternalAPI(domain, options);

            // Verberg de zwarte loader zodra de verbinding staat
            api.addEventListener('videoConferenceJoined', () => {
                const loader = document.getElementById('loader');
                if(loader) {
                    loader.style.opacity = '0';
                    setTimeout(() => loader.remove(), 800);
                }
            });

            // Stel de naam in voor de familie-ervaring
            api.executeCommand('displayName', 'Familie Forcekes');
        };
    </script>
</body>
</html>