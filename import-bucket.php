<?php
/** * FORCEKES - import-bucket.php (Storage naar Database) */
require_once 'config.php';

echo "<h2>BUCKET <span style='color:#3b82f6;'>DISCOVERY</span></h2>";

// 1. Haal de lijst met mappen op uit de bucket 'familie-media'
// We gebruiken de Storage API van Supabase
$storageUrl = SUPABASE_URL . '/storage/v1/object/list/familie-media';
$ch = curl_init($storageUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['prefix' => '']));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . SUPABASE_SERVICE_KEY,
    'Content-Type: application/json'
]);
$folders = json_encode(json_decode(curl_exec($ch), true)); // Lijst van mappen
curl_close($ch);

$folderList = json_decode($folders, true);

if (!is_array($folderList)) {
    die("<p style='color:red;'>Kon de bucket niet lezen. Controleer je Service Key.</p>");
}

foreach ($folderList as $f) {
    if (!isset($f['name']) || $f['id'] === null) continue; // Alleen mappen (id is null bij mappen in lijst)
    
    $category = $f['name'];
    echo "<h3>Map gevonden: <span style='color:#3b82f6;'>$category</span></h3>";

    // 2. Scan de bestanden IN deze map
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
            $fullUrl = SUPABASE_URL . "/storage/v1/object/public/familie-media/$category/$fileName";

            // 3. Controleer of deze URL al in de database staat
            $check = supabaseRequest("album_photos?image_url=eq." . rawurlencode($fullUrl), 'GET');

            if (empty($check)) {
                // Toevoegen aan DB
                $payload = [
                    'id' => bin2hex(random_bytes(12)),
                    'image_url' => $fullUrl,
                    'category' => strtolower($category),
                    'captured_at' => date('c'),
                    'mime_type' => (strpos($fileName, '.mp4') !== false) ? 'video/mp4' : 'image/jpeg'
                ];
                supabaseRequest("album_photos", "POST", $payload);
                echo "<div style='color:green; font-size:10px;'>+ Toegevoegd: $fileName</div>";
            } else {
                echo "<div style='color:#555; font-size:10px;'>- Reeds aanwezig: $fileName</div>";
            }
        }
    }
}
echo "<br><strong>Klaar! Ververs nu je startpagina.</strong>";