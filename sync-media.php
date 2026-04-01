<?php
/** * FORCEKES - sync-media.php (Import & Migratie Edition - Gekeurd door Manu) */
require_once 'config.php';

set_time_limit(600); 
$isCron = (php_sapi_name() === 'cli' || isset($_GET['cron']));

echo "<body style='background:#000;color:#fff;font-family:monospace;padding:20px;line-height:1.6;'>";
echo "<h2>FORCEKES <span style='color:#3b82f6;'>MEDIA ENGINE</span></h2>";

// --- FASE 1: NIEUWE GOOGLE ALBUMS INGESTEN ---
$newAlbums = supabaseRequest("album_settings?google_link=not.is.null", 'GET');

if (is_array($newAlbums)) {
    foreach ($newAlbums as $album) {
        $slug = $album['slug'];
        $link = $album['google_link'];
        
        // Check of we dit album al eens hebben ingelezen (KISS: we checken of er al foto's zijn)
        $check = supabaseRequest("album_photos?category=eq.$slug&limit=1", 'GET');
        
        if (empty($check)) {
            echo "Nieuw album gedetecteerd: <strong>$slug</strong>. Bezig met inlezen van Google...<br>";
            importPhotosFromGoogle($link, $slug);
        }
    }
}

// --- FASE 2: MEDIA MIGREREN (GOOGLE -> SUPABASE) ---
$items = supabaseRequest("album_photos?or=(image_url.like.*googleusercontent*,thumbnail_url.like.*googleusercontent*)&limit=20", 'GET');

if (is_array($items) && count($items) > 0) {
    echo "<br>Bezig met migratie van " . count($items) . " bestanden naar Supabase...<br>";
    foreach ($items as $item) {
        $id = $item['id']; $cat = $item['category']; $updates = [];
        
        // Image & Thumbnail Sync
        syncFile($item['image_url'], $id, $cat, 'image_url', $updates);
        syncFile($item['thumbnail_url'], $id, $cat, 'thumbnail_url', $updates);

        if (!empty($updates)) {
            supabaseRequest("album_photos?id=eq.$id", "PATCH", $updates);
            echo "<span style='color:#3b82f6;'>[v]</span> Item $id gemigreerd.<br>";
        }
    }
    if (!$isCron) echo "<script>setTimeout(() => { window.location.reload(); }, 2000);</script>";
} else {
    echo "<h3 style='color:green;'>[v] Alle media is veilig opgeslagen in je eigen kluis.</h3>";
}

/**
 * HELPER: Scrape Google Shared Album
 */
function importPhotosFromGoogle($url, $slug) {
    $html = @file_get_contents($url);
    if (!$html) return;

    // Manu's vlijmscherpe Regex voor Google Photos Shared Links
    // We zoeken naar de specifieke patronen van afbeeldings-URL's in de broncode
    preg_match_all('/"(https:\/\/lh3\.googleusercontent\.com\/pw\/[a-zA-Z0-9\-_]+)"/', $html, $matches);
    
    $urls = array_unique($matches[1] ?? []);
    $count = 0;

    foreach ($urls as $googleUrl) {
        // We filteren op de 'grote' versies (vaak eindigend op =w of =h)
        // en voegen ze toe aan de database
        supabaseRequest("album_photos", "POST", [
            "category" => $slug,
            "image_url" => $googleUrl . "=w2048-h2048", // Forceer hoge resolutie
            "thumbnail_url" => $googleUrl . "=w400-h400", // Forceer thumbnail
            "is_visible" => true,
            "owner_email" => "koen@lauwe.com"
        ]);
        $count++;
    }
    echo "<span style='color:green;'>[v] $count foto's gevonden en klaargezet voor migratie.</span><br>";
}

/**
 * HELPER: Sync een individueel bestand
 */
function syncFile($url, $id, $cat, $field, &$updates) {
    if (strpos($url, 'google') !== false) {
        $data = @file_get_contents($url);
        if ($data) {
            $isThumb = ($field === 'thumbnail_url');
            $ext = (strpos($url, '.mp4') !== false) ? '.mp4' : '.jpg';
            $path = ($isThumb ? "thumbs/" : "") . "$cat/$id$ext";
            $newUrl = uploadToSupabase($path, $data, ($ext == '.mp4' ? 'video/mp4' : 'image/jpeg'));
            if ($newUrl) $updates[$field] = $newUrl;
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