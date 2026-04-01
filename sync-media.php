<?php
/** * FORCEKES - sync-media.php (De Grote Thumbnail Migratie) */
require_once 'config.php';
set_time_limit(36000); // Geef de uil de tijd

echo "<body style='background:#000;color:#fff;font-family:monospace;padding:40px;'>";
echo "<h2>FORCEKES <span style='color:#3b82f6;'>MEDIA SYNC</span></h2>";

// Zoek items waar image of thumbnail nog naar Google wijst
$items = supabaseRequest("album_photos?or=(image_url.like.*googleusercontent*,thumbnail_url.like.*googleusercontent*)&limit=20", 'GET');

if (is_array($items) && count($items) > 0) {
    foreach ($items as $item) {
        $id = $item['id'];
        $cat = $item['category'];
        $updates = [];

        echo "Verwerken: $id ($cat)... ";

        // 1. Image Sync
        if (strpos($item['image_url'], 'googleusercontent') !== false) {
            $img = file_get_contents($item['image_url']);
            if ($img) {
                $ext = (strpos($item['image_url'], '.mp4') !== false) ? '.mp4' : '.jpg';
                $path = $cat . "/" . $id . $ext;
                $newUrl = uploadToSupabase($path, $img, $ext == '.mp4' ? 'video/mp4' : 'image/jpeg');
                if ($newUrl) $updates['image_url'] = $newUrl;
            }
        }

        // 2. Thumbnail Sync (KISS: We slaan ze op in een submap /thumbs)
        if (strpos($item['thumbnail_url'], 'googleusercontent') !== false) {
            $thumb = file_get_contents($item['thumbnail_url']);
            if ($thumb) {
                $thumbPath = "thumbs/" . $cat . "/" . $id . ".jpg";
                $newThumb = uploadToSupabase($thumbPath, $thumb, 'image/jpeg');
                if ($newThumb) $updates['thumbnail_url'] = $newThumb;
            }
        }

        if (!empty($updates)) {
            supabaseRequest("album_photos?id=eq.$id", "PATCH", $updates);
            echo "<span style='color:#3b82f6;'>SUCCESS</span><br>";
        } else {
            echo "<span style='color:red;'>FAILED</span><br>";
        }
    }
    echo "<p>Batch voltooid. <a href='sync-media.php' style='color:#3b82f6;'>Klik hier voor de volgende 20</a></p>";
} else {
    echo "<h3 style='color:green;'>Alles is gemigreerd! Het archief is 100% onafhankelijk.</h3>";
}

function uploadToSupabase($path, $data, $mime) {
    $url = SUPABASE_URL . "/storage/v1/object/familie-media/" . rawurlencode($path);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . SUPABASE_SERVICE_KEY,
        'Content-Type: ' . $mime,
        'x-upsert: true'
    ]);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($code == 200 || $code == 201) ? SUPABASE_URL . "/storage/v1/object/public/familie-media/" . $path : false;
}