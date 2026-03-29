<?php
/** * FORCEKES - repareer-verhuizing.php (Noodverhuizing) */
require_once 'config.php';
set_time_limit(0); // Geef de uil alle tijd

$oldFolder = "feest 2024";
$newFolder = "kerstfeest 2024";

echo "<h2>Verhuisbedrijf <span style='color:#3b82f6;'>Forcekes</span></h2>";
echo "<p>Verhuizen van <strong>$oldFolder</strong> naar <strong>$newFolder</strong>...</p>";

// 1. Zoek alle foto's in de database die al op 'kerstfeest 2024' staan 
// maar waarvan de URL nog gerepareerd moet worden of die fysiek verplaatst moeten worden.
$items = supabaseRequest("album_photos?category=eq." . rawurlencode($newFolder), 'GET');

if (!is_array($items) || empty($items)) {
    die("Geen items gevonden in de database voor $newFolder.");
}

$successCount = 0;
$errorCount = 0;

foreach ($items as $item) {
    $url = $item['image_url'];
    $fileName = basename(parse_url($url, PHP_URL_PATH));
    
    echo "Check: $fileName... ";

    // We proberen het bestand fysiek te verplaatsen in de Storage
    $movePayload = [
        "bucketId" => "familie-media",
        "sourceKey" => "$oldFolder/$fileName",
        "destinationKey" => "$newFolder/$fileName"
    ];

    $ch = curl_init(SUPABASE_URL . "/storage/v1/object/move");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($movePayload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . SUPABASE_SERVICE_KEY,
        'Content-Type: application/json'
    ]);
    
    $res = json_decode(curl_exec($ch), true);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        // Verhuizing gelukt! Nu de URL in de database nog één keer definitief strak zetten
        $newUrl = SUPABASE_URL . "/storage/v1/object/public/familie-media/" . rawurlencode($newFolder) . "/" . rawurlencode($fileName);
        supabaseRequest("album_photos?id=eq." . $item['id'], "PATCH", ['image_url' => $newUrl]);
        echo "<span style='color:green;'>VERHUISD</span><br>";
        $successCount++;
    } elseif ($httpCode === 403 || $httpCode === 404) {
        // Misschien staat hij er al?
        echo "<span style='color:orange;'>REEDS AANWEZIG OF NIET GEVONDEN</span><br>";
    } else {
        echo "<span style='color:red;'>FOUT ($httpCode)</span><br>";
        $errorCount++;
    }
    
    usleep(50000); // Pauze voor de server
}

echo "<hr><p>Klaar! $successCount bestanden verhuisd, $errorCount fouten.</p>";
echo "<p><a href='index.php'>Bekijk het resultaat op de homepagina</a></p>";