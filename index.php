<?php
require_once 'config.php';

// 1. Welk album laden we?
$albumUrl = isset($_GET['album']) ? $_GET['album'] : "https://goo.gl/photos/H5nmVV473rJw1d6aA";
// We gebruiken /tmp omdat Docker daar meestal schrijfrechten geeft
$cacheFile = '/tmp/cache_' . md5($albumUrl) . '.json';
$cacheTime = 600; 

if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
    $data = json_decode(file_get_contents($cacheFile), true);
    $photos = $data['photos'];
    $albumTitle = $data['title'];
} else {
    // Verhoog de tijd die PHP mag besteden aan dit script om 504 te voorkomen
    set_time_limit(60); 

    $content = @file_get_contents($albumUrl);
    if (!$content) { die("Album niet bereikbaar. Controleer of de link openbaar is."); }

    preg_match('/<meta property="og:title" content="([^"]+)">/', $content, $titleMatches);
    $albumTitle = isset($titleMatches[1]) ? str_replace(" - Google Photos", "", $titleMatches[1]) : "Album";
    
    preg_match_all('/https:\/\/lh3\.googleusercontent\.com\/pw\/[a-zA-Z0-9\-_]+/', $content, $matches);
    $photos = array_unique($matches[0]);

    // Opslaan in /tmp
    if (!empty($photos)) {
        file_put_contents($cacheFile, json_encode(['photos' => $photos, 'title' => $albumTitle]));
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($albumTitle) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/photoswipe/dist/photoswipe.css">
    <script src="https://unpkg.com/smartcrop@2.0.5/smartcrop.js"></script>
    <style>
        .gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; }
        .photo-wrapper { position: relative; height: 300px; width: 100%; overflow: hidden; border-radius: 1rem; background: #eee; }
        .smart-img { position: absolute; max-width: none; opacity: 0; transition: opacity 0.3s ease; }
    </style>
</head>
<body class="bg-gray-50">
    <?php include 'menu.php'; ?>

    <div class="max-w-7xl mx-auto px-4 py-12">
        <header class="mb-12 text-center">
            <h1 class="text-4xl font-black"><?= htmlspecialchars($albumTitle) ?></h1>
        </header>

        <div class="gallery-grid" id="my-gallery">
            <?php foreach (array_slice($photos, 0, 24) as $url): // Laad er eerst maar 24 voor snelheid ?>
                <a href="<?= $url ?>=w2048" data-pswp-width="2048" data-pswp-height="1536" class="group block shadow-lg rounded-2xl overflow-hidden">
                    <div class="photo-wrapper">
                        <img src="<?= $url ?>=w600" class="smart-img" onload="doSmartCrop(this)">
                    </div>
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

        window.doSmartCrop = function(img) {
            const container = img.parentElement;
            smartcrop.crop(img, { width: container.offsetWidth, height: container.offsetHeight }).then(result => {
                const crop = result.topCrop;
                const scale = container.offsetWidth / crop.width;
                img.style.width = (img.naturalWidth * scale) + 'px';
                img.style.height = (img.naturalHeight * scale) + 'px';
                img.style.left = (-crop.x * scale) + 'px';
                img.style.top = (-crop.y * scale) + 'px';
                img.style.opacity = "1";
            });
        };
    </script>
</body>
</html>