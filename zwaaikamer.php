<?php
/** * FORCEKES - zwaaikamer.php (Direct Match Edition) */
require_once 'config.php';

// We gebruiken exact de naam die voor jou werkt
$roomName = "zwaaikamer";
$jitsiDomain = "jitsi.ulyssis.org";
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forcekes | Zwaaikamer</title>
    <style>
        body { margin: 0; padding: 0; background-color: #000; color: #fff; overflow: hidden; height: 100vh; }
        #jitsi-container { width: 100vw; height: 100vh; background-color: #000; }
        
        /* Fallback link mocht de inbedding toch blokkeren */
        #fallback {
            display: none; position: fixed; inset: 0; z-index: 200;
            background: #000; flex-direction: column; align-items: center; justify-content: center;
            text-align: center; padding: 20px;
        }
        .btn-premium {
            background: #3b82f6; color: white; padding: 15px 30px; border-radius: 99px;
            text-decoration: none; font-weight: 900; text-transform: uppercase; letter-spacing: 1px;
            margin-top: 20px; font-family: sans-serif; font-size: 12px;
        }
    </style>
</head>
<body>

    <div id="fallback">
        <p>De Zwaaikamer beveiliging staat rechtstreekse inbedding niet toe.</p>
        <a href="https://<?= $jitsiDomain ?>/<?= $roomName ?>" target="_blank" class="btn-premium">Open Zwaaikamer in nieuw venster</a>
    </div>

    <div id="jitsi-container"></div>

    <script src="https://<?= $jitsiDomain ?>/external_api.js"></script>
    <script>
        window.onload = () => {
            try {
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
                    },
                    interfaceConfigOverwrite: {
                        SHOW_JITSI_WATERMARK: false,
                        SHOW_BRAND_WATERMARK: false,
                        SHOW_POWERED_BY: false,
                        DEFAULT_BACKGROUND: '#000000',
                    }
                };

                const api = new JitsiMeetExternalAPI(domain, options);

                // Check of de verbinding mislukt (bijv. door CSP/Frame blockers)
                api.addEventListener('videoConferenceLeft', () => {
                     // Als de gebruiker eruit wordt gegooid of het niet laadt
                });

            } catch (e) {
                console.error("Jitsi loading failed", e);
                document.getElementById('fallback').style.display = 'flex';
            }
        };

        // Als na 10 seconden het container-element nog leeg is, toon de fallback
        setTimeout(() => {
            const container = document.getElementById('jitsi-container');
            if (container && container.innerHTML === "") {
                document.getElementById('fallback').style.display = 'flex';
            }
        }, 10000);
    </script>
</body>
</html>