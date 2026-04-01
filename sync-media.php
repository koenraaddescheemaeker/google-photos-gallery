<?php
/** * FORCEKES - sync-media.php (Inclusief Asset Sync) */
require_once 'config.php';
set_time_limit(3600);

echo "<body style='background:#000;color:#fff;font-family:monospace;padding:40px;'>";
echo "<h2>FORCEKES <span style='color:#3b82f6;'>SYSTEM SYNC</span></h2>";

// 1. ACHTERGROND VIDEO SYNC (KISS: Slechts één keer nodig)
$assetPath = "assets/bg-atmosphere.mp4";
$checkUrl = SUPABASE_URL . "/storage/v1/object/public/familie-media/" . $assetPath;
$file_headers = @get_headers($checkUrl);

if(!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
    echo "Achtergrondvideo ontbreekt. Bezig met binnenhalen... ";
    $videoData = file_get_contents("https://www.w3schools.com/howto/rain.mp4");
    if($videoData) {
        uploadToSupabase($assetPath, $videoData, 'video/mp4');
        echo "<span style='color:green;'>OK</span><br>";
    }
} else {
    echo "Achtergrondvideo is reeds aanwezig in bucket.<br>";
}

// 2. THUMBNAIL & IMAGE SYNC (De rest van je media)
$items = supabaseRequest("album_photos?or=(image_url.like.*googleusercontent*,thumbnail_url.like.*googleusercontent*)&limit=20", 'GET');
if (is_array($items) && count($items) > 0) {
    foreach ($items as $item) {
        $id = $item['id']; $cat = $item['category']; $updates = [];
        echo "Syncing: $id... ";
        
        if (strpos($item['image_url'], 'google') !== false) {
            $img = file_get_contents($item['image_url']);
            if($img) {
                $ext = (strpos($item['image_url'], '.mp4') !== false) ? '.mp4' : '.jpg';
                $newUrl = uploadToSupabase("$cat/$id$ext", $img, $ext == '.mp4' ? 'video/mp4' : 'image/jpeg');
                if($newUrl) $updates['image_url'] = $newUrl;
            }
        }
        
        if (strpos($item['thumbnail_url'], 'google') !== false) {
            $thumb = file_get_contents($item['thumbnail_url']);
            if($thumb) {
                $newThumb = uploadToSupabase("thumbs/$cat/$id.jpg", $thumb, 'image/jpeg');
                if($newThumb) $updates['thumbnail_url'] = $newThumb;
            }
        }

        if(!empty($updates)) {
            supabaseRequest("album_photos?id=eq.$id", "PATCH", $updates);
            echo "DONE<br>";
        }
    }
    echo "<p><a href='sync-media.php' style='color:#3b82f6;'>Volgende batch...</a></p>";
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