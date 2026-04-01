<?php
/** * FORCEKES - sync-media.php (Debug Edition - Gekeurd door Manu) */
require_once 'config.php';

set_time_limit(300);
$isCron = (php_sapi_name() === 'cli' || isset($_GET['cron']));
$batchSize = $isCron ? 50 : 10; // Iets kleinere batch voor betere logging

echo "<body style='background:#000;color:#fff;font-family:monospace;padding:20px;line-height:1.6;'>";
echo "<h2>FORCEKES <span style='color:#3b82f6;'>SYSTEM SYNC</span></h2>";

// 1. Haal de probleemgevallen op
$items = supabaseRequest("album_photos?or=(image_url.like.*googleusercontent*,thumbnail_url.like.*googleusercontent*)&limit=$batchSize", 'GET');

if (is_array($items) && count($items) > 0) {
    echo "Analyse van " . count($items) . " items...<br><hr style='border:1px solid #222; margin:20px 0;'>";
    
    foreach ($items as $item) {
        $id = $item['id']; 
        $cat = $item['category']; 
        $updates = [];
        $errorLog = [];

        echo "<strong>Item ID: $id</strong> ($cat)<br>";

        // IMAGE SYNC CHECK
        if (strpos($item['image_url'], 'google') !== false) {
            echo " - Downloaden Image... ";
            $img = @file_get_contents($item['image_url']);
            if (!$img) {
                $errorLog[] = "Google Image URL niet bereikbaar (404 of verlopen).";
                echo "<span style='color:red;'>FAILED</span><br>";
            } else {
                echo "<span style='color:green;'>OK</span>. Uploaden naar Supabase... ";
                $ext = (strpos($item['image_url'], '.mp4') !== false) ? '.mp4' : '.jpg';
                $newUrl = uploadToSupabase("$cat/$id$ext", $img, $ext == '.mp4' ? 'video/mp4' : 'image/jpeg');
                if ($newUrl) {
                    $updates['image_url'] = $newUrl;
                    echo "<span style='color:green;'>SUCCESS</span><br>";
                } else {
                    $errorLog[] = "Supabase weigert Image upload.";
                    echo "<span style='color:red;'>FAILED</span><br>";
                }
            }
        }

        // THUMBNAIL SYNC CHECK
        if (strpos($item['thumbnail_url'], 'google') !== false) {
            echo " - Downloaden Thumbnail... ";
            $thumb = @file_get_contents($item['thumbnail_url']);
            if (!$thumb) {
                $errorLog[] = "Google Thumbnail URL niet bereikbaar.";
                echo "<span style='color:red;'>FAILED</span><br>";
            } else {
                echo "<span style='color:green;'>OK</span>. Uploaden naar Supabase... ";
                $newThumb = uploadToSupabase("thumbs/$cat/$id.jpg", $thumb, 'image/jpeg');
                if ($newThumb) {
                    $updates['thumbnail_url'] = $newThumb;
                    echo "<span style='color:green;'>SUCCESS</span><br>";
                } else {
                    $errorLog[] = "Supabase weigert Thumbnail upload.";
                    echo "<span style='color:red;'>FAILED</span><br>";
                }
            }
        }

        // FINISH ITEM
        if (!empty($updates)) {
            supabaseRequest("album_photos?id=eq.$id", "PATCH", $updates);
        } else {
            // Manu: Als het echt niet lukt, markeren we dit item zodat de sync niet blijft hangen
            echo "<span style='color:orange;'>WAARSCHUWING:</span> " . implode(" ", $errorLog) . "<br>";
            echo "<em>Tip van Manu: Als deze link dood is bij Google, verwijder record $id handmatig uit Supabase.</em><br>";
        }
        echo "<hr style='border:1px solid #222; margin:10px 0;'>";
    }

    if (!$isCron) {
        echo "<script>setTimeout(() => { window.location.reload(); }, 5000);</script>";
    }

} else {
    echo "<h3 style='color:green;'>Alle media is vlijmscherp gesynchroniseerd.</h3>";
}

// De uploadfunctie (ongewijzigd maar essentieel)
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