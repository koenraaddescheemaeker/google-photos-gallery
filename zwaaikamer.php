<?php
/** * FORCEKES - zwaaikamer.php (Compact Edition - Gekeurd door Manu) */
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
        .jitsi-frame { width: 100%; height: calc(100vh - 100px); border: none; border-radius: 1.5rem; }
    </style>
</head>
<body class="bg-black">
    <?php include 'menu.php'; ?>
    <main class="pt-24 px-6 h-screen flex flex-col">
        <header class="mb-4 flex justify-between items-end px-4">
            <p class="text-[10px] font-black uppercase tracking-[0.5em] text-zinc-500 italic">
                De <span class="text-blue-600">Zwaai</span>kamer
            </p>
            <div class="flex items-center gap-2 mb-0.5">
                <div class="w-1.5 h-1.5 bg-red-600 rounded-full animate-pulse"></div>
                <span class="text-[8px] font-bold uppercase tracking-widest text-zinc-700">Live</span>
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