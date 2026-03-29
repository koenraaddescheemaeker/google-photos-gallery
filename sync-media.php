<?php
/**
 * FORCEKES - sync-media.php
 * Synchroniseert media-bestanden van externe bronnen naar de Supabase Storage Bucket.
 * Zo creëren we de mappen joris, museum, feest 2025, etc.
 */
require_once 'config.php';

// Zorg voor een schone output in de logs
echo "<style>
    body { background: #000; font-family: 'Inter', sans-serif; color: #fff; font-size: 11px; line-height: 1.6; padding: 30px; }
    h2 { font-weight: 900; text-transform: uppercase; color: #fff; margin-bottom: 30px; }
    .log-category { font-weight: 700; color: #3b82f6; margin-top: 20px; text-transform: uppercase; letter-spacing: 1px; }
    .log-entry { margin-left: 20px; border-left: 1px solid #222; padding-left: 15px; margin-bottom: 3px; color: #ccc; }
    .status-ok { color: #fff; font-weight: bold; }
    .status-new { color: #00ff00; font-weight: bold; }
</style>";

echo "<h2>MEDIA <span style='color:#3b82f6;'>SYNC JOB</span></h2>";

// 1. Haal alle unieke categorieën op uit de database
$categoryRequest = supabaseRequest("album_photos?select=category", 'GET');
$uniqueCategories = [];
if (is_array($categoryRequest)) {
    // Haal alle unieke categorieën eruit en sorteer ze
    $rawCategories = array_unique(array_column($categoryRequest, 'category'));
    foreach ($rawCategories as $cat) {
        if (!empty($cat)) {
            $uniqueCategories[] = (string)$cat;
        }
    }
}

echo "<div>Systeem heeft <span class='status-new'>" . count($uniqueCategories) . " categorieën</span> gevonden.</div>";

// Een array om de public URL van Supabase Storage te bouwen
$supabasePublicUrlPrefix = SUPABASE_URL . '/storage/v1/object/public/familie-media/';

foreach ($uniqueCategories as $category) {
    echo "<div class='log-category'>Categorie: " . ucfirst($category) . "</div>";

    // 2. Haal alle database entries op voor deze categorie die nog naar Google verwijzen
    // We zoeken naar image_url die googleusercontent bevat
    $googleItems = supabaseRequest("album_photos?category=eq." . rawurlencode($category) . "&image_url=like.*googleusercontent*", 'GET');

    if (is_array($googleItems) && !empty($googleItems)) {
        echo "<div class='log-entry'><span style='color:#3b82f6; font-weight:bold;'>" . count($googleItems) . " items</span> te verplaatsen naar Supabase Storage.</div>";

        foreach ($googleItems as $item) {
            $id = (string)($item['id'] ?? '');
            $googleUrl = (string)($item['image_url'] ?? '');

            // 3. Download het bestand van Google
            $tempFile = 'temp_' . $id;
            file_put_contents($tempFile, file_get_contents($googleUrl));
            
            // 4. Bepaal MIME type en extensie
            $mimeType = mime_content_type($tempFile);
            $extension = ($mimeType === 'image/jpeg') ? '.jpg' : (($mimeType === 'image/png') ? '.png' : (($mimeType === 'video/mp4') ? '.mp4' : ''));
            if (empty($extension)) {
                echo "<div class='log-entry' style='color:red;'>FOUT: Onbekend bestandstype voor ID " . substr($id, 0, 8) . ".</div>";
                unlink($tempFile);
                continue;
            }

            // 5. Upload naar Supabase Storage bucket: familie-media/[category]/[id][extension]
            // We gebruiken cURL direct voor de storage upload
            // We coderen de categorie om spaties te handhaven
            $encodedCategory = rawurlencode($category);
            $storageObjectPath = $encodedCategory . '/' . $id . $extension;
            $uploadUrl = SUPABASE_URL . '/storage/v1/object/familie-media/' . $storageObjectPath;

            $ch = curl_init($uploadUrl);
            $cfile = new CURLFile($tempFile, $mimeType, $id . $extension);
            $data = array('file' => $cfile);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . SUPABASE_SERVICE_KEY,
                // cURL handles multipart/form-data
            ]);
            $uploadRes = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 || $httpCode === 201) {
                // 6. Update database entry met de nieuwe Supabase URL
                $newSupabaseUrl = $supabasePublicUrlPrefix . $storageObjectPath;
                $updatePayload = ['image_url' => $newSupabaseUrl];
                supabaseRequest("album_photos?id=eq." . $id, "PATCH", $updatePayload);
                
                echo "<div class='log-entry'>";
                echo "<span class='timestamp'>" . date('H:i:s') . "</span>";
                echo "<span class='status-new'>📸 Bestand verplaatst:</span> ";
                echo "<span style='color:#222;'>| ID: " . substr($id, 0, 8) . "... naar familie-media/" . $category . "</span>";
                echo "</div>";
            } else {
                 echo "<div class='log-entry' style='color:red;'>FOUT bij uploaden naar Storage. HTTP Code: " . $httpCode . ".</div>";
            }

            // 7. Ruim op
            unlink($tempFile);

            // Voorkom server overload
            usleep(50000); 
        }
    } else {
        echo "<div class='log-entry'>Alles staat al in de bucket of geen items te verplaatsen.</div>";
    }
}

echo "<br><br><span class='status-ok'>✓ Synchronisatie voltooid.</span>";