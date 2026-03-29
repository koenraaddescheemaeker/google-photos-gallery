<?php
/** * FORCEKES - gallery.php (Fixed for Spaces & Slugs) */
require_once 'config.php';

// 1. Haal de categorie op en maak deze veilig voor de URL
$pageSlug = $_GET['page'] ?? 'museum';

// CRUCIAAL: Gebruik rawurlencode om spaties (zoals in 'feest 2025') te fixen
$encodedSlug = rawurlencode($pageSlug);

// 2. Haal de foto's op uit de database
$photos = supabaseRequest("album_photos?category=eq.$encodedSlug&select=*&order=captured_at.desc", 'GET');

// 3. Dynamische naamgeving voor de titel
$displayName = ($pageSlug === 'joris') ? 'Joris' : (($pageSlug === 'museum') ? 'Het Museum' : ucfirst(htmlspecialchars($pageSlug)));

$hasError = false;
$errorMessage = "";

// Verbeterde error-check
if ($photos === null || (isset($photos['error']) && $photos['error'] === true)) {
    $hasError = true;
    $errorMessage = "Kon geen verbinding maken met de database. Controleer de instellingen.";
} elseif (isset($photos['message'])) {
    // Supabase geeft soms een foutmelding in een 'message' veld
    $hasError = true;
    $errorMessage = "Database meldt: " . $photos['message'];
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forcekes | <?= $displayName ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { margin: 0; padding: 0; font-family: 'Inter', sans-serif; background-color: #000; color: #fff; overflow-x: hidden; }
        
        /* Modal & UI styling blijft behouden zoals voorheen */
        #forcekes-modal { position: fixed; inset: 0; z-index: 9999; display: none; align-items: center; justify-content: center; background-color: #000; }
        .modal-media { width: 100%; height: 100%; object-fit: contain; }
    </style>
</head>
<body class="bg-black">
    <?php include 'menu.php'; ?>

    <main class="max-w-7xl mx-auto px-6 py-20 mt-20">
        <header class="mb-16">
            <h1 class="text-4xl md:text-6xl font-black italic uppercase tracking-tighter leading-none"><?= $displayName ?></h1>
            <div class="h-2 w-24 bg-blue-600 mt-6 rounded-full"></div>
        </header>

        <?php if ($hasError): ?>
            <div class="py-20 text-center glass rounded-[3rem] border border-red-900/20">
                <p class="text-red-500 font-bold"><?= $errorMessage ?></p>
                <p class="text-[10px] text-zinc-600 uppercase mt-4 tracking-widest">Gevraagd album: <?= htmlspecialchars($pageSlug) ?></p>
            </div>
        <?php else: ?>
            <div class="gallery grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-8">
                <?php if (!empty($photos) && is_array($photos)): ?>
                    <?php foreach ($photos as $index => $p): 
                        if (!isset($p['image_url'])) continue;
                        $url = htmlspecialchars($p['image_url']);
                        $isVid = (strpos($url, '.mp4') !== false || strpos($url, '.webm') !== false);
                    ?>
                        <a href="<?= $url ?>" class="gallery-item group" data-index="<?= $index ?>" data-type="<?= $isVid ? 'video' : 'image' ?>">
                            <div class="aspect-square rounded-[2rem] md:rounded-[3rem] overflow-hidden border border-white/5 bg-zinc-900 relative">
                                <?php if ($isVid): ?>
                                    <video src="<?= $url ?>#t=0.1" class="w-full h-full object-cover opacity-60" muted playsinline></video>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center pl-1"><svg fill="white" viewBox="0 0 24 24" class="w-6 h-6"><path d="M8 5v14l11-7z"/></svg></div>
                                    </div>
                                <?php else: ?>
                                    <img src="<?= $url ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-700" loading="lazy">
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-full py-20 text-center border border-dashed border-white/10 rounded-[3rem]">
                        <p class="text-zinc-500 italic">Dit gedeelte van <strong><?= strtolower($displayName) ?></strong> is momenteel leeg...</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>

    <?php include 'modal-logic.php'; // Indien je dit apart hebt staan, anders de script tag hier ?>

</body>
</html>