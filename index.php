<?php
// De link naar jouw gedeelde album
$albumUrl = "https://photos.app.goo.gl/iZnBAYe88LB4r5Z89";

// Haal de inhoud van de pagina op
$content = file_get_contents($albumUrl);

// Zoek naar alle directe linkjes naar foto's (Google gebruikt specifieke patronen)
preg_match_all('/https:\/\/lh3\.googleusercontent\.com\/pw\/[a-zA-Z0-9\-_]+/', $content, $matches);

// Haal dubbele linkjes weg
$photos = array_unique($matches[0]);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Mijn Foto Album</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .gallery-grid {
            column-count: 3;
            column-gap: 1.5rem;
        }
        @media (max-width: 768px) { .gallery-grid { column-count: 2; } }
        @media (max-width: 480px) { .gallery-grid { column-count: 1; } }
    </style>
</head>
<body class="bg-gray-50 p-8">
    <h1 class="text-3xl font-bold text-center mb-10">Mijn Gedeelde Foto's</h1>
    
    <div class="gallery-grid">
        <?php foreach ($photos as $photoUrl): ?>
            <div class="mb-6 break-inside-avoid shadow-lg rounded-lg overflow-hidden border border-gray-200">
                <img src="<?php echo $photoUrl; ?>=w800" class="w-full h-auto block">
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>