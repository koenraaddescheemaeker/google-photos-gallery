<?php
/** * FORCEKES - sync-media.php (Gekeurd door Manu - Agressieve Scraper) */
require_once 'config.php';

// Verleng de tijdslimiet voor grote albums
set_time_limit(900); 
$isCron = (php_sapi_name() === 'cli' || isset($_GET['cron']));

echo "<body style='background:#000;color:#fff;font-family:monospace;padding:30px;line-height:1.6;'>";
echo "<h2 style='border-bottom:1px solid #333; padding-bottom:15px;'>FORCEKES <span style='color:#3b82f6;'>MEDIA ENGINE v2.0</span></h2>";

// --- FASE 1: NIEUWE GOOGLE ALBUMS INGESTEN ---
$newAlbums = supabaseRequest("album_settings?google_link=not.is.null", 'GET');

if (is_array($newAlbums)) {
    foreach ($newAlbums as $album) {
        $slug = $album['slug'];
        $link = $album['google_link'];
        $owner = $album['created_by'] ?? 'koen@lauwe.com';
        
        // We checken of er al foto's zijn voor deze categorie
        $check = supabaseRequest("album_photos?category=eq." . rawurlencode($slug) . "&limit=1", 'GET');
        
        if (empty($check)) {
            echo "<div style='color:#3b82f6; margin-bottom:10px;'>[ENGINE] Nieuw album gedetecteerd: <strong>$slug</strong></div>";
            importPhotosFromGoogle($link, $slug, $owner);
        }
    }
}

// --- FASE 2: MIGRATIE (GOOGLE -> SUPABASE) ---
$items = supabaseRequest("album_photos?or=(image_url.like.*googleusercontent*,thumbnail_url.like.*googleusercontent*)&limit=15", 'GET');

if (is_array($items) && count($items) > 0) {
    echo "<div style='margin-top:20px;'>Bezig met migratie van " . count($items) . " bestanden...</div>";
    foreach ($items as $item) {
        $id = $item['id']; $cat = $item['category']; $updates = [];
        
        syncFile($item['image_url'], $id, $cat, 'image_url', $updates);
        syncFile($item['thumbnail_url'], $id, $cat, 'thumbnail_url', $updates);

        if (!empty($updates)) {
            supabaseRequest("album_photos?id=eq.$id", "PATCH", $updates);
            echo "<span style='color:#4ade80;'>[OK]</span> Item $id succesvol overgezet.<br>";
        }
    }
    // Automatische herlaad in de browser
    if (!$isCron) {
        echo "<p style='color:#666;'>Batch voltooid. Volgende start over 2 seconden...</p>";
        echo "<script>setTimeout(() => { window.location.reload(); }, 2000);</script>";
    }
} else {
    echo "<div style='margin-top:20px; color:#4ade80;'>[SYSTEM] Alle media is vlijmscherp gesynchroniseerd.</div>";
}

/**
 * DE AGRESSIEVE SCRAPER
 */
function importPhotosFromGoogle($url, $slug, $owner) {
    echo "Bezig met ophalen van Google broncode... ";
    
    // Manu: We gebruiken CURL met een User-Agent om een browser te simuleren
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
    $html = curl_exec($ch);
    curl_close($ch);

    if (!$html) {
        echo "<span style='color:#ef4444;'>FAILED (Lege respons)</span><br>";
        return;
    }

    // Manu: Google zet de URL's vaak in een enorme JSON-array in de script-tags.
    // We zoeken naar het specifieke patroon: https://lh3.googleusercontent.com/pw/[code]
    preg_match_all('/"(https:\/\/lh3\.googleusercontent\.com\/pw\/[a-zA-Z0-9\-_]+)"/', $html, $matches);
    
    $urls = array_unique($matches[1] ?? []);
    $count = 0;

    foreach ($urls as $googleUrl) {
        // Filter: We negeren URL's die waarschijnlijk profielfoto's of iconen zijn (kortere strings)
        if (strlen($googleUrl) > 60) {
            supabaseRequest("album_photos", "POST", [
                "category" => $slug,
                "image_url" => $googleUrl . "=w2400", // Full resolution
                "thumbnail_url" => $googleUrl . "=w500", // Thumbnail
                "owner_email" => $owner,
                "is_visible" => true
            ]);
            $count++;
        }
    }
    
    if ($count > 0) {
        echo "<span style='color:#4ade80;'>SUCCESS ($count foto's gevonden)</span><br>";
    } else {
        echo "<span style='color:#ef4444;'>FAILED (0 foto's gevonden). Is de link openbaar?</span><br>";
    }
}

/**
 * BESTAND SYNC LOGICA
 */
function syncFile($url, $id, $cat, $field, &$updates) {
    if (strpos($url, 'google') !== false) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
        $data = curl_exec($ch);
        curl_close($ch);

        if ($data) {
            $isThumb = ($field === 'thumbnail_url');
            $ext = (strpos($url, '.mp4') !== false) ? '.mp4' : '.jpg';
            $folder = $isThumb ? "thumbs/" : "";
            $path = $folder . "$cat/$id$ext";
            
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
?>