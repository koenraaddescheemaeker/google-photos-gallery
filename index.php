<?php
// De link naar jouw gedeelde album
$albumUrl = "https://photos.app.goo.gl/iZnBAYe88LB4r5Z89";
// 2. Haal de foto's op (met foutafhandeling)
$content = @file_get_contents($albumUrl);
if (!$content) {
    die("Kon het album niet laden. Controleer de link.");
}
// Zoek naar de foto-URL's in de broncode
preg_match_all('/https:\/\/lh3\.googleusercontent\.com\/pw\/[a-zA-Z0-9\-_]+/', $content, $matches);
$photos = array_unique($matches[0]);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mijn Fotoalbum</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/photoswipe/dist/photoswipe.css">
    <style>
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased">

    <div class="max-w-7xl mx-auto px-4 py-12">
        <header class="mb-16 text-center">
            <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl">
                Mijn Gedeelde Foto's
            </h1>
            <p class="mt-4 text-lg text-gray-500">Een premium overzicht van onze mooiste herinneringen.</p>
            <div class="mt-6 h-1 w-24 bg-indigo-600 mx-auto rounded-full"></div>
        </header>

        <div class="gallery-grid" id="my-gallery">
            <?php foreach ($photos as $url): ?>
                <a href="<?= $url ?>=w2048" 
                   data-pswp-width="2048" 
                   data-pswp-height="1536" 
                   target="_blank"
                   class="group relative block overflow-hidden rounded-2xl bg-gray-200 shadow-md transition-all hover:shadow-2xl">
                    
                    <img src="<?= $url ?>=w800" 
                         alt="Foto" 
                         loading="lazy"
                         class="h-72 w-full object-cover transition-transform duration-700 group-hover:scale-110">
                    
                    <div class="absolute inset-0 bg-black opacity-0 group-hover:opacity-20 transition-opacity duration-300"></div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <script type="module">
        import PhotoSwipeLightbox from 'https://unpkg.com/photoswipe/dist/photoswipe-lightbox.esm.js';
        const lightbox = new PhotoSwipeLightbox({
            gallery: '#my-gallery',
            children: 'a',
            pswpModule: () => import('https://unpkg.com/photoswipe/dist/photoswipe.esm.js')
        });
        lightbox.init();
    </script>
</body>
</html>