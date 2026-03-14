<?php
// De link naar jouw gedeelde album
$albumUrl = "https://photos.app.goo.gl/vVky35x2foq6Lpv26";
$content = @file_get_contents($albumUrl);
if (!$content) { die("Kon het album niet laden."); }

// Albumtitel ophalen
preg_match('/<meta property="og:title" content="([^"]+)">/', $content, $titleMatches);
$albumTitle = isset($titleMatches[1]) ? str_replace(" - Google Photos", "", $titleMatches[1]) : "Mijn Foto's";
if ($albumTitle == "Google Photos") $albumTitle = "Gedeelde Herinneringen";

// Foto's ophalen
preg_match_all('/https:\/\/lh3\.googleusercontent\.com\/pw\/[a-zA-Z0-9\-_]+/', $content, $matches);
$photos = array_unique($matches[0]);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($albumTitle) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/photoswipe/dist/photoswipe.css">
    <script src="https://unpkg.com/smartcrop@2.0.5/smartcrop.js"></script>
    <style>
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        /* Container voor de intelligente crop */
        .photo-wrapper {
            position: relative;
            height: 300px; 
            width: 100%;
            overflow: hidden;
            border-radius: 1rem;
        }
        .smart-img {
            position: absolute;
            max-width: none;
            opacity: 0;
            transition: opacity 0.5s ease-in;
        }
        .pswp__img { object-fit: contain !important; }
    </style>
</head>
<?php include 'menu.php'; ?>
<body class="bg-gray-50 text-gray-900">

    <div class="max-w-7xl mx-auto px-4 py-12">
        <header class="mb-12 text-center">
            <h1 class="text-5xl font-black tracking-tight text-gray-900"><?= htmlspecialchars($albumTitle) ?></h1>
            <div class="mt-4 h-1 w-20 bg-indigo-600 mx-auto rounded-full"></div>
        </header>

        <div class="gallery-grid" id="my-gallery">
            <?php foreach ($photos as $url): ?>
                <a href="<?= $url ?>=w2048" 
                   data-pswp-width="2048" 
                   data-pswp-height="1536" 
                   class="group block bg-gray-200 shadow-lg hover:shadow-2xl transition-all duration-300 rounded-2xl">
                    
                    <div class="photo-wrapper">
                        <img src="<?= $url ?>=w800" 
                             alt="Foto" 
                             class="smart-img"
                             onload="doSmartCrop(this)">
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

        // Intelligente centrering functie
        window.doSmartCrop = function(img) {
            const container = img.parentElement;
            smartcrop.crop(img, { width: container.offsetWidth, height: container.offsetHeight }).then(function(result) {
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