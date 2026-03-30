<?php
/** * FORCEKES - sync-media.php (Marathon Edition) */
require_once 'config.php';

// Match de 1-uur limiet van de Docker & Cron
set_time_limit(3600);
ini_set('memory_limit', '512M');
ignore_user_abort(true);

// Detecteer of dit een Cron-job of handmatige actie is
$isCron = (php_sapi_name() === 'cli' || isset($_GET['cron']));
$limit = $isCron ? 500 : 15; 

echo "<h2>MIGRATOR <span style='color:#3b82f6;'>MARATHON</span></h2>";
echo "<p>Status: <strong>" . ($isCron ? "Automatisatie Actief" : "Handmatige Batch") . "</strong></p>";

// Haal items op die nog op Google staan (geen supabase.co in de URL)
$items = supabaseRequest("album_photos?image_url=not.like.*supabase.co*&limit=$limit", 'GET');

if (is_array($items) && count($items) > 0) {
    foreach ($items as $item) {
        $id = $item['id'];
        $category = trim($item['category'] ?? 'ongecategoriseerd');
        $oldUrl = $item['image_url'];

        echo "Verwerken: $id... ";

        // Download van Google
        $ch = curl_init($oldUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $binaryData = curl_exec($ch);
        $mimeType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        if (!$binaryData || strlen($binaryData) < 1000) {
            echo "<span style='color:red;'>Download mislukt.</span><br>";
            continue;
        }

        // Pad bepalen in de Bucket
        $ext = (strpos($mimeType, 'video') !== false) ? '.mp4' : '.jpg';
        $storagePath = $category . '/' . $id . $ext;
        $uploadUrl = SUPABASE_URL . '/storage/v1/object/familie-media/' . rawurlencode($storagePath);
        
        // Upload naar Supabase Bucket
        $ch = curl_init($uploadUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $binaryData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . SUPABASE_SERVICE_KEY,
            'Content-Type: $mimeType',
            'x-upsert: true'
        ]);
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 || $httpCode === 201) {
            $newUrl = SUPABASE_URL . '/storage/v1/object/public/familie-media/' . str_replace(' ', '%20', $storagePath);
            supabaseRequest("album_photos?id=eq.$id", "PATCH", ['image_url' => $newUrl]);
            echo "<span style='color:green;'>Succes.</span><br>";
        } else {
            echo "<span style='color:red;'>Upload fout ($httpCode).</span><br>";
        }
        
        usleep(50000); // Korte pauze voor stabiliteit
    }

    // Browser-modus: ververs automatisch voor de volgende batch
    if (!$isCron) {
        echo "<script>setTimeout(() => { window.location.reload(); }, 1500);</script>";
        echo "<p>Batch voltooid. Volgende stap start automatisch...</p>";
    }
} else {
    echo "<h3 style='color:green;'>✓ Alle media staat veilig in de kluis.</h3>";
}