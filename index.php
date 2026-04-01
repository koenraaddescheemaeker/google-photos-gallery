<?php
/** * FORCEKES - index.php (Fase 11: Diagnostisch Herstel) */
require_once 'config.php';

$dashboardData = supabaseRequest("rpc/get_album_dashboard", 'GET');

// Diagnose stellen
$apiError = null;
if (isset($dashboardData['error'])) {
    $apiError = $dashboardData['message'];
} elseif (!is_array($dashboardData) || !array_is_list($dashboardData)) {
    $apiError = "De database gaf een onverwacht antwoord terug.";
}

$albumGrid = (!$apiError) ? $dashboardData : [];
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8"><title>Forcekes Portaal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;900&family=Playfair+Display:ital,wght@1,900&display=swap');
        body { background: #000; color: #fff; font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        .serif-italic { font-family: 'Playfair Display', serif; font-style: italic; }
    </style>
</head>
<body class="bg-black">
    <?php include 'menu.php'; ?>
    <main class="max-w-7xl mx-auto px-10 pt-48 pb-32">
        <header class="mb-32 text-center">
            <h1 class="text-7xl md:text-[9rem] font-black italic uppercase tracking-tighter leading-none mb-8">Force<span class="text-blue-600">kes</span></h1>
            <p class="serif-italic text-2xl md:text-4xl text-zinc-500 italic">Archief van de <span class="text-white">Toekomst</span></p>
        </header>

        <?php if ($apiError): ?>
            <div class="bg-red-950/30 border border-red-500 p-10 rounded-[3rem] text-center">
                <p class="text-red-500 font-black uppercase tracking-widest text-[10px] mb-4">Verbindingsfout</p>
                <code class="text-white font-mono text-sm"><?= htmlspecialchars($apiError) ?></code>
                <p class="text-zinc-500 text-[9px] mt-6 uppercase tracking-widest">Controleer de API sleutel in config.php</p>
            </div>
        <?php elseif (empty($albumGrid)): ?>
            <div class="bg-zinc-900/50 border border-white/5 p-20 rounded-[4rem] text-center">
                <p class="serif-italic text-2xl text-zinc-500">Het archief is momenteel leeg...</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-16">
                <?php foreach ($albumGrid as $album): ?>
                    <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>