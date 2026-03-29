<?php
/** * FORCEKES - sync-media.php (Deep Scan Edition) */
require_once 'config.php';
set_time_limit(0); 

echo "<h2>BUCKET <span style='color:#3b82f6;'>DEEP SYNC</span></h2>";

// 1. Haal ALLE items op uit de database (geen filters in de URL om fouten te voorkomen)
$allItems = supabaseRequest("album_photos?select=*", 'GET');

if (!is_array($allItems)) {
    die("<p style='color:red;'>FOUT: Kon de database niet bereiken.</p>");
}

// 2. Filter in PHP op Google links
$toSync = [];
foreach ($allItems as $item) {
    if (strpos($item['image_url'], 'googleusercontent') !== false || strpos($item['image_url'], 'google') !== false) {
        $toSync[] = $item;
    }
}

$total = count($toSync);
echo "<p>Systeem heeft <strong>$total</strong> items gevonden die nog op Google staan.</p><hr>";

if ($total === 0) {
    echo "<p style='color:green; font-weight:bold;'>✓ Alles staat al veilig in de Supabase Bucket!</p>";
    exit;
}

foreach ($toSync as $item) {
    $id = $item['id'];
    $category = !empty($item['category']) ? (string)$item['category'] : 'ongecategoriseerd';
    $googleUrl = $item['image_url'];

    echo "Verwerken: <span style='color:#3b82f6;'>$category</span> / " . substr($id, 0, 8) . "... ";

    // Download met User-Agent
    $ch = curl_init($googleUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $binaryData = curl_exec($ch);
    $mimeType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    if (!$binaryData) {
        echo "<span style='color:red;'>Download van Google mislukt.</span><br>";
        continue;
    }

    // Bestandstype en pad bepalen
    $ext = (strpos($mimeType, 'video') !== false) ? '.mp4' : '.jpg';
    $storagePath = trim($category) . '/' . $id . $ext;

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
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 || $httpCode === 201) {
        // Update database met de nieuwe publieke link
        $newUrl = SUPABASE_URL . '/storage/v1/object/public/familie-media/' . str_replace(' ', '%20', $storagePath);
        supabaseRequest("album_photos?id=eq.$id", "PATCH", ['image_url' => $newUrl]);
        echo "<span style='color:green;'>Overgezet!</span><br>";
    } else {
        echo "<span style='color:red;'>Upload naar bucket mislukt (Code $httpCode)</span><br>";
    }

    usleep(50000); 
}

echo "<h3>Sync voltooid. Ververs de pagina.</h3>";