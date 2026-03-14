<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>De Zwaaikamer - Familie Portaal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src='https://jitsi.riot.im/external_api.js'></script>
    <style>
        #jitsi-container {
            width: 100%;
            height: 75vh;
            border-radius: 1.5rem;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
    </style>
</head>
<?php include 'menu.php'; ?>
<body class="bg-gray-950 text-white font-sans antialiased">

    <nav class="p-6">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <a href="index.php" class="text-xl font-bold tracking-tighter hover:text-indigo-400 transition">
                &larr; Terug naar de Foto's
            </a>
            <div class="bg-green-500/10 text-green-400 px-4 py-1 rounded-full text-sm font-medium border border-green-500/20">
                Sessie Actief
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 py-8">
        <header class="mb-10 text-center">
            <h1 class="text-5xl font-black mb-4 bg-clip-text text-transparent bg-gradient-to-r from-indigo-400 to-purple-400">
                De Zwaaikamer
            </h1>
            <p class="text-gray-400 text-lg max-w-2xl mx-auto">
                Welkom in onze digitale huiskamer. Zet je camera aan en zwaai even naar de rest van de familie!
            </p>
        </header>

        <div id="jitsi-container" class="bg-gray-900 border border-gray-800">
            </div>

        <footer class="mt-12 text-center text-gray-500 text-sm">
            <p>&copy; 2026 Familie Portaal - Veilig en privé via Jitsi Meet</p>
        </footer>
    </main>

    <script>
        window.onload = () => {
            const domain = "jitsi.riot.im";
            const options = {
                roomName: "FamilieZwaaikamer_UniekeNaam12345", // TIP: Verander dit in een unieke naam voor jouw familie
                parentNode: document.querySelector('#jitsi-container'),
                configOverwrite: {
                    startWithAudioMuted: true,
                    startWithVideoMuted: false,
                    // Jouw specifieke toolbar knoppen:
                    toolbarButtons: [
                        "microphone", "camera", "desktop", "chat", 
                        "raisehand", "participants-pane", "tileview", 
                        "hangup", "settings"
                    ],
                    disableDeepLinking: true, // Voorkomt dat mobiele gebruikers naar de app worden gedwongen
                },
                interfaceConfigOverwrite: {
                    LANG_DETECTION: true,
                    SHOW_JITSI_WATERMARK: false,
                    DEFAULT_REMOTE_DISPLAY_NAME: 'Familielid',
                },
                userInfo: {
                    displayName: 'Bezoeker' // Je kunt dit later dynamisch maken via Supabase
                }
            };
            const api = new JitsiMeetExternalAPI(domain, options);

            // Optioneel: acties toevoegen als de call eindigt
            api.addEventListener('videoConferenceLeft', () => {
                window.location.href = "index.php";
            });
        };
    </script>
</body>
</html>