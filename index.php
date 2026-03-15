<?php
require_once 'config.php';

// Forceer een snelle reactie
$albumUrl = isset($_GET['album']) ? $_GET['album'] : "https://goo.gl/photos/H5nmVV473rJw1d6aA";
$cacheFile = '/tmp/cache_' . md5($albumUrl) . '.json';

$photos = [];
$albumTitle = "Laden...";

// Alleen uit cache laden! Als er geen cache is, doen we een 'silent' update.
if (file_exists($cacheFile)) {
    $data = json_decode(file_get_contents($cacheFile), true);
    $photos = $data['photos'];
    $albumTitle = $data['title'];
} 

// Als er geen foto's zijn, of de cache is oud, triggeren we een achtergrond-fetch
// Dit voorkomt de 504 gateway timeout omdat de pagina direct rendert
if (empty($photos) || (time() - filemtime($cacheFile) > 3600)) {
    // We tonen een melding dat we de foto's ophalen
    $needsUpdate = true;
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
    <?php if (empty($photos)): ?>
    <div class="flex flex-col items-center justify-center min-h-[50vh]">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mb-4"></div>
        <p class="text-gray-500 font-medium">We halen de familieherinneringen op, momentje...</p>
        <script>
            // Herlaad de pagina automatisch na 5 seconden om te kijken of de cache klaar is
            setTimeout(() => { window.location.reload(); }, 5000);
        </script>
    </div>
    <?php 
        // Start de fetch in de achtergrond (dit is de 'crazy' fix)
        // We roepen een klein verborgen script aan dat de cache vult
        echo "<img src='update_cache.php?url=".urlencode($albumUrl)."' class='hidden'>";
    ?>
<?php endif; ?>

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