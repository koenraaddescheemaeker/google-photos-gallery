<?php
/** FORCEKES - sync-media.php (Master Scraper & Processor) */
require_once 'config.php';
set_time_limit(0); // Voorkom dat het script stopt bij grote video's

echo "<body style='background:#000;color:#4ade80;font-family:monospace;padding:20px;line-height:1.5;'>";
echo "<h2>🚀 Forcekes Media Processor gestart...</h2>";

// 1. Haal de album configuraties op
$configs = supabaseRequest('page_configs', 'GET');

if (!$configs) die("❌ Geen album configuraties gevonden.");

foreach ($configs as $conf) {
    $slug = $conf['page_slug'];
    $albumUrl = $conf['google_album_id'];
    
    echo "<h3>📂 Verwerken: $slug...</h3>";

    // Google Album Scrapen
    $content = file_get_contents($albumUrl);
    // Regex om Google Media links te vinden (vereenvoudigd voor deze dialoog)
    preg_match_all('/(https:\/\/lh3\.googleusercontent\.com\/pw\/[a-zA-Z0-9\-_]+)=w[0-9]+-h[0-9]+/', $content, $matches);
    $mediaLinks = array_unique($matches[1]);

    echo "Found " . count($mediaLinks) . " items.<br>";

    foreach ($mediaLinks as $index => $link) {
        $itemId = md5($link); // Unieke ID op basis van de link
        $tempFile = "/tmp/raw_$itemId";
        
        // Overslaan als we deze al hebben (optioneel, maar voor nu doen we vers)
        echo "⬇️ Downloading item " . ($index + 1) . "... ";
        file_put_contents($tempFile, file_get_contents($link));

        // Detecteer type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tempFile);
        finfo_close($finfo);

        $finalName = $slug . "_" . $itemId;
        $dbData = ['category' => $slug, 'google_id' => $itemId];

        if (strpos($mime, 'image') !== false) {
            // --- FOTO VERWERKING (WebP) ---
            $img = imagecreatefromstring(file_get_contents($tempFile));
            $webpPath = "/tmp/$finalName.webp";
            imagewebp($img, $webpPath, 80); // 80% kwaliteit
            imagedestroy($img);

            $uploadName = "$slug/$finalName.webp";
            $publicUrl = uploadToSupabase($webpPath, $uploadName, 'image/webp');
            
            $dbData['image_url'] = $publicUrl;
            $dbData['thumbnail_url'] = $publicUrl; // Voor nu hetzelfde
            echo "✅ Foto omgezet naar WebP en geüpload.<br>";
            
            @unlink($webpPath);
        } 
        elseif (strpos($mime, 'video') !== false || $mime == 'application/octet-stream') {
            // --- VIDEO VERWERKING (WebM) ---
            echo "🎥 Video gedetecteerd. Starten FFmpeg... ";
            $webmPath = "/tmp/$finalName.webm";
            
            // FFmpeg commando: snelle conversie naar WebM
            $cmd = "ffmpeg -i $tempFile -c:v libvpx -b:v 1M -c:a libvorbis $webmPath 2>&1";
            exec($cmd, $output, $returnVar);

            if ($returnVar === 0) {
                $uploadName = "$slug/$finalName.webm";
                $publicUrl = uploadToSupabase($webmPath, $uploadName, 'video/webm');
                $dbData['image_url'] = $publicUrl;
                $dbData['thumbnail_url'] = ""; // Eventueel een frame pakken met ffmpeg later
                echo "✅ Video omgezet naar WebM en geüpload.<br>";
            } else {
                echo "❌ FFmpeg fout: " . implode(" ", $output) . "<br>";
            }
            @unlink($webmPath);
        }

        // Opslaan in Database
        if (isset($dbData['image_url'])) {
            supabaseRequest('album_photos', 'POST', $dbData);
        }

        @unlink($tempFile);
        flush(); // Stuur output direct naar browser
    }
}

echo "<h2>✨ Synchronisatie voltooid!</h2>";

/**
 * Functie om bestanden naar Supabase Storage te pushen
 */
function uploadToSupabase($filePath, $destPath, $contentType) {
    global $supabaseUrl, $serviceRoleKey;
    
    $url = rtrim($supabaseUrl, '/') . "/storage/v1/object/familie-media/" . $destPath;
    
    $ch = curl_init($url);
    $fp = fopen($filePath, 'r');
    
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => fread($fp, filesize($filePath)),
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $serviceRoleKey",
            "Content-Type: $contentType",
            "x-upsert: true"
        ]
    ]);
    
    $res = curl_exec($ch);
    curl_close($ch);
    fclose($fp);
    
    // De publieke URL constructie
    return rtrim($supabaseUrl, '/') . "/storage/v1/object/public/familie-media/" . $destPath;
}