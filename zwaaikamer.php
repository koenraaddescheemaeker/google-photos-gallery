<?php
/** * FORCEKES - zwaaikamer.php (Freifunk München Edition) */
require_once 'config.php';
$roomName = "Forcekes_Zwaaikamer_Master_" . substr(md5(SITE_URL), 0, 6);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forcekes | Zwaaikamer</title>
    <style>
        body { margin: 0; padding: 0; background-color: #000; overflow: hidden; }
        #jitsi-container { width: 100vw; height: 100vh; }
    </style>
</head>
<body>
    <div id="jitsi-container"></div>
    <script src="https://meet.ffmuc.net/external_api.js"></script>
    <script>
        window.onload = () => {
            const options = {
                roomName: "<?= $roomName ?>",
                width: "100%", height: "100%",
                parentNode: document.querySelector('#jitsi-container'),
                lang: 'nl',
                configOverwrite: {
                    prejoinPageEnabled: false,
                    startWithAudioMuted: false,
                    startWithVideoMuted: false,
                    disableModeratorIndicator: true,
                    enableLobbyChat: false,
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
            const api = new JitsiMeetExternalAPI("meet.ffmuc.net", options);
            api.executeCommand('displayName', 'Familie Forcekes');
        };
    </script>
</body>
</html>