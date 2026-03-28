<?php
/** * FORCEKES - zwaaikamer.php (Premium Navigation Edition) */
require_once 'config.php';

$roomName = "zwaaikamer";
$jitsiDomain = "jitsi.ulyssis.org";
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
        
        body { margin: 0; padding: 0; background-color: #000; color: #fff; font-family: 'Inter', sans-serif; overflow: hidden; height: 100vh; }
        #jitsi-container { width: 100vw; height: 100vh; background-color: #000; }
        
        /* Zwevende Terug-knop */
        .btn-exit {
            position: fixed;
            top: 25px;
            left: 25px;
            z-index: 9999;
            display: flex;
            items-center: center;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 10px 20px;
            border-radius: 99px;
            color: #fff;
            text-decoration: none;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 2px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-exit:hover {
            background: #3b82f6;
            border-color: #3b82f6;
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.4);
        }
        .btn-exit svg { margin-right: 10px; }

        #fallback {
            display: none; position: fixed; inset: 0; z-index: 200;
            background: #000; flex-direction: column; align-items: center; justify-content: center;
            text-align: center; padding: 20px;
        }
    </style>
</head>
<body>

    <a href="index.php" class="btn-exit">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
        Terug naar Portaal
    </a>

    <div id="fallback">
        <p class="text-zinc-500 mb-6">De verbinding met de Zwaaikamer kon niet in de pagina worden geladen.</p>
        <a href="https://<?= $jitsiDomain ?>/<?= $roomName ?>" target="_blank" class="px-8 py-4 bg-blue-600 rounded-full font-black uppercase text-[10px] tracking-widest">Open in nieuw venster</a>
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
                    prejoinPageEnabled: false,
                    startWithAudioMuted: false,
                    startWithVideoMuted: false,
                    disableDeepLinking: true,
                    enableWelcomePage: false,
                },
                interfaceConfigOverwrite: {
                    SHOW_JITSI_WATERMARK: false,
                    SHOW_BRAND_WATERMARK: false,
                    SHOW_POWERED_BY: false,
                    DEFAULT_BACKGROUND: '#000000',
                    TOOLBAR_BUTTONS: [
                        'microphone', 'camera', 'fullscreen', 'fodeviceselection', 
                        'hangup', 'profile', 'chat', 'settings', 'tileview'
                    ]
                }
            };

            try {
                const api = new JitsiMeetExternalAPI(domain, options);
                api.executeCommand('displayName', 'Familie Forcekes');
            } catch (e) {
                document.getElementById('fallback').style.display = 'flex';
            }
        };

        // Fallback check
        setTimeout(() => {
            const container = document.getElementById('jitsi-container');
            if (container && container.innerHTML === "") {
                document.getElementById('fallback').style.display = 'flex';
            }
        }, 8000);
    </script>
</body>
</html>