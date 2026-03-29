<?php
/** * FORCEKES - sync-media.php (Robust Bucket Sync) */
require_once 'config.php';
set_time_limit(0); // Geen tijdslimiet voor grote syncs

echo "<h2>BUCKET <span style='color:#3b82f6;'>SYNC</span></h2>";

// 1. Zoek alle items die nog op Google staan
$items = supabaseRequest("album_photos?image_url=like.*googleusercontent*&limit=50", 'GET');

if (!is_array($items) || empty($items)) {
    die("<p style='color:green;'>Alles is al overgezet naar de Supabase Bucket!</p>");
}

foreach ($items as $item) {
    $id = $item['id'];
    $category = (string)$item['category'];
    $googleUrl = $item['image_url'];

    echo "Verwerken: $category / $id... ";

    // Download van Google met User-Agent (voorkomt 403 errors)
    $ch = curl_init($googleUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $binaryData = curl_exec($ch);
    $mimeType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    if (!$binaryData) {
        echo "<span style='color:red;'>Download mislukt</span><br>";
        continue;
    }

    // Extensie bepalen
    $ext = ($mimeType === 'video/mp4') ? '.mp4' : '.jpg';
    // Mapnaam in bucket (spaties worden %20 in URL, maar we slaan ze op als tekst)
    $storagePath = $category . '/' . $id . $ext;

    // Upload naar Supabase Storage (PUT methode is stabieler voor streams)
    $uploadUrl = SUPABASE_URL . '/storage/v1/object/familie-media/' . rawurlencode($storagePath);
    
    $ch = curl_init($uploadUrl);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); // PostgREST Storage gebruikt POST voor nieuwe files
    curl_setopt($ch, CURLOPT_POSTFIELDS, $binaryData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . SUPABASE_SERVICE_KEY,
        'Content-Type: ' . $mimeType,
        'x-upsert: true'
    ]);
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 || $httpCode === 201) {
        $newUrl = SUPABASE_URL . '/storage/v1/object/public/familie-media/' . str_replace(' ', '%20', $storagePath);
        supabaseRequest("album_photos?id=eq.$id", "PATCH", ['image_url' => $newUrl]);
        echo "<span style='color:cyan;'>Succesvol overgezet naar bucket.</span><br>";
    } else {
        echo "<span style='color:red;'>Upload fout (Code $httpCode)</span><br>";
    }

    usleep(100000); // Korte pauze voor de server
}

echo "<h3>Sync klaar. Ververs de startpagina.</h3>";