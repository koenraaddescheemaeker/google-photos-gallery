<?php
/** FORCEKES - sync-media.php (Master Sync, Metadata & Cleanup) */
require_once 'config.php';
set_time_limit(0); 

// Voeg de Service Role Key toe voor Storage administratie
$serviceRoleKey = getenv('SUPABASE_SERVICE_ROLE_KEY') ?: 'JOUW_SERVICE_ROLE_KEY_HIER';

echo "<body style='background:#000;color:#4ade80;font-family:monospace;padding:20px;line-height:1.5;'>";
echo "<div style='max-width:900px;margin:0 auto;'>";
echo "<header style='border-bottom:1px solid #222;padding-bottom:20px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;'>";
echo "<h2 style='margin:0;letter-spacing:-1px;'>🚀 Media Sync Engine <span style='color:#3b82f6;'>v2.0</span></h2>";
echo "<a href='sync-media.php' style='background:#3b82f6;color:white;text-decoration:none;padding:12px 24px;border-radius:12px;font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:1px;'>Retry Sync</a>";
echo "</header>";

$configs = supabaseRequest('page_configs', 'GET');
if (!$configs) die("❌ Geen album configuraties gevonden in page_configs.");

foreach ($configs as $conf) {
    $slug = $conf['page_slug'];
    $albumUrl = $conf['google_album_id'];
    
    echo "<div style='background:#111;padding:25px;border-radius:24px;margin-bottom:25px;border:1px solid #222;box-shadow:0 10px 30px rgba(0,0,0,0.5);'>";
    echo "<h3 style='color:#3b82f6;margin-top:0;text-transform:uppercase;font-size:14px;'>📂 Album: <span style='color:white;'>$slug</span></h3>";

    // A. Google Scannen
    $content = file_get_contents($albumUrl);
    preg_match_all('/(https:\/\/lh3\.googleusercontent\.com\/pw\/[a-zA-Z0-9\-_]+)=w[0-9]+-h[0-9]+/', $content, $matches);
    $foundGoogleItems = array_unique($matches[1]);
    
    $googleIdsInAlbum = [];
    foreach($foundGoogleItems as $link) { $googleIdsInAlbum[] = md5($link); }

    // B. Database Inventarisatie
    $existingItems = supabaseRequest("album_photos?category=eq.$slug&select=google_id,image_url", 'GET');
    $dbIds = is_array($existingItems) ? array_column($existingItems, 'google_id') : [];
    $dbUrls = is_array($existingItems) ? array_combine(array_column($existingItems, 'google_id'), array_column($existingItems, 'image_url')) : [];

    // C. CLEANUP: Verwijderen wat niet meer in Google staat
    $toDelete = array_diff($dbIds, $googleIdsInAlbum);
    if (!empty($toDelete)) {
        echo "<p style='color:#f87171;font-weight:bold;'>🧹 Opschonen: " . count($toDelete) . " items verwijderen...</p>";
        foreach ($toDelete as $oldId) {
            $storagePath = str_replace(rtrim($supabaseUrl, '/') . "/storage/v1/object/public/familie-media/", "", $dbUrls[$oldId]);
            deleteFromSupabaseStorage($storagePath);
            supabaseRequest("album_photos?google_id=eq.$oldId", 'DELETE');
            echo "<span style='color:#7f1d1d;'>🗑️ $oldId</span><br>";
        }
    }

    // D. IMPORT: Nieuwe media verwerken
    $countNew = 0;
    foreach ($foundGoogleItems as $link) {
        $itemId = md5($link);
        if (!in_array($itemId, $dbIds)) {
            $countNew++;
            processMediaItem($link, $itemId, $slug);
        }
    }

    if ($countNew === 0 && empty($toDelete)) {
        echo "<p style='color:#555;'>✅ Volledig synchroon.</p>";
    } elseif ($countNew > 0) {
        echo "<p style='color:#4ade80;font-weight:bold;'>✨ $countNew nieuwe herinneringen toegevoegd!</p>";
    }
    echo "</div>";
    flush();
}

echo "<footer style='text-align:center;padding:40px;color:#333;font-size:10px;text-transform:uppercase;letter-spacing:2px;'>Forcekes Infrastructure</footer>";
echo "</div></body>";

/** * Hoofdfunctie voor Media Verwerking 
 */
function processMediaItem($link, $itemId, $slug) {
    global $supabaseUrl, $serviceRoleKey;
    $tempFile = "/tmp/raw_$itemId";
    file_put_contents($tempFile, file_get_contents($link));

    $mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $tempFile);
    $finalName = $slug . "_" . $itemId;
    $dbData = ['category' => $slug, 'google_id' => $itemId, 'captured_at' => date('Y-m-d H:i:s')];

    // --- FOTO (WebP + EXIF) ---
    if (strpos($mime, 'image') !== false) {
        $exif = @exif_read_data($tempFile);
        if ($exif && isset($exif['DateTimeOriginal'])) {
            $dbData['captured_at'] = date('Y-m-d H:i:s', strtotime($exif['DateTimeOriginal']));
        }
        $img = imagecreatefromstring(file_get_contents($tempFile));
        $webpPath = "/tmp/$finalName.webp";
        imagewebp($img, $webpPath, 80);
        imagedestroy($img);

        $dbData['image_url'] = uploadToSupabase($webpPath, "$slug/$finalName.webp", 'image/webp');
        @unlink($webpPath);
        echo "📸 <span style='color:#fff;'>Afbeelding verwerkt: " . $dbData['captured_at'] . "</span><br>";
    } 
    // --- VIDEO (WebM + FFprobe) ---
    elseif (strpos($mime, 'video') !== false || $mime == 'application/octet-stream') {
        $creationTime = trim(shell_exec("ffprobe -v quiet -select_streams v:0 -show_entries stream_tags=creation_time -of default=noprint_wrappers=1:nokey=1 $tempFile"));
        if (!empty($creationTime)) {
            $dbData['captured_at'] = date('Y-m-d H:i:s', strtotime($creationTime));
        }
        $webmPath = "/tmp/$finalName.webm";
        exec("ffmpeg -i $tempFile -c:v libvpx -b:v 1.5M -c:a libvorbis $webmPath 2>&1");
        
        $dbData['image_url'] = uploadToSupabase($webmPath, "$slug/$finalName.webm", 'video/webm');
        @unlink($webmPath);
        echo "🎥 <span style='color:#3b82f6;'>Video verwerkt: " . $dbData['captured_at'] . "</span><br>";
    }

    if (isset($dbData['image_url'])) {
        supabaseRequest('album_photos', 'POST', $dbData);
    }
    @unlink($tempFile);
}

function uploadToSupabase($filePath, $destPath, $contentType) {
    global $supabaseUrl, $serviceRoleKey;
    $url = rtrim($supabaseUrl, '/') . "/storage/v1/object/familie-media/" . $destPath;
    $ch = curl_init($url);
    $fp = fopen($filePath, 'r');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => fread($fp, filesize($filePath)),
        CURLOPT_HTTPHEADER => ["Authorization: Bearer $serviceRoleKey", "Content-Type: $contentType", "x-upsert: true"]
    ]);
    curl_exec($ch); curl_close($ch); fclose($fp);
    return rtrim($supabaseUrl, '/') . "/storage/v1/object/public/familie-media/" . $destPath;
}

function deleteFromSupabaseStorage($path) {
    global $supabaseUrl, $serviceRoleKey;
    $url = rtrim($supabaseUrl, '/') . "/storage/v1/object/familie-media/" . $path;
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_CUSTOMREQUEST => "DELETE", CURLOPT_HTTPHEADER => ["Authorization: Bearer $serviceRoleKey"]]);
    curl_exec($ch); curl_close($ch);
}