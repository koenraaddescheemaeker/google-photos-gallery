<?php
/** * FORCEKES - sync-media.php (Fase 9: Dual-Sync Thumbnails) */
require_once 'config.php';

// Match de 1-uur limiet van de Docker & Cron
set_time_limit(3600);
ini_set('memory_limit', '512M');
ignore_user_abort(true);

$isCron = (php_sapi_name() === 'cli' || isset($_GET['cron']));
$limit = $isCron ? 200 : 10; // Iets lagere limiet omdat we nu 2 bestanden per item verwerken

echo "<h2>MIGRATOR <span style='color:#3b82f6;'>DUAL-SYNC</span></h2>";
echo "<p>Status: <strong>" . ($isCron ? "Automatisatie" : "Handmatige Batch") . "</strong></p>";

// Haal items op waar de image_url OF de thumbnail_url nog naar Google wijst
$items = supabaseRequest("album_photos?or=(image_url.not.like.*supabase.co*,thumbnail_url.not.like.*supabase.co*)&limit=$limit", 'GET');

if (is_array($items) && count($items) > 0) {
    foreach ($items as $item) {
        $id = $item['id'];
        $category = trim($item['category'] ?? 'ongecategoriseerd');
        $updateData = [];

        echo "<div style='margin-bottom:10px; padding:10px; border-left:3px solid #3b82f6; background:#111;'>";
        echo "Verwerken ID: <strong>$id</strong> ($category)<br>";

        // --- 1. HOOFDFOTO SYNC ---
        if (strpos($item['image_url'], 'supabase.co') === false) {
            echo "-> Hoofdfoto downloaden... ";
            $imgData = downloadFile($item['image_url']);
            if ($imgData) {
                $ext = (strpos($imgData['mime'], 'video') !== false) ? '.mp4' : '.jpg';
                $path = $category . '/' . $id . $ext;
                $newUrl = uploadToSupabase($path, $imgData['data'], $imgData['mime']);
                if ($newUrl) {
                    $updateData['image_url'] = $newUrl;
                    echo "<span style='color:green;'>OK</span><br>";
                }
            } else { echo "<span style='color:red;'>Mislukt</span><br>"; }
        }

        // --- 2. THUMBNAIL SYNC ---
        if (strpos($item['thumbnail_url'], 'supabase.co') === false && !empty($item['thumbnail_url'])) {
            echo "-> Thumbnail downloaden... ";
            $thumbData = downloadFile($item['thumbnail_url']);
            if ($thumbData) {
                // Thumbnails slaan we op in een submap 'thumbnails'
                $thumbPath = 'thumbnails/' . $category . '/' . $id . '.jpg';
                $newThumbUrl = uploadToSupabase($thumbPath, $thumbData['data'], 'image/jpeg');
                if ($newThumbUrl) {
                    $updateData['thumbnail_url'] = $newThumbUrl;
                    echo "<span style='color:green;'>OK</span><br>";
                }
            } else { echo "<span style='color:red;'>Mislukt</span><br>"; }
        }

        // --- 3. DATABASE BIJWERKEN ---
        if (!empty($updateData)) {
            supabaseRequest("album_photos?id=eq.$id", "PATCH", $updateData);
        }
        echo "</div>";
        
        usleep(100000); // 0.1s rust
    }

    if (!$isCron) {
        echo "<script>setTimeout(() => { window.location.reload(); }, 2000);</script>";
        echo "<p>Batch voltooid. Volgende reeks start over 2 seconden...</p>";
    }
} else {
    echo "<h3 style='color:green;'>✓ Alle hoofdfoto's én thumbnails staan veilig in de kluis.</h3>";
}

/**
 * Helper: Bestand downloaden met User-Agent
 */
function downloadFile($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $data = curl_exec($ch);
    $mime = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);
    
    return ($data && strlen($data) > 500) ? ['data' => $data, 'mime' => $mime] : false;
}

/**
 * Helper: Uploaden naar Supabase Storage
 */
function uploadToSupabase($path, $data, $mime) {
    $uploadUrl = SUPABASE_URL . '/storage/v1/object/familie-media/' . rawurlencode($path);
    $ch = curl_init($uploadUrl);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . SUPABASE_SERVICE_KEY,
        'Content-Type: ' . $mime,
        'x-upsert: true'
    ]);
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 || $httpCode === 201) {
        return SUPABASE_URL . '/storage/v1/object/public/familie-media/' . $path;
    }
    return false;
}