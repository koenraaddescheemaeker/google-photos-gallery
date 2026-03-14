<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>De Zwaaikamer - Familie Portaal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src='https://jitsi.riot.im/external_api.js'></script>
    <script src="https://unpkg.com/@supabase/supabase-js@2"></script>
    <style>
        #jitsi-container {
            width: 100%;
            height: 70vh;
            border-radius: 1.5rem;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            background-color: #111827;
        }
    </style>
</head>
<body class="bg-gray-950 text-white font-sans antialiased">

    <?php include 'menu.php'; ?>

    <main class="max-w-7xl mx-auto px-4 py-8">
        <header class="mb-10 text-center">
            <h1 class="text-5xl font-black mb-4 bg-clip-text text-transparent bg-gradient-to-r from-indigo-400 to-purple-400">
                De Zwaaikamer
            </h1>
            <p class="text-gray-400 text-lg max-w-2xl mx-auto">
                Je bent nu zichtbaar voor de familie. Zodra je de kamer verlaat, gaat je lampje weer uit.
            </p>
        </header>

        <div id="jitsi-container" class="border border-gray-800">
            </div>

        <div class="mt-8 flex justify-center space-x-4">
            <div class="flex items-center space-x-2 text-sm text-gray-400 bg-gray-900/50 px-4 py-2 rounded-full border border-gray-800">
                <span class="flex h-2 w-2 rounded-full bg-green-500"></span>
                <span>Je bent momenteel online</span>
            </div>
        </div>
    </main>

    <script>
        // 1. Initialiseer Supabase voor Presence
        const supabaseClient = supabase.createClient('<?= $supabaseUrl ?>', '<?= $supabaseKey ?>');
        const channel = supabaseClient.channel('zwaaikamer_presence', {
            config: { presence: { key: 'user' } }
        });

        // 2. Initialiseer Jitsi
        window.onload = () => {
            const domain = "jitsi.riot.im";
            const options = {
                roomName: "FamilieZwaaikamer_UniekeNaam12345", 
                parentNode: document.querySelector('#jitsi-container'),
                configOverwrite: {
                    startWithAudioMuted: true,
                    startWithVideoMuted: false,
                    toolbarButtons: [
                        "microphone", "camera", "desktop", "chat", 
                        "raisehand", "participants-pane", "tileview", 
                        "hangup", "settings"
                    ],
                    disableDeepLinking: true,
                },
                interfaceConfigOverwrite: {
                    SHOW_JITSI_WATERMARK: false,
                    DEFAULT_REMOTE_DISPLAY_NAME: 'Familielid',
                }
            };
            
            const api = new JitsiMeetExternalAPI(domain, options);

            // 3. Start Tracking Presence in Supabase
            channel.subscribe(async (status) => {
                if (status === 'SUBSCRIBED') {
                    // We tracken een willekeurige ID of naam om te laten weten dat er IEMAND is
                    await channel.track({
                        online_at: new Date().toISOString(),
                        user_id: Math.random().toString(36).substring(7)
                    });
                }
            });

            // 4. Stop tracking als de gebruiker ophangt
            api.addEventListener('videoConferenceLeft', () => {
                window.location.href = "index.php";
            });
        };
    </script>
</body>
</html>