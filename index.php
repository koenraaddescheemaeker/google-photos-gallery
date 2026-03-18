<?php
/**
 * index.php - De Premium Familie Galerij
 */
require_once 'config.php';

// 1. Haal de tokens en het gekozen album op (Olifant-geheugen)
$res = supabaseRequest('google_tokens?id=eq.1&select=*');
$dbData = $res[0] ?? null;

// Als er geen tokens zijn, toon de welkomstpagina
if (!$dbData || !getValidAccessToken()) {
    include 'welcome.php'; // Een simpel bestandje met een login knop
    exit;
}

$token = getValidAccessToken();
$albumId = $dbData['active_album_id'] ?? null;
$albumTitle = $dbData['active_album_title'] ?? "Onze Foto's";

// 2. Haal de foto's op (Arend-overzicht)
$photos = [];
if ($albumId) {
    $ch = curl_init("https://photoslibrary.googleapis.com/v1/mediaItems:search");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        "albumId" => $albumId,
        "pageSize" => 100 // We halen direct een mooie set op
    ]));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);
    $photos = $response['mediaItems'] ?? [];
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Familie Forcekes - Herinneringen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/glightbox/3.2.0/css/glightbox.min.css">
    <style>
        .photo-grid { column-count: 2; column-gap: 1rem; }
        @media (min-width: 768px) { .photo-grid { column-count: 3; } }
        @media (min-width: 1024px) { .photo-grid { column-count: 4; } }
        .photo-item { break-inside: avoid; margin-bottom: 1rem; transition: all 0.3s ease; }
        .photo-item:hover { transform: scale(1.02); filter: brightness(1.1); }
    </style>
</head>
<body class="bg-slate-950 text-white font-sans">

    <header class="p-8 text-center">
        <h1 class="text-4xl font-black tracking-tighter mb-2 italic">FORCEKES</h1>
        <p class="text-slate-400 uppercase tracking-widest text-xs"><?php echo htmlspecialchars($albumTitle); ?></p>
        <div class="mt-4 h-1 w-12 bg-blue-600 mx-auto rounded-full"></div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-8">
        <?php if (!$albumId): ?>
            <div class="text-center py-20 bg-slate-900 rounded-3xl border border-dashed border-slate-800">
                <p class="text-slate-500 italic">Beheerder, kies eerst een album in de <a href="admin.php" class="text-blue-500 underline">Admin</a>.</p>
            </div>
        <?php elseif (empty($photos)): ?>
            <div class="text-center py-20">
                <p class="text-slate-500">Dit album lijkt leeg te zijn...</p>
            </div>
        <?php else: ?>
            <div class="photo-grid">
                <?php foreach ($photos as $photo): ?>
                    <div class="photo-item overflow-hidden rounded-2xl bg-slate-900 shadow-xl">
                        <a href="<?php echo $photo['baseUrl']; ?>=w1600" class="glightbox">
                            <img src="<?php echo $photo['baseUrl']; ?>=w600" 
                                 alt="<?php echo htmlspecialchars($photo['filename'] ?? ''); ?>"
                                 class="w-full h-auto block"
                                 loading="lazy">
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer class="py-12 text-center text-slate-600 text-[10px] tracking-widest uppercase">
        &copy; 2026 Familie Forcekes &bull; Premium Memories
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/glightbox/3.2.0/js/glightbox.min.js"></script>
    <script>const lightbox = GLightbox({ selector: '.glightbox' });</script>
</body>
</html>