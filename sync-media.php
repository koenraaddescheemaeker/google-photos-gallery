<?php
/** * FORCEKES - sync-media.php (Bucket Migrator v3) */
require_once 'config.php';
set_time_limit(0); 

echo "<h2>BUCKET <span style='color:#3b82f6;'>MIGRATOR</span></h2>";

// 1. Haal de data op
$res = supabaseRequest("album_photos?select=*", 'GET');

if ($res === null) {
    die("<p style='color:red;'>FOUT: De database gaf GEEN antwoord (null). Controleer je API keys.</p>");
}

if (isset($res['error'])) {
    die("<p style='color:red;'>FOUT: Database gaf een error: " . ($res['message'] ?? 'Onbekend') . "</p>");
}

$toSync = [];
$alreadyInBucket = 0;

foreach ($res as $item) {
    $url = $item['image_url'] ?? '';
    // Als de URL NIET ons eigen Supabase domein bevat, moet hij gesynct worden
    if (!empty($url) && strpos($url, 'supabase.co') === false) {
        $toSync[] = $item;
    } else {
        $alreadyInBucket++;
    }
}

echo "<p>Database status: <strong>$alreadyInBucket</strong> items in bucket, <strong>" . count($toSync) . "</strong> items te verwerken.</p><hr>";

if (empty($toSync)) {
    echo "<p style='color:green;'>✓ Alles is al overgezet naar de Supabase Bucket!</p>";
    exit;
}

foreach ($toSync as $item) {
    $id = $item['id'];
    $category = trim($item['category'] ?? 'ongecategoriseerd');
    $externalUrl = $item['image_url'];

    echo "Verwerken: <span style='color:#3b82f6;'>$category</span> / " . substr($id, 0, 8) . "... ";

    // Downloaden met User-Agent
    $ch = curl_init($externalUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $binaryData = curl_exec($ch);
    $mimeType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    if (!$binaryData) {
        echo "<span style='color:red;'>Download mislukt.</span><br>";
        continue;
    }

    // Bestandstype en pad
    $ext = (strpos($mimeType, 'video') !== false) ? '.mp4' : '.jpg';
    $storagePath = $category . '/' . $id . $ext;

    // Upload naar Bucket
    $uploadUrl = SUPABASE_URL . '/storage/v1/object/familie-media/' . rawurlencode($storagePath);
    
    $ch = curl_init($uploadUrl);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $binaryData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . SUPABASE_SERVICE_KEY,
        'Content-Type: ' . $mimeType,
        'x-upsert: true'
    ]);
    $uploadRes = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 || $httpCode === 201) {
        // Update database met de nieuwe publieke bucket-URL
        $newUrl = SUPABASE_URL . '/storage/v1/object/public/familie-media/' . str_replace(' ', '%20', $storagePath);
        supabaseRequest("album_photos?id=eq.$id", "PATCH", ['image_url' => $newUrl]);
        echo "<span style='color:green;'>Succesvol overgezet naar bucket.</span><br>";
    } else {
        echo "<span style='color:red;'>Upload fout (Code $httpCode)</span><br>";
    }
}
echo "<h3>Sync voltooid.</h3>";