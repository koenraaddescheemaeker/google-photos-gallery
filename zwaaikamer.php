<?php
/**
 * FORCEKES - Zwaaikamer (Jitsi Meet)
 * Standalone Edition
 */
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forcekes | Zwaaikamer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://8x8.vc/vpaas-magic-cookie-861f8749386d44869507983692686161/external_api.js" async></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #000; overflow: hidden; }
    </style>
</head>
<body class="text-zinc-100">
    <div class="flex flex-col h-screen">
        <header class="p-6 flex justify-between items-center border-b border-zinc-800 bg-black/50 backdrop-blur-md">
            <div class="flex items-center gap-4">
                <a href="admin.php" class="p-2 hover:bg-zinc-800 rounded-full transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                </a>
                <h1 class="text-2xl font-black italic uppercase tracking-tighter">
                    FORCEKES <span class="text-blue-500">ZWAAIKAMER</span>
                </h1>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                <span class="text-[10px] font-bold uppercase tracking-widest opacity-50">Live Mode</span>
            </div>
        </header>

        <main class="flex-grow bg-zinc-950 relative">
            <div id="jaas-container" class="absolute inset-0"></div>
        </main>
    </div>

    <script type="text/javascript">
        window.onload = () => {
            const api = new JitsiMeetExternalAPI("8x8.vc", {
                roomName: "vpaas-magic-cookie-861f8749386d44869507983692686161/ForcekesZwaaikamer",
                parentNode: document.querySelector('#jaas-container'),
                configOverwrite: {
                    disableThirdPartyRequests: true,
                    prejoinPageEnabled: false,
                    startWithAudioMuted: false,
                    startWithVideoMuted: false
                },
                interfaceConfigOverwrite: {
                    TOOLBAR_BUTTONS: [
                        'microphone', 'camera', 'closedcaptions', 'desktop', 'fullscreen',
                        'fodeviceselection', 'hangup', 'profile', 'chat', 'recording',
                        'livestreaming', 'etherpad', 'sharedvideo', 'settings', 'raisehand',
                        'videoquality', 'filmstrip', 'invite', 'feedback', 'stats', 'shortcuts',
                        'tileview', 'videobackgroundblur', 'download', 'help', 'mute-everyone',
                        'security'
                    ],
                }
            });
        }
    </script>
</body>
</html>