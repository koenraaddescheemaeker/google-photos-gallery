<?php
/** * FORCEKES - sync-media.php (Auto-Batch Edition - Gekeurd door Manu) */
require_once 'config.php';

// 1. Instellingen & Detectie
set_time_limit(300); // 5 minuten per run
$isCron = (php_sapi_name() === 'cli' || isset($_GET['cron']));
$batchSize = $isCron ? 50 : 20; // Cron mag iets harder werken

echo "<body style='background:#000;color:#fff;font-family:monospace;padding:20px;line-height:1.6;'>";
echo "<h2>FORCEKES <span style='color:#3b82f6;'>SYSTEM SYNC</span></h2>";
echo "Modus: " . ($isCron ? "<span style='color:orange;'>AUTOMATISCH (CRON)</span>" : "<span style='color:green;'>INTERACTIEF (BROWSER)</span>") . "<br><br>";

// 2. EERST: De Achtergrondvideo Assets (Eenmalig)
syncGlobalAssets();

// 3. DAN: De Media Batch (Thumbnails & Images)
$items = supabaseRequest("album_photos?or=(image_url.like.*googleusercontent*,thumbnail_url.like.*googleusercontent*)&limit=$batchSize", 'GET');

if (is_array($items) && count($items) > 0) {
    echo "Bezig met batch van " . count($items) . " items...<br>";
    
    foreach ($items as $item) {
        $id = $item['id']; 
        $cat = $item['category']; 
        $updates = [];

        // Image Sync
        if (strpos($item['image_url'], 'google') !== false) {
            $img = @file_get_contents($item['image_url']);
            if ($img) {
                $ext = (strpos($item['image_url'], '.mp4') !== false) ? '.mp4' : '.jpg';
                $newUrl = uploadToSupabase("$cat/$id$ext", $img, $ext == '.mp4' ? 'video/mp4' : 'image/jpeg');
                if ($newUrl) $updates['image_url'] = $newUrl;
            }
        }

        // Thumbnail Sync
        if (strpos($item['thumbnail_url'], 'google') !== false) {
            $thumb = @file_get_contents($item['thumbnail_url']);
            if ($thumb) {
                $newThumb = uploadToSupabase("thumbs/$cat/$id.jpg", $thumb, 'image/jpeg');
                if ($newThumb) $updates['thumbnail_url'] = $newThumb;
            }
        }

        if (!empty($updates)) {
            supabaseRequest("album_photos?id=eq.$id", "PATCH", $updates);
            echo "<span style='color:#3b82f6;'>[v]</span> $id verwerkt.<br>";
        } else {
            echo "<span style='color:red;'>[x]</span> $id mislukt.<br>";
        }
    }

    // 4. De KISS-oplossing voor doorgaan
    if (!$isCron) {
        // Voor de mens: Automatische refresh via JS
        echo "<p style='color:green;'>Batch klaar. Volgende batch start over 2 seconden...</p>";
        echo "<script>setTimeout(() => { window.location.reload(); }, 2000);</script>";
    } else {
        // Voor de Cron: We stoppen hier. De Cron-taak in Coolify zal het script 
        // simpelweg over een paar minuten weer opnieuw aanroepen.
        echo "Cron-cyclus voltooid.";
    }

} else {
    echo "<h3 style='color:green;'>[SUCCESS] Alles is vlijmscherp gemigreerd naar Supabase.</h3>";
}

/**
 * Functies (Onder de motorkap)
 */
function syncGlobalAssets() {
    $assetPath = "assets/bg-atmosphere.mp4";
    $checkUrl = SUPABASE_URL . "/storage/v1/object/public/familie-media/" . $assetPath;
    $headers = @get_headers($checkUrl);
    if (!$headers || strpos($headers[0], '404') !== false) {
        echo "Achtergrondvideo ophalen... ";
        $video = @file_get_contents("https://www.w3schools.com/howto/rain.mp4");
        if ($video) {
            uploadToSupabase($assetPath, $video, 'video/mp4');
            echo "<span style='color:green;'>Klaar</span><br>";
        }
    }
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
?>