<?php
/** * FORCEKES - zwaaikamer.php (Direct Jitsi Embed) */
require_once 'config.php';
$jitsiUrl = "https://jitsi.riot.im/zwaaikamer#config.toolbarButtons=[%22microphone%22,%22camera%22,%22desktop%22,%22chat%22,%22raisehand%22,%22participants-pane%22,%22tileview%22,%22hangup%22,%22settings%22]";
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Zwaaikamer | Forcekes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: #000; overflow: hidden; }
        .jitsi-frame { width: 100%; height: calc(100vh - 100px); border: none; border-radius: 2rem; }
    </style>
</head>
<body class="bg-black">
    <?php include 'menu.php'; ?>
    <main class="pt-32 px-6 h-screen flex flex-col">
        <header class="mb-6 flex justify-between items-center px-4">
            <h1 style="font-family:'Playfair Display', serif;" class="text-3xl italic text-white">De Zwaaikamer</h1>
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                <span class="text-[10px] font-bold uppercase tracking-widest text-zinc-500">Live Verbinding</span>
            </div>
        </header>
        
        <iframe 
            src="<?= $jitsiUrl ?>" 
            allow="camera; microphone; display-capture; autoplay; clipboard-write" 
            class="jitsi-frame shadow-2xl border border-white/5"
        ></iframe>
    </main>
</body>
</html>