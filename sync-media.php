<?php
/** * FORCEKES - sync-media.php (Fase 9: De Grote URL-Vervanger) */
require_once 'config.php';

// We geven de uil een vol uur de tijd voor deze grote migratie
set_time_limit(3600);
ini_set('memory_limit', '512M');
ignore_user_abort(true);

$isCron = (php_sapi_name() === 'cli' || isset($_GET['cron']));
// We pakken een batch van 15 per keer in de browser om de voortgang te zien
$limit = $isCron ? 250 : 15; 

echo "<h2>ARCHIEF <span style='color:#3b82f6;'>REINIGING</span></h2>";
echo "<p>Status: <strong>" . ($isCron ? "Automatische Overschrijving" : "Handmatige Batch") . "</strong></p>";

/**
 * We zoeken items waar:
 * 1. De image_url nog GEEN 'supabase.co' bevat
 * OF
 * 2. De thumbnail_url nog GEEN 'supabase.co' bevat
 */
$items = supabaseRequest("album_photos?or=(image_url.not.like.*supabase.co*,thumbnail_url.not.like.*supabase.co*)&limit=$limit", 'GET');

if (is_array($items) && count($items) > 0) {
    foreach ($items as $item) {
        $id = $item['id'];
        $category = trim($item['category'] ?? 'ongecategoriseerd');
        $updateData = [];

        echo "<div style='margin-bottom:10px; padding:15px; border-radius:15px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); font-family:sans-serif; font-size:12px;'>";
        echo "Controleer item: <strong>$id</strong> in <em>$category</em><br>";

        // --- 1. VERVANG HOOFDFOTO ---
        if (strpos($item['image_url'], 'supabase.co') === false) {
            echo "⚡ Google Foto link gedetecteerd. Migreren... ";
            $imgData = downloadFile($item['image_url']);
            if ($imgData) {
                $ext = (strpos($imgData['mime'], 'video') !== false) ? '.mp4' : '.jpg';
                $path = $category . '/' . $id . $ext;
                $newUrl = uploadToSupabase($path, $imgData['data'], $imgData['mime']);
                if ($newUrl) {
                    $updateData['image_url'] = $newUrl;
                    echo "<span style='color:green;'>VERVANGEN ✓</span><br>";
                }
            } else { echo "<span style='color:red;'>FOUT</span><br>"; }
        }

        // --- 2. VERVANG THUMBNAIL ---
        if (strpos($item['thumbnail_url'], 'supabase.co') === false && !empty($item['thumbnail_url'])) {
            echo "⚡ Google Thumbnail link gedetecteerd. Migreren... ";
            $thumbData = downloadFile($item['thumbnail_url']);
            if ($thumbData) {
                // We slaan thumbnails op in een overzichtelijke submap
                $thumbPath = 'thumbnails/' . $category . '/' . $id . '.jpg';
                $newThumbUrl = uploadToSupabase($thumbPath, $thumbData['data'], 'image/jpeg');
                if ($newThumbUrl) {
                    $updateData['thumbnail_url'] = $newThumbUrl;
                    echo "<span style='color:green;'>VERVANGEN ✓</span><br>";
                }
            } else { echo "<span style='color:red;'>FOUT</span><br>"; }
        }

        // --- 3. DATABASE UPDATE (De daadwerkelijke vervanging) ---
        if (!empty($updateData)) {
            supabaseRequest("album_photos?id=eq.$id", "PATCH", $updateData);
            echo "<p style='color:#3b82f6; font-weight:bold;'>Database succesvol bijgewerkt voor dit archiefstuk.</p>";
        } else {
            echo "Geen wijzigingen nodig.";
        }
        echo "</div>";
    }

    if (!$isCron) {
        echo "<script>setTimeout(() => { window.location.reload(); }, 1500);</script>";
        echo "<p>Batch voltooid. De uil zoekt de volgende reeks...</p>";
    }
} else {
    echo "<h3 style='color:green;'>✓ Missie Voltooid: Alle Google-links zijn definitief verbannen uit het portaal.</h3>";
}

/**
 * HELPERS
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
    return ($httpCode === 200 || $httpCode === 201) ? SUPABASE_URL . '/storage/v1/object/public/familie-media/' . $path : false;
}