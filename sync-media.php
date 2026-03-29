<?php
/**
 * FORCEKES - sync-media.php
 * Verwerkt en synchroniseert media-items tussen de bron en Supabase.
 * Geoptimaliseerd voor PHP 8.1+ (geen rtrim null warnings).
 */

require_once 'config.php';

// Zorg voor een schone, premium output in de logs
echo "<style>
    body { background: #000; font-family: 'Inter', sans-serif; color: #555; font-size: 12px; line-height: 1.6; padding: 20px; }
    .log-entry { margin-bottom: 5px; border-left: 2px solid #222; padding-left: 15px; }
    .status-ok { color: #3b82f6; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; }
    .timestamp { color: #222; margin-right: 10px; }
</style>";

// 1. Haal alle items op die verwerkt moeten worden
// We halen alles op uit de tabel om de URLs en metadata te valideren/schoonmaken
$items = supabaseRequest("album_photos?select=*", 'GET');

if (!is_array($items)) {
    die("<span style='color:red;'>FOUT: Kon geen data ophalen uit Supabase.</span>");
}

echo "<h2>SYNC <span style='color:#fff;'>PROCESS</span></h2>";

foreach ($items as $item) {
    // --- DE FIX VOOR PHP 8.1 WAARSCHUWINGEN ---
    // We casten elke variabele naar (string) voordat we rtrim() gebruiken.
    // Zelfs als een waarde NULL is in de database, wordt het nu een lege string "".
    
    $id        = (string)($item['id'] ?? '');
    $imageUrl  = (string)($item['image_url'] ?? '');
    $category  = (string)($item['category'] ?? '');
    
    // Regel 121 & 130 fix: rtrim veilig aanroepen
    $cleanUrl  = rtrim($imageUrl, '/ ');
    $cleanId   = rtrim($id, ' ');

    // Logica: Alleen updaten als er daadwerkelijk iets schoongemaakt is
    if ($cleanUrl !== $imageUrl) {
        $updatePayload = [
            'image_url' => $cleanUrl
        ];
        supabaseRequest("album_photos?id=eq." . $id, "PATCH", $updatePayload);
    }

    // De output die je in je logs zag, nu zonder de 'Deprecated' regels
    echo "<div class='log-entry'>";
    echo "<span class='timestamp'>" . date('H:i:s') . "</span>";
    echo "<span class='status-ok'>📸 Afbeelding verwerkt:</span> ";
    echo "<span style='color:#eee;'>" . date('Y-m-d H:i:s') . "</span>";
    echo " <span style='color:#222;'>| ID: " . substr($id, 0, 8) . "...</span>";
    echo "</div>";

    // Voorkom server overload bij grote hoeveelheden data
    usleep(50000); 
}

echo "<br><br><span class='status-ok'>✓ Synchronisatie voltooid.</span>";