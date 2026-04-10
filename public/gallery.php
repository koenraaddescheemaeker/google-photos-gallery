<?php

require_once __DIR__ . '/../src/gallery.php';

$id = $_GET['id'] ?? 1;
$type = ($id == 1) ? 'museum' : 'albums';

$photos = getPhotos($type);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Forcekes Gallery</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white">

<div class="p-6 flex justify-between items-center">
    <h1 class="text-2xl">
        <?= $type === 'museum' ? 'Museum' : 'Albums' ?>
    </h1>

    <div class="space-x-4">
        <a href="/gallery.php?id=1" class="underline">Museum</a>
        <a href="/gallery.php?id=2" class="underline">Albums</a>
    </div>
</div>

<div class="p-6 grid grid-cols-2 md:grid-cols-4 gap-4">
    <?php foreach ($photos as $photo): ?>
        <a href="<?= $photo['image_url'] ?>" target="_blank">
            <img 
                src="<?= $photo['thumbnail_url'] ?>" 
                class="w-full h-48 object-cover rounded-lg hover:scale-105 transition"
                loading="lazy"
            />
        </a>
    <?php endforeach; ?>
</div>

</body>
</html>