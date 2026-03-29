<?php
/** * FORCEKES - import-bucket.php (Storage -> Database Sync) */
require_once 'config.php';

echo "<h2>BUCKET <span style='color:#3b82f6;'>DISCOVERY</span></h2>";

// 1. Haal de lijst met mappen op
$storageUrl = SUPABASE_URL . '/storage/v1/object/list/familie-media';
$ch = curl_init($storageUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['prefix' => '']));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . SUPABASE_SERVICE_KEY,
    'Content-Type: application/json'
]);
$folderList = json_decode(curl_exec($ch), true);
curl_close($ch);

if (!is_array($folderList)) die("Fout bij uitlezen bucket.");

foreach ($folderList as $f) {
    if (isset($f['id'])) continue; // Sla bestanden in de root over, we zoeken mappen (id is null bij mappen)
    
    $category = $f['name'];
    echo "<h3>Map: <span style='color:#3b82f6;'>$category</span></h3>";

    // Scan bestanden in deze map
    $ch = curl_init($storageUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['prefix' => $category]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . SUPABASE_SERVICE_KEY,
        'Content-Type: application/json'
    ]);
    $files = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (is_array($files)) {
        foreach ($files as $file) {
            if ($file['name'] === '.emptyFolderPlaceholder') continue;
            
            $fileName = $file['name'];
            $fullUrl = SUPABASE_URL . "/storage/v1/object/public/familie-media/" . rawurlencode($category) . "/" . rawurlencode($fileName);

            // Check database
            $check = supabaseRequest("album_photos?image_url=eq." . rawurlencode($fullUrl), 'GET');

            if (empty($check)) {
                $payload = [
                    'id' => bin2hex(random_bytes(12)),
                    'image_url' => $fullUrl,
                    'category' => strtolower($category),
                    'captured_at' => date('c'),
                    'mime_type' => (strpos($fileName, '.mp4') !== false) ? 'video/mp4' : 'image/jpeg'
                ];
                supabaseRequest("album_photos", "POST", $payload);
                echo "<div style='color:green;'>+ $fileName toegevoegd.</div>";
            }
        }
    }
}
echo "<p>Klaar! Ga terug naar de <a href='admin.php'>Admin</a>.</p>";