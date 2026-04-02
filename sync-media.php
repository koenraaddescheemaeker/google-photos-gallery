<?php
/** * FORCEKES - sync-media.php (Ownership Edition - Gekeurd door Manu) */
require_once 'config.php';
set_time_limit(600); 
$isCron = (php_sapi_name() === 'cli' || isset($_GET['cron']));

echo "<body style='background:#000;color:#fff;font-family:monospace;padding:20px;line-height:1.6;'>";
echo "<h2>FORCEKES <span style='color:#3b82f6;'>MEDIA ENGINE</span></h2>";

// --- FASE 1: NIEUWE ALBUMS INGESTEN ---
$newAlbums = supabaseRequest("album_settings?google_link=not.is.null", 'GET');

if (is_array($newAlbums)) {
    foreach ($newAlbums as $album) {
        $slug = $album['slug'];
        $link = $album['google_link'];
        $owner = $album['created_by'] ?? 'koen@lauwe.com'; // Manu: Hier leggen we de eigenaar vast
        
        $check = supabaseRequest("album_photos?category=eq." . rawurlencode($slug) . "&limit=1", 'GET');
        
        if (empty($check)) {
            echo "Inlezen: <strong>$slug</strong> (Eigenaar: $owner)...<br>";
            importPhotosFromGoogle($link, $slug, $owner);
        }
    }
}

// --- FASE 2: MIGRATIE ---
$items = supabaseRequest("album_photos?or=(image_url.like.*googleusercontent*,thumbnail_url.like.*googleusercontent*)&limit=20", 'GET');

if (is_array($items) && count($items) > 0) {
    foreach ($items as $item) {
        $id = $item['id']; $cat = $item['category']; $updates = [];
        syncFile($item['image_url'], $id, $cat, 'image_url', $updates);
        syncFile($item['thumbnail_url'], $id, $cat, 'thumbnail_url', $updates);
        if (!empty($updates)) supabaseRequest("album_photos?id=eq.$id", "PATCH", $updates);
    }
    if (!$isCron) echo "<script>setTimeout(() => { window.location.reload(); }, 2000);</script>";
} else {
    echo "<h3 style='color:green;'>[v] Systeem is up-to-date.</h3>";
}

function importPhotosFromGoogle($url, $slug, $owner) {
    $html = @file_get_contents($url);
    if (!$html) return;
    preg_match_all('/"(https:\/\/lh3\.googleusercontent\.com\/pw\/[a-zA-Z0-9\-_]+)"/', $html, $matches);
    $urls = array_unique($matches[1] ?? []);
    foreach ($urls as $googleUrl) {
        supabaseRequest("album_photos", "POST", [
            "category" => $slug,
            "image_url" => $googleUrl . "=w2048-h2048",
            "thumbnail_url" => $googleUrl . "=w400-h400",
            "owner_email" => $owner, // Manu: Vlijmscherp toegewezen
            "is_visible" => true
        ]);
    }
}

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
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.SUPABASE_SERVICE_KEY, 'Content-Type: '.$mime, 'x-upsert: true']);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($code == 200 || $code == 201) ? SUPABASE_URL . "/storage/v1/object/public/familie-media/" . $path : false;
}
?>